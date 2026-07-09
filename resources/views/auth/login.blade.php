<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - PARADOU</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect x=%2210%22 y=%225%22 width=%2280%22 height=%2290%22 rx=%2210%22 fill=%22%234f46e5%22/><line x1=%2210%22 y1=%2225%22 x2=%2290%22 y2=%2225%22 stroke=%22white%22 stroke-width=%225%22/><circle cx=%2225%22 cy=%2215%22 r=%224%22 fill=%22white%22/><circle cx=%2240%22 cy=%2215%22 r=%224%22 fill=%22white%22/><circle cx=%2255%22 cy=%2215%22 r=%224%22 fill=%22white%22/><circle cx=%2250%22 cy=%2260%22 r=%2220%22 fill=%22none%22 stroke=%22white%22 stroke-width=%228%22/><circle cx=%2250%22 cy=%2260%22 r=%2212%22 fill=%22none%22 stroke=%22white%22 stroke-width=%224%22 stroke-dasharray=%2210 5%22/></svg>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-image: radial-gradient(circle at top right, rgba(99, 102, 241, 0.08), transparent 40%),
                              radial-gradient(circle at bottom left, rgba(79, 70, 229, 0.05), transparent 45%);
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="h-full antialiased flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->
        <div class="flex justify-center">
            <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-xl shadow-indigo-600/30">
                <span class="text-white font-extrabold font-display text-2xl">P</span>
            </div>
        </div>
        
        <h2 class="mt-6 text-center text-3xl font-black font-display tracking-wider text-white">
            PARAD<svg class="h-8 w-8 inline-block text-indigo-400 align-middle -mt-2 mx-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><rect x="4" y="3" width="16" height="18" rx="2" /><line x1="4" y1="7" x2="20" y2="7" /><circle cx="7" cy="5" r="0.75" fill="currentColor" /><circle cx="10" cy="5" r="0.75" fill="currentColor" /><circle cx="13" cy="5" r="0.75" fill="currentColor" /><circle cx="12" cy="14" r="4" /><circle cx="12" cy="14" r="2.5" stroke-dasharray="3 2" /></svg>U
        </h2>
        <p class="mt-1.5 text-center text-sm text-slate-400">
            Identifiez-vous pour accéder au système de caisse
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-slate-900 border border-slate-800/80 rounded-3xl p-8 shadow-2xl relative overflow-hidden backdrop-blur-xl">
            <!-- Form -->
            <form action="{{ url('/login') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Adresse Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                           class="w-full bg-slate-950 border @error('email') border-rose-500/50 @else border-slate-800 @enderror rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition-colors">
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Mot de Passe</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition-colors">
                </div>

                <!-- Remember me -->
                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-indigo-500">
                        <label for="remember" class="ml-2 block text-xs font-medium text-slate-400">
                            Se souvenir de moi
                        </label>
                    </div>
                </div>

                <!-- Submit button -->
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-display font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/20 active:translate-y-0.5 transition-all text-sm flex items-center justify-center">
                    Connexion
                </button>
            </form>

            <!-- Quick Demo Credentials (UX Touch) -->
            <div class="mt-8 pt-6 border-t border-slate-800/80">
                <span class="block text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Accès Rapides Démo / Test</span>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="fillAuth('admin@dryplus.com')" 
                            class="px-3 py-2 bg-slate-950 border border-slate-800 hover:border-slate-700 hover:bg-slate-900 rounded-xl text-left text-xs transition-all cursor-pointer">
                        <p class="font-bold text-indigo-400">Administrateur</p>
                        <p class="text-[10px] text-slate-500 font-mono mt-0.5">admin@dryplus.com</p>
                    </button>
                    <button onclick="fillAuth('caisse1@dryplus.com')" 
                            class="px-3 py-2 bg-slate-950 border border-slate-800 hover:border-slate-700 hover:bg-slate-900 rounded-xl text-left text-xs transition-all cursor-pointer">
                        <p class="font-bold text-indigo-400">Caissier 1</p>
                        <p class="text-[10px] text-slate-500 font-mono mt-0.5">caisse1@dryplus.com</p>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillAuth(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }
    </script>
</body>
</html>
