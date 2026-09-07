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
        $order = Order::with('orderItems')->findOrFail($id);

        if ($order->status === 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande est déjà entièrement livrée.'
            ], 422);
        }

        $cashCollected = floatval($request->input('cash_collected', 0));
        $itemIdsToDeliver = $request->input('item_ids', null); // Array of item IDs to deliver

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
}

