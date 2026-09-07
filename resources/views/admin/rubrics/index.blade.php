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
                                <button type="button" onclick="openAddRubricModal('{{ $key }}')"
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

<!-- Custom Add Rubric Item Modal -->
<div id="add-rubric-modal" class="hidden fixed inset-0 bg-slate-950/70 z-50 flex items-center justify-center p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-sm flex flex-col shadow-2xl overflow-hidden transform scale-100 transition-all duration-150">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <h3 id="add-rubric-modal-title" class="text-base font-bold text-white font-display">Nouvelle entrée</h3>
            <button onclick="closeAddRubricModal()" class="text-slate-400 hover:text-white cursor-pointer">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body / Form -->
        <div class="p-6 space-y-4">
            <div>
                <label id="add-rubric-modal-label" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nom *</label>
                <input type="text" id="add-rubric-modal-input" required placeholder="Saisissez la valeur..." 
                       onkeydown="if(event.key === 'Enter') { event.preventDefault(); submitAddRubricModal(); }"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-700/50">
                <button type="button" onclick="closeAddRubricModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors cursor-pointer">Annuler</button>
                <button type="button" onclick="submitAddRubricModal()" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors cursor-pointer">Ajouter</button>
            </div>
        </div>
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

    let currentModalType = '';

    window.openAddRubricModal = function(type) {
        currentModalType = type;
        
        let title = "Nouvelle entrée";
        let label = "Nom";
        let placeholder = "";
        
        if (type === 'patterns') {
            title = "Nouveau Motif";
            label = "Nom du motif *";
            placeholder = "Ex: Carreaux fins, Rayures, ...";
        } else if (type === 'colors') {
            title = "Nouvelle Couleur";
            label = "Nom de la couleur *";
            placeholder = "Ex: Bleu Turquoise, Rouge Brique, ...";
        } else if (type === 'defects') {
            title = "Nouveau Défaut";
            label = "Nom du défaut *";
            placeholder = "Ex: Fermeture cassée, Accroc, ...";
        } else if (type === 'stains') {
            title = "Nouvelle Tache";
            label = "Nom de la tache *";
            placeholder = "Ex: Café, Herbe, Cambouis, ...";
        }
        
        document.getElementById('add-rubric-modal-title').textContent = title;
        document.getElementById('add-rubric-modal-label').textContent = label;
        
        const input = document.getElementById('add-rubric-modal-input');
        input.placeholder = placeholder;
        input.value = '';
        
        const modal = document.getElementById('add-rubric-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.querySelector('.transform').classList.remove('scale-95');
            modal.querySelector('.transform').classList.add('scale-100');
            input.focus();
        }, 50);
    };

    window.closeAddRubricModal = function() {
        const modal = document.getElementById('add-rubric-modal');
        modal.querySelector('.transform').classList.remove('scale-100');
        modal.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    };

    window.submitAddRubricModal = function() {
        const input = document.getElementById('add-rubric-modal-input');
        const val = input.value.trim();
        
        if (val === '') {
            alert("Veuillez saisir une valeur.");
            return;
        }

        const type = currentModalType;

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
        
        closeAddRubricModal();

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
