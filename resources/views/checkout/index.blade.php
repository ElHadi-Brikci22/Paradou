@extends('layouts.app')

@section('title', 'Caisse Tactile')

@section('styles')
<style>
    /* Styling for active tabs */
    .service-tab-active {
        background-color: rgb(79, 70, 229); /* Indigo 600 */
        color: white;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.2);
    }
    .target-pill-active {
        background-color: rgb(51, 65, 85); /* Slate 700 */
        color: white;
        border-color: rgb(99, 102, 241); /* Indigo 500 */
    }
    /* Grid adjustments for catalog */
    .catalog-grid {
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    }
</style>
@endsection

@section('content')
<!-- Left Panel: Catalog (2/3 width on large screens) -->
<div class="flex-1 flex flex-col min-w-0 border-r border-slate-700/50 bg-slate-900">
    <!-- Services Tabs (Top horizontal bar) -->
    <div class="bg-slate-800/40 p-4 border-b border-slate-700/50 shrink-0 flex gap-2 overflow-x-auto">
        @foreach($services as $service)
            <button onclick="selectService({{ $service->id }})" 
                    id="service-tab-{{ $service->id }}" 
                    class="service-tab shrink-0 px-5 py-3 rounded-xl font-display font-semibold text-sm transition-all duration-200 bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white">
                {{ $service->name }}
            </button>
        @endforeach
    </div>

    <!-- Target Public Pills (Secondary sub-bar) -->
    <div id="targets-bar" class="bg-slate-800/10 px-6 py-3 border-b border-slate-700/30 shrink-0 flex gap-2 overflow-x-auto">
        @foreach($targets as $target)
            <button onclick="selectTarget({{ $target->id }})" 
                    id="target-pill-{{ $target->id }}" 
                    class="target-pill shrink-0 px-4 py-1.5 rounded-full border border-slate-700 bg-slate-800/50 text-slate-400 font-medium text-xs transition-all duration-150 hover:text-slate-200">
                {{ $target->name }}
            </button>
        @endforeach
    </div>

    <!-- Items Grid (Scrollable central container) -->
    <div class="flex-1 overflow-y-auto p-6">
        <div id="catalog-grid" class="grid catalog-grid gap-4">
            <!-- Dynamically populated by JS based on service and target selection -->
        </div>
        <div id="catalog-empty" class="hidden flex flex-col items-center justify-center h-full text-slate-500 py-12">
            <svg class="h-16 w-16 mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <p class="font-display font-medium text-lg">Aucun article dans cette catégorie</p>
            <p class="text-sm">Veuillez choisir un autre filtre de service ou de public.</p>
        </div>
    </div>
</div>

<!-- Right Panel: Checkout / Cart (1/3 width) -->
<div class="w-96 shrink-0 bg-slate-800/40 backdrop-blur-md flex flex-col overflow-hidden">
    <!-- Client Selection Box -->
    <div class="p-4 border-b border-slate-700/50 shrink-0">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center space-x-2">
                <button onclick="toggleClientPanel()" class="text-slate-400 hover:text-white transition-colors cursor-pointer" id="client-toggle-btn">
                    <svg class="h-4 w-4 transform transition-transform" id="client-toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Client associé</label>
            </div>
            <button onclick="openNewClientModal()" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center space-x-1 cursor-pointer">
                <span>+ Nouveau</span>
            </button>
        </div>
        
        <!-- Collapsible client fields panel -->
        <div id="client-fields-panel" class="space-y-3 transition-all duration-200">
            <!-- Client input & search -->
            <div class="relative">
                <div class="flex">
                    <div class="relative flex-1">
                        <input type="text" id="client-search-input" oninput="searchClients(this.value)" 
                               placeholder="Rechercher client (nom, code, tél)..." 
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-3 pr-8 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
                        <button id="client-clear-btn" onclick="clearSelectedClient()" class="hidden absolute right-2.5 top-2.5 text-slate-500 hover:text-slate-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Floating search results drop -->
                <div id="client-search-results" class="hidden absolute left-0 right-0 mt-1 bg-slate-800 border border-slate-700 rounded-lg shadow-xl z-30 max-h-60 overflow-y-auto">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Selected client info card -->
            <div id="selected-client-card" class="p-3 bg-slate-900/50 border border-indigo-500/20 rounded-lg flex items-center justify-between">
                <div>
                    <h4 id="selected-client-name" class="text-sm font-bold text-slate-200">Client Passage</h4>
                    <div class="flex items-center space-x-2 mt-0.5">
                        <span id="selected-client-code" class="text-[10px] bg-slate-800 text-slate-400 font-mono px-1.5 py-0.5 rounded">GUEST</span>
                        <span id="selected-client-discount" class="text-[10px] bg-indigo-500/10 text-indigo-400 font-bold px-1.5 py-0.5 rounded">Remise: 0%</span>
                    </div>
                </div>
                <div id="selected-client-credit-badge" class="hidden text-right">
                    <p class="text-[10px] text-slate-500">Solde Crédit</p>
                    <p id="selected-client-credit" class="text-sm font-bold text-amber-500">0.00 DA</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart items list (Scrollable) -->
    <div class="flex-1 overflow-y-auto p-4 space-y-3" id="cart-items-container">
        <!-- Rendered dynamically by JS -->
        <div id="cart-empty" class="flex flex-col items-center justify-center h-full text-slate-500 py-12">
            <svg class="h-12 w-12 mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <p class="text-sm font-medium">Le panier est vide</p>
            <p class="text-xs opacity-60">Touchez des articles à gauche</p>
        </div>
    </div>

    <!-- Cart totals & submit panel (Shrink-0) -->
    <div class="bg-slate-900 border-t border-slate-700/50 p-4 shrink-0">
        <!-- Header / Summary of totals (Always visible) -->
        <div class="flex items-center justify-between cursor-pointer py-1 border-b border-slate-800 pb-3" onclick="toggleBillingPanel()">
            <div class="flex items-center space-x-2">
                <svg class="h-4 w-4 text-slate-400 transform transition-transform -rotate-90" id="billing-toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wide">Détails Règlement</span>
            </div>
            <div class="text-right">
                <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider block">Net à Payer</span>
                <span id="total-net" class="text-lg font-black text-white font-display">0.00 DA</span>
            </div>
        </div>

        <!-- Collapsible details body -->
        <div id="billing-details-panel" class="hidden space-y-4 mt-3 transition-all duration-200 max-h-64 overflow-y-auto pr-1">
            <!-- Express Mode Toggle Switch -->
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-red-500/10 border border-red-500/20 hover:bg-red-500/20 transition-all select-none">
                <div class="flex items-center space-x-2">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                    <span class="text-xs font-black text-red-500 dark:text-red-400 uppercase tracking-wider">Commande Express</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="express-toggle-input" onchange="toggleExpressMode()" class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600"></div>
                </label>
            </div>

            <!-- Billing recap -->
            <div class="space-y-2 text-sm">
                <div class="flex justify-between text-slate-400">
                    <span>Sous-total brut</span>
                    <span id="total-brut">0.00 DA</span>
                </div>
                <div class="flex justify-between text-indigo-400">
                    <span>Remise appliquée (<span id="discount-display-percent">0</span>%)</span>
                    <span id="total-discount">- 0.00 DA</span>
                </div>
                
                <div class="h-px bg-slate-800 my-2"></div>
                
                <!-- Deposit amount paid -->
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Acompte payé (DA)</span>
                    <input type="number" id="paid-amount-input" oninput="updateCartCalculations()" value="0" min="0" step="10"
                           class="w-32 bg-slate-800 border border-slate-700 rounded-md px-2 py-1 text-sm text-right font-bold text-white focus:outline-none focus:border-indigo-500">
                </div>

                <!-- Custom Discount Input & Type -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center space-x-1.5 shrink-0">
                        <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Remise</span>
                        <select id="discount-type-select" onchange="changeDiscountType()" 
                                class="bg-slate-850 border border-slate-700 rounded text-[10px] font-bold text-indigo-300 py-0.5 px-1 focus:outline-none">
                            <option value="percent">%</option>
                            <option value="fixed">DA</option>
                        </select>
                    </div>
                    <input type="number" id="discount-percent-input" oninput="updateCustomDiscount()" value="0" min="0" step="1"
                           class="w-32 bg-slate-800 border border-indigo-500/30 text-indigo-400 rounded-md px-2 py-1 text-sm text-right font-bold focus:outline-none focus:border-indigo-500">
                </div>

                <!-- Remaining balance -->
                <div class="flex justify-between text-amber-500 font-semibold text-sm">
                    <span>Reste à payer (Solde)</span>
                    <span id="remaining-balance">0.00 DA</span>
                </div>
            </div>

            <!-- Inputs for delivery date and remarks -->
            <div class="grid grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Livraison Prévue</label>
                    <input type="date" id="delivery-date-input" 
                           class="w-full bg-slate-850 border border-slate-700 rounded-md px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Caisse Ticket N°</label>
                    <input type="text" id="ticket-number-input" value="{{ $nextTicketNumber }}"
                           class="w-full bg-slate-850 border border-slate-700 rounded-md px-2.5 py-1.5 text-xs text-slate-200 font-mono text-center focus:outline-none focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Remarques / Notes du ticket</label>
                <input type="text" id="remarks-input" placeholder="Ex: suspendu, urgent, ..."
                       class="w-full bg-slate-850 border border-slate-700 rounded-md px-2.5 py-1.5 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500">
            </div>
        </div>

        <!-- Submit actions -->
        <div class="mt-4">
            <button onclick="submitOrder()" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-display font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/20 active:translate-y-0.5 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>Valider & Imprimer le Ticket</span>
            </button>
        </div>
    </div>
</div>

<!-- ================= MODALS OVERLAYS ================= -->

<!-- 1. Item Options Modal (Colors, Defects, Stains) -->
<div id="options-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-[500px] max-h-[90vh] flex flex-col shadow-2xl overflow-hidden transform scale-95 transition-all">
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-white font-display">Options de l'article</h3>
                <p id="options-modal-item-name" class="text-xs text-slate-400">Pantalon classique</p>
            </div>
            <button onclick="closeOptionsModal()" class="text-slate-400 hover:text-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content (Scrollable list of choices) -->
        <div class="p-6 overflow-y-auto space-y-5">
            <!-- Colors Selection -->
            @php
                $colorMap = [
                    'argent' => ['bg' => '#c0c0c0', 'text' => '#000000', 'border' => '#a9a9a9'],
                    'azur' => ['bg' => '#007fff', 'text' => '#ffffff', 'border' => '#005fcf'],
                    'beige' => ['bg' => '#f5f5dc', 'text' => '#000000', 'border' => '#d2b48c'],
                    'blanc' => ['bg' => '#ffffff', 'text' => '#000000', 'border' => '#cbd5e1'],
                    'blanc cassé' => ['bg' => '#fcf6eb', 'text' => '#000000', 'border' => '#cbd5e1'],
                    'bleu' => ['bg' => '#2563eb', 'text' => '#ffffff', 'border' => '#1d4ed8'],
                    'bleu ciel' => ['bg' => '#bae6fd', 'text' => '#000000', 'border' => '#7dd3fc'],
                    'bleu marine' => ['bg' => '#0f172a', 'text' => '#ffffff', 'border' => '#334155'],
                    'bleu turquoise' => ['bg' => '#2dd4bf', 'text' => '#000000', 'border' => '#14b8a6'],
                    'blond' => ['bg' => '#fef08a', 'text' => '#000000', 'border' => '#fde047'],
                    'blond vénitien' => ['bg' => '#fda4af', 'text' => '#000000', 'border' => '#f43f5e'],
                    'bordeaux' => ['bg' => '#991b1b', 'text' => '#ffffff', 'border' => '#7f1d1d'],
                    'brun' => ['bg' => '#78350f', 'text' => '#ffffff', 'border' => '#451a03'],
                    'châtain' => ['bg' => '#a16207', 'text' => '#ffffff', 'border' => '#78350f'],
                    'écru' => ['bg' => '#f5f5f5', 'text' => '#000000', 'border' => '#e5e5e5'],
                    'fauve' => ['bg' => '#c2410c', 'text' => '#ffffff', 'border' => '#9a3412'],
                    'grenat' => ['bg' => '#881337', 'text' => '#ffffff', 'border' => '#4c0519'],
                    'gris' => ['bg' => '#4b5563', 'text' => '#ffffff', 'border' => '#374151'],
                    'indigo' => ['bg' => '#4338ca', 'text' => '#ffffff', 'border' => '#3730a3'],
                    'ivoire' => ['bg' => '#fffff0', 'text' => '#000000', 'border' => '#fde047'],
                    'jaune' => ['bg' => '#eab308', 'text' => '#000000', 'border' => '#ca8a04'],
                    'kaki' => ['bg' => '#854d0e', 'text' => '#ffffff', 'border' => '#a16207'],
                    'marron' => ['bg' => '#451a03', 'text' => '#ffffff', 'border' => '#291002'],
                    'mauve' => ['bg' => '#c084fc', 'text' => '#000000', 'border' => '#a855f7'],
                    'or' => ['bg' => '#ffd700', 'text' => '#000000', 'border' => '#b5a642'],
                    'orange' => ['bg' => '#ea580c', 'text' => '#ffffff', 'border' => '#c2410c'],
                    'rose' => ['bg' => '#f472b6', 'text' => '#ffffff', 'border' => '#ec4899'],
                    'rouge' => ['bg' => '#dc2626', 'text' => '#ffffff', 'border' => '#b91c1c'],
                    'saumon' => ['bg' => '#fca5a5', 'text' => '#000000', 'border' => '#f87171'],
                    'sépia' => ['bg' => '#78350f', 'text' => '#ffffff', 'border' => '#451a03'],
                    'vert' => ['bg' => '#16a34a', 'text' => '#ffffff', 'border' => '#15803d'],
                    'vert émeraude' => ['bg' => '#059669', 'text' => '#ffffff', 'border' => '#047857'],
                    'violet' => ['bg' => '#7c3aed', 'text' => '#ffffff', 'border' => '#6d28d9']
                ];
            @endphp
            <div>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Couleur(s)</span>
                <div class="flex flex-wrap gap-1.5" id="modal-colors-container">
                    @foreach($colors as $color)
                        @php
                            $normalizedColor = strtolower(trim($color));
                            $c = $colorMap[$normalizedColor] ?? ['bg' => '#0f172a', 'text' => '#94a3b8', 'border' => '#334155'];
                            $isMapped = isset($colorMap[$normalizedColor]);
                        @endphp
                        <button onclick="toggleItemOption('colors', '{{ $color }}', this)" 
                                data-color-btn="{{ $isMapped ? 'true' : 'false' }}"
                                data-bg="{{ $c['bg'] }}"
                                data-text="{{ $c['text'] }}"
                                data-border="{{ $c['border'] }}"
                                style="background-color: {{ $c['bg'] }}; color: {{ $c['text'] }}; border-color: {{ $c['border'] }}; font-weight: bold;"
                                class="option-badge px-3 py-1 rounded-full text-xs border transition-all cursor-pointer">
                            {{ $color }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Defects Selection -->
            <div>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Défaut(s) signalé(s)</span>
                <div class="flex flex-wrap gap-1.5" id="modal-defects-container">
                    @foreach($defects as $defect)
                        <button onclick="toggleItemOption('defects', '{{ $defect }}', this)" 
                                class="option-badge px-3 py-1 rounded-full text-xs border border-slate-700 bg-slate-900 text-slate-300 hover:border-slate-500 transition-colors">
                            {{ $defect }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Stains Selection -->
            <div>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Tache(s) à traiter</span>
                <div class="flex flex-wrap gap-1.5" id="modal-stains-container">
                    @foreach($stains as $stain)
                        <button onclick="toggleItemOption('stains', '{{ $stain }}', this)" 
                                class="option-badge px-3 py-1 rounded-full text-xs border border-slate-700 bg-slate-900 text-slate-300 hover:border-slate-500 transition-colors">
                            {{ $stain }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Custom notes -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Notes particulières</label>
                <input type="text" id="modal-notes-input" placeholder="Ex: à recoudre, repasser à part, ..." 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-slate-900 border-t border-slate-700 flex justify-end space-x-3">
            <button onclick="closeOptionsModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors">Annuler</button>
            <button onclick="saveItemOptions()" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors">Enregistrer</button>
        </div>
    </div>
</div>

<!-- 2. New Client Modal -->
<div id="new-client-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-96 flex flex-col shadow-2xl overflow-hidden transform scale-95 transition-all">
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-base font-bold text-white font-display">Nouveau Client</h3>
            <button onclick="closeNewClientModal()" class="text-slate-400 hover:text-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <form id="new-client-form" onsubmit="submitNewClient(event)" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nom Complet *</label>
                <input type="text" id="new-client-name" required
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Téléphone</label>
                <input type="text" id="new-client-phone"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Remise par défaut (%)</label>
                <input type="number" id="new-client-discount" min="0" max="100" value="0"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Adresse</label>
                <input type="text" id="new-client-address"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Remarques</label>
                <input type="text" id="new-client-remarks"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <!-- Error message container -->
            <div id="new-client-error" class="hidden text-xs text-rose-400 font-medium"></div>

            <!-- Form buttons -->
            <div class="pt-2 flex justify-end space-x-3">
                <button type="button" onclick="closeNewClientModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors">Annuler</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Global catalog state injected from PHP
    const allItems = @json($items);
    
    // Default client object
    const defaultClient = {
        id: {{ $guestClient->id }},
        code: '{{ $guestClient->code }}',
        name: '{{ $guestClient->name }}',
        discount_percent: {{ $guestClient->discount_percent }},
        credit: {{ $guestClient->credit }}
    };

    // App state
    let selectedServiceId = 1;
    let selectedTargetId = 1;
    let selectedClient = { ...defaultClient };
    let cart = [];
    
    // Auxiliary State for modal options
    let currentOptionsIndex = null;
    let currentOptions = {
        colors: [],
        defects: [],
        stains: [],
        notes: ''
    };

    // Document Init
    document.addEventListener("DOMContentLoaded", () => {
        // Set default delivery date to today + 2 days
        const defaultDate = new Date();
        defaultDate.setDate(defaultDate.getDate() + 2);
        const yyyy = defaultDate.getFullYear();
        const mm = String(defaultDate.getMonth() + 1).padStart(2, '0');
        const dd = String(defaultDate.getDate()).padStart(2, '0');
        document.getElementById('delivery-date-input').value = `${yyyy}-${mm}-${dd}`;

        // Initialize display
        selectService(1);
        selectTarget(1);
        renderSelectedClient();
        updateCartCalculations();
    });

    // ================= CATALOG MANAGEMENT =================

    function selectService(id) {
        selectedServiceId = id;
        
        // Update active tab styles
        document.querySelectorAll('.service-tab').forEach(btn => {
            btn.classList.remove('service-tab-active');
        });
        const activeBtn = document.getElementById(`service-tab-${id}`);
        if(activeBtn) activeBtn.classList.add('service-tab-active');

        // Services 2 & 4 don't have targets sub-bar, hide it if they are active
        const targetsBar = document.getElementById('targets-bar');
        if (id === 2 || id === 4) {
            targetsBar.classList.add('hidden');
            selectedTargetId = 5; // Target Linge de maison by default
        } else {
            targetsBar.classList.remove('hidden');
            if (selectedTargetId === 5) {
                selectedTargetId = 1; // Default to Homme
            }
        }

        // Highlight target pills
        updateTargetPillsStyles();

        // Render catalog grid
        renderCatalog();
    }

    function selectTarget(id) {
        selectedTargetId = id;
        updateTargetPillsStyles();
        renderCatalog();
    }

    function updateTargetPillsStyles() {
        document.querySelectorAll('.target-pill').forEach(btn => {
            btn.classList.remove('target-pill-active');
        });
        const activePill = document.getElementById(`target-pill-${selectedTargetId}`);
        if(activePill) activePill.classList.add('target-pill-active');
    }

    function renderCatalog() {
        const grid = document.getElementById('catalog-grid');
        const empty = document.getElementById('catalog-empty');
        grid.innerHTML = '';

        // Filter items
        // Direct items for service 2 and 4 belong to target 5 (Maison), or match the selected service price
        const filtered = allItems.filter(item => {
            // Must have a service price for the selected service
            const hasPrice = item.service_prices.some(sp => sp.service_id === selectedServiceId);
            if (!hasPrice) return false;

            if (selectedServiceId === 2 || selectedServiceId === 4) {
                return item.garment_target_id === 5;
            } else {
                return item.garment_target_id === selectedTargetId;
            }
        });

        if (filtered.length === 0) {
            grid.classList.add('hidden');
            empty.classList.remove('hidden');
            return;
        }

        grid.classList.remove('hidden');
        empty.classList.add('hidden');

        filtered.forEach(item => {
            const priceObj = item.service_prices.find(sp => sp.service_id === selectedServiceId);
            const price = priceObj ? parseFloat(priceObj.price) : 0;

            const card = document.createElement('button');
            card.className = "bg-slate-800 border border-slate-700/60 p-3.5 rounded-2xl text-left flex flex-col justify-between h-32 active:scale-95 hover:border-slate-500 hover:bg-slate-800/80 transition-all duration-150 shadow-md cursor-pointer";
            card.onclick = () => addToCart(item, price);

            // Item Name
            const title = document.createElement('h3');
            title.className = "text-xs font-bold text-slate-100 leading-snug line-clamp-2 uppercase font-display";
            title.textContent = item.name;

            // Price badge
            const priceBadge = document.createElement('div');
            priceBadge.className = "text-right mt-auto";
            
            const priceText = document.createElement('span');
            priceText.className = "text-sm font-black font-display text-indigo-400";
            priceText.textContent = `${price.toFixed(0)} DA`;

            priceBadge.appendChild(priceText);
            card.appendChild(title);
            card.appendChild(priceBadge);
            grid.appendChild(card);
        });
    }

    // ================= CART LOGIC =================

    function addToCart(item, price) {
        // Check if item is already in cart under the same service
        const existingIndex = cart.findIndex(cartItem => 
            cartItem.id === item.id && 
            cartItem.service_id === selectedServiceId
        );

        if (existingIndex !== -1) {
            cart[existingIndex].quantity += 1;
        } else {
            cart.push({
                id: item.id,
                name: item.name,
                service_id: selectedServiceId,
                service_name: selectedServiceId === 1 ? 'Pressing' : 
                              (selectedServiceId === 2 ? 'Blanchisserie' : 
                              (selectedServiceId === 3 ? 'Lavage' : 
                              (selectedServiceId === 4 ? 'Au Kilo' : 
                              (selectedServiceId === 5 ? 'Repassage' : 'Teinture')))),
                quantity: 1,
                unit_price: price,
                colors: [],
                defects: [],
                stains: [],
                notes: ''
            });
        }

        renderCart();
        updateCartCalculations();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const empty = document.getElementById('cart-empty');
        
        // Remove existing items (except empty notice)
        document.querySelectorAll('.cart-item-row').forEach(el => el.remove());

        if (cart.length === 0) {
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');

        cart.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = "cart-item-row bg-slate-900/60 border border-slate-700/40 rounded-xl p-3 flex flex-col space-y-2 hover:border-slate-600 transition-colors";

            // Top line: Name + Service + Delete button
            const topLine = document.createElement('div');
            topLine.className = "flex items-start justify-between";

            const info = document.createElement('div');
            const nameText = document.createElement('h4');
            nameText.className = "text-xs font-bold text-slate-100 uppercase";
            nameText.textContent = item.name;
            
            const serviceText = document.createElement('span');
            serviceText.className = "text-[9px] font-bold text-indigo-400 bg-indigo-500/10 px-1 py-0.5 rounded uppercase tracking-wider";
            serviceText.textContent = item.service_name;
            
            info.appendChild(nameText);
            info.appendChild(serviceText);

            const deleteBtn = document.createElement('button');
            deleteBtn.onclick = () => removeFromCart(index);
            deleteBtn.className = "text-slate-500 hover:text-rose-400 transition-colors p-0.5";
            deleteBtn.innerHTML = `
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            `;

            topLine.appendChild(info);
            topLine.appendChild(deleteBtn);
            row.appendChild(topLine);

            // Bottom line: Qty controller, Price & Options gear
            const bottomLine = document.createElement('div');
            bottomLine.className = "flex items-center justify-between pt-1 border-t border-slate-800/60";

            // Qty controller
            const qtyCtrl = document.createElement('div');
            qtyCtrl.className = "flex items-center bg-slate-800 rounded-md border border-slate-700/50";
            
            const minusBtn = document.createElement('button');
            minusBtn.onclick = () => updateItemQty(index, item.quantity - (selectedServiceId === 4 ? 0.5 : 1));
            minusBtn.className = "px-2 py-1 text-slate-400 hover:text-white font-bold text-xs";
            minusBtn.textContent = '-';
            
            const qtyText = document.createElement('span');
            qtyText.className = "px-2 text-xs font-mono font-bold text-slate-200";
            qtyText.textContent = item.quantity;
            
            const plusBtn = document.createElement('button');
            plusBtn.onclick = () => updateItemQty(index, item.quantity + (selectedServiceId === 4 ? 0.5 : 1));
            plusBtn.className = "px-2 py-1 text-slate-400 hover:text-white font-bold text-xs";
            plusBtn.textContent = '+';

            qtyCtrl.appendChild(minusBtn);
            qtyCtrl.appendChild(qtyText);
            qtyCtrl.appendChild(plusBtn);

            // Options triggers
            const actionContainer = document.createElement('div');
            actionContainer.className = "flex items-center space-x-3";

            // Option details tags indicator (if any color/defect is chosen)
            const badgesCount = item.colors.length + item.defects.length + item.stains.length;
            if (badgesCount > 0) {
                const badge = document.createElement('span');
                badge.className = "text-[9px] bg-amber-500/20 text-amber-500 font-bold px-1.5 py-0.5 rounded-full";
                badge.textContent = `${badgesCount} options`;
                actionContainer.appendChild(badge);
            }

            const optionsBtn = document.createElement('button');
            optionsBtn.onclick = () => openOptionsModal(index);
            optionsBtn.className = "text-slate-400 hover:text-indigo-400 transition-colors p-1 bg-slate-800/80 hover:bg-slate-800 rounded-md border border-slate-700/50";
            optionsBtn.innerHTML = `
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
            `;
            actionContainer.appendChild(optionsBtn);

            // Item pricing (double if express is active)
            const isExpress = document.getElementById('express-toggle-input')?.checked || false;
            const displayUnitPrice = isExpress ? (item.unit_price * 2) : item.unit_price;

            const priceText = document.createElement('span');
            priceText.className = "text-xs font-bold text-slate-300 font-mono";
            priceText.textContent = `${(displayUnitPrice * item.quantity).toFixed(0)} DA`;

            actionContainer.appendChild(priceText);

            bottomLine.appendChild(qtyCtrl);
            bottomLine.appendChild(actionContainer);
            row.appendChild(bottomLine);

            container.appendChild(row);
        });
    }

    function updateItemQty(index, qty) {
        if (qty <= 0) {
            removeFromCart(index);
        } else {
            cart[index].quantity = parseFloat(qty);
            renderCart();
            updateCartCalculations();
        }
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
        updateCartCalculations();
    }

    function toggleExpressMode() {
        renderCart();
        updateCartCalculations();
    }

    function updateCartCalculations() {
        let totalBrut = 0;
        cart.forEach(item => {
            totalBrut += item.unit_price * item.quantity;
        });

        const isExpress = document.getElementById('express-toggle-input')?.checked || false;
        if (isExpress) {
            totalBrut = totalBrut * 2;
        }

        const discountType = document.getElementById('discount-type-select').value;
        const discountInput = document.getElementById('discount-percent-input');
        let discountValue = parseFloat(discountInput.value) || 0;
        if (discountValue < 0) discountValue = 0;

        let discountAmount = 0;
        let discountPercentDisplay = 0;

        if (discountType === 'percent') {
            if (discountValue > 100) discountValue = 100;
            discountInput.value = discountValue;
            discountAmount = totalBrut * (discountValue / 100);
            discountPercentDisplay = discountValue.toFixed(0);
        } else {
            // Fixed amount discount
            if (discountValue > totalBrut) discountValue = totalBrut;
            discountInput.value = discountValue;
            discountAmount = discountValue;
            discountPercentDisplay = totalBrut > 0 ? ((discountAmount / totalBrut) * 100).toFixed(0) : 0;
        }

        const totalNet = Math.max(0, totalBrut - discountAmount);

        // Input paid check
        const paidInput = document.getElementById('paid-amount-input');
        let paidAmount = parseFloat(paidInput.value);
        if (isNaN(paidAmount) || paidAmount < 0) {
            paidAmount = 0;
        }

        const remainingBalance = Math.max(0, totalNet - paidAmount);

        // Update DOM
        document.getElementById('total-brut').textContent = `${totalBrut.toFixed(0)} DA`;
        document.getElementById('discount-display-percent').textContent = discountPercentDisplay;
        document.getElementById('total-discount').textContent = `- ${discountAmount.toFixed(0)} DA`;
        document.getElementById('total-net').textContent = `${totalNet.toFixed(0)} DA`;
        document.getElementById('remaining-balance').textContent = `${remainingBalance.toFixed(0)} DA`;
    }

    // ================= CLIENT SELECTION & SEARCH =================

    function searchClients(term) {
        const resultsBox = document.getElementById('client-search-results');
        const clearBtn = document.getElementById('client-clear-btn');
        
        if (term.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        if (term.trim().length < 2) {
            resultsBox.innerHTML = '';
            resultsBox.classList.add('hidden');
            return;
        }

        fetch(`/api/clients/search?q=${encodeURIComponent(term)}`)
            .then(res => res.json())
            .then(clients => {
                resultsBox.innerHTML = '';
                if (clients.length === 0) {
                    resultsBox.classList.add('hidden');
                    return;
                }

                clients.forEach(client => {
                    const row = document.createElement('button');
                    row.className = "w-full text-left px-4 py-2 hover:bg-slate-700 flex flex-col text-sm border-b border-slate-700/50 cursor-pointer";
                    row.onclick = (e) => {
                        e.preventDefault();
                        selectClient(client);
                    };

                    const nameText = document.createElement('span');
                    nameText.className = "font-bold text-slate-200";
                    nameText.textContent = client.name;

                    const meta = document.createElement('span');
                    meta.className = "text-xs text-slate-400";
                    meta.textContent = `Code: ${client.code} | Tél: ${client.phone || '-'} | Remise: ${client.discount_percent}%`;

                    row.appendChild(nameText);
                    row.appendChild(meta);
                    resultsBox.appendChild(row);
                });
                resultsBox.classList.remove('hidden');
            });
    }

    function selectClient(client) {
        selectedClient = { ...client };
        document.getElementById('discount-type-select').value = 'percent';
        document.getElementById('discount-percent-input').value = client.discount_percent;
        renderSelectedClient();
        updateCartCalculations();
        
        // Hide search drop
        const resultsBox = document.getElementById('client-search-results');
        resultsBox.innerHTML = '';
        resultsBox.classList.add('hidden');
        
        // Populate search input with name
        document.getElementById('client-search-input').value = client.name;
        document.getElementById('client-clear-btn').classList.remove('hidden');
    }

    function clearSelectedClient() {
        selectedClient = { ...defaultClient };
        document.getElementById('discount-type-select').value = 'percent';
        document.getElementById('discount-percent-input').value = defaultClient.discount_percent;
        renderSelectedClient();
        updateCartCalculations();
        document.getElementById('client-search-input').value = '';
        document.getElementById('client-clear-btn').classList.add('hidden');
        
        const resultsBox = document.getElementById('client-search-results');
        resultsBox.innerHTML = '';
        resultsBox.classList.add('hidden');
    }

    function changeDiscountType() {
        document.getElementById('discount-percent-input').value = 0;
        updateCartCalculations();
    }

    function updateCustomDiscount() {
        updateCartCalculations();
    }

    function renderSelectedClient() {
        document.getElementById('selected-client-name').textContent = selectedClient.name;
        document.getElementById('selected-client-code').textContent = selectedClient.code;
        document.getElementById('selected-client-discount').textContent = `Remise: ${selectedClient.discount_percent}%`;

        const creditBadge = document.getElementById('selected-client-credit-badge');
        const creditVal = parseFloat(selectedClient.credit);
        
        if (creditVal > 0) {
            document.getElementById('selected-client-credit').textContent = `${creditVal.toFixed(0)} DA`;
            creditBadge.classList.remove('hidden');
        } else {
            creditBadge.classList.add('hidden');
        }
    }

    // ================= CUSTOM OPTIONS MODAL =================

    function openOptionsModal(index) {
        currentOptionsIndex = index;
        const item = cart[index];
        
        currentOptions = {
            colors: [...item.colors],
            defects: [...item.defects],
            stains: [...item.stains],
            notes: item.notes
        };

        // Update UI
        document.getElementById('options-modal-item-name').textContent = `${item.service_name} > ${item.name}`;
        document.getElementById('modal-notes-input').value = currentOptions.notes;

        // Reset badge active classes
        document.querySelectorAll('#options-modal .option-badge').forEach(badge => {
            const isColor = badge.getAttribute('data-color-btn') === 'true';
            const badgeText = badge.textContent.trim();
            const isActive = currentOptions.colors.includes(badgeText) || 
                             currentOptions.defects.includes(badgeText) || 
                             currentOptions.stains.includes(badgeText);

            if (isColor) {
                if (isActive) {
                    badge.classList.add('ring-4', 'ring-white', 'scale-105', 'shadow-lg', 'shadow-black/60');
                    badge.style.borderColor = '#ffffff';
                } else {
                    badge.classList.remove('ring-4', 'ring-white', 'scale-105', 'shadow-lg', 'shadow-black/60');
                    badge.style.borderColor = badge.getAttribute('data-border');
                }
            } else {
                badge.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-500');
                badge.classList.add('bg-slate-900', 'text-slate-300', 'border-slate-700');
                if (isActive) {
                    badge.classList.remove('bg-slate-900', 'text-slate-300', 'border-slate-700');
                    badge.classList.add('bg-indigo-600', 'text-white', 'border-indigo-500');
                }
            }
        });

        document.getElementById('options-modal').classList.remove('hidden');
    }

    function closeOptionsModal() {
        document.getElementById('options-modal').classList.add('hidden');
        currentOptionsIndex = null;
    }

    function toggleItemOption(type, value, btn) {
        const index = currentOptions[type].indexOf(value);
        const isColor = btn.getAttribute('data-color-btn') === 'true';

        if (index === -1) {
            currentOptions[type].push(value);
            if (isColor) {
                btn.classList.add('ring-4', 'ring-white', 'scale-105', 'shadow-lg', 'shadow-black/60');
                btn.style.borderColor = '#ffffff';
            } else {
                btn.classList.remove('bg-slate-900', 'text-slate-300', 'border-slate-700');
                btn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-500');
            }
        } else {
            currentOptions[type].splice(index, 1);
            if (isColor) {
                btn.classList.remove('ring-4', 'ring-white', 'scale-105', 'shadow-lg', 'shadow-black/60');
                btn.style.borderColor = btn.getAttribute('data-border');
            } else {
                btn.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-500');
                btn.classList.add('bg-slate-900', 'text-slate-300', 'border-slate-700');
            }
        }
    }

    function saveItemOptions() {
        if (currentOptionsIndex !== null) {
            cart[currentOptionsIndex].colors = [...currentOptions.colors];
            cart[currentOptionsIndex].defects = [...currentOptions.defects];
            cart[currentOptionsIndex].stains = [...currentOptions.stains];
            cart[currentOptionsIndex].notes = document.getElementById('modal-notes-input').value.trim();
            
            renderCart();
            closeOptionsModal();
        }
    }

    // ================= NEW CLIENT MODAL =================

    function openNewClientModal() {
        document.getElementById('new-client-form').reset();
        document.getElementById('new-client-error').classList.add('hidden');
        document.getElementById('new-client-modal').classList.remove('hidden');
    }

    function closeNewClientModal() {
        document.getElementById('new-client-modal').classList.add('hidden');
    }

    function submitNewClient(e) {
        e.preventDefault();
        
        const name = document.getElementById('new-client-name').value.trim();
        const phone = document.getElementById('new-client-phone').value.trim();
        const discount = document.getElementById('new-client-discount').value;
        const address = document.getElementById('new-client-address').value.trim();
        const remarks = document.getElementById('new-client-remarks').value.trim();

        fetch('/api/clients', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                name,
                phone,
                discount_percent: discount,
                address,
                remarks
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                selectClient(data.client);
                closeNewClientModal();
            } else {
                const errBox = document.getElementById('new-client-error');
                errBox.textContent = data.message || "Erreur lors de la création.";
                errBox.classList.remove('hidden');
            }
        })
        .catch(err => {
            const errBox = document.getElementById('new-client-error');
            errBox.textContent = "Erreur de connexion avec le serveur.";
            errBox.classList.remove('hidden');
        });
    }

    // ================= SUBMIT CART ORDER =================

    function submitOrder() {
        if (cart.length === 0) {
            showAppAlert("Le panier est vide. Veuillez ajouter des articles.", "error", "Panier Vide");
            return;
        }

        const clientId = selectedClient.id;
        if (!clientId) {
            showAppAlert("Veuillez associer un client (ex: Client Passage ou client recherché).", "error", "Client manquant");
            return;
        }

        const paidAmount = parseFloat(document.getElementById('paid-amount-input').value) || 0;
        const targetDeliveryDate = document.getElementById('delivery-date-input').value;
        const ticketNumber = document.getElementById('ticket-number-input').value.trim();
        const remarks = document.getElementById('remarks-input').value.trim();

        // Build items payload
        const payloadItems = cart.map(item => ({
            service_id: item.service_id,
            garment_item_id: item.id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            colors: item.colors,
            defects: item.defects,
            stains: item.stains,
            notes: item.notes
        }));

        const discountType = document.getElementById('discount-type-select').value;
        const discountValue = parseFloat(document.getElementById('discount-percent-input').value) || 0;

        const isExpress = document.getElementById('express-toggle-input').checked;

        const body = {
            client_id: clientId,
            ticket_number: ticketNumber,
            discount_type: discountType,
            discount_percent: discountType === 'percent' ? discountValue : 0,
            discount_amount: discountType === 'fixed' ? discountValue : 0,
            paid_amount: paidAmount,
            target_delivery_date: targetDeliveryDate,
            remarks: remarks,
            is_express: isExpress,
            items: payloadItems
        };

        // Submit via AJAX
        fetch('/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Lancer l'impression complète de manière transparente via l'iframe cachée
                printOrder(data.order_id, 'all');

                showAppAlert(`Ticket N° ${data.ticket_number} enregistré avec succès !`, "success", "Commande Enregistrée");
                
                // Clear cart
                cart = [];
                // Reset express checkbox
                document.getElementById('express-toggle-input').checked = false;
                renderCart();
                clearSelectedClient();
                
                // Refresh next ticket number
                const nextNo = String(parseInt(data.ticket_number) + 1).padStart(6, '0');
                document.getElementById('ticket-number-input').value = nextNo;
                document.getElementById('remarks-input').value = '';
                document.getElementById('paid-amount-input').value = 0;
                
                updateCartCalculations();
            } else {
                showAppAlert(`Erreur : ${data.message}`, "error", "Erreur");
            }
        })
        .catch(err => {
            showAppAlert("Erreur lors de l'enregistrement de la commande.", "error", "Erreur");
        });
    }

    // Collapsible sidebar panels toggle functions
    function toggleClientPanel() {
        const panel = document.getElementById('client-fields-panel');
        const icon = document.getElementById('client-toggle-icon');
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            icon.classList.remove('-rotate-90');
        } else {
            panel.classList.add('hidden');
            icon.classList.add('-rotate-90');
        }
    }

    function toggleBillingPanel() {
        const panel = document.getElementById('billing-details-panel');
        const icon = document.getElementById('billing-toggle-icon');
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            icon.classList.remove('-rotate-90');
        } else {
            panel.classList.add('hidden');
            icon.classList.add('-rotate-90');
        }
    }
</script>
@endsection
