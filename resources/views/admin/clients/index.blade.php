@extends('layouts.app')

@section('title', 'Gestion Clients')

@section('styles')
<style>
    .client-card {
        background-color: rgba(30, 41, 59, 0.4);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(71, 85, 105, 0.2);
    }
</style>
@endsection

@section('content')
<div class="flex-1 flex flex-col min-w-0 bg-slate-900 overflow-hidden">
    <!-- Header panel -->
    <div class="bg-slate-800/40 p-5 border-b border-slate-700/50 shrink-0 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div>
            <h2 class="text-xl font-bold font-display text-white">Gestion du Portefeuille Clients</h2>
            <p class="text-xs text-slate-400">Gérer les profils clients, configurer les remises attitrées et suivre l'état de leurs comptes</p>
        </div>

        <div>
            <button onclick="openAddClientModal()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold font-display rounded-lg shadow-lg shadow-indigo-600/10 transition-colors flex items-center space-x-1.5 cursor-pointer">
                <span>+ Nouveau Client</span>
            </button>
        </div>
    </div>

    <!-- Toolbar / Search filter -->
    <div class="bg-slate-900 border-b border-slate-800 px-6 py-3 shrink-0">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex items-center max-w-md gap-2">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par nom, code, téléphone..." 
                       class="w-full bg-slate-800 border border-slate-700/60 rounded-lg pl-9 pr-4 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                <div class="absolute left-3 top-2.5 text-slate-500">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <button type="submit" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 border border-slate-700/80 rounded-lg text-xs font-bold text-slate-300 transition-colors cursor-pointer">
                Filtrer
            </button>
            @if(!empty($search))
                <a href="{{ route('admin.clients.index') }}" class="text-xs text-slate-500 hover:text-slate-300 px-1 py-1 font-medium transition-colors">Effacer</a>
            @endif
        </form>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 overflow-y-auto p-6 space-y-4">
        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold rounded-xl shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold rounded-xl space-y-1 shadow-sm">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Table -->
        <div class="client-card rounded-2xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-800/20">
                            <th class="py-3.5 px-6">Code</th>
                            <th class="py-3.5 px-6">Client</th>
                            <th class="py-3.5 px-4">Téléphone</th>
                            <th class="py-3.5 px-4">Adresse</th>
                            <th class="py-3.5 px-4 text-center">Remise défaut</th>
                            <th class="py-3.5 px-4 text-right">Crédit / Solde</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-xs text-slate-300">
                        @forelse($clients as $client)
                            <tr class="hover:bg-slate-800/10 transition-colors">
                                <td class="py-3.5 px-6 font-mono text-slate-400 font-bold">
                                    {{ $client->code }}
                                </td>
                                <td class="py-3.5 px-6">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-100 uppercase">{{ $client->name }}</span>
                                        @if($client->email)
                                            <span class="text-[10px] text-slate-500 mt-0.5">{{ $client->email }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-300">
                                    {{ $client->phone ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-400 max-w-[200px] truncate" title="{{ $client->address }}">
                                    {{ $client->address ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($client->discount_percent > 0)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-indigo-500/20 text-indigo-400 border border-indigo-500/20">
                                            {{ $client->discount_percent }}%
                                        </span>
                                    @else
                                        <span class="text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold">
                                    @if($client->credit > 0)
                                        <span class="text-rose-400 font-black">{{ number_format($client->credit, 0, '.', ' ') }} DA</span>
                                    @else
                                        <span class="text-emerald-400">{{ number_format($client->credit, 0, '.', ' ') }} DA</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Do not allow modifications of GUEST client -->
                                        @if($client->code !== 'GUEST')
                                            <button onclick="openEditClientModal({{ $client->id }}, '{{ addslashes($client->code) }}', '{{ addslashes($client->name) }}', '{{ addslashes($client->phone) }}', '{{ addslashes($client->email) }}', '{{ addslashes($client->address) }}', {{ $client->discount_percent }}, {{ $client->credit }}, '{{ addslashes($client->remarks) }}')" 
                                                    class="bg-slate-800 hover:bg-slate-700 text-indigo-400 p-1.5 rounded-lg border border-slate-700 cursor-pointer transition-colors" 
                                                    title="Modifier">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            <form action="{{ route('admin.clients.destroy', $client->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
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
                                        @else
                                            <span class="text-[9px] bg-slate-800 text-slate-500 border border-slate-700 px-2 py-0.5 rounded uppercase font-semibold">Système</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    Aucun client trouvé dans le système.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($clients->hasPages())
                <div class="bg-slate-800/20 px-6 py-4 border-t border-slate-800">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ================= MODALS OVERLAYS ================= -->

<!-- Client Modal (Add & Edit) -->
<div id="client-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-md flex flex-col shadow-2xl overflow-hidden transform scale-95 transition-all">
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <h3 id="modal-title" class="text-base font-bold text-white font-display">Nouveau Client</h3>
            <button onclick="closeClientModal()" class="text-slate-400 hover:text-white cursor-pointer">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form content -->
        <form id="client-form" method="POST" class="p-6 space-y-4 overflow-y-auto max-h-[75vh]">
            @csrf
            <div id="form-method-container"></div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nom Complet *</label>
                <input type="text" id="client-name" name="name" required placeholder="Ex: Jean Dupont, Ahmed Brikci, ..." 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Téléphone</label>
                    <input type="text" id="client-phone" name="phone" placeholder="Ex: 0550123456" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Email</label>
                    <input type="email" id="client-email" name="email" placeholder="Ex: client@domain.com" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Adresse</label>
                <input type="text" id="client-address" name="address" placeholder="Ex: 12 Rue Didouche Mourad, Alger" 
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Remise par défaut (%)</label>
                    <input type="number" id="client-discount" name="discount_percent" min="0" max="100" placeholder="0" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Crédit Client initial (DA)</label>
                    <input type="number" id="client-credit" name="credit" min="0" step="10" placeholder="0" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Remarques / Notes</label>
                <textarea id="client-remarks" name="remarks" rows="2" placeholder="Ex: Remise fidélité, paiement différé autorisé, ..." 
                          class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-700/50">
                <button type="button" onclick="closeClientModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors cursor-pointer">Annuler</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors cursor-pointer">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.openAddClientModal = function() {
        document.getElementById('modal-title').textContent = "Nouveau Client";
        document.getElementById('client-form').action = "{{ route('admin.clients.store') }}";
        document.getElementById('form-method-container').innerHTML = ""; 
        
        document.getElementById('client-name').value = "";
        document.getElementById('client-phone').value = "";
        document.getElementById('client-email').value = "";
        document.getElementById('client-address').value = "";
        document.getElementById('client-discount').value = "";
        document.getElementById('client-credit').value = "";
        document.getElementById('client-remarks').value = "";

        document.getElementById('client-modal').classList.remove('hidden');
    };

    window.openEditClientModal = function(id, code, name, phone, email, address, discount, credit, remarks) {
        document.getElementById('modal-title').textContent = "Modifier le Client";
        document.getElementById('client-form').action = `/admin/clients/${id}`;
        document.getElementById('form-method-container').innerHTML = `@method('PUT')`;

        document.getElementById('client-name').value = name;
        document.getElementById('client-phone').value = phone;
        document.getElementById('client-email').value = email;
        document.getElementById('client-address').value = address;
        document.getElementById('client-discount').value = discount;
        document.getElementById('client-credit').value = credit;
        document.getElementById('client-remarks').value = remarks;

        document.getElementById('client-modal').classList.remove('hidden');
    };

    window.closeClientModal = function() {
        document.getElementById('client-modal').classList.add('hidden');
    };
</script>
@endsection
