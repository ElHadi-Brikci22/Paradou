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
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.garment_item_id' => 'required|exists:garment_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.colors' => 'nullable|array',
            'items.*.defects' => 'nullable|array',
            'items.*.stains' => 'nullable|array',
            'items.*.notes' => 'nullable|string'
        ]);

        try {
            $order = DB::transaction(function () use (&$validated) {
                // Generate next ticket number if not provided
                $ticketNumber = $validated['ticket_number'];
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
                $discountAmountInput = floatval($validated['discount_amount'] ?? 0);
                $paidAmount = floatval($validated['paid_amount']);
                $isExpress = filter_var($validated['is_express'] ?? false, FILTER_VALIDATE_BOOLEAN);

                // Calculate total item amounts
                $subtotal = 0;
                foreach ($validated['items'] as $idx => $item) {
                    if ($isExpress) {
                        $validated['items'][$idx]['unit_price'] = floatval($item['unit_price']) * 2;
                    }
                    $subtotal += floatval($validated['items'][$idx]['quantity']) * floatval($validated['items'][$idx]['unit_price']);
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
                    'paid_amount' => $paidAmount,
                    'balance_amount' => $balanceAmount,
                    'remarks' => $validated['remarks'] ?? null,
                    'is_express' => $isExpress,
                ]);

                // Create order items
                foreach ($validated['items'] as $item) {
                    $qty = floatval($item['quantity']);
                    $uPrice = floatval($item['unit_price']);
                    $itemTotal = $qty * $uPrice;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'service_id' => $item['service_id'],
                        'garment_item_id' => $item['garment_item_id'],
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du ticket : ' . $e->getMessage()
            ], 500);
        }
    }
}
