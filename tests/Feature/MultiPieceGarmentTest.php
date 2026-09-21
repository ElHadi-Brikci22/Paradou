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

class MultiPieceGarmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Client $client;
    protected Service $pressing;
    protected GarmentItem $suit3Piece;
    protected GarmentItem $suit2Piece;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create(['role' => 'cashier']);
        $this->client = Client::create(['name' => 'Walid Client', 'phone' => '0555123456', 'code' => '000001']);

        $target = GarmentTarget::create(['name' => 'Homme', 'sort_order' => 1]);

        $this->pressing = Service::create([
            'name' => 'Nettoyage à sec',
            'code' => 'pressing',
            'price' => 300,
            'sort_order' => 1
        ]);

        $this->suit3Piece = GarmentItem::create([
            'name' => 'Costume 3 pièces',
            'garment_target_id' => $target->id,
            'pieces_count' => 3,
            'unit_type' => 'piece'
        ]);

        $this->suit2Piece = GarmentItem::create([
            'name' => 'Costume 2 pièces',
            'garment_target_id' => $target->id,
            'pieces_count' => 2,
            'unit_type' => 'piece'
        ]);

        ServicePrice::create([
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->suit3Piece->id,
            'price' => 600.00
        ]);

        ServicePrice::create([
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->suit2Piece->id,
            'price' => 450.00
        ]);
    }

    public function test_can_order_multi_piece_garments_and_pieces_count_is_stored(): void
    {
        $payload = [
            'client_id' => $this->client->id,
            'ticket_number' => '000555',
            'paid_amount' => 1050,
            'discount_type' => 'fixed',
            'discount_percent' => 0,
            'discount_amount' => 0,
            'target_delivery_date' => now()->addDays(2)->format('Y-m-d'),
            'remarks' => '',
            'is_express' => false,
            'items' => [
                [
                    'service_id' => $this->pressing->id,
                    'garment_item_id' => $this->suit3Piece->id,
                    'quantity' => 1,
                    'unit_price' => 600.00,
                    'colors' => ['Noir'],
                    'defects' => [],
                    'stains' => [],
                    'notes' => null
                ],
                [
                    'service_id' => $this->pressing->id,
                    'garment_item_id' => $this->suit2Piece->id,
                    'quantity' => 1,
                    'unit_price' => 450.00,
                    'colors' => ['Bleu marine'],
                    'defects' => [],
                    'stains' => [],
                    'notes' => null
                ]
            ]
        ];

        $response = $this->actingAs($this->cashier)->postJson('/orders', $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order = Order::where('ticket_number', '000555')->first();
        $this->assertNotNull($order);
        $this->assertCount(2, $order->orderItems);

        $item3 = $order->orderItems->where('garment_item_id', $this->suit3Piece->id)->first();
        $item2 = $order->orderItems->where('garment_item_id', $this->suit2Piece->id)->first();

        $this->assertEquals(3, $item3->pieces);
        $this->assertEquals(2, $item2->pieces);
    }

    public function test_customer_receipt_shows_single_lines_with_pieces_badges(): void
    {
        $order = Order::create([
            'ticket_number' => '000777',
            'client_id' => $this->client->id,
            'user_id' => $this->cashier->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 1050,
            'paid_amount' => 1050,
            'balance_amount' => 0,
            'status' => 'pending',
            'is_express' => false,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->suit3Piece->id,
            'quantity' => 1,
            'pieces' => 3,
            'unit_price' => 600,
            'total_price' => 600
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->suit2Piece->id,
            'quantity' => 1,
            'pieces' => 2,
            'unit_price' => 450,
            'total_price' => 450
        ]);

        $response = $this->actingAs($this->cashier)->get("/orders/{$order->id}/print-ticket");
        $response->assertStatus(200);
        $response->assertSee('Costume 3 pièces');
        $response->assertSee('[3 pièces]');
        $response->assertSee('Costume 2 pièces');
        $response->assertSee('[2 pièces]');
        $response->assertSee('TOTAL ARTICLES DÉPOSÉS');
        $response->assertSee('5 pièces');
    }

    public function test_tag_printing_generates_one_tag_per_piece_with_composite_info(): void
    {
        $order = Order::create([
            'ticket_number' => '000888',
            'client_id' => $this->client->id,
            'user_id' => $this->cashier->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 600,
            'paid_amount' => 600,
            'balance_amount' => 0,
            'status' => 'pending',
            'is_express' => false,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $this->pressing->id,
            'garment_item_id' => $this->suit3Piece->id,
            'quantity' => 1,
            'pieces' => 3,
            'unit_price' => 600,
            'total_price' => 600
        ]);

        $response = $this->actingAs($this->cashier)->get("/orders/{$order->id}/print-tags");
        $response->assertStatus(200);

        // Should have 3 tags for the 3 pieces
        $response->assertSee('PIÈCE 1 / 3');
        $response->assertSee('PIÈCE 2 / 3');
        $response->assertSee('PIÈCE 3 / 3');
        $response->assertSee('Nombre de pièces = 3 (Article composé)');
        // Does NOT mention piece names
        $response->assertDontSee('Veste');
        $response->assertDontSee('Pantalon');
    }
}
