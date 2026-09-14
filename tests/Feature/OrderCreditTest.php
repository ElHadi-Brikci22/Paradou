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

class OrderCreditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Service $service;
    protected GarmentItem $garmentItem;
    protected Client $guestClient;
    protected Client $regularClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'cashier']);

        $target = GarmentTarget::create(['name' => 'Homme', 'code' => 'homme']);
        $this->service = Service::create(['name' => 'Nettoyage', 'code' => 'nettoyage', 'base_price' => 500]);
        $this->garmentItem = GarmentItem::create([
            'name' => 'Costume',
            'code' => 'costume',
            'garment_target_id' => $target->id
        ]);

        $this->guestClient = Client::create([
            'code' => 'GUEST',
            'name' => 'Client Passage',
            'discount_percent' => 0,
            'credit' => 0.00
        ]);

        $this->regularClient = Client::create([
            'code' => '000002',
            'name' => 'Mohamed Benali',
            'phone' => '0555123456',
            'discount_percent' => 0,
            'credit' => 0.00
        ]);
    }

    private function createOrderFor(Client $client, float $total, float $paid, string $status = 'pending'): Order
    {
        $order = Order::create([
            'ticket_number' => str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'client_id' => $client->id,
            'user_id' => $this->user->id,
            'status' => $status,
            'is_paid' => ($total - $paid) <= 0,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'actual_delivery_date' => $status === 'delivered' ? now() : null,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'balance_amount' => max(0, $total - $paid),
            'is_express' => false,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $this->service->id,
            'garment_item_id' => $this->garmentItem->id,
            'quantity' => 1,
            'unit_price' => $total,
            'total_price' => $total,
            'is_ready' => true,
            'is_delivered' => $status === 'delivered',
            'delivered_at' => $status === 'delivered' ? now() : null,
        ]);

        return $order;
    }

    /**
     * Test that guest client cannot receive delivery on credit (partial or zero payment).
     */
    public function test_guest_client_cannot_take_delivery_on_credit(): void
    {
        $order = $this->createOrderFor($this->guestClient, 1000.00, 200.00); // 800 DA remaining

        // 1. Attempt to deliver with 0 DA payment
        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/deliver", [
                'cash_collected' => 0,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString("Le client passager n'est pas autorisé au crédit", $response->json('message'));

        // 2. Attempt to deliver with partial payment (500 DA, leaving 300 DA)
        $responsePartial = $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/deliver", [
                'cash_collected' => 500,
            ]);

        $responsePartial->assertStatus(422);

        // 3. Deliver with full remaining balance (800 DA)
        $responseFull = $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/deliver", [
                'cash_collected' => 800,
            ]);

        $responseFull->assertStatus(200);
        $responseFull->assertJson([
            'success' => true,
            'is_all_delivered' => true,
        ]);

        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertEquals(0, $order->balance_amount);
        $this->assertTrue($order->is_paid);
    }

    /**
     * Test that regular client is allowed to receive delivery on credit.
     */
    public function test_regular_client_can_take_delivery_on_credit(): void
    {
        $order = $this->createOrderFor($this->regularClient, 1000.00, 0.00);

        // Deliver with 0 payment (credit)
        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/deliver", [
                'cash_collected' => 0,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_all_delivered' => true,
        ]);

        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertEquals(1000.00, $order->balance_amount);
        $this->assertFalse($order->is_paid);
        $this->assertTrue($order->isCredit());
    }

    /**
     * Test that credit filter displays only delivered or partially delivered orders with balance > 0.
     */
    public function test_credit_filter_returns_only_unpaid_delivered_orders(): void
    {
        // Order A: Delivered, Unpaid (Credit order) -> SHOULD APPEAR
        $creditOrder = $this->createOrderFor($this->regularClient, 1200.00, 200.00, 'delivered');

        // Order B: Delivered, Fully Paid -> SHOULD NOT APPEAR
        $paidOrder = $this->createOrderFor($this->regularClient, 800.00, 800.00, 'delivered');

        // Order C: Pending, Unpaid -> SHOULD NOT APPEAR
        $pendingOrder = $this->createOrderFor($this->regularClient, 1500.00, 0.00, 'pending');

        $response = $this->actingAs($this->user)->get('/orders?status=credit');

        $response->assertStatus(200);
        $response->assertSee('#' . $creditOrder->ticket_number);
        $response->assertDontSee('#' . $paidOrder->ticket_number);
        $response->assertDontSee('#' . $pendingOrder->ticket_number);
    }

    /**
     * Test that settling credit updates balance and removes order from credit filter.
     */
    public function test_settle_credit_allows_cashing_balance_and_clearing_debt(): void
    {
        $creditOrder = $this->createOrderFor($this->regularClient, 1000.00, 200.00, 'delivered'); // 800 DA balance

        // Settle partial payment (300 DA)
        $partialResponse = $this->actingAs($this->user)
            ->postJson("/api/orders/{$creditOrder->id}/settle-credit", [
                'cash_collected' => 300,
            ]);

        $partialResponse->assertStatus(200);
        $creditOrder->refresh();
        $this->assertEquals(500.00, $creditOrder->balance_amount);
        $this->assertFalse($creditOrder->is_paid);

        // Settle remaining 500 DA
        $fullResponse = $this->actingAs($this->user)
            ->postJson("/api/orders/{$creditOrder->id}/settle-credit", [
                'cash_collected' => 500,
            ]);

        $fullResponse->assertStatus(200);
        $creditOrder->refresh();
        $this->assertEquals(0, $creditOrder->balance_amount);
        $this->assertTrue($creditOrder->is_paid);

        // Order should no longer appear in credit filter
        $listResponse = $this->actingAs($this->user)->get('/orders?status=credit');
        $listResponse->assertDontSee('#' . $creditOrder->ticket_number);
    }

    /**
     * Test that admin dashboard displays credit stats in numbers and in details.
     */
    public function test_admin_dashboard_displays_credit_statistics_and_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creditOrder = $this->createOrderFor($this->regularClient, 2500.00, 500.00, 'delivered'); // 2000 DA remaining

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        // Chiffres clés
        $response->assertSee('Commandes Livrées à Crédit (Créances Clients)');
        $response->assertSee('2 000 DA');
        // Détails
        $response->assertSee('#' . $creditOrder->ticket_number);
        $response->assertSee($this->regularClient->name);
    }
}
