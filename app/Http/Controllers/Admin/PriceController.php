<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GarmentItem;
use App\Models\GarmentTarget;
use App\Models\Service;
use App\Models\ServicePrice;

class PriceController extends Controller
{
    /**
     * Display the price management dashboard.
     */
    public function index()
    {
        $targets = GarmentTarget::all();
        $services = Service::all();
        
        // Load items with target and servicePrices
        $items = GarmentItem::with(['garmentTarget', 'servicePrices'])->get();

        return view('admin.prices.index', compact('targets', 'services', 'items'));
    }

    /**
     * Update or create the price for a specific garment item and service.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'garment_item_id' => 'required|exists:garment_items,id',
            'service_id' => 'required|exists:services,id',
            'price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'standard_weight' => 'nullable|numeric|min:0',
        ]);

        $garmentItemId = $validated['garment_item_id'];
        $serviceId = $validated['service_id'];
        $price = $request->input('price');
        $wholesalePrice = $request->input('wholesale_price');
        $service = Service::find($serviceId);

        // If standard_weight is provided, update garment_item
        $item = null;
        if ($request->has('standard_weight')) {
            $item = GarmentItem::find($garmentItemId);
            if ($item) {
                $rawWeight = $request->input('standard_weight');
                $item->update([
                    'standard_weight' => ($rawWeight !== null && $rawWeight !== '') ? floatval($rawWeight) : null
                ]);
            }
        }

        // For Au Kilo service, if price wasn't specified at item level, automatically use service price
        if ($service && $service->isKilo() && ($price === null || $price === '')) {
            if ($service->price !== null) {
                $servicePrice = ServicePrice::updateOrCreate(
                    ['garment_item_id' => $garmentItemId, 'service_id' => $serviceId],
                    [
                        'price' => floatval($service->price),
                        'wholesale_price' => $service->wholesale_price !== null ? floatval($service->wholesale_price) : null
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Poids standard enregistré avec succès.',
                'item' => $item
            ]);
        }

        // If both inputs are empty/null, remove the pricing row
        if (($price === null || $price === '') && ($wholesalePrice === null || $wholesalePrice === '')) {
            ServicePrice::where('garment_item_id', $garmentItemId)
                ->where('service_id', $serviceId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tarif supprimé avec succès.',
                'action' => 'deleted'
            ]);
        }

        // Update or create pricing
        $servicePrice = ServicePrice::updateOrCreate(
            ['garment_item_id' => $garmentItemId, 'service_id' => $serviceId],
            [
                'price' => ($price !== null && $price !== '') ? floatval($price) : 0.00,
                'wholesale_price' => ($wholesalePrice !== null && $wholesalePrice !== '') ? floatval($wholesalePrice) : null
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Tarif mis à jour avec succès.',
            'data' => $servicePrice
        ]);
    }

    /**
     * Update the uniform price for a service (e.g. Au Kilo service-wide price per kg).
     */
    public function updateServicePrice(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $price = $request->filled('price') ? floatval($request->input('price')) : null;
        $wholesalePrice = $request->filled('wholesale_price') ? floatval($request->input('wholesale_price')) : null;

        $service->update([
            'price' => $price,
            'wholesale_price' => $wholesalePrice,
        ]);

        // If Au Kilo, synchronize across all items
        if ($service->isKilo() && $price !== null) {
            $items = GarmentItem::all();
            foreach ($items as $item) {
                ServicePrice::updateOrCreate(
                    ['garment_item_id' => $item->id, 'service_id' => $service->id],
                    [
                        'price' => $price,
                        'wholesale_price' => $wholesalePrice,
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tarif unique du service enregistré avec succès pour tous les articles.',
            'service' => $service
        ]);
    }
}
