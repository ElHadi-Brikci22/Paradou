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

        $query = Order::with(['client', 'user', 'orderItems.service', 'orderItems.garmentItem'])
            ->orderBy('order_date', 'desc');

        // Apply filters
        if ($status === 'express') {
            $query->where('is_express', true);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
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

        return view('orders.index', compact('orders', 'status', 'search'));
    }

    /**
     * Toggle the is_ready state of an order item.
     */
    public function toggleItemReady(Request $request, $id)
    {
        $item = OrderItem::findOrFail($id);
        $isReady = $request->input('is_ready', false);

        $item->update(['is_ready' => $isReady]);

        // Reload order to check other items
        $order = $item->order()->with('orderItems')->first();
        
        $oldStatus = $order->status;
        $allReady = $order->orderItems->every(function ($oi) {
            return $oi->is_ready;
        });

        $notificationMessage = null;

        if ($allReady && $order->status === 'pending') {
            $order->update(['status' => 'ready']);
            // Notifications SMS/WhatsApp désactivées
            $notificationMessage = null;
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
     * Finalize delivery (retrait) and cash in remaining balance.
     */
    public function deliver(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->status === 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande est déjà marquée comme livrée.'
            ], 422);
        }

        $cashCollected = floatval($request->input('cash_collected', 0));

        // Update financials
        $order->paid_amount += $cashCollected;
        $order->balance_amount = max(0, $order->total_amount - $order->paid_amount);
        
        if ($order->balance_amount <= 0) {
            $order->is_paid = true;
        }

        $order->status = 'delivered';
        $order->actual_delivery_date = now();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Commande livrée avec succès !',
            'order' => $order
        ]);
    }
}
