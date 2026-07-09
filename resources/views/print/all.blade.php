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
            .page-block {
                page-break-after: always;
                border-bottom: 2px dashed #000;
                padding: 6mm 4mm !important;
            }
            .page-block:last-child {
                page-break-after: avoid;
                border-bottom: none;
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

        /* Tags specifics */
        .tag-block {
            text-align: center;
            padding: 6mm 2mm;
        }
        .ticket-no {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 1px;
            margin-bottom: 1mm;
            border: 2px solid #000;
            display: inline-block;
            padding: 1mm 4mm;
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
            font-size: 10px;
            font-weight: bold;
            margin-top: 2mm;
            border: 1px solid #000;
            padding: 1.5mm;
            text-align: left;
            background: #f0f0f0;
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
            <div class="info-row">
                <span>CODE CLIENT:</span>
                <span class="font-bold">{{ $order->client->code }}</span>
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
                                    * {!! implode("<br>* ", $opts) !!}
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
            <div class="barcode-text text-center font-mono">*{{ $order->ticket_number }}*</div>
        </div>
    </div>

    <!-- PART 2: Hanger Tags -->
    @foreach($tags as $index => $tag)
        <div class="page-block tag-block">
            <div class="ticket-no">#{{ $order->ticket_number }}</div>
            
            @if($order->is_express)
                <div style="background-color: #000000; color: #ffffff; text-align: center; padding: 2px 0; font-weight: 900; font-size: 11px; margin: 1mm 0; text-transform: uppercase; letter-spacing: 1px;">
                    !!! EXPRESS !!!
                </div>
            @endif
            <p class="font-bold" style="font-size: 14px; margin-top: 1mm;">
                Vêtement {{ $tag['index'] }} sur {{ $tag['total_qty'] }}
            </p>
            <div class="garment-title">{{ $tag['garment_name'] }}</div>
            <p class="font-bold" style="text-transform: uppercase; font-size: 11px;">
                SERVICE: {{ $tag['service_name'] }}
            </p>

            <div class="details-box">
                <div class="info-row">
                    <span>Client:</span>
                    <span class="font-bold">{{ $order->client->name }}</span>
                </div>
                <div class="info-row">
                    <span>Code Client:</span>
                    <span>{{ $order->client->code }}</span>
                </div>
                <div class="info-row">
                    <span>Dépôt:</span>
                    <span>{{ $order->order_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row" style="margin-top: 0.5mm;">
                    <span class="font-bold">LIVRAISON:</span>
                    <span class="font-bold">{{ $order->target_delivery_date->format('d/m/Y') }}</span>
                </div>
            </div>

            @php
                $tagOpts = [];
                if ($tag['colors'] && count($tag['colors']) > 0) $tagOpts[] = 'COULEUR: ' . implode('/', $tag['colors']);
                if ($tag['defects'] && count($tag['defects']) > 0) $tagOpts[] = 'DÉFAUT: ' . implode('/', $tag['defects']);
                if ($tag['stains'] && count($tag['stains']) > 0) $tagOpts[] = 'TACHE: ' . implode('/', $tag['stains']);
                if ($tag['notes']) $tagOpts[] = 'NOTE: ' . $tag['notes'];
            @endphp

            @if(count($tagOpts) > 0)
                <div class="tag-options">
                    @foreach($tagOpts as $opt)
                        <div>• {{ $opt }}</div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    <!-- Auto Print Script -->
    <script>
        if (window.self === window.top) {
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                    if (window.history.length === 1) {
                        window.close();
                    }
                }, 500);
            });
        }
    </script>
</body>
</html>
