<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étiquettes Cintres {{ $order->ticket_number }}</title>
    <style>
        /* CSS reset & thermal paper configurations */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-weight: bold !important;
        }
        @page {
            margin: 0;
            size: 80mm 50mm;
        }
        html, body {
            width: 100%;
            max-width: 76mm;
            margin: 0 !important;
            margin-left: 0 !important;
            padding: 0 2mm 7mm 0 !important; /* Zero marge a gauche, 0 en haut, 2mm a droite, 7mm en bas */
            padding-left: 0 !important;
            font-family: 'Courier New', Courier, monospace;
            font-size: 9.5px;
            font-weight: bold !important;
            color: #000;
            background: #fff;
            line-height: 1.25;
            zoom: 1 !important;
            transform: scale(1) !important;
            transform-origin: top left !important;
        }

        /* Hide elements on print */
        @media print {
            html, body {
                width: 100%;
                max-width: 76mm;
                margin: 0 !important;
                margin-left: 0 !important;
                padding: 0 2mm 7mm 0 !important; /* Zero marge a gauche */
                padding-left: 0 !important;
                color: #000 !important;
                background: #fff !important;
                font-weight: bold !important;
                zoom: 1 !important;
                transform: scale(1) !important;
                transform-origin: top left !important;
            }
            .no-print {
                display: none !important;
            }
            @if(request('preview'))
                .print-btn-container { display: none !important; }
                body { padding: 3mm 3mm 6mm 3mm !important; }
            @endif
            @page {
                size: 80mm 50mm;
                margin: 0;
            }
            .tag-block {
                margin: 0 !important;
                margin-left: 0 !important;
                padding: 0 !important;
                padding-left: 0 !important;
                width: 100% !important;
                max-width: 76mm !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        /* Hanger Tag Block - Aligned to left */
        .tag-block {
            width: 100%;
            max-width: 76mm;
            margin: 0 !important;
            margin-left: 0 !important;
            padding: 0 !important;
            padding-left: 0 !important;
            text-align: center;
            font-weight: bold !important;
        }

        .ticket-no {
            font-size: 32px;
            font-weight: 900 !important;
            letter-spacing: 2px;
            margin: 0 0 1.5mm 0;
            border: 3px solid #000;
            display: block;
            width: 100%;
            padding: 2mm 1mm;
            text-align: center;
            font-family: Arial, 'Segoe UI', Helvetica, sans-serif;
            box-sizing: border-box;
            line-height: 1;
            white-space: nowrap;
            background: #fff;
            color: #000;
        }

        .express-banner {
            background-color: #000000;
            color: #ffffff;
            text-align: center;
            padding: 1px 0;
            font-weight: 900 !important;
            font-size: 9px;
            margin-bottom: 1.5mm;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .garment-title {
            font-size: 12.5px;
            font-weight: 900 !important;
            margin: 1mm 0 0.8mm 0;
            text-transform: uppercase;
            line-height: 1.15;
            color: #000;
        }

        .service-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 900 !important;
            font-size: 9.5px;
            text-transform: uppercase;
            margin: 0.8mm 0 1.2mm 0;
            color: #000;
        }

        .details-box {
            text-align: left;
            font-size: 9.5px;
            font-weight: 900 !important;
            margin-top: 1.2mm;
            line-height: 1.25;
            color: #000;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
            font-weight: 900 !important;
        }

        .details-row span {
            font-weight: 900 !important;
        }

        /* Options layout (no borders) */
        .tag-options {
            font-size: 9px;
            font-weight: 900 !important;
            margin-top: 0.8mm;
            border: none;
            padding: 0;
            text-align: left;
            background: transparent;
            color: #000;
        }

        .tag-options div {
            font-weight: 900 !important;
        }

        /* Print float controller (visible in browser view) */
        .print-btn-container {
            position: fixed;
            bottom: 10px;
            right: 10px;
            z-index: 100;
        }
        .print-btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 12px;
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
        <button onclick="window.print()" class="print-btn">Imprimer les Étiquettes</button>
    </div>

    <!-- Tags Container - Centered on 80mm x 50mm -->
    <div class="tag-block">
        <!-- Massive Ticket Number for immediate workshop visibility -->
        <div class="ticket-no"><strong>{{ $order->ticket_number }}</strong></div>
        
        @if($order->is_express)
            <div class="express-banner">!!! EXPRESS !!!</div>
        @endif

        @if(count($itemsSummary) === 1)
            @php $item = $itemsSummary[0]; @endphp
            <!-- Garment Description -->
            <div class="garment-title">{{ $item['name'] }}</div>
            <div class="service-line">
                <span>SERVICE: {{ $item['service'] }}</span>
                @if($item['pieces'] > 1)
                    <span>Nbr Pieces={{ $item['pieces'] }}</span>
                @endif
            </div>

            <div class="details-box">
                <div class="details-row">
                    <span>Client:</span>
                    <span class="font-bold">{{ $order->client->name }}</span>
                </div>
                <div class="details-row">
                    <span>Dépôt:</span>
                    <span>{{ $order->order_date->format('d/m/Y') }}</span>
                </div>
            </div>

            <!-- Item Options (Colors, defects, stains) -->
            @php
                $opts = [];
                if (!empty($item['colors'])) $opts[] = 'COULEUR: ' . implode('/', $item['colors']);
                if (!empty($item['defects'])) $opts[] = 'DÉFAUT: ' . implode('/', $item['defects']);
                if (!empty($item['stains'])) $opts[] = 'TACHE: ' . implode('/', $item['stains']);
                if (!empty($item['notes'])) $opts[] = 'NOTE: ' . $item['notes'];
            @endphp

            @if(count($opts) > 0)
                <div class="tag-options">
                    @foreach($opts as $opt)
                        <div>• {{ $opt }}</div>
                    @endforeach
                </div>
            @endif
        @else
            <!-- Multi-articles dans la commande -->
            @foreach($itemsSummary as $item)
                <div style="{{ !$loop->first ? 'margin-top: 1.5mm; border-top: 1px dashed #000; padding-top: 1mm;' : '' }}">
                    <div class="garment-title" style="font-size: 12px;">{{ $item['quantity'] > 1 ? $item['quantity'].'X ' : '' }}{{ $item['name'] }}</div>
                    <div class="service-line">
                        <span>SERVICE: {{ $item['service'] }}</span>
                        @if($item['pieces'] > 1)
                            <span>Nbr Pieces={{ $item['pieces'] }}</span>
                        @endif
                    </div>
                    @php
                        $opts = [];
                        if (!empty($item['colors'])) $opts[] = 'COULEUR: ' . implode('/', $item['colors']);
                        if (!empty($item['defects'])) $opts[] = 'DÉFAUT: ' . implode('/', $item['defects']);
                        if (!empty($item['stains'])) $opts[] = 'TACHE: ' . implode('/', $item['stains']);
                        if (!empty($item['notes'])) $opts[] = 'NOTE: ' . $item['notes'];
                    @endphp
                    @if(count($opts) > 0)
                        <div class="tag-options">
                            @foreach($opts as $opt)
                                <div>• {{ $opt }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="details-box" style="margin-top: 1.5mm; border-top: 1px dotted #555; padding-top: 1mm;">
                <div class="details-row">
                    <span>Client:</span>
                    <span class="font-bold">{{ $order->client->name }}</span>
                </div>
                <div class="details-row">
                    <span>Dépôt:</span>
                    <span>{{ $order->order_date->format('d/m/Y') }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Auto Print Script -->
    <script>
        const isElectron = navigator.userAgent.toLowerCase().includes('electron') || !!window.posDesktop;
        if (window.self === window.top && !isElectron) {
            window.addEventListener('afterprint', () => {
                setTimeout(() => {
                    if (window.history.length === 1) {
                        window.close();
                    }
                }, 500);
            });

            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.focus();
                    window.print();
                }, 400);
            });
        }
    </script>
</body>
</html>
