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
    </div>

    <!-- Main Scrollable Dashboard Content -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6">
        
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
                <div class="flex items-center justify-between text-[10px] text-slate-500 mt-2">
                    <span>Soldes clients restants</span>
                </div>
            </div>

            <!-- KPI 4: Total Expenses -->
            <div class="kpi-card rounded-2xl p-4 border-l-4 border-l-rose-500">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Dépenses</p>
                <p class="text-xl font-black font-display text-rose-400 mt-2">
                    @if(is_null($totalExpenses))
                        N/A
                    @else
                        {{ number_format($totalExpenses, 0, '.', ' ') }} DA
                    @endif
                </p>
                <div class="flex items-center justify-between text-[10px] text-slate-500 mt-2">
                    <span>{{ is_null($totalExpenses) ? 'Charges non réparties' : 'Charges enregistrées' }}</span>
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

        <!-- Row 5: Analyse des Remises -->
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
@endsection

@section('scripts')
<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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
