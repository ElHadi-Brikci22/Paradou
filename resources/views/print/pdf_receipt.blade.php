<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu Ticket #{{ $order->ticket_number }} - Le Paradou</title>
    <style>
        @page {
            margin: 4mm 3mm;
            size: 80mm 230mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            width: 100%;
            max-width: 74mm;
            margin: 0 auto;
            padding: 3mm 2mm;
            font-size: 11px;
            line-height: 1.35;
            color: #000000;
            background: #ffffff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        .header {
            margin-bottom: 4mm;
            text-align: center;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1px;
            margin-bottom: 1mm;
        }
        .header p {
            font-size: 10px;
            line-height: 1.3;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 3mm 0;
        }
        .double-divider {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin: 3mm 0;
        }

        .info-table {
            width: 100%;
            margin-bottom: 2mm;
            font-size: 10.5px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 1mm 0;
            vertical-align: top;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 2mm 0;
        }
        .items-table th {
            border-bottom: 1px dashed #000;
            padding-bottom: 1.5mm;
            font-size: 10.5px;
            text-align: left;
        }
        .items-table td {
            padding: 1.5mm 0;
            vertical-align: top;
        }
        .items-table .item-desc {
            font-weight: bold;
        }
        .items-table .item-sub {
            font-size: 9px;
            color: #333;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-top: 2mm;
        }
        .totals-table td {
            padding: 1mm 0;
        }
        .totals-table .balance-row td {
            font-size: 13px;
            font-weight: bold;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 2mm 0;
        }

        .footer {
            margin-top: 4mm;
            font-size: 8.5px;
            text-align: center;
            line-height: 1.3;
        }
        .barcode-box {
            margin: 3mm 0 1mm 0;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 4px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #000;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 1mm;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>LE PARADOU</h1>
        <p>Pressing & Blanchisserie & Nettoyage à Sec</p>
        <p>Tél : 0561 99 88 01</p>
        <p>Facebook : pressing blanchisserie le paradou</p>
        
        @if($order->is_express)
            <div class="badge" style="background: #000; color: #fff; margin-top: 2mm;">
                *** TICKET EXPRESS ***
            </div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- Meta Info -->
    <table class="info-table">
        <tr>
            <td class="text-left">TICKET N°:</td>
            <td class="text-right font-bold">#{{ $order->ticket_number }}</td>
        </tr>
        <tr>
            <td class="text-left">DATE DÉPÔT:</td>
            <td class="text-right">{{ $order->order_date ? $order->order_date->format('d/m/Y H:i') : '-' }}</td>
        </tr>
        <tr>
            <td class="text-left font-bold">LIVRAISON PRÉVUE:</td>
            <td class="text-right font-bold">{{ $order->target_delivery_date ? $order->target_delivery_date->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="text-left">CAISSIER:</td>
            <td class="text-right">{{ $order->user ? $order->user->name : 'Caisse' }}</td>
        </tr>
        <tr>
            <td class="text-left">STATUT:</td>
            <td class="text-right font-bold">
                @if($order->status === 'delivered')
                    LIVRÉ
                @elseif($order->status === 'ready')
                    PRÊT POUR RETRAIT
                @else
                    EN COURS
                @endif
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Client Info -->
    <table class="info-table">
        <tr>
            <td class="text-left">CLIENT:</td>
            <td class="text-right font-bold">{{ $order->client ? $order->client->name : 'Client Passage' }}</td>
        </tr>
        @if($order->client && $order->client->phone)
        <tr>
            <td class="text-left">TÉLÉPHONE:</td>
            <td class="text-right">{{ $order->client->phone }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 55%;">ARTICLE</th>
                <th class="text-center" style="width: 15%;">QTÉ</th>
                <th class="text-right" style="width: 30%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPieces = 0; @endphp
            @foreach($order->orderItems as $item)
                @php
                    $garment = $item->garmentItem;
                    $piecesPerItem = $garment ? max(1, intval($garment->pieces_count ?: 1)) : 1;
                    $qty = max(1, intval(ceil($item->quantity)));
                    $pieces = !empty($item->pieces) && intval($item->pieces) > 0 ? intval($item->pieces) : ($qty * $piecesPerItem);
                    $totalPieces += $pieces;
                @endphp
                <tr>
                    <td>
                        <div class="item-desc">{{ $garment ? $garment->name : 'Article' }}</div>
                        <div class="item-sub">
                            {{ $item->service ? $item->service->name : 'Service' }}
                            @if($pieces > 1) • ({{ $pieces }} pièces) @endif
                        </div>
                        @if($item->notes)
                            <div class="item-sub">Note: {{ $item->notes }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ floatval($item->quantity) }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total_price, 0, '.', ' ') }} DA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div style="font-size: 10px; margin-bottom: 2mm;">
        TOTAL ARTICLES DÉPOSÉS : <strong>{{ $totalPieces }} pièce(s)</strong>
    </div>

    <!-- Totals -->
    <table class="totals-table">
        <tr>
            <td class="text-left">Total:</td>
            <td class="text-right font-bold">{{ number_format($order->total_amount, 0, '.', ' ') }} DA</td>
        </tr>
        <tr>
            <td class="text-left">Acompte versé:</td>
            <td class="text-right">{{ number_format($order->paid_amount, 0, '.', ' ') }} DA</td>
        </tr>
        <tr class="balance-row">
            <td class="text-left">RESTE À PAYER:</td>
            <td class="text-right">{{ number_format($order->balance_amount, 0, '.', ' ') }} DA</td>
        </tr>
    </table>

    <!-- Barcode simulation -->
    <div class="barcode-box">
        {{ $order->ticket_number }}
    </div>

    <!-- Footer Conditions -->
    <div class="footer">
        <p style="margin-bottom: 1.5mm;">
            CONDITIONS : Veuillez conserver ce reçu pour le retrait. Tout article non retiré après 30 jours n'est plus garanti.
        </p>
        <p class="font-bold">*** MERCI DE VOTRE VISITE ***</p>
        <p style="margin-top: 1mm; font-size: 7.5px; color: #555;">Reçu digital généré par Paradou POS</p>
    </div>

</body>
</html>
