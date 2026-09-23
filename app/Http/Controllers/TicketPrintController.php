<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

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
     * Display public mobile-friendly digital receipt accessible via QR code scan.
     */
    public function publicReceipt($identifier)
    {
        $order = Order::with(['client', 'user', 'orderItems.service', 'orderItems.garmentItem'])
            ->where('ticket_number', $identifier)
            ->orWhere('uuid', $identifier)
            ->orWhere('id', $identifier)
            ->firstOrFail();

        return view('print.public_receipt', compact('order'));
    }

    /**
     * Generate and stream/download the PDF receipt for a ticket.
     */
    public function downloadPublicPdf($identifier, Request $request)
    {
        $order = Order::with(['client', 'user', 'orderItems.service', 'orderItems.garmentItem'])
            ->where('ticket_number', $identifier)
            ->orWhere('uuid', $identifier)
            ->orWhere('id', $identifier)
            ->firstOrFail();

        $pdf = Pdf::loadView('print.pdf_receipt', compact('order'));
        $pdf->setPaper([0, 0, 226.77, 650], 'portrait');

        $filename = "recu-paradou-{$order->ticket_number}.pdf";

        if ($request->has('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Resolve the public URL for the QR code.
     * Replaces 'localhost' or '127.0.0.1' with the computer's LAN IP so smartphones on Wi-Fi can open it.
     */
    public static function getPublicReceiptUrl(string $ticketNumber, bool $directPdf = true): string
    {
        $path = $directPdf ? "/r/{$ticketNumber}/pdf" : "/r/{$ticketNumber}";

        // 1. Check if user configured an explicit public URL in .env
        $publicAppUrl = env('PUBLIC_APP_URL');
        if (!empty($publicAppUrl)) {
            return rtrim($publicAppUrl, '/') . $path;
        }

        $request = request();
        $host = $request ? $request->getHost() : 'localhost';
        $port = $request ? $request->getPort() : 8000;
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';
        $scheme = $request ? $request->getScheme() : 'http';

        // 2. If accessing via localhost on PC, substitute with local LAN IP (e.g. 192.168.1.22)
        if (in_array($host, ['localhost', '127.0.0.1', '::1', ''])) {
            $localIp = getHostByName(getHostName());
            if (!empty($localIp) && $localIp !== '127.0.0.1') {
                return "{$scheme}://{$localIp}{$portSuffix}{$path}";
            }
        }

        return url($path);
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
