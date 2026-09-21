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
        }
        body {
            width: 80mm;
            margin: 0 auto;
            padding: 4mm 3mm;
            font-family: 'Courier New', Courier, monospace;
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
            .tag-block {
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                border-bottom: none;
                padding: 2mm 1mm !important;
            }
        }

        /* Hanger Tag Styling */
        .tag-block {
            padding: 4mm 2mm;
            border-bottom: none;
            text-align: center;
        }

        .ticket-no {
            font-size: 50px;
            font-weight: 800;
            letter-spacing: 5px;
            margin: 2mm 0 4mm 0;
            border: 3px solid #000;
            display: block;
            width: 100%;
            padding: 8mm 1mm;
            text-align: center;
            font-family: Arial, 'Segoe UI', Helvetica, sans-serif;
            box-sizing: border-box;
            line-height: 1;
            white-space: nowrap;
        }

        .garment-title {
            font-size: 16px;
            font-weight: bold;
            margin: 2mm 0;
            text-transform: uppercase;
        }

        .details-box {
            text-align: left;
            font-size: 11px;
            margin-top: 2mm;
            line-height: 1.3;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
        }

        /* Options layout (no borders) */
        .tag-options {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2mm;
            border: none;
            padding: 1mm 0;
            text-align: left;
            background: transparent;
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
        <button onclick="window.print()" class="print-btn">Imprimer les Étiquettes</button>
    </div>

    <!-- Tags Container - Exactly 1 single tag block -->
    <div class="tag-block">
        <!-- Massive Ticket Number for immediate workshop visibility -->
        <div class="ticket-no"><strong>{{ $order->ticket_number }}</strong></div>
        
        @if($order->is_express)
            <div style="background-color: #000000; color: #ffffff; text-align: center; padding: 2px 0; font-weight: 900; font-size: 11px; margin: 1mm 0; text-transform: uppercase; letter-spacing: 1px;">
                !!! EXPRESS !!!
            </div>
        @endif

        @if(count($itemsSummary) === 1)
            @php $item = $itemsSummary[0]; @endphp
            <!-- Garment Description -->
            <div class="garment-title">{{ $item['name'] }}</div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 11px; text-transform: uppercase; margin: 1.5mm 0 2mm 0;">
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
                <div style="{{ !$loop->first ? 'margin-top: 2.5mm; border-top: 1px dashed #000; padding-top: 2mm;' : '' }}">
                    <div class="garment-title">{{ $item['quantity'] > 1 ? $item['quantity'].'X ' : '' }}{{ $item['name'] }}</div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 11px; text-transform: uppercase; margin: 1.5mm 0 2mm 0;">
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
                        <div class="tag-options" style="margin-top: 1mm;">
                            @foreach($opts as $opt)
                                <div>• {{ $opt }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="details-box" style="margin-top: 3mm; border-top: 1px dotted #555; padding-top: 2mm;">
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
