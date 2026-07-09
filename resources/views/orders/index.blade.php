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
    .status-badge-delivered {
        background-color: rgba(59, 130, 246, 0.1);
        color: rgb(59, 130, 246);
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
</style>
@endsection

@section('content')
<div class="flex-1 flex flex-col min-w-0 bg-slate-900 overflow-hidden">
    <!-- Filters & Search sub-bar -->
    <div class="bg-slate-800/40 p-4 border-b border-slate-700/50 shrink-0 flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- Status Tabs -->
        <div class="flex gap-2 w-full md:w-auto overflow-x-auto">
            <a href="{{ route('orders.index', ['status' => 'all', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors {{ $status === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Toutes les commandes
            </a>
            <a href="{{ route('orders.index', ['status' => 'express', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors {{ $status === 'express' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                ⚡ Express
            </a>
            <a href="{{ route('orders.index', ['status' => 'pending', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                En préparation
            </a>
            <a href="{{ route('orders.index', ['status' => 'ready', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors {{ $status === 'ready' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Prêtes pour retrait
            </a>
            <a href="{{ route('orders.index', ['status' => 'delivered', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold font-display tracking-wide transition-colors {{ $status === 'delivered' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Livrées / Clôturées
            </a>
        </div>

        <!-- Navigation shortcut back to checkout -->
        <div class="flex gap-3 w-full md:w-auto justify-end">
            <a href="{{ route('checkout.index') }}" class="px-4 py-2 rounded-lg text-xs font-bold text-slate-300 bg-slate-800 border border-slate-700 hover:text-white hover:bg-slate-700 transition-colors flex items-center space-x-1">
                <span>&larr; Caisse Tactile</span>
            </a>

            <!-- Search input form -->
            <form method="GET" action="{{ route('orders.index') }}" class="relative w-64">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher ticket, client..." 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-3 pr-8 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                @if($search)
                    <a href="{{ route('orders.index', ['status' => $status]) }}" class="absolute right-8 top-2.5 text-slate-500 hover:text-slate-300">
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
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
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
                            <tr class="hover:bg-slate-800/20 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-200">
                                    #{{ $order->ticket_number }}
                                    @if($order->is_express)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black bg-rose-500/10 text-rose-400 border border-rose-500/20 animate-pulse uppercase tracking-wider ml-1">Express</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-white">{{ $order->client->name }}</div>
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
                                        @else
                                            Livré
                                        @endif
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right flex items-center justify-end space-x-2">
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
                                    <button onclick='openOrderDetailModal({{ json_encode($order) }})'
                                            class="bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold py-1.5 px-3 rounded-lg border border-slate-700 transition-all cursor-pointer">
                                        Détails & Retrait
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
<div id="order-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-[600px] max-h-[90vh] flex flex-col shadow-2xl overflow-hidden transform scale-95 transition-all">
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-white font-display">Ticket N° <span id="modal-ticket-no" class="font-mono">081916</span></h3>
                <p class="text-xs text-slate-400">Client : <span id="modal-client-name" class="font-bold">Merad</span></p>
            </div>
            <div class="flex items-center space-x-3">
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

                <button onclick="submitOrderDelivery()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-display font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-emerald-600/10 transition-colors flex items-center justify-center space-x-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Confirmer la Livraison & Clôturer</span>
                </button>
            </div>

            <!-- Already delivered label -->
            <div id="modal-delivered-alert" class="hidden p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl text-center text-xs font-bold text-blue-400 uppercase">
                Ce ticket a été retiré et clôturé le <span id="modal-delivered-date" class="font-mono">29/09/2021 à 12:00</span>.
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
    let currentOrder = null;

    function openOrderDetailModal(order) {
        currentOrder = order;

        // Set text values
        document.getElementById('modal-ticket-no').textContent = order.ticket_number;
        document.getElementById('modal-client-name').textContent = order.client.name;
        document.getElementById('modal-total-amount').textContent = `${parseFloat(order.total_amount).toFixed(0)} DA`;
        document.getElementById('modal-paid-amount').textContent = `${parseFloat(order.paid_amount).toFixed(0)} DA`;
        
        const balance = parseFloat(order.balance_amount);
        document.getElementById('modal-balance-amount').textContent = `${balance.toFixed(0)} DA`;
        
        // Prefill cash collected with the remaining balance
        document.getElementById('cash-collected-input').value = balance > 0 ? balance.toFixed(0) : 0;
        document.getElementById('delivery-suggested-balance').textContent = `${balance.toFixed(0)} DA`;

        // Render items inside ticket
        const itemsBox = document.getElementById('modal-items-container');
        itemsBox.innerHTML = '';

        order.order_items.forEach(item => {
            const row = document.createElement('div');
            row.className = "flex items-center justify-between p-3 bg-slate-900/40 border border-slate-700/40 rounded-xl";

            // Description of item
            const left = document.createElement('div');
            left.className = "flex items-start space-x-3";

            // Tactile toggle button/checkbox for is_ready
            // Disabled if order is already delivered
            const isDelivered = order.status === 'delivered';
            
            const checkbox = document.createElement('button');
            checkbox.className = `h-6 w-6 rounded-md border flex items-center justify-center transition-all ${isDelivered ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'} ${item.is_ready ? 'bg-emerald-600 border-emerald-500 text-white' : 'border-slate-600 bg-slate-900 hover:border-slate-400'}`;
            
            checkbox.innerHTML = item.is_ready ? `
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            ` : '';

            left.appendChild(checkbox);

            const details = document.createElement('div');
            
            const titleLine = document.createElement('div');
            titleLine.className = "flex items-center space-x-2";
            
            const qty = document.createElement('span');
            qty.className = "text-xs font-black text-indigo-400 font-mono";
            qty.textContent = `${parseFloat(item.quantity)}x`;
            
            const name = document.createElement('span');
            name.className = "text-xs font-bold text-slate-100 uppercase";
            name.textContent = item.garment_item.name;

            const serviceBadge = document.createElement('span');
            serviceBadge.className = "text-[9px] bg-slate-800 text-slate-400 font-bold px-1.5 py-0.2 rounded uppercase";
            serviceBadge.textContent = item.service.name;

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

            left.appendChild(details);
            row.appendChild(left);

            // Right container (price + action button)
            const rightSide = document.createElement('div');
            rightSide.className = "flex items-center space-x-3 shrink-0";

            // Price tag
            const rightPrice = document.createElement('div');
            rightPrice.className = "text-xs font-bold text-slate-400 font-mono";
            rightPrice.textContent = `${parseFloat(item.total_price).toFixed(0)} DA`;
            rightSide.appendChild(rightPrice);

            // Small tactile action button
            const actionBtn = document.createElement('button');
            actionBtn.className = `px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all border ${isDelivered ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'} ${item.is_ready ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-indigo-600 border-indigo-500 hover:bg-indigo-500 text-white'}`;
            actionBtn.textContent = item.is_ready ? 'Prêt' : 'Marquer Prêt';
            
            if (!isDelivered) {
                const clickHandler = () => {
                    const nextReadyState = !item.is_ready;
                    toggleItemReady(item.id, nextReadyState, checkbox, actionBtn);
                    item.is_ready = nextReadyState; // update local state for toggling back/forth
                };
                checkbox.onclick = clickHandler;
                actionBtn.onclick = clickHandler;
            }
            
            rightSide.appendChild(actionBtn);
            row.appendChild(rightSide);

            itemsBox.appendChild(row);
        });

        // Toggle Delivery Panels based on status
        const deliverySection = document.getElementById('modal-delivery-section');
        const deliveredAlert = document.getElementById('modal-delivered-alert');

        if (order.status === 'delivered') {
            deliverySection.classList.add('hidden');
            
            const devDate = order.actual_delivery_date ? new Date(order.actual_delivery_date) : new Date();
            const dateStr = devDate.toLocaleDateString('fr-FR') + ' à ' + devDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            document.getElementById('modal-delivered-date').textContent = dateStr;
            deliveredAlert.classList.remove('hidden');
        } else {
            deliverySection.classList.remove('hidden');
            deliveredAlert.classList.add('hidden');
        }

        document.getElementById('order-modal').classList.remove('hidden');
    }

    function closeOrderDetailModal() {
        document.getElementById('order-modal').classList.add('hidden');
        currentOrder = null;
    }

    // Toggle Item ready state via AJAX
    function toggleItemReady(itemId, isReady, checkboxBtn, actionBtn) {
        fetch(`/api/order-items/${itemId}/ready`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_ready: isReady })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update checkbox UI state
                if (data.is_ready) {
                    checkboxBtn.classList.add('bg-emerald-600', 'border-emerald-500', 'text-white');
                    checkboxBtn.classList.remove('border-slate-600', 'bg-slate-900');
                    checkboxBtn.innerHTML = `
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    `;
                } else {
                    checkboxBtn.classList.remove('bg-emerald-600', 'border-emerald-500', 'text-white');
                    checkboxBtn.classList.add('border-slate-600', 'bg-slate-900');
                    checkboxBtn.innerHTML = '';
                }

                // Update action button UI state
                if (actionBtn) {
                    if (data.is_ready) {
                        actionBtn.className = "px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg border bg-emerald-500/10 text-emerald-400 border-emerald-500/20";
                        actionBtn.textContent = 'Prêt';
                    } else {
                        actionBtn.className = "px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg border bg-indigo-600 border-indigo-500 hover:bg-indigo-500 text-white cursor-pointer";
                        actionBtn.textContent = 'Marquer Prêt';
                    }
                }

                // Update current cached object is_ready state
                if (currentOrder) {
                    const idx = currentOrder.order_items.findIndex(oi => oi.id === itemId);
                    if (idx !== -1) currentOrder.order_items[idx].is_ready = data.is_ready;

                    // Update main screen table status badge instantly
                    const badge = document.getElementById(`order-status-badge-${currentOrder.id}`);
                    if (badge) {
                        badge.className = `px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider status-badge-${data.order_status}`;
                        if (data.order_status === 'pending') {
                            badge.textContent = 'En cours';
                        } else if (data.order_status === 'ready') {
                            badge.textContent = 'Prêt';
                        } else {
                            badge.textContent = 'Livré';
                        }
                    }
                    currentOrder.status = data.order_status;
                }
            }
        })
        .catch(err => {
            showAppAlert("Erreur de modification d'état de préparation.", "error", "Erreur");
        });
    }

    // Finalize order delivery
    function submitOrderDelivery() {
        if (!currentOrder) return;

        const cashCollected = parseFloat(document.getElementById('cash-collected-input').value) || 0;

        fetch(`/api/orders/${currentOrder.id}/deliver`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ cash_collected: cashCollected })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAppAlert(data.message, "success", "Livraison Effectuée", () => {
                    const orderId = currentOrder.id;

                    // Update paid amount instantly in list
                    const paidEl = document.getElementById(`order-paid-${orderId}`);
                    if (paidEl) {
                        const totalText = document.getElementById(`order-status-badge-${orderId}`)
                            ?.closest('tr')?.querySelector('td:nth-child(5)')?.textContent || '0';
                        paidEl.textContent = parseFloat(totalText).toFixed(0);
                    }

                    // Update balance amount instantly in list
                    const balanceEl = document.getElementById(`order-balance-${orderId}`);
                    if (balanceEl) {
                        balanceEl.textContent = "0";
                        balanceEl.className = "text-emerald-500";
                        balanceEl.parentElement.className = "font-bold font-mono text-emerald-500";
                    }

                    // Update status badge instantly in list
                    const badge = document.getElementById(`order-status-badge-${orderId}`);
                    if (badge) {
                        badge.className = "px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider status-badge-delivered";
                        badge.textContent = 'Livré';
                    }

                    closeOrderDetailModal();
                });
            } else {
                showAppAlert(data.message, "error", "Erreur");
            }
        })
        .catch(err => {
            showAppAlert("Erreur lors de la livraison de la commande.", "error", "Erreur");
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
</script>
@endsection
