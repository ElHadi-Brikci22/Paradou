@extends('layouts.app')

@section('title', 'Gestion des Tarifs')

@section('styles')
<style>
    .prices-card {
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
<div class="flex-1 flex flex-col min-w-0 bg-slate-900 overflow-hidden">
    <!-- Header panel -->
    <div class="bg-slate-800/40 p-5 border-b border-slate-700/50 shrink-0 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div>
            <h2 class="text-xl font-bold font-display text-white">Gestion des Tarifs</h2>
            <p class="text-xs text-slate-400">Configurer les prix des articles en détails et en gros pour chaque service</p>
        </div>
        
        <!-- Search bar -->
        <div class="w-full sm:w-72 relative">
            <input type="text" id="search-item" oninput="filterItems()" placeholder="Rechercher un article..." 
                   class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-10 pr-4 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
            <div class="absolute left-3 top-2.5 text-slate-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Service Tabs Navigation -->
    <div class="bg-slate-900 border-b border-slate-800 px-6 py-3 shrink-0 flex items-center space-x-2 overflow-x-auto">
        @foreach($services as $idx => $service)
            @php
                $isKilo = ($service->code === 'au_kilo' || str_contains(strtolower($service->name), 'kilo') || $service->id === 4);
            @endphp
            <button onclick="switchServiceTab('{{ $service->id }}')" id="service-tab-btn-{{ $service->id }}"
                    class="service-tab-btn px-4 py-1.5 rounded-full text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer {{ $idx === 0 ? 'tab-btn-active' : '' }}">
                @if($isKilo) ⚖️ @endif {{ $service->name }}
            </button>
        @endforeach
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 overflow-y-auto p-6 space-y-4">
        <!-- Target Categories Filter (Pills) -->
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

        <!-- Prices Tables for Each Service -->
        @foreach($services as $idx => $service)
            @php
                $isKiloService = ($service->code === 'au_kilo' || str_contains(strtolower($service->name), 'kilo') || $service->id === 4);
            @endphp
            <div id="service-table-content-{{ $service->id }}" class="service-table-content space-y-4 {{ $idx === 0 ? '' : 'hidden' }}">
                
                @if($isKiloService)
                    <!-- Top Control Banner for Au Kilo Uniform Pricing -->
                    <div class="bg-gradient-to-r from-amber-950/40 via-slate-800/80 to-slate-900 border border-amber-500/30 rounded-2xl p-5 shadow-xl flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5">
                        <div class="space-y-1.5 max-w-xl">
                            <div class="flex items-center space-x-2">
                                <span class="text-2xl">⚖️</span>
                                <h3 class="text-sm font-extrabold text-amber-300 uppercase tracking-wide">Tarif Unique du Service Au Kilo</h3>
                                <span class="text-[10px] bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2.5 py-0.5 rounded-full font-bold uppercase">Prix unique par kg</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Dans le service <strong>Au Kilo</strong>, tous les articles ont le même tarif au kilo. Définissez ce tarif unique une seule fois ci-contre. Dans le tableau ci-dessous, renseignez simplement le poids standard (en grammes) de chaque article.
                            </p>
                        </div>
                        <div class="flex items-center flex-wrap gap-3 bg-slate-900/90 p-3.5 rounded-xl border border-slate-700 shrink-0">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tarif / Kg Détail (DA)</label>
                                <div class="relative">
                                    <input type="number" id="global-kilo-price-{{ $service->id }}" 
                                           value="{{ $service->price !== null ? floatval($service->price) : '' }}" min="0" step="10" placeholder="ex: 250"
                                           class="bg-slate-800 border border-slate-700 rounded-lg pl-3 pr-8 py-2 text-xs font-bold text-amber-300 font-mono w-32 focus:outline-none focus:border-amber-500 transition-colors">
                                    <span class="absolute right-2.5 top-2 text-[10px] font-bold text-slate-500 select-none">DA</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tarif / Kg Gros (DA)</label>
                                <div class="relative">
                                    <input type="number" id="global-kilo-wholesale-{{ $service->id }}" 
                                           value="{{ $service->wholesale_price !== null ? floatval($service->wholesale_price) : '' }}" min="0" step="10" placeholder="ex: 200"
                                           class="bg-slate-800 border border-slate-700 rounded-lg pl-3 pr-8 py-2 text-xs font-bold text-indigo-300 font-mono w-32 focus:outline-none focus:border-indigo-500 transition-colors">
                                    <span class="absolute right-2.5 top-2 text-[10px] font-bold text-slate-500 select-none">DA</span>
                                </div>
                            </div>
                            <div class="self-end">
                                <button onclick="saveServicePrice('{{ $service->id }}', this)"
                                        class="bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold py-2 px-4 rounded-lg text-xs shadow-lg shadow-amber-600/20 transition-all cursor-pointer flex items-center space-x-1.5 active:scale-95">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Enregistrer le tarif / kg</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="prices-card rounded-2xl overflow-hidden shadow-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[750px]">
                            <thead>
                                <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-800/20">
                                    <th class="py-3.5 px-6">Nom de l'article</th>
                                    <th class="py-3.5 px-4">Catégorie</th>
                                    
                                    @if($isKiloService)
                                        <th class="py-3.5 px-4 text-center">
                                            <span class="text-amber-400 font-bold">Poids standard (g)</span>
                                        </th>
                                        <th class="py-3.5 px-6 text-center">Tarif / Kg appliqué</th>
                                        <th class="py-3.5 px-6 text-center">Prix estimé / pièce</th>
                                        <th class="py-3.5 px-6 text-right">Actions</th>
                                    @else
                                        <th class="py-3.5 px-4 text-center">
                                            <span class="text-amber-400 font-bold">Poids standard (g)</span>
                                        </th>
                                        <th class="py-3.5 px-6 text-center">Tarif Détail (DA)</th>
                                        <th class="py-3.5 px-6 text-center">Tarif Gros (DA)</th>
                                        <th class="py-3.5 px-6 text-right">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50 text-xs text-slate-300">
                                @foreach($items as $item)
                                    @php
                                        $servicePrice = $item->servicePrices->firstWhere('service_id', $service->id);
                                        $detailPrice = $servicePrice ? $servicePrice->price : '';
                                        $wholesalePrice = $servicePrice ? $servicePrice->wholesale_price : '';
                                        
                                        $kiloPrice = $service->price ? floatval($service->price) : 0;
                                        $stdWKg = $item->standard_weight ? floatval($item->standard_weight) / 1000 : 0;
                                        $estPrice = round($kiloPrice * $stdWKg, 0);
                                    @endphp
                                    <tr class="hover:bg-slate-800/10 transition-colors price-row" 
                                        data-target-id="{{ $item->garment_target_id }}" 
                                        data-item-name="{{ strtolower($item->name) }}">
                                        <td class="py-3.5 px-6 font-bold text-slate-100 uppercase">
                                            {{ $item->name }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-800 text-slate-300 border border-slate-700/60 uppercase">
                                                {{ $item->garmentTarget ? $item->garmentTarget->name : '-' }}
                                            </span>
                                        </td>
                                        
                                        @if($isKiloService)
                                            <!-- Au Kilo Row: Weight input + Applied /kg price + Estimated line price -->
                                            <td class="py-2 px-4 text-center">
                                                <div class="inline-flex items-center justify-center space-x-1">
                                                    <input type="number" id="weight-{{ $item->id }}-{{ $service->id }}" 
                                                           value="{{ $item->standard_weight !== null ? floatval($item->standard_weight) : '' }}" min="0" step="10" placeholder="ex: 500"
                                                           oninput="updateEstimatedPrice('{{ $item->id }}', '{{ $service->id }}')"
                                                           class="weight-input-{{ $item->id }} bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-bold text-amber-400 focus:outline-none focus:border-amber-500 font-mono w-28 text-center transition-all">
                                                    <span class="text-[10px] text-slate-500 font-bold">g</span>
                                                </div>
                                            </td>
                                            <td class="py-2 px-6 text-center">
                                                <span class="kilo-unit-badge-{{ $service->id }} px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-amber-500/10 text-amber-300 border border-amber-500/20">
                                                    {{ $service->price !== null ? floatval($service->price) . ' DA/kg' : 'Non défini' }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-6 text-center">
                                                <span id="est-price-{{ $item->id }}-{{ $service->id }}" class="text-xs font-mono font-bold text-slate-300">
                                                    {{ $stdWKg > 0 && $kiloPrice > 0 ? $estPrice . ' DA' : '-' }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-6 text-right">
                                                <button onclick="saveKiloWeight('{{ $item->id }}', '{{ $service->id }}', this)" 
                                                        class="bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold py-1.5 px-3 rounded-lg text-xs shadow-md shadow-amber-600/10 transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Enregistrer</span>
                                                </button>
                                            </td>
                                        @else
                                            <!-- Standard Service Row (Pressing, Blanchisserie, Repassage...) -->
                                            <td class="py-2 px-4 text-center">
                                                <div class="inline-flex items-center justify-center space-x-1">
                                                    <input type="number" id="weight-{{ $item->id }}-{{ $service->id }}" 
                                                           value="{{ $item->standard_weight !== null ? floatval($item->standard_weight) : '' }}" min="0" step="10" placeholder="ex: 500"
                                                           class="weight-input-{{ $item->id }} bg-slate-900 border border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold text-amber-400 focus:outline-none focus:border-amber-500 font-mono w-24 text-center transition-all">
                                                    <span class="text-[10px] text-slate-500 font-bold">g</span>
                                                </div>
                                            </td>
                                            <td class="py-2 px-6 text-center">
                                                <input type="number" id="detail-{{ $item->id }}-{{ $service->id }}" 
                                                       value="{{ $detailPrice }}" min="0" placeholder="Non défini"
                                                       class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-200 focus:outline-none focus:border-indigo-500 font-mono w-28 text-center transition-all">
                                            </td>
                                            <td class="py-2 px-6 text-center">
                                                <input type="number" id="wholesale-{{ $item->id }}-{{ $service->id }}" 
                                                       value="{{ $wholesalePrice }}" min="0" placeholder="Non défini"
                                                       class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs font-bold text-indigo-400 focus:outline-none focus:border-indigo-500 font-mono w-28 text-center transition-all">
                                            </td>
                                            <td class="py-2 px-6 text-right">
                                                <button onclick="savePrice('{{ $item->id }}', '{{ $service->id }}', this)" 
                                                        class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs shadow-md shadow-indigo-600/10 transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Enregistrer</span>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
    let activeServiceId = '{{ $services->first() ? $services->first()->id : "" }}';
    let activeTargetId = 'all';

    window.switchServiceTab = function(serviceId) {
        activeServiceId = serviceId;

        document.querySelectorAll('.service-tab-btn').forEach(btn => {
            btn.classList.remove('tab-btn-active');
        });
        const activeBtn = document.getElementById(`service-tab-btn-${serviceId}`);
        if (activeBtn) activeBtn.classList.add('tab-btn-active');

        document.querySelectorAll('.service-table-content').forEach(content => {
            content.classList.add('hidden');
        });
        const activeTable = document.getElementById(`service-table-content-${serviceId}`);
        if (activeTable) activeTable.classList.remove('hidden');

        filterItems();
    };

    window.filterTarget = function(targetId) {
        activeTargetId = targetId;

        document.querySelectorAll('.target-tab').forEach(btn => {
            btn.classList.remove('tab-btn-active');
        });
        const activeTab = document.getElementById(`target-tab-${targetId}`);
        if (activeTab) activeTab.classList.add('tab-btn-active');

        filterItems();
    };

    window.filterItems = function() {
        const searchTerm = document.getElementById('search-item').value.toLowerCase().trim();

        document.querySelectorAll(`#service-table-content-${activeServiceId} .price-row`).forEach(row => {
            const rowTargetId = row.getAttribute('data-target-id');
            const rowItemName = row.getAttribute('data-item-name');

            const matchesTarget = (activeTargetId === 'all' || rowTargetId === activeTargetId);
            const matchesSearch = (searchTerm === '' || rowItemName.includes(searchTerm));

            if (matchesTarget && matchesSearch) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });
    };

    // Calculate estimated line price for Au Kilo items
    window.updateEstimatedPrice = function(itemId, serviceId) {
        const weightInput = document.getElementById(`weight-${itemId}-${serviceId}`);
        const globalPriceInput = document.getElementById(`global-kilo-price-${serviceId}`);
        const estSpan = document.getElementById(`est-price-${itemId}-${serviceId}`);
        if (!weightInput || !estSpan) return;

        const weightG = parseFloat(weightInput.value) || 0;
        const kiloPrice = globalPriceInput ? (parseFloat(globalPriceInput.value) || 0) : 0;

        if (weightG > 0 && kiloPrice > 0) {
            const est = Math.round((weightG / 1000) * kiloPrice);
            estSpan.textContent = `${est} DA`;
        } else {
            estSpan.textContent = '-';
        }
    };

    // Save uniform kilo service price
    window.saveServicePrice = function(serviceId, button) {
        const priceInput = document.getElementById(`global-kilo-price-${serviceId}`);
        const wholesaleInput = document.getElementById(`global-kilo-wholesale-${serviceId}`);

        const price = priceInput ? priceInput.value.trim() : '';
        const wholesalePrice = wholesaleInput ? wholesaleInput.value.trim() : '';

        const originalContent = button.innerHTML;
        button.disabled = true;
        button.className = "bg-slate-700 text-slate-400 font-bold py-2 px-4 rounded-lg text-xs transition-all flex items-center space-x-1.5 cursor-not-allowed";
        button.innerHTML = `
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Enregistrement...</span>
        `;

        fetch('/admin/prices/service-price', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                service_id: serviceId,
                price: price !== '' ? parseFloat(price) : null,
                wholesale_price: wholesalePrice !== '' ? parseFloat(wholesalePrice) : null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                button.className = "bg-emerald-600 text-white font-bold py-2 px-4 rounded-lg text-xs transition-all flex items-center space-x-1.5";
                button.innerHTML = `
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Tarif au kilo enregistré !</span>
                `;

                // Update all unit price badges in the table
                const formattedPrice = price !== '' ? `${parseFloat(price)} DA/kg` : 'Non défini';
                document.querySelectorAll(`.kilo-unit-badge-${serviceId}`).forEach(b => {
                    b.textContent = formattedPrice;
                });

                // Recalculate estimated prices
                document.querySelectorAll(`[id^="weight-"][id$="-${serviceId}"]`).forEach(input => {
                    const parts = input.id.split('-');
                    if (parts.length >= 3) {
                        updateEstimatedPrice(parts[1], serviceId);
                    }
                });

                setTimeout(() => {
                    button.disabled = false;
                    button.className = "bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold py-2 px-4 rounded-lg text-xs shadow-lg shadow-amber-600/20 transition-all cursor-pointer flex items-center space-x-1.5 active:scale-95";
                    button.innerHTML = originalContent;
                }, 1500);
            } else {
                button.disabled = false;
                button.className = "bg-rose-600 hover:bg-rose-500 text-white font-bold py-2 px-4 rounded-lg text-xs transition-all cursor-pointer flex items-center space-x-1.5";
                button.innerHTML = `<span>Erreur !</span>`;
                setTimeout(() => {
                    button.className = "bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold py-2 px-4 rounded-lg text-xs shadow-lg shadow-amber-600/20 transition-all cursor-pointer flex items-center space-x-1.5 active:scale-95";
                    button.innerHTML = originalContent;
                }, 2000);
                showAppAlert(`Erreur : ${data.message}`, "error", "Erreur");
            }
        })
        .catch(err => {
            button.disabled = false;
            button.className = "bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold py-2 px-4 rounded-lg text-xs shadow-lg shadow-amber-600/20 transition-all cursor-pointer flex items-center space-x-1.5 active:scale-95";
            button.innerHTML = originalContent;
            showAppAlert("Erreur réseau ou connexion perdue.", "error", "Erreur");
        });
    };

    // Save individual item standard weight in Au Kilo
    window.saveKiloWeight = function(itemId, serviceId, button) {
        const weightInput = document.getElementById(`weight-${itemId}-${serviceId}`);
        const standardWeight = weightInput ? weightInput.value.trim() : '';

        const originalContent = button.innerHTML;
        button.disabled = true;
        button.className = "bg-slate-700 text-slate-400 font-bold py-1.5 px-3 rounded-lg text-xs transition-all flex items-center space-x-1.5 inline-flex ml-auto cursor-not-allowed";
        button.innerHTML = `
            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>...</span>
        `;

        fetch('/admin/prices/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                garment_item_id: itemId,
                service_id: serviceId,
                standard_weight: standardWeight !== '' ? parseFloat(standardWeight) : null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                button.className = "bg-emerald-600 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition-all flex items-center space-x-1.5 inline-flex ml-auto";
                button.innerHTML = `
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Enregistré</span>
                `;

                if (weightInput) {
                    weightInput.classList.add('border-emerald-500', 'bg-emerald-950/20');
                }

                // Synchronize weight inputs for this item across other service tabs
                document.querySelectorAll(`.weight-input-${itemId}`).forEach(inp => {
                    inp.value = standardWeight;
                });

                updateEstimatedPrice(itemId, serviceId);

                setTimeout(() => {
                    button.disabled = false;
                    button.className = "bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold py-1.5 px-3 rounded-lg text-xs shadow-md shadow-amber-600/10 transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto";
                    button.innerHTML = originalContent;

                    if (weightInput) {
                        weightInput.classList.remove('border-emerald-500', 'bg-emerald-950/20');
                    }
                }, 1200);
            } else {
                button.disabled = false;
                button.className = "bg-rose-600 hover:bg-rose-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto";
                button.innerHTML = `<span>Erreur</span>`;
                setTimeout(() => {
                    button.className = "bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold py-1.5 px-3 rounded-lg text-xs shadow-md shadow-amber-600/10 transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto";
                    button.innerHTML = originalContent;
                }, 2000);
                showAppAlert(`Erreur : ${data.message}`, "error", "Erreur");
            }
        })
        .catch(err => {
            button.disabled = false;
            button.className = "bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold py-1.5 px-3 rounded-lg text-xs shadow-md shadow-amber-600/10 transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto";
            button.innerHTML = originalContent;
            showAppAlert("Erreur réseau ou connexion perdue.", "error", "Erreur");
        });
    };

    // Save item price for non-kilo services
    window.savePrice = function(itemId, serviceId, button) {
        const detailInput = document.getElementById(`detail-${itemId}-${serviceId}`);
        const wholesaleInput = document.getElementById(`wholesale-${itemId}-${serviceId}`);
        const weightInput = document.getElementById(`weight-${itemId}-${serviceId}`);
        
        const detailPrice = detailInput ? detailInput.value.trim() : '';
        const wholesalePrice = wholesaleInput ? wholesaleInput.value.trim() : '';
        const standardWeight = weightInput ? weightInput.value.trim() : '';

        const originalContent = button.innerHTML;
        button.disabled = true;
        button.className = "bg-slate-700 text-slate-400 font-bold py-1.5 px-3 rounded-lg text-xs transition-all flex items-center space-x-1.5 inline-flex ml-auto cursor-not-allowed";
        button.innerHTML = `
            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Enregistrement...</span>
        `;

        fetch('/admin/prices/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                garment_item_id: itemId,
                service_id: serviceId,
                price: detailPrice !== '' ? parseFloat(detailPrice) : null,
                wholesale_price: wholesalePrice !== '' ? parseFloat(wholesalePrice) : null,
                standard_weight: standardWeight !== '' ? parseFloat(standardWeight) : null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                button.className = "bg-emerald-600 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition-all flex items-center space-x-1.5 inline-flex ml-auto";
                button.innerHTML = `
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Enregistré !</span>
                `;

                if (detailInput) detailInput.classList.add('border-emerald-500', 'bg-emerald-950/20');
                if (wholesaleInput) wholesaleInput.classList.add('border-emerald-500', 'bg-emerald-950/20');
                if (weightInput) weightInput.classList.add('border-emerald-500', 'bg-emerald-950/20');

                // Synchronize weight inputs for this item across other service tabs
                document.querySelectorAll(`.weight-input-${itemId}`).forEach(inp => {
                    inp.value = standardWeight;
                });

                setTimeout(() => {
                    button.disabled = false;
                    button.className = "bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs shadow-md shadow-indigo-600/10 transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto";
                    button.innerHTML = originalContent;

                    if (detailInput) detailInput.classList.remove('border-emerald-500', 'bg-emerald-950/20');
                    if (wholesaleInput) wholesaleInput.classList.remove('border-emerald-500', 'bg-emerald-950/20');
                    if (weightInput) weightInput.classList.remove('border-emerald-500', 'bg-emerald-950/20');
                }, 1500);
            } else {
                button.disabled = false;
                button.className = "bg-rose-600 hover:bg-rose-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto";
                button.innerHTML = `<span>Erreur !</span>`;
                setTimeout(() => {
                    button.className = "bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs shadow-md shadow-indigo-600/10 transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto";
                    button.innerHTML = originalContent;
                }, 2000);
                
                showAppAlert(`Erreur : ${data.message}`, "error", "Erreur");
            }
        })
        .catch(err => {
            button.disabled = false;
            button.className = "bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs shadow-md shadow-indigo-600/10 transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto";
            button.innerHTML = originalContent;
            showAppAlert("Erreur réseau ou connexion perdue.", "error", "Erreur");
        });
    };
</script>
@endsection
