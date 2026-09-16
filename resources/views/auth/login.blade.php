<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator | Pemuda MTA Perwakilan Sragen</title>

    <!-- PWA & Mobile Web App Meta Tags -->
    <meta name="theme-color" content="#dc2626">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Pemuda MTA">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="shortcut icon" href="{{ asset('icons/pemudamta.png') }}" type="image/png">
    <link rel="icon" type="image/png" href="{{ asset('icons/pemudamta.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-gradient-to-br from-slate-900 via-slate-850 to-red-950 font-sans text-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Card Container -->
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden text-slate-800 bg-white">
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-700 via-red-600 to-amber-600 p-8 text-center text-white relative">
                <div class="w-20 h-20 mx-auto bg-white rounded-2xl p-2.5 shadow-lg mb-3 flex items-center justify-center">
                    <img src="{{ asset('icons/pemudamta.png') }}" alt="Logo Pemuda MTA" class="w-full h-full object-contain">
                </div>
                <h2 class="text-xl font-extrabold tracking-tight">Pemuda MTA Sragen</h2>
                <p class="text-red-100 text-xs font-medium mt-0.5">Portal Administrator & Pengurus</p>
            </div>

            <!-- Body -->
            <div class="p-6 sm:p-8">
                <h3 class="text-lg font-bold text-slate-900 text-center mb-6">Masuk ke Dashboard</h3>

                @if(session('error'))
                    <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm flex items-start gap-2.5">
                        <i class="bi bi-exclamation-circle-fill text-red-500 text-base flex-shrink-0 mt-0.5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm flex items-start gap-2.5">
                        <i class="bi bi-check-circle-fill text-emerald-500 text-base flex-shrink-0 mt-0.5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" autocomplete="off" class="space-y-4">
                    @csrf

                    <div>
                        <label for="login" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Username atau Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                <i class="bi bi-person text-lg"></i>
                            </span>
                            <input type="text"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition shadow-sm placeholder-slate-400"
                                id="login"
                                name="login"
                                value="{{ old('login') }}"
                                placeholder="Masukkan username atau email"
                                required
                                autofocus>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                <i class="bi bi-lock text-lg"></i>
                            </span>
                            <input type="password"
                                class="w-full pl-10 pr-12 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition shadow-sm placeholder-slate-400"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                required>
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 transition">
                                <i class="bi bi-eye text-lg" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition duration-200 flex items-center justify-center gap-2 text-sm mt-2 cursor-pointer">
                        <i class="bi bi-box-arrow-in-right text-base"></i>
                        <span>Masuk Sekarang</span>
                    </button>
                </form>

                <div class="mt-6 pt-4 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                    <a href="{{ route('home') }}" class="hover:text-slate-800 flex items-center gap-1 transition">
                        <i class="bi bi-house-door"></i>
                        <span>Beranda</span>
                    </a>
                    <a href="{{ route('pendataan.index') }}" class="font-semibold text-red-600 hover:text-red-700 flex items-center gap-1 transition">
                        <i class="bi bi-ui-checks"></i>
                        <span>Form Pendataan</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            &copy; {{ date('Y') }} Pemuda MTA Perwakilan Sragen. All rights reserved.
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (togglePassword && passwordInput && eyeIcon) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                if (type === 'password') {
                    eyeIcon.classList.remove('bi-eye-slash');
                    eyeIcon.classList.add('bi-eye');
                } else {
                    eyeIcon.classList.remove('bi-eye');
                    eyeIcon.classList.add('bi-eye-slash');
                }
            });
        }
    </script>
</body>
</html>
