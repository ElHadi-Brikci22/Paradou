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

class KiloServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Service $kiloService;
    protected GarmentItem $jeanItem;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);

        $target = GarmentTarget::create(['name' => 'Homme', 'code' => 'homme']);
        $this->kiloService = Service::create([
            'name' => 'Au Kilo', 
            'code' => 'au-kilo',
            'price' => 250.00,
            'wholesale_price' => 200.00
        ]);
        $this->jeanItem = GarmentItem::create([
            'name' => 'Jean',
            'code' => 'jean',
            'garment_target_id' => $target->id,
            'standard_weight' => 500.00,
        ]);

        $this->client = Client::create([
            'code' => '000001',
            'name' => 'Test Client',
            'discount_percent' => 0,
            'credit' => 0.00,
        ]);
    }

    public function test_garment_item_standard_weight_accessor(): void
    {
        $this->assertEquals(500.00, $this->jeanItem->standard_weight);
        $this->assertEquals(0.50, $this->jeanItem->standard_weight_kg);

        $emptyItem = GarmentItem::create([
            'name' => 'T-Shirt',
            'code' => 'tshirt',
            'garment_target_id' => $this->jeanItem->garment_target_id,
            'standard_weight' => null,
        ]);
        $this->assertEquals(0, $emptyItem->standard_weight_kg);
    }

    public function test_update_uniform_service_price_for_au_kilo(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/admin/prices/service-price', [
            'service_id' => $this->kiloService->id,
            'price' => 300.00,
            'wholesale_price' => 260.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->kiloService->refresh();
        $this->assertEquals(300.00, (float) $this->kiloService->price);
        $this->assertEquals(260.00, (float) $this->kiloService->wholesale_price);

        // Verify that Au Kilo items automatically receive this uniform price in service_prices
        $this->assertDatabaseHas('service_prices', [
            'garment_item_id' => $this->jeanItem->id,
            'service_id' => $this->kiloService->id,
            'price' => 300.00,
            'wholesale_price' => 260.00,
        ]);
    }

    public function test_save_kilo_item_weight_only_without_individual_price(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/admin/prices/update', [
            'garment_item_id' => $this->jeanItem->id,
            'service_id' => $this->kiloService->id,
            'standard_weight' => 750.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->jeanItem->refresh();
        $this->assertEquals(750.00, (float) $this->jeanItem->standard_weight);
        $this->assertEquals(0.75, (float) $this->jeanItem->standard_weight_kg);
    }

    public function test_price_controller_saves_standard_weight_and_prices(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/admin/prices/update', [
            'garment_item_id' => $this->jeanItem->id,
            'service_id' => $this->kiloService->id,
            'price' => 250.00,
            'wholesale_price' => 220.00,
            'standard_weight' => 600.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->jeanItem->refresh();
        $this->assertEquals(600.00, $this->jeanItem->standard_weight);
        $this->assertEquals(0.60, $this->jeanItem->standard_weight_kg);

        $this->assertDatabaseHas('service_prices', [
            'garment_item_id' => $this->jeanItem->id,
            'service_id' => $this->kiloService->id,
            'price' => 250.00,
            'wholesale_price' => 220.00,
        ]);
    }

    public function test_create_order_with_kilo_service_stores_pieces_weight_and_total_weight(): void
    {
        ServicePrice::create([
            'garment_item_id' => $this->jeanItem->id,
            'service_id' => $this->kiloService->id,
            'price' => 300.00, // 300 DA / kg
            'wholesale_price' => 280.00,
        ]);

        $orderPayload = [
            'client_id' => $this->client->id,
            'target_delivery_date' => now()->addDays(2)->format('Y-m-d'),
            'paid_amount' => 450.00,
            'total_weight' => 1.500, // 3 jeans = 1.5 kg
            'items' => [
                [
                    'garment_item_id' => $this->jeanItem->id,
                    'service_id' => $this->kiloService->id,
                    'pieces' => 3,
                    'weight' => 1.500,
                    'quantity' => 1.500, // Weight in kg
                    'unit_price' => 300.00,
                    'notes' => 'Au kilo test item',
                ]
            ],
            'subtotal' => 450.00,
            'discount_amount' => 0.00,
            'total_amount' => 450.00,
        ];

        $response = $this->actingAs($this->adminUser)->postJson('/orders', $orderPayload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(1.500, (float) $order->total_weight);
        $this->assertEquals(450.00, (float) $order->total_amount);

        $orderItem = $order->orderItems()->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals(3, $orderItem->pieces);
        $this->assertEquals(1.500, (float) $orderItem->weight);
        $this->assertEquals(1.500, (float) $orderItem->quantity);
        $this->assertEquals(300.00, (float) $orderItem->unit_price);
        $this->assertEquals(450.00, (float) $orderItem->total_price);
    }

    public function test_update_order_with_kilo_service_updates_pieces_weight_and_total_weight(): void
    {
        $order = Order::create([
            'ticket_number' => '000888',
            'client_id' => $this->client->id,
            'user_id' => $this->adminUser->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'subtotal' => 300.00,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'total_amount' => 300.00,
            'paid_amount' => 300.00,
            'balance_amount' => 0.00,
            'status' => 'pending',
            'is_paid' => true,
            'total_weight' => 1.000,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'garment_item_id' => $this->jeanItem->id,
            'service_id' => $this->kiloService->id,
            'pieces' => 2,
            'weight' => 1.000,
            'quantity' => 1.000,
            'unit_price' => 300.00,
            'total_price' => 300.00,
        ]);

        $updatePayload = [
            'client_id' => $this->client->id,
            'target_delivery_date' => now()->addDays(3)->format('Y-m-d'),
            'paid_amount' => 600.00,
            'total_weight' => 2.000,
            'items' => [
                [
                    'id' => $orderItem->id,
                    'garment_item_id' => $this->jeanItem->id,
                    'service_id' => $this->kiloService->id,
                    'pieces' => 4,
                    'weight' => 2.000,
                    'quantity' => 2.000,
                    'unit_price' => 300.00,
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)->postJson("/orders/{$order->id}/update", $updatePayload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(2.000, (float) $order->total_weight);
        $this->assertEquals(600.00, (float) $order->total_amount);

        $item = $order->orderItems()->first();
        $this->assertEquals(4, $item->pieces);
        $this->assertEquals(2.000, (float) $item->weight);
        $this->assertEquals(2.000, (float) $item->quantity);
    }

    public function test_ticket_view_displays_kilo_info(): void
    {
        $order = Order::create([
            'ticket_number' => 'T-000999',
            'client_id' => $this->client->id,
            'user_id' => $this->adminUser->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'subtotal' => 300.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total_amount' => 300.00,
            'paid_amount' => 300.00,
            'status' => 'pending',
            'payment_status' => 'paid',
            'total_weight' => 1.000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'garment_item_id' => $this->jeanItem->id,
            'service_id' => $this->kiloService->id,
            'pieces' => 2,
            'weight' => 1.000,
            'quantity' => 1.000,
            'unit_price' => 300.00,
            'total_price' => 300.00,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/orders/{$order->id}/print-ticket");
        $response->assertStatus(200);
        $response->assertSee('1.00 kg');
        $response->assertSee('2 pcs');
        $response->assertSee('Poids total (Au Kilo)');
    }
}
