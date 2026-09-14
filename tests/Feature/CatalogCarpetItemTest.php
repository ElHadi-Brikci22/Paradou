<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\GarmentTarget;
use App\Models\GarmentItem;
use App\Models\Service;

class CatalogCarpetItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_garment_item_flagged_as_carpet()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = GarmentTarget::create(['name' => 'Maison']);
        $service = Service::create(['name' => 'Lavage', 'code' => 'lavage']);

        $response = $this->actingAs($admin)->post(route('admin.catalog.item.store'), [
            'name' => 'Kilim Oriental',
            'garment_target_id' => $target->id,
            'is_carpet' => '1',
            'prices' => [
                $service->id => 450
            ]
        ]);

        $response->assertRedirect(route('admin.catalog.index'));

        $item = GarmentItem::where('name', 'Kilim Oriental')->first();
        $this->assertNotNull($item);
        $this->assertTrue((bool)$item->is_carpet);
        $this->assertEquals('m2', $item->unit_type);
        $this->assertTrue($item->isCarpet());
    }

    public function test_can_create_regular_item_not_carpet()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = GarmentTarget::create(['name' => 'Homme']);

        $response = $this->actingAs($admin)->post(route('admin.catalog.item.store'), [
            'name' => 'Pantalon lin',
            'garment_target_id' => $target->id,
            'is_carpet' => '0',
        ]);

        $response->assertRedirect(route('admin.catalog.index'));

        $item = GarmentItem::where('name', 'Pantalon lin')->first();
        $this->assertNotNull($item);
        $this->assertFalse((bool)$item->is_carpet);
        $this->assertEquals('piece', $item->unit_type);
        $this->assertFalse($item->isCarpet());
    }

    public function test_can_update_item_to_carpet_and_back()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = GarmentTarget::create(['name' => 'Maison']);

        $item = GarmentItem::create([
            'name' => 'Grand plaid salon',
            'garment_target_id' => $target->id,
            'unit_type' => 'piece',
            'is_carpet' => false,
        ]);

        $this->assertFalse($item->isCarpet());

        // Update to carpet
        $response = $this->actingAs($admin)->put(route('admin.catalog.item.update', $item->id), [
            'name' => 'Grand plaid salon',
            'garment_target_id' => $target->id,
            'is_carpet' => '1',
        ]);

        $response->assertRedirect(route('admin.catalog.index'));
        $item->refresh();
        $this->assertTrue((bool)$item->is_carpet);
        $this->assertEquals('m2', $item->unit_type);
        $this->assertTrue($item->isCarpet());

        // Update back to regular piece
        $response2 = $this->actingAs($admin)->put(route('admin.catalog.item.update', $item->id), [
            'name' => 'Grand plaid salon',
            'garment_target_id' => $target->id,
            'is_carpet' => '0',
        ]);

        $response2->assertRedirect(route('admin.catalog.index'));
        $item->refresh();
        $this->assertFalse((bool)$item->is_carpet);
        $this->assertEquals('piece', $item->unit_type);
        $this->assertFalse($item->isCarpet());
    }
}
