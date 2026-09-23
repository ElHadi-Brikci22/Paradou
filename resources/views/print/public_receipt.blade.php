<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu Ticket #{{ $order->ticket_number }} - Le Paradou</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN for standalone public client receipt) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    
    <style>
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
        }
        .receipt-jagged-bottom {
            background-image: radial-gradient(circle, transparent 65%, #ffffff 66%);
            background-size: 14px 14px;
            background-repeat: repeat-x;
        }
    </style>
</head>
<body class="min-h-full py-6 px-4 sm:px-6 lg:px-8 flex flex-col items-center justify-center font-sans antialiased text-slate-800">

    <!-- Top floating action bar -->
    <div class="w-full max-w-md mb-4 flex items-center justify-between no-print">
        <a href="tel:0561998801" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-white shadow-sm border border-slate-200 text-xs font-bold text-slate-700 hover:text-indigo-600 transition-colors">
            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            <span>0561 99 88 01</span>
        </a>

        <div class="flex items-center space-x-2">
            <a href="{{ route('orders.public-receipt-pdf', $order->ticket_number) }}?download=1" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white shadow-sm text-xs font-bold transition-all transform active:scale-95">
                <svg class="h-4 w-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Télécharger PDF</span>
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/20 text-xs font-bold transition-all transform active:scale-95 cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Imprimer</span>
            </button>
        </div>
    </div>

    <!-- Digital Receipt Card -->
    <div class="receipt-card w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden transition-all">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-br from-indigo-900 via-indigo-850 to-slate-900 p-6 text-center text-white relative">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 mb-3 shadow-inner">
                <svg class="h-6 w-6 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <rect x="4" y="3" width="16" height="18" rx="2" />
                    <line x1="4" y1="7" x2="20" y2="7" />
                    <circle cx="7" cy="5" r="0.75" fill="currentColor" />
                    <circle cx="10" cy="5" r="0.75" fill="currentColor" />
                    <circle cx="13" cy="5" r="0.75" fill="currentColor" />
                    <circle cx="12" cy="14" r="4" />
                    <circle cx="12" cy="14" r="2.5" stroke-dasharray="3 2" />
                </svg>
            </div>
            
            <h1 class="text-xl font-black font-display tracking-wider uppercase">LE PARADOU</h1>
            <p class="text-xs text-indigo-200 font-medium mt-0.5">Pressing & Blanchisserie & Nettoyage à Sec</p>
            <p class="text-[11px] text-slate-300 font-mono mt-1">Facebook : pressing blanchisserie le paradou</p>

            <!-- Express Tag if applicable -->
            @if($order->is_express)
                <div class="mt-3 inline-block px-3 py-1 rounded-full bg-rose-500 text-white font-black text-xs uppercase tracking-widest shadow-md">
                    ⚡ TICKET EXPRESS
                </div>
            @endif
        </div>

        <!-- Ticket Status Pill -->
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Ticket N°</span>
                <span class="text-lg font-black font-mono text-indigo-600">#{{ $order->ticket_number }}</span>
            </div>
            
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Statut</span>
                @if($order->status === 'ready')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Prêt pour retrait
                    </span>
                @elseif($order->status === 'delivered')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-300">
                        Remis au client
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                        En cours de traitement
                    </span>
                @endif
            </div>
        </div>

        <!-- Meta info (Client, dates) -->
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider mb-0.5">Client</span>
                    <span class="font-bold text-slate-900 block truncate">{{ $order->client ? $order->client->name : 'Client Passage' }}</span>
                    @if($order->client && $order->client->phone)
                        <span class="text-[10px] text-slate-500 font-mono">{{ $order->client->phone }}</span>
                    @endif
                </div>

                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider mb-0.5">Retrait prévu</span>
                    <span class="font-bold text-indigo-700 block">{{ $order->target_delivery_date ? $order->target_delivery_date->format('d/m/Y') : '-' }}</span>
                    <span class="text-[10px] text-slate-400">Dépôt: {{ $order->order_date ? $order->order_date->format('d/m/Y') : '-' }}</span>
                </div>
            </div>

            <!-- Articles Table -->
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2 flex items-center justify-between">
                    <span>Articles Déposés</span>
                    <span class="text-[10px] font-mono text-slate-500">{{ count($order->orderItems) }} ligne(s)</span>
                </h3>

                <div class="border border-slate-100 rounded-2xl overflow-hidden divide-y divide-slate-100">
                    @foreach($order->orderItems as $item)
                        <div class="p-3 flex items-center justify-between hover:bg-slate-50/60 transition-colors">
                            <div class="min-w-0 pr-3">
                                <div class="font-bold text-xs text-slate-900 truncate">
                                    {{ $item->garmentItem ? $item->garmentItem->name : 'Article' }}
                                    @if($item->pieces && $item->pieces > 1)
                                        <span class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 px-1.5 py-0.2 rounded font-mono">({{ $item->pieces }} pièces)</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-500 flex items-center space-x-2 mt-0.5">
                                    <span class="font-medium text-indigo-600">{{ $item->service ? $item->service->name : 'Service' }}</span>
                                    <span>•</span>
                                    <span>Qté : <strong>{{ floatval($item->quantity) }}</strong></span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-mono font-bold text-xs text-slate-900">{{ number_format($item->total_price, 0, '.', ' ') }} DA</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ number_format($item->unit_price, 0, '.', ' ') }} DA/u</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Financial Summary Box -->
            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-4 space-y-2.5 shadow-lg">
                <div class="flex justify-between items-center text-xs text-slate-300">
                    <span>Total Net</span>
                    <span class="font-mono font-bold text-sm text-white">{{ number_format($order->total_amount, 0, '.', ' ') }} DA</span>
                </div>

                <div class="flex justify-between items-center text-xs text-emerald-400">
                    <span>Acompte Versé</span>
                    <span class="font-mono font-bold text-sm">- {{ number_format($order->paid_amount, 0, '.', ' ') }} DA</span>
                </div>

                <div class="border-t border-slate-800 pt-2 flex justify-between items-center">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Solde Restant</span>
                        @if($order->balance_amount <= 0)
                            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wide">✓ Entièrement Réglé</span>
                        @else
                            <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wide">À régler au retrait</span>
                        @endif
                    </div>
                    <div class="font-mono font-black text-xl {{ $order->balance_amount <= 0 ? 'text-emerald-400' : 'text-amber-400' }}">
                        {{ number_format($order->balance_amount, 0, '.', ' ') }} DA
                    </div>
                </div>
            </div>

            <!-- Remarks / Notes if any -->
            @if($order->remarks)
                <div class="p-3 bg-amber-50/70 border border-amber-200/70 rounded-xl text-xs text-amber-800">
                    <span class="font-bold block text-[10px] uppercase tracking-wider text-amber-600 mb-0.5">Note importante :</span>
                    {{ $order->remarks }}
                </div>
            @endif

            <!-- Terms notice -->
            <p class="text-[10px] text-slate-400 text-center leading-relaxed">
                Veuillez présenter ce reçu lors du retrait de vos vêtements au pressing.<br>
                Merci de votre confiance !
            </p>
        </div>

        <!-- Footer Bar with Facebook link -->
        <div class="bg-slate-50 p-4 border-t border-slate-100 flex items-center justify-between text-xs">
            <a href="https://www.facebook.com" target="_blank" class="inline-flex items-center space-x-1.5 text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span>pressing blanchisserie le paradou</span>
            </a>

            <span class="text-[10px] text-slate-400 font-mono">Paradou POS</span>
        </div>

    </div>

    <!-- Small footer copyright -->
    <div class="mt-6 text-center text-xs text-slate-400 no-print">
        &copy; {{ date('Y') }} Le Paradou • Pressing & Blanchisserie
    </div>

</body>
</html>
