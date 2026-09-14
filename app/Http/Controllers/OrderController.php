<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Store a new checkout order ticket in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'ticket_number' => 'nullable|string|max:50',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|string|in:percent,fixed',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'target_delivery_date' => 'required|date',
            'remarks' => 'nullable|string',
            'is_express' => 'nullable|boolean',
            'total_weight' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.garment_item_id' => 'required|exists:garment_items,id',
            'items.*.pieces' => 'nullable|integer|min:1',
            'items.*.weight' => 'nullable|numeric|min:0',
            'items.*.length' => 'nullable|numeric|min:0',
            'items.*.width' => 'nullable|numeric|min:0',
            'items.*.area' => 'nullable|numeric|min:0',
            'items.*.is_measured' => 'nullable|boolean',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.colors' => 'nullable|array',
            'items.*.defects' => 'nullable|array',
            'items.*.stains' => 'nullable|array',
            'items.*.notes' => 'nullable|string'
        ]);

        try {
            $order = DB::transaction(function () use (&$validated) {
                // Generate next ticket number if not provided
                $ticketNumber = $validated['ticket_number'] ?? null;
                if (empty($ticketNumber)) {
                    $lastOrder = Order::orderBy('id', 'desc')->first();
                    $ticketNumber = $lastOrder ? str_pad(intval($lastOrder->ticket_number) + 1, 6, '0', STR_PAD_LEFT) : '000001';
                }

                // Check if ticket number is unique, otherwise increment it
                while (Order::where('ticket_number', $ticketNumber)->exists()) {
                    $ticketNumber = str_pad(intval($ticketNumber) + 1, 6, '0', STR_PAD_LEFT);
                }

                $discountType = $validated['discount_type'] ?? 'percent';
                $discountPercent = floatval($validated['discount_percent'] ?? 0);
                if (Auth::user()->role !== 'admin' && $discountType === 'percent' && $discountPercent > 0) {
                    throw new \Exception("Les caissiers ne peuvent appliquer que des remises en montant fixe (DA).", 422);
                }
                $discountAmountInput = floatval($validated['discount_amount'] ?? 0);
                $paidAmount = floatval($validated['paid_amount']);
                $isExpress = filter_var($validated['is_express'] ?? false, FILTER_VALIDATE_BOOLEAN);

                // Calculate total item amounts
                $subtotal = 0;
                foreach ($validated['items'] as $idx => $item) {
                    if ($isExpress) {
                        $validated['items'][$idx]['unit_price'] = floatval($item['unit_price']) * 2;
                    }
                    $garmentItem = \App\Models\GarmentItem::find($item['garment_item_id']);
                    $isCarpet = $garmentItem && $garmentItem->isCarpet();
                    $isMeasured = isset($item['is_measured']) ? filter_var($item['is_measured'], FILTER_VALIDATE_BOOLEAN) : false;

                    if ($isCarpet && !$isMeasured) {
                        $itemSubtotal = 0;
                    } else {
                        $itemSubtotal = floatval($validated['items'][$idx]['quantity']) * floatval($validated['items'][$idx]['unit_price']);
                    }
                    $subtotal += $itemSubtotal;
                }

                // Apply order-level discount
                if ($discountType === 'fixed') {
                    $discountAmount = $discountAmountInput;
                    $discountPercent = $subtotal > 0 ? round(($discountAmount / $subtotal) * 100) : 0;
                } else {
                    $discountAmount = $subtotal * ($discountPercent / 100);
                }

                $totalAmount = max(0, $subtotal - $discountAmount);
                
                // Keep balance
                $balanceAmount = max(0, $totalAmount - $paidAmount);
                $isPaid = $balanceAmount <= 0;

                // Create order
                $order = Order::create([
                    'ticket_number' => $ticketNumber,
                    'client_id' => $validated['client_id'],
                    'user_id' => Auth::id() ?: \App\Models\User::first()->id,
                    'status' => 'pending',
                    'is_paid' => $isPaid,
                    'order_date' => now(),
                    'target_delivery_date' => Carbon::parse($validated['target_delivery_date']),
                    'actual_delivery_date' => null,
                    'discount_percent' => $discountPercent,
                    'discount_type' => $discountType,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'total_weight' => isset($validated['total_weight']) && $validated['total_weight'] !== '' ? floatval($validated['total_weight']) : null,
                    'paid_amount' => $paidAmount,
                    'balance_amount' => $balanceAmount,
                    'remarks' => $validated['remarks'] ?? null,
                    'is_express' => $isExpress,
                ]);

                // Create order items
                foreach ($validated['items'] as $item) {
                    $qty = floatval($item['quantity']);
                    $uPrice = floatval($item['unit_price']);
                    
                    $garmentItem = \App\Models\GarmentItem::find($item['garment_item_id']);
                    $isCarpet = $garmentItem && $garmentItem->isCarpet();
                    $isMeasured = isset($item['is_measured']) ? filter_var($item['is_measured'], FILTER_VALIDATE_BOOLEAN) : false;
                    $length = isset($item['length']) && $item['length'] !== '' ? floatval($item['length']) : null;
                    $width = isset($item['width']) && $item['width'] !== '' ? floatval($item['width']) : null;
                    $area = isset($item['area']) && $item['area'] !== '' ? floatval($item['area']) : null;

                    if ($isCarpet && !$isMeasured) {
                        $itemTotal = 0;
                    } else {
                        $itemTotal = $qty * $uPrice;
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'service_id' => $item['service_id'],
                        'garment_item_id' => $item['garment_item_id'],
                        'pieces' => isset($item['pieces']) ? intval($item['pieces']) : 1,
                        'weight' => isset($item['weight']) && $item['weight'] !== '' ? floatval($item['weight']) : null,
                        'length' => $length,
                        'width' => $width,
                        'area' => $area,
                        'is_measured' => $isMeasured,
                        'quantity' => $qty,
                        'unit_price' => $uPrice,
                        'total_price' => $itemTotal,
                        'colors' => $item['colors'] ?? [],
                        'defects' => $item['defects'] ?? [],
                        'stains' => $item['stains'] ?? [],
                        'is_ready' => false,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }

                // If balance is negative or customer pays more, we could credit the customer
                // (Optional: handle customer credit adjustment)

                return $order;
            });

            return response()->json([
                'success' => true,
                'message' => 'Ticket créé avec succès.',
                'ticket_number' => $order->ticket_number,
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            $status = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 422;
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du ticket : ' . $e->getMessage()
            ], $status);
        }
    }

    /**
     * Update an existing order and its items (admin only, pending status only).
     */
    public function update(Request $request, $id)
    {
        // 1. Authorize - must be admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé. Seul un administrateur peut modifier une commande.'
            ], 403);
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'ticket_number' => 'nullable|string|max:50',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|string|in:percent,fixed',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'target_delivery_date' => 'required|date',
            'remarks' => 'nullable|string',
            'is_express' => 'nullable|boolean',
            'total_weight' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.garment_item_id' => 'required|exists:garment_items,id',
            'items.*.pieces' => 'nullable|integer|min:1',
            'items.*.weight' => 'nullable|numeric|min:0',
            'items.*.length' => 'nullable|numeric|min:0',
            'items.*.width' => 'nullable|numeric|min:0',
            'items.*.area' => 'nullable|numeric|min:0',
            'items.*.is_measured' => 'nullable|boolean',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.colors' => 'nullable|array',
            'items.*.defects' => 'nullable|array',
            'items.*.stains' => 'nullable|array',
            'items.*.notes' => 'nullable|string'
        ]);

        try {
            $order = Order::findOrFail($id);

            // 2. Verify status is pending
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les commandes en cours peuvent être modifiées.'
                ], 422);
            }

            DB::transaction(function () use (&$validated, $order) {
                $discountType = $validated['discount_type'] ?? 'percent';
                $discountPercent = floatval($validated['discount_percent'] ?? 0);
                $discountAmountInput = floatval($validated['discount_amount'] ?? 0);
                $paidAmount = floatval($validated['paid_amount']);
                $isExpress = filter_var($validated['is_express'] ?? false, FILTER_VALIDATE_BOOLEAN);

                // Calculate total item amounts
                $subtotal = 0;
                foreach ($validated['items'] as $idx => $item) {
                    if ($isExpress) {
                        $validated['items'][$idx]['unit_price'] = floatval($item['unit_price']) * 2;
                    }
                    $garmentItem = \App\Models\GarmentItem::find($item['garment_item_id']);
                    $isCarpet = $garmentItem && $garmentItem->isCarpet();
                    $isMeasured = isset($item['is_measured']) ? filter_var($item['is_measured'], FILTER_VALIDATE_BOOLEAN) : false;

                    if ($isCarpet && !$isMeasured) {
                        $itemSubtotal = 0;
                    } else {
                        $itemSubtotal = floatval($validated['items'][$idx]['quantity']) * floatval($validated['items'][$idx]['unit_price']);
                    }
                    $subtotal += $itemSubtotal;
                }

                // Apply order-level discount
                if ($discountType === 'fixed') {
                    $discountAmount = $discountAmountInput;
                    $discountPercent = $subtotal > 0 ? round(($discountAmount / $subtotal) * 100) : 0;
                } else {
                    $discountAmount = $subtotal * ($discountPercent / 100);
                }

                $totalAmount = max(0, $subtotal - $discountAmount);
                $balanceAmount = max(0, $totalAmount - $paidAmount);
                $isPaid = $balanceAmount <= 0;

                // Update order
                $order->update([
                    'client_id' => $validated['client_id'],
                    'ticket_number' => $validated['ticket_number'] ?? $order->ticket_number,
                    'discount_percent' => $discountPercent,
                    'discount_type' => $discountType,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'total_weight' => isset($validated['total_weight']) && $validated['total_weight'] !== '' ? floatval($validated['total_weight']) : null,
                    'paid_amount' => $paidAmount,
                    'balance_amount' => $balanceAmount,
                    'is_paid' => $isPaid,
                    'target_delivery_date' => Carbon::parse($validated['target_delivery_date']),
                    'remarks' => $validated['remarks'] ?? null,
                    'is_express' => $isExpress,
                ]);

                // Delete old order items
                $order->orderItems()->delete();

                // Create new order items
                foreach ($validated['items'] as $item) {
                    $qty = floatval($item['quantity']);
                    $uPrice = floatval($item['unit_price']);

                    $garmentItem = \App\Models\GarmentItem::find($item['garment_item_id']);
                    $isCarpet = $garmentItem && $garmentItem->isCarpet();
                    $isMeasured = isset($item['is_measured']) ? filter_var($item['is_measured'], FILTER_VALIDATE_BOOLEAN) : false;
                    $length = isset($item['length']) && $item['length'] !== '' ? floatval($item['length']) : null;
                    $width = isset($item['width']) && $item['width'] !== '' ? floatval($item['width']) : null;
                    $area = isset($item['area']) && $item['area'] !== '' ? floatval($item['area']) : null;

                    if ($isCarpet && !$isMeasured) {
                        $itemTotal = 0;
                    } else {
                        $itemTotal = $qty * $uPrice;
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'service_id' => $item['service_id'],
                        'garment_item_id' => $item['garment_item_id'],
                        'pieces' => isset($item['pieces']) ? intval($item['pieces']) : 1,
                        'weight' => isset($item['weight']) && $item['weight'] !== '' ? floatval($item['weight']) : null,
                        'length' => $length,
                        'width' => $width,
                        'area' => $area,
                        'is_measured' => $isMeasured,
                        'quantity' => $qty,
                        'unit_price' => $uPrice,
                        'total_price' => $itemTotal,
                        'colors' => $item['colors'] ?? [],
                        'defects' => $item['defects'] ?? [],
                        'stains' => $item['stains'] ?? [],
                        'is_ready' => false,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Ticket modifié avec succès.',
                'ticket_number' => $order->ticket_number,
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du ticket : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a single order (admin only).
     */
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé. Seul un administrateur peut supprimer une commande.'
            ], 403);
        }

        try {
            $order = Order::findOrFail($id);

            DB::transaction(function () use ($order) {
                $order->orderItems()->delete();
                $order->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Commande supprimée avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la commande : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete multiple orders (admin only).
     */
    public function bulkDestroy(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé. Seul un administrateur peut supprimer des commandes.'
            ], 403);
        }

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:orders,id'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $ids = $validated['ids'];
                OrderItem::whereIn('order_id', $ids)->delete();
                Order::whereIn('id', $ids)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Commandes sélectionnées supprimées avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression groupée : ' . $e->getMessage()
            ], 500);
        }
    }
}
