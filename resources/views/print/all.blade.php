<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impression Complète #{{ $order->ticket_number }}</title>
    <style>
        /* CSS reset & thermal paper configurations */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            width: 100%;
            max-width: 80mm;
            margin: 0 !important;
            margin-left: 0 !important;
            padding: 3mm 2mm 3mm 0 !important; /* Zero marge a gauche */
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            line-height: 1.35;
        }

        /* Hide elements on print */
        @media print {
            html, body {
                width: 100%;
                max-width: 80mm;
                margin: 0 !important;
                margin-left: 0 !important;
                padding: 2mm 2mm 2mm 0 !important; /* Zero marge a gauche */
                color: #000 !important;
                background: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 0;
                size: 80mm auto;
            }
            .page-block {
                page-break-after: always;
                border-bottom: 2px dashed #000;
                padding: 6mm 4mm !important;
            }
            .page-block:last-child {
                page-break-after: avoid;
                border-bottom: none;
            }
            .tag-block {
                border-bottom: none !important;
                padding: 4mm 2mm !important;
            }
        }

        /* Ticket & tags styling */
        .page-block {
            padding: 6mm 2mm;
            border-bottom: 2px dashed #000;
        }
        .page-block:last-child {
            border-bottom: none;
        }

        /* Header / text utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 3mm 0;
        }

        /* Ticket specifics */
        .info-section {
            font-size: 11px;
            margin: 3mm 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 3mm 0;
        }
        .items-table th, .items-table td {
            text-align: left;
            padding: 1mm 0;
        }
        .item-options {
            font-size: 9px;
            padding-left: 2mm;
            font-style: italic;
        }
        .totals-section {
            font-size: 11px;
            margin-top: 3mm;
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
        .footer {
            margin-top: 6mm;
            font-size: 9px;
            line-height: 1.3;
            text-align: center;
        }
        .barcode-text {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 5px;
            margin: 4mm 0 2mm 0;
        }

        .qrcode-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 2mm 0 3mm 0;
        }
        .qrcode-container svg {
            display: block;
            margin: 0 auto;
        }

        /* Tags specifics */
        .tag-block {
            text-align: center;
            padding: 4mm 2mm;
            border-bottom: none !important;
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
        .tag-options {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2mm;
            border: none;
            padding: 1mm 0;
            text-align: left;
            background: transparent;
        }

        /* Browser Floating button */
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
    </style>
</head>
<body>

    <!-- Browser Action Button (Hidden during print) -->
    <div class="print-btn-container no-print">
        <button onclick="window.print()" class="print-btn">Imprimer Tout</button>
    </div>

    <!-- PART 1: Client Receipt Ticket -->
    <div class="page-block">
        <div class="header text-center">
            <h1 style="font-size: 22px; font-weight: 900; letter-spacing: 1px; margin-bottom: 1mm; display: inline-flex; align-items: center; justify-content: center; width: 100%;">
                LE PARAD<svg style="width: 20px; height: 20px; margin: 0 1px; display: inline-block; vertical-align: middle;" fill="none" viewBox="0 0 24 24" stroke="#000" stroke-width="2.5"><rect x="4" y="3" width="16" height="18" rx="2" /><line x1="4" y1="7" x2="20" y2="7" /><circle cx="7" cy="5" r="0.75" fill="#000" /><circle cx="10" cy="5" r="0.75" fill="#000" /><circle cx="13" cy="5" r="0.75" fill="#000" /><circle cx="12" cy="14" r="4" /><circle cx="12" cy="14" r="2.5" stroke-dasharray="3 2" /></svg>U
            </h1>
            <p>Pressing & Blanchisserie & Nettoyage à Sec</p>
            <p>Tél : 0561 99 88 01</p>
            <p style="font-size: 10px;">Facebook : pressing blanchisserie le paradou</p>
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
                <span>DATE:</span>
                <span>{{ $order->order_date->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span>CAISSIER:</span>
                <span>{{ $order->user->name ?? 'Système' }}</span>
            </div>
            <div class="info-row">
                <span>CLIENT:</span>
                <span class="font-bold">{{ $order->client->name }}</span>
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
                    <th>ARTICLE</th>
                    <th class="text-center" style="width: 10mm;">QTÉ</th>
                    <th class="text-right" style="width: 20mm;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                    @php
                        $isKilo = $item->service_id === 4 || str_contains(strtolower($item->service->name), 'kilo');
                        $isCarpet = $item->isCarpet();
                    @endphp
                    <tr>
                        <td>
                            <span class="font-bold">{{ $item->garmentItem->name }}</span> 
                            <span style="font-size: 9px; text-transform: uppercase;">({{ $item->service->name }})</span>
                            @if($isKilo && $item->pieces)
                                <span style="font-size: 9px; font-weight: bold; color: #555;">[{{ $item->pieces }} pcs]</span>
                            @elseif($isCarpet && $item->pieces)
                                <span style="font-size: 9px; font-weight: bold; color: #555;">[{{ $item->pieces }} pcs]</span>
                            @endif

                            @if($isCarpet)
                                @if($item->is_measured && $item->area)
                                    <div style="font-size: 9px; font-weight: bold; color: #222;">
                                        📏 {{ $item->length }}m × {{ $item->width }}m = {{ $item->area }} m² ({{ number_format($item->unit_price, 0, '.', '') }} DA/m²)
                                    </div>
                                @else
                                    <div style="font-size: 9px; font-style: italic; color: #555;">
                                        📏 {{ number_format($item->unit_price, 0, '.', '') }} DA/m² (Métrage à l'atelier)
                                    </div>
                                @endif
                            @endif
                            
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
                                    * {!! implode("<br>* ", $opts) !!}
                                </div>
                            @endif
                        </td>
                        <td class="text-center font-bold">
                            @if($isKilo)
                                {{ number_format($item->quantity, 2) }} kg
                            @elseif($isCarpet)
                                @if($item->is_measured && $item->area)
                                    {{ number_format($item->area, 2) }} m²
                                @else
                                    {{ $item->pieces ?? 1 }} pc
                                @endif
                            @else
                                {{ floatval($item->quantity) }}
                            @endif
                        </td>
                        <td class="text-right font-bold">
                            @if($isCarpet && !$item->is_measured)
                                <span style="font-size: 9px; font-style: italic; color: #666;">À mesurer</span>
                            @else
                                {{ number_format($item->total_price, 0, '.', '') }} DA
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        @php
            $totalPieces = $order->orderItems->sum(function($i) {
                $isKilo = $i->service_id === 4 || ($i->service && str_contains(strtolower($i->service->name), 'kilo'));
                if ($isKilo) return $i->pieces ?: 1;
                if ($i->isCarpet()) return $i->pieces ?: 1;
                return $i->pieces ?: intval(ceil($i->quantity));
            });
        @endphp
        <div class="info-section" style="margin: 2mm 0;">
            <div class="info-row font-bold" style="font-size: 11px;">
                <span>TOTAL ARTICLES DÉPOSÉS :</span>
                <span>{{ $totalPieces }} {{ $totalPieces > 1 ? 'pièces' : 'pièce' }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Billing Summary -->
        @php
            $totalBrut = $order->orderItems->sum('total_price');
            $discountAmount = $order->discount_amount;
        @endphp
        <div class="totals-section">
            @if($order->total_weight && $order->total_weight > 0)
                <div class="totals-row">
                    <span class="font-bold">Poids total (Au Kilo):</span>
                    <span class="font-bold">{{ number_format($order->total_weight, 2) }} kg ({{ round($order->total_weight * 1000) }} g)</span>
                </div>
            @endif
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
                <span>Total:</span>
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
            <div style="margin-top: 3mm; font-size: 10px; background: #eee; padding: 1.5mm; font-style: italic;">
                <strong>Note :</strong> {{ $order->remarks }}
            </div>
        @endif

        <div class="info-section text-center" style="margin-top: 5mm;">
            <p class="font-bold">LIVRAISON PRÉVUE LE :</p>
            <p class="font-bold" style="font-size: 15px; margin-top: 1mm;">
                {{ $order->target_delivery_date->format('d/m/Y') }}
            </p>
        </div>

        <!-- Footer terms -->
        <div class="footer">
            <p class="footer-clause font-bold">⚠️ IMPORTANT : Présentation obligatoire de ce ticket lors du retrait des vêtements.</p>
            <p class="footer-clause">1. Les vêtements doivent être retirés au maximum sous 30 jours.</p>
            <p class="footer-clause">2. En cas de perte, le dédommagement se fera selon la réglementation.</p>
            <p class="text-center font-bold" style="margin-top: 4mm; font-size: 10px;">Merci pour votre confiance !</p>
            
            <!-- Simulated barcode text -->
            <div class="barcode-text text-center font-mono">{{ $order->ticket_number }}</div>

            <!-- Code QR du numéro de commande / ticket -->
            <div class="qrcode-container text-center">
                {!! QrCode::size(110)->margin(1)->generate($order->ticket_number) !!}
            </div>
        </div>
    </div>

    <!-- PART 2: Single Adapted Hanger Tag -->
    <div class="page-block tag-block">
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
            @foreach($itemsSummary as $item)
                <div style="{{ !$loop->first ? 'margin-top: 2.5mm; border-top: 1px dashed #000; padding-top: 2mm;' : '' }}">
                    <div class="garment-title">{{ $item['quantity'] > 1 ? $item['quantity'].'X ' : '' }}{{ $item['name'] }}</div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 11px; text-transform: uppercase; margin: 1.5mm 0 2mm 0;">
                        <span>SERVICE: {{ $item['service'] }}</span>
                        @if($item['pieces'] > 1)
                            <span>NBR PIECES={{ $item['pieces'] }}</span>
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
                }, 500);
            });
        }
    </script>
</body>
</html>
