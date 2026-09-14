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
use Illuminate\Foundation\Testing\RefreshDatabase;

class CarpetMeasurementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Service $service;
    protected GarmentItem $carpetItem;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);

        $target = GarmentTarget::create(['name' => 'Linge de maison']);
        $this->service = Service::create(['name' => 'Blanchisserie', 'code' => 'blanchisserie']);
        $this->carpetItem = GarmentItem::create([
            'name' => 'Tapis 350DAM²',
            'garment_target_id' => $target->id,
            'unit_type' => 'm2'
        ]);

        $this->client = Client::create([
            'code' => '000001',
            'name' => 'Client Test',
            'phone' => '0555000111',
            'discount_percent' => 0,
            'credit' => 0.00
        ]);
    }

    public function test_carpet_can_be_ordered_at_checkout_with_pending_measurement()
    {
        $payload = [
            'client_id' => $this->client->id,
            'ticket_number' => '000100',
            'paid_amount' => 500, // advance deposit
            'target_delivery_date' => now()->addDays(3)->format('Y-m-d'),
            'items' => [
                [
                    'service_id' => $this->service->id,
                    'garment_item_id' => $this->carpetItem->id,
                    'pieces' => 1,
                    'quantity' => 1,
                    'unit_price' => 350.00,
                    'is_measured' => false,
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->postJson('/orders', $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order = Order::where('ticket_number', '000100')->first();
        $this->assertNotNull($order);
        // At checkout, unmeasured carpet total is 0 until measured
        $this->assertEquals(0, $order->total_amount);

        $orderItem = $order->orderItems->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals(1, $orderItem->pieces);
        $this->assertEquals(350.00, $orderItem->unit_price);
        $this->assertEquals(0, $orderItem->total_price);
        $this->assertFalse($orderItem->is_measured);
        $this->assertFalse($orderItem->is_ready);
        $this->assertTrue($orderItem->isCarpet());
    }

    public function test_carpet_dimensions_can_be_updated_and_prices_recalculated()
    {
        $order = Order::create([
            'ticket_number' => '000101',
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'is_paid' => false,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'discount_percent' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => 500.00,
            'balance_amount' => 0,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $this->service->id,
            'garment_item_id' => $this->carpetItem->id,
            'pieces' => 1,
            'quantity' => 1,
            'unit_price' => 350.00,
            'total_price' => 0,
            'is_measured' => false,
            'is_ready' => false,
        ]);

        // Submit measurement: Length = 2.50m, Width = 3.00m (Area = 7.50m²)
        $response = $this->actingAs($this->user)->postJson("/api/order-items/{$orderItem->id}/dimensions", [
            'length' => 2.50,
            'width' => 3.00,
            'unit_price' => 350.00
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'item' => [
                'length' => 2.50,
                'width' => 3.00,
                'area' => 7.50,
                'total_price' => 2625.00,
                'is_measured' => true,
                'is_ready' => true,
            ]
        ]);

        $order->refresh();
        $this->assertEquals(2625.00, $order->total_amount);
        $this->assertEquals(2125.00, $order->balance_amount); // 2625 - 500 = 2125 DA
        $this->assertEquals('ready', $order->status); // All items are ready
    }

    public function test_validation_requires_positive_length_and_width()
    {
        $order = Order::create([
            'ticket_number' => '000102',
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'is_paid' => false,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance_amount' => 0,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $this->service->id,
            'garment_item_id' => $this->carpetItem->id,
            'pieces' => 1,
            'quantity' => 1,
            'unit_price' => 350.00,
            'total_price' => 0,
            'is_measured' => false,
            'is_ready' => false,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/order-items/{$orderItem->id}/dimensions", [
            'length' => -2,
            'width' => 0
        ]);

        $response->assertStatus(422);
    }
}
