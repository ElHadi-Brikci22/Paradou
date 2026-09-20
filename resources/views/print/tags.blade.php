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
                page-break-after: always;
                border-bottom: none;
                padding: 4mm 2mm !important;
            }
            .tag-block:last-child {
                page-break-after: avoid;
                border-bottom: none;
            }
        }

        /* Hanger Tag Styling */
        .tag-block {
            padding: 4mm 2mm;
            border-bottom: none;
            text-align: center;
        }
        .tag-block:last-child {
            border-bottom: none;
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

    <!-- Tags Container -->
    @foreach($tags as $index => $tag)
        <div class="tag-block">
            <!-- Massive Ticket Number for immediate workshop visibility -->
            <div class="ticket-no"><strong>{{ $order->ticket_number }}</strong></div>
            
            @if($order->is_express)
                <div style="background-color: #000000; color: #ffffff; text-align: center; padding: 2px 0; font-weight: 900; font-size: 11px; margin: 1mm 0; text-transform: uppercase; letter-spacing: 1px;">
                    !!! EXPRESS !!!
                </div>
            @endif

            <!-- Garment Description -->
            <div class="garment-title">{{ $tag['garment_name'] }}</div>
            <p class="font-bold" style="text-transform: uppercase; font-size: 11px;">
                SERVICE: {{ $tag['service_name'] }}
            </p>

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
                if ($tag['colors'] && count($tag['colors']) > 0) $opts[] = 'COULEUR: ' . implode('/', $tag['colors']);
                if ($tag['defects'] && count($tag['defects']) > 0) $opts[] = 'DÉFAUT: ' . implode('/', $tag['defects']);
                if ($tag['stains'] && count($tag['stains']) > 0) $opts[] = 'TACHE: ' . implode('/', $tag['stains']);
                if ($tag['notes']) $opts[] = 'NOTE: ' . $tag['notes'];
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

    <!-- Auto Print Script -->
    <script>
        if (window.self === window.top) {
            // Close window ONLY after user finishes interacting with print dialog (print or cancel)
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
                }, 500);
            });
        }
    </script>
</body>
</html>
