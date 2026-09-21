<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étiquette Cintre #{{ $order->ticket_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @page {
            margin: 0;
            size: 76mm auto;
        }
        html, body {
            width: 76mm;
            max-width: 76mm;
            margin: 0 auto;
            padding: 2mm 2.5mm;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            line-height: 1.25;
            overflow: hidden;
        }
        @media print {
            html, body {
                width: 76mm;
                max-width: 76mm;
                margin: 0;
                padding: 1.5mm 2mm;
                overflow: hidden;
            }
            .no-print {
                display: none !important;
            }
            .cintre-tag-card {
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                break-inside: avoid !important;
            }
        }

        .cintre-tag-card {
            border: 2px solid #000;
            border-radius: 6px;
            padding: 2.5mm 2.5mm;
            text-align: left;
            background: #fff;
            page-break-inside: avoid;
            page-break-after: avoid;
            break-inside: avoid;
        }

        /* En-tête Numéro Ticket Géant (Blanc sur Fond Noir) */
        .ticket-no-box {
            font-size: 34px;
            font-weight: 900;
            letter-spacing: 1.5px;
            border: 2px solid #000;
            border-radius: 4px;
            display: block;
            width: 100%;
            padding: 2.5mm 1mm;
            text-align: center;
            line-height: 1;
            white-space: nowrap;
            background: #000;
            color: #fff;
            margin-bottom: 2mm;
        }

        .express-banner {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 1.5mm 0;
            font-weight: 900;
            font-size: 12px;
            margin-bottom: 2mm;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-radius: 3px;
        }

        /* Section Informations Client & Dates */
        .meta-table {
            width: 100%;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2mm;
            border-bottom: 1.5px dashed #000;
            padding-bottom: 1.5mm;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1mm;
        }
        .meta-label {
            color: #222;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: 800;
        }
        .meta-val {
            font-weight: 900;
            text-align: right;
        }
        .badge-payment {
            display: inline-block;
            padding: 0.5mm 2mm;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .badge-paid {
            border: 1.5px solid #000;
            background: #fff;
            color: #000;
        }
        .badge-unpaid {
            background: #000;
            color: #fff;
        }

        /* Liste des Articles */
        .items-list {
            margin-bottom: 2mm;
        }
        .item-row {
            margin-bottom: 1.5mm;
            padding-bottom: 1.5mm;
            border-bottom: 1px dotted #888;
        }
        .item-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .item-title {
            font-size: 12.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .item-subline {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            margin-top: 0.8mm;
        }
        .pieces-highlight {
            background: #000;
            color: #fff;
            padding: 0.4mm 1.8mm;
            border-radius: 3px;
            font-size: 9.5px;
            font-weight: 900;
            white-space: nowrap;
        }

        .item-opts {
            font-size: 9.5px;
            font-weight: 700;
            margin-top: 0.8mm;
            color: #111;
            padding-left: 1.5mm;
        }

        /* Total Cintre (Bas d'étiquette) */
        .total-summary {
            border-top: 2px solid #000;
            margin-top: 2mm;
            padding-top: 1.5mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
        }

        /* Bouton Navigateur pour aperçu manuel */
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
        }
    </style>
</head>
<body>
    <div class="print-btn-container no-print">
        <button onclick="window.print()" class="print-btn">Imprimer l'Étiquette Cintre</button>
    </div>

    <div class="cintre-tag-card">
        <!-- Numéro de ticket géant pour lecture rapide en atelier -->
        <div class="ticket-no-box">#{{ $order->ticket_number }}</div>

        @if($order->is_express)
            <div class="express-banner">!!! EXPRESS !!!</div>
        @endif

        <div class="meta-table">
            <div class="meta-row">
                <span class="meta-label">Client :</span>
                <span class="meta-val">{{ $order->client->name }}</span>
            </div>
            @if($order->client->phone)
            <div class="meta-row">
                <span class="meta-label">Tél :</span>
                <span class="meta-val">{{ $order->client->phone }}</span>
            </div>
            @endif
            <div class="meta-row">
                <span class="meta-label">Dépôt :</span>
                <span class="meta-val">{{ $order->order_date->format('d/m/Y H:i') }}</span>
            </div>
            @if($order->target_delivery_date || $order->due_date)
            <div class="meta-row">
                <span class="meta-label">Prévu le :</span>
                <span class="meta-val">{{ ($order->target_delivery_date ?? $order->due_date)->format('d/m/Y') }}</span>
            </div>
            @endif
            <div class="meta-row" style="margin-top: 1mm;">
                <span class="meta-label">Statut :</span>
                @if($order->is_paid || $order->balance_amount <= 0)
                    <span class="badge-payment badge-paid">PAYÉ</span>
                @else
                    <span class="badge-payment badge-unpaid">RESTE : {{ number_format($order->balance_amount, 0, ',', ' ') }} DA</span>
                @endif
            </div>
        </div>

        <div class="items-list">
            @foreach($itemsSummary as $item)
                <div class="item-row">
                    <div class="item-title">
                        {{ $item['quantity'] > 1 ? $item['quantity'].'x ' : '' }}{{ $item['name'] }}
                    </div>
                    <div class="item-subline">
                        <span>SERVICE: {{ $item['service'] }}</span>
                        @if($item['pieces'] > 1)
                            <span class="pieces-highlight">Nbr Pieces={{ $item['pieces'] }}</span>
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
                        <div class="item-opts">
                            @foreach($opts as $opt)
                                <div>• {{ $opt }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="total-summary">
            <span>TOTAL CINTRE :</span>
            <span>{{ $totalOrderPieces }} PIÈCE{{ $totalOrderPieces > 1 ? 'S' : '' }}</span>
        </div>
    </div>

    <!-- Auto Print Script (Uniquement si ouvert hors Electron) -->
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
