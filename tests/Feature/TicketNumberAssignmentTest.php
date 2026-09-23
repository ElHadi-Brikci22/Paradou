<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\GarmentItem;
use App\Models\GarmentTarget;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketNumberAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_number_is_assigned_at_validation_sequentially()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $client = Client::create([
            'code' => 'CLI-TEST',
            'name' => 'Client Test',
            'phone' => '0555000000',
            'discount_percent' => 0,
            'credit' => 0.00
        ]);

        $target = GarmentTarget::create(['name' => 'Homme', 'code' => 'homme']);
        $service = Service::create(['name' => 'Pressing', 'code' => 'pressing', 'base_price' => 200]);
        $item = GarmentItem::create([
            'name' => 'Chemise',
            'code' => 'chemise',
            'garment_target_id' => $target->id
        ]);
        ServicePrice::create([
            'service_id' => $service->id,
            'garment_item_id' => $item->id,
            'price' => 250
        ]);

        // 1. Check checkout index view does not display preassigned ticket number
        $viewResponse = $this->get(route('checkout.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('À la validation');

        // 2. Submit Order 1 without ticket_number
        $payload1 = [
            'client_id' => $client->id,
            'ticket_number' => null, // not specified
            'discount_type' => 'fixed',
            'discount_amount' => 0,
            'paid_amount' => 250,
            'target_delivery_date' => now()->addDays(2)->toDateString(),
            'remarks' => '',
            'items' => [
                [
                    'service_id' => $service->id,
                    'garment_item_id' => $item->id,
                    'quantity' => 1,
                    'unit_price' => 250,
                ]
            ]
        ];

        $resp1 = $this->postJson(route('orders.store'), $payload1);
        $resp1->assertStatus(200);
        $resp1->assertJson(['success' => true]);
        $ticket1 = $resp1->json('ticket_number');

        // 3. Submit Order 2 without ticket_number
        $payload2 = $payload1;
        $resp2 = $this->postJson(route('orders.store'), $payload2);
        $resp2->assertStatus(200);
        $resp2->assertJson(['success' => true]);
        $ticket2 = $resp2->json('ticket_number');

        // Verify sequential assignment
        $this->assertNotEmpty($ticket1);
        $this->assertNotEmpty($ticket2);
        $this->assertEquals(intval($ticket1) + 1, intval($ticket2));

        // 4. Verify orders in database
        $this->assertDatabaseHas('orders', ['ticket_number' => $ticket1]);
        $this->assertDatabaseHas('orders', ['ticket_number' => $ticket2]);
    }
}
