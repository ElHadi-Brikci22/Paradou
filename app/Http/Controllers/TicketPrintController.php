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

        $tagsData = $this->buildTagsSummary($order);
        $tags = $tagsData['tags'];
        $itemsSummary = $tagsData['items_summary'];
        $totalOrderPieces = $tagsData['total_pieces'];

        return view('print.tags', compact('order', 'tags', 'itemsSummary', 'totalOrderPieces'));
    }

    /**
     * Display both the ticket receipt and the hanger tags combined in a single view.
     */
    public function printAll($id)
    {
        $order = Order::with(['client', 'user', 'orderItems.service', 'orderItems.garmentItem'])
            ->findOrFail($id);

        $tagsData = $this->buildTagsSummary($order);
        $tags = $tagsData['tags'];
        $itemsSummary = $tagsData['items_summary'];
        $totalOrderPieces = $tagsData['total_pieces'];

        return view('print.all', compact('order', 'tags', 'itemsSummary', 'totalOrderPieces'));
    }

    /**
     * Generate tags summary and pieces count for the order.
     */
    private function buildTagsSummary(Order $order): array
    {
        $tags = [];
        $itemsSummary = [];
        $totalOrderPieces = 0;

        foreach ($order->orderItems as $item) {
            $garment = $item->garmentItem;
            $piecesPerItem = $garment ? max(1, intval($garment->pieces_count ?: 1)) : 1;
            $qty = max(1, intval(ceil($item->quantity)));

            $totalPieces = !empty($item->pieces) && intval($item->pieces) > 0 
                ? intval($item->pieces) 
                : ($qty * $piecesPerItem);
            $totalPieces = max(1, $totalPieces);

            $totalOrderPieces += $totalPieces;
            $isMultiPiece = ($piecesPerItem > 1 || $totalPieces > 1);

            $itemsSummary[] = [
                'name' => $garment ? $garment->name : 'Article',
                'service' => $item->service ? $item->service->name : 'Service',
                'quantity' => $qty,
                'pieces' => $totalPieces,
                'pieces_per_item' => $piecesPerItem,
                'is_multi_piece' => $isMultiPiece,
                'colors' => $item->colors,
                'defects' => $item->defects,
                'stains' => $item->stains,
                'notes' => $item->notes
            ];

            // Retain individual tag items for backward compatibility
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

        return [
            'tags' => $tags,
            'items_summary' => $itemsSummary,
            'total_pieces' => $totalOrderPieces
        ];
    }
}
