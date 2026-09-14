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
            <a href="{{ route('orders.index', ['status' => 'credit', 'search' => $search, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors shrink-0 flex items-center space-x-1.5 {{ $status === 'credit' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/10' : 'bg-slate-800 text-amber-400 hover:bg-slate-700' }}">
                <span>💳 Livrées à Crédit</span>
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
                                <td class="py-3.5 px-4 font-bold text-slate-100 font-mono"><span id="order-total-{{ $order->id }}">{{ number_format($order->total_amount, 0, '.', '') }}</span> DA</td>
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
                                    @if(in_array($order->status, ['delivered', 'partially_delivered']) && $order->balance_amount > 0)
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                                Crédit ({{ number_format($order->balance_amount, 0, '.', '') }} DA)
                                            </span>
                                        </div>
                                    @endif
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
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Articles déposés (À marquer prêts)</span>
                    <span id="modal-articles-total-count" class="text-[10px] font-bold font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-2 py-0.5 rounded-full">0 article</span>
                </div>
                <div class="space-y-3" id="modal-items-container">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Order Total Weight Banner (If applicable) -->
            <div id="modal-order-weight-banner" class="hidden p-2.5 bg-amber-500/10 border border-amber-500/20 rounded-xl flex items-center justify-between text-amber-400">
                <span class="text-xs font-bold uppercase tracking-wider flex items-center space-x-1.5">
                    <span>⚖️</span>
                    <span>Poids Total Commande (Au Kilo)</span>
                </span>
                <span id="modal-order-weight-value" class="text-xs font-mono font-black">0.00 kg</span>
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
            <div id="modal-delivery-section" class="p-4 bg-indigo-600/10 border border-indigo-500/20 rounded-xl space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Finaliser le Retrait & Livraison</h4>
                    <span class="text-[11px] text-slate-400">Préparez les articles puis cliquez sur Livrer</span>
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

                    <button type="button" id="modal-submit-delivery-btn" onclick="openDeliveryPaymentModal()" 
                            class="w-full bg-emerald-600 hover:bg-emerald-500 active:translate-y-0.5 text-white font-display font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-emerald-600/10 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span id="modal-delivery-btn-text">Livrer articles prêts</span>
                    </button>
                </div>
            </div>

            <!-- Settle Credit section (Visible if completely delivered with remaining balance) -->
            <div id="modal-credit-settle-section" class="hidden p-4 bg-amber-500/10 border border-amber-500/25 rounded-xl space-y-3">
                <div>
                    <h4 class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center space-x-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Règlement du Solde Restant (Crédit)</span>
                    </h4>
                    <p class="text-[11px] text-slate-300 mt-1">Ce ticket a été remis au client avec un crédit. Solde restant impayé : <span id="credit-settle-balance" class="font-bold text-amber-400 font-mono">0 DA</span>.</p>
                </div>

                <div class="flex items-center justify-between gap-4 pt-1">
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-slate-400">Montant Reçu (DA):</span>
                        <input type="number" id="credit-settle-input" value="0" min="1" 
                               class="w-32 bg-slate-900 border border-slate-700 rounded-md px-2.5 py-1 text-sm font-bold text-right text-white focus:outline-none focus:border-amber-500">
                    </div>

                    <button type="button" id="credit-settle-btn" onclick="submitCreditSettlement()" 
                            class="bg-amber-600 hover:bg-amber-500 active:translate-y-0.5 text-white font-display font-bold py-2 px-4 rounded-xl shadow-lg shadow-amber-600/10 transition-all flex items-center space-x-2 cursor-pointer text-xs">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Encaisser & Solder</span>
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

<!-- ================= DELIVERY PAYMENT & CREDIT MODAL ================= -->
<div id="delivery-payment-modal" class="hidden fixed inset-0 bg-slate-950/85 flex items-center justify-center p-4" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 60;">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-md p-6 shadow-2xl overflow-hidden flex flex-col space-y-5 animate-in fade-in zoom-in duration-150">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-slate-700/80 pb-4">
            <div class="flex items-center space-x-3">
                <div class="p-2.5 rounded-xl bg-emerald-500/15 border border-emerald-500/25 text-emerald-400 text-xl flex items-center justify-center">
                    💰
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h3 class="text-base font-bold text-white font-display">Paiement & Livraison</h3>
                        <span id="pay-modal-ticket-badge" class="px-2 py-0.5 rounded-md text-[11px] font-mono font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">N° 000000</span>
                    </div>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-xs text-slate-400">Client :</span>
                        <span id="pay-modal-client-name" class="text-xs font-bold text-slate-200">Nom du client</span>
                        <span id="pay-modal-client-type-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-700 text-slate-300">Enregistré</span>
                    </div>
                </div>
            </div>
            <button onclick="closeDeliveryPaymentModal()" class="text-slate-400 hover:text-white p-1 rounded-lg transition-colors cursor-pointer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Financial Breakdown Strip -->
        <div class="p-3.5 bg-slate-900/70 border border-slate-700/60 rounded-xl grid grid-cols-3 gap-2 text-center font-mono">
            <div>
                <p class="text-[10px] text-slate-400 uppercase font-sans font-medium">Montant Net</p>
                <p id="pay-modal-total-amount" class="text-sm font-bold text-white mt-0.5">0 DA</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 uppercase font-sans font-medium">Acompte Versé</p>
                <p id="pay-modal-paid-amount" class="text-sm font-bold text-emerald-400 mt-0.5">0 DA</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 uppercase font-sans font-medium">Solde Restant</p>
                <p id="pay-modal-balance-amount" class="text-sm font-bold text-amber-400 mt-0.5">0 DA</p>
            </div>
        </div>

        <!-- Cash input container (if balance > 0) -->
        <div id="pay-modal-cash-container" class="space-y-3">
            <div>
                <label for="pay-modal-cash-input" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    <span>Montant Reçu du Client</span>
                    <span class="text-[11px] font-normal text-slate-400">en Dinars (DA)</span>
                </label>
                <div class="relative">
                    <input type="number" id="pay-modal-cash-input" min="0" step="any"
                           oninput="onPayModalCashInput()"
                           onkeydown="if(event.key === 'Enter') { event.preventDefault(); submitDeliveryWithPayment(); }"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xl font-bold font-mono text-right text-emerald-400 focus:outline-none focus:border-emerald-500 transition-colors"
                           placeholder="0">
                    <span class="absolute left-4 top-3.5 text-sm font-bold text-slate-500">DA</span>
                </div>
            </div>

            <!-- Quick shortcut buttons -->
            <div class="flex items-center space-x-2 pt-0.5" id="pay-modal-quick-buttons">
                <button type="button" onclick="setPayModalAmount('exact')" 
                        class="flex-1 py-1.5 px-2 bg-slate-700/60 hover:bg-slate-700 border border-slate-600 rounded-lg text-xs font-bold text-slate-200 transition-all cursor-pointer text-center">
                    Solde Exact (<span id="pay-modal-quick-exact">0</span> DA)
                </button>
                <button type="button" onclick="setPayModalAmount(500)" 
                        class="py-1.5 px-3 bg-slate-700/60 hover:bg-slate-700 border border-slate-600 rounded-lg text-xs font-bold text-slate-200 transition-all cursor-pointer">
                    500 DA
                </button>
                <button type="button" onclick="setPayModalAmount(1000)" 
                        class="py-1.5 px-3 bg-slate-700/60 hover:bg-slate-700 border border-slate-600 rounded-lg text-xs font-bold text-slate-200 transition-all cursor-pointer">
                    1000 DA
                </button>
                <button type="button" onclick="setPayModalAmount(2000)" 
                        class="py-1.5 px-3 bg-slate-700/60 hover:bg-slate-700 border border-slate-600 rounded-lg text-xs font-bold text-slate-200 transition-all cursor-pointer">
                    2000 DA
                </button>
            </div>

            <!-- Real-time calculation feedback (Change to return / Credit report / Warning) -->
            <div id="pay-modal-change-box" class="hidden p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-between text-xs">
                <span class="text-emerald-300 font-semibold flex items-center space-x-1.5">
                    <span>💵</span>
                    <span>Monnaie à rendre au client :</span>
                </span>
                <span id="pay-modal-change-val" class="font-mono font-bold text-emerald-300 text-sm">0 DA</span>
            </div>

            <div id="pay-modal-partial-box" class="hidden p-3 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-between text-xs">
                <span class="text-amber-300 font-semibold flex items-center space-x-1.5">
                    <span>📝</span>
                    <span>Reste différé en crédit :</span>
                </span>
                <span id="pay-modal-partial-val" class="font-mono font-bold text-amber-300 text-sm">0 DA</span>
            </div>

            <div id="pay-modal-guest-warning" class="hidden p-3 rounded-xl bg-rose-500/15 border border-rose-500/30 flex items-start space-x-2 text-xs text-rose-300">
                <span class="text-rose-400 mt-0.5">⚠️</span>
                <span><strong>Client Passager :</strong> Crédit refusé. Le solde intégral doit être payé pour pouvoir retirer la commande.</span>
            </div>
        </div>

        <!-- Zero balance notice (when order is already fully paid) -->
        <div id="pay-modal-zero-balance-box" class="hidden p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-center text-xs text-emerald-400 font-bold space-y-1">
            <p class="text-base">✅ Solde Intégralement Réglé</p>
            <p class="text-slate-300 font-normal">Aucun paiement restant. Cliquez ci-dessous pour confirmer la remise et finaliser la livraison.</p>
        </div>

        <!-- Action Buttons -->
        <div class="border-t border-slate-700/80 pt-4 space-y-2.5">
            <div class="grid grid-cols-2 gap-3" id="pay-modal-buttons-grid">
                <!-- Button Credit (ONLY visible for registered clients!) -->
                <button type="button" id="pay-modal-credit-btn" onclick="submitDeliveryAsCredit()"
                        class="w-full py-3 px-4 rounded-xl bg-amber-600 hover:bg-amber-500 active:translate-y-0.5 text-white font-bold font-display text-xs shadow-lg shadow-amber-600/10 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Crédit (0 DA perçu)</span>
                </button>

                <!-- Button Encaisser & Livrer -->
                <button type="button" id="pay-modal-submit-btn" onclick="submitDeliveryWithPayment()"
                        class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:translate-y-0.5 text-white font-bold font-display text-xs shadow-lg shadow-emerald-600/10 transition-all flex items-center justify-center space-x-2 cursor-pointer col-span-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span id="pay-modal-submit-btn-text">Encaisser & Livrer</span>
                </button>
            </div>

            <!-- Cancel / Back Button -->
            <button type="button" onclick="closeDeliveryPaymentModal()"
                    class="w-full py-2 px-4 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-700/40 transition-colors text-center cursor-pointer">
                Retour
            </button>
        </div>

    </div>
</div>

<!-- ================= CARPET MEASUREMENT MODAL ================= -->
<div id="carpet-measure-modal" class="hidden fixed inset-0 bg-slate-950/80 flex items-center justify-center p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 100;">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-md p-6 shadow-2xl overflow-hidden flex flex-col space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-700/80 pb-3">
            <div class="flex items-center space-x-2.5">
                <div class="p-2 rounded-xl bg-amber-500/15 border border-amber-500/25 text-amber-400 text-lg">
                    📏
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-100 uppercase tracking-wide font-display">Métrage du Tapis</h3>
                    <p id="carpet-modal-item-name" class="text-xs text-indigo-400 font-semibold">Tapis</p>
                </div>
            </div>
            <button onclick="closeCarpetMeasureModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form fields -->
        <div class="space-y-4">
            <input type="hidden" id="carpet-measure-item-id">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Longueur (mètres)</label>
                    <div class="relative">
                        <input type="number" id="carpet-length-input" step="0.01" min="0.1" placeholder="Ex: 3.00" oninput="calculateCarpetAreaAndPrice()"
                               class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-3.5 py-2.5 text-sm font-mono font-bold text-slate-100 focus:outline-none pr-8">
                        <span class="absolute right-3 top-2.5 text-xs text-slate-500 font-mono">m</span>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Largeur (mètres)</label>
                    <div class="relative">
                        <input type="number" id="carpet-width-input" step="0.01" min="0.1" placeholder="Ex: 2.00" oninput="calculateCarpetAreaAndPrice()"
                               class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-3.5 py-2.5 text-sm font-mono font-bold text-slate-100 focus:outline-none pr-8">
                        <span class="absolute right-3 top-2.5 text-xs text-slate-500 font-mono">m</span>
                    </div>
                </div>
            </div>

            <!-- Calculated Area & Price Summary Box -->
            <div class="bg-slate-900/80 border border-indigo-500/20 rounded-xl p-4 space-y-2.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-medium">Surface calculée :</span>
                    <span id="carpet-area-display" class="font-mono font-black text-indigo-300 text-sm">0.00 m²</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-medium">Prix unitaire au m² :</span>
                    <span id="carpet-unit-price-display" class="font-mono font-bold text-slate-200">0 DA/m²</span>
                </div>
                <div class="border-t border-slate-800 pt-2 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wide">Montant Total du Tapis :</span>
                    <span id="carpet-total-price-display" class="font-mono font-black text-emerald-400 text-base">0 DA</span>
                </div>
            </div>

            <p class="text-[11px] text-slate-400 leading-relaxed italic">
                ℹ️ La validation enregistrera ces dimensions, mettra à jour le montant exact de la commande et marquera ce tapis comme <strong>Prêt</strong> pour retrait.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-3 pt-2">
            <button type="button" onclick="closeCarpetMeasureModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 transition-colors">
                Annuler
            </button>
            <button type="button" id="save-carpet-dimensions-btn" onclick="submitCarpetDimensions()" 
                    class="px-5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 active:translate-y-0.5 transition-all flex items-center space-x-2 cursor-pointer">
                <span>✓ Enregistrer & Marquer Prêt</span>
            </button>
        </div>
    </div>
</div>

<!-- ================= TOAST NOTIFICATION CONTAINER (Top Right) ================= -->
<div id="toast-container" class="fixed top-20 right-6 space-y-3 w-80 pointer-events-none" style="z-index: 99999;">
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
        
        const weightBanner = document.getElementById('modal-order-weight-banner');
        if (order.total_weight && parseFloat(order.total_weight) > 0) {
            if (weightBanner) {
                weightBanner.classList.remove('hidden');
                document.getElementById('modal-order-weight-value').textContent = `${parseFloat(order.total_weight).toFixed(2)} kg (${Math.round(parseFloat(order.total_weight) * 1000)} g)`;
            }
        } else {
            if (weightBanner) weightBanner.classList.add('hidden');
        }
        
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

        const totalArticlesPieces = items.reduce((sum, i) => {
            const isKilo = i.service_id === 4 || (i.service && i.service.name && i.service.name.toLowerCase().includes('kilo'));
            if (isKilo) return sum + (i.pieces || 1);
            return sum + (i.pieces || 1);
        }, 0);
        const countBadge = document.getElementById('modal-articles-total-count');
        if (countBadge) {
            countBadge.textContent = `${totalArticlesPieces} ${totalArticlesPieces > 1 ? 'articles' : 'article'}`;
        }

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
            
            const garment = item.garment_item || item.garmentItem || {};
            const srv = item.service || {};

            const isCarpet = (garment.is_carpet) || 
                             (garment.unit_type === 'm2') || 
                             (garment.name && garment.name.toLowerCase().includes('tapis')) ||
                             (garment.name && garment.name.toLowerCase().includes('m²')) ||
                             (item.area !== null && item.area !== undefined && item.area !== '');

            const isKiloItem = item.service_id === 4 || (srv.name && srv.name.toLowerCase().includes('kilo'));

            const qty = document.createElement('span');
            qty.className = "text-xs font-black text-indigo-400 font-mono";
            if (isKiloItem) {
                const piecesStr = item.pieces ? `${item.pieces} pcs ` : '';
                qty.textContent = `${piecesStr}(${parseFloat(item.quantity).toFixed(2)} kg)`;
            } else if (isCarpet) {
                qty.textContent = `${item.pieces || 1} pc`;
            } else {
                qty.textContent = `${parseFloat(item.quantity)}x`;
            }
            
            const name = document.createElement('span');
            name.className = `text-xs font-bold uppercase ${isItemDelivered ? 'text-slate-300' : 'text-slate-100'}`;
            name.textContent = garment.name || 'Article';

            const serviceBadge = document.createElement('span');
            serviceBadge.className = "text-[9px] bg-slate-800 text-slate-400 font-bold px-1.5 py-0.2 rounded uppercase";
            serviceBadge.textContent = srv.name || '';

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

            // Carpet Dimensions or Pending Measurement Badge
            if (isCarpet) {
                const carpetInfo = document.createElement('div');
                if (item.is_measured && item.area) {
                    carpetInfo.className = "inline-flex items-center space-x-1.5 px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/20";
                    carpetInfo.innerHTML = `
                        <span>📏 ${item.length}m × ${item.width}m = ${item.area} m² (${parseFloat(item.unit_price).toFixed(0)} DA/m²)</span>
                        ${!isItemDelivered ? `<button type="button" onclick="event.stopPropagation(); openCarpetMeasureModal(${item.id})" class="text-indigo-400 hover:text-indigo-300 underline ml-1.5 cursor-pointer font-bold">Modifier</button>` : ''}
                    `;
                } else {
                    carpetInfo.className = "inline-flex items-center space-x-1.5 px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30";
                    carpetInfo.innerHTML = `
                        <span>⚠️ Métrage en attente (${parseFloat(item.unit_price).toFixed(0)} DA/m²)</span>
                    `;
                }
                details.appendChild(carpetInfo);
            }

            left.appendChild(details);
            row.appendChild(left);

            // Right container (price + action button / delivered badge)
            const rightSide = document.createElement('div');
            rightSide.className = "flex items-center space-x-3 shrink-0";

            // Price tag
            const rightPrice = document.createElement('div');
            if (isCarpet && !item.is_measured) {
                rightPrice.className = "text-xs font-bold text-amber-400 font-mono";
                rightPrice.textContent = "À mesurer";
            } else {
                rightPrice.className = "text-xs font-bold text-slate-400 font-mono";
                rightPrice.textContent = `${parseFloat(item.total_price).toFixed(0)} DA`;
            }
            rightSide.appendChild(rightPrice);

            if (isItemDelivered) {
                const actionBadge = document.createElement('span');
                actionBadge.className = "px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20";
                actionBadge.textContent = 'Livré';
                rightSide.appendChild(actionBadge);
            } else if (isCarpet && !item.is_measured) {
                const measureBtn = document.createElement('button');
                measureBtn.type = 'button';
                measureBtn.id = `modal-item-btn-${itemId}`;
                measureBtn.className = "px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all border bg-amber-500/20 border-amber-500/30 text-amber-300 hover:bg-amber-500/30 cursor-pointer flex items-center space-x-1";
                measureBtn.innerHTML = `<span>📏</span><span>Mesurer & Marquer Prêt</span>`;
                measureBtn.onclick = (e) => {
                    e.stopPropagation();
                    openCarpetMeasureModal(itemId);
                };

                checkbox.onclick = (e) => {
                    e.stopPropagation();
                    openCarpetMeasureModal(itemId);
                };

                rightSide.appendChild(measureBtn);
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
        const creditSection = document.getElementById('modal-credit-settle-section');
        const guestWarning = document.getElementById('modal-guest-credit-warning');

        const totalItems = items.length;
        const deliveredItems = items.filter(i => i.is_delivered);
        const undeliveredItems = items.filter(i => !i.is_delivered);
        const readyToDeliver = undeliveredItems.filter(i => !!modalItemsState[i.id]);

        const isGuest = !currentOrder.client || currentOrder.client.code === 'GUEST' || 
                        (currentOrder.client.name && (currentOrder.client.name.toLowerCase().includes('passage') || currentOrder.client.name.toLowerCase().includes('passager')));
        const currentBalance = parseFloat(currentOrder.balance_amount) || 0;

        if (totalItems > 0 && deliveredItems.length === totalItems) {
            // Completely delivered
            deliverySection.classList.add('hidden');
            const devDate = currentOrder.actual_delivery_date ? new Date(currentOrder.actual_delivery_date) : new Date();
            const dateStr = formatDateTime(devDate);
            document.getElementById('modal-delivered-date').textContent = dateStr;
            deliveredAlert.classList.remove('hidden');

            // Credit settlement section: show if delivered and balance > 0
            if (creditSection) {
                if (currentBalance > 0) {
                    creditSection.classList.remove('hidden');
                    document.getElementById('credit-settle-balance').textContent = `${currentBalance.toFixed(0)} DA`;
                    document.getElementById('credit-settle-input').value = currentBalance.toFixed(0);
                } else {
                    creditSection.classList.add('hidden');
                }
            }
        } else {
            // Still has undelivered items
            deliverySection.classList.remove('hidden');
            deliveredAlert.classList.add('hidden');
            if (creditSection) creditSection.classList.add('hidden');

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
        closeDeliveryPaymentModal();
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
                updateOrderRowInTable(data.order);

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

    // ==========================================
    // NOUVELLE GESTION DE PAIEMENT & LIVRAISON (POPUP DÉDIÉ)
    // ==========================================

    function openDeliveryPaymentModal() {
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

        const isGuest = !currentOrder.client || currentOrder.client.code === 'GUEST' || 
                        (currentOrder.client.name && (currentOrder.client.name.toLowerCase().includes('passage') || currentOrder.client.name.toLowerCase().includes('passager')));
        const clientName = currentOrder.client ? currentOrder.client.name : 'Client Passager';
        const total = parseFloat(currentOrder.total_amount) || 0;
        const paid = parseFloat(currentOrder.paid_amount) || 0;
        const balance = parseFloat(currentOrder.balance_amount) || 0;

        // Set Header details
        document.getElementById('pay-modal-ticket-badge').textContent = `Ticket N° ${currentOrder.ticket_number}`;
        document.getElementById('pay-modal-client-name').textContent = clientName;

        const typeBadge = document.getElementById('pay-modal-client-type-badge');
        if (isGuest) {
            typeBadge.className = "px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30";
            typeBadge.textContent = "Client Passager";
        } else {
            typeBadge.className = "px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30";
            typeBadge.textContent = "Client Enregistré";
        }

        // Set financial breakdown
        document.getElementById('pay-modal-total-amount').textContent = `${total.toFixed(0)} DA`;
        document.getElementById('pay-modal-paid-amount').textContent = `${paid.toFixed(0)} DA`;
        document.getElementById('pay-modal-balance-amount').textContent = `${balance.toFixed(0)} DA`;

        const quickExact = document.getElementById('pay-modal-quick-exact');
        if (quickExact) quickExact.textContent = balance.toFixed(0);

        const cashInput = document.getElementById('pay-modal-cash-input');
        const zeroBalanceBox = document.getElementById('pay-modal-zero-balance-box');
        const cashContainer = document.getElementById('pay-modal-cash-container');
        const creditBtn = document.getElementById('pay-modal-credit-btn');
        const submitBtn = document.getElementById('pay-modal-submit-btn');
        const submitBtnText = document.getElementById('pay-modal-submit-btn-text');
        const guestWarning = document.getElementById('pay-modal-guest-warning');

        if (balance <= 0) {
            // Already fully paid
            zeroBalanceBox.classList.remove('hidden');
            cashContainer.classList.add('hidden');
            creditBtn.classList.add('hidden');
            submitBtn.className = "w-full col-span-2 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:translate-y-0.5 text-white font-bold font-display text-xs shadow-lg shadow-emerald-600/10 transition-all flex items-center justify-center space-x-2 cursor-pointer";
            submitBtnText.textContent = "Confirmer la Livraison";
        } else {
            // Balance remaining
            zeroBalanceBox.classList.add('hidden');
            cashContainer.classList.remove('hidden');
            cashInput.value = balance.toFixed(0);

            if (isGuest) {
                // Client Passager: Credit button STRICTLY forbidden / hidden
                creditBtn.classList.add('hidden');
                submitBtn.className = "w-full col-span-2 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:translate-y-0.5 text-white font-bold font-display text-xs shadow-lg shadow-emerald-600/10 transition-all flex items-center justify-center space-x-2 cursor-pointer";
                submitBtnText.textContent = `Encaisser ${balance.toFixed(0)} DA & Livrer`;
                guestWarning.classList.remove('hidden');
            } else {
                // Registered Client: Credit button available
                creditBtn.classList.remove('hidden');
                submitBtn.className = "w-full col-span-1 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:translate-y-0.5 text-white font-bold font-display text-xs shadow-lg shadow-emerald-600/10 transition-all flex items-center justify-center space-x-2 cursor-pointer";
                submitBtnText.textContent = "Encaisser & Livrer";
                guestWarning.classList.add('hidden');
            }

            onPayModalCashInput();
        }

        document.getElementById('delivery-payment-modal').classList.remove('hidden');
        if (balance > 0 && cashInput) {
            setTimeout(() => {
                cashInput.focus();
                cashInput.select();
            }, 100);
        }
    }

    function closeDeliveryPaymentModal() {
        const modal = document.getElementById('delivery-payment-modal');
        if (modal) modal.classList.add('hidden');
    }

    function setPayModalAmount(amount) {
        if (!currentOrder) return;
        const balance = parseFloat(currentOrder.balance_amount) || 0;
        const cashInput = document.getElementById('pay-modal-cash-input');
        if (!cashInput) return;

        if (amount === 'exact') {
            cashInput.value = balance.toFixed(0);
        } else {
            cashInput.value = amount;
        }
        onPayModalCashInput();
        cashInput.focus();
    }

    function onPayModalCashInput() {
        if (!currentOrder) return;
        const balance = parseFloat(currentOrder.balance_amount) || 0;
        const cashInput = document.getElementById('pay-modal-cash-input');
        if (!cashInput) return;

        const val = parseFloat(cashInput.value) || 0;
        const isGuest = !currentOrder.client || currentOrder.client.code === 'GUEST' || 
                        (currentOrder.client.name && (currentOrder.client.name.toLowerCase().includes('passage') || currentOrder.client.name.toLowerCase().includes('passager')));

        const changeBox = document.getElementById('pay-modal-change-box');
        const changeVal = document.getElementById('pay-modal-change-val');
        const partialBox = document.getElementById('pay-modal-partial-box');
        const partialVal = document.getElementById('pay-modal-partial-val');
        const guestWarning = document.getElementById('pay-modal-guest-warning');

        if (val > balance) {
            const diff = val - balance;
            changeBox.classList.remove('hidden');
            changeVal.textContent = `${diff.toFixed(0)} DA`;
            partialBox.classList.add('hidden');
            guestWarning.classList.add('hidden');
        } else if (val < balance) {
            const diff = balance - val;
            changeBox.classList.add('hidden');
            if (isGuest) {
                guestWarning.classList.remove('hidden');
                partialBox.classList.add('hidden');
            } else {
                guestWarning.classList.add('hidden');
                partialBox.classList.remove('hidden');
                partialVal.textContent = `${diff.toFixed(0)} DA`;
            }
        } else {
            // Exact balance
            changeBox.classList.add('hidden');
            partialBox.classList.add('hidden');
            guestWarning.classList.add('hidden');
        }
    }

    function submitDeliveryWithPayment() {
        if (!currentOrder) return;
        const balance = parseFloat(currentOrder.balance_amount) || 0;
        const isGuest = !currentOrder.client || currentOrder.client.code === 'GUEST' || 
                        (currentOrder.client.name && (currentOrder.client.name.toLowerCase().includes('passage') || currentOrder.client.name.toLowerCase().includes('passager')));

        let cashToCollect = 0;
        if (balance > 0) {
            const cashInput = document.getElementById('pay-modal-cash-input');
            const entered = parseFloat(cashInput ? cashInput.value : 0) || 0;

            if (isGuest && entered < balance) {
                showAppAlert(`Client Passager : Aucun crédit n'est autorisé. La totalité du solde (${balance.toFixed(0)} DA) doit être encaissée pour valider la livraison.`, "error", "Crédit non autorisé");
                if (cashInput) {
                    cashInput.value = balance.toFixed(0);
                    onPayModalCashInput();
                    cashInput.focus();
                }
                return;
            }

            // Cap the cash to the actual balance needed so surplus is considered change returned
            cashToCollect = Math.min(entered, balance);
        }

        executeDelivery(cashToCollect);
    }

    function submitDeliveryAsCredit() {
        if (!currentOrder) return;
        const isGuest = !currentOrder.client || currentOrder.client.code === 'GUEST' || 
                        (currentOrder.client.name && (currentOrder.client.name.toLowerCase().includes('passage') || currentOrder.client.name.toLowerCase().includes('passager')));

        if (isGuest) {
            showAppAlert("Le crédit est strictement réservé aux clients enregistrés. Impossible de reporter en crédit pour un client passager.", "error", "Crédit refusé");
            return;
        }

        // Deliver with 0 DA cash collected (entire remaining balance goes to credit)
        executeDelivery(0);
    }

    function executeDelivery(cashCollected) {
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

        const submitBtn = document.getElementById('pay-modal-submit-btn');
        const creditBtn = document.getElementById('pay-modal-credit-btn');

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-wait');
        }
        if (creditBtn) {
            creditBtn.disabled = true;
            creditBtn.classList.add('opacity-75', 'cursor-wait');
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

                closeDeliveryPaymentModal();

                if (data.is_all_delivered) {
                    showAppAlert(data.message || "Commande entièrement livrée avec succès !", "success", "Livraison Totale", () => {
                        closeOrderDetailModal();
                    });
                } else {
                    showAppAlert(data.message || "Livraison partielle effectuée avec succès !", "success", "Livraison Partielle", () => {
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
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-wait');
            }
            if (creditBtn) {
                creditBtn.disabled = false;
                creditBtn.classList.remove('opacity-75', 'cursor-wait');
            }
        });
    }

    // Alias for backwards compatibility
    function submitOrderDelivery() {
        openDeliveryPaymentModal();
    }

    // Finalize credit settlement for an already delivered order
    function submitCreditSettlement() {
        if (!currentOrder) return;
        const cashAmount = parseFloat(document.getElementById('credit-settle-input').value) || 0;
        if (cashAmount <= 0) {
            showAppAlert("Veuillez indiquer un montant valide à percevoir.", "warning", "Montant Invalide");
            return;
        }

        const btn = document.getElementById('credit-settle-btn');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-wait');
        }

        fetch(`/api/orders/${currentOrder.id}/settle-credit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ cash_collected: cashAmount })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.order) {
                    ordersMap[data.order.id] = data.order;
                    currentOrder = data.order;
                }
                const orderId = data.order ? data.order.id : currentOrder.id;

                const paidEl = document.getElementById(`order-paid-${orderId}`);
                if (paidEl) paidEl.textContent = parseFloat(data.order.paid_amount).toFixed(0);

                const balanceEl = document.getElementById(`order-balance-${orderId}`);
                if (balanceEl) {
                    balanceEl.textContent = parseFloat(data.order.balance_amount).toFixed(0);
                    balanceEl.className = data.order.balance_amount > 0 ? "text-amber-500" : "text-emerald-500";
                    balanceEl.parentElement.className = `font-bold font-mono ${data.order.balance_amount > 0 ? 'text-amber-500' : 'text-emerald-500'}`;
                }

                document.getElementById('modal-paid-amount').textContent = `${parseFloat(data.order.paid_amount).toFixed(0)} DA`;
                document.getElementById('modal-balance-amount').textContent = `${parseFloat(data.order.balance_amount).toFixed(0)} DA`;

                showAppAlert(data.message, "success", "Règlement Enregistré", () => {
                    closeOrderDetailModal();
                    window.location.reload();
                });
            } else {
                showAppAlert(data.message || "Erreur lors du règlement du solde.", "error", "Erreur");
            }
        })
        .catch(err => {
            showAppAlert("Erreur de communication avec le serveur.", "error", "Erreur");
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-wait');
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

    // ================= CARPET MEASUREMENT LOGIC =================
    let activeCarpetItem = null;

    function openCarpetMeasureModal(itemId) {
        if (!currentOrder) return;
        const items = currentOrder.order_items || currentOrder.orderItems || [];
        const item = items.find(i => i.id == itemId);
        if (!item) return;
        activeCarpetItem = item;

        document.getElementById('carpet-measure-item-id').value = item.id;
        const garment = item.garment_item || item.garmentItem;
        const srv = item.service;
        const itmName = (garment ? garment.name : 'Tapis') + (srv ? ` (${srv.name})` : '');
        document.getElementById('carpet-modal-item-name').textContent = itmName;
        document.getElementById('carpet-length-input').value = item.length || '';
        document.getElementById('carpet-width-input').value = item.width || '';
        document.getElementById('carpet-unit-price-display').textContent = `${parseFloat(item.unit_price).toFixed(0)} DA/m²`;

        calculateCarpetAreaAndPrice();
        document.getElementById('carpet-measure-modal').classList.remove('hidden');
        setTimeout(() => document.getElementById('carpet-length-input').focus(), 100);
    }

    function closeCarpetMeasureModal() {
        document.getElementById('carpet-measure-modal').classList.add('hidden');
        activeCarpetItem = null;
    }

    function calculateCarpetAreaAndPrice() {
        if (!activeCarpetItem) return;
        const length = parseFloat(document.getElementById('carpet-length-input').value) || 0;
        const width = parseFloat(document.getElementById('carpet-width-input').value) || 0;
        const area = Math.round(length * width * 100) / 100;
        const unitPrice = parseFloat(activeCarpetItem.unit_price) || 0;
        const total = Math.round(area * unitPrice);

        document.getElementById('carpet-area-display').textContent = `${area.toFixed(2)} m²`;
        document.getElementById('carpet-total-price-display').textContent = `${total.toLocaleString()} DA`;
    }

    function submitCarpetDimensions() {
        if (!activeCarpetItem) return;
        const length = parseFloat(document.getElementById('carpet-length-input').value);
        const width = parseFloat(document.getElementById('carpet-width-input').value);

        if (!length || length <= 0 || !width || width <= 0) {
            showAppAlert("Veuillez saisir une longueur et une largeur valides (supérieures à 0).", "error", "Dimensions invalides");
            return;
        }

        const btn = document.getElementById('save-carpet-dimensions-btn');
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-wait');

        fetch(`/api/order-items/${activeCarpetItem.id}/dimensions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                length: length,
                width: width,
                area: Math.round(length * width * 100) / 100,
                unit_price: activeCarpetItem.unit_price
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update memory cache with updated order
                if (data.order) {
                    ordersMap[data.order.id] = data.order;
                    currentOrder = data.order;
                }

                // Update modal items state
                modalItemsState[activeCarpetItem.id] = true;

                closeCarpetMeasureModal();

                // Re-render order modal with fresh order details and recalculated prices
                openOrderDetailModal(currentOrder.id);

                // Update table row in background
                updateOrderRowInTable(currentOrder);

                showAppAlert(data.message, "success", "Dimensions enregistrées");
            } else {
                showAppAlert(data.message || "Erreur lors de l'enregistrement des dimensions.", "error", "Erreur");
            }
        })
        .catch(err => {
            showAppAlert("Erreur de communication avec le serveur.", "error", "Erreur");
        })
        .finally(() => {
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-wait');
        });
    }

    function updateOrderRowInTable(order) {
        if (!order) return;
        
        // Total cell
        const totalSpan = document.getElementById(`order-total-${order.id}`);
        if (totalSpan) {
            totalSpan.textContent = Number(order.total_amount).toLocaleString('fr-FR');
        }

        // Paid cell
        const paidSpan = document.getElementById(`order-paid-${order.id}`);
        if (paidSpan) {
            paidSpan.textContent = Number(order.paid_amount).toLocaleString('fr-FR');
        }

        // Balance cell
        const balanceSpan = document.getElementById(`order-balance-${order.id}`);
        if (balanceSpan) {
            balanceSpan.textContent = Number(order.balance_amount).toLocaleString('fr-FR');
            balanceSpan.className = order.balance_amount > 0 ? 'text-amber-500' : 'text-emerald-500';
        }

        // Status badge
        const badge = document.getElementById(`order-status-badge-${order.id}`);
        if (badge) {
            badge.className = `px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider status-badge-${order.status}`;
            if (order.status === 'pending') {
                badge.textContent = 'En cours';
            } else if (order.status === 'ready') {
                badge.textContent = 'Prêt';
            } else if (order.status === 'partially_delivered') {
                badge.textContent = 'Livr. Partielle';
            } else {
                badge.textContent = 'Livré';
            }
        }
    }
</script>
@endsection
