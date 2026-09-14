<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\NotificationService;
use Carbon\Carbon;

class OrderManagementController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display list of orders with filters and search.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search', '');
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');

        $query = Order::with(['client', 'user', 'orderItems.service', 'orderItems.garmentItem'])
            ->orderBy('order_date', 'desc');

        // Apply filters
        if ($status === 'express') {
            $query->where('is_express', true);
        } elseif ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'partially_delivered') {
            $query->where('status', 'partially_delivered');
        } elseif ($status === 'ready') {
            $query->where('status', 'ready');
        } elseif ($status === 'delivered') {
            $query->where('status', 'delivered');
        } elseif ($status === 'credit') {
            $query->whereIn('status', ['delivered', 'partially_delivered'])
                  ->where('balance_amount', '>', 0);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        // Apply date filters
        if (!empty($startDate)) {
            $query->whereDate('order_date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('order_date', '<=', $endDate);
        }

        // Apply search
        if (!empty(trim($search))) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function ($cq) use ($search) {
                      $cq->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('orders.index', compact('orders', 'status', 'search', 'startDate', 'endDate'));
    }

    /**
     * Toggle the is_ready state of an order item.
     */
    public function toggleItemReady(Request $request, $id)
    {
        $item = OrderItem::findOrFail($id);
        $isReady = $request->input('is_ready', false);

        if (!$item->is_delivered) {
            $item->update(['is_ready' => $isReady]);
        }

        // Reload order to check other items
        $order = $item->order()->with('orderItems')->first();
        
        $oldStatus = $order->status;
        $allReady = $order->orderItems->every(function ($oi) {
            return $oi->is_ready;
        });

        $notificationMessage = null;

        if ($allReady && $order->status === 'pending') {
            $order->update(['status' => 'ready']);
        } elseif (!$allReady && $order->status === 'ready') {
            $order->update(['status' => 'pending']);
        }

        return response()->json([
            'success' => true,
            'item_id' => $item->id,
            'is_ready' => $item->is_ready,
            'order_status' => $order->status,
            'old_status' => $oldStatus,
            'notification' => $notificationMessage
        ]);
    }

    /**
     * Update all items ready states for an order upon clicking Modifier button.
     */
    public function updateItemsReady(Request $request, $id)
    {
        $order = Order::with('orderItems')->findOrFail($id);
        $itemsData = $request->input('items', []);

        foreach ($itemsData as $itemData) {
            if (isset($itemData['id']) && isset($itemData['is_ready'])) {
                OrderItem::where('id', $itemData['id'])
                    ->where('order_id', $order->id)
                    ->where('is_delivered', false)
                    ->update(['is_ready' => (bool)$itemData['is_ready']]);
            }
        }

        // Refresh items & recalculate status
        $order->load('orderItems');
        $allDelivered = $order->orderItems->isNotEmpty() && $order->orderItems->every(function ($oi) {
            return (bool)$oi->is_delivered;
        });

        if ($allDelivered) {
            $order->update([
                'status' => 'delivered',
                'actual_delivery_date' => $order->actual_delivery_date ?? now()
            ]);
        } else {
            $anyDelivered = $order->orderItems->contains(function ($oi) {
                return (bool)$oi->is_delivered;
            });

            $undeliveredItems = $order->orderItems->where('is_delivered', false);
            $allUndeliveredReady = $undeliveredItems->isNotEmpty() && $undeliveredItems->every(function ($oi) {
                return (bool)$oi->is_ready;
            });

            if ($anyDelivered) {
                $order->update(['status' => 'partially_delivered']);
            } elseif ($allUndeliveredReady) {
                $order->update(['status' => 'ready']);
            } else {
                $order->update(['status' => 'pending']);
            }
        }

        // Return updated order with relations for frontend cache
        $order->load(['client', 'user', 'orderItems.service', 'orderItems.garmentItem']);

        return response()->json([
            'success' => true,
            'message' => 'Modifications enregistrées avec succès.',
            'order' => $order
        ]);
    }

    /**
     * Finalize delivery (partial or total) and cash in payment.
     */
    public function deliver(Request $request, $id)
    {
        $order = Order::with(['orderItems', 'client'])->findOrFail($id);

        if ($order->status === 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande est déjà entièrement livrée.'
            ], 422);
        }

        $cashCollected = floatval($request->input('cash_collected', 0));
        $itemIdsToDeliver = $request->input('item_ids', null); // Array of item IDs to deliver

        // VÉRIFICATION CLIENT PASSAGER : Aucun crédit autorisé
        if ($order->isGuestOrder()) {
            $projectedBalance = max(0, $order->total_amount - ($order->paid_amount + $cashCollected));
            if ($projectedBalance > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Le client passager n'est pas autorisé au crédit. La totalité du solde (" . number_format($order->balance_amount, 0, '.', '') . " DA restants) doit être réglée pour pouvoir récupérer la commande."
                ], 422);
            }
        }

        // Find eligible undelivered items
        $itemsQuery = OrderItem::where('order_id', $order->id)->where('is_delivered', false);
        if (is_array($itemIdsToDeliver) && !empty($itemIdsToDeliver)) {
            $itemsQuery->whereIn('id', $itemIdsToDeliver);
        } else {
            $itemsQuery->where('is_ready', true);
        }

        $itemsToDeliver = $itemsQuery->get();

        if ($itemsToDeliver->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun article prêt à être livré dans cette commande.'
            ], 422);
        }

        $now = now();
        foreach ($itemsToDeliver as $item) {
            $item->update([
                'is_ready' => true,
                'is_delivered' => true,
                'delivered_at' => $now,
            ]);
        }

        // Update financials
        $order->paid_amount += $cashCollected;
        $order->balance_amount = max(0, $order->total_amount - $order->paid_amount);
        
        if ($order->balance_amount <= 0) {
            $order->is_paid = true;
        }

        // Refresh items to check overall order status
        $order->load('orderItems');
        $allDelivered = $order->orderItems->isNotEmpty() && $order->orderItems->every(function ($oi) {
            return (bool)$oi->is_delivered;
        });

        if ($allDelivered) {
            $order->status = 'delivered';
            $order->actual_delivery_date = $now;
        } else {
            $order->status = 'partially_delivered';
        }

        $order->save();

        $order->load(['client', 'user', 'orderItems.service', 'orderItems.garmentItem']);

        return response()->json([
            'success' => true,
            'message' => $allDelivered ? 'Commande entièrement livrée avec succès !' : 'Livraison partielle effectuée avec succès !',
            'order' => $order,
            'is_all_delivered' => $allDelivered
        ]);
    }

    /**
     * Settle remaining balance for an already delivered credit order.
     */
    public function settleCredit(Request $request, $id)
    {
        $order = Order::with(['orderItems', 'client'])->findOrFail($id);

        if ($order->balance_amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande est déjà totalement soldée (solde à 0 DA).'
            ], 422);
        }

        $cashCollected = floatval($request->input('cash_collected', 0));
        if ($cashCollected <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez saisir un montant valide à encaisser.'
            ], 422);
        }

        $order->paid_amount += $cashCollected;
        $order->balance_amount = max(0, $order->total_amount - $order->paid_amount);

        if ($order->balance_amount <= 0) {
            $order->is_paid = true;
        }

        $order->save();
        $order->load(['client', 'user', 'orderItems.service', 'orderItems.garmentItem']);

        return response()->json([
            'success' => true,
            'message' => $order->balance_amount <= 0 
                ? 'Crédit entièrement soldé avec succès !' 
                : 'Acompte enregistré avec succès. Nouveau solde restant : ' . number_format($order->balance_amount, 0, '.', '') . ' DA.',
            'order' => $order,
            'is_paid' => $order->is_paid
        ]);
    }

    /**
     * Update carpet dimensions (length, width, area), calculate item price and update order totals.
     */
    public function updateCarpetDimensions(Request $request, $id)
    {
        $orderItem = OrderItem::with(['order.orderItems', 'garmentItem'])->findOrFail($id);
        $order = $orderItem->order;

        $validated = $request->validate([
            'length' => 'required|numeric|min:0.01',
            'width' => 'required|numeric|min:0.01',
            'area' => 'nullable|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $length = round(floatval($validated['length']), 2);
        $width = round(floatval($validated['width']), 2);
        $area = isset($validated['area']) && floatval($validated['area']) > 0 
            ? round(floatval($validated['area']), 2) 
            : round($length * $width, 2);

        $unitPrice = isset($validated['unit_price']) && floatval($validated['unit_price']) > 0 
            ? floatval($validated['unit_price']) 
            : floatval($orderItem->unit_price);

        // Calculate total for this carpet item: area * unit_price
        $itemTotalPrice = round($area * $unitPrice, 2);

        $orderItem->update([
            'length' => $length,
            'width' => $width,
            'area' => $area,
            'quantity' => $area,
            'unit_price' => $unitPrice,
            'total_price' => $itemTotalPrice,
            'is_measured' => true,
            'is_ready' => true,
        ]);

        // Recalculate order total
        $order->load(['orderItems.garmentItem', 'orderItems.service', 'client', 'user']);
        $subtotal = 0;
        foreach ($order->orderItems as $item) {
            $subtotal += floatval($item->total_price);
        }

        // Handle discount
        $discountAmount = 0;
        if ($order->discount_type === 'fixed') {
            $discountAmount = min($subtotal, floatval($order->discount_amount));
            $order->discount_percent = $subtotal > 0 ? round(($discountAmount / $subtotal) * 100) : 0;
        } else {
            $discountPercent = floatval($order->discount_percent);
            $discountAmount = round($subtotal * ($discountPercent / 100), 2);
        }
        $order->discount_amount = $discountAmount;
        $order->total_amount = max(0, $subtotal - $discountAmount);
        $order->balance_amount = max(0, $order->total_amount - floatval($order->paid_amount));
        $order->is_paid = ($order->balance_amount <= 0);

        // Update order status if all undelivered items are ready
        $allDelivered = $order->orderItems->isNotEmpty() && $order->orderItems->every(fn($oi) => (bool)$oi->is_delivered);
        if ($allDelivered) {
            $order->status = 'delivered';
        } else {
            $anyDelivered = $order->orderItems->contains(fn($oi) => (bool)$oi->is_delivered);
            $undelivered = $order->orderItems->where('is_delivered', false);
            $allUndeliveredReady = $undelivered->isNotEmpty() && $undelivered->every(fn($oi) => (bool)$oi->is_ready);

            if ($anyDelivered) {
                $order->status = 'partially_delivered';
            } elseif ($allUndeliveredReady) {
                $order->status = 'ready';
            } else {
                $order->status = 'pending';
            }
        }

        $order->save();

        // Optional notification simulation if order became ready
        $notificationMessage = null;
        if ($order->status === 'ready') {
            $notificationMessage = $this->notificationService->sendReadyNotification($order);
        }

        return response()->json([
            'success' => true,
            'message' => "Dimensions enregistrées ({$length}m × {$width}m = {$area} m²). Prix calculé : " . number_format($itemTotalPrice, 0, '.', ' ') . " DA.",
            'item' => $orderItem,
            'order' => $order,
            'notification' => $notificationMessage
        ]);
    }
}

