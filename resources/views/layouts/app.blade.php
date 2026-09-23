<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900 text-slate-100" translate="no">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google" content="notranslate">

    <title>@yield('title', 'Caisse Tactile') - PARADOU</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect x=%2210%22 y=%225%22 width=%2280%22 height=%2290%22 rx=%2210%22 fill=%22%234f46e5%22/><line x1=%2210%22 y1=%2225%22 x2=%2290%22 y2=%2225%22 stroke=%22white%22 stroke-width=%225%22/><circle cx=%2225%22 cy=%2215%22 r=%224%22 fill=%22white%22/><circle cx=%2240%22 cy=%2215%22 r=%224%22 fill=%22white%22/><circle cx=%2255%22 cy=%2215%22 r=%224%22 fill=%22white%22/><circle cx=%2250%22 cy=%2260%22 r=%2220%22 fill=%22none%22 stroke=%22white%22 stroke-width=%228%22/><circle cx=%2250%22 cy=%2260%22 r=%2212%22 fill=%22none%22 stroke=%22white%22 stroke-width=%224%22 stroke-dasharray=%2210 5%22/></svg>">

    <!-- Tailwind & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .font-display {
            font-family: 'Outfit', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        /* Custom scrollbar for checkout */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.3);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.5);
        }

        /* ================= LIGHT THEME OVERRIDES ================= */
        .theme-light, .theme-light body {
            background-color: #f8fafc !important; /* slate-50 */
            color: #0f172a !important;            /* slate-900 */
        }

        /* Header */
        .theme-light header {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .theme-light header h1 {
            color: #0f172a !important;
        }
        .theme-light header p {
            color: #64748b !important;
        }
        .theme-light header span.text-indigo-400 {
            color: #4f46e5 !important;
        }
        .theme-light header a:not(.bg-indigo-600) {
            color: #475569 !important;
        }
        .theme-light header a:not(.bg-indigo-600):hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }
        .theme-light #clock-time, .theme-light #clock-date {
            color: #475569 !important;
        }
        .theme-light header .text-white {
            color: #0f172a !important;
        }

        /* Catalog Panel (Left) & Layout Content */
        .theme-light main {
            background-color: #f8fafc !important;
        }
        .theme-light .flex-1.bg-slate-900,
        .theme-light .bg-slate-900 {
            background-color: #f8fafc !important;
        }
        .theme-light .bg-slate-800\/40 {
            background-color: #ffffff !important; /* Sidebar card */
            border-left: 1px solid #e2e8f0;
        }
        .theme-light .border-slate-700\/50,
        .theme-light .border-slate-800 {
            border-color: #e2e8f0 !important;
        }

        /* Card and buttons */
        .theme-light .bg-slate-800 {
            background-color: #ffffff !important;
        }
        .theme-light .text-slate-100 {
            color: #0f172a !important; /* Pure black / dark text for card titles */
        }
        .theme-light .text-slate-200 {
            color: #1e293b !important;
        }
        .theme-light .text-slate-300 {
            color: #334155 !important;
        }
        .theme-light .text-slate-400 {
            color: #64748b !important;
        }
        .theme-light .text-slate-500 {
            color: #94a3b8 !important;
        }
        .theme-light .text-indigo-400 {
            color: #4f46e5 !important; /* Readable dark indigo for prices */
        }

        /* Category button active/inactive state */
        .theme-light .service-tab:not(.service-tab-active) {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #000000 !important;
        }
        .theme-light .service-tab:not(.service-tab-active):hover {
            background-color: #f1f5f9 !important;
            color: #000000 !important;
        }
        .theme-light .target-pill:not(.target-pill-active) {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #000000 !important;
        }
        .theme-light .target-pill:not(.target-pill-active):hover {
            background-color: #f1f5f9 !important;
            color: #000000 !important;
        }
        .theme-light .service-tab.service-tab-active {
            background-color: #4f46e5 !important; /* Lighter indigo */
            color: #ffffff !important;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.1) !important;
        }
        .theme-light .target-pill.target-pill-active {
            background-color: #334155 !important; /* Clean slate highlight */
            color: #ffffff !important;
            border-color: #4f46e5 !important;
        }

        /* Product Cards */
        .theme-light .bg-slate-850,
        .theme-light div.bg-slate-800\/60,
        .theme-light .bg-slate-900\/50 {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important;
        }
        .theme-light div.bg-slate-800\/60:hover {
            border-color: #cbd5e1 !important;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important;
        }
        .theme-light .text-white {
            color: #0f172a !important;
        }

        /* Sidebar / Cart */
        .theme-light .bg-slate-900 {
            background-color: #ffffff !important; /* Sidebar bottom recap */
        }
        .theme-light #cart-items-container {
            background-color: #f1f5f9 !important;
        }
        .theme-light #cart-items-container > div {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }
        .theme-light #cart-items-container .text-slate-200 {
            color: #0f172a !important;
        }
        .theme-light #cart-items-container .text-slate-400 {
            color: #64748b !important;
        }

        /* Inputs & Form Fields */
        .theme-light input, 
        .theme-light select,
        .theme-light textarea {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        .theme-light input::placeholder {
            color: #94a3b8 !important;
        }
        .theme-light #client-search-results {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .theme-light #client-search-results div:hover {
            background-color: #f1f5f9 !important;
        }

        /* Modals */
        .theme-light #options-modal > div,
        .theme-light #new-client-modal > div,
        .theme-light #order-modal > div,
        .theme-light #modal-printers-config > div,
        .theme-light #custom-alert-modal > div,
        .theme-light #checkout-payment-modal > div,
        .theme-light #ticket-preview-modal > div {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
        }
        .theme-light #options-modal h3,
        .theme-light #new-client-modal h3,
        .theme-light #order-modal h3,
        .theme-light #modal-printers-config h3,
        .theme-light #checkout-payment-modal h3,
        .theme-light #ticket-preview-modal h3,
        .theme-light #custom-alert-title,
        .theme-light #custom-alert-modal span {
            color: #0f172a !important;
        }
        .theme-light #options-modal .bg-slate-800,
        .theme-light #new-client-modal .bg-slate-800,
        .theme-light #order-modal .bg-slate-800,
        .theme-light #checkout-payment-modal .bg-slate-800,
        .theme-light #checkout-payment-modal .bg-slate-800\/90,
        .theme-light #ticket-preview-modal .bg-slate-800,
        .theme-light #ticket-preview-modal .bg-slate-800\/90,
        .theme-light #custom-alert-modal .bg-slate-800 {
            background-color: #ffffff !important;
        }
        .theme-light .cpay-key {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        .theme-light .cpay-key:hover {
            background-color: #e2e8f0 !important;
        }
        #ticket-preview-modal .overflow-y-auto {
            scrollbar-width: thin;
            scrollbar-color: #64748b rgba(15, 23, 42, 0.6);
        }
        #ticket-preview-modal .overflow-y-auto::-webkit-scrollbar {
            width: 10px;
            display: block;
        }
        #ticket-preview-modal .overflow-y-auto::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.7);
            border-radius: 5px;
        }
        #ticket-preview-modal .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 5px;
            border: 2px solid rgba(15, 23, 42, 0.7);
        }
        #ticket-preview-modal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        .theme-light #options-modal .option-badge:not(.bg-indigo-600) {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            border-color: #cbd5e1 !important;
        }

        /* Order follow-up table */
        .theme-light table {
            background-color: #ffffff !important;
        }
        .theme-light th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            border-bottom-color: #e2e8f0 !important;
        }
        .theme-light td {
            border-bottom-color: #f1f5f9 !important;
            color: #334155 !important;
        }
        .theme-light tr:hover td {
            background-color: #f8fafc !important;
        }
        .theme-light .bg-slate-900\/50 {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }

        /* Scrollbars overrides in light theme */
        .theme-light ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .theme-light ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
        }
        .theme-light ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Stats dashboard */
        .theme-light .grid > div {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .theme-light .grid > div .text-white {
            color: #0f172a !important;
        }

        /* Dropdown custom light theme styling */
        .theme-light .absolute.bg-slate-800 {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .theme-light .absolute.bg-slate-800 a {
            color: #475569 !important;
            border-color: #e2e8f0 !important;
        }
        .theme-light .absolute.bg-slate-800 a:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }
    </style>
    @yield('styles')
</head>
<body class="h-full antialiased overflow-hidden flex flex-col">

    <!-- Header bar (Slim, compact, responsive height) -->
    <header class="bg-slate-800/80 backdrop-blur border-b border-slate-700/50 px-4 sm:px-6 py-2 sm:py-2.5 flex items-center justify-between shrink-0">
        <div class="flex items-center space-x-3">
            <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-500/20">
                <span class="text-white font-bold font-display text-base">P</span>
            </div>
            <div>
                <h1 class="text-base font-black font-display tracking-wider text-white">
                    PARAD<svg class="h-4.5 w-4.5 inline-block text-indigo-400 align-middle -mt-1 mx-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><rect x="4" y="3" width="16" height="18" rx="2" /><line x1="4" y1="7" x2="20" y2="7" /><circle cx="7" cy="5" r="0.75" fill="currentColor" /><circle cx="10" cy="5" r="0.75" fill="currentColor" /><circle cx="13" cy="5" r="0.75" fill="currentColor" /><circle cx="12" cy="14" r="4" /><circle cx="12" cy="14" r="2.5" stroke-dasharray="3 2" /></svg>U <span class="text-indigo-400 font-medium text-[11px] font-sans tracking-normal lowercase">v2026</span>
                </h1>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="hidden md:flex space-x-1.5">
            <a href="{{ route('checkout.index') }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold font-display uppercase tracking-wide transition-colors {{ Request::routeIs('checkout.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                Caisse Tactile
            </a>
            <a href="{{ route('orders.index') }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold font-display uppercase tracking-wide transition-colors {{ Request::routeIs('orders.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                Suivi Commandes
            </a>
            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold font-display uppercase tracking-wide transition-colors {{ Request::routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                    Dashboard Admin
                </a>
                <div class="relative inline-block text-left">
                    <button id="gestion-dropdown-btn" class="px-3.5 py-1.5 rounded-lg text-xs font-bold font-display uppercase tracking-wide transition-colors text-slate-300 hover:text-white hover:bg-slate-700/50 flex items-center space-x-1 cursor-pointer">
                        <span>Gestion</span>
                        <svg id="gestion-dropdown-arrow" class="h-3 w-3 transition-transform duration-150" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="gestion-dropdown-menu" class="absolute left-0 mt-1 w-52 rounded-xl bg-slate-800 border border-slate-700/60 shadow-xl opacity-0 invisible transition-all duration-150 z-50 overflow-hidden">
                        <div class="py-1">
                            <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-700/50 transition-colors uppercase font-display">
                                Gestion Caissiers
                            </a>
                            <a href="{{ route('admin.clients.index') }}" class="block px-4 py-2 text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-700/50 transition-colors uppercase font-display border-t border-slate-700/30">
                                Gestion Clients
                            </a>
                            <a href="{{ route('admin.catalog.index') }}" class="block px-4 py-2 text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-700/50 transition-colors uppercase font-display border-t border-slate-700/30">
                                Catégories & Articles
                            </a>
                            <a href="{{ route('admin.prices.index') }}" class="block px-4 py-2 text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-700/50 transition-colors uppercase font-display border-t border-slate-700/30">
                                Gestion des Prix
                            </a>
                            <a href="{{ route('admin.rubrics.index') }}" class="block px-4 py-2 text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-700/50 transition-colors uppercase font-display border-t border-slate-700/30">
                                Rubriques (Dicos)
                            </a>
                            <button type="button" onclick="openGlobalBackupModal()" class="w-full text-left block px-4 py-2 text-xs font-bold text-indigo-400 hover:text-white hover:bg-slate-700/50 transition-colors uppercase font-display border-t border-slate-700/30 cursor-pointer">
                                💾 Sauvegardes Base
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </nav>

        <div class="flex items-center space-x-3 sm:space-x-4">
            <!-- Simple Round Dot Indicator (Green: Online / Red: Offline / Orange: Syncing) -->
            <div id="dual-mode-indicator" onclick="triggerManualSync()" class="cursor-pointer relative flex items-center justify-center p-1.5 rounded-full hover:bg-slate-700/40 transition-colors" title="En Ligne (Cloud) - Cliquer pour vérifier la synchronisation">
                <div id="dual-mode-dot" style="width: 13px; height: 13px; border-radius: 50%; background-color: #22c55e; box-shadow: 0 0 10px rgba(34, 197, 94, 0.9), 0 0 3px #22c55e; border: 2px solid #15803d; transition: all 0.3s ease;"></div>
                <span id="dual-mode-queue" class="hidden absolute -top-1 -right-1 bg-rose-600 text-white text-[9px] font-bold h-4 min-w-[16px] px-1 rounded-full flex items-center justify-center border border-slate-900 shadow">0</span>
            </div>

            <div class="h-6 w-px bg-slate-700/50"></div>

            <!-- Operator Status -->
            <div class="flex items-center space-x-2.5">
                <div class="h-7 w-7 rounded-full bg-slate-700 flex items-center justify-center border border-slate-600">
                    <svg class="h-3.5 w-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="text-left">
                    <div class="flex items-center space-x-1.5">
                        <p class="text-[11px] text-slate-400">Opérateur</p>
                        @if(Auth::check())
                            <span class="text-[9px] px-1.5 py-0.2 rounded font-bold uppercase {{ Auth::user()->role === 'admin' ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/20' : 'bg-slate-700 text-slate-300' }}">
                                {{ Auth::user()->role === 'admin' ? 'Admin' : 'Caissier' }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs font-semibold text-slate-200">{{ Auth::user() ? Auth::user()->name : 'Caisse 1' }}</p>
                </div>
            </div>

            <div class="h-6 w-px bg-slate-700/50"></div>

            <!-- Action Controls: Fullscreen + Theme + Logout -->
            <div class="flex items-center space-x-1.5">
                <!-- Printer Configuration Button -->
                <button onclick="openPrinterConfigModal()" id="printer-config-btn" class="p-1.5 text-slate-400 hover:text-indigo-400 transition-colors bg-slate-800 border border-slate-700 rounded-lg hover:border-indigo-500/20 hover:bg-indigo-500/5 cursor-pointer flex items-center justify-center" title="Configurer les Imprimantes (Reçus & Étiquettes)">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                </button>

                <!-- Fullscreen Toggle Button -->
                <button onclick="toggleAppFullscreen()" id="fullscreen-toggle-btn" class="p-1.5 text-slate-400 hover:text-indigo-400 transition-colors bg-slate-800 border border-slate-700 rounded-lg hover:border-indigo-500/20 hover:bg-indigo-500/5 cursor-pointer flex items-center justify-center" title="Plein Écran / Mode Kiosque (F11)">
                    <!-- Expand icon -->
                    <svg id="fullscreen-icon-expand" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    <!-- Compress icon (hidden by default) -->
                    <svg id="fullscreen-icon-compress" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9L4 4m0 0h5m-5 0v5m11-5l5 5m0-5h-5m5 0v5M9 15l-5 5m0 0h5m-5 0v-5m11 5l5-5m0 5h-5m5 0v-5" />
                    </svg>
                </button>

                @if(Auth::check())
                    <!-- Theme Toggle Button -->
                    <button onclick="toggleTheme()" class="p-1.5 text-slate-400 hover:text-indigo-400 transition-colors bg-slate-800 border border-slate-700 rounded-lg hover:border-indigo-500/20 hover:bg-indigo-500/5 cursor-pointer flex items-center justify-center" title="Changer de thème">
                        <!-- Sun Icon (visible in dark mode) -->
                        <svg id="theme-icon-sun" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M14 12a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <!-- Moon Icon (visible in light mode) -->
                        <svg id="theme-icon-moon" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 transition-colors bg-slate-800 border border-slate-700 rounded-lg hover:border-rose-500/20 hover:bg-rose-500/5 cursor-pointer flex items-center justify-center" title="Se déconnecter">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="flex-1 flex overflow-hidden bg-slate-900">
        @yield('content')
    </main>

    <!-- JavaScript Clock -->
    <script>
        function updateClock() {
            const now = new Date();
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            
            const timeEl = document.getElementById('clock-time');
            const dateEl = document.getElementById('clock-date');
            
            if(timeEl) timeEl.textContent = now.toLocaleTimeString('fr-FR', timeOptions);
            if(dateEl) {
                const dateStr = now.toLocaleDateString('fr-FR', dateOptions);
                dateEl.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Theme Toggle Functionality
        const savedTheme = localStorage.getItem('theme') || 'dark';
        if (savedTheme === 'light') {
            document.documentElement.classList.add('theme-light');
        }

        function applyTheme(theme) {
            const htmlEl = document.documentElement;
            const sunIcon = document.getElementById('theme-icon-sun');
            const moonIcon = document.getElementById('theme-icon-moon');
            
            if (theme === 'light') {
                htmlEl.classList.add('theme-light');
                if (sunIcon) sunIcon.classList.add('hidden');
                if (moonIcon) moonIcon.classList.remove('hidden');
            } else {
                htmlEl.classList.remove('theme-light');
                if (sunIcon) sunIcon.classList.remove('hidden');
                if (moonIcon) moonIcon.classList.add('hidden');
            }
        }

        function toggleTheme() {
            const currentTheme = localStorage.getItem('theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        }

        function initAppLayout() {
            applyTheme(localStorage.getItem('theme') || 'dark');

            // Gestion Dropdown functionality
            const btn = document.getElementById('gestion-dropdown-btn');
            const menu = document.getElementById('gestion-dropdown-menu');
            const arrow = document.getElementById('gestion-dropdown-arrow');

            if (btn && menu) {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = !menu.classList.contains('invisible');
                    if (isOpen) {
                        menu.classList.add('opacity-0', 'invisible');
                        menu.classList.remove('opacity-100', 'visible');
                        if (arrow) arrow.classList.remove('rotate-180');
                    } else {
                        menu.classList.remove('opacity-0', 'invisible');
                        menu.classList.add('opacity-100', 'visible');
                        if (arrow) arrow.classList.add('rotate-180');
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!btn.contains(e.target) && !menu.contains(e.target)) {
                        menu.classList.add('opacity-0', 'invisible');
                        menu.classList.remove('opacity-100', 'visible');
                        if (arrow) arrow.classList.remove('rotate-180');
                    }
                });
            }
        }

        if (document.readyState === 'loading') {
            window.addEventListener('DOMContentLoaded', initAppLayout);
        } else {
            initAppLayout();
        }

        // -------------------------------------------------------------
        // -------------------------------------------------------------
        // MULTI-PRINTER CONFIGURATION & SILENT ESC/POS ROUTING
        // -------------------------------------------------------------
        function getPrinterConfig() {
            try {
                const saved = localStorage.getItem('pos_printers_cfg');
                if (saved) return JSON.parse(saved);
            } catch(e) {}
            return {
                receiptPrinter: '',
                tagPrinter: '',
                autoPrintReceipt: true,
                autoPrintTags: true
            };
        }

        async function getMergedPrinterConfig() {
            let cfg = getPrinterConfig();
            if (window.posDesktop && typeof window.posDesktop.getConfig === 'function') {
                try {
                    const desktopCfg = await window.posDesktop.getConfig();
                    if (desktopCfg) {
                        cfg = { ...cfg, ...desktopCfg };
                        try {
                            localStorage.setItem('pos_printers_cfg', JSON.stringify(cfg));
                        } catch(e) {}
                    }
                } catch (e) {
                    console.warn('Erreur lecture desktop config:', e);
                }
            }
            return cfg;
        }

        // Synchronisation automatique de la configuration au chargement
        document.addEventListener('DOMContentLoaded', async () => {
            await getMergedPrinterConfig();
        });

        async function openPrinterConfigModal() {
            const modal = document.getElementById('modal-printers-config');
            const receiptSelect = document.getElementById('cfg-printer-receipt');
            const tagSelect = document.getElementById('cfg-printer-tags');
            const autoReceipt = document.getElementById('cfg-autoprint-receipt');
            const autoTags = document.getElementById('cfg-autoprint-tags');
            const banner = document.getElementById('printer-env-banner');
            
            if (!modal) return;

            // Load saved settings
            let cfg = await getMergedPrinterConfig();

            if (autoReceipt) autoReceipt.checked = cfg.autoPrintReceipt !== false;
            if (autoTags) autoTags.checked = cfg.autoPrintTags !== false;

            // Reset dropdowns
            receiptSelect.innerHTML = '<option value="">-- Imprimante par défaut de Windows --</option>';
            tagSelect.innerHTML = '<option value="">-- Imprimante par défaut de Windows --</option>';

            if (window.posDesktop && typeof window.posDesktop.getPrinters === 'function') {
                if (banner) {
                    banner.style.backgroundColor = '#1e1b4b';
                    banner.style.border = '1px solid #6366f1';
                    banner.innerHTML = `<svg class="h-5 w-5 text-indigo-300 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <div class="leading-relaxed">
                        <span class="font-bold text-white block mb-0.5">Impression Thermique Silencieuse (ESC/POS) :</span>
                        <span class="text-indigo-100 font-medium">Les impressions sont envoyées instantanément et directement aux imprimantes sélectionnées sans ouvrir de boîte de dialogue.</span>
                    </div>`;
                }
                try {
                    const printers = await window.posDesktop.getPrinters();
                    printers.forEach(p => {
                        const optR = document.createElement('option');
                        optR.value = p.name;
                        optR.textContent = `${p.displayName || p.name} ${p.isDefault ? '(Par défaut)' : ''}`;
                        if (cfg.receiptPrinter === p.name) optR.selected = true;
                        receiptSelect.appendChild(optR);

                        const optT = document.createElement('option');
                        optT.value = p.name;
                        optT.textContent = `${p.displayName || p.name} ${p.isDefault ? '(Par défaut)' : ''}`;
                        if (cfg.tagPrinter === p.name) optT.selected = true;
                        tagSelect.appendChild(optT);
                    });
                } catch (err) {
                    console.error('Error fetching system printers:', err);
                }
            } else {
                if (banner) {
                    banner.style.backgroundColor = '#451a03';
                    banner.style.border = '1px solid #d97706';
                    banner.innerHTML = `<svg class="h-5 w-5 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <div class="leading-relaxed text-amber-100">
                        <span class="font-bold text-white block mb-0.5">Mode Navigateur Web :</span>
                        Pour bénéficier de l'impression thermique silencieuse directe (ESC/POS) et gérer deux imprimantes distinctes en simultané, lancez l'application <b>Paradou POS Desktop</b>.
                    </div>`;
                }
            }

            modal.style.display = 'flex';
        }

        function closePrinterConfigModal() {
            const modal = document.getElementById('modal-printers-config');
            if (modal) modal.style.display = 'none';
        }

        async function savePrinterConfig() {
            const receiptPrinter = document.getElementById('cfg-printer-receipt').value;
            const tagPrinter = document.getElementById('cfg-printer-tags').value;
            const autoPrintReceipt = document.getElementById('cfg-autoprint-receipt').checked;
            const autoPrintTags = document.getElementById('cfg-autoprint-tags').checked;

            const cfg = {
                receiptPrinter,
                tagPrinter,
                autoPrintReceipt,
                autoPrintTags
            };

            localStorage.setItem('pos_printers_cfg', JSON.stringify(cfg));

            if (window.posDesktop && typeof window.posDesktop.saveConfig === 'function') {
                try {
                    const current = await window.posDesktop.getConfig();
                    await window.posDesktop.saveConfig({ ...current, ...cfg });
                } catch(e) {
                    console.error('Erreur sauvegarde config Desktop:', e);
                }
            }

            closePrinterConfigModal();
            showAppAlert("Configuration des imprimantes enregistrée avec succès !", "success", "Imprimantes Configurées");
        }

        async function testPrinter(role) {
            const cfg = await getMergedPrinterConfig();
            const targetPrinter = role === 'receipt' 
                ? (document.getElementById('cfg-printer-receipt')?.value || cfg.receiptPrinter)
                : (document.getElementById('cfg-printer-tags')?.value || cfg.tagPrinter);

            const now = new Date().toLocaleTimeString('fr-FR');
            let testHtml = '';

            if (role === 'receipt') {
                testHtml = '<div style="font-family: monospace; width: 72mm; padding: 10px; font-size: 13px; text-align: center; color: #000;">'
                    + '<h2 style="margin: 5px 0; font-size: 16px; font-weight: 900;">PARADOU PRESSING</h2>'
                    + '<div>*** TEST IMPRIMANTE REÇU CLIENT ***</div>'
                    + '<div style="border-top: 1px dashed #000; margin: 8px 0;"></div>'
                    + '<p style="margin: 4px 0;">Périphérique : <b>' + (targetPrinter || 'Défaut Windows') + '</b></p>'
                    + '<p style="margin: 4px 0;">Heure du test : ' + now + '</p>'
                    + '<p style="color: green; font-weight: bold; margin: 6px 0;">STATUT : CONNEXION RÉUSSIE !</p>'
                    + '<div style="border-top: 1px dashed #000; margin: 8px 0;"></div>'
                    + '<p style="font-size: 11px; margin: 0;">Imprimante dédiée aux Factures & Reçus Clients 80mm.</p>'
                    + '</div>';
            } else {
                testHtml = '<div style="font-family: Arial, sans-serif; width: 72mm; padding: 6px; font-size: 12px; text-align: center; color: #000;">'
                    + '<div style="border: 2px solid #000; padding: 6px; border-radius: 4px;">'
                    + '<div style="font-size: 10px; font-weight: bold; text-transform: uppercase;">PARADOU - ÉTIQUETTE CINTRE</div>'
                    + '<div style="background:#000; color:#fff; font-size: 28px; font-weight: 900; margin: 3px 0; padding: 2px 0; border-radius: 3px;">#TEST</div>'
                    + '<div style="border-top: 1px dashed #000; margin: 5px 0;"></div>'
                    + '<p style="margin: 2px 0; font-weight: 900; font-size: 12px;">1x COSTUME 2 PIÈCES</p>'
                    + '<p style="margin: 2px 0; font-size: 10px; font-weight: bold;">PRESSING | Nbr Pieces=2</p>'
                    + '<div style="border-top: 1px dashed #000; margin: 5px 0;"></div>'
                    + '<p style="font-size: 10px; margin: 0; font-weight: bold;">TEST OK : 1 SEUL TICKET ADAPTÉ</p>'
                    + '</div></div>';
            }

            if (window.posDesktop && typeof window.posDesktop.silentPrint === 'function') {
                try {
                    const res = await window.posDesktop.silentPrint(testHtml, targetPrinter);
                    if (res && res.success === false) {
                        showAppAlert(`Échec du test : ${res.error || 'Erreur inconnue'}`, "error", "Erreur d'impression");
                    } else {
                        showAppAlert(`Ticket test envoyé avec succès à l'imprimante "${targetPrinter || 'Par défaut'}" !`, "success", "Test Réussi");
                    }
                } catch(e) {
                    showAppAlert(`Erreur lors du test : ${e.message}`, "error", "Erreur");
                }
            } else {
                // Browser popup fallback
                const win = window.open('', '_blank', 'width=400,height=500');
                if (win) {
                    win.document.write(testHtml);
                    win.document.close();
                    win.focus();
                    setTimeout(() => { win.print(); }, 300);
                }
            }
        }

        // Global function for printing without opening new windows (Multi-Printer Silent ESC/POS Routing)
        async function printOrder(orderId, type = 'all') {
            const cfg = await getMergedPrinterConfig();

            // 1. Silent ESC/POS Printing via Electron Desktop
            if (window.posDesktop && typeof window.posDesktop.silentPrint === 'function') {
                try {
                    // Type: 'ticket' -> Ticket Reçu Client (Facture comptoir)
                    if (type === 'ticket') {
                        const r = await fetch(`/orders/${orderId}/print-ticket`);
                        const html = await r.text();
                        await window.posDesktop.silentPrint(html, cfg.receiptPrinter || '');
                        return;
                    }

                    // Type: 'tags' -> Étiquette Cintre Unique Adaptée
                    if (type === 'tags') {
                        const r = await fetch(`/orders/${orderId}/print-tags`);
                        const html = await r.text();
                        await window.posDesktop.silentPrint(html, cfg.tagPrinter || '');
                        return;
                    }

                    // Type: 'all' -> Validation commande / Impression automatique
                    if (type === 'all') {
                        // Cas A: Deux imprimantes distinctes sont configurées (Reçu + Étiquettes)
                        if (cfg.receiptPrinter && cfg.tagPrinter && cfg.receiptPrinter !== cfg.tagPrinter) {
                            if (cfg.autoPrintReceipt !== false) {
                                fetch(`/orders/${orderId}/print-ticket`)
                                    .then(r => r.text())
                                    .then(html => window.posDesktop.silentPrint(html, cfg.receiptPrinter))
                                    .catch(e => console.error('Erreur print ticket:', e));
                            }
                            if (cfg.autoPrintTags !== false) {
                                fetch(`/orders/${orderId}/print-tags`)
                                    .then(r => r.text())
                                    .then(html => window.posDesktop.silentPrint(html, cfg.tagPrinter))
                                    .catch(e => console.error('Erreur print tags:', e));
                            }
                            return;
                        }

                        // Cas B: Seule l'imprimante d'étiquettes / cintres est configurée (ex: Xprinter XP-420B)
                        if (!cfg.receiptPrinter && cfg.tagPrinter) {
                            if (cfg.autoPrintTags !== false) {
                                const r = await fetch(`/orders/${orderId}/print-tags`);
                                const html = await r.text();
                                await window.posDesktop.silentPrint(html, cfg.tagPrinter);
                            }
                            return;
                        }

                        // Cas C: Seule l'imprimante de reçu est configurée
                        if (cfg.receiptPrinter && !cfg.tagPrinter) {
                            if (cfg.autoPrintReceipt !== false) {
                                const r = await fetch(`/orders/${orderId}/print-ticket`);
                                const html = await r.text();
                                await window.posDesktop.silentPrint(html, cfg.receiptPrinter);
                            }
                            return;
                        }

                        // Cas D: Imprimante par défaut Windows (ou pas encore configurée)
                        // On envoie exclusivement l'étiquette cintre unique adaptée
                        if (cfg.autoPrintTags !== false) {
                            const r = await fetch(`/orders/${orderId}/print-tags`);
                            const html = await r.text();
                            await window.posDesktop.silentPrint(html, cfg.tagPrinter || cfg.receiptPrinter || '');
                        }
                        return;
                    }
                } catch (err) {
                    console.error('Erreur silent print Electron:', err);
                }
            }

            // 2. Fallback Navigateur Standard (Offscreen iframe)
            const targetType = (type === 'all') ? 'tags' : type;
            const oldIframe = document.getElementById('global-print-iframe');
            if (oldIframe) {
                oldIframe.remove();
            }
            const iframe = document.createElement('iframe');
            iframe.id = 'global-print-iframe';
            iframe.style.position = 'fixed';
            iframe.style.left = '-9999px';
            iframe.style.top = '-9999px';
            iframe.style.width = '76mm';
            iframe.style.height = '1000px';
            iframe.style.border = 'none';
            iframe.style.zIndex = '-9999';
            iframe.src = `/orders/${orderId}/print-${targetType}`;
            
            iframe.onload = function() {
                setTimeout(() => {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        console.error('Erreur impression iframe, ouverture fenetre secours:', e);
                        window.open(`/orders/${orderId}/print-${targetType}`, '_blank', 'width=320,height=600');
                    }
                }, 500);
            };

        }

        // Custom Alert Logic
        let customAlertCallback = null;

        function showAppAlert(message, type = 'info', title = 'Notification', callback = null) {
            const modal = document.getElementById('custom-alert-modal');
            const msgEl = document.getElementById('custom-alert-message');
            const titleEl = document.getElementById('custom-alert-title');
            const container = document.getElementById('custom-alert-icon-container');
            
            const iconSuccess = document.getElementById('custom-alert-icon-success');
            const iconError = document.getElementById('custom-alert-icon-error');
            const iconInfo = document.getElementById('custom-alert-icon-info');
            
            msgEl.textContent = message;
            titleEl.textContent = title;
            customAlertCallback = callback;
            
            iconSuccess.classList.add('hidden');
            iconError.classList.add('hidden');
            iconInfo.classList.add('hidden');
            
            container.className = "h-12 w-12 rounded-full flex items-center justify-center mb-4";
            
            if (type === 'success') {
                iconSuccess.classList.remove('hidden');
                container.classList.add('bg-emerald-500/10');
            } else if (type === 'error') {
                iconError.classList.remove('hidden');
                container.classList.add('bg-rose-500/10');
            } else {
                iconInfo.classList.remove('hidden');
                container.classList.add('bg-indigo-500/10');
            }
            
            modal.classList.remove('hidden');
            modal.querySelector('.transform').classList.remove('scale-95');
            modal.querySelector('.transform').classList.add('scale-100');
            
            document.getElementById('custom-alert-ok-btn').focus();
        }

        function closeAppAlert() {
            const modal = document.getElementById('custom-alert-modal');
            modal.querySelector('.transform').classList.remove('scale-100');
            modal.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                if (customAlertCallback) {
                    customAlertCallback();
                    customAlertCallback = null;
                }
            }, 100);
        }

        // ================= FULLSCREEN LOGIC =================
        function toggleAppFullscreen() {
            if (window.posDesktop && typeof window.posDesktop.toggleFullscreen === 'function') {
                window.posDesktop.toggleFullscreen().then(isFull => {
                    updateFullscreenIcons(isFull);
                }).catch(() => {
                    fallbackHtmlFullscreen();
                });
            } else {
                fallbackHtmlFullscreen();
            }
        }

        function fallbackHtmlFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => updateFullscreenIcons(true)).catch(() => {});
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().then(() => updateFullscreenIcons(false)).catch(() => {});
                }
            }
        }

        function updateFullscreenIcons(isFull) {
            const expand = document.getElementById('fullscreen-icon-expand');
            const compress = document.getElementById('fullscreen-icon-compress');
            if (expand && compress) {
                if (isFull) {
                    expand.classList.add('hidden');
                    compress.classList.remove('hidden');
                } else {
                    expand.classList.remove('hidden');
                    compress.classList.add('hidden');
                }
            }
        }

        document.addEventListener('fullscreenchange', () => {
            updateFullscreenIcons(!!document.fullscreenElement);
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'F11') {
                e.preventDefault();
                toggleAppFullscreen();
            }
        });

        // ================= DUAL-MODE ONLINE / OFFLINE WATCHDOG =================
        window.isAppSyncing = false;

        function updateDualModeStatus() {
            const btn = document.getElementById('dual-mode-indicator');
            const dot = document.getElementById('dual-mode-dot');
            const queueBadge = document.getElementById('dual-mode-queue');
            if (!btn || !dot) return;

            const queue = JSON.parse(localStorage.getItem('pos_pending_offline_orders') || '[]');
            const isOnline = navigator.onLine;

            if (queue.length > 0 && queueBadge) {
                queueBadge.classList.remove('hidden');
                queueBadge.textContent = queue.length;
            } else if (queueBadge) {
                queueBadge.classList.add('hidden');
            }

            // Vert: En Ligne | Rouge: Offline | Orange: Synchronisation
            if (!isOnline) {
                dot.style.backgroundColor = '#ef4444';
                dot.style.boxShadow = '0 0 10px rgba(239, 68, 68, 0.9), 0 0 3px #ef4444';
                dot.style.borderColor = '#b91c1c';
                dot.className = "animate-pulse";
                btn.title = "Hors-Ligne (Mode Secours) - " + (queue.length ? queue.length + " commande(s) locale(s) en attente" : "Connexion coupée");
            } else if (window.isAppSyncing) {
                dot.style.backgroundColor = '#f97316';
                dot.style.boxShadow = '0 0 10px rgba(249, 115, 22, 0.9), 0 0 3px #f97316';
                dot.style.borderColor = '#c2410c';
                dot.className = "animate-ping";
                btn.title = "Synchronisation avec le Cloud en cours...";
            } else {
                dot.style.backgroundColor = '#22c55e';
                dot.style.boxShadow = '0 0 10px rgba(34, 197, 94, 0.9), 0 0 3px #22c55e';
                dot.style.borderColor = '#15803d';
                dot.className = "";
                btn.title = "En Ligne (Cloud) - Tout est synchronisé";
            }
        }

        function triggerManualSync() {
            if (!navigator.onLine) {
                showAppAlert("La caisse fonctionne actuellement en MODE SECOURS HORS-LIGNE.\n\nVos commandes et tickets sont sécurisés localement et seront synchronisés automatiquement dès le rétablissement de la connexion Internet.", "info", "Mode Secours Actif");
                return;
            }
            const queue = JSON.parse(localStorage.getItem('pos_pending_offline_orders') || '[]');
            if (queue.length === 0) {
                showAppAlert("Toutes les commandes sont à jour et synchronisées avec le Cloud !", "success", "En Ligne & Synchronisé");
                return;
            }
            window.isAppSyncing = true;
            updateDualModeStatus();
            
            if (typeof syncOfflineOrdersIfAny === 'function') {
                syncOfflineOrdersIfAny();
            }

            setTimeout(() => {
                window.isAppSyncing = false;
                updateDualModeStatus();
                showAppAlert(`${queue.length} commande(s) transmise(s) au Cloud avec succès !`, "success", "Synchronisation Réussie");
            }, 1500);
        }

        window.addEventListener('online', () => {
            updateDualModeStatus();
            if (typeof syncOfflineOrdersIfAny === 'function') syncOfflineOrdersIfAny();
        });
        window.addEventListener('offline', updateDualModeStatus);
        setInterval(updateDualModeStatus, 4000);
        document.addEventListener('DOMContentLoaded', updateDualModeStatus);

        // -------------------------------------------------------------
        // TICKET PREVIEW MODAL (RECEIPT & HANGER TAGS)
        // -------------------------------------------------------------
        let currentPreviewOrderId = null;
        let currentPreviewType = 'ticket';

        window.openTicketPreview = function(orderId, ticketNumber = '', defaultType = 'ticket') {
            if (!orderId) {
                console.warn('openTicketPreview appelé sans identifiant de commande.');
                return;
            }
            currentPreviewOrderId = orderId;
            currentPreviewType = defaultType || 'ticket';

            const modal = document.getElementById('ticket-preview-modal');
            const ticketNoElem = document.getElementById('preview-ticket-number');
            if (ticketNoElem) {
                ticketNoElem.textContent = ticketNumber ? `#${ticketNumber}` : `#${orderId}`;
            }

            if (modal) {
                modal.classList.remove('hidden');
                modal.style.setProperty('display', 'flex', 'important');
            }

            try {
                switchPreviewTab(defaultType || 'ticket');
            } catch (err) {
                console.error('Erreur switchPreviewTab:', err);
            }
        };

        window.closeTicketPreview = function() {
            const modal = document.getElementById('ticket-preview-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.setProperty('display', 'none', 'important');
            }
            const iframe = document.getElementById('ticket-preview-iframe');
            if (iframe) {
                iframe.src = 'about:blank';
            }
            currentPreviewOrderId = null;
        };

        // Close ticket preview modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                const modal = document.getElementById('ticket-preview-modal');
                if (modal && modal.style.display !== 'none' && !modal.classList.contains('hidden')) {
                    closeTicketPreview();
                }
            }
        });

        window.switchPreviewTab = function(type) {
            currentPreviewType = type;
            const tabTicket = document.getElementById('tab-preview-ticket');
            const tabTags = document.getElementById('tab-preview-tags');
            const iframe = document.getElementById('ticket-preview-iframe');
            const printLabel = document.getElementById('preview-print-btn-label');

            if (type === 'ticket') {
                if (tabTicket) {
                    tabTicket.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 cursor-pointer bg-indigo-600 text-white shadow-sm shadow-indigo-600/30';
                }
                if (tabTags) {
                    tabTags.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 cursor-pointer bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700';
                }
                if (iframe) {
                    iframe.src = `/orders/${currentPreviewOrderId}/print-ticket?preview=1`;
                }
                if (printLabel) printLabel.textContent = 'Imprimer le Reçu';
            } else {
                if (tabTags) {
                    tabTags.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 cursor-pointer bg-amber-600 text-white shadow-sm shadow-amber-600/30';
                }
                if (tabTicket) {
                    tabTicket.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 cursor-pointer bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700';
                }
                if (iframe) {
                    iframe.src = `/orders/${currentPreviewOrderId}/print-tags?preview=1`;
                }
                if (printLabel) printLabel.textContent = 'Imprimer le Cintre';
            }

            if (iframe) {
                iframe.onload = () => {
                    try {
                        const doc = iframe.contentDocument || iframe.contentWindow.document;
                        if (doc) {
                            // Forward Escape key pressed inside iframe to close modal
                            doc.addEventListener('keydown', (e) => {
                                if (e.key === 'Escape' || e.key === 'Esc') {
                                    closeTicketPreview();
                                }
                            });
                        }
                    } catch(e) {}
                };
            }
        };

        window.printFromPreview = function() {
            if (currentPreviewOrderId) {
                printOrder(currentPreviewOrderId, currentPreviewType);
            }
        };
    </script>

    <!-- Modal Configuration Imprimantes Multi-Rôles -->
    <div id="modal-printers-config" class="fixed inset-0 items-center justify-center p-4" style="display: none; background: rgba(7, 11, 25, 0.94); backdrop-filter: blur(28px) saturate(180%); -webkit-backdrop-filter: blur(28px) saturate(180%); z-index: 999998;">
        <div class="rounded-2xl w-full max-w-lg p-6 overflow-hidden flex flex-col space-y-5 text-left" style="background-color: #0f172a; border: 1px solid rgba(99, 102, 241, 0.4); box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.95), 0 0 40px rgba(79, 70, 229, 0.25);">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-700/90">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-xl bg-indigo-600/30 text-indigo-400 flex items-center justify-center border border-indigo-500/40 shadow-inner">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white font-display tracking-wide">Configuration des Imprimantes</h3>
                        <p class="text-xs text-indigo-200 font-medium">Routage automatique des tickets de caisse et des étiquettes cintres</p>
                    </div>
                </div>
                <button type="button" onclick="closePrinterConfigModal()" class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition-colors cursor-pointer" title="Fermer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Banner info for Desktop vs Browser -->
            <div id="printer-env-banner" class="rounded-xl p-3.5 flex items-start space-x-3 text-xs shadow-md" style="background-color: #1e1b4b; border: 1px solid #6366f1;">
                <svg class="h-5 w-5 text-indigo-300 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div class="leading-relaxed">
                    <span class="font-bold text-white block mb-0.5">Impression Thermique Silencieuse (ESC/POS) :</span>
                    <span class="text-indigo-100 font-medium">Les impressions sont envoyées instantanément et directement aux imprimantes sélectionnées sans ouvrir de boîte de dialogue.</span>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Imprimante 1 : Reçus Clients (Ticket Facture 80mm) -->
                <div class="p-4 rounded-xl space-y-3 shadow-md" style="background-color: #1e293b; border: 1px solid #334155;">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black text-white uppercase tracking-wider flex items-center space-x-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399] inline-block"></span>
                            <span>1. Imprimante Reçu Client (Facture Comptoir)</span>
                        </label>
                        <span class="text-xs font-bold text-emerald-300 bg-emerald-950/90 border border-emerald-500/50 px-2.5 py-0.5 rounded-md">80mm</span>
                    </div>
                    <div class="flex space-x-2">
                        <select id="cfg-printer-receipt" class="flex-1 rounded-xl px-3.5 py-2.5 text-xs text-white font-medium focus:outline-none transition-colors shadow-inner" style="background-color: #0b0f19; border: 1px solid #475569;">
                            <option value="">-- Imprimante par défaut de Windows --</option>
                        </select>
                        <button type="button" onclick="testPrinter('receipt')" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-indigo-600/30 active:scale-95 flex items-center space-x-1.5 shrink-0 cursor-pointer" title="Lancer un ticket test">
                            <span>Test Reçu</span>
                        </button>
                    </div>
                    <p class="text-xs text-slate-200 font-normal">Ticket remis au client au comptoir lors du dépôt et du retrait des vêtements.</p>
                </div>

                <!-- Imprimante 2 : Étiquettes Laverie / Cintres -->
                <div class="p-4 rounded-xl space-y-3 shadow-md" style="background-color: #1e293b; border: 1px solid #334155;">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black text-white uppercase tracking-wider flex items-center space-x-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-indigo-400 shadow-[0_0_8px_#818cf8] inline-block"></span>
                            <span>2. Imprimante Étiquettes Laverie (Cintres / Vêtements)</span>
                        </label>
                        <span class="text-xs font-bold text-indigo-300 bg-indigo-950/90 border border-indigo-500/50 px-2.5 py-0.5 rounded-md">80mm / 58mm</span>
                    </div>
                    <div class="flex space-x-2">
                        <select id="cfg-printer-tags" class="flex-1 rounded-xl px-3.5 py-2.5 text-xs text-white font-medium focus:outline-none transition-colors shadow-inner" style="background-color: #0b0f19; border: 1px solid #475569;">
                            <option value="">-- Imprimante par défaut de Windows --</option>
                        </select>
                        <button type="button" onclick="testPrinter('tags')" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-indigo-600/30 active:scale-95 flex items-center space-x-1.5 shrink-0 cursor-pointer" title="Lancer une étiquette test">
                            <span>Test Étiquette</span>
                        </button>
                    </div>
                    <p class="text-xs text-slate-200 font-normal">Étiquettes agrafées sur les cintres avec le gros numéro de commande pour l'atelier.</p>
                </div>

                <!-- Options automatiques -->
                <div class="p-4 rounded-xl space-y-2.5 shadow-md" style="background-color: #1e293b; border: 1px solid #334155;">
                    <span class="text-xs font-bold text-white tracking-wide">Comportement lors de l'encaissement d'une commande :</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <label class="flex items-center space-x-2.5 cursor-pointer text-xs font-medium text-slate-100 hover:text-white transition-colors">
                            <input type="checkbox" id="cfg-autoprint-receipt" checked class="rounded border-slate-500 text-indigo-600 focus:ring-indigo-500 bg-slate-900 h-4 w-4">
                            <span>Imprimer automatiquement le Reçu</span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer text-xs font-medium text-slate-100 hover:text-white transition-colors">
                            <input type="checkbox" id="cfg-autoprint-tags" checked class="rounded border-slate-500 text-indigo-600 focus:ring-indigo-500 bg-slate-900 h-4 w-4">
                            <span>Imprimer automatiquement les Étiquettes</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-700/90">
                <button type="button" onclick="closePrinterConfigModal()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-600 transition-colors cursor-pointer">
                    Annuler
                </button>
                <button type="button" onclick="savePrinterConfig()" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-extrabold font-display uppercase tracking-wider rounded-xl shadow-lg shadow-indigo-600/40 active:scale-95 transition-all cursor-pointer">
                    Enregistrer la Configuration
                </button>
            </div>
        </div>
    </div>

    <!-- ================= TICKET PREVIEW MODAL ================= -->
    <div id="ticket-preview-modal" class="fixed inset-0 items-center justify-center p-3 sm:p-4" style="display: none; background-color: rgba(2, 6, 23, 0.88); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 9999999;" onclick="closeTicketPreview()">
        <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-[480px] max-h-[92vh] flex flex-col shadow-2xl overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="px-5 py-3.5 bg-slate-800/90 border-b border-slate-700/80 flex justify-between items-center shrink-0">
                <div class="flex items-center space-x-2.5">
                    <span class="p-1.5 rounded-lg bg-sky-500/10 text-sky-400 border border-sky-500/20">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold text-white font-display flex items-center space-x-2">
                            <span>Aperçu Impression</span>
                            <span id="preview-ticket-number" class="text-sky-400 font-mono text-xs px-2 py-0.5 rounded bg-sky-950/80 border border-sky-500/30">#...</span>
                        </h3>
                    </div>
                </div>
                <button type="button" onclick="closeTicketPreview()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-700/60 transition-colors cursor-pointer" title="Fermer (Échap)">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Tab Switcher -->
            <div class="px-5 py-2.5 bg-slate-800/50 border-b border-slate-700/60 flex items-center justify-between shrink-0">
                <div class="flex space-x-2">
                    <button type="button" id="tab-preview-ticket" onclick="switchPreviewTab('ticket')" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 cursor-pointer bg-indigo-600 text-white shadow-sm shadow-indigo-600/30">
                        <span>🧾 Reçu Client</span>
                    </button>
                    <button type="button" id="tab-preview-tags" onclick="switchPreviewTab('tags')" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 cursor-pointer bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700">
                        <span>🏷️ Étiquette Cintre (80×50)</span>
                    </button>
                </div>
                <span class="text-[10px] text-slate-400 font-mono hidden sm:inline">Format 80mm</span>
            </div>

            <!-- Iframe Container with native dedicated scrollbar -->
            <div class="p-3 sm:p-4 flex-1 flex justify-center items-center bg-slate-950/70 overflow-hidden">
                <div class="bg-white rounded-xl shadow-2xl border border-slate-700/80 overflow-hidden flex flex-col" style="width: 360px; max-width: 100%; height: 500px; max-height: calc(85vh - 140px);">
                    <iframe id="ticket-preview-iframe" class="w-full h-full border-0" style="display: block; width: 100%; height: 100%; background: #ffffff;" src="about:blank"></iframe>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 bg-slate-800/90 border-t border-slate-700/80 flex items-center justify-between shrink-0">
                <button type="button" onclick="closeTicketPreview()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold rounded-xl border border-slate-600 transition-colors cursor-pointer">
                    Fermer
                </button>
                <div class="flex space-x-2">
                    <button type="button" onclick="printFromPreview()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-extrabold rounded-xl shadow-md shadow-indigo-600/30 active:scale-95 transition-all flex items-center space-x-1.5 cursor-pointer">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span id="preview-print-btn-label">Imprimer ce ticket</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Alert Modal Overlay -->
    <div id="custom-alert-modal" class="hidden fixed inset-0 bg-slate-950/70 flex items-center justify-center p-4" style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 999999;">
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl w-[380px] p-6 flex flex-col items-center shadow-2xl overflow-hidden transform scale-100 transition-all text-center">
            <!-- Logo Header -->
            <div class="flex items-center space-x-2 mb-4">
                <svg class="h-6 w-6 stroke-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
                    <rect x="4" y="3" width="16" height="18" rx="2" />
                    <line x1="4" y1="7" x2="20" y2="7" />
                    <circle cx="7" cy="5" r="0.75" fill="#4f46e5" />
                    <circle cx="10" cy="5" r="0.75" fill="#4f46e5" />
                    <circle cx="13" cy="5" r="0.75" fill="#4f46e5" />
                    <circle cx="12" cy="14" r="4" />
                    <circle cx="12" cy="14" r="2.5" stroke-dasharray="3 2" />
                </svg>
                <span class="text-sm font-black text-slate-100 uppercase tracking-wider font-display">PARADOU</span>
            </div>
            
            <!-- Alert Icon/Status -->
            <div id="custom-alert-icon-container" class="h-12 w-12 rounded-full flex items-center justify-center mb-4">
                <!-- Success Icon -->
                <svg id="custom-alert-icon-success" class="h-6 w-6 text-emerald-400 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <!-- Error Icon -->
                <svg id="custom-alert-icon-error" class="h-6 w-6 text-rose-400 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <!-- Info Icon -->
                <svg id="custom-alert-icon-info" class="h-6 w-6 text-indigo-400 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- Alert Message -->
            <h4 id="custom-alert-title" class="text-sm font-bold text-slate-100 mb-2 font-display">Notification</h4>
            <p id="custom-alert-message" class="text-xs text-slate-400 leading-relaxed mb-6">Message...</p>

            <!-- Close button -->
            <button id="custom-alert-ok-btn" onclick="closeAppAlert()" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-display font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-indigo-600/10 active:translate-y-0.5 transition-all cursor-pointer">
                D'accord
            </button>
        </div>
    </div>

    <!-- Admin Database Backup Modal -->
    @if(Auth::check() && Auth::user()->role === 'admin')
        @include('admin.backups.modal')
    @endif

    @yield('scripts')
</body>
</html>
