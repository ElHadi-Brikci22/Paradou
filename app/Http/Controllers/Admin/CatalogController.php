<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GarmentItem;
use App\Models\GarmentTarget;
use App\Models\GarmentSubcategory;
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
        $targets = GarmentTarget::with('subcategories')->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        $services = Service::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        $subcategories = GarmentSubcategory::with('garmentTarget')
            ->withCount('garmentItems')
            ->orderBy('garment_target_id')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        // Load items with target, subcategory and servicePrices
        $items = GarmentItem::with(['garmentTarget', 'garmentSubcategory', 'servicePrices'])->get();

        return view('admin.catalog.index', compact('targets', 'services', 'items', 'subcategories'));
    }

    /**
     * Store a new garment item and its service prices.
     */
    public function storeGarmentItem(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'garment_target_id' => 'required|exists:garment_targets,id',
            'garment_subcategory_id' => 'nullable|exists:garment_subcategories,id',
            'standard_weight' => 'nullable|numeric|min:0',
            'is_carpet' => 'nullable|boolean',
            'prices' => 'nullable|array',
            'prices.*' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);

        DB::transaction(function () use ($validated, $request) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/catalog'), $filename);
                $imagePath = 'images/catalog/' . $filename;
            }

            $isCarpet = $request->boolean('is_carpet');

            $item = GarmentItem::create([
                'name' => $validated['name'],
                'garment_target_id' => $validated['garment_target_id'],
                'garment_subcategory_id' => $validated['garment_subcategory_id'] ?? null,
                'image_path' => $imagePath,
                'standard_weight' => isset($validated['standard_weight']) && $validated['standard_weight'] !== '' ? floatval($validated['standard_weight']) : null,
                'is_carpet' => $isCarpet,
                'unit_type' => $isCarpet ? 'm2' : 'piece',
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
            'garment_subcategory_id' => 'nullable|exists:garment_subcategories,id',
            'standard_weight' => 'nullable|numeric|min:0',
            'is_carpet' => 'nullable|boolean',
            'prices' => 'nullable|array',
            'prices.*' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);

        DB::transaction(function () use ($item, $validated, $request) {
            $isCarpet = $request->boolean('is_carpet');

            $item->update([
                'name' => $validated['name'],
                'garment_target_id' => $validated['garment_target_id'],
                'garment_subcategory_id' => $validated['garment_subcategory_id'] ?? null,
                'standard_weight' => isset($validated['standard_weight']) && $validated['standard_weight'] !== '' ? floatval($validated['standard_weight']) : null,
                'is_carpet' => $isCarpet,
                'unit_type' => $isCarpet ? 'm2' : 'piece',
            ]);

            if ($request->hasFile('image')) {
                // Delete old image if it exists on disk
                if ($item->image_path && file_exists(public_path($item->image_path))) {
                    @unlink(public_path($item->image_path));
                }
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/catalog'), $filename);
                $item->update(['image_path' => 'images/catalog/' . $filename]);
            }

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
     * Helper to perform clean swap of sort_order between an item and conflicting item.
     */
    protected function performSortOrderSwap($model, int $newOrder, $queryScope = null)
    {
        $oldOrder = $model->sort_order;
        if ($oldOrder === $newOrder) {
            return;
        }

        $query = $queryScope ? clone $queryScope : $model->newQuery();
        $conflict = $query->where('id', '!=', $model->id)->where('sort_order', $newOrder)->first();

        if ($conflict) {
            // Direct swap: conflicting item takes the old order of the item being modified
            $conflict->update(['sort_order' => $oldOrder ?? 0]);
        }

        $model->update(['sort_order' => $newOrder]);
    }

    /**
     * Helper to assign sort_order on store.
     */
    protected function assignNewSortOrder($queryScope, ?int $requestedOrder)
    {
        $query = clone $queryScope;
        $maxOrder = $query->max('sort_order') ?? 0;

        if ($requestedOrder === null || $requestedOrder <= 0) {
            return $maxOrder + 1;
        }

        $conflict = $query->where('sort_order', $requestedOrder)->first();
        if ($conflict) {
            $conflict->update(['sort_order' => $maxOrder + 1]);
        }

        return $requestedOrder;
    }

    /**
     * Store a new garment target (category).
     */
    public function storeGarmentTarget(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:garment_targets,name',
            'sort_order' => 'nullable|integer|min:1'
        ]);

        $sortOrder = $this->assignNewSortOrder(GarmentTarget::query(), isset($validated['sort_order']) ? intval($validated['sort_order']) : null);

        GarmentTarget::create([
            'name' => $validated['name'],
            'sort_order' => $sortOrder,
        ]);

        return redirect()->route('admin.catalog.index', ['tab' => 'targets'])->with('success', 'La catégorie a été créée avec succès.');
    }

    /**
     * Update an existing garment target (category).
     */
    public function updateGarmentTarget(Request $request, $id)
    {
        $target = GarmentTarget::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:garment_targets,name,' . $target->id,
            'sort_order' => 'nullable|integer|min:1'
        ]);

        $target->update(['name' => $validated['name']]);

        if (isset($validated['sort_order']) && intval($validated['sort_order']) > 0) {
            $this->performSortOrderSwap($target, intval($validated['sort_order']));
        }

        return redirect()->route('admin.catalog.index', ['tab' => 'targets'])->with('success', 'La catégorie et son ordre d\'affichage ont été mis à jour.');
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
     * Store a new garment subcategory.
     */
    public function storeGarmentSubcategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'garment_target_id' => 'required|exists:garment_targets,id',
            'sort_order' => 'nullable|integer|min:1'
        ]);

        $scope = GarmentSubcategory::where('garment_target_id', $validated['garment_target_id']);
        $sortOrder = $this->assignNewSortOrder($scope, isset($validated['sort_order']) ? intval($validated['sort_order']) : null);

        GarmentSubcategory::create([
            'name' => $validated['name'],
            'garment_target_id' => $validated['garment_target_id'],
            'sort_order' => $sortOrder,
        ]);

        return redirect()->route('admin.catalog.index', ['tab' => 'subcategories'])->with('success', 'La sous-catégorie a été créée avec succès.');
    }

    /**
     * Update an existing garment subcategory.
     */
    public function updateGarmentSubcategory(Request $request, $id)
    {
        $subcategory = GarmentSubcategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'garment_target_id' => 'required|exists:garment_targets,id',
            'sort_order' => 'nullable|integer|min:1'
        ]);

        $subcategory->update([
            'name' => $validated['name'],
            'garment_target_id' => $validated['garment_target_id'],
        ]);

        if (isset($validated['sort_order']) && intval($validated['sort_order']) > 0) {
            $scope = GarmentSubcategory::where('garment_target_id', $validated['garment_target_id']);
            $this->performSortOrderSwap($subcategory, intval($validated['sort_order']), $scope);
        }

        return redirect()->route('admin.catalog.index', ['tab' => 'subcategories'])->with('success', 'La sous-catégorie et son ordre d\'affichage ont été mis à jour.');
    }

    /**
     * Delete a garment subcategory.
     */
    public function destroyGarmentSubcategory($id)
    {
        $subcategory = GarmentSubcategory::findOrFail($id);
        $subcategory->delete();

        return redirect()->route('admin.catalog.index', ['tab' => 'subcategories'])->with('success', 'La sous-catégorie a été supprimée avec succès.');
    }

    /**
     * Store a new service.
     */
    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'code' => 'required|string|max:255|unique:services,code',
            'sort_order' => 'nullable|integer|min:1'
        ]);

        $sortOrder = $this->assignNewSortOrder(Service::query(), isset($validated['sort_order']) ? intval($validated['sort_order']) : null);

        Service::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'sort_order' => $sortOrder,
        ]);

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
            'code' => 'required|string|max:255|unique:services,code,' . $service->id,
            'sort_order' => 'nullable|integer|min:1'
        ]);

        $service->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
        ]);

        if (isset($validated['sort_order']) && intval($validated['sort_order']) > 0) {
            $this->performSortOrderSwap($service, intval($validated['sort_order']));
        }

        return redirect()->route('admin.catalog.index', ['tab' => 'services'])->with('success', 'Le service et son ordre d\'affichage ont été mis à jour.');
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

    /**
     * Reorder (swap with previous or next neighbor) for targets, subcategories, or services.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:target,subcategory,service',
            'id' => 'required|integer',
            'direction' => 'required|in:up,down',
        ]);

        $type = $validated['type'];
        $id = $validated['id'];
        $direction = $validated['direction'];

        $tab = 'items';
        if ($type === 'target') {
            $model = GarmentTarget::findOrFail($id);
            $query = GarmentTarget::query();
            $tab = 'targets';
        } elseif ($type === 'subcategory') {
            $model = GarmentSubcategory::findOrFail($id);
            $query = GarmentSubcategory::where('garment_target_id', $model->garment_target_id);
            $tab = 'subcategories';
        } else {
            $model = Service::findOrFail($id);
            $query = Service::query();
            $tab = 'services';
        }

        // Get all items in this scope sorted by sort_order asc, id asc
        $items = $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        $currentIndex = $items->search(fn($item) => $item->id === $model->id);

        if ($currentIndex !== false) {
            $swapIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;
            if (isset($items[$swapIndex])) {
                $neighbor = $items[$swapIndex];
                $currentOrder = $model->sort_order;
                $neighborOrder = $neighbor->sort_order;

                // If identical, assign distinct sequential numbers
                if ($currentOrder === $neighborOrder) {
                    $currentOrder = $currentIndex + 1;
                    $neighborOrder = $swapIndex + 1;
                }

                $model->update(['sort_order' => $neighborOrder]);
                $neighbor->update(['sort_order' => $currentOrder]);
            }
        }

        return redirect()->route('admin.catalog.index', ['tab' => $tab])->with('success', 'L\'ordre d\'affichage a été mis à jour.');
    }
}
