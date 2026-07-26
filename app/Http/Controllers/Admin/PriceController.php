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
        ]);

        $garmentItemId = $validated['garment_item_id'];
        $serviceId = $validated['service_id'];
        $price = $request->input('price');
        $wholesalePrice = $request->input('wholesale_price');

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
}
