<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paradou:backup {--dir= : Dossier de destination optionnel}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Effectue une sauvegarde complète de la base de données vers le dossier configuré';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $customDir = $this->option('dir');
        $targetDir = $customDir ?: $backupService->getBackupDirectory();

        $this->info("Dossier de destination : {$targetDir}");
        $this->line("Sauvegarde en cours...");

        try {
            $result = $backupService->createBackup($customDir);

            $this->newLine();
            $this->info("=================================================");
            $this->info("  SAUVEGARDE EFFECTUÉE AVEC SUCCÈS !");
            $this->info("=================================================");
            $this->line(" Fichier : <comment>{$result['filename']}</comment>");
            $this->line(" Chemin  : <comment>{$result['path']}</comment>");
            $this->line(" Taille  : <comment>{$result['size']}</comment>");
            $this->line(" Date    : <comment>{$result['created_at']}</comment>");
            $this->newLine();

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Erreur lors de la sauvegarde : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
