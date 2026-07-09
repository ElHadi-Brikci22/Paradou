@extends('layouts.app')

@section('title', 'Gestion Caissiers')

@section('content')
<div class="flex-1 flex flex-col min-w-0 bg-slate-900 overflow-hidden">
    <!-- Header bar -->
    <div class="bg-slate-800/40 p-5 border-b border-slate-700/50 shrink-0 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div>
            <h2 class="text-xl font-bold font-display text-white">Gestion des Caissiers & Utilisateurs</h2>
            <p class="text-xs text-slate-400">Ajouter, modifier ou supprimer des comptes d'accès pour votre personnel</p>
        </div>

        <button onclick="openAddUserModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold font-display rounded-lg shadow-lg shadow-indigo-600/10 transition-colors flex items-center space-x-1.5 cursor-pointer">
            <span>+ Ajouter un caissier</span>
        </button>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 overflow-y-auto p-6 space-y-4">
        <!-- Validation alerts -->
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

        <!-- Users Table Card -->
        <div class="bg-slate-800/40 border border-slate-700/30 rounded-2xl overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-800/20">
                        <th class="py-3 px-6">Nom</th>
                        <th class="py-3 px-6">Email</th>
                        <th class="py-3 px-6 text-center">Rôle</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-xs text-slate-300">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-800/10 transition-colors">
                            <td class="py-3.5 px-6 font-bold text-slate-100 flex items-center space-x-3">
                                <div class="h-7 w-7 rounded-full bg-slate-700 flex items-center justify-center font-bold text-indigo-400 uppercase">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <span>{{ $user->name }}</span>
                            </td>
                            <td class="py-3.5 px-6 font-mono">{{ $user->email }}</td>
                            <td class="py-3.5 px-6 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $user->role === 'admin' ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/20' : 'bg-slate-700/60 text-slate-300' }}">
                                    {{ $user->role === 'admin' ? 'Admin' : 'Caissier' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button onclick='openEditUserModal({{ json_encode($user) }})' 
                                            class="bg-slate-800 hover:bg-slate-700 text-indigo-400 p-1.5 rounded-lg border border-slate-700 cursor-pointer" title="Modifier">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- Delete form -->
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-slate-800 hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 p-1.5 rounded-lg border border-slate-700 hover:border-rose-500/20 cursor-pointer" 
                                                title="Supprimer" 
                                                {{ Auth::id() == $user->id ? 'disabled style=opacity:0.3;cursor:not-allowed;' : '' }}>
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

<!-- ================= MODALS OVERLAYS ================= -->

<!-- 1. Add User Modal -->
<div id="add-user-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-96 flex flex-col shadow-2xl overflow-hidden transform scale-95 transition-all">
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-base font-bold text-white font-display">Ajouter un Caissier</h3>
            <button onclick="closeAddUserModal()" class="text-slate-400 hover:text-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nom Complet *</label>
                <input type="text" name="name" required placeholder="Ex: Mohammed Benz"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Adresse Email *</label>
                <input type="email" name="email" required placeholder="Ex: mohammed@dryplus.com"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Mot de Passe *</label>
                <input type="password" name="password" required placeholder="Min 6 caractères"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Rôle *</label>
                <select name="role" required 
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="cashier" selected>Caissier (Accès caisse & commandes)</option>
                    <option value="admin">Administrateur (Accès complet)</option>
                </select>
            </div>

            <!-- Form buttons -->
            <div class="pt-2 flex justify-end space-x-3">
                <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors">Annuler</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Edit User Modal -->
<div id="edit-user-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-96 flex flex-col shadow-2xl overflow-hidden transform scale-95 transition-all">
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-base font-bold text-white font-display">Modifier l'Utilisateur</h3>
            <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <form id="edit-user-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nom Complet *</label>
                <input type="text" id="edit-user-name" name="name" required
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Adresse Email *</label>
                <input type="email" id="edit-user-email" name="email" required
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nouveau Mot de Passe (Optionnel)</label>
                <input type="password" name="password" placeholder="Laissez vide pour ne pas modifier"
                       class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Rôle *</label>
                <select id="edit-user-role" name="role" required 
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="cashier">Caissier (Accès caisse & commandes)</option>
                    <option value="admin">Administrateur (Accès complet)</option>
                </select>
            </div>

            <!-- Form buttons -->
            <div class="pt-2 flex justify-end space-x-3">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors">Annuler</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openAddUserModal() {
        document.getElementById('add-user-modal').classList.remove('hidden');
    }
    function closeAddUserModal() {
        document.getElementById('add-user-modal').classList.add('hidden');
    }

    function openEditUserModal(user) {
        // Set form action route dynamically
        document.getElementById('edit-user-form').action = `/admin/users/${user.id}`;
        
        // Fill form fields
        document.getElementById('edit-user-name').value = user.name;
        document.getElementById('edit-user-email').value = user.email;
        document.getElementById('edit-user-role').value = user.role;

        document.getElementById('edit-user-modal').classList.remove('hidden');
    }
    
    function closeEditUserModal() {
        document.getElementById('edit-user-modal').classList.add('hidden');
    }
</script>
@endsection
