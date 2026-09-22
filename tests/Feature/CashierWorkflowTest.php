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

class CashierWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Client $regularClient;
    protected Client $guestClient;
    protected Service $pressing;
    protected Service $kiloService;
    protected GarmentItem $shirt;
    protected GarmentItem $kiloBundle;
    protected GarmentItem $carpet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create(['role' => 'cashier']);

        $target = GarmentTarget::create(['name' => 'Adulte', 'code' => 'adulte']);

        $this->pressing = Service::create(['name' => 'Pressing', 'code' => 'pressing', 'base_price' => 200]);
        $this->kiloService = Service::create([
            'name' => 'Au Kilo',
            'code' => 'au_kilo',
            'base_price' => 300,
            'price' => 300
        ]);

        $this->shirt = GarmentItem::create([
            'name' => 'Chemise',
            'code' => 'chemise',
            'garment_target_id' => $target->id
        ]);
        ServicePrice::create([
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->shirt->id,
            'price' => 250
        ]);

        $this->kiloBundle = GarmentItem::create([
            'name' => 'Linge Kilo',
            'code' => 'linge_kilo',
            'garment_target_id' => $target->id,
            'standard_weight' => 2.5
        ]);

        $this->carpet = GarmentItem::create([
            'name' => 'Tapis Salon',
            'code' => 'tapis_salon',
            'garment_target_id' => $target->id,
            'unit_type' => 'm2'
        ]);
        ServicePrice::create([
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->carpet->id,
            'price' => 500
        ]);

        $this->regularClient = Client::create([
            'code' => '000100',
            'name' => 'Sofiane Larbi',
            'phone' => '0555112233'
        ]);

        $this->guestClient = Client::create([
            'code' => 'GUEST',
            'name' => 'Client Passager',
            'phone' => null
        ]);
    }

    public function test_cashier_can_access_checkout_and_orders_pages(): void
    {
        $responseCheckout = $this->actingAs($this->cashier)->get(route('checkout.index'));
        $responseCheckout->assertStatus(200);

        $responseOrders = $this->actingAs($this->cashier)->get(route('orders.index'));
        $responseOrders->assertStatus(200);
    }

    public function test_cashier_is_forbidden_from_admin_management_routes(): void
    {
        $this->actingAs($this->cashier)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($this->cashier)->get('/admin/users')->assertStatus(403);
        $this->actingAs($this->cashier)->get('/admin/clients')->assertStatus(403);
        $this->actingAs($this->cashier)->get('/admin/catalog')->assertStatus(403);
        $this->actingAs($this->cashier)->get('/admin/prices')->assertStatus(403);
        $this->actingAs($this->cashier)->get('/admin/rubrics')->assertStatus(403);
    }

    public function test_cashier_can_search_and_create_client_via_api(): void
    {
        $searchResponse = $this->actingAs($this->cashier)->getJson('/api/clients/search?query=Sofiane');
        $searchResponse->assertStatus(200);
        $searchResponse->assertJsonFragment(['name' => 'Sofiane Larbi']);

        $createResponse = $this->actingAs($this->cashier)->postJson('/api/clients', [
            'name' => 'Nouveau Client Test',
            'phone' => '0777889900',
            'address' => 'Alger Centre'
        ]);
        $createResponse->assertStatus(200);
        $this->assertDatabaseHas('clients', ['name' => 'Nouveau Client Test']);
    }

    public function test_cashier_can_create_order_with_standard_kilo_and_carpet_items(): void
    {
        $orderData = [
            'client_id' => $this->regularClient->id,
            'ticket_number' => '109999',
            'target_delivery_date' => now()->addDays(3)->format('Y-m-d'),
            'paid_amount' => 500,
            'discount_type' => 'fixed',
            'discount_amount' => 50,
            'is_express' => false,
            'items' => [
                [
                    'service_id' => $this->pressing->id,
                    'garment_item_id' => $this->shirt->id,
                    'quantity' => 2,
                    'unit_price' => 250,
                    'colors' => ['bleu'],
                    'defects' => [],
                    'stains' => []
                ],
                [
                    'service_id' => $this->kiloService->id,
                    'garment_item_id' => $this->kiloBundle->id,
                    'quantity' => 3.5, // 3.5 kg
                    'pieces' => 5,
                    'unit_price' => 300,
                    'colors' => [],
                    'defects' => [],
                    'stains' => []
                ],
                [
                    'service_id' => $this->pressing->id,
                    'garment_item_id' => $this->carpet->id,
                    'quantity' => 1,
                    'pieces' => 1,
                    'unit_price' => 500,
                    'is_measured' => false,
                    'area' => null,
                    'colors' => [],
                    'defects' => [],
                    'stains' => []
                ]
            ]
        ];

        $response = $this->actingAs($this->cashier)->postJson('/orders', $orderData);
        $response->assertStatus(200);

        $order = Order::where('ticket_number', '109999')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->orderItems()->count());
        $this->assertEquals(500, $order->paid_amount);

        // Verify print views work without error and ticket contains QR code & ticket number
        $ticketResponse = $this->actingAs($this->cashier)->get(route('orders.print-ticket', $order->id));
        $ticketResponse->assertStatus(200);
        $ticketResponse->assertSee($order->ticket_number);
        $ticketResponse->assertSee('qrcode-container');
        $ticketResponse->assertSee('<svg', false);

        $this->actingAs($this->cashier)->get(route('orders.print-tags', $order->id))->assertStatus(200);
        
        $allResponse = $this->actingAs($this->cashier)->get(route('orders.print-all', $order->id));
        $allResponse->assertStatus(200);
        $allResponse->assertSee('qrcode-container');
        $allResponse->assertSee('<svg', false);
    }

    public function test_cashier_cannot_apply_percentage_discount(): void
    {
        $orderData = [
            'client_id' => $this->regularClient->id,
            'ticket_number' => '109998',
            'target_delivery_date' => now()->addDays(2)->format('Y-m-d'),
            'paid_amount' => 200,
            'discount_type' => 'percent',
            'discount_percent' => 10,
            'items' => [
                [
                    'service_id' => $this->pressing->id,
                    'garment_item_id' => $this->shirt->id,
                    'quantity' => 1,
                    'unit_price' => 250
                ]
            ]
        ];

        $response = $this->actingAs($this->cashier)->postJson('/orders', $orderData);
        $response->assertStatus(422);
    }

    public function test_cashier_can_measure_carpet_item_and_recalculate_order(): void
    {
        $order = Order::create([
            'ticket_number' => '109997',
            'client_id' => $this->regularClient->id,
            'user_id' => $this->cashier->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance_amount' => 0,
            'status' => 'pending'
        ]);

        $carpetItem = OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->carpet->id,
            'quantity' => 1,
            'pieces' => 1,
            'unit_price' => 500, // 500 DA/m²
            'total_price' => 0,
            'is_measured' => false,
            'is_ready' => false,
            'is_delivered' => false
        ]);

        // Measure carpet: 3m x 2m = 6 m² @ 500 DA = 3000 DA
        $response = $this->actingAs($this->cashier)->postJson("/api/order-items/{$carpetItem->id}/dimensions", [
            'length' => 3.0,
            'width' => 2.0,
            'area' => 6.0,
            'unit_price' => 500
        ]);

        $response->assertStatus(200);

        $carpetItem->refresh();
        $this->assertTrue($carpetItem->is_measured);
        $this->assertTrue($carpetItem->is_ready);
        $this->assertEquals(6.0, $carpetItem->area);
        $this->assertEquals(3000, $carpetItem->total_price);

        $order->refresh();
        $this->assertEquals(3000, $order->total_amount);
        $this->assertEquals(3000, $order->balance_amount);
    }

    public function test_cashier_can_deliver_items_partially_and_settle_credit(): void
    {
        $order = Order::create([
            'ticket_number' => '109996',
            'client_id' => $this->regularClient->id,
            'user_id' => $this->cashier->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 1000,
            'paid_amount' => 200,
            'balance_amount' => 800,
            'status' => 'pending'
        ]);

        $item1 = OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->shirt->id,
            'quantity' => 2,
            'unit_price' => 250,
            'total_price' => 500,
            'is_ready' => true,
            'is_delivered' => false
        ]);

        $item2 = OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->shirt->id,
            'quantity' => 2,
            'unit_price' => 250,
            'total_price' => 500,
            'is_ready' => false,
            'is_delivered' => false
        ]);

        // Partial delivery: deliver item1, collect 300 DA
        $response = $this->actingAs($this->cashier)->postJson("/api/orders/{$order->id}/deliver", [
            'item_ids' => [$item1->id],
            'cash_collected' => 300
        ]);
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('partially_delivered', $order->status);
        $this->assertEquals(500, $order->paid_amount); // 200 + 300
        $this->assertEquals(500, $order->balance_amount); // 1000 - 500

        // Deliver item2, total delivery, client leaves 200 DA in credit
        $item2->update(['is_ready' => true]);
        $responseAll = $this->actingAs($this->cashier)->postJson("/api/orders/{$order->id}/deliver", [
            'item_ids' => [$item2->id],
            'cash_collected' => 300
        ]);
        $responseAll->assertStatus(200);

        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertEquals(800, $order->paid_amount);
        $this->assertEquals(200, $order->balance_amount);

        // Later: Settle the remaining 200 DA credit
        $settleResponse = $this->actingAs($this->cashier)->postJson("/api/orders/{$order->id}/settle-credit", [
            'cash_collected' => 200
        ]);
        $settleResponse->assertStatus(200);

        $order->refresh();
        $this->assertEquals(1000, $order->paid_amount);
        $this->assertEquals(0, $order->balance_amount);
    }

    public function test_guest_client_strictly_blocked_from_credit_delivery(): void
    {
        $guestOrder = Order::create([
            'ticket_number' => '109995',
            'client_id' => $this->guestClient->id,
            'user_id' => $this->cashier->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(1),
            'total_amount' => 500,
            'paid_amount' => 100,
            'balance_amount' => 400,
            'status' => 'pending'
        ]);

        $item = OrderItem::create([
            'order_id' => $guestOrder->id,
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->shirt->id,
            'quantity' => 2,
            'unit_price' => 250,
            'total_price' => 500,
            'is_ready' => true,
            'is_delivered' => false
        ]);

        // Attempting to deliver with less than full balance should fail with 422
        $response = $this->actingAs($this->cashier)->postJson("/api/orders/{$guestOrder->id}/deliver", [
            'item_ids' => [$item->id],
            'cash_collected' => 200 // Balance is 400!
        ]);
        $response->assertStatus(422);

        // Delivering with full balance succeeds
        $responseSuccess = $this->actingAs($this->cashier)->postJson("/api/orders/{$guestOrder->id}/deliver", [
            'item_ids' => [$item->id],
            'cash_collected' => 400
        ]);
        $responseSuccess->assertStatus(200);
    }
}
