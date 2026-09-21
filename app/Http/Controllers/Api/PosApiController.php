<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use App\Models\GarmentItem;
use App\Models\GarmentTarget;
use App\Models\GarmentSubcategory;
use App\Models\Service;
use App\Models\ServicePrice;

class PosApiController extends Controller
{
    /**
     * Health check endpoint for POS network watchdog.
     */
    public function ping()
    {
        return response()->json([
            'status' => 'ok',
            'server_time' => now()->toISOString(),
            'app_name' => 'Paradou / MSK Dry Plus',
            'api_version' => '1.0.0'
        ]);
    }

    /**
     * Bootstrap data for the desktop POS local database / cache.
     * Returns full catalog, services, prices, dictionary rubrics, and active clients.
     */
    public function bootstrap()
    {
        // 1. Targets (Categories) and Subcategories
        $targets = GarmentTarget::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'name', 'sort_order']);

        $subcategories = GarmentSubcategory::orderBy('garment_target_id')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get(['id', 'garment_target_id', 'name', 'sort_order']);

        // 2. Services
        $services = Service::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'name', 'code', 'price', 'sort_order']);

        // 3. Items with simplified service prices map
        $rawItems = GarmentItem::with('servicePrices')->get();
        $items = $rawItems->map(function ($item) {
            $pricesMap = [];
            foreach ($item->servicePrices as $sp) {
                $pricesMap[$sp->service_id] = floatval($sp->price);
            }
            return [
                'id' => $item->id,
                'name' => $item->name,
                'garment_target_id' => $item->garment_target_id,
                'garment_subcategory_id' => $item->garment_subcategory_id,
                'standard_weight' => $item->standard_weight ? floatval($item->standard_weight) : null,
                'is_carpet' => (bool)$item->is_carpet,
                'unit_type' => $item->unit_type ?? 'piece',
                'image_path' => $item->image_path ? asset($item->image_path) : null,
                'prices' => $pricesMap
            ];
        });

        // 4. Clients
        $clients = Client::orderBy('name', 'asc')->get([
            'id', 'code', 'name', 'phone', 'email', 'address', 'discount_percent', 'credit', 'remarks'
        ]);

        // 5. Options dictionary
        $colors = $this->loadDictionary('Couleur.db', [
            'argent', 'azur', 'beige', 'blanc', 'blanc cassé', 'bleu', 'bleu ciel', 
            'bleu marine', 'bleu turquoise', 'blond', 'blond vénitien', 'bordeaux', 'brun', 'châtain', 'écru', 'fauve', 
            'fushia', 'grenat', 'gris', 'indigo', 'ivoire', 'jaune', 'kaki', 'marron', 'mauve', 'noir', 'or', 
            'orange', 'rose', 'rouge', 'saumon', 'sépia', 'vert', 'vert eau', 'vert émeraude', 'vert olive', 'vert pistache', 'violet'
        ]);

        $defects = $this->loadDictionary('Defauts.db', [
            'Bouton Brisé', 'Bouton Manquant', 'Bulle', 'Col Déchiré', 'Déchiré', 
            'Délavé', 'Manchette Déchirée', 'Marque de Repassage', 'Repassage Service', 
            'Tissu Boulochage', 'Trou'
        ]);

        $stains = $this->loadDictionary('Taches.db', [
            'Aliments', 'Alcool', 'Biro', 'Boue', 'Café', 'Couleur Purge', 
            'Collier Souillée', 'Eau de Javel', 'EncreGraisse', 'Maquillage', 
            'Moisissure', 'Parfum', 'Peinture', 'Pétrole', 'Rouille', 'Col souillé', 
            'Sang', 'Transpiration', 'Vin'
        ]);

        $patterns = [
            'A carreaux', 'A rayures', 'Bi color', 'Florale', 'Moucheté', 'Pied de poule'
        ];

        // 6. Next ticket number recommendation
        $lastOrder = Order::orderBy('id', 'desc')->first();
        $nextTicketNumber = $lastOrder ? str_pad(intval($lastOrder->ticket_number) + 1, 6, '0', STR_PAD_LEFT) : '000001';

        return response()->json([
            'success' => true,
            'synced_at' => now()->toISOString(),
            'targets' => $targets,
            'subcategories' => $subcategories,
            'services' => $services,
            'items' => $items,
            'clients' => $clients,
            'rubrics' => [
                'colors' => $colors,
                'defects' => $defects,
                'stains' => $stains,
                'patterns' => $patterns
            ],
            'next_ticket_number' => $nextTicketNumber,
            'store_info' => [
                'name' => 'MSK DRY PLUS',
                'brand' => 'PARADOU',
                'slogan' => 'Pressing - Blanchisserie - Tapis',
                'currency' => 'DA',
                'phone' => '0550 00 00 00'
            ]
        ]);
    }

    /**
     * Pull delta updates since a given timestamp.
     */
    public function pullUpdates(Request $request)
    {
        $since = $request->input('since');
        if (!$since) {
            return $this->bootstrap();
        }

        $sinceDate = Carbon::parse($since);

        $updatedClients = Client::where('updated_at', '>=', $sinceDate)->get();
        $updatedItems = GarmentItem::with('servicePrices')->where('updated_at', '>=', $sinceDate)->get();
        $updatedTargets = GarmentTarget::where('updated_at', '>=', $sinceDate)->get();
        $updatedSubcategories = GarmentSubcategory::where('updated_at', '>=', $sinceDate)->get();
        $updatedServices = Service::where('updated_at', '>=', $sinceDate)->get();

        return response()->json([
            'success' => true,
            'synced_at' => now()->toISOString(),
            'has_updates' => ($updatedClients->count() + $updatedItems->count() + $updatedTargets->count()) > 0,
            'clients' => $updatedClients,
            'items' => $updatedItems,
            'targets' => $updatedTargets,
            'subcategories' => $updatedSubcategories,
            'services' => $updatedServices
        ]);
    }

    /**
     * Batch synchronisation of orders created offline on the POS desktop application.
     * Guaranteed idempotency using the order UUID.
     */
    public function syncOrders(Request $request)
    {
        $payload = $request->validate([
            'terminal_code' => 'nullable|string',
            'pos_terminal_code' => 'nullable|string',
            'orders' => 'required|array',
            'orders.*.uuid' => 'required|string',
            'orders.*.pos_terminal_code' => 'nullable|string',
            'orders.*.ticket_number' => 'required|string',
            'orders.*.client_id' => 'nullable|integer',
            'orders.*.client_code' => 'nullable|string',
            'orders.*.user_id' => 'nullable|integer',
            'orders.*.status' => 'nullable|string',
            'orders.*.order_date' => 'nullable|string',
            'orders.*.target_delivery_date' => 'nullable|string',
            'orders.*.actual_delivery_date' => 'nullable|string',
            'orders.*.discount_percent' => 'nullable|numeric',
            'orders.*.discount_type' => 'nullable|string',
            'orders.*.discount_amount' => 'nullable|numeric',
            'orders.*.total_amount' => 'required|numeric',
            'orders.*.total_weight' => 'nullable|numeric',
            'orders.*.paid_amount' => 'required|numeric',
            'orders.*.balance_amount' => 'required|numeric',
            'orders.*.remarks' => 'nullable|string',
            'orders.*.is_express' => 'nullable|boolean',
            'orders.*.items' => 'required|array|min:1',
            'orders.*.items.*.service_id' => 'required|integer',
            'orders.*.items.*.garment_item_id' => 'required|integer',
            'orders.*.items.*.quantity' => 'nullable|numeric',
            'orders.*.items.*.unit_price' => 'required|numeric',
            'orders.*.items.*.total_price' => 'required|numeric',
            'orders.*.items.*.colors' => 'nullable',
            'orders.*.items.*.defects' => 'nullable',
            'orders.*.items.*.stains' => 'nullable',
            'orders.*.items.*.notes' => 'nullable|string',
            'orders.*.items.*.is_ready' => 'nullable|boolean',
        ]);

        $results = [];

        foreach ($payload['orders'] as $orderData) {
            $uuid = $orderData['uuid'];

            // 1. Idempotency Check: if UUID already exists in cloud, return already_synced
            $existing = Order::where('uuid', $uuid)->first();
            if ($existing) {
                $results[] = [
                    'uuid' => $uuid,
                    'status' => 'already_synced',
                    'order_id' => $existing->id,
                    'ticket_number' => $existing->ticket_number,
                    'message' => 'Commande déjà synchronisée.'
                ];
                continue;
            }

            // 2. Process within atomic transaction
            try {
                $orderRecord = DB::transaction(function () use ($orderData, $uuid) {
                    // Resolve Client
                    $client = null;
                    if (!empty($orderData['client_id'])) {
                        $client = Client::find($orderData['client_id']);
                    }
                    if (!$client && !empty($orderData['client_code'])) {
                        $client = Client::where('code', $orderData['client_code'])->first();
                    }
                    if (!$client) {
                        $client = Client::firstOrCreate(
                            ['code' => 'GUEST'],
                            ['name' => 'Client Passage', 'discount_percent' => 0, 'credit' => 0.00]
                        );
                    }

                    // Terminal code
                    $terminalCode = $orderData['pos_terminal_code'] ?? $payload['terminal_code'] ?? $payload['pos_terminal_code'] ?? null;

                    // Ticket number uniqueness handling
                    $ticketNumber = $orderData['ticket_number'];
                    if (Order::where('ticket_number', $ticketNumber)->exists()) {
                        // Append POS terminal or unique suffix to guarantee non-collision
                        $termPrefix = $terminalCode ?: 'POS';
                        $ticketNumber = "{$termPrefix}-{$ticketNumber}";
                    }

                    $order = Order::create([
                        'uuid' => $uuid,
                        'pos_terminal_code' => $terminalCode,
                        'ticket_number' => $ticketNumber,
                        'client_id' => $client->id,
                        'user_id' => $orderData['user_id'] ?? null,
                        'status' => $orderData['status'] ?? 'pending',
                        'is_paid' => $orderData['balance_amount'] <= 0,
                        'order_date' => !empty($orderData['order_date']) ? Carbon::parse($orderData['order_date']) : now(),
                        'target_delivery_date' => !empty($orderData['target_delivery_date']) ? Carbon::parse($orderData['target_delivery_date']) : now()->addDays(2),
                        'actual_delivery_date' => !empty($orderData['actual_delivery_date']) ? Carbon::parse($orderData['actual_delivery_date']) : null,
                        'discount_percent' => $orderData['discount_percent'] ?? 0,
                        'discount_type' => $orderData['discount_type'] ?? 'percent',
                        'discount_amount' => $orderData['discount_amount'] ?? 0.00,
                        'total_amount' => $orderData['total_amount'],
                        'total_weight' => $orderData['total_weight'] ?? null,
                        'paid_amount' => $orderData['paid_amount'],
                        'balance_amount' => $orderData['balance_amount'],
                        'remarks' => $orderData['remarks'] ?? null,
                        'is_express' => $orderData['is_express'] ?? false,
                        'synced_at' => now(),
                    ]);

                    // Insert Order Items
                    foreach ($orderData['items'] as $itemData) {
                        $colors = $itemData['colors'] ?? null;
                        if ($colors !== null && !is_array($colors)) {
                            $colors = [$colors];
                        }

                        $defects = $itemData['defects'] ?? null;
                        if ($defects !== null && !is_array($defects)) {
                            $defects = [$defects];
                        }

                        $stains = $itemData['stains'] ?? null;
                        if ($stains !== null && !is_array($stains)) {
                            $stains = [$stains];
                        }

                        OrderItem::create([
                            'order_id' => $order->id,
                            'service_id' => $itemData['service_id'],
                            'garment_item_id' => $itemData['garment_item_id'],
                            'quantity' => $itemData['quantity'] ?? 1.00,
                            'unit_price' => $itemData['unit_price'],
                            'total_price' => $itemData['total_price'],
                            'colors' => $colors,
                            'defects' => $defects,
                            'stains' => $stains,
                            'notes' => $itemData['notes'] ?? null,
                            'is_ready' => $itemData['is_ready'] ?? false,
                        ]);
                    }

                    // Update client balance/credit
                    if ($order->balance_amount > 0 && !$client->isPassager()) {
                        $client->increment('credit', $order->balance_amount);
                    }

                    return $order;
                });

                $results[] = [
                    'uuid' => $uuid,
                    'status' => 'success',
                    'order_id' => $orderRecord->id,
                    'ticket_number' => $orderRecord->ticket_number,
                    'message' => 'Commande synchronisée avec succès.'
                ];
            } catch (\Exception $e) {
                Log::error("POS Sync error on order UUID {$uuid}: " . $e->getMessage());
                $results[] = [
                    'uuid' => $uuid,
                    'status' => 'error',
                    'message' => 'Erreur lors de la synchronisation : ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'processed_count' => count($results),
            'results' => $results,
            'synced_at' => now()->toISOString()
        ]);
    }

    /**
     * Batch synchronization of clients created offline.
     */
    public function syncClients(Request $request)
    {
        $payload = $request->validate([
            'clients' => 'required|array',
            'clients.*.local_id' => 'nullable',
            'clients.*.name' => 'required|string|max:255',
            'clients.*.phone' => 'nullable|string|max:50',
            'clients.*.email' => 'nullable|email|max:255',
            'clients.*.address' => 'nullable|string|max:255',
            'clients.*.discount_percent' => 'nullable|integer|min:0|max:100',
            'clients.*.remarks' => 'nullable|string'
        ]);

        $results = [];

        foreach ($payload['clients'] as $c) {
            $phone = !empty($c['phone']) ? trim($c['phone']) : null;
            $client = null;

            // Match existing by phone
            if ($phone) {
                $client = Client::where('phone', $phone)->first();
            }

            if (!$client) {
                // Generate next code
                $lastClient = Client::orderBy('id', 'desc')->first();
                $nextCode = $lastClient ? str_pad(intval($lastClient->code) + 1, 6, '0', STR_PAD_LEFT) : '000001';

                $client = Client::create([
                    'code' => $nextCode,
                    'name' => $c['name'],
                    'phone' => $phone,
                    'email' => $c['email'] ?? null,
                    'address' => $c['address'] ?? null,
                    'discount_percent' => $c['discount_percent'] ?? 0,
                    'credit' => 0.00,
                    'remarks' => $c['remarks'] ?? null,
                ]);
            }

            $results[] = [
                'local_id' => $c['local_id'] ?? null,
                'server_id' => $client->id,
                'code' => $client->code,
                'name' => $client->name,
                'status' => 'synced'
            ];
        }

        return response()->json([
            'success' => true,
            'clients' => $results,
            'synced_at' => now()->toISOString()
        ]);
    }

    /**
     * Helper to load dictionary from flat files or fallback array.
     */
    private function loadDictionary(string $filename, array $default): array
    {
        $paths = [
            storage_path('app/db/' . $filename),
            storage_path('app/Menu/0/2/' . $filename),
            'c:/Users/hadib/OneDrive/Bureau/MSK-DRY-PLUS-2022/db/' . $filename
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if (!empty($lines)) {
                    $cleaned = [];
                    foreach ($lines as $line) {
                        $trimmed = trim($line);
                        if ($trimmed !== '') {
                            if (!mb_check_encoding($trimmed, 'UTF-8')) {
                                $trimmed = mb_convert_encoding($trimmed, 'UTF-8', 'Windows-1252, ISO-8859-1, UTF-8');
                            }
                            $cleaned[] = $trimmed;
                        }
                    }
                    if (!empty($cleaned)) {
                        return array_values(array_unique($cleaned));
                    }
                }
            }
        }

        return $default;
    }
}
