@extends('layouts.app')

@section('title', 'Tableau de Bord Administrateur')

@section('styles')
<style>
    .kpi-card {
        background-color: rgba(30, 41, 59, 0.4);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(71, 85, 105, 0.2);
    }
</style>
@endsection

@section('content')
<div class="flex-1 flex flex-col min-w-0 bg-slate-900 overflow-hidden">
    <!-- Header panel with range selector -->
    <div class="bg-slate-800/40 p-5 border-b border-slate-700/50 shrink-0 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div>
            <h2 class="text-xl font-bold font-display text-white">Analyses & Statistiques</h2>
            <p class="text-xs text-slate-400">
                @if($segment === 'all')
                    Rapports financiers et performance globale de la boutique
                @elseif($segment === 'blanchisserie')
                    Rapports financiers pour la <strong>Blanchisserie</strong> uniquement
                @elseif($segment === 'teinture')
                    Rapports financiers pour la <strong>Teinture</strong> uniquement
                @else
                    Rapports financiers pour les <strong>Autres Services</strong> uniquement
                @endif
                @if($userId !== 'all')
                    @php $selectedUser = $users->firstWhere('id', $userId); @endphp
                    (Acteur: <strong>{{ $selectedUser ? $selectedUser->name : 'l\'acteur' }}</strong>)
                @endif
            </p>
        </div>

        <!-- Actions & Filters -->
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <!-- Range & User & Segment Filter Form -->
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-slate-400 font-medium">Service :</span>
                    <select name="segment" onchange="this.form.submit()" 
                            class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs font-semibold text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="all" {{ $segment === 'all' ? 'selected' : '' }}>Tout le magasin (Global)</option>
                        <option value="blanchisserie" {{ $segment === 'blanchisserie' ? 'selected' : '' }}>Blanchisserie uniquement</option>
                        <option value="teinture" {{ $segment === 'teinture' ? 'selected' : '' }}>Teinture uniquement</option>
                        <option value="others" {{ $segment === 'others' ? 'selected' : '' }}>Autres services</option>
                    </select>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="text-xs text-slate-400 font-medium">Acteur :</span>
                    <select name="user_id" onchange="this.form.submit()" 
                            class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs font-semibold text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="all" {{ $userId === 'all' ? 'selected' : '' }}>Tous les acteurs</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ intval($userId) === $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->role === 'admin' ? 'Admin' : 'Caissier' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="text-xs text-slate-400 font-medium">Période :</span>
                    <select name="range" onchange="this.form.submit()" 
                            class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs font-semibold text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="today" {{ $range === 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                        <option value="week" {{ $range === 'week' ? 'selected' : '' }}>7 derniers jours</option>
                        <option value="month" {{ $range === 'month' ? 'selected' : '' }}>Ce mois-ci</option>
                        <option value="year" {{ $range === 'year' ? 'selected' : '' }}>Cette année</option>
                        <option value="all" {{ $range === 'all' ? 'selected' : '' }}>Toutes les données</option>
                    </select>
                </div>
            </form>

            <!-- Quick Add Expense Button -->
            <button type="button" onclick="openExpenseModal()" 
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-lg bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white shadow-lg shadow-rose-950/40 text-xs font-bold transition-all transform active:scale-95 border border-rose-500/30 whitespace-nowrap cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>+ Nouvelle Charge</span>
            </button>

            <!-- Quick Backup Modal Button -->
            <button type="button" onclick="openGlobalBackupModal()" 
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-lg bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white shadow-lg shadow-indigo-950/40 text-xs font-bold transition-all transform active:scale-95 border border-indigo-500/30 whitespace-nowrap cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
                <span>💾 Sauvegardes DB</span>
            </button>
        </div>
    </div>

    <!-- Main Scrollable Dashboard Content -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6">
        
        <!-- Flash notifications -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400/60 hover:text-emerald-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-400/60 hover:text-rose-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
        
        <!-- Row 1: KPI Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            
            <!-- KPI 1: Net Billing CA -->
            <div class="kpi-card rounded-2xl p-4 border-l-4 border-l-indigo-500">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Chiffre d'Affaires Net</p>
                <p class="text-xl font-black font-display text-white mt-2">{{ number_format($totalNetCA, 0, '.', ' ') }} DA</p>
                <div class="flex items-center justify-between text-[10px] text-slate-500 mt-2">
                    <span>Tickets émis : {{ $totalOrdersCount }}</span>
                </div>
            </div>

            <!-- KPI 2: Cash Collected -->
            <div class="kpi-card rounded-2xl p-4 border-l-4 border-l-emerald-500">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Encaissé</p>
                <p class="text-xl font-black font-display text-emerald-400 mt-2">{{ number_format($totalCollected, 0, '.', ' ') }} DA</p>
                <div class="flex items-center justify-between text-[10px] text-slate-500 mt-2">
                    <span>Taux d'encaissement : {{ $totalNetCA > 0 ? number_format(($totalCollected / $totalNetCA) * 100, 0) : 0 }}%</span>
                </div>
            </div>

            <!-- KPI 3: Remaining Balance -->
            <div class="kpi-card rounded-2xl p-4 border-l-4 border-l-amber-500">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Reste à Encaisser</p>
                <p class="text-xl font-black font-display text-amber-500 mt-2">{{ number_format($totalBalance, 0, '.', ' ') }} DA</p>
                <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2">
                    <span>Dont livrés à crédit : <strong class="text-amber-400 font-mono">{{ number_format($totalCreditAmount, 0, '.', ' ') }} DA</strong> ({{ $totalCreditTickets }})</span>
                </div>
            </div>

            <!-- KPI 4: Total Expenses -->
            <div class="kpi-card rounded-2xl p-4 border-l-4 border-l-rose-500">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Dépenses</p>
                    <button type="button" onclick="openExpenseModal()" class="text-[10px] font-bold text-rose-400 hover:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 px-2 py-0.5 rounded border border-rose-500/20 transition-colors">
                        + Saisir
                    </button>
                </div>
                <p class="text-xl font-black font-display text-rose-400 mt-2">
                    @if(is_null($totalExpenses))
                        N/A
                    @else
                        {{ number_format($totalExpenses, 0, '.', ' ') }} DA
                    @endif
                </p>
                <div class="flex items-center justify-between text-[10px] text-slate-500 mt-2">
                    <span>{{ is_null($totalExpenses) ? 'Charges non réparties' : ($expensesCount . ' charge' . ($expensesCount > 1 ? 's' : '')) }}</span>
                    <a href="#charges-section" class="text-rose-400/80 hover:text-rose-400 underline text-[10px]">Voir détail</a>
                </div>
            </div>

            <!-- KPI 5: Net Profit -->
            <div class="kpi-card rounded-2xl p-4 border-l-4 border-l-teal-500 bg-gradient-to-br from-slate-800/60 to-teal-950/20">
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Bénéfice Réel</p>
                <p class="text-xl font-black font-display {{ is_null($netProfit) ? 'text-slate-400' : ($netProfit >= 0 ? 'text-teal-400' : 'text-rose-500') }} mt-2">
                    @if(is_null($netProfit))
                        N/A
                    @else
                        {{ number_format($netProfit, 0, '.', ' ') }} DA
                    @endif
                </p>
                <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2">
                    <span>{{ is_null($netProfit) ? 'Charges non réparties' : 'Encaissé - Dépenses' }}</span>
                </div>
            </div>

        </div>

        <!-- Row 2: Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Sales Trend Line Chart (Col span 2) -->
            <div class="kpi-card rounded-2xl p-5 lg:col-span-2 flex flex-col h-96">
                <h3 class="text-sm font-bold text-slate-200 font-display mb-4">Évolution des ventes (Ventes nettes en DA)</h3>
                <div class="flex-1 min-h-0 relative">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Doughnut Chart: Ventes par Service -->
            <div class="kpi-card rounded-2xl p-5 flex flex-col h-96">
                <h3 class="text-sm font-bold text-slate-200 font-display mb-4">Chiffre d'Affaires par Service</h3>
                <div class="flex-1 min-h-0 relative flex items-center justify-center">
                    @if(count($serviceLabels) > 0)
                        <canvas id="servicesChart"></canvas>
                    @else
                        <div class="text-slate-500 text-xs font-medium text-center">Aucune vente enregistrée sur cette période</div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Row 3: Tables Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Top Clients Table -->
            <div class="kpi-card rounded-2xl p-5">
                <h3 class="text-sm font-bold text-slate-200 font-display mb-4 flex items-center justify-between">
                    <span>Top 5 Clients de la période</span>
                    <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded font-mono font-normal">Total clients: {{ $totalClientsCount }}</span>
                </h3>
                
                @if($topClients->isEmpty())
                    <p class="text-slate-500 text-xs py-8 text-center">Aucune transaction enregistrée.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="pb-2">Client</th>
                                    <th class="pb-2 text-center">Tickets</th>
                                    <th class="pb-2 text-right">Montant Cumulé</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                @foreach($topClients as $tc)
                                    <tr class="hover:bg-slate-800/10">
                                        <td class="py-2.5">
                                            <div class="font-bold text-slate-200">{{ $tc->client->name }}</div>
                                            <div class="text-[9px] text-slate-500 font-mono">Code: {{ $tc->client->code }}</div>
                                        </td>
                                        <td class="py-2.5 text-center font-semibold text-slate-300">{{ $tc->tickets_count }}</td>
                                        <td class="py-2.5 text-right font-bold text-indigo-400 font-mono">{{ number_format($tc->total_spent, 0, '.', ' ') }} DA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Services Detailed Table -->
            <div class="kpi-card rounded-2xl p-5">
                <h3 class="text-sm font-bold text-slate-200 font-display mb-4">Détail des performances par Service</h3>
                
                @if($servicesBreakdown->isEmpty())
                    <p class="text-slate-500 text-xs py-8 text-center">Aucun vêtement traité.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="pb-2">Service</th>
                                    <th class="pb-2 text-center">Nombre vêtements</th>
                                    <th class="pb-2 text-right">Revenus générés</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                @foreach($servicesBreakdown as $sb)
                                    <tr class="hover:bg-slate-800/10">
                                        <td class="py-2.5 font-bold text-slate-200 uppercase">{{ $sb->service->name }}</td>
                                        <td class="py-2.5 text-center text-slate-300 font-mono">{{ floatval($sb->qty) }}</td>
                                        <td class="py-2.5 text-right font-bold text-emerald-400 font-mono">{{ number_format($sb->revenue, 0, '.', ' ') }} DA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        <!-- Row 4: Performance par Acteur (Visible only when global is selected) -->
        @if($userId === 'all')
            <div class="kpi-card rounded-2xl p-5">
                <h3 class="text-sm font-bold text-slate-200 font-display mb-4 flex items-center justify-between">
                    <span>Performance par Acteur (Caissiers & Admins)</span>
                    <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded font-mono font-normal">Nombre d'acteurs: {{ count($usersStats) }}</span>
                </h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-500 uppercase tracking-wider bg-slate-800/10">
                                <th class="py-3 px-4">Utilisateur</th>
                                <th class="py-3 px-4">Rôle</th>
                                <th class="py-3 px-4 text-center">Tickets émis</th>
                                <th class="py-3 px-4 text-right">Chiffre d'Affaires</th>
                                <th class="py-3 px-4 text-right">Total Encaissé</th>
                                <th class="py-3 px-4 text-right">Total Dépenses</th>
                                <th class="py-3 px-4 text-right">Bénéfice Réel</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @foreach($usersStats as $stat)
                                <tr class="hover:bg-slate-800/10 transition-colors">
                                    <td class="py-3 px-4 font-bold text-slate-200">
                                        {{ $stat['user']->name }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold {{ $stat['user']->role === 'admin' ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/20' : 'bg-slate-700 text-slate-300' }} uppercase">
                                            {{ $stat['user']->role === 'admin' ? 'Admin' : 'Caissier' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-semibold text-slate-300 font-mono">
                                        {{ $stat['tickets'] }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-indigo-400 font-mono">
                                        {{ number_format($stat['ca'], 0, '.', ' ') }} DA
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-emerald-400 font-mono">
                                        {{ number_format($stat['collected'], 0, '.', ' ') }} DA
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold text-rose-400 font-mono">
                                        @if(is_null($stat['expenses']))
                                            N/A
                                        @else
                                            {{ number_format($stat['expenses'], 0, '.', ' ') }} DA
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right font-black {{ is_null($stat['profit']) ? 'text-slate-400' : ($stat['profit'] >= 0 ? 'text-teal-400' : 'text-rose-500') }} font-mono">
                                        @if(is_null($stat['profit']))
                                            N/A
                                        @else
                                            {{ number_format($stat['profit'], 0, '.', ' ') }} DA
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Row 5: Suivi & Analyse des Commandes Livrées à Crédit -->
        <div class="kpi-card rounded-2xl p-5 border-l-4 border-l-amber-500">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-3 border-b border-slate-700/40">
                <div class="flex items-center space-x-2.5">
                    <div class="h-8 w-8 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-100 font-display">Commandes Livrées à Crédit (Créances Clients)</h3>
                        <p class="text-[11px] text-slate-400">Suivi des vêtements remis au client sans encaissement intégral du solde</p>
                    </div>
                </div>
                <a href="{{ route('orders.index', ['status' => 'credit']) }}" 
                   class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold transition-colors">
                    <span>Voir dans le Suivi des Commandes</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chiffres Clés Crédit (Col 1) -->
                <div class="space-y-3 flex flex-col justify-between">
                    <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700/30">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Solde Crédit de la Période</span>
                        <span class="text-xl font-black text-amber-400 font-mono mt-1 block">{{ number_format($totalCreditAmount, 0, '.', ' ') }} DA</span>
                        <div class="text-[10px] text-slate-400 mt-1 flex justify-between">
                            <span>Tickets concernés : <strong class="text-white">{{ $totalCreditTickets }}</strong></span>
                            <span>Déjà perçu : {{ number_format($totalCreditPaid, 0, '.', ' ') }} DA</span>
                        </div>
                    </div>

                    <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700/30">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Part du Crédit sur les Ventes</span>
                        <span class="text-xl font-black text-rose-400 font-mono mt-1 block">{{ $totalNetCA > 0 ? number_format(($totalCreditAmount / $totalNetCA) * 100, 1) : 0 }} %</span>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">Du Chiffre d'Affaires Net de la période</span>
                    </div>

                    <div class="bg-slate-900/50 p-4 rounded-xl border border-amber-500/30 bg-gradient-to-br from-slate-900/60 to-amber-950/20">
                        <span class="text-[10px] text-amber-300 font-bold uppercase tracking-wider block">Créances Totales Magasin (Tout Temps)</span>
                        <span class="text-xl font-black text-amber-400 font-mono mt-1 block">{{ number_format($globalOutstandingCredit, 0, '.', ' ') }} DA</span>
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Sur un total de <strong class="text-white">{{ $globalOutstandingTickets }} tickets</strong> livrés non soldés</span>
                    </div>
                </div>

                <!-- Détail des Commandes Crédit (Col 2-3) -->
                <div class="lg:col-span-2 flex flex-col">
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Détail des Tickets Livrés Non Soldés ({{ $creditOrders->count() }})</h4>
                    
                    @if($creditOrders->isEmpty())
                        <div class="flex-1 flex flex-col items-center justify-center p-8 bg-slate-900/30 rounded-xl border border-slate-800 text-center">
                            <svg class="h-10 w-10 text-emerald-400 mb-2 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xs font-bold text-slate-300">Aucune créance sur cette période</p>
                            <p class="text-[10px] text-slate-500">Toutes les commandes livrées ont été intégralement réglées.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto max-h-[320px] overflow-y-auto rounded-xl border border-slate-800 bg-slate-900/40">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider sticky top-0 bg-slate-900 py-2.5 px-3">
                                        <th class="py-2 px-3">Ticket</th>
                                        <th class="py-2 px-3">Client</th>
                                        <th class="py-2 px-3">Date</th>
                                        <th class="py-2 px-3 text-right">Total Net</th>
                                        <th class="py-2 px-3 text-right">Payé</th>
                                        <th class="py-2 px-3 text-right">Solde Dû</th>
                                        <th class="py-2 px-3 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/50">
                                    @foreach($creditOrders as $co)
                                        <tr class="hover:bg-slate-800/30 transition-colors">
                                            <td class="py-2.5 px-3 font-mono font-bold text-slate-200">
                                                #{{ $co->ticket_number }}
                                            </td>
                                            <td class="py-2.5 px-3">
                                                <div class="font-bold text-slate-200">{{ $co->client ? $co->client->name : 'N/A' }}</div>
                                                <div class="text-[9px] text-slate-400 font-mono">{{ $co->client && $co->client->phone ? $co->client->phone : ($co->client ? $co->client->code : '') }}</div>
                                            </td>
                                            <td class="py-2.5 px-3 text-slate-400 font-mono text-[10px]">
                                                {{ $co->order_date->format('d/m/Y') }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono text-slate-300">
                                                {{ number_format($co->total_amount, 0, '.', ' ') }} DA
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono text-emerald-400">
                                                {{ number_format($co->paid_amount, 0, '.', ' ') }} DA
                                            </td>
                                            <td class="py-2.5 px-3 text-right">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded font-mono font-bold text-[10px] bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                                    {{ number_format($co->balance_amount, 0, '.', ' ') }} DA
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-3 text-center">
                                                <a href="{{ route('orders.index', ['status' => 'credit', 'search' => $co->ticket_number]) }}" 
                                                   class="p-1 rounded bg-slate-800 hover:bg-slate-700 text-amber-400 hover:text-amber-300 inline-flex items-center transition-colors" title="Régler ce ticket">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        <!-- Row: Suivi & Comptabilisation des Charges (Dépenses) -->
        <div id="charges-section" class="kpi-card rounded-2xl p-5 border-l-4 border-l-rose-500">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-3 border-b border-slate-700/40">
                <div class="flex items-center space-x-2.5">
                    <div class="h-8 w-8 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-100 font-display">Charges & Dépenses de la période</h3>
                        <p class="text-[11px] text-slate-400">Salaires employés, loyer, factures d'énergie, réparations et sorties de caisse</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 block">Total charges :</span>
                        <span class="text-sm font-black text-rose-400 font-mono">{{ number_format($expensesSum, 0, '.', ' ') }} DA</span>
                    </div>
                    <button type="button" onclick="openExpenseModal()" 
                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-rose-600/20 hover:bg-rose-600/30 text-rose-400 hover:text-rose-300 border border-rose-500/30 text-xs font-bold transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Saisir une charge</span>
                    </button>
                </div>
            </div>

            <!-- Category summary pills if any -->
            @if($expensesByCategory->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($expensesByCategory as $cat)
                        <div class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700/50 text-xs font-medium text-slate-300">
                            <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                            <span>{{ $cat->category }} :</span>
                            <span class="font-bold text-rose-400 font-mono">{{ number_format($cat->total, 0, '.', ' ') }} DA</span>
                            <span class="text-[10px] text-slate-500">({{ $cat->count }})</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($periodExpenses->isEmpty())
                <div class="text-center py-8 text-slate-500 text-xs">
                    <div class="h-10 w-10 mx-auto mb-2 rounded-full bg-slate-800 flex items-center justify-center text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="font-semibold text-slate-400">Aucune charge enregistrée pour cette période.</p>
                    <p class="text-[11px] text-slate-500 mt-1">Vous pouvez consigner une dépense (ex: salaire d'un employé, loyer, réparation) pour déduire ce montant du bénéfice réel.</p>
                    <button type="button" onclick="openExpenseModal()" class="mt-3 inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition-all shadow">
                        <span>+ Enregistrer la première charge</span>
                    </button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-500 uppercase tracking-wider bg-slate-800/20">
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Catégorie / Motif</th>
                                <th class="py-2.5 px-3">Détails & Notes</th>
                                <th class="py-2.5 px-3">Enregistré par</th>
                                <th class="py-2.5 px-3 text-right">Montant</th>
                                <th class="py-2.5 px-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @foreach($periodExpenses as $exp)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="py-2.5 px-3 text-slate-300 font-mono text-[11px] whitespace-nowrap">
                                        {{ $exp->expense_date ? $exp->expense_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold 
                                            @if(str_contains(strtolower($exp->category), 'salaire')) bg-blue-500/10 text-blue-400 border border-blue-500/30
                                            @elseif(str_contains(strtolower($exp->category), 'loyer')) bg-purple-500/10 text-purple-400 border border-purple-500/30
                                            @elseif(str_contains(strtolower($exp->category), 'electr') || str_contains(strtolower($exp->category), 'eau')) bg-amber-500/10 text-amber-400 border border-amber-500/30
                                            @elseif(str_contains(strtolower($exp->category), 'produit') || str_contains(strtolower($exp->category), 'lessive')) bg-teal-500/10 text-teal-400 border border-teal-500/30
                                            @else bg-rose-500/10 text-rose-400 border border-rose-500/30 @endif">
                                            {{ $exp->category }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-300">
                                        {{ $exp->notes ?: '—' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-400">
                                        {{ $exp->user ? $exp->user->name : 'Système' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-black text-rose-400 font-mono text-sm whitespace-nowrap">
                                        - {{ number_format($exp->amount, 0, '.', ' ') }} DA
                                    </td>
                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                        <form method="POST" action="{{ route('admin.expenses.destroy', $exp->id) }}" onsubmit="return confirm('Confirmez-vous la suppression de cette charge de {{ number_format($exp->amount, 0, '.', ' ') }} DA ?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded bg-slate-800 hover:bg-rose-950/40 text-slate-400 hover:text-rose-400 transition-colors" title="Supprimer cette dépense">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Row 6: Analyse des Remises -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Summary Stats on Discounts -->
            <div class="kpi-card rounded-2xl p-5 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-200 font-display mb-4">Statistiques des Remises</h3>
                    
                    <div class="space-y-4">
                        <div class="bg-slate-900/40 p-3.5 rounded-xl border border-slate-700/20">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Montant Total des Remises</span>
                            <span class="text-lg font-black text-amber-500 font-mono mt-1 block">{{ number_format($totalDiscountAmount, 0, '.', ' ') }} DA</span>
                        </div>
                        
                        <div class="bg-slate-900/40 p-3.5 rounded-xl border border-slate-700/20">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Tickets avec Remise</span>
                            <span class="text-lg font-black text-indigo-400 font-mono mt-1 block">{{ $totalDiscountedTickets }} / {{ $totalOrdersCount }}</span>
                            <span class="text-[9px] text-slate-500 mt-0.5 block">Taux d'application : {{ $totalOrdersCount > 0 ? number_format(($totalDiscountedTickets / $totalOrdersCount) * 100, 1) : 0 }}%</span>
                        </div>
                        
                        <div class="bg-slate-900/40 p-3.5 rounded-xl border border-slate-700/20">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Taux de Remise Moyen</span>
                            <span class="text-lg font-black text-rose-400 font-mono mt-1 block">{{ number_format($averageDiscountPercent, 1) }} %</span>
                            <span class="text-[9px] text-slate-500 mt-0.5 block">Sur le CA Brut total</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Discounts Table (Col span 2) -->
            <div class="kpi-card rounded-2xl p-5 lg:col-span-2">
                <h3 class="text-sm font-bold text-slate-200 font-display mb-4">Historique Journalier des Remises</h3>
                
                @if(empty($dailyDiscounts))
                    <p class="text-slate-500 text-xs py-8 text-center">Aucune remise accordée sur cette période.</p>
                @else
                    <div class="overflow-x-auto max-h-[280px] overflow-y-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-500 uppercase tracking-wider sticky top-0 bg-slate-900 py-2">
                                    <th class="pb-2">Date</th>
                                    <th class="pb-2 text-center">Nombre de Remises</th>
                                    <th class="pb-2 text-right">Montant des Remises</th>
                                    <th class="pb-2 text-right">Taux de Remise Moyen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                @foreach($dailyDiscounts as $dd)
                                    <tr class="hover:bg-slate-800/10 transition-colors">
                                        <td class="py-2.5 font-bold text-slate-300 font-mono">{{ $dd->date }}</td>
                                        <td class="py-2.5 text-center text-slate-300 font-semibold">{{ $dd->count }}</td>
                                        <td class="py-2.5 text-right font-bold text-amber-500 font-mono">{{ number_format($dd->amount, 0, '.', ' ') }} DA</td>
                                        <td class="py-2.5 text-right font-black text-rose-400 font-mono">{{ number_format($dd->percentage, 1) }} %</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

    </div>
</div>

<!-- Modal Saisie d'une charge -->
<div id="expenseModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-md bg-slate-900 border border-slate-700/80 rounded-2xl shadow-2xl overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-rose-950/50 via-slate-800/60 to-slate-800/40 p-5 border-b border-slate-700/60 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="h-9 w-9 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white font-display">Enregistrer une Charge</h3>
                    <p class="text-[11px] text-slate-400">Sortie de caisse ou dépense de la boutique</p>
                </div>
            </div>
            <button type="button" onclick="closeExpenseModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.expenses.store') }}" class="p-5 space-y-4">
            @csrf

            <!-- Montant -->
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                    Montant de la charge (DA) <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <input type="number" step="any" min="1" name="amount" id="expense_amount" required placeholder="0" 
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-2xl font-black font-mono text-rose-400 text-center focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-500 font-mono">DA</span>
                </div>
            </div>

            <!-- Catégorie / Motif -->
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                    Motif / Catégorie <span class="text-rose-400">*</span>
                </label>
                <select name="category" id="expense_category_select" onchange="toggleCustomCategory(this)" 
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-rose-500">
                    <option value="Salaire employé">💼 Salaire employé</option>
                    <option value="Avance sur salaire">💵 Avance sur salaire</option>
                    <option value="Loyer du local">🏢 Loyer du local</option>
                    <option value="Électricité, Eau & Gaz">⚡ Facture Sonelgaz / Eau</option>
                    <option value="Produits & Lessive">🧴 Produits de nettoyage & Lessive</option>
                    <option value="Maintenance & Réparations">🛠️ Maintenance & Réparation machine</option>
                    <option value="Fournitures & Emballage">📦 Cintres, Housses, Papier & Bobines</option>
                    <option value="Carburant & Transport">🚚 Carburant & Transport</option>
                    <option value="__custom__">📝 Autre motif personnalisé...</option>
                </select>
                <input type="text" id="custom_category_input" name="custom_category" placeholder="Précisez le motif..." 
                       class="hidden mt-2 w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-200 focus:outline-none focus:border-rose-500">
            </div>

            <!-- Date & Acteur -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                        Date <span class="text-rose-400">*</span>
                    </label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required 
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 font-mono focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                        Enregistré par
                    </label>
                    <select name="user_id" 
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-rose-500">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ Auth::id() === $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Bénéficiaire / Notes -->
            <div>
                <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                    Bénéficiaire / Description (Optionnel)
                </label>
                <input type="text" name="notes" placeholder="Ex: Paiement salaire Karim (septembre)" 
                       class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-200 focus:outline-none focus:border-rose-500">
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeExpenseModal()" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 transition-colors">
                    Annuler
                </button>
                <button type="submit" 
                        class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 shadow-lg shadow-rose-950/40 transition-all transform active:scale-95">
                    Enregistrer la charge
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<!-- Load Chart.js locally for offline support -->
<script src="{{ asset('js/chart.min.js') }}"></script>
<script>
    function openExpenseModal() {
        const modal = document.getElementById('expenseModal');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                const input = document.getElementById('expense_amount');
                if (input) input.focus();
            }, 100);
        }
    }

    function closeExpenseModal() {
        const modal = document.getElementById('expenseModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function toggleCustomCategory(select) {
        const customInput = document.getElementById('custom_category_input');
        if (customInput) {
            if (select.value === '__custom__') {
                customInput.classList.remove('hidden');
                customInput.focus();
                customInput.required = true;
            } else {
                customInput.classList.add('hidden');
                customInput.required = false;
            }
        }
    }

    // Close modal on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeExpenseModal();
        }
    });

    // Close modal when clicking outside
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('expenseModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'expenseModal') {
                closeExpenseModal();
            }
        });
    });

    document.addEventListener("DOMContentLoaded", () => {
        
        // 1. Sales Trend Line Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendLabels = @json($trendLabels);
        const trendData = @json($trendData);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels.length > 0 ? trendLabels : ['Aucune vente'],
                datasets: [{
                    label: 'Ventes Net (DA)',
                    data: trendData.length > 0 ? trendData : [0],
                    borderColor: '#6366f1', // Indigo 500
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#818cf8',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(148, 163, 184, 0.05)' },
                        ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' } }
                    },
                    x: {
                        grid: { color: 'rgba(148, 163, 184, 0.05)' },
                        ticks: { color: '#94a3b8', font: { size: 10 } }
                    }
                }
            }
        });

        // 2. Services Doughnut Chart
        const servicesCanvas = document.getElementById('servicesChart');
        if (servicesCanvas) {
            const servicesCtx = servicesCanvas.getContext('2d');
            const serviceLabels = @json($serviceLabels);
            const serviceRevenues = @json($serviceRevenues);
            const serviceColors = @json($serviceColors);

            new Chart(servicesCtx, {
                type: 'doughnut',
                data: {
                    labels: serviceLabels,
                    datasets: [{
                        data: serviceRevenues,
                        backgroundColor: serviceColors.slice(0, serviceLabels.length),
                        borderWidth: 2,
                        borderColor: '#1e293b' // Slate 800
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#cbd5e1',
                                font: { size: 10 }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>
@endsection
