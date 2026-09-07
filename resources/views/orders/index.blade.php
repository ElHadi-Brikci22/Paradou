@extends('layouts.app')

@section('title', 'Suivi des Commandes')

@section('styles')
<style>
    .status-badge-pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: rgb(245, 158, 11);
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    .status-badge-ready {
        background-color: rgba(16, 185, 129, 0.1);
        color: rgb(16, 185, 129);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    .status-badge-partially_delivered {
        background-color: rgba(168, 85, 247, 0.1);
        color: rgb(168, 85, 247);
        border: 1px solid rgba(168, 85, 247, 0.2);
    }
    .status-badge-delivered {
        background-color: rgba(59, 130, 246, 0.1);
        color: rgb(59, 130, 246);
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(0.8);
        cursor: pointer;
        opacity: 0.7;
    }
    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }
</style>
@endsection

@section('content')
<div class="flex-1 flex flex-col min-w-0 bg-slate-900 overflow-hidden">
    <!-- Filters & Search sub-bar -->
    <div class="bg-slate-800/40 p-4 border-b border-slate-700/50 shrink-0 flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- Status Tabs -->
        <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
            <a href="{{ route('orders.index', ['status' => 'all', 'search' => $search, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors shrink-0 {{ $status === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Toutes les commandes
            </a>
            <a href="{{ route('orders.index', ['status' => 'express', 'search' => $search, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors shrink-0 {{ $status === 'express' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                ⚡ Express
            </a>
            <a href="{{ route('orders.index', ['status' => 'pending', 'search' => $search, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors shrink-0 {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                En préparation
            </a>
            <a href="{{ route('orders.index', ['status' => 'ready', 'search' => $search, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors shrink-0 {{ $status === 'ready' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Prêtes pour retrait
            </a>
            <a href="{{ route('orders.index', ['status' => 'partially_delivered', 'search' => $search, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors shrink-0 {{ $status === 'partially_delivered' ? 'bg-purple-600 text-white shadow-md shadow-purple-600/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Partiellement livrées
            </a>
            <a href="{{ route('orders.index', ['status' => 'delivered', 'search' => $search, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors shrink-0 {{ $status === 'delivered' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Livrées / Clôturées
            </a>
        </div>

        <!-- Search & Date Filter Form -->
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto justify-end items-stretch sm:items-center">
            <form method="GET" action="{{ route('orders.index') }}" class="flex flex-col sm:flex-row gap-2 shrink-0 items-stretch sm:items-center">
                <input type="hidden" name="status" value="{{ $status }}">
                
                <!-- Date range button & dropdown -->
                <div class="relative shrink-0">
                    <button type="button" id="date-filter-toggle" class="bg-slate-900 border border-slate-700 hover:bg-slate-800/80 rounded-lg p-2 text-xs text-slate-200 font-semibold transition-all flex items-center justify-center space-x-1.5 cursor-pointer" title="{{ ($startDate || $endDate) ? 'Filtre date actif' : 'Filtrer par date' }}">
                        <svg class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        @if($startDate || $endDate)
                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                        @endif
                    </button>

                    <!-- Dropdown calendar popover -->
                    <div id="date-filter-dropdown" class="hidden absolute right-0 mt-2 bg-slate-800 border border-slate-700 rounded-xl p-4 shadow-2xl z-30 w-72 space-y-3">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Filtrer par date de dépôt</h4>
                        <div class="space-y-3">
                            <div class="flex flex-col space-y-1">
                                <label class="text-[10px] text-slate-400 font-bold">Du (Date début) :</label>
                                <input type="date" name="start_date" id="start-date-picker" value="{{ $startDate }}" 
                                       class="bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 w-full cursor-pointer">
                            </div>
                            <div class="flex flex-col space-y-1">
                                <label class="text-[10px] text-slate-400 font-bold">Au (Date fin) :</label>
                                <input type="date" name="end_date" id="end-date-picker" value="{{ $endDate }}" 
                                       class="bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 w-full cursor-pointer">
                            </div>
                        </div>
                        <div class="flex justify-between items-center pt-2.5 border-t border-slate-700/60">
                            @if($startDate || $endDate)
                                <a href="{{ route('orders.index', ['status' => $status, 'search' => $search]) }}" class="text-[10px] text-rose-400 hover:text-rose-300 font-semibold cursor-pointer">
                                    Effacer
                                </a>
                            @else
                                <span></span>
                            @endif
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-bold px-3.5 py-1.5 rounded-lg shadow-md transition-colors cursor-pointer">
                                Appliquer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Text Search -->
                <div class="relative w-full sm:w-56">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher ticket, client..." 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-3 pr-14 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                    @if($search)
                        <a href="{{ route('orders.index', ['status' => $status, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="absolute right-8 top-2.5 text-slate-500 hover:text-slate-300" title="Effacer texte">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                    <button type="submit" class="absolute right-2.5 top-2.5 text-slate-500 hover:text-slate-300">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table (Scrollable container) -->
    <div class="flex-1 overflow-y-auto p-6">
        @if($orders->isEmpty())
            <div class="flex flex-col items-center justify-center h-full text-slate-500 py-12">
                <svg class="h-16 w-16 mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <p class="font-display font-medium text-lg">Aucun ticket trouvé</p>
                <p class="text-xs">Essayez d'élargir vos filtres ou termes de recherche.</p>
            </div>
        @else
            @if(auth()->user()->role === 'admin')
                <!-- Bulk Action Panel -->
                <div id="bulk-action-panel" class="hidden mb-4 p-4 bg-rose-500/10 border border-rose-500/20 rounded-xl flex items-center justify-between transition-all">
                    <div class="flex items-center space-x-2 text-rose-400 text-xs font-semibold">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span id="bulk-selected-count">0 commande(s) sélectionnée(s)</span>
                    </div>
                    <button onclick="bulkDeleteOrders()" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 active:translate-y-0.5 transition-all text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 cursor-pointer shadow-lg shadow-rose-600/10">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Supprimer la sélection</span>
                    </button>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            @if(auth()->user()->role === 'admin')
                                <th class="py-3 px-4 w-10">
                                    <input type="checkbox" id="select-all-orders" class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                </th>
                            @endif
                            <th class="py-3 px-4">Ticket</th>
                            <th class="py-3 px-4">Client</th>
                            <th class="py-3 px-4">Date dépôt</th>
                            <th class="py-3 px-4 font-semibold text-slate-300">Livraison Prévue</th>
                            <th class="py-3 px-4">Total Net</th>
                            <th class="py-3 px-4">Acompte / Solde</th>
                            <th class="py-3 px-4 text-center">Statut</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-xs">
                        @foreach($orders as $order)
                            <tr onclick="handleOrderRowClick(event, {{ $order->id }})" class="hover:bg-slate-800/40 transition-colors cursor-pointer group">
                                @if(auth()->user()->role === 'admin')
                                    <td class="py-3.5 px-4 w-10">
                                        <input type="checkbox" class="order-checkbox rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer" value="{{ $order->id }}">
                                    </td>
                                @endif
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-200 group-hover:text-indigo-400 transition-colors">
                                    #{{ $order->ticket_number }}
                                    @if($order->is_express)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-rose-500/10 text-rose-400 border border-rose-500/20 animate-pulse uppercase tracking-wider ml-1">Express</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-white group-hover:text-indigo-200 transition-colors">{{ $order->client->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">Code: {{ $order->client->code }} {{ $order->client->phone ? ' | Tél: '.$order->client->phone : '' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-300">{{ $order->order_date->format('d/m/Y H:i') }}</td>
                                <td class="py-3.5 px-4 font-bold text-indigo-300">{{ $order->target_delivery_date->format('d/m/Y') }}</td>
                                <td class="py-3.5 px-4 font-bold text-slate-100 font-mono">{{ number_format($order->total_amount, 0, '.', '') }} DA</td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-300 font-mono">Payé: <span id="order-paid-{{ $order->id }}">{{ number_format($order->paid_amount, 0, '.', '') }}</span> DA</div>
                                    <div class="font-bold font-mono">
                                        Solde: <span id="order-balance-{{ $order->id }}" class="{{ $order->balance_amount > 0 ? 'text-amber-500' : 'text-emerald-500' }}">{{ number_format($order->balance_amount, 0, '.', '') }}</span> DA
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span id="order-status-badge-{{ $order->id }}" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider status-badge-{{ $order->status }}">
                                        @if($order->status === 'pending')
                                            En cours
                                        @elseif($order->status === 'ready')
                                            Prêt
                                        @elseif($order->status === 'partially_delivered')
                                            Livr. Partielle
                                        @else
                                            Livré
                                        @endif
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right flex items-center justify-end space-x-2">
                                    @if(auth()->user()->role === 'admin' && $order->status === 'pending')
                                        <a href="{{ route('checkout.index', ['order_id' => $order->id]) }}" 
                                           class="bg-slate-800 hover:bg-slate-700 text-amber-500 hover:text-amber-400 p-1.5 rounded-lg border border-slate-700 transition-all cursor-pointer" title="Modifier la commande">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif
                                    @if(auth()->user()->role === 'admin')
                                        <button onclick="deleteOrder({{ $order->id }}, '{{ $order->ticket_number }}')" 
                                                class="bg-slate-800 hover:bg-slate-700 text-rose-500 hover:text-rose-400 p-1.5 rounded-lg border border-slate-700 transition-all cursor-pointer" title="Supprimer la commande">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                    <button onclick="printOrder({{ $order->id }}, 'ticket')" 
                                             class="bg-slate-800 hover:bg-slate-700 text-indigo-400 hover:text-indigo-300 p-1.5 rounded-lg border border-slate-700 transition-all cursor-pointer" title="Imprimer Reçu Client">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </button>
                                    <button onclick="printOrder({{ $order->id }}, 'tags')" 
                                             class="bg-slate-800 hover:bg-slate-700 text-amber-500 hover:text-amber-400 p-1.5 rounded-lg border border-slate-700 transition-all cursor-pointer" title="Imprimer Étiquettes Cintres">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v9a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination links -->
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ================= ORDER DETAIL & DELIVERY MODAL ================= -->
<div id="order-modal" class="hidden fixed inset-0 bg-slate-950/70 z-50 flex items-center justify-center p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-[600px] max-h-[85vh] flex flex-col shadow-2xl overflow-hidden transform scale-100 transition-all">
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-white font-display">Ticket N° <span id="modal-ticket-no" class="font-mono">081916</span></h3>
                <p class="text-xs text-slate-400">Client : <span id="modal-client-name" class="font-bold">Merad</span></p>
            </div>
            <div class="flex items-center space-x-3">
                @if(auth()->user()->role === 'admin')
                    <a id="modal-edit-order-link" href="#" 
                       class="hidden text-amber-500 hover:text-amber-400 p-1.5 bg-slate-700/50 hover:bg-slate-700 rounded-lg border border-slate-600 cursor-pointer" title="Modifier la commande">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>
                    <button onclick="deleteOrder(currentOrder.id, currentOrder.ticket_number, true)" 
                            class="text-rose-500 hover:text-rose-400 p-1.5 bg-slate-700/50 hover:bg-slate-700 rounded-lg border border-slate-600 cursor-pointer" title="Supprimer la commande">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                @endif
                <button onclick="printOrder(currentOrder.id, 'ticket')" 
                        class="text-indigo-400 hover:text-indigo-300 p-1.5 bg-slate-700/50 hover:bg-slate-700 rounded-lg border border-slate-600 cursor-pointer" title="Imprimer Reçu Client">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                </button>
                <button onclick="printOrder(currentOrder.id, 'tags')" 
                        class="text-amber-500 hover:text-amber-400 p-1.5 bg-slate-700/50 hover:bg-slate-700 rounded-lg border border-slate-600 cursor-pointer" title="Imprimer Étiquettes Cintres">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v9a2 2 0 002 2z" />
                    </svg>
                </button>
                {{-- Bouton WhatsApp modal temporairement commenté --}}
                {{-- <button id="modal-whatsapp-btn" onclick="triggerModalWhatsApp()" 
                        class="hidden text-emerald-400 hover:text-emerald-300 p-1.5 bg-slate-700/50 hover:bg-slate-700 rounded-lg border border-slate-600 cursor-pointer" title="Notifier via WhatsApp">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.012 2c-5.506 0-9.988 4.482-9.988 9.988 0 1.761.458 3.414 1.258 4.86L2.001 22l5.342-1.401a9.92 9.92 0 0 0 4.669 1.178h.005c5.506 0 9.988-4.482 9.988-9.988C22 6.482 17.518 2 12.012 2zm6.208 14.194c-.273.766-1.579 1.482-2.164 1.554-.5.06-1.157.087-2.735-.536-2.022-.797-3.324-2.859-3.425-2.993-.102-.134-.817-1.084-.817-2.069 0-.985.518-1.468.702-1.661.184-.193.4-.241.533-.241H11c.102 0 .239-.038.375.292.136.33.461 1.127.502 1.21.041.083.068.179.014.288-.055.109-.082.179-.164.275-.082.096-.172.215-.246.29-.089.09-.181.187-.078.363.102.176.452.747.969 1.206.666.592 1.229.776 1.405.864.177.088.279.073.383-.046.104-.12.443-.514.562-.69.119-.176.239-.147.4-.087.162.06 1.025.483 1.222.581.197.098.328.147.376.228.048.081.048.468-.225 1.234z"/>
                    </svg>
                </button> --}}
                <button onclick="closeOrderDetailModal()" class="text-slate-400 hover:text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1">
            <!-- Items list with checkboxes -->
            <div>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Vêtements déposés (À marquer prêts)</span>
                <div class="space-y-3" id="modal-items-container">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Total billing summary -->
            <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-700/50 grid grid-cols-3 gap-4 text-center font-mono">
                <div>
                    <p class="text-[10px] text-slate-500 uppercase">Montant Net</p>
                    <p id="modal-total-amount" class="text-base font-bold text-white mt-1">1910 DA</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 uppercase">Acompte versé</p>
                    <p id="modal-paid-amount" class="text-base font-bold text-emerald-500 mt-1">0 DA</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 uppercase">Solde restant</p>
                    <p id="modal-balance-amount" class="text-base font-bold text-amber-500 mt-1">1910 DA</p>
                </div>
            </div>

            <!-- Delivery panel (Visible if not already delivered) -->
            <div id="modal-delivery-section" class="p-4 bg-indigo-600/10 border border-indigo-500/20 rounded-xl space-y-4">
                <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Finaliser le Retrait & Livraison</h4>
                
                <div class="flex items-center justify-between gap-4">
                    <div class="text-xs text-slate-300">
                        Encaisser le solde restant de <span id="delivery-suggested-balance" class="font-bold text-amber-500">1910 DA</span>.
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-slate-400">Montant Perçu (DA):</span>
                        <input type="number" id="cash-collected-input" value="0" min="0" 
                               class="w-28 bg-slate-900 border border-slate-700 rounded-md px-2.5 py-1 text-sm font-bold text-right text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <!-- Action buttons: Modifier (Sauvegarder) & Livraison -->
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <button type="button" id="modal-save-changes-btn" onclick="saveOrderModalChanges()" 
                            class="w-full bg-amber-600 hover:bg-amber-500 active:translate-y-0.5 text-white font-display font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-amber-600/10 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Modifier</span>
                    </button>

                    <button type="button" id="modal-submit-delivery-btn" onclick="submitOrderDelivery()" 
                            class="w-full bg-emerald-600 hover:bg-emerald-500 active:translate-y-0.5 text-white font-display font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-emerald-600/10 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span id="modal-delivery-btn-text">Livrer articles prêts</span>
                    </button>
                </div>
            </div>

            <!-- Already delivered label -->
            <div id="modal-delivered-alert" class="hidden p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl text-center text-xs font-bold text-blue-400 uppercase">
                Ce ticket a été totalement retiré et clôturé le <span id="modal-delivered-date" class="font-mono">29/09/2021 à 12:00</span>.
            </div>
        </div>
    </div>
</div>

<!-- ================= TOAST NOTIFICATION CONTAINER (Top Right) ================= -->
<div id="toast-container" class="fixed top-20 right-6 z-50 space-y-3 w-80 pointer-events-none">
    <!-- Populated by JS alerts -->
</div>
@endsection

@section('scripts')
<script>
    // In-memory cache of orders for instant, dynamic updates
    const ordersMap = {};
    @if(isset($orders))
        const initialOrders = @json($orders->items());
        initialOrders.forEach(o => {
            ordersMap[o.id] = o;
        });
    @endif

    let currentOrder = null;
    let modalItemsState = {};

    function formatDateTime(dtStr) {
        if (!dtStr) return '';
        try {
            const d = new Date(dtStr);
            if (isNaN(d.getTime())) return dtStr;
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            return `${day}/${month}/${year} à ${hours}:${minutes}`;
        } catch (e) {
            return dtStr;
        }
    }

    function handleOrderRowClick(event, orderRef) {
        // Ignorer le clic si l'utilisateur a cliqué sur un bouton, un lien ou une case à cocher
        if (event.target.closest('button, a, input, select')) {
            return;
        }
        openOrderDetailModal(orderRef);
    }

    function openOrderDetailModal(orderRef) {
        let order = (typeof orderRef === 'object' && orderRef !== null) ? orderRef : ordersMap[orderRef];
        if (!order) return;
        currentOrder = order;
        modalItemsState = {};

        @if(auth()->user()->role === 'admin')
        const editLink = document.getElementById('modal-edit-order-link');
        if (editLink) {
            if (order.status === 'pending' || order.status === 'partially_delivered') {
                editLink.href = `{{ route('checkout.index') }}?order_id=${order.id}`;
                editLink.classList.remove('hidden');
            } else {
                editLink.classList.add('hidden');
            }
        }
        @endif

        // Set text values
        document.getElementById('modal-ticket-no').textContent = order.ticket_number;
        document.getElementById('modal-client-name').textContent = order.client ? order.client.name : '';
        document.getElementById('modal-total-amount').textContent = `${parseFloat(order.total_amount).toFixed(0)} DA`;
        document.getElementById('modal-paid-amount').textContent = `${parseFloat(order.paid_amount).toFixed(0)} DA`;
        
        const balance = parseFloat(order.balance_amount);
        document.getElementById('modal-balance-amount').textContent = `${balance.toFixed(0)} DA`;
        
        // Prefill cash collected with the remaining balance
        document.getElementById('cash-collected-input').value = balance > 0 ? balance.toFixed(0) : 0;
        document.getElementById('delivery-suggested-balance').textContent = `${balance.toFixed(0)} DA`;

        const items = order.order_items || [];

        // Initialize local preparation state for each item
        items.forEach(item => {
            if (item.is_delivered) {
                modalItemsState[item.id] = true;
            } else {
                modalItemsState[item.id] = !!item.is_ready;
            }
        });

        // Render items inside ticket
        const itemsBox = document.getElementById('modal-items-container');
        itemsBox.innerHTML = '';

        items.forEach(item => {
            const itemId = item.id;
            const isItemDelivered = !!item.is_delivered;
            const isReady = !!modalItemsState[itemId];

            const row = document.createElement('div');
            row.className = `flex items-center justify-between p-3 rounded-xl border transition-all ${
                isItemDelivered 
                    ? 'bg-blue-950/20 border-blue-500/20' 
                    : 'bg-slate-900/40 border-slate-700/40 hover:border-slate-600'
            }`;

            // Left side (icon/checkbox + description)
            const left = document.createElement('div');
            left.className = "flex items-start space-x-3";

            let checkbox;
            if (isItemDelivered) {
                // Delivered icon (locked)
                checkbox = document.createElement('div');
                checkbox.className = "h-6 w-6 rounded-md bg-blue-500/20 border border-blue-500/40 text-blue-400 flex items-center justify-center shrink-0 mt-0.5";
                checkbox.innerHTML = `
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                `;
            } else {
                // Interactive checkbox button
                checkbox = document.createElement('button');
                checkbox.type = 'button';
                checkbox.id = `modal-item-chk-${itemId}`;
                checkbox.className = `h-6 w-6 rounded-md border flex items-center justify-center transition-all cursor-pointer shrink-0 mt-0.5 ${
                    isReady ? 'bg-emerald-600 border-emerald-500 text-white' : 'border-slate-600 bg-slate-900 hover:border-slate-400'
                }`;
                checkbox.innerHTML = isReady ? `
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                ` : '';
            }

            left.appendChild(checkbox);

            const details = document.createElement('div');
            
            const titleLine = document.createElement('div');
            titleLine.className = "flex items-center space-x-2 flex-wrap gap-y-1";
            
            const qty = document.createElement('span');
            qty.className = "text-xs font-black text-indigo-400 font-mono";
            qty.textContent = `${parseFloat(item.quantity)}x`;
            
            const name = document.createElement('span');
            name.className = `text-xs font-bold uppercase ${isItemDelivered ? 'text-slate-300' : 'text-slate-100'}`;
            name.textContent = item.garment_item ? item.garment_item.name : 'Article';

            const serviceBadge = document.createElement('span');
            serviceBadge.className = "text-[9px] bg-slate-800 text-slate-400 font-bold px-1.5 py-0.2 rounded uppercase";
            serviceBadge.textContent = item.service ? item.service.name : '';

            titleLine.appendChild(qty);
            titleLine.appendChild(name);
            titleLine.appendChild(serviceBadge);
            details.appendChild(titleLine);

            // Display colors, defects, stains choice
            const optionString = [];
            if (item.colors && item.colors.length > 0) optionString.push(`Couleurs: ${item.colors.join('/')}`);
            if (item.defects && item.defects.length > 0) optionString.push(`Défauts: ${item.defects.join('/')}`);
            if (item.stains && item.stains.length > 0) optionString.push(`Taches: ${item.stains.join('/')}`);
            if (item.notes) optionString.push(`Note: ${item.notes}`);

            if (optionString.length > 0) {
                const optText = document.createElement('div');
                optText.className = "text-[10px] text-amber-500 font-medium mt-0.5";
                optText.textContent = optionString.join(' | ');
                details.appendChild(optText);
            }

            // If DELIVERED: Show delivery date clearly on its line!
            if (isItemDelivered && item.delivered_at) {
                const deliveryDateBadge = document.createElement('div');
                deliveryDateBadge.className = "inline-flex items-center space-x-1.5 px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-blue-500/10 text-blue-300 border border-blue-500/20";
                deliveryDateBadge.innerHTML = `
                    <svg class="h-3 w-3 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    <span>Livré le ${formatDateTime(item.delivered_at)}</span>
                `;
                details.appendChild(deliveryDateBadge);
            }

            left.appendChild(details);
            row.appendChild(left);

            // Right container (price + action button / delivered badge)
            const rightSide = document.createElement('div');
            rightSide.className = "flex items-center space-x-3 shrink-0";

            // Price tag
            const rightPrice = document.createElement('div');
            rightPrice.className = "text-xs font-bold text-slate-400 font-mono";
            rightPrice.textContent = `${parseFloat(item.total_price).toFixed(0)} DA`;
            rightSide.appendChild(rightPrice);

            if (isItemDelivered) {
                const actionBadge = document.createElement('span');
                actionBadge.className = "px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20";
                actionBadge.textContent = 'Livré';
                rightSide.appendChild(actionBadge);
            } else {
                const actionBtn = document.createElement('button');
                actionBtn.type = 'button';
                actionBtn.id = `modal-item-btn-${itemId}`;
                actionBtn.className = `px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all border ${
                    isReady ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-indigo-600 border-indigo-500 hover:bg-indigo-500 text-white cursor-pointer'
                }`;
                actionBtn.textContent = isReady ? 'Prêt' : 'Marquer Prêt';

                const toggleHandler = () => {
                    modalItemsState[itemId] = !modalItemsState[itemId];
                    const nowReady = modalItemsState[itemId];

                    if (nowReady) {
                        checkbox.className = "h-6 w-6 rounded-md border flex items-center justify-center transition-all cursor-pointer shrink-0 mt-0.5 bg-emerald-600 border-emerald-500 text-white";
                        checkbox.innerHTML = `
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        `;
                        actionBtn.className = "px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all border bg-emerald-500/10 text-emerald-400 border-emerald-500/20 cursor-pointer";
                        actionBtn.textContent = 'Prêt';
                    } else {
                        checkbox.className = "h-6 w-6 rounded-md border flex items-center justify-center transition-all cursor-pointer shrink-0 mt-0.5 border-slate-600 bg-slate-900 hover:border-slate-400";
                        checkbox.innerHTML = '';
                        actionBtn.className = "px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all border bg-indigo-600 border-indigo-500 hover:bg-indigo-500 text-white cursor-pointer";
                        actionBtn.textContent = 'Marquer Prêt';
                    }
                    updateDeliverySectionUI(items);
                };

                checkbox.onclick = toggleHandler;
                actionBtn.onclick = toggleHandler;
                rightSide.appendChild(actionBtn);
            }

            row.appendChild(rightSide);
            itemsBox.appendChild(row);
        });

        // Update Delivery Section buttons and visibility
        updateDeliverySectionUI(items);

        document.getElementById('order-modal').classList.remove('hidden');
    }

    function updateDeliverySectionUI(items) {
        const deliverySection = document.getElementById('modal-delivery-section');
        const deliveredAlert = document.getElementById('modal-delivered-alert');
        const deliveryBtnText = document.getElementById('modal-delivery-btn-text');

        const totalItems = items.length;
        const deliveredItems = items.filter(i => i.is_delivered);
        const undeliveredItems = items.filter(i => !i.is_delivered);
        const readyToDeliver = undeliveredItems.filter(i => !!modalItemsState[i.id]);

        if (totalItems > 0 && deliveredItems.length === totalItems) {
            // Completely delivered
            deliverySection.classList.add('hidden');
            const devDate = currentOrder.actual_delivery_date ? new Date(currentOrder.actual_delivery_date) : new Date();
            const dateStr = formatDateTime(devDate);
            document.getElementById('modal-delivered-date').textContent = dateStr;
            deliveredAlert.classList.remove('hidden');
        } else {
            // Still has undelivered items
            deliverySection.classList.remove('hidden');
            deliveredAlert.classList.add('hidden');

            if (deliveryBtnText) {
                if (readyToDeliver.length > 0) {
                    if (readyToDeliver.length === undeliveredItems.length && deliveredItems.length === 0) {
                        deliveryBtnText.textContent = `Livraison Totale (${readyToDeliver.length})`;
                    } else {
                        deliveryBtnText.textContent = `Livrer articles prêts (${readyToDeliver.length})`;
                    }
                } else {
                    deliveryBtnText.textContent = `Livrer articles prêts (0)`;
                }
            }
        }
    }

    function closeOrderDetailModal() {
        document.getElementById('order-modal').classList.add('hidden');
        currentOrder = null;
        modalItemsState = {};
    }

    // Sauvegarder TOUTES les modifications de préparation seulement au clic sur le bouton Modifier
    function saveOrderModalChanges() {
        if (!currentOrder) return;

        const items = currentOrder.order_items || [];
        const undeliveredItems = items.filter(i => !i.is_delivered);

        const payload = undeliveredItems.map(item => ({
            id: item.id,
            is_ready: !!modalItemsState[item.id]
        }));

        const saveBtn = document.getElementById('modal-save-changes-btn');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.classList.add('opacity-75', 'cursor-wait');
        }

        fetch(`/api/orders/${currentOrder.id}/update-items`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ items: payload })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update client memory cache with fresh order data
                if (data.order) {
                    ordersMap[data.order.id] = data.order;
                }

                // Update status badge in the list
                const orderId = data.order ? data.order.id : currentOrder.id;
                const status = data.order ? data.order.status : currentOrder.status;
                const badge = document.getElementById(`order-status-badge-${orderId}`);
                if (badge) {
                    badge.className = `px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider status-badge-${status}`;
                    if (status === 'pending') {
                        badge.textContent = 'En cours';
                    } else if (status === 'ready') {
                        badge.textContent = 'Prêt';
                    } else if (status === 'partially_delivered') {
                        badge.textContent = 'Livr. Partielle';
                    } else {
                        badge.textContent = 'Livré';
                    }
                }

                const ticketNo = currentOrder.ticket_number;
                closeOrderDetailModal();
                showAppAlert(data.message || `Modifications enregistrées avec succès pour le ticket #${ticketNo}.`, "success", "Enregistré");
            } else {
                showAppAlert(data.message || "Erreur lors de l'enregistrement.", "error", "Erreur");
            }
        })
        .catch(err => {
            showAppAlert("Erreur de communication avec le serveur.", "error", "Erreur");
        })
        .finally(() => {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.classList.remove('opacity-75', 'cursor-wait');
            }
        });
    }

    // Finalize order delivery (partial or total)
    function submitOrderDelivery() {
        if (!currentOrder) return;

        const items = currentOrder.order_items || [];
        const undeliveredItems = items.filter(i => !i.is_delivered);
        const readyItemIds = undeliveredItems
            .filter(i => !!modalItemsState[i.id])
            .map(i => i.id);

        if (readyItemIds.length === 0) {
            showAppAlert("Veuillez d'abord marquer au moins un article comme « Prêt » pour effectuer la livraison.", "warning", "Aucun article prêt");
            return;
        }

        const cashCollected = parseFloat(document.getElementById('cash-collected-input').value) || 0;
        const deliveryBtn = document.getElementById('modal-submit-delivery-btn');
        if (deliveryBtn) {
            deliveryBtn.disabled = true;
            deliveryBtn.classList.add('opacity-75', 'cursor-wait');
        }

        fetch(`/api/orders/${currentOrder.id}/deliver`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                cash_collected: cashCollected,
                item_ids: readyItemIds
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.order) {
                    ordersMap[data.order.id] = data.order;
                }

                const orderId = data.order ? data.order.id : (currentOrder ? currentOrder.id : null);

                if (orderId && data.order) {
                    // Update paid amount instantly in list
                    const paidEl = document.getElementById(`order-paid-${orderId}`);
                    if (paidEl) {
                        paidEl.textContent = parseFloat(data.order.paid_amount).toFixed(0);
                    }

                    // Update balance amount instantly in list
                    const balanceEl = document.getElementById(`order-balance-${orderId}`);
                    if (balanceEl) {
                        balanceEl.textContent = parseFloat(data.order.balance_amount).toFixed(0);
                        balanceEl.className = data.order.balance_amount > 0 ? "text-amber-500" : "text-emerald-500";
                        balanceEl.parentElement.className = `font-bold font-mono ${data.order.balance_amount > 0 ? 'text-amber-500' : 'text-emerald-500'}`;
                    }

                    // Update status badge instantly in list
                    const badge = document.getElementById(`order-status-badge-${orderId}`);
                    if (badge) {
                        const status = data.order.status;
                        badge.className = `px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider status-badge-${status}`;
                        if (status === 'pending') {
                            badge.textContent = 'En cours';
                        } else if (status === 'ready') {
                            badge.textContent = 'Prêt';
                        } else if (status === 'partially_delivered') {
                            badge.textContent = 'Livr. Partielle';
                        } else {
                            badge.textContent = 'Livré';
                        }
                    }
                }

                if (data.is_all_delivered) {
                    showAppAlert(data.message || "Commande entièrement livrée avec succès !", "success", "Livraison Totale", () => {
                        closeOrderDetailModal();
                    });
                } else {
                    showAppAlert(data.message || "Livraison partielle effectuée avec succès !", "success", "Livraison Partielle", () => {
                        // Refresh the modal content with newly updated order so client immediately sees delivered status & date
                        openOrderDetailModal(data.order);
                    });
                }
            } else {
                showAppAlert(data.message || "Erreur lors de la livraison.", "error", "Erreur");
            }
        })
        .catch(err => {
            showAppAlert("Erreur lors de la livraison de la commande.", "error", "Erreur");
        })
        .finally(() => {
            if (deliveryBtn) {
                deliveryBtn.disabled = false;
                deliveryBtn.classList.remove('opacity-75', 'cursor-wait');
            }
        });
    }

    // Display a floating Toast Notification for simulated SMS/WhatsApp message
    function showToastNotification(message) {
        const container = document.getElementById('toast-container');
        
        const toast = document.createElement('div');
        toast.className = "bg-slate-800 border border-indigo-500 rounded-xl p-4 shadow-2xl flex flex-col space-y-2 pointer-events-auto transform translate-y-2 opacity-0 transition-all duration-300";
        
        toast.innerHTML = `
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-1.5">
                <div class="flex items-center space-x-2 text-indigo-400 font-bold text-xs uppercase tracking-wider font-display">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Simulation SMS Envoyé</span>
                </div>
                <button onclick="this.closest('.toast').remove()" class="text-slate-500 hover:text-slate-300">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="text-[11px] text-slate-300 leading-normal">${message}</p>
        `;
        toast.classList.add('toast'); // class for self target

        container.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        }, 10);

        // Auto remove after 8 seconds
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 8000);
    }

    // Toggle date filter dropdown
    const toggleBtn = document.getElementById('date-filter-toggle');
    const dropdown = document.getElementById('date-filter-dropdown');

    if (toggleBtn && dropdown) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    @if(auth()->user()->role === 'admin')
    document.addEventListener('DOMContentLoaded', () => {
        const selectAllCheckbox = document.getElementById('select-all-orders');
        const orderCheckboxes = document.querySelectorAll('.order-checkbox');
        const bulkActionPanel = document.getElementById('bulk-action-panel');
        const bulkCountSpan = document.getElementById('bulk-selected-count');

        function updateBulkPanel() {
            const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
            const count = checkedBoxes.length;
            if (count > 0) {
                if (bulkCountSpan) bulkCountSpan.textContent = `${count} commande(s) sélectionnée(s)`;
                if (bulkActionPanel) bulkActionPanel.classList.remove('hidden');
            } else {
                if (bulkActionPanel) bulkActionPanel.classList.add('hidden');
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', () => {
                const isChecked = selectAllCheckbox.checked;
                orderCheckboxes.forEach(cb => {
                    cb.checked = isChecked;
                });
                updateBulkPanel();
            });
        }

        orderCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const allChecked = Array.from(orderCheckboxes).every(c => c.checked);
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                }
                updateBulkPanel();
            });
        });
    });

    function deleteOrder(orderId, ticketNumber, fromModal = false) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement la commande #${ticketNumber} ? Cette action est irréversible.`)) {
            fetch(`/admin/orders/${orderId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'DELETE'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAppAlert(data.message, "success", "Succès", () => {
                        window.location.reload();
                    });
                } else {
                    showAppAlert(data.message, "error", "Erreur");
                }
            })
            .catch(err => {
                showAppAlert("Erreur lors de la suppression de la commande.", "error", "Erreur");
            });
        }
    }

    function bulkDeleteOrders() {
        const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.value);

        if (ids.length === 0) return;

        if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement les ${ids.length} commandes sélectionnées ? Cette action est irréversible.`)) {
            fetch('/admin/orders/bulk-destroy', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'DELETE'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAppAlert(data.message, "success", "Succès", () => {
                        window.location.reload();
                    });
                } else {
                    showAppAlert(data.message, "error", "Erreur");
                }
            })
            .catch(err => {
                showAppAlert("Erreur lors de la suppression groupée.", "error", "Erreur");
            });
        }
    }
    @endif

    /*
    function sendWhatsAppMessage(phoneNum, message) {
        if (!phoneNum) {
            showAppAlert("Ce client n'a pas de numéro de téléphone renseigné.", "error", "Numéro de téléphone absent");
            return;
        }
        let phone = phoneNum.trim();
        phone = phone.replace(/\s+/g, '').replace(/-/g, '');
        if (phone.startsWith('0')) {
            phone = '213' + phone.substring(1);
        } else if (!phone.startsWith('213') && !phone.startsWith('+')) {
            phone = '213' + phone;
        }
        phone = phone.replace('+', '');
        const encodedText = encodeURIComponent(message);
        const waUrl = `https://api.whatsapp.com/send?phone=${phone}&text=${encodedText}`;
        window.open(waUrl, '_blank');
    }

    function triggerModalWhatsApp() {
        if (!currentOrder) return;
        const client = currentOrder.client;
        if (!client.phone) {
            showAppAlert("Ce client n'a pas de numéro de téléphone renseigné.", "error", "Numéro de téléphone absent");
            return;
        }

        const balance = parseFloat(currentOrder.balance_amount);
        const balanceText = balance > 0 
            ? `Reste à régler : ${balance.toFixed(0)} DA.` 
            : "Commande entièrement réglée.";

        const message = `Bonjour ${client.name}, vos vêtements du ticket #${currentOrder.ticket_number} sont prêts à être retirés chez MSK DRY PLUS. ${balanceText} Merci pour votre confiance !`;

        sendWhatsAppMessage(client.phone, message);
    }

    function triggerDirectWhatsApp(clientName, ticketNumber, balance, phoneNum) {
        const balanceText = balance > 0 
            ? `Reste à régler : ${parseFloat(balance).toFixed(0)} DA.` 
            : "Commande entièrement réglée.";

        const message = `Bonjour ${clientName}, vos vêtements du ticket #${ticketNumber} sont prêts à être retirés chez MSK DRY PLUS. ${balanceText} Merci pour votre confiance !`;

        sendWhatsAppMessage(phoneNum, message);
    }
    */
</script>
@endsection
