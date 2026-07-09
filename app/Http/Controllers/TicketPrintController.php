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

        // Generate flat array of tags (e.g. if qty of costume is 2, make 2 tags)
        $tags = [];
        foreach ($order->orderItems as $item) {
            $qty = intval(ceil($item->quantity));
            for ($i = 0; $i < $qty; $i++) {
                $tags[] = [
                    'index' => ($i + 1),
                    'total_qty' => $qty,
                    'garment_name' => $item->garmentItem->name,
                    'service_name' => $item->service->name,
                    'colors' => $item->colors,
                    'defects' => $item->defects,
                    'stains' => $item->stains,
                    'notes' => $item->notes
                ];
            }
        }

        return view('print.tags', compact('order', 'tags'));
    }

    /**
     * Display both the ticket receipt and the hanger tags combined in a single view.
     */
    public function printAll($id)
    {
        $order = Order::with(['client', 'user', 'orderItems.service', 'orderItems.garmentItem'])
            ->findOrFail($id);

        $tags = [];
        foreach ($order->orderItems as $item) {
            $qty = intval(ceil($item->quantity));
            for ($i = 0; $i < $qty; $i++) {
                $tags[] = [
                    'index' => ($i + 1),
                    'total_qty' => $qty,
                    'garment_name' => $item->garmentItem->name,
                    'service_name' => $item->service->name,
                    'colors' => $item->colors,
                    'defects' => $item->defects,
                    'stains' => $item->stains,
                    'notes' => $item->notes
                ];
            }
        }

        return view('print.all', compact('order', 'tags'));
    }
}
