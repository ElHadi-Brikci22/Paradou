<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\GarmentItem;
use App\Models\GarmentTarget;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\User;
use App\Http\Controllers\TicketPrintController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_receipt_is_accessible_without_authentication()
    {
        $user = User::factory()->create();
        $client = Client::create([
            'code' => 'CLI-001',
            'name' => 'Karim Benali',
            'phone' => '0555123456',
            'discount_percent' => 0,
            'credit' => 0.00
        ]);

        $order = Order::create([
            'ticket_number' => '000042',
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 500,
            'paid_amount' => 200,
            'balance_amount' => 300,
            'status' => 'pending',
            'payment_status' => 'partial',
            'remarks' => 'Faire attention aux boutons'
        ]);

        $target = GarmentTarget::create(['name' => 'Homme', 'code' => 'homme']);
        $item = GarmentItem::create([
            'name' => 'Costume 2 Pièces',
            'code' => 'costume-2-pcs',
            'pieces_count' => 2,
            'garment_target_id' => $target->id
        ]);
        $service = Service::create(['name' => 'Pressing', 'code' => 'pressing', 'base_price' => 500]);

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'garment_item_id' => $item->id,
            'quantity' => 1,
            'pieces' => 2,
            'unit_price' => 500,
            'total_price' => 500
        ]);

        // Unauthenticated request to /r/000042
        $response = $this->get('/r/000042');

        $response->assertStatus(200);
        $response->assertSee('LE PARADOU');
        $response->assertSee('#000042');
        $response->assertSee('Karim Benali');
        $response->assertSee('Costume 2 Pièces');
        $response->assertSee('(2 pièces)');
        $response->assertSee('500 DA');
        $response->assertSee('300 DA');
        $response->assertSee('Faire attention aux boutons');
        $response->assertSee('Télécharger PDF');
    }

    public function test_public_receipt_returns_404_for_unknown_ticket()
    {
        $response = $this->get('/r/INVALID_TICKET_9999');
        $response->assertStatus(404);
    }

    public function test_public_receipt_pdf_download_without_authentication()
    {
        $user = User::factory()->create();
        $client = Client::create([
            'code' => 'CLI-001',
            'name' => 'Karim Benali',
            'phone' => '0555123456',
            'discount_percent' => 0,
            'credit' => 0.00
        ]);

        $order = Order::create([
            'ticket_number' => '000042',
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 500,
            'paid_amount' => 200,
            'balance_amount' => 300,
            'status' => 'pending',
            'payment_status' => 'partial',
        ]);

        $target = GarmentTarget::create(['name' => 'Homme', 'code' => 'homme']);
        $item = GarmentItem::create([
            'name' => 'Costume 2 Pièces',
            'code' => 'costume-2-pcs',
            'pieces_count' => 2,
            'garment_target_id' => $target->id
        ]);
        $service = Service::create(['name' => 'Pressing', 'code' => 'pressing', 'base_price' => 500]);

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'garment_item_id' => $item->id,
            'quantity' => 1,
            'pieces' => 2,
            'unit_price' => 500,
            'total_price' => 500
        ]);

        // Stream PDF
        $response = $this->get('/r/000042/pdf');
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));

        // Force download PDF
        $downloadResponse = $this->get('/r/000042/pdf?download=1');
        $downloadResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $downloadResponse->headers->get('content-type'));
        $this->assertStringContainsString('attachment', $downloadResponse->headers->get('content-disposition'));
    }

    public function test_printed_ticket_generates_public_receipt_qr_url_and_back_button()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::create([
            'code' => 'CLI-002',
            'name' => 'Sarah Merad',
            'phone' => '0555987654',
            'discount_percent' => 0,
            'credit' => 0.00
        ]);

        $order = Order::create([
            'ticket_number' => '000088',
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 400,
            'paid_amount' => 400,
            'balance_amount' => 0,
            'status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $printResponse = $this->get(route('orders.print-ticket', $order->id));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Retour');
        $printResponse->assertSee('Télécharger PDF');
        $printResponse->assertSee('Scannez pour télécharger le reçu PDF');

        $printAllResponse = $this->get(route('orders.print-all', $order->id));
        $printAllResponse->assertStatus(200);
        $printAllResponse->assertSee('Retour');
        $printAllResponse->assertSee('Télécharger PDF');
        $printAllResponse->assertSee('Scannez pour télécharger le reçu PDF');
    }

    public function test_get_public_receipt_url_replaces_localhost()
    {
        $url = TicketPrintController::getPublicReceiptUrl('000042', true);
        
        $this->assertStringContainsString('/r/000042/pdf', $url);
        // Ensure that if accessed locally, it uses an IP or non-localhost if getHostByName succeeds
        if (getHostByName(getHostName()) !== '127.0.0.1') {
            $this->assertStringNotContainsString('localhost', $url);
        }
    }
}
