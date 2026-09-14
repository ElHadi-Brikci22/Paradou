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
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected GarmentTarget $target;
    protected Service $service;
    protected GarmentItem $item;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@dryplus.test']);
        $this->cashier = User::factory()->create(['role' => 'cashier', 'email' => 'caisse@dryplus.test']);

        $this->target = GarmentTarget::create(['name' => 'Homme', 'code' => 'homme']);
        $this->service = Service::create(['name' => 'Nettoyage', 'code' => 'nettoyage', 'base_price' => 400]);
        $this->item = GarmentItem::create([
            'name' => 'Veste',
            'code' => 'veste',
            'garment_target_id' => $this->target->id,
            'standard_weight' => 1.2
        ]);
        ServicePrice::create([
            'service_id' => $this->service->id,
            'garment_item_id' => $this->item->id,
            'price' => 450
        ]);

        $this->client = Client::create([
            'code' => '000050',
            'name' => 'Karim Benali',
            'phone' => '0560123456'
        ]);
    }

    public function test_admin_can_access_dashboard_with_all_filter_ranges(): void
    {
        // Seed an order and an expense for metrics
        $order = Order::create([
            'ticket_number' => '200001',
            'client_id' => $this->client->id,
            'user_id' => $this->admin->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 900,
            'paid_amount' => 500,
            'balance_amount' => 400,
            'status' => 'pending'
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $this->service->id,
            'garment_item_id' => $this->item->id,
            'quantity' => 2,
            'unit_price' => 450,
            'total_price' => 900
        ]);

        Expense::create([
            'user_id' => $this->admin->id,
            'title' => 'Achat Détergent',
            'amount' => 150,
            'expense_date' => now(),
            'category' => 'Produits'
        ]);

        $ranges = ['today', 'week', 'month', 'year', 'all'];

        foreach ($ranges as $range) {
            $response = $this->actingAs($this->admin)->get(route('admin.dashboard', ['range' => $range]));
            $response->assertStatus(200);
            $response->assertViewHasAll([
                'totalNetCA',
                'totalCollected',
                'totalBalance',
                'totalExpenses',
                'netProfit',
                'serviceLabels',
                'serviceRevenues'
            ]);
        }
    }

    public function test_admin_can_manage_users_crud(): void
    {
        // 1. List users
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('admin@dryplus.test');

        // 2. Create new user
        $createResponse = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Nouveau Caissier',
            'email' => 'newcaisse@dryplus.test',
            'password' => 'secret123',
            'role' => 'cashier'
        ]);
        $createResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'newcaisse@dryplus.test']);

        $newUser = User::where('email', 'newcaisse@dryplus.test')->first();

        // 3. Update user
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.users.update', $newUser->id), [
            'name' => 'Caissier Modifié',
            'email' => 'newcaisse@dryplus.test',
            'role' => 'cashier'
        ]);
        $updateResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['name' => 'Caissier Modifié']);

        // 4. Admin cannot delete own account
        $deleteSelfResponse = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin->id));
        $deleteSelfResponse->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        // 5. Admin can delete other user
        $deleteOtherResponse = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $newUser->id));
        $deleteOtherResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $newUser->id]);
    }

    public function test_admin_can_manage_clients_crud(): void
    {
        // 1. List & search
        $response = $this->actingAs($this->admin)->get(route('admin.clients.index', ['search' => 'Karim']));
        $response->assertStatus(200);
        $response->assertSee('Karim Benali');

        // 2. Create client
        $createResponse = $this->actingAs($this->admin)->post(route('admin.clients.store'), [
            'name' => 'Amina Mansouri',
            'phone' => '0661998877',
            'email' => 'amina@test.com',
            'address' => 'Bab Ezzouar',
            'discount_percent' => 5
        ]);
        $createResponse->assertRedirect(route('admin.clients.index'));
        $this->assertDatabaseHas('clients', ['name' => 'Amina Mansouri']);

        $created = Client::where('name', 'Amina Mansouri')->first();

        // 3. Duplicate phone should be rejected
        $duplicatePhoneResponse = $this->actingAs($this->admin)->post(route('admin.clients.store'), [
            'name' => 'Autre Client',
            'phone' => '0661998877'
        ]);
        $duplicatePhoneResponse->assertSessionHasErrors('phone');

        // 4. Update client
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.clients.update', $created->id), [
            'name' => 'Amina Mansouri Updated',
            'phone' => '0661998877',
            'discount_percent' => 10
        ]);
        $updateResponse->assertRedirect(route('admin.clients.index'));
        $this->assertDatabaseHas('clients', ['name' => 'Amina Mansouri Updated', 'discount_percent' => 10]);
    }

    public function test_admin_can_manage_catalog(): void
    {
        // 1. List catalog
        $response = $this->actingAs($this->admin)->get(route('admin.catalog.index'));
        $response->assertStatus(200);

        // 2. Create target (Rayon)
        $targetResponse = $this->actingAs($this->admin)->post(route('admin.catalog.target.store'), [
            'name' => 'Enfant',
            'code' => 'enfant'
        ]);
        $targetResponse->assertRedirect(route('admin.catalog.index', ['tab' => 'targets']));
        $this->assertDatabaseHas('garment_targets', ['name' => 'Enfant']);

        $target = GarmentTarget::where('name', 'Enfant')->first();

        // 3. Create service
        $serviceResponse = $this->actingAs($this->admin)->post(route('admin.catalog.service.store'), [
            'name' => 'Teinture Cuir',
            'code' => 'teinture_cuir',
            'base_price' => 800
        ]);
        $serviceResponse->assertRedirect(route('admin.catalog.index', ['tab' => 'services']));
        $this->assertDatabaseHas('services', ['name' => 'Teinture Cuir']);

        $service = Service::where('code', 'teinture_cuir')->first();

        // 4. Create garment item with pricing
        $itemResponse = $this->actingAs($this->admin)->post(route('admin.catalog.item.store'), [
            'name' => 'Blouson Cuir',
            'garment_target_id' => $target->id,
            'standard_weight' => 1.8,
            'prices' => [
                $service->id => 1200
            ]
        ]);
        $itemResponse->assertRedirect(route('admin.catalog.index'));
        $this->assertDatabaseHas('garment_items', ['name' => 'Blouson Cuir']);

        $item = GarmentItem::where('name', 'Blouson Cuir')->first();
        $this->assertDatabaseHas('service_prices', [
            'garment_item_id' => $item->id,
            'service_id' => $service->id,
            'price' => 1200
        ]);

        // 5. Update garment item
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.catalog.item.update', $item->id), [
            'name' => 'Blouson Cuir Premium',
            'garment_target_id' => $target->id,
            'standard_weight' => 2.0,
            'prices' => [
                $service->id => 1500
            ]
        ]);
        $updateResponse->assertRedirect(route('admin.catalog.index'));
        $this->assertDatabaseHas('garment_items', ['name' => 'Blouson Cuir Premium']);

        // 6. Delete garment item
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.catalog.item.destroy', $item->id));
        $deleteResponse->assertRedirect(route('admin.catalog.index'));
        $this->assertDatabaseMissing('garment_items', ['id' => $item->id]);
    }

    public function test_admin_can_manage_prices(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.prices.index'));
        $response->assertStatus(200);

        // Update item price via AJAX
        $updatePriceResponse = $this->actingAs($this->admin)->postJson(route('admin.prices.update'), [
            'garment_item_id' => $this->item->id,
            'service_id' => $this->service->id,
            'price' => 550,
            'wholesale_price' => 400,
            'standard_weight' => 1.5
        ]);

        $updatePriceResponse->assertStatus(200);
        $updatePriceResponse->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('service_prices', [
            'garment_item_id' => $this->item->id,
            'service_id' => $this->service->id,
            'price' => 550,
            'wholesale_price' => 400
        ]);

        $this->assertDatabaseHas('garment_items', [
            'id' => $this->item->id,
            'standard_weight' => 1.5
        ]);

        // Update service-level price (e.g. Au Kilo)
        $updateServicePriceResponse = $this->actingAs($this->admin)->postJson(route('admin.prices.service-price'), [
            'service_id' => $this->service->id,
            'price' => 600,
            'wholesale_price' => 450
        ]);
        $updateServicePriceResponse->assertStatus(200);
        $this->assertDatabaseHas('services', [
            'id' => $this->service->id,
            'price' => 600
        ]);
    }

    public function test_admin_can_manage_rubrics(): void
    {
        $couleurPath = storage_path('app/db/Couleur.db');
        $defautsPath = storage_path('app/db/Defauts.db');
        $backupColors = file_exists($couleurPath) ? file_get_contents($couleurPath) : null;
        $backupDefects = file_exists($defautsPath) ? file_get_contents($defautsPath) : null;

        try {
            $response = $this->actingAs($this->admin)->get(route('admin.rubrics.index'));
            $response->assertStatus(200);

            // Save colors
            $colorsResponse = $this->actingAs($this->admin)->post(route('admin.rubrics.save'), [
                'type' => 'colors',
                'items' => ['Bleu Marine', 'Rouge Bordeaux', 'Vert Olive']
            ]);
            $colorsResponse->assertRedirect();

            // Save defects
            $defectsResponse = $this->actingAs($this->admin)->post(route('admin.rubrics.save'), [
                'type' => 'defects',
                'items' => ['Accroc', 'Bouton Cassé', 'Fermeture Coincée']
            ]);
            $defectsResponse->assertRedirect();
        } finally {
            if ($backupColors !== null) {
                file_put_contents($couleurPath, $backupColors);
                \Illuminate\Support\Facades\Cache::forget('dict_Couleur.db');
            }
            if ($backupDefects !== null) {
                file_put_contents($defautsPath, $backupDefects);
                \Illuminate\Support\Facades\Cache::forget('dict_Defauts.db');
            }
        }
    }

    public function test_admin_can_delete_and_bulk_delete_orders(): void
    {
        $order1 = Order::create([
            'ticket_number' => '300001',
            'client_id' => $this->client->id,
            'user_id' => $this->admin->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 500,
            'paid_amount' => 500,
            'balance_amount' => 0,
            'status' => 'pending'
        ]);

        $order2 = Order::create([
            'ticket_number' => '300002',
            'client_id' => $this->client->id,
            'user_id' => $this->admin->id,
            'order_date' => now(),
            'target_delivery_date' => now()->addDays(2),
            'total_amount' => 800,
            'paid_amount' => 800,
            'balance_amount' => 0,
            'status' => 'pending'
        ]);

        // Single delete
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.orders.destroy', $order1->id));
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order1->id]);

        // Bulk delete
        $bulkResponse = $this->actingAs($this->admin)->delete(route('admin.orders.bulk-destroy'), [
            'ids' => [$order2->id]
        ]);
        $bulkResponse->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order2->id]);
    }
}
