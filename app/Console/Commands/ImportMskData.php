<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Client;
use App\Models\Service;
use App\Models\GarmentTarget;
use App\Models\GarmentItem;
use App\Models\ServicePrice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Expense;
use App\Models\DailyCashRegister;

class ImportMskData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:msk-data {--source=c:/Users/hadib/OneDrive/Bureau/MSK-DRY-PLUS-2022 : Path to the source MSK folder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all flat-file database records from MSK Dry Plus 2022 to MySQL database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sourcePath = rtrim($this->option('source'), '/\\');

        if (!File::isDirectory($sourcePath)) {
            $this->error("The source directory does not exist: $sourcePath");
            return 1;
        }

        $this->info("Starting MSK Dry Plus data migration from: $sourcePath");

        // 1. Clear existing database tables
        $this->warn("Clearing database tables for a clean import...");
        Schema::disableForeignKeyConstraints();
        DailyCashRegister::truncate();
        Expense::truncate();
        OrderItem::truncate();
        Order::truncate();
        ServicePrice::truncate();
        GarmentItem::truncate();
        GarmentTarget::truncate();
        Service::truncate();
        Client::truncate();
        User::truncate();
        Schema::enableForeignKeyConstraints();
        $this->info("Database tables cleared successfully.");

        // 2. Create default Admin and Cashier accounts
        $this->info("Seeding default user accounts...");
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@dryplus.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $cashier = User::create([
            'name' => 'Caisse 1',
            'email' => 'caisse1@dryplus.com',
            'password' => bcrypt('password'),
            'role' => 'cashier'
        ]);

        // 3. Import Service Catalog & Tarifs from Menu/
        $this->importMenuCatalog($sourcePath);

        // 4. Import Clients from Client.db
        $this->importClients($sourcePath);

        // 5. Import Orders from CommandNo.db and command folders
        $this->importOrders($sourcePath);

        // 6. Import Expenses
        $this->importExpenses($sourcePath);

        // 7. Import Daily Cash Registers
        $this->importDailyCashRegisters($sourcePath);

        $this->info("Migration completed successfully!");
        return 0;
    }

    /**
     * Import service menu and pricing structure
     */
    private function importMenuCatalog($sourcePath)
    {
        $menuPath = $sourcePath . '/Menu';
        if (!File::isDirectory($menuPath)) {
            $this->warn("Menu directory not found at $menuPath. Skipping catalog seeding.");
            return;
        }

        $this->info("Importing services and item catalog...");

        // Ensure default services exist
        $servicesMap = [
            1 => ['name' => 'Pressing', 'code' => 'pressing'],
            2 => ['name' => 'Blanchisserie', 'code' => 'blanchisserie'],
            3 => ['name' => 'Lavage', 'code' => 'lavage'],
            4 => ['name' => 'Au Kilo', 'code' => 'au_kilo'],
            5 => ['name' => 'Repassage', 'code' => 'repassage'],
            6 => ['name' => 'Teinture', 'code' => 'teinture'],
        ];

        foreach ($servicesMap as $id => $serviceData) {
            Service::firstOrCreate(['id' => $id], $serviceData);
        }

        // Ensure default targets exist
        $targetsMap = [
            1 => 'Homme',
            2 => 'Femme',
            3 => 'Bébé',
            4 => 'Cuir',
            5 => 'Linge de maison',
        ];

        foreach ($targetsMap as $id => $name) {
            GarmentTarget::firstOrCreate(['id' => $id], ['name' => $name]);
        }

        // Scan catalog files and prices
        for ($s = 1; $s <= 6; $s++) {
            $serviceDir = "$menuPath/$s";
            if (!File::isDirectory($serviceDir)) continue;

            $service = Service::find($s);

            // Services 1, 3, 5, 6 have subdirectories for targets
            if (in_array($s, [1, 3, 5, 6])) {
                for ($t = 1; $t <= 4; $t++) {
                    $targetDir = "$serviceDir/$t";
                    if (!File::isDirectory($targetDir)) continue;

                    $target = GarmentTarget::find($t);
                    $files = File::files($targetDir);
                    
                    foreach ($files as $file) {
                        if ($file->getExtension() === 'txt') {
                            $priceVal = floatval(trim(mb_convert_encoding(File::get($file->getPathname()), 'UTF-8', 'Windows-1252')));
                            $cleanName = mb_convert_encoding($this->cleanItemName($file->getFilename()), 'UTF-8', 'Windows-1252');
                            
                            if (empty($cleanName)) continue;

                            // Create garment item
                            $item = GarmentItem::firstOrCreate(
                                ['name' => $cleanName, 'garment_target_id' => $target->id]
                            );

                            // Handle image copy if exists
                            $jpgFile = str_replace('.txt', '.jpg', $file->getPathname());
                            if (File::exists($jpgFile)) {
                                $destDir = public_path('images/catalog');
                                File::ensureDirectoryExists($destDir);
                                $destPath = $destDir . '/' . basename($jpgFile);
                                File::copy($jpgFile, $destPath);
                                $item->update(['image_path' => 'images/catalog/' . basename($jpgFile)]);
                            }

                            // Create or update service price
                            ServicePrice::updateOrCreate(
                                ['service_id' => $service->id, 'garment_item_id' => $item->id],
                                ['price' => $priceVal]
                            );
                        }
                    }
                }
            } else {
                // Services 2 (Blanchisserie) and 4 (Au Kilo) have items directly in their root
                $files = File::files($serviceDir);
                $target = GarmentTarget::find(5); // Default to Linge de maison

                foreach ($files as $file) {
                    if ($file->getExtension() === 'txt') {
                        $priceVal = floatval(trim(mb_convert_encoding(File::get($file->getPathname()), 'UTF-8', 'Windows-1252')));
                        $cleanName = mb_convert_encoding($this->cleanItemName($file->getFilename()), 'UTF-8', 'Windows-1252');
                        
                        if (empty($cleanName) || in_array($cleanName, ['BLCH', 'PRESSING', 'Baby', 'AU KILO', 'Mix'])) continue;

                        $item = GarmentItem::firstOrCreate(
                            ['name' => $cleanName, 'garment_target_id' => $target->id]
                        );

                        // Image copy
                        $jpgFile = str_replace('.txt', '.jpg', $file->getPathname());
                        if (File::exists($jpgFile)) {
                            $destDir = public_path('images/catalog');
                            File::ensureDirectoryExists($destDir);
                            $destPath = $destDir . '/' . basename($jpgFile);
                            File::copy($jpgFile, $destPath);
                            $item->update(['image_path' => 'images/catalog/' . basename($jpgFile)]);
                        }

                        ServicePrice::updateOrCreate(
                            ['service_id' => $service->id, 'garment_item_id' => $item->id],
                            ['price' => $priceVal]
                        );
                    }
                }
            }
        }
        $this->info("Catalog imported: " . GarmentItem::count() . " items, " . ServicePrice::count() . " prices.");
    }

    /**
     * Import clients list from Client.db
     */
    private function importClients($sourcePath)
    {
        $dbPath = $sourcePath . '/db';
        $clientDbFile = $dbPath . '/Client.db';

        if (!File::exists($clientDbFile)) {
            $this->warn("Client.db not found at $clientDbFile. Skipping clients import.");
            return;
        }

        $content = mb_convert_encoding(File::get($clientDbFile), 'UTF-8', 'Windows-1252');
        $lines = explode("\n", str_replace("\r\n", "\n", $content));
        $total = count($lines);
        $this->info("Importing $total clients from Client.db...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                $bar->advance();
                continue;
            }

            $data = $this->parseMskLine($line);
            if (empty($data['code'])) {
                $bar->advance();
                continue;
            }

            // Parse Date/Time
            $createdAt = $this->parseMskDateTime($data['BD'] ?? '', $data['BT'] ?? '');

            Client::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['N'] ?: 'Client Anonyme',
                    'phone' => $data['T'] ?: null,
                    'email' => $data['M'] ?: null,
                    'address' => $data['A'] ?: null,
                    'remarks' => $data['RM'] ?: null,
                    'discount_percent' => intval($data['RZ'] ?? 0),
                    'credit' => floatval($data['CR'] ?? 0.00),
                    'created_at' => $createdAt ?: now(),
                    'updated_at' => $createdAt ?: now(),
                ]
            );
            $bar->advance();
        }
        $bar->finish();
        $this->info("\nClients imported successfully: " . Client::count() . " records.");
    }

    /**
     * Import orders from CommandNo.db and archived folder
     */
    private function importOrders($sourcePath)
    {
        $dbPath = $sourcePath . '/db';
        $orders = [];
        $importedTickets = [];

        // 1. Gather orders from CommandNo.db
        $commandNoFile = $dbPath . '/CommandNo.db';
        if (File::exists($commandNoFile)) {
            $content = mb_convert_encoding(File::get($commandNoFile), 'UTF-8', 'Windows-1252');
            $lines = explode("\n", str_replace("\r\n", "\n", $content));
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $orders[] = ['line' => $line, 'source' => 'CommandNo.db'];
            }
        }

        // 2. Gather orders from db/Command/No/ directory .itm files
        $noDir = $dbPath . '/Command/No';
        if (File::isDirectory($noDir)) {
            $files = File::files($noDir);
            foreach ($files as $file) {
                if ($file->getExtension() === 'itm') {
                    $orders[] = ['line' => mb_convert_encoding(File::get($file->getPathname()), 'UTF-8', 'Windows-1252'), 'source' => $file->getFilename()];
                }
            }
        }

        // 3. Gather orders from db/Command/Oui/ directory (archived)
        $ouiDir = $dbPath . '/Command/Oui';
        if (File::isDirectory($ouiDir)) {
            $files = File::files($ouiDir);
            foreach ($files as $file) {
                if ($file->getExtension() === 'itm') {
                    $orders[] = ['line' => mb_convert_encoding(File::get($file->getPathname()), 'UTF-8', 'Windows-1252'), 'source' => $file->getFilename()];
                }
            }
        }

        $totalOrders = count($orders);
        $this->info("Importing $totalOrders order tickets...");

        $bar = $this->output->createProgressBar($totalOrders);
        $bar->start();

        foreach ($orders as $orderData) {
            $line = $orderData['line'];
            $parts = explode('#', trim($line));

            if (count($parts) < 31) {
                $bar->advance();
                continue;
            }

            $ticketNumber = trim($parts[1]);

            // Avoid double importing
            if (in_array($ticketNumber, $importedTickets)) {
                $bar->advance();
                continue;
            }

            $isDelivered = trim($parts[4]) === 'Y';
            $cashierName = trim($parts[12]) ?: 'Caisse 1';
            $clientName = trim($parts[13]) ?: 'Client Anonyme';
            $clientCode = trim($parts[15]);

            // Get or create cashier user
            $user = User::firstOrCreate(
                ['name' => $cashierName],
                [
                    'email' => strtolower(str_replace(' ', '', $cashierName)) . '@dryplus.com',
                    'password' => bcrypt('password'),
                    'role' => strtolower($cashierName) === 'admin' ? 'admin' : 'cashier'
                ]
            );

            // Find client or create guest fallback
            $client = Client::where('code', $clientCode)->first();
            if (!$client) {
                $client = Client::firstOrCreate(
                    ['name' => $clientName],
                    [
                        'code' => $clientCode ?: 'GUEST_' . uniqid(),
                        'discount_percent' => 0,
                        'credit' => 0.00
                    ]
                );
            }

            // Dates parsing
            $orderDate = $this->parseMskDateTime($parts[6] ?? '', $parts[7] ?? '') ?: now();
            $targetDeliveryDate = $this->parseMskDateOnly($parts[16] ?? '');
            if (!$targetDeliveryDate) {
                $targetDeliveryDate = Carbon::instance($orderDate)->addDays(2);
            }
            $actualDeliveryDate = $isDelivered ? ($this->parseMskDateTime($parts[17] ?? '', '12:00:00') ?: now()) : null;

            // Financials
            $totalAmount = floatval($parts[20] ?? 0);
            $paidAmount = floatval($parts[21] ?? 0);
            $balanceAmount = $totalAmount - $paidAmount;
            $isPaid = $parts[3] !== '0' || $balanceAmount <= 0;

            $discountStr = trim($parts[29] ?? '0 %');
            $discountPercent = intval(str_replace('%', '', $discountStr));

            // Determine status
            $status = 'pending';
            if ($isDelivered) {
                $status = 'delivered';
            }

            // Create Order
            $order = Order::create([
                'ticket_number' => $ticketNumber,
                'client_id' => $client->id,
                'user_id' => $user->id,
                'status' => $status,
                'is_paid' => $isPaid,
                'order_date' => $orderDate,
                'target_delivery_date' => $targetDeliveryDate,
                'actual_delivery_date' => $actualDeliveryDate,
                'discount_percent' => $discountPercent,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
                'remarks' => trim($parts[10] ?? '') ?: null,
            ]);

            $importedTickets[] = $ticketNumber;

            // Import Items inside ticket
            $itemsStr = $parts[30];
            $items = explode('|', rtrim($itemsStr, '|'));
            $allReady = true;

            foreach ($items as $itemStr) {
                if (empty(trim($itemStr))) continue;
                $itemParts = explode(';', $itemStr);

                if (count($itemParts) < 11) continue;

                $serviceName = trim($itemParts[0]);
                $targetName = trim($itemParts[1]);
                $itemName = trim($itemParts[2]);

                // Map target name
                if ($targetName === 'X' || empty($targetName)) {
                    $targetName = 'Linge de maison';
                }

                $colors = array_filter(explode('/', trim($itemParts[3])));
                $defects = array_filter(explode('/', trim($itemParts[4])));
                $stains = array_filter(explode('/', trim($itemParts[5])));

                $quantity = floatval($itemParts[6]);
                $unitPrice = floatval($itemParts[7]);
                $itemTotalPrice = floatval($itemParts[8]);
                $notes = trim($itemParts[9]) ?: null;
                $isReady = strtolower(trim($itemParts[10])) === 'true';

                if (!$isReady) {
                    $allReady = false;
                }

                // Resolve Service, Target and Garment Item dynamically to maintain references
                $service = Service::firstOrCreate(
                    ['name' => $serviceName],
                    ['code' => strtolower(str_replace(' ', '', $serviceName))]
                );

                $target = GarmentTarget::firstOrCreate(['name' => $targetName]);

                $garmentItem = GarmentItem::firstOrCreate(
                    ['name' => $itemName, 'garment_target_id' => $target->id]
                );

                // Make sure service price is stored
                ServicePrice::firstOrCreate(
                    ['service_id' => $service->id, 'garment_item_id' => $garmentItem->id],
                    ['price' => $unitPrice]
                );

                OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $service->id,
                    'garment_item_id' => $garmentItem->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotalPrice,
                    'colors' => array_values($colors),
                    'defects' => array_values($defects),
                    'stains' => array_values($stains),
                    'is_ready' => $isReady,
                    'notes' => $notes,
                ]);
            }

            // If not delivered but all items are ready, set order status to ready
            if ($status === 'pending' && $allReady && count($items) > 0) {
                $order->update(['status' => 'ready']);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nOrders and items imported successfully: " . Order::count() . " tickets.");
    }

    /**
     * Import financial expenses
     */
    private function importExpenses($sourcePath)
    {
        $dbPath = $sourcePath . '/db';
        $chargesListFile = $dbPath . '/ChargesList.db';

        $expensesCount = 0;

        if (File::exists($chargesListFile)) {
            $content = mb_convert_encoding(File::get($chargesListFile), 'UTF-8', 'Windows-1252');
            $lines = explode("\n", str_replace("\r\n", "\n", $content));
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;

                // Split by key marker since some lines contain multiple keys
                $records = explode('MSKNumKey;', $line);
                foreach ($records as $record) {
                    if (empty(trim($record))) continue;
                    $parts = explode(';', 'MSKNumKey;' . trim($record));

                    if (count($parts) < 8) continue;

                    $operator = trim($parts[2]);
                    $dateStr = trim($parts[3]);
                    $timeStr = trim($parts[4]);
                    
                    // Detect if part 3 is date
                    if (preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $dateStr)) {
                        $date = $this->parseMskDateTime($dateStr, $timeStr) ?: now();
                        $amount = floatval($parts[6] ?? 0);
                        $category = trim($parts[7] ?? 'Général');
                        $notes = trim($parts[9] ?? '') ?: null;
                    } else {
                        // Part 3 is amount
                        $amount = floatval($parts[3]);
                        $category = trim($parts[4]);
                        $notes = trim($parts[5]) ?: null;
                        $date = now();
                    }

                    $user = User::where('name', $operator)->first() ?: User::first();

                    Expense::create([
                        'category' => $category ?: 'Autre',
                        'amount' => $amount,
                        'user_id' => $user ? $user->id : null,
                        'expense_date' => $date,
                        'notes' => $notes,
                    ]);
                    $expensesCount++;
                }
            }
        }

        // Scan folder db/Charges/*.itm for individual logs
        $chargesDir = $dbPath . '/Charges';
        if (File::isDirectory($chargesDir)) {
            $files = File::files($chargesDir);
            foreach ($files as $file) {
                if ($file->getExtension() === 'itm') {
                    $content = trim(mb_convert_encoding(File::get($file->getPathname()), 'UTF-8', 'Windows-1252'));
                    $parts = explode(';', $content);
                    if (count($parts) < 6) continue;

                    $operator = trim($parts[2]);
                    $dateStr = trim($parts[3]);
                    
                    if (preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $dateStr)) {
                        $timeStr = trim($parts[4] ?? '00:00:00');
                        $date = $this->parseMskDateTime($dateStr, $timeStr) ?: now();
                        $amount = floatval($parts[6] ?? 0);
                        $category = trim($parts[7] ?? 'Dépense');
                        $notes = trim($parts[9] ?? '') ?: null;
                    } else {
                        $amount = floatval($parts[3]);
                        $category = trim($parts[4]);
                        $notes = trim($parts[5]) ?: null;
                        $date = now();
                    }

                    $user = User::where('name', $operator)->first() ?: User::first();

                    Expense::create([
                        'category' => $category ?: 'Autre',
                        'amount' => $amount,
                        'user_id' => $user ? $user->id : null,
                        'expense_date' => $date,
                        'notes' => $notes,
                    ]);
                    $expensesCount++;
                }
            }
        }

        $this->info("Expenses imported: $expensesCount records.");
    }

    /**
     * Import daily cash registers history
     */
    private function importDailyCashRegisters($sourcePath)
    {
        $dbPath = $sourcePath . '/db';
        $ciDir = $dbPath . '/CISystem';

        if (!File::isDirectory($ciDir)) {
            $this->warn("CISystem directory not found. Skipping daily cash registers import.");
            return;
        }

        $files = File::files($ciDir);
        $count = 0;

        foreach ($files as $file) {
            if ($file->getExtension() === 'itm') {
                $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                
                // Parse date from ddmmyy format
                if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $filename, $matches)) {
                    $day = $matches[1];
                    $month = $matches[2];
                    $year = '20' . $matches[3];

                    try {
                        $date = Carbon::parse("$year-$month-$day");
                        $openingCash = floatval(trim(File::get($file->getPathname())));

                        DailyCashRegister::updateOrCreate(
                            ['date' => $date->format('Y-m-d')],
                            [
                                'opening_cash' => $openingCash,
                                'closing_cash' => null, // Historically only opening register is recorded in Flat file
                                'user_id' => User::first() ? User::first()->id : null
                            ]
                        );
                        $count++;
                    } catch (\Exception $e) {
                        // Ignore date parse failure
                    }
                }
            }
        }
        $this->info("Daily Cash registers imported: $count records.");
    }

    /**
     * Clean leading numbers and dash from catalog items
     */
    private function cleanItemName($filename)
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/^\d+\s*[-_]?\s*/', '', $name);
        return trim($name);
    }

    /**
     * Parse MSK flat file line to key-value pairs
     */
    private function parseMskLine($line)
    {
        $parts = explode(';', trim($line));
        $data = [];
        $data['magic'] = $parts[0] ?? '';
        $data['code'] = $parts[1] ?? '';

        for ($i = 2; $i < count($parts) - 1; $i += 2) {
            $key = $parts[$i] ?? null;
            $val = $parts[$i + 1] ?? '';
            if ($key !== null) {
                $data[trim($key)] = trim($val);
            }
        }
        return $data;
    }

    /**
     * Parse date and time in d/m/y format
     */
    private function parseMskDateTime($dateStr, $timeStr)
    {
        $dateStr = trim($dateStr);
        $timeStr = trim($timeStr);

        if (empty($dateStr)) return null;
        if (empty($timeStr)) $timeStr = '00:00:00';

        try {
            return Carbon::createFromFormat('d/m/y H:i:s', "$dateStr $timeStr");
        } catch (\Exception $e) {
            try {
                return Carbon::parse("$dateStr $timeStr");
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    /**
     * Parse date only in d/m/y format
     */
    private function parseMskDateOnly($dateStr)
    {
        $dateStr = trim($dateStr);
        if (empty($dateStr)) return null;

        try {
            return Carbon::createFromFormat('d/m/y', $dateStr)->startOfDay();
        } catch (\Exception $e) {
            try {
                return Carbon::parse($dateStr)->startOfDay();
            } catch (\Exception $e) {
                return null;
            }
        }
    }
}
