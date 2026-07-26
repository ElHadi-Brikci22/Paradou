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
            <button onclick="switchServiceTab('{{ $service->id }}')" id="service-tab-btn-{{ $service->id }}"
                    class="service-tab-btn px-4 py-1.5 rounded-full text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer {{ $idx === 0 ? 'tab-btn-active' : '' }}">
                {{ $service->name }}
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

        <!-- Prices Tables -->
        @foreach($services as $idx => $service)
            <div id="service-table-content-{{ $service->id }}" class="service-table-content space-y-4 {{ $idx === 0 ? '' : 'hidden' }}">
                <div class="prices-card rounded-2xl overflow-hidden shadow-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-800/20">
                                    <th class="py-3.5 px-6">Nom de l'article</th>
                                    <th class="py-3.5 px-6">Catégorie</th>
                                    <th class="py-3.5 px-6 text-center">Tarif Détail (DA)</th>
                                    <th class="py-3.5 px-6 text-center">Tarif Gros (DA)</th>
                                    <th class="py-3.5 px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50 text-xs text-slate-300">
                                @foreach($items as $item)
                                    @php
                                        $servicePrice = $item->servicePrices->firstWhere('service_id', $service->id);
                                        $detailPrice = $servicePrice ? $servicePrice->price : '';
                                        $wholesalePrice = $servicePrice ? $servicePrice->wholesale_price : '';
                                    @endphp
                                    <tr class="hover:bg-slate-800/10 transition-colors price-row" 
                                        data-target-id="{{ $item->garment_target_id }}" 
                                        data-item-name="{{ strtolower($item->name) }}">
                                        <td class="py-3.5 px-6 font-bold text-slate-100 uppercase">
                                            {{ $item->name }}
                                        </td>
                                        <td class="py-3.5 px-6">
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-800 text-slate-300 border border-slate-700/60 uppercase">
                                                {{ $item->garmentTarget ? $item->garmentTarget->name : '-' }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-6 text-center">
                                            <input type="number" id="detail-{{ $item->id }}-{{ $service->id }}" 
                                                   value="{{ $detailPrice }}" min="0" placeholder="Non défini"
                                                   class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-200 focus:outline-none focus:border-indigo-500 font-mono w-32 text-center transition-all">
                                        </td>
                                        <td class="py-2 px-6 text-center">
                                            <input type="number" id="wholesale-{{ $item->id }}-{{ $service->id }}" 
                                                   value="{{ $wholesalePrice }}" min="0" placeholder="Non défini"
                                                   class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs font-bold text-indigo-400 focus:outline-none focus:border-indigo-500 font-mono w-32 text-center transition-all">
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

    window.savePrice = function(itemId, serviceId, button) {
        const detailInput = document.getElementById(`detail-${itemId}-${serviceId}`);
        const wholesaleInput = document.getElementById(`wholesale-${itemId}-${serviceId}`);
        
        const detailPrice = detailInput.value.trim();
        const wholesalePrice = wholesaleInput.value.trim();

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
                wholesale_price: wholesalePrice !== '' ? parseFloat(wholesalePrice) : null
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

                detailInput.classList.add('border-emerald-500', 'bg-emerald-950/20');
                wholesaleInput.classList.add('border-emerald-500', 'bg-emerald-950/20');

                setTimeout(() => {
                    button.disabled = false;
                    button.className = "bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs shadow-md shadow-indigo-600/10 transition-all cursor-pointer flex items-center space-x-1.5 inline-flex ml-auto";
                    button.innerHTML = originalContent;

                    detailInput.classList.remove('border-emerald-500', 'bg-emerald-950/20');
                    wholesaleInput.classList.remove('border-emerald-500', 'bg-emerald-950/20');
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
