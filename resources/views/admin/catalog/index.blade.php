@extends('layouts.app')

@section('title', 'Gestion Articles & Référentiels')

@section('styles')
<style>
    .catalog-card {
        background-color: rgba(30, 41, 59, 0.4);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(71, 85, 105, 0.2);
    }
    .tab-btn-active {
        background-color: #4f46e5 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.2);
    }
</style>
@endsection

@section('content')
@php
    // Detect active main tab from request parameter, default to 'items'
    $activeMainTab = request()->query('tab', 'items');
    if (!in_array($activeMainTab, ['items', 'targets', 'services'])) {
        $activeMainTab = 'items';
    }
@endphp

<div class="flex-1 flex flex-col min-w-0 bg-slate-900 overflow-hidden">
    <!-- Header panel -->
    <div class="bg-slate-800/40 p-5 border-b border-slate-700/50 shrink-0 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div>
            <h2 class="text-xl font-bold font-display text-white">Gestion du Référentiel & Tarifs</h2>
            <p class="text-xs text-slate-400">Gérer les articles du catalogue, les catégories d'habits et les services</p>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Add Item button (shown in Items tab) -->
            <button onclick="openAddCatalogModal()" id="btn-add-item"
                    class="main-add-btn px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold font-display rounded-lg shadow-lg shadow-indigo-600/10 transition-colors flex items-center space-x-1.5 cursor-pointer {{ $activeMainTab === 'items' ? '' : 'hidden' }}">
                <span>+ Nouvel Article</span>
            </button>

            <!-- Add Target button (shown in Targets tab) -->
            <button onclick="openAddTargetModal()" id="btn-add-target"
                    class="main-add-btn px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold font-display rounded-lg shadow-lg shadow-indigo-600/10 transition-colors flex items-center space-x-1.5 cursor-pointer {{ $activeMainTab === 'targets' ? '' : 'hidden' }}">
                <span>+ Nouvelle Catégorie</span>
            </button>

            <!-- Add Service button (shown in Services tab) -->
            <button onclick="openAddServiceModal()" id="btn-add-service"
                    class="main-add-btn px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold font-display rounded-lg shadow-lg shadow-indigo-600/10 transition-colors flex items-center space-x-1.5 cursor-pointer {{ $activeMainTab === 'services' ? '' : 'hidden' }}">
                <span>+ Nouveau Service</span>
            </button>
        </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="bg-slate-900 border-b border-slate-800 px-6 py-3 shrink-0 flex items-center space-x-2 overflow-x-auto">
        <button onclick="switchMainTab('items')" id="main-tab-btn-items"
                class="main-tab-btn px-4 py-1.5 rounded-full text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer {{ $activeMainTab === 'items' ? 'tab-btn-active' : '' }}">
            Articles & Tarifs
        </button>
        <button onclick="switchMainTab('targets')" id="main-tab-btn-targets"
                class="main-tab-btn px-4 py-1.5 rounded-full text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer {{ $activeMainTab === 'targets' ? 'tab-btn-active' : '' }}">
            Catégories
        </button>
        <button onclick="switchMainTab('services')" id="main-tab-btn-services"
                class="main-tab-btn px-4 py-1.5 rounded-full text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer {{ $activeMainTab === 'services' ? 'tab-btn-active' : '' }}">
            Services
        </button>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 overflow-y-auto p-6 space-y-4">
        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold rounded-xl space-y-1">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Tab 1: Articles & Prices -->
        <div id="main-tab-content-items" class="main-tab-content space-y-4 {{ $activeMainTab === 'items' ? '' : 'hidden' }}">
            <!-- Sub-navigation Tabs (Targets/Categories Filter) -->
            <div class="flex items-center space-x-2 overflow-x-auto pb-2 border-b border-slate-800">
                <button onclick="filterTarget('all')" id="target-tab-all"
                        class="target-tab px-3 py-1 rounded-lg text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer tab-btn-active">
                    Tous
                </button>
                @foreach($targets as $target)
                    <button onclick="filterTarget('{{ $target->id }}')" id="target-tab-{{ $target->id }}"
                            class="target-tab px-3 py-1 rounded-lg text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer">
                        {{ $target->name }}
                    </button>
                @endforeach
            </div>

            <!-- Table -->
            <div class="catalog-card rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-800/20">
                                <th class="py-3.5 px-6">Nom de l'article</th>
                                <th class="py-3.5 px-4">Cible</th>
                                @foreach($services as $service)
                                    <th class="py-3.5 px-3 text-center font-display font-medium">{{ $service->name }}</th>
                                @endforeach
                                <th class="py-3.5 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50 text-xs text-slate-300">
                            @foreach($items as $item)
                                @php
                                    $itemPrices = [];
                                    foreach($item->servicePrices as $sp) {
                                        $itemPrices[$sp->service_id] = $sp->price;
                                    }
                                @endphp
                                <tr class="hover:bg-slate-800/10 transition-colors catalog-row" data-target-id="{{ $item->garment_target_id }}">
                                    <td class="py-3 px-6 font-bold text-slate-100 uppercase">
                                        {{ $item->name }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-800 text-slate-300 border border-slate-700/60 uppercase">
                                            {{ $item->garmentTarget ? $item->garmentTarget->name : '-' }}
                                        </span>
                                    </td>
                                    @foreach($services as $service)
                                        @php
                                            $price = $itemPrices[$service->id] ?? null;
                                        @endphp
                                        <td class="py-3 px-3 text-center font-mono text-slate-300">
                                            @if($price !== null)
                                                <span class="text-indigo-400 font-bold">{{ number_format($price, 0) }}</span> <span class="text-[9px] text-slate-500">DA</span>
                                            @else
                                                <span class="text-slate-600">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="py-3 px-6 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button onclick='openEditCatalogModal(@json($item), @json($itemPrices))' 
                                                    class="bg-slate-800 hover:bg-slate-700 text-indigo-400 p-1.5 rounded-lg border border-slate-700 cursor-pointer transition-colors" 
                                                    title="Modifier">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            <form action="{{ route('admin.catalog.item.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article du catalogue ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="bg-slate-800 hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 p-1.5 rounded-lg border border-slate-700 hover:border-rose-500/20 cursor-pointer transition-colors" 
                                                        title="Supprimer">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 2: Targets (Categories Cibles) -->
        <div id="main-tab-content-targets" class="main-tab-content space-y-4 {{ $activeMainTab === 'targets' ? '' : 'hidden' }}">
            <div class="catalog-card rounded-2xl overflow-hidden shadow-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-800/20">
                            <th class="py-3.5 px-6">ID</th>
                            <th class="py-3.5 px-6">Nom de la catégorie cible</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-xs text-slate-300">
                        @foreach($targets as $target)
                            <tr class="hover:bg-slate-800/10 transition-colors">
                                <td class="py-3 px-6 font-mono text-slate-400">{{ $target->id }}</td>
                                <td class="py-3 px-6 font-bold text-slate-100 uppercase">{{ $target->name }}</td>
                                <td class="py-3 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button onclick='openEditTargetModal(@json($target))' 
                                                class="bg-slate-800 hover:bg-slate-700 text-indigo-400 p-1.5 rounded-lg border border-slate-700 cursor-pointer transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <form action="{{ route('admin.catalog.target.destroy', $target->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Les articles associés resteront dans le catalogue sans catégorie.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-slate-800 hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 p-1.5 rounded-lg border border-slate-700 hover:border-rose-500/20 cursor-pointer transition-colors">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Services -->
        <div id="main-tab-content-services" class="main-tab-content space-y-4 {{ $activeMainTab === 'services' ? '' : 'hidden' }}">
            <div class="catalog-card rounded-2xl overflow-hidden shadow-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-800/20">
                            <th class="py-3.5 px-6">ID</th>
                            <th class="py-3.5 px-6">Nom du Service</th>
                            <th class="py-3.5 px-6">Code Unique</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-xs text-slate-300">
                        @foreach($services as $service)
                            <tr class="hover:bg-slate-800/10 transition-colors">
                                <td class="py-3 px-6 font-mono text-slate-400">{{ $service->id }}</td>
                                <td class="py-3 px-6 font-bold text-slate-100 uppercase">{{ $service->name }}</td>
                                <td class="py-3 px-6 font-mono text-slate-400">{{ $service->code }}</td>
                                <td class="py-3 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button onclick='openEditServiceModal(@json($service))' 
                                                class="bg-slate-800 hover:bg-slate-700 text-indigo-400 p-1.5 rounded-lg border border-slate-700 cursor-pointer transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <form action="{{ route('admin.catalog.service.destroy', $service->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce service ? Tous les tarifs associés dans la base de données seront effacés.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-slate-800 hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 p-1.5 rounded-lg border border-slate-700 hover:border-rose-500/20 cursor-pointer transition-colors">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALS OVERLAYS ================= -->

<!-- 1. Catalog Modal (Add & Edit Item) -->
<div id="catalog-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-lg flex flex-col shadow-2xl overflow-hidden transform scale-95 transition-all">
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <h3 id="modal-title" class="text-base font-bold text-white font-display">Nouvel Article</h3>
            <button onclick="closeCatalogModal()" class="text-slate-400 hover:text-white cursor-pointer">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="catalog-form" method="POST" class="p-6 space-y-4 overflow-y-auto max-h-[75vh]">
            @csrf
            <div id="form-method-container"></div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nom de l'article *</label>
                <input type="text" id="catalog-item-name" name="name" required placeholder="Ex: Chemise classique, Doudoune, ..." 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Public Cible (Catégorie cible) *</label>
                <select id="catalog-item-target" name="garment_target_id" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Sélectionner la cible...</option>
                    @foreach($targets as $target)
                        <option value="{{ $target->id }}">{{ $target->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-2">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider border-b border-slate-700/60 pb-1.5 mb-3">Grille Tarifaire (DA)</h4>
                <div class="grid grid-cols-2 gap-4">
                    @foreach($services as $service)
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase mb-1">{{ $service->name }}</label>
                            <input type="number" id="catalog-price-{{ $service->id }}" name="prices[{{ $service->id }}]" min="0" placeholder="-" 
                                   class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 font-mono">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-700/50">
                <button type="button" onclick="closeCatalogModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors cursor-pointer">Annuler</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors cursor-pointer">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Target Modal (Add & Edit Category) -->
<div id="target-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-sm flex flex-col shadow-2xl overflow-hidden transform scale-95 transition-all">
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <h3 id="target-modal-title" class="text-base font-bold text-white font-display">Nouvelle Catégorie</h3>
            <button onclick="closeTargetModal()" class="text-slate-400 hover:text-white cursor-pointer">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="target-form" method="POST" class="p-6 space-y-4">
            @csrf
            <div id="target-form-method-container"></div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nom de la catégorie *</label>
                <input type="text" id="target-name" name="name" required placeholder="Ex: Enfant, Rideaux, Tapis, ..." 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-700/50">
                <button type="button" onclick="closeTargetModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors cursor-pointer">Annuler</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors cursor-pointer">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Service Modal (Add & Edit Service) -->
<div id="service-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-sm flex flex-col shadow-2xl overflow-hidden transform scale-95 transition-all">
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <h3 id="service-modal-title" class="text-base font-bold text-white font-display">Nouveau Service</h3>
            <button onclick="closeServiceModal()" class="text-slate-400 hover:text-white cursor-pointer">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="service-form" method="POST" class="p-6 space-y-4">
            @csrf
            <div id="service-form-method-container"></div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nom du service *</label>
                <input type="text" id="service-name" name="name" required placeholder="Ex: Repassage rapide, Retouches, ..." 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Code unique (sans espace) *</label>
                <input type="text" id="service-code" name="code" required placeholder="Ex: repassage_rapide, retouches" 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 font-mono">
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-700/50">
                <button type="button" onclick="closeServiceModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors cursor-pointer">Annuler</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors cursor-pointer">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let activeFilter = 'all';

    // 1. Tab Navigation logic
    window.switchMainTab = function(tabId) {
        // Toggle tab button active state
        document.querySelectorAll('.main-tab-btn').forEach(btn => {
            btn.classList.remove('tab-btn-active');
        });
        const activeBtn = document.getElementById(`main-tab-btn-${tabId}`);
        if(activeBtn) activeBtn.classList.add('tab-btn-active');

        // Toggle main content sections
        document.querySelectorAll('.main-tab-content').forEach(section => {
            section.classList.add('hidden');
        });
        const activeSection = document.getElementById(`main-tab-content-${tabId}`);
        if(activeSection) activeSection.classList.remove('hidden');

        // Toggle visibility of add buttons in header
        document.querySelectorAll('.main-add-btn').forEach(btn => {
            btn.classList.add('hidden');
        });
        if (tabId === 'items') document.getElementById('btn-add-item').classList.remove('hidden');
        else if (tabId === 'targets') document.getElementById('btn-add-target').classList.remove('hidden');
        else if (tabId === 'services') document.getElementById('btn-add-service').classList.remove('hidden');

        // Update URL query parameter
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + tabId;
        window.history.pushState({ path: newUrl }, '', newUrl);
    };

    // 2. Articles Filters
    window.filterTarget = function(targetId) {
        activeFilter = targetId;

        // Manage active classes on pills
        document.querySelectorAll('.target-tab').forEach(btn => {
            btn.classList.remove('tab-btn-active');
        });
        const activeTab = document.getElementById(`target-tab-${targetId}`);
        if(activeTab) activeTab.classList.add('tab-btn-active');

        // Filter table rows
        document.querySelectorAll('.catalog-row').forEach(row => {
            const rowTargetId = row.getAttribute('data-target-id');
            if (targetId === 'all' || rowTargetId === targetId) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });
    };

    // ================= ARTICLES MODAL =================
    window.openAddCatalogModal = function() {
        document.getElementById('modal-title').textContent = "Nouvel Article";
        document.getElementById('catalog-form').action = "{{ route('admin.catalog.item.store', [], false) }}";
        document.getElementById('form-method-container').innerHTML = ""; 
        
        document.getElementById('catalog-item-name').value = "";
        document.getElementById('catalog-item-target').value = "";
        
        @foreach($services as $service)
            document.getElementById('catalog-price-{{ $service->id }}').value = "";
        @endforeach

        document.getElementById('catalog-modal').classList.remove('hidden');
    };

    window.openEditCatalogModal = function(item, prices) {
        document.getElementById('modal-title').textContent = "Modifier l'Article";
        document.getElementById('catalog-form').action = `/admin/catalog/item/${item.id}`;
        document.getElementById('form-method-container').innerHTML = `@method('PUT')`;

        document.getElementById('catalog-item-name').value = item.name;
        document.getElementById('catalog-item-target').value = item.garment_target_id || "";

        @foreach($services as $service)
            document.getElementById('catalog-price-{{ $service->id }}').value = prices['{{ $service->id }}'] || "";
        @endforeach

        document.getElementById('catalog-modal').classList.remove('hidden');
    };

    window.closeCatalogModal = function() {
        document.getElementById('catalog-modal').classList.add('hidden');
    };

    // ================= CATEGORIES (TARGETS) MODAL =================
    window.openAddTargetModal = function() {
        document.getElementById('target-modal-title').textContent = "Nouvelle Catégorie";
        document.getElementById('target-form').action = "{{ route('admin.catalog.target.store', [], false) }}";
        document.getElementById('target-form-method-container').innerHTML = "";
        document.getElementById('target-name').value = "";
        document.getElementById('target-modal').classList.remove('hidden');
    };

    window.openEditTargetModal = function(target) {
        document.getElementById('target-modal-title').textContent = "Modifier la Catégorie";
        document.getElementById('target-form').action = `/admin/catalog/target/${target.id}`;
        document.getElementById('target-form-method-container').innerHTML = `@method('PUT')`;
        document.getElementById('target-name').value = target.name;
        document.getElementById('target-modal').classList.remove('hidden');
    };

    window.closeTargetModal = function() {
        document.getElementById('target-modal').classList.add('hidden');
    };

    // ================= SERVICES MODAL =================
    window.openAddServiceModal = function() {
        document.getElementById('service-modal-title').textContent = "Nouveau Service";
        document.getElementById('service-form').action = "{{ route('admin.catalog.service.store', [], false) }}";
        document.getElementById('service-form-method-container').innerHTML = "";
        document.getElementById('service-name').value = "";
        document.getElementById('service-code').value = "";
        document.getElementById('service-modal').classList.remove('hidden');
    };

    window.openEditServiceModal = function(service) {
        document.getElementById('service-modal-title').textContent = "Modifier le Service";
        document.getElementById('service-form').action = `/admin/catalog/service/${service.id}`;
        document.getElementById('service-form-method-container').innerHTML = `@method('PUT')`;
        document.getElementById('service-name').value = service.name;
        document.getElementById('service-code').value = service.code;
        document.getElementById('service-modal').classList.remove('hidden');
    };

    window.closeServiceModal = function() {
        document.getElementById('service-modal').classList.add('hidden');
    };
</script>
@endsection
