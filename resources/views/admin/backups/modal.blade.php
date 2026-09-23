<!-- Database Backup & Destination Directory Modal -->
<div id="globalBackupModal" class="hidden fixed inset-0 bg-slate-950/80 z-[99999] flex items-center justify-center p-4 backdrop-blur-sm transition-all duration-200">
    <div class="relative w-full max-w-2xl bg-slate-900 border border-slate-700/80 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950/40 to-slate-900 p-5 border-b border-slate-700/60 flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center shadow-inner">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white font-display flex items-center gap-2">
                        Sauvegarde & Base de Données
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">MySQL</span>
                    </h3>
                    <p class="text-xs text-slate-400">Configurez l'emplacement des fichiers .sql et sauvegardez vos données</p>
                </div>
            </div>
            <button type="button" onclick="closeGlobalBackupModal()" class="text-slate-400 hover:text-white p-1.5 rounded-xl hover:bg-slate-800 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scrollable Modal Body -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1 text-slate-200">
            
            <!-- Notification feedback container -->
            <div id="backup-modal-alert" class="hidden p-4 rounded-xl text-xs font-semibold flex items-center justify-between transition-all"></div>

            <!-- 1. Emplacement du dossier de sauvegarde -->
            <div class="bg-slate-800/60 border border-slate-700/60 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <label for="backup-dir-input" class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        Dossier de destination des sauvegardes
                    </label>
                    <span id="backup-dir-badge" class="text-[10px] font-mono text-slate-400 bg-slate-900/80 px-2 py-0.5 rounded border border-slate-700">Dossier par défaut</span>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" id="backup-dir-input" placeholder="Ex: D:\Sauvegardes_Paradou ou E:\Backups"
                           class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <button type="button" id="save-backup-dir-btn" onclick="saveBackupDirectory()" 
                            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-xl transition-all active:scale-95 flex items-center justify-center gap-1.5 shrink-0 border border-slate-600">
                        <span>Enregistrer l'emplacement</span>
                    </button>
                </div>

                <p class="text-[11px] text-slate-400 leading-relaxed">
                    💡 Vous pouvez spécifier n'importe quel disque local ou clé USB (ex: <code class="text-amber-300 font-mono">D:\Sauvegardes</code> ou <code class="text-amber-300 font-mono">E:\Cle_USB</code>). S'il n'existe pas, il sera créé automatiquement. Le script <code class="text-indigo-300 font-mono">Sauvegarder_Base_De_Donnees.bat</code> utilisera également cet emplacement !
                </p>
            </div>

            <!-- 2. Action immédiate : Créer une sauvegarde -->
            <div class="bg-gradient-to-r from-indigo-950/30 to-slate-800/60 border border-indigo-500/20 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>🚀 Sauvegarde Instantanée</span>
                    </h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Génère immédiatement un dump complet de la base de données <strong class="text-slate-300">msk_dry_plus</strong>.
                    </p>
                </div>
                <button type="button" id="run-backup-btn" onclick="triggerDatabaseBackup()" 
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white text-xs font-extrabold rounded-xl shadow-lg shadow-indigo-950/50 transition-all active:scale-95 flex items-center gap-2 shrink-0 border border-indigo-400/30">
                    <svg id="run-backup-icon" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <svg id="run-backup-spinner" class="animate-spin h-4 w-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span id="run-backup-label">Sauvegarder maintenant</span>
                </button>
            </div>

            <!-- 3. Liste des sauvegardes disponibles -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                        <span>Historique des Sauvegardes (.sql)</span>
                        <span id="backups-count-badge" class="px-2 py-0.5 rounded-full bg-slate-800 text-[10px] font-mono text-slate-400 border border-slate-700">0</span>
                    </h4>
                    <button type="button" onclick="loadBackupData()" class="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 font-semibold">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Actualiser</span>
                    </button>
                </div>

                <!-- Backups Table Container -->
                <div class="bg-slate-950/60 border border-slate-800 rounded-xl overflow-hidden max-h-56 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-800/80 text-[10px] font-bold uppercase tracking-wider text-slate-400 sticky top-0 backdrop-blur-sm">
                            <tr>
                                <th class="py-2.5 px-4">Fichier</th>
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Taille</th>
                                <th class="py-2.5 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="backups-table-body" class="divide-y divide-slate-800 text-slate-300">
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-500">
                                    Chargement des sauvegardes...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3.5 bg-slate-800/80 border-t border-slate-700/80 flex items-center justify-between shrink-0">
            <span class="text-[11px] text-slate-400">
                Double-cliquer sur <span class="font-mono text-slate-300">Sauvegarder_Base_De_Donnees.bat</span> fonctionne également en arrière-plan.
            </span>
            <button type="button" onclick="closeGlobalBackupModal()" 
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold rounded-xl border border-slate-600 transition-colors">
                Fermer
            </button>
        </div>
    </div>
</div>

<script>
    window.openGlobalBackupModal = function() {
        const modal = document.getElementById('globalBackupModal');
        if (modal) {
            modal.classList.remove('hidden');
            loadBackupData();
        }
    };

    window.closeGlobalBackupModal = function() {
        const modal = document.getElementById('globalBackupModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    function showBackupAlert(message, type = 'success') {
        const alertEl = document.getElementById('backup-modal-alert');
        if (!alertEl) return;

        alertEl.classList.remove('hidden', 'bg-emerald-500/10', 'border-emerald-500/30', 'text-emerald-400', 'bg-rose-500/10', 'border-rose-500/30', 'text-rose-400');
        
        if (type === 'success') {
            alertEl.classList.add('bg-emerald-500/10', 'border', 'border-emerald-500/30', 'text-emerald-400');
            alertEl.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>${message}</span>
                </div>
            `;
        } else {
            alertEl.classList.add('bg-rose-500/10', 'border', 'border-rose-500/30', 'text-rose-400');
            alertEl.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    <span>${message}</span>
                </div>
            `;
        }

        setTimeout(() => {
            alertEl.classList.add('hidden');
        }, 6000);
    }

    async function loadBackupData() {
        try {
            const res = await fetch("{{ route('admin.backups.index') }}", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error("Erreur de chargement");
            const data = await res.json();

            const dirInput = document.getElementById('backup-dir-input');
            const dirBadge = document.getElementById('backup-dir-badge');
            if (dirInput) dirInput.value = data.directory || '';
            if (dirBadge) {
                dirBadge.textContent = data.is_default ? 'Dossier par défaut (sauvegardes/)' : 'Emplacement personnalisé';
                dirBadge.className = data.is_default 
                    ? 'text-[10px] font-mono text-slate-400 bg-slate-900/80 px-2 py-0.5 rounded border border-slate-700' 
                    : 'text-[10px] font-mono text-emerald-400 bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-700/60';
            }

            renderBackupsTable(data.backups || []);
        } catch (e) {
            console.error(e);
        }
    }

    function renderBackupsTable(backups) {
        const tbody = document.getElementById('backups-table-body');
        const countBadge = document.getElementById('backups-count-badge');
        if (countBadge) countBadge.textContent = backups.length;

        if (!tbody) return;

        if (!backups || backups.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="py-6 text-center text-slate-500">
                        Aucun fichier de sauvegarde trouvé dans ce dossier.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        backups.forEach(b => {
            const downloadUrl = `{{ url('/admin/backups/download') }}/${encodeURIComponent(b.filename)}`;
            html += `
                <tr class="hover:bg-slate-800/40 transition-colors">
                    <td class="py-2.5 px-4 font-mono font-medium text-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="text-indigo-400">📄</span>
                            <span title="${b.path}">${b.filename}</span>
                        </div>
                    </td>
                    <td class="py-2.5 px-3 text-slate-400 whitespace-nowrap">${b.created_at}</td>
                    <td class="py-2.5 px-3 font-mono text-slate-300 font-semibold whitespace-nowrap">${b.size}</td>
                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                        <a href="${downloadUrl}" download="${b.filename}" 
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 hover:text-indigo-100 border border-indigo-500/30 text-[11px] font-bold transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Télécharger</span>
                        </a>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    async function saveBackupDirectory() {
        const dirInput = document.getElementById('backup-dir-input');
        const btn = document.getElementById('save-backup-dir-btn');
        if (!dirInput) return;

        const val = dirInput.value.trim();
        if (!val) {
            alert("Veuillez renseigner un chemin valide (ex: D:\\Sauvegardes)");
            return;
        }

        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span>Enregistrement...</span>`;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch("{{ route('admin.backups.directory') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ directory: val })
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || "Erreur lors de l'enregistrement");
            }

            showBackupAlert(data.message, 'success');
            loadBackupData();
        } catch (err) {
            showBackupAlert(err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    async function triggerDatabaseBackup() {
        const btn = document.getElementById('run-backup-btn');
        const icon = document.getElementById('run-backup-icon');
        const spinner = document.getElementById('run-backup-spinner');
        const label = document.getElementById('run-backup-label');

        btn.disabled = true;
        icon.classList.add('hidden');
        spinner.classList.remove('hidden');
        label.textContent = "Sauvegarde en cours...";

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch("{{ route('admin.backups.run') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || "Erreur lors de la sauvegarde.");
            }

            showBackupAlert(`✅ Sauvegarde réussie : ${data.backup.filename} (${data.backup.size})`, 'success');
            renderBackupsTable(data.backups || []);
        } catch (err) {
            showBackupAlert(err.message, 'error');
        } finally {
            btn.disabled = false;
            icon.classList.remove('hidden');
            spinner.classList.add('hidden');
            label.textContent = "Sauvegarder maintenant";
        }
    }

    // Close backup modal on click outside
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('globalBackupModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target.id === 'globalBackupModal') {
                    closeGlobalBackupModal();
                }
            });
        }

        // Auto open if query param ?openBackup=1 is present
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('openBackup') === '1') {
            window.openGlobalBackupModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeGlobalBackupModal();
        }
    });
</script>
