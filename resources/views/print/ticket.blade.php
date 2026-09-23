<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $order->ticket_number }}</title>
    <style>
        /* CSS reset & thermal typography */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-weight: bold !important;
        }

        /* Screen Preview Styling (Browser) */
        @media screen {
            html {
                background: #0f172a;
                overflow-y: scroll !important;
                scrollbar-width: thin;
                scrollbar-color: #64748b #0f172a;
            }
            body {
                background: #0f172a;
                color: #000;
                min-height: 100vh;
                height: auto !important;
                overflow-y: visible !important;
                margin: 0 !important;
                padding: 68px 15px 40px 15px !important;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                font-family: 'Courier New', Courier, monospace;
            }

            /* Custom Visible Scrollbar (Chrome, Edge, Safari, Opera) */
            ::-webkit-scrollbar {
                width: 14px !important;
                height: 14px !important;
                display: block !important;
            }
            ::-webkit-scrollbar-track {
                background: #0f172a !important;
                border-left: 1px solid rgba(255, 255, 255, 0.12) !important;
            }
            ::-webkit-scrollbar-thumb {
                background: #475569 !important;
                border-radius: 7px !important;
                border: 3px solid #0f172a !important;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #64748b !important;
            }
            ::-webkit-scrollbar-thumb:active {
                background: #94a3b8 !important;
            }

            @if(request('preview'))
                .preview-toolbar { display: none !important; }
                html {
                    background: #ffffff !important;
                    overflow-y: scroll !important;
                    scrollbar-width: thin !important;
                    scrollbar-color: #94a3b8 #f1f5f9 !important;
                }
                body { 
                    padding: 8px 10px 30px 10px !important; 
                    background: #ffffff !important; 
                    min-height: 100% !important;
                    height: auto !important;
                    overflow-y: visible !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    display: block !important;
                }
                .ticket-paper {
                    box-shadow: none !important;
                    padding: 0 !important;
                    width: 100% !important;
                    max-width: 100% !important;
                }
                ::-webkit-scrollbar {
                    width: 8px !important;
                    display: block !important;
                }
                ::-webkit-scrollbar-track {
                    background: #f1f5f9 !important;
                    border: none !important;
                }
                ::-webkit-scrollbar-thumb {
                    background: #94a3b8 !important;
                    border-radius: 4px !important;
                    border: none !important;
                }
                ::-webkit-scrollbar-thumb:hover {
                    background: #64748b !important;
                }
            @endif
            .ticket-paper {
                background: #ffffff;
                width: 76mm;
                max-width: 100%;
                padding: 6mm 4.5mm 8mm 4.5mm;
                border-radius: 8px;
                box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.1);
            }
            .preview-toolbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 52px;
                background: rgba(15, 23, 42, 0.96);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.12);
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 16px;
                z-index: 99999;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            .toolbar-left, .toolbar-right {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .toolbar-title {
                color: #f8fafc;
                font-size: 13px;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .toolbar-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 7px 13px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 700;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.15s ease;
                border: none;
            }
            .btn-back {
                background: #334155;
                color: #f1f5f9;
            }
            .btn-back:hover {
                background: #475569;
                color: #ffffff;
            }
            .btn-pdf {
                background: #0284c7;
                color: #ffffff;
            }
            .btn-pdf:hover {
                background: #0369a1;
            }
            .btn-print {
                background: #4f46e5;
                color: #ffffff;
                box-shadow: 0 2px 8px rgba(79, 70, 229, 0.35);
            }
            .btn-print:hover {
                background: #4338ca;
            }
        }

        /* Thermal Print Styling (Printer) */
        @media print {
            .no-print, .preview-toolbar {
                display: none !important;
            }
            html, body {
                width: 100% !important;
                max-width: 72mm !important;
                margin: 0 !important;
                margin-left: 0 !important;
                padding: 0 3mm 8mm 5px !important;
                color: #000 !important;
                background: #fff !important;
                font-weight: bold !important;
                font-family: 'Courier New', Courier, monospace;
            }
            .ticket-paper {
                width: 100% !important;
                max-width: 72mm !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: #fff !important;
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

    </style>
</head>
<body>

    <!-- Top Preview Bar (Screen view only) -->
    <header class="preview-toolbar no-print">
        <div class="toolbar-left">
            <button type="button" onclick="historyBackOrOrders()" class="toolbar-btn btn-back">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Retour</span>
            </button>
        </div>
        <div class="toolbar-title">
            <span>Aperçu Reçu Client</span>
            <span style="color: #38bdf8; font-family: monospace;">#{{ $order->ticket_number }}</span>
            <span style="color: #94a3b8; font-weight: normal; font-size: 11px;">({{ $order->client ? $order->client->name : 'Passage' }})</span>
        </div>
        <div class="toolbar-right">
            <a href="{{ route('orders.public-receipt-pdf', $order->ticket_number) }}?download=1" class="toolbar-btn btn-pdf" target="_blank" title="Télécharger le fichier PDF du reçu">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Télécharger PDF</span>
            </a>
            <button type="button" onclick="window.print()" class="toolbar-btn btn-print">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Imprimer</span>
            </button>
        </div>
    </header>

    <!-- Ticket Paper Container -->
    <div class="ticket-paper">

    <!-- Ticket Container -->
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
                        @php
                            $piecesCount = $item->pieces ?: ($item->garmentItem->pieces_count ?? 1);
                        @endphp
                        @if($isKilo && $item->pieces)
                            <span style="font-size: 9px; font-weight: bold; color: #555;">[{{ $item->pieces }} pcs]</span>
                        @elseif($isCarpet && $item->pieces)
                            <span style="font-size: 9px; font-weight: bold; color: #555;">[{{ $item->pieces }} pcs]</span>
                        @elseif($piecesCount > 1)
                            <span style="font-size: 9px; font-weight: bold; color: #333;">[{{ $piecesCount }} pièces]</span>
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
                                * {{ implode("\n* ", $opts) }}
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
            {{ $order->ticket_number }}
        </div>

        <!-- Code QR du reçu digital client -->
        <div class="qrcode-container text-center">
            @php
                $receiptUrl = \App\Http\Controllers\TicketPrintController::getPublicReceiptUrl($order->ticket_number, true);
            @endphp
            {!! QrCode::size(110)->margin(1)->generate($receiptUrl) !!}
            <div style="font-size: 8px; font-weight: normal; margin-top: 3px; color: #333; font-family: sans-serif;">
                Scannez pour télécharger le reçu PDF
            </div>
        </div>
    </div>
    </div> <!-- /ticket-paper -->

    <!-- Auto Print & Navigation Scripts -->
    <script>
        function historyBackOrOrders() {
            if (window.opener) {
                window.close();
            } else if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = "{{ route('orders.index') }}";
            }
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                historyBackOrOrders();
            }
        });

        const isElectron = navigator.userAgent.toLowerCase().includes('electron') || !!window.posDesktop;
        @if(request('autoprint'))
            if (window.self === window.top && !isElectron) {
                window.addEventListener('afterprint', () => {
                    setTimeout(() => {
                        if (window.opener && window.history.length === 1) {
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
        @endif
    </script>
</body>
</html>
