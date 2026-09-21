<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class TicketPrintController extends Controller
{
    /**
     * Display the thermal receipt (ticket de caisse client) for print.
     */
    public function printTicket($id)
    {
        $order = Order::with(['client', 'user', 'orderItems.service', 'orderItems.garmentItem'])
            ->findOrFail($id);

        return view('print.ticket', compact('order'));
    }

    /**
     * Display hanger identification tags (étiquettes cintres) for print.
     */
    public function printTags($id)
    {
        $order = Order::with(['client', 'orderItems.service', 'orderItems.garmentItem'])
            ->findOrFail($id);

        $tags = $this->buildTagsList($order);

        return view('print.tags', compact('order', 'tags'));
    }

    /**
     * Display both the ticket receipt and the hanger tags combined in a single view.
     */
    public function printAll($id)
    {
        $order = Order::with(['client', 'user', 'orderItems.service', 'orderItems.garmentItem'])
            ->findOrFail($id);

        $tags = $this->buildTagsList($order);

        return view('print.all', compact('order', 'tags'));
    }

    /**
     * Generate individual tags for each physical piece of each order item.
     */
    private function buildTagsList(Order $order): array
    {
        $tags = [];
        foreach ($order->orderItems as $item) {
            $garment = $item->garmentItem;
            $piecesPerItem = $garment ? max(1, intval($garment->pieces_count ?: 1)) : 1;
            $qty = max(1, intval(ceil($item->quantity)));

            $totalPieces = !empty($item->pieces) && intval($item->pieces) > 0 
                ? intval($item->pieces) 
                : ($qty * $piecesPerItem);
            $totalPieces = max(1, $totalPieces);

            $isMultiPiece = ($piecesPerItem > 1 || $totalPieces > 1);

            for ($i = 0; $i < $totalPieces; $i++) {
                $tags[] = [
                    'index' => ($i + 1),
                    'total_qty' => $totalPieces,
                    'pieces_per_item' => $piecesPerItem,
                    'is_multi_piece' => $isMultiPiece,
                    'garment_name' => $garment ? $garment->name : 'Article',
                    'service_name' => $item->service ? $item->service->name : 'Service',
                    'colors' => $item->colors,
                    'defects' => $item->defects,
                    'stains' => $item->stains,
                    'notes' => $item->notes
                ];
            }
        }
        return $tags;
    }
}
