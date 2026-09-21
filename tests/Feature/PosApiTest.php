<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\GarmentItem;
use App\Models\GarmentTarget;
use App\Models\ServicePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class PosApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected GarmentTarget $target;
    protected Service $service;
    protected GarmentItem $item;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $this->target = GarmentTarget::create([
            'name' => 'Homme',
            'sort_order' => 1,
        ]);

        $this->service = Service::create([
            'name' => 'Nettoyage',
            'code' => 'nettoyage',
            'price' => 300,
            'sort_order' => 1,
        ]);

        $this->item = GarmentItem::create([
            'name' => 'Veste',
            'garment_target_id' => $this->target->id,
        ]);

        ServicePrice::create([
            'garment_item_id' => $this->item->id,
            'service_id' => $this->service->id,
            'price' => 450,
        ]);

        $this->client = Client::create([
            'code' => 'CLI-001',
            'name' => 'Karim Benali',
            'phone' => '0555123456',
            'discount_percent' => 0,
            'credit' => 0.00,
        ]);
    }

    public function test_pos_ping_returns_online_status(): void
    {
        $response = $this->getJson('/api/pos/ping');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'ok',
                     'api_version' => '1.0.0',
                 ]);
    }

    public function test_pos_bootstrap_returns_catalog_and_clients(): void
    {
        $response = $this->getJson('/api/pos/bootstrap');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'synced_at',
                     'targets',
                     'subcategories',
                     'services',
                     'items',
                     'clients',
                     'rubrics' => [
                         'colors',
                         'defects',
                         'stains',
                         'patterns'
                     ],
                     'next_ticket_number',
                     'store_info'
                 ]);

        $this->assertCount(1, $response->json('targets'));
        $this->assertCount(1, $response->json('items'));
        $this->assertEquals(450, $response->json('items.0.prices.' . $this->service->id));
    }

    public function test_pos_pull_updates_returns_delta(): void
    {
        $since = now()->subHour()->toISOString();
        $response = $this->getJson('/api/pos/sync/pull?since=' . urlencode($since));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'synced_at',
                     'has_updates',
                     'clients',
                     'items',
                     'targets',
                     'subcategories',
                     'services'
                 ]);
    }

    public function test_pos_sync_orders_creates_orders_and_updates_client_credit(): void
    {
        $orderUuid = (string) Str::uuid();

        $payload = [
            'orders' => [
                [
                    'uuid' => $orderUuid,
                    'pos_terminal_code' => 'POS-CAISSE-1',
                    'ticket_number' => 'TCK-9901',
                    'client_id' => $this->client->id,
                    'user_id' => $this->cashier->id,
                    'status' => 'pending',
                    'total_amount' => 450,
                    'paid_amount' => 200,
                    'balance_amount' => 250,
                    'remarks' => 'Commande synchronisée depuis Electron Desktop POS',
                    'items' => [
                        [
                            'garment_item_id' => $this->item->id,
                            'service_id' => $this->service->id,
                            'quantity' => 1,
                            'unit_price' => 450,
                            'total_price' => 450,
                            'colors' => 'Bleu marine',
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/pos/sync/orders', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'processed_count' => 1,
                 ]);

        $this->assertDatabaseHas('orders', [
            'uuid' => $orderUuid,
            'pos_terminal_code' => 'POS-CAISSE-1',
            'total_amount' => 450,
            'paid_amount' => 200,
            'balance_amount' => 250,
        ]);

        $this->assertDatabaseHas('order_items', [
            'garment_item_id' => $this->item->id,
            'service_id' => $this->service->id,
            'unit_price' => 450,
            'total_price' => 450,
        ]);

        $createdOrder = Order::where('uuid', $orderUuid)->first();
        $this->assertNotNull($createdOrder);
        $this->assertCount(1, $createdOrder->items);
        $this->assertEquals(['Bleu marine'], $createdOrder->items->first()->colors);

        // Client credit check: 250 credit added
        $this->assertEquals(250.00, $this->client->fresh()->credit);
    }

    public function test_pos_sync_orders_is_idempotent_with_duplicate_uuid(): void
    {
        $orderUuid = (string) Str::uuid();

        $payload = [
            'orders' => [
                [
                    'uuid' => $orderUuid,
                    'pos_terminal_code' => 'POS-CAISSE-1',
                    'ticket_number' => 'TCK-9902',
                    'client_id' => $this->client->id,
                    'user_id' => $this->cashier->id,
                    'status' => 'pending',
                    'total_amount' => 450,
                    'paid_amount' => 450,
                    'balance_amount' => 0,
                    'items' => [
                        [
                            'garment_item_id' => $this->item->id,
                            'service_id' => $this->service->id,
                            'quantity' => 1,
                            'unit_price' => 450,
                            'total_price' => 450,
                        ]
                    ]
                ]
            ]
        ];

        // First sync
        $firstResponse = $this->postJson('/api/pos/sync/orders', $payload);
        $firstResponse->assertStatus(200)
                      ->assertJson(['processed_count' => 1]);

        // Second sync with identical UUID
        $secondResponse = $this->postJson('/api/pos/sync/orders', $payload);
        $secondResponse->assertStatus(200);

        $results = $secondResponse->json('results');
        $this->assertEquals('already_synced', $results[0]['status']);

        // Assert exactly 1 order in DB with this UUID
        $this->assertEquals(1, Order::where('uuid', $orderUuid)->count());
    }

    public function test_pos_sync_clients_creates_new_clients_from_offline_pos(): void
    {
        $payload = [
            'clients' => [
                [
                    'name' => 'Yacine Offline Client',
                    'phone' => '0770998877',
                    'email' => 'yacine@example.com',
                    'address' => 'Alger Centre',
                ]
            ]
        ];

        $response = $this->postJson('/api/pos/sync/clients', $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('clients', [
            'phone' => '0770998877',
            'name' => 'Yacine Offline Client',
        ]);
    }
}
