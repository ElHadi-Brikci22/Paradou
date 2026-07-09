<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $order->ticket_number }}</title>
    <style>
        /* CSS reset & thermal paper configurations */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            width: 80mm;
            margin: 0 auto;
            padding: 4mm 3mm;
            font-family: 'Courier New', Courier, monospace; /* Classic thermal monospace style */
            font-size: 12px;
            color: #000;
            background: #fff;
            line-height: 1.4;
        }

        /* Hide elements on print */
        @media print {
            body {
                width: 80mm;
                margin: 0 auto;
                padding: 4mm 3mm;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 0;
                size: 80mm auto;
            }
        }

        /* Layout Elements */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .header {
            margin-bottom: 5mm;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 1mm;
        }
        .header p {
            font-size: 11px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 3mm 0;
            height: 0;
        }

        .double-divider {
            border-top: 3px double #000;
            margin: 3mm 0;
            height: 0;
        }

        .info-section {
            font-size: 11px;
            margin-bottom: 3mm;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 2mm 0;
        }
        .items-table th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding-bottom: 1mm;
            font-weight: bold;
        }
        .items-table td {
            padding: 1.5mm 0;
            vertical-align: top;
        }
        .item-options {
            font-size: 9px;
            padding-left: 2mm;
            font-style: italic;
            line-height: 1.2;
        }

        /* Billing Block */
        .totals-section {
            width: 100%;
            margin-top: 2mm;
            font-size: 12px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 1mm 0;
        }
        .totals-row.big {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 2mm 0;
            margin: 1.5mm 0;
        }

        /* Footer Notes */
        .footer {
            margin-top: 6mm;
            font-size: 9px;
            line-height: 1.3;
            text-align: center;
        }
        .footer-clause {
            margin-bottom: 3mm;
            text-align: justify;
        }

        /* Simulated barcode text style */
        .barcode-text {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 5px;
            margin: 4mm 0 2mm 0;
        }

        /* Print float controller (visible in browser view) */
        .print-btn-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
        }
        .print-btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);
            font-family: sans-serif;
        }
        .print-btn:hover {
            background: #4338ca;
        }
    </style>
</head>
<body>

    <!-- Browser Action Button (Hidden during print) -->
    <div class="print-btn-container no-print">
        <button onclick="window.print()" class="print-btn">Imprimer le Ticket</button>
    </div>

    <!-- Ticket Container -->
    <div class="header text-center">
        <h1 style="font-size: 22px; font-weight: 900; letter-spacing: 1px; margin-bottom: 1mm; display: inline-flex; align-items: center; justify-content: center; width: 100%;">
            PARAD<svg style="width: 20px; height: 20px; margin: 0 1px; display: inline-block; vertical-align: middle;" fill="none" viewBox="0 0 24 24" stroke="#000" stroke-width="2.5"><rect x="4" y="3" width="16" height="18" rx="2" /><line x1="4" y1="7" x2="20" y2="7" /><circle cx="7" cy="5" r="0.75" fill="#000" /><circle cx="10" cy="5" r="0.75" fill="#000" /><circle cx="13" cy="5" r="0.75" fill="#000" /><circle cx="12" cy="14" r="4" /><circle cx="12" cy="14" r="2.5" stroke-dasharray="3 2" /></svg>U
        </h1>
        <p>Pressing & Nettoyage à Sec Moderne</p>
        <p>Tél : 0561 99 88 01</p>
        <p>Alger, Algérie</p>
    </div>

    @if($order->is_express)
        <div style="background-color: #000000; color: #ffffff; text-align: center; padding: 6px 0; font-weight: 900; font-size: 14px; letter-spacing: 2px; margin: 8px 0; text-transform: uppercase; border-radius: 4px;">
            *** TICKET EXPRESS ***
        </div>
    @else
        <div class="divider"></div>
    @endif

    <!-- Ticket Meta Info -->
    <div class="info-section">
        <div class="info-row">
            <span>TICKET N°:</span>
            <span class="font-bold">#{{ $order->ticket_number }}</span>
        </div>
        <div class="info-row">
            <span>DATE DÉPÔT:</span>
            <span>{{ $order->order_date->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="font-bold">LIVRAISON PRÉVUE:</span>
            <span class="font-bold">{{ $order->target_delivery_date->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span>CAISSIER:</span>
            <span>{{ $order->user->name }}</span>
        </div>
        <div class="info-row">
            <span>STATUT:</span>
            <span style="text-transform: uppercase;">{{ $order->status === 'delivered' ? 'LIVRÉ' : ($order->status === 'ready' ? 'PRÊT' : 'EN COURS') }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Client Info -->
    <div class="info-section">
        <div class="info-row">
            <span>CLIENT:</span>
            <span class="font-bold">{{ $order->client->name }}</span>
        </div>
        <div class="info-row">
            <span>CODE CLIENT:</span>
            <span>{{ $order->client->code }}</span>
        </div>
        @if($order->client->phone)
            <div class="info-row">
                <span>TÉLÉPHONE:</span>
                <span>{{ $order->client->phone }}</span>
            </div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- Items Listing -->
    <table class="items-table">
        <thead>
            <tr>
                <th>ARTICLE (Service)</th>
                <th class="text-center" style="width: 10mm;">QTÉ</th>
                <th class="text-right" style="width: 20mm;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
                <tr>
                    <td>
                        <span class="font-bold">{{ $item->garmentItem->name }}</span> 
                        <span style="font-size: 9px; text-transform: uppercase;">({{ $item->service->name }})</span>
                        
                        <!-- Options formatting -->
                        @php
                            $opts = [];
                            if ($item->colors && count($item->colors) > 0) $opts[] = 'Couleurs: ' . implode('/', $item->colors);
                            if ($item->defects && count($item->defects) > 0) $opts[] = 'Défauts: ' . implode('/', $item->defects);
                            if ($item->stains && count($item->stains) > 0) $opts[] = 'Taches: ' . implode('/', $item->stains);
                            if ($item->notes) $opts[] = 'Note: ' . $item->notes;
                        @endphp
                        
                        @if(count($opts) > 0)
                            <div class="item-options">
                                * {{ implode("\n* ", $opts) }}
                            </div>
                        @endif
                    </td>
                    <td class="text-center font-bold">{{ floatval($item->quantity) }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total_price, 0, '.', '') }} DA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- Billing Summary -->
    @php
        $totalBrut = $order->orderItems->sum('total_price');
        $discountAmount = $order->discount_amount;
    @endphp
    <div class="totals-section">
        <div class="totals-row">
            <span>Sous-total brut:</span>
            <span>{{ number_format($totalBrut, 0, '.', '') }} DA</span>
        </div>
        @if($discountAmount > 0)
            <div class="totals-row">
                @if($order->discount_type === 'percent')
                    <span>Remise ({{ number_format($order->discount_percent, 0) }}%):</span>
                @else
                    <span>Remise (DA):</span>
                @endif
                <span>- {{ number_format($discountAmount, 0, '.', '') }} DA</span>
            </div>
        @endif
        <div class="totals-row">
            <span>Total Net:</span>
            <span>{{ number_format($order->total_amount, 0, '.', '') }} DA</span>
        </div>
        <div class="totals-row">
            <span>Acompte versé:</span>
            <span class="font-bold">{{ number_format($order->paid_amount, 0, '.', '') }} DA</span>
        </div>
        <div class="totals-row big">
            <span>RESTE À PAYER:</span>
            <span>{{ number_format($order->balance_amount, 0, '.', '') }} DA</span>
        </div>
    </div>

    @if($order->remarks)
        <div class="info-section" style="margin-top: 3mm;">
            <span class="font-bold">NOTE TICKET:</span>
            <span>{{ $order->remarks }}</span>
        </div>
    @endif

    <div class="divider"></div>

    <!-- Terms & Policies (Thermal standard) -->
    <div class="footer">
        <div class="footer-clause">
            CONDITIONS: Veuillez conserver ce ticket pour le retrait. Tout article non retiré après 30 jours n'est plus garanti. Le pressing n'est pas responsable des boutons fragiles, fermetures et décolorations naturelles.
        </div>
        <p class="font-bold">*** MERCI DE VOTRE VISITE ***</p>
        
        <!-- Simulated Barcode for scanning -->
        <div class="barcode-text text-center">
            *{{ $order->ticket_number }}*
        </div>
    </div>

    <!-- Auto Print Script -->
    <script>
        if (window.self === window.top) {
            window.addEventListener('DOMContentLoaded', () => {
                // Delay slightly to ensure page assets load
                setTimeout(() => {
                    window.print();
                    
                    // If it was opened in a new tab/window via JS, automatically close it after printing dialog closes
                    // Check if history length is 1 (meaning it's likely a new window/tab)
                    if (window.history.length === 1) {
                        window.close();
                    }
                }, 500);
            });
        }
    </script>
</body>
</html>
