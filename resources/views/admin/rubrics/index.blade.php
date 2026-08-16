@extends('layouts.app')

@section('title', 'Gestion des Rubriques & Dicos')

@section('styles')
<style>
    .rubric-card {
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
    // Detect active tab from request parameter, default to 'patterns'
    $activeTab = request()->query('tab', 'patterns');
    if (!in_array($activeTab, ['colors', 'defects', 'stains', 'patterns'])) {
        $activeTab = 'patterns';
    }
@endphp

<div class="flex-1 flex flex-col min-w-0 bg-slate-900 overflow-hidden">
    <!-- Header bar -->
    <div class="bg-slate-800/40 p-5 border-b border-slate-700/50 shrink-0">
        <h2 class="text-xl font-bold font-display text-white">Gestion des Rubriques & Dictionnaires</h2>
        <p class="text-xs text-slate-400">Modifier les listes d'options disponibles lors de l'enregistrement d'un article</p>
    </div>

    <!-- Rubric Tabs Selector -->
    <div class="bg-slate-900 border-b border-slate-800 px-6 py-3 shrink-0 flex items-center space-x-2 overflow-x-auto">
        <button onclick="switchTab('patterns')" id="tab-btn-patterns"
                class="rubric-tab-btn px-4 py-1.5 rounded-full text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer {{ $activeTab === 'patterns' ? 'tab-btn-active' : '' }}">
            Motifs
        </button>
        <button onclick="switchTab('colors')" id="tab-btn-colors"
                class="rubric-tab-btn px-4 py-1.5 rounded-full text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer {{ $activeTab === 'colors' ? 'tab-btn-active' : '' }}">
            Couleurs
        </button>
        <button onclick="switchTab('defects')" id="tab-btn-defects"
                class="rubric-tab-btn px-4 py-1.5 rounded-full text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer {{ $activeTab === 'defects' ? 'tab-btn-active' : '' }}">
            Défauts signalés
        </button>
        <button onclick="switchTab('stains')" id="tab-btn-stains"
                class="rubric-tab-btn px-4 py-1.5 rounded-full text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 border border-slate-700/50 transition-all cursor-pointer {{ $activeTab === 'stains' ? 'tab-btn-active' : '' }}">
            Taches à traiter
        </button>
    </div>

    <!-- Workspace -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6">
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

        <!-- Forms Containers -->
        @foreach(['patterns' => ['title' => 'Motifs (Carreaux, Rayures, ...)', 'data' => $patterns], 
                  'colors' => ['title' => 'Couleurs d\'articles', 'data' => $colors], 
                  'defects' => ['title' => 'Défauts à signaler', 'data' => $defects], 
                  'stains' => ['title' => 'Taches courantes à traiter', 'data' => $stains]] as $key => $meta)
            
            <div id="tab-content-{{ $key }}" class="rubric-tab-content {{ $activeTab === $key ? '' : 'hidden' }}">
                <form action="{{ route('admin.rubrics.save', [], false) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="{{ $key }}">

                    <div class="rubric-card rounded-2xl p-6 shadow-xl space-y-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-700/60 pb-4">
                            <div>
                                <h3 class="text-sm font-bold font-display text-white uppercase tracking-wider">{{ $meta['title'] }}</h3>
                                <p class="text-xs text-slate-400">Ajouter ou retirer des choix de cette liste.</p>
                            </div>

                            <!-- Add Item Box -->
                            <div class="flex items-center space-x-2 w-full sm:w-auto">
                                <button type="button" onclick="addNewItem('{{ $key }}')"
                                        class="shrink-0 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold font-display rounded-lg shadow-lg shadow-indigo-600/10 active:translate-y-0.5 transition-all cursor-pointer flex items-center space-x-1.5">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Ajouter</span>
                                </button>
                            </div>
                        </div>

                        <!-- Badges Grid Container -->
                        <div class="flex flex-wrap gap-2.5 min-h-[120px] p-5 bg-slate-950/40 border border-slate-800/80 rounded-xl" id="badges-container-{{ $key }}">
                            @foreach($meta['data'] as $item)
                                <div class="badge-item flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-700/60 bg-slate-850 hover:bg-slate-800 hover:border-slate-600 text-slate-200 transition-all shadow-sm">
                                    <span>{{ $item }}</span>
                                    <input type="hidden" name="items[]" value="{{ $item }}">
                                    <button type="button" onclick="removeItem(this)" class="text-slate-400 hover:text-rose-400 font-bold transition-colors cursor-pointer ml-1">×</button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Form submission footer -->
                        <div class="flex justify-end pt-4 border-t border-slate-700/50">
                            <button type="submit" 
                                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold font-display rounded-lg shadow-lg shadow-indigo-600/10 active:translate-y-0.5 transition-all cursor-pointer">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.switchTab = function(tabId) {
        // Toggle active tabs
        document.querySelectorAll('.rubric-tab-btn').forEach(btn => {
            btn.classList.remove('tab-btn-active');
        });
        const activeBtn = document.getElementById(`tab-btn-${tabId}`);
        if(activeBtn) activeBtn.classList.add('tab-btn-active');

        // Toggle visibility of contents
        document.querySelectorAll('.rubric-tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        const activeContent = document.getElementById(`tab-content-${tabId}`);
        if(activeContent) activeContent.classList.remove('hidden');

        // Update URL query parameter without reloading page
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + tabId;
        window.history.pushState({ path: newUrl }, '', newUrl);
    };

    window.addNewItem = function(type) {
        let label = "choix";
        if (type === 'patterns') label = "motif";
        else if (type === 'colors') label = "couleur";
        else if (type === 'defects') label = "défaut";
        else if (type === 'stains') label = "tache";

        const val = prompt("Saisissez le nom du " + label + " :")?.trim();

        if (!val) return; // Annulé ou vide

        // Check duplicates
        let exists = false;
        document.querySelectorAll(`#badges-container-${type} input[type="hidden"]`).forEach(el => {
            if (el.value.toLowerCase() === val.toLowerCase()) {
                exists = true;
            }
        });

        if (exists) {
            alert('Cette entrée existe déjà dans la liste.');
            return;
        }

        // Create new badge DOM
        const container = document.getElementById(`badges-container-${type}`);
        if (!container) {
            alert("Erreur : l'élément badges-container n'a pas été trouvé.");
            return;
        }
        const badge = document.createElement('div');
        badge.className = "badge-item flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-700/60 bg-slate-850 hover:bg-slate-800 hover:border-slate-600 text-slate-200 transition-all shadow-sm";
        badge.innerHTML = `
            <span>${val}</span>
            <input type="hidden" name="items[]" value="${val}">
            <button type="button" onclick="removeItem(this)" class="text-slate-400 hover:text-rose-400 font-bold transition-colors cursor-pointer ml-1">×</button>
        `;

        container.appendChild(badge);

        const form = document.querySelector(`#tab-content-${type} form`);
        if (!form) {
            alert("Erreur : le formulaire parent n'a pas été trouvé.");
            return;
        }
        form.submit();
    };

    window.removeItem = function(btn) {
        const form = btn.closest('form');
        btn.closest('.badge-item').remove();
        
        // Auto submit to save changes instantly
        form.submit();
    };
</script>
@endsection
