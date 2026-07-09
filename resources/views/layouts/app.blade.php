<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Caisse Tactile') - PARADOU</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect x=%2210%22 y=%225%22 width=%2280%22 height=%2290%22 rx=%2210%22 fill=%22%234f46e5%22/><line x1=%2210%22 y1=%2225%22 x2=%2290%22 y2=%2225%22 stroke=%22white%22 stroke-width=%225%22/><circle cx=%2225%22 cy=%2215%22 r=%224%22 fill=%22white%22/><circle cx=%2240%22 cy=%2215%22 r=%224%22 fill=%22white%22/><circle cx=%2255%22 cy=%2215%22 r=%224%22 fill=%22white%22/><circle cx=%2250%22 cy=%2260%22 r=%2220%22 fill=%22none%22 stroke=%22white%22 stroke-width=%228%22/><circle cx=%2250%22 cy=%2260%22 r=%2212%22 fill=%22none%22 stroke=%22white%22 stroke-width=%224%22 stroke-dasharray=%2210 5%22/></svg>">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
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
        .theme-light #custom-alert-modal > div {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
        }
        .theme-light #options-modal h3,
        .theme-light #new-client-modal h3,
        .theme-light #order-modal h3,
        .theme-light #custom-alert-title,
        .theme-light #custom-alert-modal span {
            color: #0f172a !important;
        }
        .theme-light #options-modal .bg-slate-800,
        .theme-light #new-client-modal .bg-slate-800,
        .theme-light #order-modal .bg-slate-800,
        .theme-light #custom-alert-modal .bg-slate-800 {
            background-color: #ffffff !important;
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
    </style>
    @yield('styles')
</head>
<body class="h-full antialiased overflow-hidden flex flex-col">

    <!-- Header bar -->
    <header class="bg-slate-800/80 backdrop-blur border-b border-slate-700/50 px-6 py-4 flex items-center justify-between shrink-0">
        <div class="flex items-center space-x-3">
            <div class="h-9 w-9 rounded-lg bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <span class="text-white font-bold font-display text-lg">P</span>
            </div>
            <div>
                <h1 class="text-lg font-black font-display tracking-wider text-white">
                    PARAD<svg class="h-5 w-5 inline-block text-indigo-400 align-middle -mt-1 mx-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><rect x="4" y="3" width="16" height="18" rx="2" /><line x1="4" y1="7" x2="20" y2="7" /><circle cx="7" cy="5" r="0.75" fill="currentColor" /><circle cx="10" cy="5" r="0.75" fill="currentColor" /><circle cx="13" cy="5" r="0.75" fill="currentColor" /><circle cx="12" cy="14" r="4" /><circle cx="12" cy="14" r="2.5" stroke-dasharray="3 2" /></svg>U <span class="text-indigo-400 font-medium text-xs font-sans tracking-normal lowercase">v2026</span>
                </h1>
                <p class="text-xs text-slate-400">Système de Caisse Tactile & Gestion Pressing</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="hidden md:flex space-x-2">
            <a href="{{ route('checkout.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold font-display uppercase tracking-wide transition-colors {{ Request::routeIs('checkout.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                Caisse Tactile
            </a>
            <a href="{{ route('orders.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold font-display uppercase tracking-wide transition-colors {{ Request::routeIs('orders.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                Suivi Commandes
            </a>
            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" 
                   class="px-4 py-2 rounded-lg text-xs font-bold font-display uppercase tracking-wide transition-colors {{ Request::routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                    Dashboard Admin
                </a>
                <a href="{{ route('admin.users.index') }}" 
                   class="px-4 py-2 rounded-lg text-xs font-bold font-display uppercase tracking-wide transition-colors {{ Request::routeIs('admin.users.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                    Gestion Caissiers
                </a>
            @endif
        </nav>

        <div class="flex items-center space-x-6">
            <!-- Clock -->
            <div class="text-right hidden sm:block">
                <p id="clock-date" class="text-xs text-slate-400 font-medium"></p>
                <p id="clock-time" class="text-sm font-bold text-slate-200 font-display"></p>
            </div>

            <div class="h-8 w-px bg-slate-700/50"></div>

            <!-- Operator Status -->
            <div class="flex items-center space-x-3">
                <div class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center border border-slate-600">
                    <svg class="h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="text-left">
                    <div class="flex items-center space-x-1.5">
                        <p class="text-xs text-slate-400">Opérateur</p>
                        @if(Auth::check())
                            <span class="text-[9px] px-1.5 py-0.2 rounded font-bold uppercase {{ Auth::user()->role === 'admin' ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/20' : 'bg-slate-700 text-slate-300' }}">
                                {{ Auth::user()->role === 'admin' ? 'Admin' : 'Caissier' }}
                            </span>
                        @endif
                    </div>
                    <p class="text-sm font-semibold text-slate-200">{{ Auth::user() ? Auth::user()->name : 'Caisse 1' }}</p>
                </div>
            </div>

            @if(Auth::check())
                <div class="h-8 w-px bg-slate-700/50"></div>

                <!-- Theme Toggle Button -->
                <button onclick="toggleTheme()" class="p-2 text-slate-400 hover:text-indigo-400 transition-colors bg-slate-800 border border-slate-700 rounded-lg hover:border-indigo-500/20 hover:bg-indigo-500/5 cursor-pointer flex items-center justify-center mr-1" title="Changer de thème">
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
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 transition-colors bg-slate-800 border border-slate-700 rounded-lg hover:border-rose-500/20 hover:bg-rose-500/5 cursor-pointer flex items-center justify-center" title="Se déconnecter">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            @endif
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

        // Sync theme elements when DOM is ready
        window.addEventListener('DOMContentLoaded', () => {
            applyTheme(localStorage.getItem('theme') || 'dark');
        });

        // Global function for printing without opening new windows (via hidden iframe)
        function printOrder(orderId, type = 'all') {
            const oldIframe = document.getElementById('global-print-iframe');
            if (oldIframe) {
                oldIframe.remove();
            }
            const iframe = document.createElement('iframe');
            iframe.id = 'global-print-iframe';
            iframe.style.position = 'fixed';
            iframe.style.width = '0px';
            iframe.style.height = '0px';
            iframe.style.border = 'none';
            iframe.style.top = '0';
            iframe.style.left = '0';
            iframe.style.opacity = '0';
            iframe.src = `/orders/${orderId}/print-${type}`;
            
            iframe.onload = function() {
                setTimeout(() => {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                }, 300);
            };
            
            document.body.appendChild(iframe);
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
    </script>

    <!-- Custom Alert Modal Overlay -->
    <div id="custom-alert-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl w-[380px] p-6 flex flex-col items-center shadow-2xl overflow-hidden transform scale-95 transition-all text-center">
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

    @yield('scripts')
</body>
</html>
