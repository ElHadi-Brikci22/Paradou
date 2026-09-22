@extends('layouts.app')

@section('title', 'Caisse Tactile')

@section('styles')
<style>
    /* Styling for active tabs & pills */
    .service-tab-active {
        background-color: rgb(79, 70, 229) !important; /* Indigo 600 */
        color: white !important;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.25) !important;
    }
    .target-pill-active {
        background-color: rgb(51, 65, 85) !important; /* Slate 700 */
        color: white !important;
        font-weight: 700 !important;
    }
    .subcat-pill-active {
        background-color: rgb(79, 70, 229) !important; /* Indigo 600 */
        color: white !important;
        font-weight: 700 !important;
        box-shadow: 0 2px 4px -1px rgba(99, 102, 241, 0.2) !important;
    }
    /* Grid adjustments for catalog */
    .catalog-grid {
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    }

    /* Collapsed Cart Rail animations and theme styles */
    #checkout-cart-panel {
        will-change: width;
    }
    #cart-collapsed-rail {
        user-select: none;
    }
    .theme-light #checkout-cart-panel {
        background-color: #ffffff !important;
        border-color: #e2e8f0 !important;
    }
    .theme-light #cart-collapsed-rail {
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
    }
    .theme-light #cart-collapsed-rail:hover {
        background-color: #f1f5f9 !important;
    }
    .theme-light #cart-collapsed-rail .bg-slate-900\/90 {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
    }
    .theme-light #cart-collapsed-rail span.text-slate-400 {
        color: #64748b !important;
    }

    @keyframes cartBadgePulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.4); filter: drop-shadow(0 0 10px rgba(99, 102, 241, 0.9)); }
        100% { transform: scale(1); }
    }
    .cart-badge-pulse {
        animation: cartBadgePulse 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    /* Hide scrollbars completely while preserving scrollability */
    .no-scrollbar::-webkit-scrollbar {
        display: none !important;
    }
    .no-scrollbar {
        -ms-overflow-style: none !important;  /* IE and Edge */
        scrollbar-width: none !important;  /* Firefox */
    }

    /* Tactile Keypad (Pavé Numérique) */
    .cpay-key {
        height: 48px;
        background-color: #1e293b;
        border: 1px solid #334155;
        border-radius: 0.75rem;
        font-size: 1.3rem;
        font-weight: 800;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        color: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
        transition: all 0.1s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .cpay-key:hover {
        background-color: #334155;
        border-color: #475569;
        color: #ffffff;
    }
    .cpay-key:active {
        transform: scale(0.94);
        background-color: #4f46e5;
        color: #ffffff;
    }
    .cpay-key-action {
        height: 48px;
        border-radius: 0.75rem;
        font-size: 1.15rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
        transition: all 0.1s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .cpay-key-action:active {
        transform: scale(0.94);
    }
</style>
@endsection

@section('content')
<!-- Left Panel: Catalog (2/3 width on large screens) -->
<div class="flex-1 flex flex-col min-w-0 border-r border-slate-700/50 bg-slate-900">
    <!-- Services & Context Bar (Row 1: Services on Left, Client & Pricing on Right) -->
    <div class="bg-slate-800/30 px-4 py-2 border-b border-slate-700/40 shrink-0 flex items-center justify-between gap-3 overflow-x-auto no-scrollbar">
        <!-- Services Tabs (Left side) -->
        <div class="flex items-center gap-2 shrink-0">
            @foreach($services as $service)
                <button onclick="selectService({{ $service->id }})" 
                        id="service-tab-{{ $service->id }}" 
                        class="service-tab shrink-0 px-4 py-1.5 rounded-lg font-display font-bold text-sm tracking-wide transition-all duration-150 bg-slate-800/80 text-slate-300 hover:bg-slate-700 hover:text-white cursor-pointer">
                    {{ $service->name }}
                </button>
            @endforeach
        </div>

        <!-- Client & Pricing Context Bar (Right side) -->
        <div class="flex items-center space-x-2.5 shrink-0 ml-auto pl-4">
            <!-- Client Selector Button -->
            <div class="flex items-center space-x-1.5">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Client :</span>
                <button onclick="openClientSelectionModal()" 
                        class="bg-slate-800 hover:bg-slate-700/90 border border-slate-700/60 hover:border-slate-600 px-3 py-1 rounded-lg text-xs font-bold text-slate-200 flex items-center space-x-2 cursor-pointer transition-colors shadow-xs">
                    <svg class="h-3.5 w-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span id="selected-client-name-display" class="font-bold">Client Passage</span>
                    <span id="selected-client-discount-badge" class="text-[10px] bg-indigo-500/10 text-indigo-400 font-bold px-1.5 py-0.5 rounded">Remise: 0%</span>
                </button>
                <button onclick="clearSelectedClient()" id="client-clear-btn-display" class="hidden text-slate-500 hover:text-rose-400 transition-colors p-1" title="Réinitialiser au client de passage">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Pricing Mode Switcher -->
            <div class="flex items-center space-x-1.5">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tarif :</span>
                <div class="flex bg-slate-950/70 p-0.5 rounded-lg border border-slate-800">
                    <button onclick="setPricingMode('detail')" id="pricing-mode-detail" 
                            class="px-2.5 py-1 rounded-md text-xs font-black uppercase transition-all cursor-pointer bg-indigo-600 text-white shadow-xs">
                        Détail
                    </button>
                    <button onclick="setPricingMode('wholesale')" id="pricing-mode-wholesale" 
                            class="px-2.5 py-1 rounded-md text-xs font-black uppercase text-slate-400 hover:text-white transition-all cursor-pointer">
                        Gros
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Bar (Row 2: Target Publics on Left, Cart Toggle on Right - In Yellow Box Area) -->
    <div id="targets-bar" class="bg-slate-900/50 px-4 py-1.5 border-b border-slate-800/80 shrink-0 flex items-center justify-between gap-3 overflow-x-auto no-scrollbar">
        <div class="flex items-center gap-1.5 shrink-0 overflow-x-auto no-scrollbar">
            @foreach($targets as $target)
                <button onclick="selectTarget({{ $target->id }})" 
                        id="target-pill-{{ $target->id }}" 
                        class="target-pill shrink-0 px-3 py-1 rounded-lg text-sm font-semibold transition-all duration-150 text-slate-400 hover:text-white hover:bg-slate-800/70 cursor-pointer">
                    {{ $target->name }}
                </button>
            @endforeach
        </div>

        <!-- Cart Toggle Button in Categories Bar (Right side - In Yellow Box Area) -->
        <button onclick="toggleCartPanel()" id="cart-toggle-header-btn" 
                class="bg-slate-800 hover:bg-slate-700/90 border border-slate-700/60 hover:border-slate-600 px-3 py-1 rounded-lg text-xs font-bold text-slate-200 flex items-center space-x-2 cursor-pointer transition-all shadow-xs active:scale-95 shrink-0 ml-auto" 
                title="Afficher / Réduire le panier (F4)">
            <span class="text-sm">🛒</span>
            <span id="cart-toggle-header-label">Panier</span>
            <span id="cart-toggle-header-badge" class="text-[10px] bg-indigo-500/20 text-indigo-300 font-bold px-1.5 py-0.5 rounded-full font-mono">0</span>
            <span id="cart-toggle-header-arrow" class="text-slate-400 text-xs">▶</span>
        </button>
    </div>

    <!-- Subcategories Bar (Row 3: Positioned cleanly UNDER Categories, full width row) -->
    <div id="subcategories-container" class="hidden bg-slate-900/70 px-4 py-1.5 border-b border-slate-800/80 shrink-0 flex items-center gap-2 overflow-x-auto no-scrollbar">
        <div id="subcategories-bar" class="flex items-center gap-1.5 shrink-0 overflow-x-auto no-scrollbar">
            <!-- Rendered dynamically by renderSubcategoryPills() -->
        </div>
    </div>

    <!-- Items Grid (Scrollable central container - maximized space for articles) -->
    <div class="flex-1 overflow-y-auto p-3 sm:p-4">
        <div id="catalog-grid" class="grid catalog-grid gap-3">
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

<!-- Right Panel: Checkout / Cart (Dynamic: Expanded w-96 or Collapsed w-14) -->
<div id="checkout-cart-panel" class="w-96 shrink-0 bg-slate-800/40 backdrop-blur-md flex flex-col overflow-hidden border-l border-slate-700/50 transition-all duration-300 ease-in-out relative">

    <!-- 1. Collapsed Side Rail (Visible when cart is reduced on the side) -->
    <div id="cart-collapsed-rail" class="hidden flex-col items-center justify-between h-full py-4 px-1.5 select-none hover:bg-slate-800/60 transition-colors cursor-pointer w-full group" onclick="toggleCartPanel(event)" title="Cliquer pour afficher le panier (F4)">
        <!-- Rail Top: Expand button -->
        <div class="flex flex-col items-center space-y-1.5 w-full">
            <button type="button" onclick="toggleCartPanel(event)" 
                    class="w-10 h-10 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center shadow-lg shadow-indigo-600/30 transition-all group-hover:scale-105 active:scale-95 cursor-pointer"
                    title="Agrandir le panier">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <span class="text-[9px] font-bold text-slate-400 group-hover:text-indigo-400 transition-colors uppercase tracking-wider">Ouvrir</span>
        </div>

        <!-- Rail Center: Icon & Badge -->
        <div class="flex flex-col items-center justify-center relative py-4">
            <div class="relative p-2 rounded-2xl bg-slate-800/80 border border-slate-700/60 group-hover:border-indigo-500/50 group-hover:bg-slate-800 transition-all shadow-md">
                <span class="text-2xl filter drop-shadow">🛒</span>
                <span id="cart-rail-badge" class="absolute -top-1.5 -right-1.5 bg-indigo-500 text-white text-[10px] font-black font-mono px-1.5 py-0.2 rounded-full shadow-md min-w-[18px] text-center border border-slate-900">
                    0
                </span>
            </div>
        </div>

        <!-- Rail Bottom: Net Total Badge & Direct Pay Button -->
        <div class="flex flex-col items-center space-y-2 w-full">
            <div class="bg-slate-900/90 border border-slate-700/80 group-hover:border-indigo-500/40 rounded-xl py-1.5 px-1 w-full text-center shadow-inner transition-colors">
                <span class="text-[8px] font-bold text-slate-400 block uppercase leading-none mb-0.5">Total</span>
                <span id="cart-rail-net-total" class="text-[10px] font-mono font-black text-indigo-400 block leading-tight truncate">0 DA</span>
            </div>
            <button type="button" onclick="openPaymentFromRail(event)" 
                    id="cart-rail-pay-btn"
                    class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center shadow-md shadow-emerald-600/25 transition-all hover:scale-105 active:scale-95 cursor-pointer"
                    title="Accéder directement au règlement">
                <span class="text-xs font-bold font-mono">➜</span>
            </button>
        </div>
    </div>

    <!-- 2. Expanded Cart Content (Normal Full Cart) -->
    <div id="cart-expanded-content" class="flex flex-col h-full w-full overflow-hidden">

        <!-- Sticky Kilo Weight Banner (Visible when kilo items are in cart) -->
        <div id="cart-kilo-summary-bar" class="hidden shrink-0 bg-slate-800/90 border-b border-amber-500/20 px-4 py-2.5 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2">
                <span class="text-base">⚖️</span>
                <div>
                    <span class="text-[9px] uppercase font-black text-amber-400 tracking-wider block">Poids Total Commande</span>
                    <span id="cart-kilo-total-weight-text" class="text-xs font-mono font-bold text-white">0.00 kg (0 g)</span>
                </div>
            </div>
            <span id="cart-kilo-items-count" class="text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded-full">0 pcs</span>
        </div>

        <!-- Cart Header with Article Count & Reduce Button -->
        <div class="px-4 py-2.5 bg-slate-800/60 border-b border-slate-700/50 flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-2">
                <h3 id="cart-header-title" class="text-xs font-bold text-slate-300 uppercase tracking-wider font-display">Panier</h3>
                <span id="cart-total-articles-count" class="text-[11px] font-mono font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2 py-0.5 rounded-full">0 article</span>
            </div>
            <button type="button" onclick="toggleCartPanel()" 
                    class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-lg border border-slate-700 hover:border-slate-600 text-xs font-semibold flex items-center space-x-1.5 transition-colors cursor-pointer shadow-sm active:scale-95"
                    title="Réduire le panier sur le côté (F4)">
                <span class="text-[11px]">Réduire</span>
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>
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
    <div class="bg-slate-900 border-t border-slate-700/50 p-4 shrink-0 transition-all duration-300">
        
        <!-- 1. Collapsed View (Review Cart Mode) -->
        <div id="billing-collapsed-view" class="space-y-2 py-1">
            <div id="cart-kilo-weight-badge-collapsed" class="hidden flex items-center justify-between bg-amber-500/10 border border-amber-500/20 px-3 py-1.5 rounded-lg text-amber-400">
                <span class="text-[10px] font-bold uppercase tracking-wider flex items-center space-x-1">
                    <span>⚖️ Poids Total Au Kilo</span>
                </span>
                <span id="total-weight-collapsed" class="text-xs font-mono font-black">0.00 kg</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <div class="flex-1">
                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider block">Net à Payer</span>
                    <span id="total-net-collapsed" class="text-lg font-black text-indigo-400 font-mono">0 DA</span>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="openPaymentView()" 
                            class="py-2.5 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-bold border border-slate-700 transition-all cursor-pointer shadow-sm" title="Voir les options (remise, date, express...)">
                        ⚙️ Détails
                    </button>
                    <button type="button" onclick="openCheckoutPaymentModal()" 
                            class="bg-indigo-600 hover:bg-indigo-500 text-white font-display font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-indigo-600/25 active:translate-y-0.5 transition-all flex items-center justify-center space-x-1.5 cursor-pointer text-xs">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Valider & Imprimer ➜</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Expanded View (Payment Mode) - Hidden by default -->
        <div id="billing-expanded-view" class="hidden space-y-4">
            <!-- Title Header -->
            <div class="border-b border-slate-800 pb-2 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Détails du Règlement</h3>
                <button type="button" onclick="closePaymentView()" class="text-[10px] font-bold text-slate-500 hover:text-slate-300 uppercase tracking-wider cursor-pointer">
                    ⬅ Retour
                </button>
            </div>

            <!-- Billing Recap -->
            <div class="space-y-2.5 text-xs">
                <!-- Sous-total brut -->
                <div class="flex justify-between text-slate-400">
                    <span>Sous-total brut</span>
                    <span id="total-brut" class="font-semibold font-mono">0.00 DA</span>
                </div>

                <!-- Total weight for kilo service -->
                <div id="cart-kilo-weight-badge-expanded" class="hidden flex items-center justify-between bg-amber-500/10 border border-amber-500/20 px-2.5 py-1.5 rounded-lg text-amber-400">
                    <span class="text-[10px] font-bold uppercase tracking-wider flex items-center space-x-1">
                        <span>⚖️ Poids Total (Au Kilo)</span>
                    </span>
                    <span id="total-weight-expanded" class="text-xs font-mono font-black">0.00 kg (0 g)</span>
                </div>

                <!-- Discount Input & Type -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center space-x-1.5 shrink-0">
                        <span class="font-semibold text-indigo-400 uppercase tracking-wider text-[10px]">Remise</span>
                        <select id="discount-type-select" onchange="changeDiscountType()" 
                                class="bg-slate-850 border border-slate-700 rounded text-[9px] font-bold text-indigo-300 py-0.5 px-1 focus:outline-none">
                            @if(auth()->user()->role === 'admin')
                                <option value="percent">%</option>
                                <option value="fixed">DA</option>
                            @else
                                <option value="fixed" selected>DA</option>
                            @endif
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-[10px] text-slate-500">(<span id="discount-display-percent">0</span>%)</span>
                        <input type="number" id="discount-percent-input" oninput="updateCustomDiscount()" value="0" min="0" step="1"
                               class="w-24 bg-slate-800 border border-slate-700 text-indigo-400 rounded-md px-2 py-1 text-xs text-right font-bold focus:outline-none focus:border-indigo-500 font-mono">
                        <span id="total-discount" class="text-indigo-400 font-bold font-mono w-16 text-right">- 0 DA</span>
                    </div>
                </div>

                <!-- Deposit amount paid -->
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Acompte payé</span>
                    <div class="flex items-center space-x-1.5">
                        <input type="number" id="paid-amount-input" oninput="updateCartCalculations()" onclick="openCheckoutPaymentModal()" value="0" min="0" step="10"
                               class="w-24 bg-slate-800 border border-slate-700 rounded-md px-2 py-1 text-xs text-right font-bold text-white focus:outline-none focus:border-indigo-500 font-mono cursor-pointer" title="Cliquer pour ouvrir le pavé tactile">
                        <button type="button" onclick="openCheckoutPaymentModal()" 
                                class="px-2 py-1 bg-indigo-600/30 hover:bg-indigo-600 border border-indigo-500/40 text-indigo-300 hover:text-white rounded text-[11px] font-bold transition-all cursor-pointer" title="Ouvrir le pavé tactile">
                            🔢 Pavé
                        </button>
                    </div>
                </div>

                <!-- Net à Payer (Grand Total Box) -->
                <div class="flex items-center justify-between bg-indigo-500/10 p-2 rounded-lg border border-indigo-500/20 my-1">
                    <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Net à Payer</span>
                    <span id="total-net-expanded" class="text-sm font-black text-indigo-400 font-mono">0.00 DA</span>
                </div>

                <!-- Remaining balance (Solde Box) -->
                <div class="flex items-center justify-between bg-amber-500/10 p-2 rounded-lg border border-amber-500/20 my-1">
                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider">Reste à payer (Solde)</span>
                    <span id="remaining-balance" class="text-sm font-black text-amber-500 font-mono">0.00 DA</span>
                </div>
            </div>

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

            <!-- Inputs for delivery date and remarks -->
            <div class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Livraison Prévue</label>
                    <input type="date" id="delivery-date-input" 
                           class="w-full bg-slate-850 border border-slate-700 rounded-md px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Caisse Ticket N°</label>
                    <input type="text" id="ticket-number-input" value="{{ $nextTicketNumber }}"
                           @if(auth()->user()->role !== 'admin') readonly @endif
                           class="w-full bg-slate-850 border border-slate-700 rounded-md px-2.5 py-1.5 text-xs text-slate-200 font-mono text-center focus:outline-none focus:border-indigo-500 font-mono @if(auth()->user()->role !== 'admin') opacity-50 cursor-not-allowed @endif">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Remarques / Notes du ticket</label>
                <input type="text" id="remarks-input" placeholder="Ex: suspendu, urgent, ..."
                       class="w-full bg-slate-850 border border-slate-700 rounded-md px-2.5 py-1.5 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500">
            </div>

            <!-- No Print Checkbox -->
            <div class="flex items-center space-x-2 py-1 select-none">
                <input type="checkbox" id="no-print-toggle" class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer h-4 w-4">
                <label for="no-print-toggle" class="text-xs font-semibold text-slate-300 cursor-pointer">Ne pas imprimer de ticket</label>
            </div>

            <!-- Submit action inside expanded view -->
            <div class="pt-2">
                <button type="button" onclick="openCheckoutPaymentModal()" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-display font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/20 active:translate-y-0.5 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Valider & Imprimer le Ticket</span>
                </button>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- ================= MODALS OVERLAYS ================= -->

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
                    'vert eau' => ['bg' => '#a7f3d0', 'text' => '#065f46', 'border' => '#6ee7b7'],
                    'vert pistache' => ['bg' => '#bef264', 'text' => '#3f6212', 'border' => '#a3e635'],
                    'vert olive' => ['bg' => '#65a30d', 'text' => '#ffffff', 'border' => '#4d7c0f'],
                    'violet' => ['bg' => '#7c3aed', 'text' => '#ffffff', 'border' => '#6d28d9'],
                    'noir' => ['bg' => '#09090b', 'text' => '#ffffff', 'border' => '#3f3f46'],
                    'fushia' => ['bg' => '#d946ef', 'text' => '#ffffff', 'border' => '#c026d3']
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

            <!-- Motifs Selection -->
            <div>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Motif(s)</span>
                <div class="flex flex-wrap gap-1.5" id="modal-patterns-container">
                    @foreach($patterns as $pattern)
                        <button onclick="toggleItemOption('patterns', '{{ $pattern }}', this)" 
                                data-pattern-btn="true"
                                class="option-badge px-3 py-1 rounded-full text-xs border border-slate-700 bg-slate-900 text-slate-300 hover:border-slate-500 transition-colors">
                            {{ $pattern }}
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
<div id="new-client-modal" class="hidden fixed inset-0 bg-slate-950/70 z-50 flex items-center justify-center p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-96 flex flex-col shadow-2xl overflow-hidden transform scale-100 transition-all">
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

<!-- 3. Client Selection Modal -->
<div id="client-select-modal" class="hidden fixed inset-0 bg-slate-950/70 z-50 flex items-center justify-center p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-md flex flex-col shadow-2xl overflow-hidden transform scale-100 transition-all">
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-base font-bold text-white font-display">Associer un Client</h3>
            <button onclick="closeClientSelectionModal()" class="text-slate-400 hover:text-white cursor-pointer">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <input type="text" id="client-search-input" oninput="searchClients(this.value)" 
                           placeholder="Rechercher client (nom, code, tél)..." 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-10 pr-8 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
                    <div class="absolute left-3.5 top-3.5 text-slate-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <button type="button" onclick="triggerNewClientFromSelect()" 
                        class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl text-xs flex items-center justify-center cursor-pointer transition-colors shadow-lg shadow-indigo-600/10">
                    + Nouveau
                </button>
            </div>

            <!-- Results list -->
            <div id="client-select-results" class="bg-slate-900 border border-slate-700/60 rounded-xl max-h-60 overflow-y-auto divide-y divide-slate-800/60 hidden">
                <!-- Populated by JS -->
            </div>

            <div id="client-select-empty" class="text-center py-6 text-slate-500 text-xs">
                Saisissez au moins 2 caractères pour rechercher un client.
            </div>
        </div>
    </div>
</div>

<!-- 4. Modal Encaissement & Pavé Numérique Tactile (Calculatrice Compacte) -->
<div id="checkout-payment-modal" class="hidden fixed inset-0" style="display: none; position: fixed; inset: 0; z-index: 999999; background-color: rgba(2, 6, 23, 0.75); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 12px;" onclick="if(event.target === this) closeCheckoutPaymentModal()">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl shadow-2xl overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150" style="width: 100%; max-width: 415px; max-height: 94vh; display: flex; flex-direction: column; margin: auto;" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="px-4 py-2.5 bg-slate-800/90 border-b border-slate-700/80 flex justify-between items-center shrink-0">
            <div class="flex items-center space-x-2.5">
                <div class="h-8 w-8 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-base shadow-inner">
                    💰
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-white font-display flex items-center space-x-1.5">
                        <span>Règlement & Acompte</span>
                        <span id="cpay-ticket-badge" class="text-sky-400 font-mono text-[11px] px-1.5 py-0.2 rounded bg-sky-950/80 border border-sky-500/30">#...</span>
                    </h3>
                    <p class="text-[10px] text-slate-400">Client : <span id="cpay-client-name" class="font-bold text-slate-200">Client Passage</span></p>
                </div>
            </div>
            <button type="button" onclick="closeCheckoutPaymentModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-700/60 transition-colors cursor-pointer" title="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-3.5 space-y-2.5 overflow-y-auto">
            <!-- Financial Recap Strip (Net, Versé, Solde) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;" class="p-2 bg-slate-950/70 border border-slate-800 rounded-xl text-center font-mono">
                <div class="bg-slate-900/70 p-1.5 rounded-lg border border-slate-800/90 flex flex-col justify-center">
                    <span class="text-[9px] uppercase font-sans font-bold text-slate-400 block mb-0.5 whitespace-nowrap">Net à Payer</span>
                    <span id="cpay-total-net" class="text-xs sm:text-sm font-black text-indigo-400 font-mono">0 DA</span>
                </div>
                <div class="bg-slate-900/70 p-1.5 rounded-lg border border-slate-800/90 flex flex-col justify-center">
                    <span class="text-[9px] uppercase font-sans font-bold text-slate-400 block mb-0.5 whitespace-nowrap">Acompte Perçu</span>
                    <span id="cpay-paid-display" class="text-xs sm:text-sm font-black text-emerald-400 font-mono">0 DA</span>
                </div>
                <div class="bg-slate-900/70 p-1.5 rounded-lg border border-slate-800/90 flex flex-col justify-center">
                    <span class="text-[9px] uppercase font-sans font-bold text-slate-400 block mb-0.5 whitespace-nowrap">Reste Solde</span>
                    <span id="cpay-balance-display" class="text-xs sm:text-sm font-black text-amber-400 font-mono">0 DA</span>
                </div>
            </div>

            <!-- Interactive Calculator Display Screen (Number cleanly inside) -->
            <div class="bg-slate-950 border-2 border-slate-700/80 focus-within:border-emerald-500/80 rounded-xl p-2.5 px-3.5 transition-all shadow-inner flex items-center justify-between overflow-hidden">
                <div class="shrink-0 pr-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Versement / Acompte :</span>
                    <span class="text-[9px] text-slate-500 font-sans">Pavé tactile</span>
                </div>
                <div class="flex items-baseline justify-end space-x-1.5 flex-1 min-w-0 pr-1">
                    <input type="text" id="cpay-input-value" value="0" readonly
                           style="background: transparent; border: none; outline: none; font-size: 1.65rem; font-weight: 900; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; text-align: right; color: #34d399; width: 100%; max-width: 140px; cursor: default; padding: 0 4px;"
                           class="select-all">
                    <span class="text-xs font-bold text-emerald-500 font-mono shrink-0">DA</span>
                </div>
            </div>

            <!-- Dynamic Feedback (Monnaie à rendre OU Solde différé OU Payé en totalité) -->
            <div id="cpay-feedback-container">
                <!-- Change box (if input > net) -->
                <div id="cpay-change-box" class="hidden p-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-between text-xs">
                    <span class="text-emerald-300 font-bold flex items-center space-x-1.5">
                        <span>💵</span>
                        <span>Monnaie à rendre au client :</span>
                    </span>
                    <span id="cpay-change-val" class="font-mono font-black text-emerald-300 text-xs">0 DA</span>
                </div>
                <!-- Balance box (if input < net) -->
                <div id="cpay-partial-box" class="p-2 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-between text-xs">
                    <span class="text-amber-300 font-bold flex items-center space-x-1.5">
                        <span>📝</span>
                        <span>Reste à payer au retrait (Solde) :</span>
                    </span>
                    <span id="cpay-partial-val" class="font-mono font-black text-amber-300 text-xs">0 DA</span>
                </div>
                <!-- Fully paid box (if input == net) -->
                <div id="cpay-full-box" class="hidden p-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-between text-xs">
                    <span class="text-emerald-300 font-bold flex items-center space-x-1.5">
                        <span>✅</span>
                        <span>Commande réglée en totalité</span>
                    </span>
                    <span class="font-mono font-bold text-emerald-300 text-xs">Solde 0 DA</span>
                </div>
            </div>

            <!-- Quick Preset: Totalité seule sur la ligne -->
            <div>
                <button type="button" onclick="cpaySetPreset('exact')" 
                        class="w-full py-2 px-3 bg-indigo-600/25 hover:bg-indigo-600 text-indigo-300 hover:text-white rounded-xl text-xs font-bold border border-indigo-500/40 transition-all cursor-pointer text-center active:scale-98 shadow-sm flex items-center justify-center space-x-2">
                    <svg class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Payer la totalité (Net)</span>
                </button>
            </div>

            <!-- Tactile Numeric Keypad (4x3 Grid) -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;" class="pt-0.5">
                <button type="button" onclick="cpayPressKey('7')" class="cpay-key">7</button>
                <button type="button" onclick="cpayPressKey('8')" class="cpay-key">8</button>
                <button type="button" onclick="cpayPressKey('9')" class="cpay-key">9</button>

                <button type="button" onclick="cpayPressKey('4')" class="cpay-key">4</button>
                <button type="button" onclick="cpayPressKey('5')" class="cpay-key">5</button>
                <button type="button" onclick="cpayPressKey('6')" class="cpay-key">6</button>

                <button type="button" onclick="cpayPressKey('1')" class="cpay-key">1</button>
                <button type="button" onclick="cpayPressKey('2')" class="cpay-key">2</button>
                <button type="button" onclick="cpayPressKey('3')" class="cpay-key">3</button>

                <button type="button" onclick="cpayClear()" class="cpay-key-action bg-rose-500/20 hover:bg-rose-500/30 text-rose-400 border border-rose-500/40 font-black">
                    C
                </button>
                <button type="button" onclick="cpayPressKey('0')" class="cpay-key">0</button>
                <button type="button" onclick="cpayPressKey('00')" class="cpay-key text-base font-bold">00</button>
            </div>
            
            <!-- Backspace button (full-width) -->
            <div class="pt-0.5">
                <button type="button" onclick="cpayBackspace()" class="w-full py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs border border-slate-700 flex items-center justify-center space-x-2 transition-all active:scale-95 cursor-pointer">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414a2 2 0 011.414-.586H19a2 2 0 012 2v10a2 2 0 01-2 2h-9.172a2 2 0 01-1.414-.586L3 12z" />
                    </svg>
                    <span>Effacer (⌫)</span>
                </button>
            </div>
        </div>

        <!-- Footer / Confirm Validation & Print -->
        <div class="p-3 bg-slate-800/90 border-t border-slate-700/80 space-y-1.5 shrink-0">
            <button type="button" onclick="cpayConfirmAndSubmit()" 
                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-display font-extrabold text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-600/30 active:scale-98 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span id="cpay-submit-btn-label">Valider sans acompte</span>
            </button>
            <button type="button" onclick="closeCheckoutPaymentModal()" 
                    class="w-full py-1 text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-700/40 rounded-xl transition-colors text-center cursor-pointer">
                Annuler / Retour au panier
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Global catalog state injected from PHP
    const allItems = @json($items);
    const allServices = @json($services);
    const allTargets = @json($targets);
    const allSubcategories = @json($subcategories);
    const editingOrder = @json($editingOrder ?? null);
    const IS_ADMIN = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};
    
    // Default client object
    const defaultClient = {
        id: {{ $guestClient->id }},
        code: '{{ $guestClient->code }}',
        name: '{{ $guestClient->name }}',
        discount_percent: {{ $guestClient->discount_percent }},
        credit: {{ $guestClient->credit }}
    };

    // App state
    let selectedServiceId = {{ $services->first() ? $services->first()->id : 1 }};
    let selectedTargetId = {{ $targets->first() ? $targets->first()->id : 1 }};
    let selectedSubcategoryId = null;
    let selectedClient = { ...defaultClient };
    let cart = [];
    let pricingMode = 'detail';

    window.setPricingMode = function(mode) {
        if (pricingMode === mode) return;
        pricingMode = mode;

        // Toggle UI buttons classes
        const btnDetail = document.getElementById('pricing-mode-detail');
        const btnWholesale = document.getElementById('pricing-mode-wholesale');

        if (mode === 'detail') {
            btnDetail.className = "px-3 py-1 rounded-md text-[10px] font-bold uppercase transition-all cursor-pointer bg-indigo-600 text-white shadow shadow-indigo-600/10";
            btnWholesale.className = "px-3 py-1 rounded-md text-[10px] font-bold uppercase text-slate-400 hover:text-white transition-all cursor-pointer";
        } else {
            btnDetail.className = "px-3 py-1 rounded-md text-[10px] font-bold uppercase text-slate-400 hover:text-white transition-all cursor-pointer";
            btnWholesale.className = "px-3 py-1 rounded-md text-[10px] font-bold uppercase transition-all cursor-pointer bg-indigo-600 text-white shadow shadow-indigo-600/10";
        }

        // Re-render the catalog items grid with the new prices
        renderCatalog();

        // Update all item prices in the cart
        cart.forEach(cartItem => {
            const itemObj = allItems.find(i => i.id === cartItem.id);
            const itemService = allServices.find(s => s.id === cartItem.service_id);
            const isItemKilo = itemService && (itemService.code === 'au_kilo' || itemService.name.toLowerCase().includes('kilo') || cartItem.service_id === 4);

            if (isItemKilo && itemService && itemService.price !== null && itemService.price !== undefined && itemService.price !== '') {
                if (mode === 'wholesale' && itemService.wholesale_price !== null && itemService.wholesale_price !== undefined && itemService.wholesale_price !== '') {
                    cartItem.unit_price = parseFloat(itemService.wholesale_price);
                } else {
                    cartItem.unit_price = parseFloat(itemService.price);
                }
            } else if (itemObj) {
                const prices = itemObj.service_prices || itemObj.servicePrices || [];
                const priceObj = prices.find(sp => sp.service_id === cartItem.service_id);
                if (priceObj) {
                    let price = 0;
                    if (mode === 'wholesale' && priceObj.wholesale_price !== null && priceObj.wholesale_price !== undefined && priceObj.wholesale_price !== '') {
                        price = parseFloat(priceObj.wholesale_price);
                    } else {
                        price = parseFloat(priceObj.price);
                    }
                    cartItem.unit_price = price;
                }
            }
        });

        // Re-render cart and update totals
        renderCart();
        updateCartCalculations();
    };
    
    // Auxiliary State for modal options
    let currentOptionsIndex = null;
    let isAddingNewItem = false;
    let pendingItem = null;
    let pendingPrice = 0;
    let currentOptions = {
        colors: [],
        patterns: [],
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
        selectService({{ $services->first() ? $services->first()->id : 1 }});
        selectTarget({{ $targets->first() ? $targets->first()->id : 1 }});
        
        // Load editing order if present
        if (editingOrder) {
            // Populate client
            selectedClient = { ...editingOrder.client };
            
            // Populate discount
            document.getElementById('discount-type-select').value = editingOrder.discount_type || 'percent';
            document.getElementById('discount-percent-input').value = editingOrder.discount_type === 'percent' ? parseFloat(editingOrder.discount_percent) : parseFloat(editingOrder.discount_amount);
            
            // Populate paid amount
            document.getElementById('paid-amount-input').value = parseFloat(editingOrder.paid_amount);
            
            // Populate express mode
            document.getElementById('express-toggle-input').checked = !!editingOrder.is_express;
            
            // Populate delivery date
            if (editingOrder.target_delivery_date) {
                const dateParts = editingOrder.target_delivery_date.substring(0, 10);
                document.getElementById('delivery-date-input').value = dateParts;
            }
            
            // Populate ticket number & remarks
            document.getElementById('ticket-number-input').value = editingOrder.ticket_number;
            document.getElementById('remarks-input').value = editingOrder.remarks || '';
            
            // Populate cart
            cart = editingOrder.order_items.map(item => {
                const stdW = item.garment_item && item.garment_item.standard_weight ? parseFloat(item.garment_item.standard_weight) : 1000;
                const itmWeight = item.weight !== null && item.weight !== undefined ? parseFloat(item.weight) : parseFloat(item.quantity);
                const isCarpet = (item.garment_item && (item.garment_item.is_carpet || item.garment_item.unit_type === 'm2')) || 
                                 (item.garment_item && item.garment_item.name && item.garment_item.name.toLowerCase().includes('tapis')) ||
                                 item.area !== null;
                const piecesPerItem = item.garment_item && item.garment_item.pieces_count ? parseInt(item.garment_item.pieces_count) : 1;
                return {
                    id: item.garment_item_id,
                    name: item.garment_item ? item.garment_item.name : 'Article',
                    service_id: item.service_id,
                    service_name: item.service ? item.service.name : 'Service',
                    is_carpet: isCarpet,
                    is_measured: !!item.is_measured,
                    length: item.length,
                    width: item.width,
                    area: item.area,
                    standard_weight: stdW,
                    pieces_count: piecesPerItem,
                    pieces: item.pieces ? parseInt(item.pieces) : Math.round(piecesPerItem * parseFloat(item.quantity)),
                    weight: itmWeight,
                    quantity: parseFloat(item.quantity),
                    unit_price: parseFloat(item.unit_price) / (editingOrder.is_express ? 2 : 1), // standard unit price
                    total_price: parseFloat(item.total_price),
                    colors: item.colors || [],
                    defects: item.defects || [],
                    stains: item.stains || [],
                    notes: item.notes || ''
                };
            });
            
            // Change title in cart header to show we are editing
            const cartHeader = document.querySelector('#cart-items-container').parentElement.querySelector('h3') || document.createElement('h3');
            cartHeader.innerHTML = `<span class="text-amber-500 font-bold">Modification Ticket N° ${editingOrder.ticket_number}</span>`;
        }

        renderSelectedClient();
        renderCart();
        updateCartCalculations();
        setCartCollapsedState(isCartCollapsed);
    });

    // ================= CATALOG MANAGEMENT =================

    window.selectService = function(id) {
        selectedServiceId = id;
        
        // Update active tab styles
        document.querySelectorAll('.service-tab').forEach(btn => {
            btn.classList.remove('service-tab-active');
        });
        const activeBtn = document.getElementById(`service-tab-${id}`);
        if(activeBtn) activeBtn.classList.add('service-tab-active');

        const currentService = allServices.find(s => s.id === id);
        const targetsBar = document.getElementById('targets-bar');
        if (targetsBar) targetsBar.classList.remove('hidden');

        // Smart target selection on service change
        const isBlanchisserie = currentService && (currentService.code === 'blanchisserie' || currentService.name.toLowerCase().includes('blanchisserie'));
        const isKilo = currentService && (currentService.code === 'au_kilo' || currentService.name.toLowerCase().includes('kilo'));

        const lingeDeMaisonTarget = allTargets.find(t => t.name.toLowerCase().includes('maison') || t.id === 5);
        const hommeTarget = allTargets.find(t => t.name.toLowerCase() === 'homme' || t.id === 1);

        if (isBlanchisserie && lingeDeMaisonTarget) {
            // For Blanchisserie, default to Linge de maison
            selectedTargetId = lingeDeMaisonTarget.id;
        } else if (isKilo && selectedTargetId === 4 && hommeTarget) {
            // Cuir (id 4) has no kilo items, default to Homme
            selectedTargetId = hommeTarget.id;
        } else if (!selectedTargetId) {
            selectedTargetId = hommeTarget ? hommeTarget.id : (allTargets[0] ? allTargets[0].id : 1);
        }

        // Reset subcategory to "Tous" on service change
        selectedSubcategoryId = null;

        // Highlight target pills
        updateTargetPillsStyles();

        // Render subcategory pills
        renderSubcategoryPills();

        // Render catalog grid
        renderCatalog();
    };

    window.selectTarget = function(id) {
        selectedTargetId = id;
        selectedSubcategoryId = null;
        updateTargetPillsStyles();
        renderSubcategoryPills();
        renderCatalog();
    };

    window.updateTargetPillsStyles = function() {
        document.querySelectorAll('.target-pill').forEach(btn => {
            btn.classList.remove('target-pill-active', 'bg-slate-800', 'text-white');
        });
        const activePill = document.getElementById(`target-pill-${selectedTargetId}`);
        if(activePill) activePill.classList.add('target-pill-active', 'bg-slate-800', 'text-white');
    };

    window.renderSubcategoryPills = function() {
        const subContainer = document.getElementById('subcategories-container');
        const subBar = document.getElementById('subcategories-bar');
        if (!subBar) return;

        const targetsBar = document.getElementById('targets-bar');
        if (targetsBar && targetsBar.classList.contains('hidden')) {
            if (subContainer) subContainer.classList.add('hidden');
            subBar.innerHTML = '';
            return;
        }

        // Filter subcategories for selectedTargetId and sort by sort_order ASC, then name
        const targetSubs = allSubcategories
            .filter(sub => sub.garment_target_id === selectedTargetId)
            .sort((a, b) => (parseInt(a.sort_order || 0) - parseInt(b.sort_order || 0)) || a.name.localeCompare(b.name));

        if (targetSubs.length === 0) {
            if (subContainer) subContainer.classList.add('hidden');
            subBar.innerHTML = '';
            return;
        }

        if (subContainer) subContainer.classList.remove('hidden');
        subBar.innerHTML = '';

        // "Tous" pill
        const isAllActive = selectedSubcategoryId === null;
        const allBtn = document.createElement('button');
        allBtn.type = 'button';
        allBtn.className = `subcat-pill shrink-0 px-3 py-1 rounded-lg text-sm font-semibold transition-all duration-150 cursor-pointer ${isAllActive ? 'subcat-pill-active bg-indigo-600 text-white shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-800/80'}`;
        allBtn.innerHTML = `<span>Tous</span>`;
        allBtn.onclick = () => selectSubcategory(null);
        subBar.appendChild(allBtn);

        // Subcategory pills
        targetSubs.forEach(sub => {
            const isActive = selectedSubcategoryId === sub.id;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `subcat-pill shrink-0 px-3 py-1 rounded-lg text-sm font-semibold transition-all duration-150 cursor-pointer ${isActive ? 'subcat-pill-active bg-indigo-600 text-white shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-800/80'}`;
            btn.innerHTML = `<span>${sub.name}</span>`;
            btn.onclick = () => selectSubcategory(sub.id);
            subBar.appendChild(btn);
        });
    };

    window.selectSubcategory = function(subcatId) {
        selectedSubcategoryId = subcatId;
        renderSubcategoryPills();
        renderCatalog();
    };

    function renderCatalog() {
        const grid = document.getElementById('catalog-grid');
        const empty = document.getElementById('catalog-empty');
        if (!grid || !empty) return;
        grid.innerHTML = '';

        const currentService = allServices.find(s => s.id === selectedServiceId);
        const isKiloService = currentService && (currentService.code === 'au_kilo' || currentService.name.toLowerCase().includes('kilo') || selectedServiceId === 4);
        const serviceHasUniformPrice = isKiloService && currentService && currentService.price !== null && currentService.price !== undefined && currentService.price !== '';

        // Filter items safely
        const filtered = allItems.filter(item => {
            const prices = item.service_prices || item.servicePrices || [];
            // Must have a service price for the selected service OR service has a uniform price
            const hasPrice = serviceHasUniformPrice || prices.some(sp => sp.service_id === selectedServiceId);
            if (!hasPrice) return false;

            // Filter strictly by target (category)
            if (item.garment_target_id !== selectedTargetId) return false;

            // Filter by subcategory (if a specific subcategory is selected, not "Tous")
            if (selectedSubcategoryId !== null && item.garment_subcategory_id !== selectedSubcategoryId) {
                return false;
            }

            return true;
        });

        if (filtered.length === 0) {
            grid.classList.add('hidden');
            empty.classList.remove('hidden');
            return;
        }

        grid.classList.remove('hidden');
        empty.classList.add('hidden');

        filtered.forEach(item => {
            const prices = item.service_prices || item.servicePrices || [];
            const priceObj = prices.find(sp => sp.service_id === selectedServiceId);
            let price = 0;

            if (isKiloService && currentService && currentService.price !== null && currentService.price !== undefined && currentService.price !== '') {
                if (pricingMode === 'wholesale' && currentService.wholesale_price !== null && currentService.wholesale_price !== undefined && currentService.wholesale_price !== '') {
                    price = parseFloat(currentService.wholesale_price);
                } else {
                    price = parseFloat(currentService.price);
                }
            } else if (priceObj) {
                if (pricingMode === 'wholesale' && priceObj.wholesale_price !== null && priceObj.wholesale_price !== undefined && priceObj.wholesale_price !== '') {
                    price = parseFloat(priceObj.wholesale_price);
                } else {
                    price = parseFloat(priceObj.price);
                }
            }

            const isCarpet = (item.is_carpet) || (item.unit_type === 'm2') || 
                             (item.name && (item.name.toLowerCase().includes('tapis') || item.name.toLowerCase().includes('m²')));

            const card = document.createElement('button');
            if (isKiloService) {
                card.onclick = () => selectKiloItem(item, price);
            } else {
                card.onclick = () => openOptionsModal(item, price);
            }

            if (item.image_path) {
                const imageSrc = encodeURI(item.image_path.startsWith('/') ? item.image_path : '/' + item.image_path);
                card.className = "relative border border-slate-700/60 p-3.5 rounded-2xl text-left flex flex-col justify-between h-32 active:scale-95 hover:border-slate-500 transition-all duration-150 shadow-md cursor-pointer overflow-hidden bg-cover bg-center";
                card.style.backgroundImage = `linear-gradient(to bottom, rgba(15, 23, 42, 0.65), rgba(15, 23, 42, 0.85)), url('${imageSrc}')`;
            } else {
                card.className = "bg-slate-800 border border-slate-700/60 p-3.5 rounded-2xl text-left flex flex-col justify-between h-32 active:scale-95 hover:border-slate-500 hover:bg-slate-800/80 transition-all duration-150 shadow-md cursor-pointer";
            }

            // Item Name
            const title = document.createElement('h3');
            title.className = "text-xs font-bold text-slate-100 leading-snug line-clamp-2 uppercase font-display z-10";
            title.textContent = item.name;
            card.appendChild(title);

            // Weight badge if Kilo Service
            if (isKiloService) {
                const stdG = item.standard_weight ? parseFloat(item.standard_weight) : null;
                const weightBadge = document.createElement('div');
                weightBadge.className = "z-10 mt-1 flex flex-col gap-0.5";
                if (stdG) {
                    const estItemPrice = Math.round((stdG / 1000) * price);
                    weightBadge.innerHTML = `
                        <span class="text-[9px] font-bold text-amber-300 bg-amber-500/20 border border-amber-500/30 px-1.5 py-0.5 rounded-full inline-flex items-center space-x-1">
                            <span>⚖️</span><span>${stdG >= 1000 ? (stdG/1000).toFixed(2) + ' kg' : stdG + 'g'}</span>
                        </span>
                        ${price > 0 ? `<span class="text-[9px] text-slate-400 font-mono">≈ ${estItemPrice} DA</span>` : ''}
                    `;
                } else {
                    weightBadge.innerHTML = `<span class="text-[9px] font-bold text-slate-400 bg-slate-700/50 border border-slate-600/50 px-1.5 py-0.5 rounded-full inline-flex items-center space-x-1"><span>⚖️</span><span>Au Kilo</span></span>`;
                }
                card.appendChild(weightBadge);
            } else if (isCarpet) {
                const carpetBadge = document.createElement('div');
                carpetBadge.className = "z-10 mt-1 flex flex-col gap-0.5";
                carpetBadge.innerHTML = `
                    <span class="text-[9px] font-bold text-amber-300 bg-amber-500/20 border border-amber-500/30 px-1.5 py-0.5 rounded-full inline-flex items-center space-x-1">
                        <span>📏</span><span>Métrage atelier</span>
                    </span>
                `;
                card.appendChild(carpetBadge);
            }

            // Price badge
            const priceBadge = document.createElement('div');
            priceBadge.className = "text-right mt-auto z-10";
            
            const priceText = document.createElement('span');
            priceText.className = "text-sm font-black font-display text-indigo-400";
            if (isKiloService) {
                priceText.textContent = `${price.toFixed(0)} DA/kg`;
            } else if (isCarpet) {
                priceText.textContent = `${price.toFixed(0)} DA/m²`;
            } else {
                priceText.textContent = `${price.toFixed(0)} DA`;
            }

            priceBadge.appendChild(priceText);
            card.appendChild(priceBadge);
            grid.appendChild(card);
        });
    }

    // ================= CARPET SERVICE QUICK SELECT =================

    window.selectCarpetItem = function(item, price) {
        openOptionsModal(item, price);
    };

    // ================= KILO SERVICE QUICK SELECT =================

    window.selectKiloItem = function(item, price) {
        const currentService = allServices.find(s => s.id === selectedServiceId);
        const sName = currentService ? currentService.name : 'Au Kilo';

        const effectivePrice = (price && price > 0) ? price : (currentService ? (pricingMode === 'wholesale' && currentService.wholesale_price ? parseFloat(currentService.wholesale_price) : (parseFloat(currentService.price) || 0)) : 0);

        const stdWeightG = item.standard_weight ? parseFloat(item.standard_weight) : 500;
        const stdWeightKg = stdWeightG / 1000;

        const existingIndex = cart.findIndex(ci => ci.id === item.id && ci.service_id === selectedServiceId);

        if (existingIndex !== -1) {
            const currentPieces = cart[existingIndex].pieces || 1;
            cart[existingIndex].pieces = currentPieces + 1;
            const currentWeight = (cart[existingIndex].weight !== undefined && cart[existingIndex].weight !== null) ? cart[existingIndex].weight : cart[existingIndex].quantity;
            const newWeight = parseFloat((currentWeight + stdWeightKg).toFixed(3));
            cart[existingIndex].weight = newWeight;
            cart[existingIndex].quantity = newWeight;
            cart[existingIndex].unit_price = effectivePrice;
        } else {
            cart.push({
                id: item.id,
                name: item.name,
                service_id: selectedServiceId,
                service_name: sName,
                standard_weight: stdWeightG,
                pieces: 1,
                weight: stdWeightKg,
                quantity: stdWeightKg,
                unit_price: effectivePrice,
                colors: [],
                defects: [],
                stains: [],
                notes: ''
            });
        }

        renderCart();
        updateCartCalculations();
        closePaymentView();
    };

    // ================= CART LOGIC =================

    function addToCart(item, price) {
        const currentService = allServices.find(s => s.id === selectedServiceId);
        const sName = currentService ? currentService.name : 'Service';
        const piecesPerItem = parseInt(item.pieces_count) || 1;

        cart.push({
            id: item.id,
            name: item.name,
            service_id: selectedServiceId,
            service_name: sName,
            pieces_count: piecesPerItem,
            pieces: piecesPerItem,
            quantity: 1,
            unit_price: price,
            colors: [],
            defects: [],
            stains: [],
            notes: ''
        });

        renderCart();
        updateCartCalculations();
        closePaymentView();
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

            const isKiloService = item.service_id === 4 || (item.service_name && item.service_name.toLowerCase().includes('kilo'));

            const info = document.createElement('div');
            const nameText = document.createElement('h4');
            nameText.className = "text-xs font-bold text-slate-100 uppercase";
            nameText.textContent = item.name;
            
            const serviceText = document.createElement('span');
            serviceText.className = "text-[9px] font-bold text-indigo-400 bg-indigo-500/10 px-1 py-0.5 rounded uppercase tracking-wider";
            serviceText.textContent = item.service_name;
            
            info.appendChild(nameText);
            info.appendChild(serviceText);

            if (isKiloService && item.standard_weight) {
                const stdTag = document.createElement('span');
                stdTag.className = "text-[9px] font-bold text-amber-400 bg-amber-400/10 border border-amber-400/20 px-1 py-0.5 rounded uppercase tracking-wider ml-1";
                stdTag.textContent = `${item.standard_weight}g/pc`;
                info.appendChild(stdTag);
            } else if (item.is_carpet) {
                const carpetTag = document.createElement('span');
                carpetTag.className = "text-[9px] font-bold text-amber-300 bg-amber-500/10 border border-amber-500/20 px-1.5 py-0.5 rounded uppercase tracking-wider ml-1";
                carpetTag.textContent = item.is_measured ? `${item.area} m²` : "Métrage atelier";
                info.appendChild(carpetTag);
            } else if (item.pieces_count && item.pieces_count > 1) {
                const pieceTag = document.createElement('span');
                pieceTag.className = "text-[9px] font-bold text-sky-400 bg-sky-500/10 border border-sky-500/20 px-1.5 py-0.5 rounded uppercase tracking-wider ml-1";
                pieceTag.textContent = `${item.pieces_count} pièces (${item.pieces || (item.pieces_count * item.quantity)} pcs)`;
                info.appendChild(pieceTag);
            }

            // Visible options summary directly in the cart item line!
            const optBadges = [];
            if (item.colors && item.colors.length > 0) optBadges.push(`<span class="text-indigo-400 font-semibold">🎨 ${item.colors.join(', ')}</span>`);
            if (item.defects && item.defects.length > 0) optBadges.push(`<span class="text-rose-400 font-semibold">⚠️ ${item.defects.join(', ')}</span>`);
            if (item.stains && item.stains.length > 0) optBadges.push(`<span class="text-amber-400 font-semibold">🫧 ${item.stains.join(', ')}</span>`);
            if (item.notes) optBadges.push(`<span class="text-slate-300">📝 ${item.notes}</span>`);
            if (optBadges.length > 0) {
                const optContainer = document.createElement('div');
                optContainer.className = "text-[10px] space-x-1 mt-0.5 leading-tight flex flex-wrap gap-y-0.5";
                optContainer.innerHTML = optBadges.join('<span class="text-slate-600 font-normal"> | </span>');
                info.appendChild(optContainer);
            }

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

            const qtyCtrl = document.createElement('div');
            qtyCtrl.className = "flex items-center space-x-1.5";

            if (isKiloService) {
                // Controller for Kilo Service:
                // 1) Pieces counter (- / X pcs / +)
                const piecesBox = document.createElement('div');
                piecesBox.className = "flex items-center bg-slate-800 rounded-md border border-slate-700/50";

                const minusBtn = document.createElement('button');
                minusBtn.className = "px-2 py-1 text-slate-400 hover:text-white font-bold text-xs cursor-pointer";
                minusBtn.textContent = '-';
                minusBtn.onclick = () => {
                    const stdKg = (item.standard_weight ? parseFloat(item.standard_weight) : 1000) / 1000;
                    const currentPieces = item.pieces || 1;
                    if (currentPieces <= 1) {
                        removeFromCart(index);
                    } else {
                        item.pieces = currentPieces - 1;
                        item.weight = parseFloat(Math.max(0.01, ((item.weight || item.quantity) - stdKg)).toFixed(3));
                        item.quantity = item.weight;
                        renderCart();
                        updateCartCalculations();
                    }
                };

                const piecesText = document.createElement('span');
                piecesText.className = "px-1.5 text-[11px] font-bold text-slate-200 select-none whitespace-nowrap";
                piecesText.textContent = `${item.pieces || 1} pcs`;

                const plusBtn = document.createElement('button');
                plusBtn.className = "px-2 py-1 text-slate-400 hover:text-white font-bold text-xs cursor-pointer";
                plusBtn.textContent = '+';
                plusBtn.onclick = () => {
                    const stdKg = (item.standard_weight ? parseFloat(item.standard_weight) : 1000) / 1000;
                    item.pieces = (item.pieces || 1) + 1;
                    item.weight = parseFloat(((item.weight || item.quantity) + stdKg).toFixed(3));
                    item.quantity = item.weight;
                    renderCart();
                    updateCartCalculations();
                };

                piecesBox.appendChild(minusBtn);
                piecesBox.appendChild(piecesText);
                piecesBox.appendChild(plusBtn);

                // 2) Direct Weight Input (kg)
                const weightBox = document.createElement('div');
                weightBox.className = "flex items-center bg-slate-800 rounded-md border border-slate-700/50 px-1.5 py-0.5";

                const weightInput = document.createElement('input');
                weightInput.type = "number";
                weightInput.min = "0.01";
                weightInput.step = "0.05";
                weightInput.value = (item.weight !== undefined && item.weight !== null ? parseFloat(item.weight) : parseFloat(item.quantity)).toFixed(2);
                weightInput.className = "w-12 bg-transparent text-center text-xs font-mono font-bold text-amber-400 focus:outline-none focus:bg-slate-900 rounded";
                weightInput.title = "Poids en kg (modifiable)";
                weightInput.onchange = (e) => {
                    let val = parseFloat(e.target.value);
                    if (isNaN(val) || val <= 0) val = 0.1;
                    item.weight = val;
                    item.quantity = val;
                    renderCart();
                    updateCartCalculations();
                };
                weightInput.onclick = (e) => e.stopPropagation();

                const kgLabel = document.createElement('span');
                kgLabel.className = "text-[10px] text-slate-400 font-bold pr-0.5 select-none";
                kgLabel.textContent = "kg";

                weightBox.appendChild(weightInput);
                weightBox.appendChild(kgLabel);

                qtyCtrl.appendChild(piecesBox);
                qtyCtrl.appendChild(weightBox);
            } else if (item.is_carpet) {
                // Pieces counter for carpet (- / 1 pc / +)
                const piecesBox = document.createElement('div');
                piecesBox.className = "flex items-center bg-slate-800 rounded-md border border-slate-700/50";

                const minusBtn = document.createElement('button');
                minusBtn.onclick = () => {
                    removeFromCart(index);
                };
                minusBtn.className = "px-2 py-1 text-slate-400 hover:text-white font-bold text-xs cursor-pointer";
                minusBtn.textContent = '-';
                minusBtn.title = "Supprimer cet article";

                const piecesText = document.createElement('span');
                piecesText.className = "px-1.5 text-xs font-mono font-bold text-slate-200";
                piecesText.textContent = `1 pc`;

                const plusBtn = document.createElement('button');
                plusBtn.onclick = () => {
                    const itemObj = allItems.find(i => i.id === item.id) || { id: item.id, name: item.name, unit_type: 'm2' };
                    openOptionsModal(itemObj, item.unit_price);
                };
                plusBtn.className = "px-2 py-1 text-slate-400 hover:text-white font-bold text-xs cursor-pointer";
                plusBtn.textContent = '+';
                plusBtn.title = "Ajouter un autre tapis (avec ses options)";

                piecesBox.appendChild(minusBtn);
                piecesBox.appendChild(piecesText);
                piecesBox.appendChild(plusBtn);

                qtyCtrl.appendChild(piecesBox);
            } else {
                // Non-kilo standard piece counter
                const countBox = document.createElement('div');
                countBox.className = "flex items-center bg-slate-800 rounded-md border border-slate-700/50";

                const minusBtn = document.createElement('button');
                minusBtn.onclick = () => {
                    const currentQty = parseFloat(item.quantity) || 1;
                    const newQty = Math.max(0, currentQty - 1);
                    updateItemQty(index, newQty);
                };
                minusBtn.className = "px-2 py-1 text-slate-400 hover:text-white font-bold text-xs cursor-pointer";
                minusBtn.textContent = '-';
                
                const qtyInput = document.createElement('input');
                qtyInput.type = "number";
                qtyInput.min = "1";
                qtyInput.step = "1";
                qtyInput.value = item.quantity;
                qtyInput.className = "w-10 bg-transparent text-center text-xs font-mono font-bold text-slate-200 focus:outline-none focus:bg-slate-900 focus:text-indigo-300 rounded";
                qtyInput.onchange = (e) => {
                    let val = parseFloat(e.target.value);
                    if (isNaN(val) || val <= 0) val = 1;
                    updateItemQty(index, val);
                };
                qtyInput.onclick = (e) => e.stopPropagation();
                
                const plusBtn = document.createElement('button');
                plusBtn.onclick = () => {
                    const currentQty = parseFloat(item.quantity) || 1;
                    updateItemQty(index, currentQty + 1);
                };
                plusBtn.className = "px-2 py-1 text-slate-400 hover:text-white font-bold text-xs cursor-pointer";
                plusBtn.textContent = '+';

                countBox.appendChild(minusBtn);
                countBox.appendChild(qtyInput);
                countBox.appendChild(plusBtn);

                qtyCtrl.appendChild(countBox);
            }

            // Options triggers
            const actionContainer = document.createElement('div');
            actionContainer.className = "flex items-center space-x-2.5";

            // Option details tags indicator (if any color/defect is chosen)
            const badgesCount = (item.colors ? item.colors.length : 0) + (item.defects ? item.defects.length : 0) + (item.stains ? item.stains.length : 0);
            if (badgesCount > 0) {
                const badge = document.createElement('span');
                badge.className = "text-[9px] bg-amber-500/20 text-amber-500 font-bold px-1.5 py-0.5 rounded-full";
                badge.textContent = `${badgesCount} options`;
                actionContainer.appendChild(badge);
            }

            const optionsBtn = document.createElement('button');
            optionsBtn.onclick = () => openOptionsModal(index);
            optionsBtn.className = "text-slate-400 hover:text-indigo-400 transition-colors p-1 bg-slate-800/80 hover:bg-slate-800 rounded-md border border-slate-700/50";
            optionsBtn.title = "Options (couleurs, défauts, taches, notes)";
            optionsBtn.innerHTML = `
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
            `;
            actionContainer.appendChild(optionsBtn);

            // Item pricing (double if express is active)
            const isExpress = document.getElementById('express-toggle-input')?.checked || false;
            const displayUnitPrice = isExpress ? (item.unit_price * 2) : item.unit_price;

            const pricingWrap = document.createElement('div');
            pricingWrap.className = "flex flex-col items-end";

            if (isKiloService) {
                const uPriceText = document.createElement('span');
                uPriceText.className = "text-[9px] text-slate-500 font-mono";
                uPriceText.textContent = `${displayUnitPrice.toFixed(0)} DA/kg`;
                pricingWrap.appendChild(uPriceText);
            } else if (item.is_carpet) {
                const uPriceText = document.createElement('span');
                uPriceText.className = "text-[9px] text-amber-400 font-mono";
                uPriceText.textContent = `${displayUnitPrice.toFixed(0)} DA/m²`;
                pricingWrap.appendChild(uPriceText);
            }

            const priceText = document.createElement('span');
            priceText.className = "text-xs font-bold font-mono";
            if (item.is_carpet && !item.is_measured) {
                priceText.className += " text-amber-400";
                priceText.textContent = "À mesurer";
            } else if (item.is_carpet && item.is_measured) {
                priceText.className += " text-emerald-400";
                priceText.textContent = `${(displayUnitPrice * item.area).toFixed(0)} DA`;
            } else {
                priceText.className += " text-slate-200";
                priceText.textContent = `${(displayUnitPrice * item.quantity).toFixed(0)} DA`;
            }
            pricingWrap.appendChild(priceText);

            actionContainer.appendChild(pricingWrap);

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
            const piecesPerItem = parseInt(cart[index].pieces_count) || 1;
            cart[index].pieces = Math.round(cart[index].quantity * piecesPerItem);
            renderCart();
            updateCartCalculations();
        }
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
        updateCartCalculations();
        closePaymentView();
    }

    function toggleExpressMode() {
        renderCart();
        updateCartCalculations();
    }

    function updateCartCalculations() {
        let totalBrut = 0;
        let totalKiloWeight = 0;
        let kiloPiecesCount = 0;
        let totalArticlesCount = 0;

        cart.forEach(item => {
            if (item.is_carpet && !item.is_measured) {
                // Not measured yet: doesn't add to subtotal
            } else if (item.is_carpet && item.is_measured) {
                totalBrut += item.unit_price * item.area;
            } else {
                totalBrut += item.unit_price * item.quantity;
            }
            const isKilo = item.service_id === 4 || (item.service_name && item.service_name.toLowerCase().includes('kilo'));
            if (isKilo) {
                totalKiloWeight += (item.weight !== undefined && item.weight !== null ? parseFloat(item.weight) : parseFloat(item.quantity));
                kiloPiecesCount += (item.pieces || 1);
                totalArticlesCount += (item.pieces || 1);
            } else if (item.is_carpet) {
                totalArticlesCount += (item.pieces || 1);
            } else {
                totalArticlesCount += (parseFloat(item.quantity) || 1);
            }
        });

        // Update Total Articles Count Badge
        const totalArticlesBadge = document.getElementById('cart-total-articles-count');
        if (totalArticlesBadge) {
            totalArticlesBadge.textContent = `${totalArticlesCount} ${totalArticlesCount > 1 ? 'articles' : 'article'}`;
        }

        // Update Kilo Weight Badges
        const kiloSummaryBar = document.getElementById('cart-kilo-summary-bar');
        const kiloBadgeCollapsed = document.getElementById('cart-kilo-weight-badge-collapsed');
        const kiloBadgeExpanded = document.getElementById('cart-kilo-weight-badge-expanded');

        if (totalKiloWeight > 0) {
            const grams = Math.round(totalKiloWeight * 1000);
            const weightFormatted = `${totalKiloWeight.toFixed(2)} kg (${grams} g)`;

            if (kiloSummaryBar) {
                kiloSummaryBar.classList.remove('hidden');
                document.getElementById('cart-kilo-total-weight-text').textContent = weightFormatted;
                document.getElementById('cart-kilo-items-count').textContent = `${kiloPiecesCount} pcs`;
            }
            if (kiloBadgeCollapsed) {
                kiloBadgeCollapsed.classList.remove('hidden');
                document.getElementById('total-weight-collapsed').textContent = `${totalKiloWeight.toFixed(2)} kg`;
            }
            if (kiloBadgeExpanded) {
                kiloBadgeExpanded.classList.remove('hidden');
                document.getElementById('total-weight-expanded').textContent = weightFormatted;
            }
        } else {
            if (kiloSummaryBar) kiloSummaryBar.classList.add('hidden');
            if (kiloBadgeCollapsed) kiloBadgeCollapsed.classList.add('hidden');
            if (kiloBadgeExpanded) kiloBadgeExpanded.classList.add('hidden');
        }

        const isExpress = document.getElementById('express-toggle-input')?.checked || false;
        if (isExpress) {
            totalBrut = totalBrut * 2;
        }

        let discountType = document.getElementById('discount-type-select').value;
        if (!IS_ADMIN) {
            discountType = 'fixed';
            document.getElementById('discount-type-select').value = 'fixed';
        }

        const discountInput = document.getElementById('discount-percent-input');

        // Dynamically compute default client discount as fixed DA if cashier and not manually overridden
        if (!IS_ADMIN && selectedClient && selectedClient.discount_percent > 0 && !isDiscountManuallyEdited) {
            discountInput.value = Math.round((totalBrut * selectedClient.discount_percent) / 100);
        }

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
        
        const netCollapsed = document.getElementById('total-net-collapsed');
        if (netCollapsed) netCollapsed.textContent = `${totalNet.toFixed(0)} DA`;
        
        const netExpanded = document.getElementById('total-net-expanded');
        if (netExpanded) netExpanded.textContent = `${totalNet.toFixed(0)} DA`;

        document.getElementById('remaining-balance').textContent = `${remainingBalance.toFixed(0)} DA`;

        // Update Rail Badge & Net Total
        const railBadge = document.getElementById('cart-rail-badge');
        if (railBadge) {
            const oldCount = parseInt(railBadge.textContent) || 0;
            railBadge.textContent = totalArticlesCount;
            if (isCartCollapsed && totalArticlesCount > oldCount) {
                railBadge.classList.remove('cart-badge-pulse');
                void railBadge.offsetWidth; // trigger reflow
                railBadge.classList.add('cart-badge-pulse');
            }
        }
        const railNetTotal = document.getElementById('cart-rail-net-total');
        if (railNetTotal) {
            railNetTotal.textContent = `${totalNet.toFixed(0)} DA`;
        }

        // Update Context Bar Header Badge & Label
        const headerBadge = document.getElementById('cart-toggle-header-badge');
        if (headerBadge) {
            headerBadge.textContent = totalArticlesCount;
        }
        const headerLabel = document.getElementById('cart-toggle-header-label');
        if (headerLabel) {
            headerLabel.textContent = isCartCollapsed && totalNet > 0 ? `${totalNet.toFixed(0)} DA` : 'Panier';
        }
    }

    // ================= DYNAMIC CART PANEL (COLLAPSE / EXPAND) =================
    let isCartCollapsed = localStorage.getItem('cart_collapsed') === 'true';

    window.toggleCartPanel = function(event = null) {
        if (event) {
            event.stopPropagation();
        }
        setCartCollapsedState(!isCartCollapsed);
    };

    window.openPaymentFromRail = function(event) {
        if (event) {
            event.stopPropagation();
        }
        if (isCartCollapsed) {
            setCartCollapsedState(false);
        }
        if (cart.length > 0) {
            openPaymentView();
        }
    };

    window.setCartCollapsedState = function(collapsed) {
        isCartCollapsed = !!collapsed;
        localStorage.setItem('cart_collapsed', isCartCollapsed ? 'true' : 'false');

        const panel = document.getElementById('checkout-cart-panel');
        const expandedContent = document.getElementById('cart-expanded-content');
        const collapsedRail = document.getElementById('cart-collapsed-rail');
        const headerArrow = document.getElementById('cart-toggle-header-arrow');
        const headerBtn = document.getElementById('cart-toggle-header-btn');
        const headerLabel = document.getElementById('cart-toggle-header-label');

        if (!panel || !expandedContent || !collapsedRail) return;

        if (isCartCollapsed) {
            // Collapse panel to sleek vertical rail (w-14)
            panel.classList.remove('w-96');
            panel.classList.add('w-14');
            expandedContent.classList.add('hidden');
            collapsedRail.classList.remove('hidden');
            collapsedRail.classList.add('flex');

            if (headerArrow) headerArrow.textContent = '◀';
            if (headerBtn) headerBtn.setAttribute('title', 'Afficher le panier (F4)');
        } else {
            // Expand panel to full cart view (w-96)
            panel.classList.remove('w-14');
            panel.classList.add('w-96');
            collapsedRail.classList.add('hidden');
            collapsedRail.classList.remove('flex');
            expandedContent.classList.remove('hidden');

            if (headerArrow) headerArrow.textContent = '▶';
            if (headerBtn) headerBtn.setAttribute('title', 'Réduire le panier sur le côté (F4)');
        }

        // Update header label text
        if (headerLabel) {
            const netCollapsed = document.getElementById('total-net-collapsed');
            const totalNetText = netCollapsed ? netCollapsed.textContent : '0 DA';
            headerLabel.textContent = isCartCollapsed && totalNetText !== '0 DA' ? totalNetText : 'Panier';
        }
    };

    // Keyboard shortcut F4 for toggling cart
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F4') {
            e.preventDefault();
            toggleCartPanel();
        }
    });

    window.openPaymentView = function() {
        updateCartCalculations();
        document.getElementById('billing-collapsed-view').classList.add('hidden');
        document.getElementById('billing-expanded-view').classList.remove('hidden');
    };

    window.closePaymentView = function() {
        document.getElementById('billing-expanded-view').classList.add('hidden');
        document.getElementById('billing-collapsed-view').classList.remove('hidden');
    };

    // ================= CLIENT SELECTION & SEARCH =================

    window.openClientSelectionModal = function() {
        document.getElementById('client-search-input').value = '';
        document.getElementById('client-select-results').innerHTML = '';
        document.getElementById('client-select-results').classList.add('hidden');
        
        const emptyNotice = document.getElementById('client-select-empty');
        emptyNotice.classList.remove('hidden');
        emptyNotice.textContent = "Saisissez au moins 2 caractères pour rechercher un client.";

        document.getElementById('client-select-modal').classList.remove('hidden');
        document.getElementById('client-search-input').focus();
    };

    window.closeClientSelectionModal = function() {
        document.getElementById('client-select-modal').classList.add('hidden');
    };

    window.triggerNewClientFromSelect = function() {
        closeClientSelectionModal();
        openNewClientModal();
    };

    function searchClients(term) {
        const resultsBox = document.getElementById('client-select-results');
        const emptyNotice = document.getElementById('client-select-empty');
        
        if (term.trim().length < 2) {
            resultsBox.innerHTML = '';
            resultsBox.classList.add('hidden');
            emptyNotice.classList.remove('hidden');
            emptyNotice.textContent = "Saisissez au moins 2 caractères pour rechercher un client.";
            return;
        }

        fetch(`/api/clients/search?q=${encodeURIComponent(term)}`)
            .then(res => res.json())
            .then(clients => {
                resultsBox.innerHTML = '';
                if (clients.length === 0) {
                    resultsBox.classList.add('hidden');
                    emptyNotice.classList.remove('hidden');
                    emptyNotice.textContent = "Aucun client trouvé pour ce terme.";
                    return;
                }

                emptyNotice.classList.add('hidden');
                clients.forEach(client => {
                    const row = document.createElement('button');
                    row.type = "button";
                    row.className = "w-full text-left px-4 py-3 hover:bg-slate-800 flex flex-col text-xs border-b border-slate-700/50 cursor-pointer transition-colors";
                    row.onclick = (e) => {
                        e.preventDefault();
                        selectClient(client);
                    };

                    const nameText = document.createElement('span');
                    nameText.className = "font-bold text-slate-200 text-sm";
                    nameText.textContent = client.name;

                    const meta = document.createElement('span');
                    meta.className = "text-[11px] text-slate-400 mt-1";
                    meta.textContent = `Code: ${client.code} | Tél: ${client.phone || '-'} | Remise: ${client.discount_percent}%`;

                    row.appendChild(nameText);
                    row.appendChild(meta);
                    resultsBox.appendChild(row);
                });
                resultsBox.classList.remove('hidden');
            });
    }

    let isDiscountManuallyEdited = false;

    function selectClient(client) {
        selectedClient = { ...client };
        isDiscountManuallyEdited = false;
        
        if (IS_ADMIN) {
            document.getElementById('discount-type-select').value = 'percent';
            document.getElementById('discount-percent-input').value = client.discount_percent;
        } else {
            document.getElementById('discount-type-select').value = 'fixed';
            let totalBrut = 0;
            cart.forEach(item => { totalBrut += item.unit_price * item.quantity; });
            if (document.getElementById('express-toggle-input')?.checked) { totalBrut *= 2; }
            document.getElementById('discount-percent-input').value = Math.round((totalBrut * client.discount_percent) / 100);
        }
        
        renderSelectedClient();
        updateCartCalculations();
        closeClientSelectionModal();
    }

    function clearSelectedClient() {
        selectedClient = { ...defaultClient };
        isDiscountManuallyEdited = false;
        
        if (IS_ADMIN) {
            document.getElementById('discount-type-select').value = 'percent';
            document.getElementById('discount-percent-input').value = defaultClient.discount_percent;
        } else {
            document.getElementById('discount-type-select').value = 'fixed';
            document.getElementById('discount-percent-input').value = 0;
        }
        
        renderSelectedClient();
        updateCartCalculations();
        setPricingMode('detail');
    }

    function changeDiscountType() {
        if (!IS_ADMIN) {
            document.getElementById('discount-type-select').value = 'fixed';
        }
        document.getElementById('discount-percent-input').value = 0;
        updateCartCalculations();
    }

    function updateCustomDiscount() {
        isDiscountManuallyEdited = true;
        updateCartCalculations();
    }

    function renderSelectedClient() {
        // Update Left Context Bar displays
        document.getElementById('selected-client-name-display').textContent = selectedClient.name;
        document.getElementById('selected-client-discount-badge').textContent = `Remise: ${selectedClient.discount_percent}%`;

        const clearBtn = document.getElementById('client-clear-btn-display');
        if (selectedClient.code !== 'GUEST') {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    // ================= CUSTOM OPTIONS MODAL =================

    function updateOptionBadgeState(btn, isActive) {
        const isColor = btn.getAttribute('data-color-btn') === 'true';
        
        if (isColor) {
            if (isActive) {
                btn.classList.add('ring-4', 'ring-white', 'scale-105', 'shadow-lg', 'shadow-black/60');
                btn.style.borderColor = '#ffffff';
            } else {
                btn.classList.remove('ring-4', 'ring-white', 'scale-105', 'shadow-lg', 'shadow-black/60');
                btn.style.borderColor = btn.getAttribute('data-border') || '#334155';
            }
        } else {
            if (isActive) {
                btn.classList.remove('bg-slate-900', 'text-slate-300', 'border-slate-700');
                btn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-500');
            } else {
                btn.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-500');
                btn.classList.add('bg-slate-900', 'text-slate-300', 'border-slate-700');
            }
        }
    }

    function openOptionsModal(indexOrItem, price = null) {
        const knownPatterns = @json($patterns);
        
        if (price !== null) {
            // Case A: Adding new item
            isAddingNewItem = true;
            pendingItem = indexOrItem;
            pendingPrice = price;
            currentOptionsIndex = null;

            currentOptions = {
                colors: [],
                patterns: [],
                defects: [],
                stains: [],
                notes: ''
            };

            const currentService = allServices.find(s => s.id === selectedServiceId);
            const sName = currentService ? currentService.name : 'Service';
            document.getElementById('options-modal-item-name').textContent = `${sName} > ${pendingItem.name}`;
            document.getElementById('modal-notes-input').value = '';
        } else {
            // Case B: Editing existing item in cart
            isAddingNewItem = false;
            currentOptionsIndex = indexOrItem;
            const item = cart[currentOptionsIndex];
            
            const itemColors = [];
            const itemPatterns = [];
            (item.colors || []).forEach(val => {
                if (knownPatterns.includes(val)) {
                    itemPatterns.push(val);
                } else {
                    itemColors.push(val);
                }
            });

            currentOptions = {
                colors: itemColors,
                patterns: itemPatterns,
                defects: [...item.defects],
                stains: [...item.stains],
                notes: item.notes
            };

            document.getElementById('options-modal-item-name').textContent = `${item.service_name} > ${item.name}`;
            document.getElementById('modal-notes-input').value = currentOptions.notes;
        }

        // Reset badge active classes
        document.querySelectorAll('#options-modal .option-badge').forEach(badge => {
            const badgeText = badge.textContent.trim();
            const isActive = currentOptions.colors.includes(badgeText) || 
                             currentOptions.patterns.includes(badgeText) || 
                             currentOptions.defects.includes(badgeText) || 
                             currentOptions.stains.includes(badgeText);

            updateOptionBadgeState(badge, isActive);
        });

        document.getElementById('options-modal').classList.remove('hidden');
    }

    function closeOptionsModal() {
        document.getElementById('options-modal').classList.add('hidden');
        currentOptionsIndex = null;
        pendingItem = null;
        pendingPrice = 0;
        isAddingNewItem = false;
    }

    function toggleItemOption(type, value, btn) {
        const index = currentOptions[type].indexOf(value);

        if (index === -1) {
            currentOptions[type].push(value);
            updateOptionBadgeState(btn, true);
        } else {
            currentOptions[type].splice(index, 1);
            updateOptionBadgeState(btn, false);
        }
    }

    function saveItemOptions() {
        if (isAddingNewItem) {
            const colorsArray = [...currentOptions.colors, ...currentOptions.patterns];
            const defectsArray = [...currentOptions.defects];
            const stainsArray = [...currentOptions.stains];
            const notesText = document.getElementById('modal-notes-input').value.trim();

            // Always add as a new independent item in the cart
            const currentService = allServices.find(s => s.id === selectedServiceId);
            const sName = currentService ? currentService.name : 'Service';

            const isCarpet = (pendingItem.is_carpet) || (pendingItem.unit_type === 'm2') || 
                             (pendingItem.name && (pendingItem.name.toLowerCase().includes('tapis') || pendingItem.name.toLowerCase().includes('m²')));
            const isKiloService = currentService && (currentService.code === 'au_kilo' || currentService.name.toLowerCase().includes('kilo') || selectedServiceId === 4);
            const stdWeightG = pendingItem.standard_weight ? parseFloat(pendingItem.standard_weight) : 1000;
            const stdWeightKg = stdWeightG / 1000;

            if (isCarpet) {
                cart.push({
                    id: pendingItem.id,
                    name: pendingItem.name,
                    service_id: selectedServiceId,
                    service_name: sName,
                    is_carpet: true,
                    is_measured: false,
                    pieces: 1,
                    length: null,
                    width: null,
                    area: null,
                    quantity: 1,
                    unit_price: pendingPrice,
                    total_price: 0,
                    colors: colorsArray,
                    defects: defectsArray,
                    stains: stainsArray,
                    notes: notesText
                });
            } else if (isKiloService) {
                cart.push({
                    id: pendingItem.id,
                    name: pendingItem.name,
                    service_id: selectedServiceId,
                    service_name: sName,
                    standard_weight: stdWeightG,
                    pieces: 1,
                    weight: stdWeightKg,
                    quantity: stdWeightKg,
                    unit_price: pendingPrice,
                    colors: colorsArray,
                    defects: defectsArray,
                    stains: stainsArray,
                    notes: notesText
                });
            } else {
                const piecesPerItem = parseInt(pendingItem.pieces_count) || 1;
                cart.push({
                    id: pendingItem.id,
                    name: pendingItem.name,
                    service_id: selectedServiceId,
                    service_name: sName,
                    pieces_count: piecesPerItem,
                    quantity: 1,
                    pieces: piecesPerItem,
                    unit_price: pendingPrice,
                    colors: colorsArray,
                    defects: defectsArray,
                    stains: stainsArray,
                    notes: notesText
                });
            }

            renderCart();
            updateCartCalculations();
            closePaymentView();
            closeOptionsModal();
        } else if (currentOptionsIndex !== null) {
            // Case B: Edit existing item
            cart[currentOptionsIndex].colors = [...currentOptions.colors, ...currentOptions.patterns];
            cart[currentOptionsIndex].defects = [...currentOptions.defects];
            cart[currentOptionsIndex].stains = [...currentOptions.stains];
            cart[currentOptionsIndex].notes = document.getElementById('modal-notes-input').value.trim();
            
            renderCart();
            updateCartCalculations();
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
        let orderTotalWeight = 0;
        const payloadItems = [];
        cart.forEach(item => {
            const isKilo = item.service_id === 4 || (item.service_name && item.service_name.toLowerCase().includes('kilo'));
            if (isKilo) {
                orderTotalWeight += (item.weight !== undefined && item.weight !== null ? parseFloat(item.weight) : parseFloat(item.quantity));
            }
            if (item.is_carpet) {
                const carpetPieces = parseInt(item.pieces) || 1;
                for (let p = 0; p < carpetPieces; p++) {
                    payloadItems.push({
                        service_id: item.service_id,
                        garment_item_id: item.id,
                        pieces: 1,
                        weight: null,
                        length: p === 0 ? item.length : null,
                        width: p === 0 ? item.width : null,
                        area: p === 0 ? item.area : null,
                        is_measured: p === 0 ? !!item.is_measured : false,
                        quantity: 1,
                        unit_price: item.unit_price,
                        colors: item.colors || [],
                        defects: item.defects || [],
                        stains: item.stains || [],
                        notes: item.notes || null
                    });
                }
            } else {
                const piecesPerItem = parseInt(item.pieces_count) || 1;
                const totalPieces = item.pieces ? parseInt(item.pieces) : Math.round(piecesPerItem * (parseFloat(item.quantity) || 1));
                payloadItems.push({
                    service_id: item.service_id,
                    garment_item_id: item.id,
                    pieces: totalPieces,
                    weight: item.weight !== undefined && item.weight !== null ? parseFloat(item.weight) : null,
                    length: null,
                    width: null,
                    area: null,
                    is_measured: false,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    colors: item.colors || [],
                    defects: item.defects || [],
                    stains: item.stains || [],
                    notes: item.notes || null
                });
            }
        });

        const discountType = document.getElementById('discount-type-select').value;
        const discountValue = parseFloat(document.getElementById('discount-percent-input').value) || 0;

        const isExpress = document.getElementById('express-toggle-input').checked;
        const noPrint = document.getElementById('no-print-toggle').checked;

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
            total_weight: orderTotalWeight > 0 ? parseFloat(orderTotalWeight.toFixed(3)) : null,
            items: payloadItems
        };

        const url = editingOrder ? `/orders/${editingOrder.id}/update` : '/orders';

        // Submit via AJAX
        fetch(url, {
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
                // Lancer l'impression complète si l'option n'est pas cochée
                if (!noPrint) {
                    printOrder(data.order_id, 'all');
                }

                const alertMsg = editingOrder ? `Ticket N° ${data.ticket_number} modifié avec succès !` : `Ticket N° ${data.ticket_number} enregistré avec succès !`;
                const alertTitle = editingOrder ? "Commande Modifiée" : "Commande Enregistrée";

                showAppAlert(alertMsg, "success", alertTitle, () => {
                    if (editingOrder) {
                        window.location.href = "{{ route('orders.index') }}";
                    }
                });
                
                if (!editingOrder) {
                    // Clear cart
                    cart = [];
                    // Reset express checkbox
                    document.getElementById('express-toggle-input').checked = false;
                    document.getElementById('no-print-toggle').checked = false;
                    renderCart();
                    clearSelectedClient();
                    
                    // Refresh next ticket number
                    const nextNo = String(parseInt(data.ticket_number) + 1).padStart(6, '0');
                    document.getElementById('ticket-number-input').value = nextNo;
                    document.getElementById('remarks-input').value = '';
                    document.getElementById('paid-amount-input').value = 0;
                    
                    updateCartCalculations();
                }
            } else {
                showAppAlert(`Erreur : ${data.message}`, "error", "Erreur");
            }
        })
        .catch(err => {
            // Sauvegarde automatique en mode secours hors-ligne si réseau coupé
            if (!navigator.onLine || err.message.includes('Failed to fetch') || err.message.includes('NetworkError')) {
                const offlineUuid = 'offline-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                const offlineOrder = {
                    uuid: offlineUuid,
                    ...body,
                    is_offline: true,
                    created_at: new Date().toISOString()
                };
                
                const queue = JSON.parse(localStorage.getItem('pos_pending_offline_orders') || '[]');
                queue.push(offlineOrder);
                localStorage.setItem('pos_pending_offline_orders', JSON.stringify(queue));

                showAppAlert(`Réseau interrompu : le ticket N° ${body.ticket_number} a été sécurisé localement en MODE SECOURS. Il sera synchronisé automatiquement avec le Cloud dès la reconnexion.`, "info", "Mode Secours Hors-Ligne");

                if (!editingOrder) {
                    cart = [];
                    document.getElementById('express-toggle-input').checked = false;
                    document.getElementById('no-print-toggle').checked = false;
                    renderCart();
                    clearSelectedClient();
                    const nextNo = String(parseInt(body.ticket_number) + 1).padStart(6, '0');
                    document.getElementById('ticket-number-input').value = nextNo;
                    document.getElementById('remarks-input').value = '';
                    document.getElementById('paid-amount-input').value = 0;
                    updateCartCalculations();
                }
                return;
            }
            showAppAlert(editingOrder ? "Erreur lors de la modification de la commande." : "Erreur lors de l'enregistrement de la commande.", "error", "Erreur");
        });
    }

    // Synchronisation automatique des commandes hors-ligne en arrière-plan
    function syncOfflineOrdersIfAny() {
        if (!navigator.onLine) return;
        const queue = JSON.parse(localStorage.getItem('pos_pending_offline_orders') || '[]');
        if (queue.length === 0) return;

        fetch('/api/pos/sync/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                terminal_code: 'POS-DESKTOP',
                orders: queue
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                localStorage.removeItem('pos_pending_offline_orders');
                console.log('Commandes hors-ligne synchronisées avec succès vers le Cloud !');
            }
        })
        .catch(e => console.warn('Synchro en attente:', e));
    }

    window.addEventListener('online', syncOfflineOrdersIfAny);
    setInterval(syncOfflineOrdersIfAny, 30000);
    document.addEventListener('DOMContentLoaded', syncOfflineOrdersIfAny);

    // ================= MODAL ENCAISSEMENT TACTILE (CALCULATRICE / PAVÉ NUMÉRIQUE) =================
    let cpayCurrentAmount = 0;
    let cpayTotalNet = 0;

    window.openCheckoutPaymentModal = function() {
        if (!selectedClient && typeof defaultClient !== 'undefined') {
            selectedClient = { ...defaultClient };
        }

        updateCartCalculations();

        // Calculate total net
        let totalBrut = 0;
        cart.forEach(item => {
            if (item.is_carpet && !item.is_measured) {
                // Not measured
            } else if (item.is_carpet && item.is_measured) {
                totalBrut += item.unit_price * item.area;
            } else {
                totalBrut += item.unit_price * item.quantity;
            }
        });

        const isExpress = document.getElementById('express-toggle-input')?.checked || false;
        if (isExpress) totalBrut = totalBrut * 2;

        const discountInput = document.getElementById('discount-percent-input');
        const discountType = document.getElementById('discount-type-select')?.value || 'fixed';
        let discountValue = parseFloat(discountInput?.value) || 0;
        let discountAmount = 0;
        if (discountType === 'percent') {
            discountAmount = totalBrut * (discountValue / 100);
        } else {
            discountAmount = discountValue;
        }

        cpayTotalNet = Math.max(0, Math.round(totalBrut - discountAmount));

        // Prefill with existing paid-amount-input value, if any, else default to 0
        const existingPaid = parseFloat(document.getElementById('paid-amount-input')?.value) || 0;
        cpayCurrentAmount = existingPaid;

        // Set labels
        const ticketNum = document.getElementById('ticket-number-input')?.value || '{{ $nextTicketNumber }}';
        const clientName = selectedClient ? selectedClient.name : 'Client Passage';
        const ticketBadge = document.getElementById('cpay-ticket-badge');
        const clientNameElem = document.getElementById('cpay-client-name');
        const totalNetElem = document.getElementById('cpay-total-net');
        
        if (ticketBadge) ticketBadge.textContent = `#${ticketNum}`;
        if (clientNameElem) clientNameElem.textContent = clientName;
        if (totalNetElem) totalNetElem.textContent = `${cpayTotalNet} DA`;

        cpayRefreshDisplay();

        const modal = document.getElementById('checkout-payment-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.setProperty('display', 'flex', 'important');
        }
    };

    window.closeCheckoutPaymentModal = function() {
        const modal = document.getElementById('checkout-payment-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.setProperty('display', 'none', 'important');
        }
    };

    window.cpayPressKey = function(digit) {
        let str = cpayCurrentAmount.toString();
        if (str === '0') {
            str = digit.toString();
        } else {
            if (str.length < 9) {
                str += digit.toString();
            }
        }
        cpayCurrentAmount = parseInt(str) || 0;
        cpayRefreshDisplay();
    };

    window.cpayBackspace = function() {
        let str = cpayCurrentAmount.toString();
        if (str.length <= 1) {
            cpayCurrentAmount = 0;
        } else {
            str = str.slice(0, -1);
            cpayCurrentAmount = parseInt(str) || 0;
        }
        cpayRefreshDisplay();
    };

    window.cpayClear = function() {
        cpayCurrentAmount = 0;
        cpayRefreshDisplay();
    };

    window.cpaySetPreset = function(preset) {
        if (preset === 'exact') {
            cpayCurrentAmount = cpayTotalNet;
        } else {
            cpayCurrentAmount = parseInt(preset) || 0;
        }
        cpayRefreshDisplay();
    };

    window.cpayAddAmount = function(amount) {
        cpayCurrentAmount = (cpayCurrentAmount || 0) + amount;
        cpayRefreshDisplay();
    };

    function cpayRefreshDisplay() {
        const inputElem = document.getElementById('cpay-input-value');
        if (inputElem) {
            inputElem.value = cpayCurrentAmount;
        }
        const paidDisplay = document.getElementById('cpay-paid-display');
        if (paidDisplay) paidDisplay.textContent = `${cpayCurrentAmount} DA`;

        const diff = cpayTotalNet - cpayCurrentAmount;
        const changeBox = document.getElementById('cpay-change-box');
        const partialBox = document.getElementById('cpay-partial-box');
        const fullBox = document.getElementById('cpay-full-box');
        const balanceElem = document.getElementById('cpay-balance-display');
        const submitLabel = document.getElementById('cpay-submit-btn-label');

        if (diff > 0) {
            // Remaining balance
            if (balanceElem) {
                balanceElem.textContent = `${diff} DA`;
                balanceElem.className = 'text-sm font-black text-amber-400 font-mono';
            }
            if (partialBox) {
                partialBox.classList.remove('hidden');
                const pVal = document.getElementById('cpay-partial-val');
                if (pVal) pVal.textContent = `${diff} DA`;
            }
            if (changeBox) changeBox.classList.add('hidden');
            if (fullBox) fullBox.classList.add('hidden');

            if (submitLabel) {
                if (cpayCurrentAmount > 0) {
                    submitLabel.textContent = `Valider avec acompte de ${cpayCurrentAmount} DA (Solde: ${diff} DA)`;
                } else {
                    submitLabel.textContent = `Valider sans acompte (Solde: ${diff} DA)`;
                }
            }
        } else if (diff === 0) {
            // Fully paid
            if (balanceElem) {
                balanceElem.textContent = `0 DA`;
                balanceElem.className = 'text-sm font-black text-emerald-400 font-mono';
            }
            if (partialBox) partialBox.classList.add('hidden');
            if (changeBox) changeBox.classList.add('hidden');
            if (fullBox) fullBox.classList.remove('hidden');
            if (submitLabel) submitLabel.textContent = `Valider & Imprimer (Totalité ${cpayTotalNet} DA)`;
        } else {
            // Overpaid (change to return)
            const change = Math.abs(diff);
            if (balanceElem) {
                balanceElem.textContent = `0 DA`;
                balanceElem.className = 'text-sm font-black text-emerald-400 font-mono';
            }
            if (partialBox) partialBox.classList.add('hidden');
            if (fullBox) fullBox.classList.add('hidden');
            if (changeBox) {
                changeBox.classList.remove('hidden');
                const cVal = document.getElementById('cpay-change-val');
                if (cVal) cVal.textContent = `${change} DA`;
            }
            if (submitLabel) submitLabel.textContent = `Valider & Imprimer (Rendre: ${change} DA)`;
        }
    }

    window.cpayConfirmAndSubmit = function() {
        if (!cart || cart.length === 0) {
            showAppAlert("Le panier est vide. Veuillez ajouter des articles avant de valider la commande.", "error", "Panier Vide");
            return;
        }

        const clientId = selectedClient ? selectedClient.id : null;
        if (!clientId) {
            showAppAlert("Veuillez associer un client (ex: Client Passage ou client recherché).", "error", "Client manquant");
            return;
        }

        // Store actual payment up to net total (since any surplus is returned as change)
        const actualPaidToStore = Math.min(cpayCurrentAmount, cpayTotalNet);
        const paidInput = document.getElementById('paid-amount-input');
        if (paidInput) {
            paidInput.value = actualPaidToStore;
        }

        updateCartCalculations();
        closeCheckoutPaymentModal();

        // Submit order via AJAX & automatically trigger dual-printing
        submitOrder();
    };

    // Keyboard support for physical numpad
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('checkout-payment-modal');
        if (!modal || modal.classList.contains('hidden') || modal.style.display === 'none') {
            return;
        }
        if (e.key >= '0' && e.key <= '9') {
            e.preventDefault();
            cpayPressKey(e.key);
        } else if (e.key === 'Backspace') {
            e.preventDefault();
            cpayBackspace();
        } else if (e.key === 'Escape') {
            e.preventDefault();
            closeCheckoutPaymentModal();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            cpayConfirmAndSubmit();
        }
    });

</script>
@endsection
