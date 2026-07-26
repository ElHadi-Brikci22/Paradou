<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GarmentItem;
use App\Models\GarmentTarget;
use App\Models\Service;
use App\Models\ServicePrice;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    /**
     * Display admin catalog management interface.
     */
    public function index()
    {
        $targets = GarmentTarget::all();
        $services = Service::all();
        // Load items with target and servicePrices
        $items = GarmentItem::with(['garmentTarget', 'servicePrices'])->get();

        return view('admin.catalog.index', compact('targets', 'services', 'items'));
    }

    /**
     * Store a new garment item and its service prices.
     */
    public function storeGarmentItem(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'garment_target_id' => 'required|exists:garment_targets,id',
            'prices' => 'nullable|array',
            'prices.*' => 'nullable|numeric|min:0'
        ]);

        DB::transaction(function () use ($validated) {
            $item = GarmentItem::create([
                'name' => $validated['name'],
                'garment_target_id' => $validated['garment_target_id']
            ]);

            if (!empty($validated['prices'])) {
                foreach ($validated['prices'] as $serviceId => $priceVal) {
                    if ($priceVal !== null && $priceVal !== '') {
                        ServicePrice::create([
                            'service_id' => $serviceId,
                            'garment_item_id' => $item->id,
                            'price' => floatval($priceVal)
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.catalog.index')->with('success', 'L\'article a été créé avec succès.');
    }

    /**
     * Update an existing garment item and its service prices.
     */
    public function updateGarmentItem(Request $request, $id)
    {
        $item = GarmentItem::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'garment_target_id' => 'required|exists:garment_targets,id',
            'prices' => 'nullable|array',
            'prices.*' => 'nullable|numeric|min:0'
        ]);

        DB::transaction(function () use ($item, $validated) {
            $item->update([
                'name' => $validated['name'],
                'garment_target_id' => $validated['garment_target_id']
            ]);

            // Sync prices
            if (isset($validated['prices'])) {
                foreach ($validated['prices'] as $serviceId => $priceVal) {
                    if ($priceVal !== null && $priceVal !== '') {
                        ServicePrice::updateOrCreate(
                            ['service_id' => $serviceId, 'garment_item_id' => $item->id],
                            ['price' => floatval($priceVal)]
                        );
                    } else {
                        // Delete if price is set to empty
                        ServicePrice::where('service_id', $serviceId)
                            ->where('garment_item_id', $item->id)
                            ->delete();
                    }
                }
            }
        });

        return redirect()->route('admin.catalog.index')->with('success', 'L\'article a été mis à jour avec succès.');
    }

    /**
     * Remove the specified garment item.
     */
    public function destroyGarmentItem($id)
    {
        $item = GarmentItem::findOrFail($id);
        $item->delete(); // Deletes service prices in cascade

        return redirect()->route('admin.catalog.index')->with('success', 'L\'article a été supprimé avec succès.');
    }

    /**
     * Store a new garment target (category).
     */
    public function storeGarmentTarget(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:garment_targets,name'
        ]);

        GarmentTarget::create($validated);

        return redirect()->route('admin.catalog.index', ['tab' => 'targets'])->with('success', 'La catégorie a été créée avec succès.');
    }

    /**
     * Update an existing garment target (category).
     */
    public function updateGarmentTarget(Request $request, $id)
    {
        $target = GarmentTarget::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:garment_targets,name,' . $target->id
        ]);

        $target->update($validated);

        return redirect()->route('admin.catalog.index', ['tab' => 'targets'])->with('success', 'La catégorie a été mise à jour avec succès.');
    }

    /**
     * Delete a garment target (category).
     */
    public function destroyGarmentTarget($id)
    {
        $target = GarmentTarget::findOrFail($id);
        $target->delete();

        return redirect()->route('admin.catalog.index', ['tab' => 'targets'])->with('success', 'La catégorie a été supprimée avec succès.');
    }

    /**
     * Store a new service.
     */
    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'code' => 'required|string|max:255|unique:services,code'
        ]);

        Service::create($validated);

        return redirect()->route('admin.catalog.index', ['tab' => 'services'])->with('success', 'Le service a été créé avec succès.');
    }

    /**
     * Update an existing service.
     */
    public function updateService(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'code' => 'required|string|max:255|unique:services,code,' . $service->id
        ]);

        $service->update($validated);

        return redirect()->route('admin.catalog.index', ['tab' => 'services'])->with('success', 'Le service a été mis à jour avec succès.');
    }

    /**
     * Delete a service.
     */
    public function destroyService($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return redirect()->route('admin.catalog.index', ['tab' => 'services'])->with('success', 'Le service a été supprimé avec succès.');
    }
}
