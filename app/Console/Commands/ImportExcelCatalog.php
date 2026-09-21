<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Service;
use App\Models\GarmentTarget;
use App\Models\GarmentSubcategory;
use App\Models\GarmentItem;
use App\Models\ServicePrice;

class ImportExcelCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:import-excel {--file= : Path to the xlsx file} {--dry-run : Simulate without writing to DB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and replace catalog from the Excel spreadsheet into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== DÉMARRAGE DE L'IMPORTATION DU CATALOGUE ===");

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn("MODE SIMULATION (DRY-RUN) ACTIF - Aucune modification ne sera enregistrée en base.");
        }

        // 1. Run Python extraction script to generate fresh JSON payload
        $scriptPath = base_path('scripts/extract_catalog.py');
        $jsonPayloadPath = storage_path('app/catalog_extracted.json');

        if (File::exists($scriptPath)) {
            $this->info("Extraction des données depuis le fichier Excel...");
            $cmd = 'python ' . escapeshellarg($scriptPath);
            exec($cmd, $output, $returnVar);
            if ($returnVar !== 0) {
                $this->warn("Avertissement : L'exécution du script Python d'extraction a retourné un code " . $returnVar);
            }
        }

        if (!File::exists($jsonPayloadPath)) {
            $this->error("Erreur : Le fichier JSON extrait [{$jsonPayloadPath}] est introuvable.");
            return 1;
        }

        $payload = json_decode(File::get($jsonPayloadPath), true);
        if (!$payload || !isset($payload['articles'])) {
            $this->error("Erreur : Données JSON invalides ou incomplètes.");
            return 1;
        }

        $meta = $payload['metadata'] ?? [];
        $this->info("Données prêtes : " . ($meta['total_articles'] ?? count($payload['articles'])) . " articles trouvés dans le catalogue.");

        // 2. Perform DB Backup before modifying anything
        if (!$isDryRun) {
            $this->backupCurrentCatalog();
        }

        // 3. Process Import inside a Transaction
        DB::beginTransaction();
        try {
            $targetsCreated = 0;
            $targetsUpdated = 0;
            $subcatsCreated = 0;
            $subcatsUpdated = 0;
            $itemsCreated = 0;
            $itemsUpdated = 0;
            $pricesInitialized = 0;

            // Cache services
            $services = Service::all();
            if ($services->isEmpty()) {
                $this->warn("Aucun service n'est présent dans la base. Création des services de base...");
                $defaultServices = [
                    ['name' => 'Pressing', 'code' => 'pressing', 'sort_order' => 1],
                    ['name' => 'Blanchisserie', 'code' => 'blanchisserie', 'sort_order' => 2],
                    ['name' => 'Lavage', 'code' => 'lavage', 'sort_order' => 3],
                    ['name' => 'Au Kilo', 'code' => 'au_kilo', 'sort_order' => 4],
                    ['name' => 'Repassage', 'code' => 'repassage', 'sort_order' => 5],
                    ['name' => 'Teinture', 'code' => 'teinture', 'sort_order' => 6],
                ];
                foreach ($defaultServices as $ds) {
                    Service::create($ds);
                }
                $services = Service::all();
            }

            // A. Import Targets
            $targetMap = []; // name => GarmentTarget
            foreach ($payload['targets'] as $tData) {
                $target = GarmentTarget::where('name', $tData['name'])->first();
                if (!$target && $tData['name'] === 'Cuir & Daim') {
                    $target = GarmentTarget::where('name', 'Cuir')->first();
                }
                if ($target) {
                    $target->update([
                        'name' => $tData['name'],
                        'sort_order' => $tData['sort_order']
                    ]);
                    $targetsUpdated++;
                } else {
                    $target = GarmentTarget::create([
                        'name' => $tData['name'],
                        'sort_order' => $tData['sort_order']
                    ]);
                    $targetsCreated++;
                }
                $targetMap[$tData['name']] = $target;
            }

            // B. Import Subcategories
            $subcatMap = []; // (target_name, subcat_name) => GarmentSubcategory
            $subcatOrderCounter = [];

            foreach ($payload['subcategories'] as $scData) {
                $targetName = $scData['target_name'];
                $scName = $scData['name'];
                $target = $targetMap[$targetName] ?? null;

                if (!$target) continue;

                $subcatOrderCounter[$targetName] = ($subcatOrderCounter[$targetName] ?? 0) + 1;
                $sortOrder = $subcatOrderCounter[$targetName];

                $subcat = GarmentSubcategory::where('garment_target_id', $target->id)
                    ->where('name', $scName)
                    ->first();

                if ($subcat) {
                    $subcat->update(['sort_order' => $sortOrder]);
                    $subcatsUpdated++;
                } else {
                    $subcat = GarmentSubcategory::create([
                        'garment_target_id' => $target->id,
                        'name' => $scName,
                        'sort_order' => $sortOrder
                    ]);
                    $subcatsCreated++;
                }

                $subcatMap[$targetName . '|' . $scName] = $subcat;
            }

            // C. Import Articles
            foreach ($payload['articles'] as $artData) {
                $targetName = $artData['target_name'];
                $subcatName = $artData['subcategory_name'];
                $articleName = $artData['article_name'];
                $stdWeight = $artData['standard_weight'];
                $isCarpet = (bool)($artData['is_carpet'] ?? false);
                $unitType = $artData['unit_type'] ?? 'piece';

                $target = $targetMap[$targetName] ?? null;
                $subcat = !empty($subcatName) ? ($subcatMap[$targetName . '|' . $subcatName] ?? null) : null;

                if (!$target) continue;

                // Match existing item strictly by target and article name
                $item = GarmentItem::where('garment_target_id', $target->id)
                    ->where('name', $articleName)
                    ->first();

                if ($item) {
                    $item->update([
                        'garment_subcategory_id' => $subcat?->id,
                        'standard_weight' => $stdWeight ?: $item->standard_weight,
                        'is_carpet' => $isCarpet,
                        'unit_type' => $unitType,
                    ]);
                    $itemsUpdated++;
                } else {
                    $item = GarmentItem::create([
                        'garment_target_id' => $target->id,
                        'garment_subcategory_id' => $subcat?->id,
                        'name' => $articleName,
                        'standard_weight' => $stdWeight,
                        'is_carpet' => $isCarpet,
                        'unit_type' => $unitType,
                    ]);
                    $itemsCreated++;
                }

                // Initialize ServicePrices for all active services
                foreach ($services as $srv) {
                    $sp = ServicePrice::where('garment_item_id', $item->id)
                        ->where('service_id', $srv->id)
                        ->first();

                    if (!$sp) {
                        ServicePrice::create([
                            'garment_item_id' => $item->id,
                            'service_id' => $srv->id,
                            'price' => 0.00,
                            'wholesale_price' => null,
                        ]);
                        $pricesInitialized++;
                    }
                }
            }

            // Clean up empty obsolete subcategories if any
            GarmentSubcategory::doesntHave('garmentItems')->delete();

            if ($isDryRun) {
                DB::rollBack();
                $this->info("Simulation terminée avec succès (Rollback effectué).");
            } else {
                DB::commit();
                $this->info("Importation enregistrée avec succès en base de données !");
            }

            $this->newLine();
            $this->table(
                ['Entité', 'Créés', 'Mis à jour / Conservés', 'Total en Base'],
                [
                    ['Rubriques (Catégories)', $targetsCreated, $targetsUpdated, GarmentTarget::count()],
                    ['Sous-Catégories', $subcatsCreated, $subcatsUpdated, GarmentSubcategory::count()],
                    ['Articles', $itemsCreated, $itemsUpdated, GarmentItem::count()],
                    ['Tarifs Services Initialisés', $pricesInitialized, '-', ServicePrice::count()],
                ]
            );

            return 0;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Erreur durant l'importation : " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Backup existing catalog tables to a JSON file.
     */
    protected function backupCurrentCatalog(): void
    {
        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = "{$backupDir}/catalog_backup_{$timestamp}.json";

        $data = [
            'timestamp' => $timestamp,
            'targets' => GarmentTarget::all()->toArray(),
            'subcategories' => GarmentSubcategory::all()->toArray(),
            'items' => GarmentItem::all()->toArray(),
            'prices' => ServicePrice::all()->toArray(),
        ];

        File::put($backupFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("Sauvegarde de sécurité créée : [{$backupFile}]");
    }
}
