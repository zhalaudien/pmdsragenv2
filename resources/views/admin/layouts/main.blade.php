<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? 'Dashboard') | Pemuda MTA Perwakilan Sragen</title>

    <!-- PWA & Mobile Web App Meta Tags -->
    <meta name="theme-color" content="#1e293b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Pemuda MTA Admin">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="shortcut icon" href="{{ asset('icons/pemudamta.png') }}" type="image/png">
    <link rel="icon" type="image/png" href="{{ asset('icons/pemudamta.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons & Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Chart.js 4.4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Tailwind CSS Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')

    <style>
        [x-cloak] { display: none !important; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>

<body class="h-full font-sans text-slate-800 antialiased bg-slate-50 flex overflow-hidden">

    @php
        $currRole    = session('role') ?? auth()->user()?->role?->name;
        $currWilayah = session('wilayah_name') ?? (session('wilayah_id') ? 'Wilayah ' . session('wilayah_id') : null);
        $currCabang  = session('cabang_name') ?? (session('cabang_id') ? 'Cabang ' . session('cabang_id') : null);
        $userName    = session('name') ?? auth()->user()?->name ?? 'Admin';
        $userEmail   = session('email') ?? auth()->user()?->email ?? '';
        $userInitial = strtoupper(substr($userName, 0, 1));

        $roleLabels = [
            'superadmin'           => 'Super Administrator',
            'admin_pemuda'         => 'Admin Pemuda (L)',
            'admin_pemudi'         => 'Admin Pemudi (P)',
            'admin_wilayah'        => 'Admin Wilayah',
            'admin_wilayah_pemuda' => 'Admin Wilayah Pemuda (L)',
            'admin_cabang'         => 'Admin Cabang',
        ];

        $roleBadges = [
            'superadmin'           => 'bg-red-500/20 text-red-400 border-red-500/30',
            'admin_pemuda'         => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            'admin_pemudi'         => 'bg-pink-500/20 text-pink-400 border-pink-500/30',
            'admin_wilayah'        => 'bg-sky-500/20 text-sky-400 border-sky-500/30',
            'admin_wilayah_pemuda' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
            'admin_cabang'         => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
        ];

        $displayRoleTitle = $roleLabels[$currRole] ?? ucfirst((string)$currRole);
        $displayRoleBadge = $roleBadges[$currRole] ?? 'bg-slate-700 text-slate-300 border-slate-600';
    @endphp

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden hidden transition-opacity" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto shadow-2xl border-r border-slate-800">
        <!-- Brand Header -->
        <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white p-1.5 shadow-md flex items-center justify-center flex-shrink-0">
                    <img src="{{ asset('icons/pemudamta.png') }}" alt="Logo Pemuda MTA" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="block text-sm font-black tracking-wide text-white">PEMUDA MTA</span>
                    <span class="block text-[11px] font-medium text-slate-400 tracking-wider">PERWAKILAN SRAGEN</span>
                </div>
            </a>
            <button class="lg:hidden p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800" onclick="toggleSidebar()">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <!-- User Mini Profile -->
        <div class="p-4 mx-3 my-3 bg-slate-800/60 rounded-2xl border border-slate-750">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-600 to-amber-500 text-white font-black text-sm flex items-center justify-center shadow-md flex-shrink-0">
                    {{ $userInitial }}
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs font-bold text-white truncate">{{ $userName }}</h4>
                    <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-semibold rounded-md border {{ $displayRoleBadge }}">
                        {{ $displayRoleTitle }}
                    </span>
                </div>
            </div>
            @if($currWilayah || $currCabang)
                <div class="mt-2.5 pt-2 border-t border-slate-700/60 text-[11px] text-slate-400 flex items-center gap-1.5 truncate">
                    <i class="bi bi-geo-alt-fill text-amber-400"></i>
                    <span class="truncate">{{ $currCabang ?? $currWilayah }}</span>
                </div>
            @endif
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-3 py-2 space-y-1 text-xs font-medium">
            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Menu Utama</div>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-speedometer2 text-base"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.persebaran') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.persebaran*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-pie-chart-fill text-base"></i>
                <span>Persebaran Data</span>
            </a>

            <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Data Pemuda</div>

            <a href="{{ route('admin.pemuda.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.pemuda.index') || request()->routeIs('admin.pemuda.detail') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-people-fill text-base"></i>
                <span>Daftar Pemuda</span>
            </a>

            <a href="{{ route('admin.pemuda.tambah') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.pemuda.tambah') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-person-plus-fill text-base"></i>
                <span>Tambah Pemuda</span>
            </a>

            <a href="{{ route('admin.pemuda.export') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.pemuda.export*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="bi bi-file-earmark-excel-fill text-base"></i>
                <span>Export Data Excel</span>
            </a>

            @if($currRole === 'superadmin')
                <a href="{{ route('admin.pemuda.import') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.pemuda.import*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="bi bi-file-earmark-arrow-up-fill text-base"></i>
                    <span>Import Excel</span>
                </a>

                <a href="{{ route('admin.pemuda.backup') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.pemuda.backup*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="bi bi-database-fill-gear text-base"></i>
                    <span>Backup &amp; Reset Data</span>
                </a>

                <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Master Data</div>

                <a href="{{ route('admin.wilayah.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.wilayah*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="bi bi-geo-alt-fill text-base"></i>
                    <span>Master Wilayah</span>
                </a>

                <a href="{{ route('admin.cabang.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.cabang*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="bi bi-diagram-3-fill text-base"></i>
                    <span>Master Cabang</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="bi bi-shield-lock-fill text-base"></i>
                    <span>Pengguna &amp; Hak Akses</span>
                </a>

                <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Integrasi &amp; Web</div>

                <a href="{{ route('admin.warga-mta.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.warga-mta*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="bi bi-cloud-arrow-down-fill text-base"></i>
                    <span>Data Warga MTA</span>
                </a>

                <a href="{{ route('admin.mta-sync.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.mta-sync*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="bi bi-arrow-repeat text-base"></i>
                    <span>Sinkronisasi API MTA</span>
                </a>

                <a href="{{ route('admin.homepage.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.homepage*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="bi bi-window-sidebar text-base"></i>
                    <span>Konten Beranda</span>
                </a>

                <a href="{{ route('admin.kegiatan-perwakilan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.kegiatan-perwakilan*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="bi bi-calendar-event-fill text-base"></i>
                    <span>Info Kegiatan Mobile</span>
                </a>

                <a href="{{ route('admin.api-settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.api-settings*') ? 'bg-red-600 text-white shadow-md font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="bi bi-phone-fill text-base"></i>
                    <span>Seting API Presensi</span>
                </a>
            @endif

            <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Tautan Luar</div>

            <a href="{{ route('pendataan.index') }}" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-emerald-400 hover:bg-slate-800 hover:text-emerald-300 transition">
                <span class="flex items-center gap-3">
                    <i class="bi bi-ui-checks text-base"></i>
                    <span>Formulir Publik</span>
                </span>
                <i class="bi bi-box-arrow-up-right text-xs"></i>
            </a>

            <a href="{{ route('home') }}" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sky-400 hover:bg-slate-800 hover:text-sky-300 transition">
                <span class="flex items-center gap-3">
                    <i class="bi bi-house-door text-base"></i>
                    <span>Landing Page</span>
                </span>
                <i class="bi bi-box-arrow-up-right text-xs"></i>
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-3 border-t border-slate-800">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-slate-800 hover:bg-red-700 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition">
                    <i class="bi bi-box-arrow-right text-sm"></i>
                    <span>Keluar Sistem</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- TOP NAVBAR -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 flex-shrink-0">
            <div class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                <!-- Left: Mobile Toggle & Page Title -->
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" class="lg:hidden p-2 text-slate-600 hover:text-slate-900 rounded-xl hover:bg-slate-100" onclick="toggleSidebar()">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <div class="truncate">
                        <h1 class="text-base sm:text-lg font-bold text-slate-900 truncate">@yield('title', $title ?? 'Dashboard Admin')</h1>
                        <p class="text-[11px] text-slate-500 hidden sm:block">Sistem Informasi &amp; Database Pemuda MTA Sragen</p>
                    </div>
                </div>

                <!-- Right: Scope, Quick Link, User Menu -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Scope Badge -->
                    <div class="hidden md:flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs text-slate-700">
                        <i class="bi bi-shield-check text-red-600"></i>
                        <span class="font-medium">Lingkup:</span>
                        <span class="font-bold">
                            @if($currRole === 'superadmin')
                                Seluruh Sistem
                            @elseif($currCabang)
                                {{ $currCabang }}
                            @elseif($currWilayah)
                                {{ $currWilayah }}
                            @else
                                Seluruh Sragen
                            @endif
                        </span>
                    </div>

                    <a href="{{ route('pendataan.index') }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold transition border border-red-200">
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span>Form Publik</span>
                    </a>

                    <!-- User Dropdown Toggle -->
                    <div class="relative">
                        <button type="button" id="userMenuBtn" onclick="toggleUserMenu()" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition focus:outline-none">
                            <div class="w-8 h-8 rounded-lg bg-red-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                                {{ $userInitial }}
                            </div>
                            <span class="text-xs font-semibold text-slate-700 hidden md:inline truncate max-w-[120px]">{{ $userName }}</span>
                            <i class="bi bi-chevron-down text-slate-400 text-xs hidden md:inline"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="userMenuDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 animate-in fade-in slide-in-from-top-2">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <div class="text-xs font-bold text-slate-900 truncate">{{ $userName }}</div>
                                <div class="text-[11px] text-slate-500 truncate">{{ $userEmail }}</div>
                                <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded bg-red-100 text-red-700">
                                    {{ $displayRoleTitle }}
                                </span>
                            </div>
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition">
                                <i class="bi bi-speedometer2 text-slate-400"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('admin.pemuda.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition">
                                <i class="bi bi-people text-slate-400"></i>
                                <span>Data Pemuda</span>
                            </a>
                            <div class="my-1 border-t border-slate-100"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-red-600 hover:bg-red-50 transition text-left">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- FLASH ALERTS CONTAINER -->
        <div class="px-4 sm:px-6 lg:px-8 pt-4 empty:hidden">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 shadow-sm">
                    <i class="bi bi-check-circle-fill text-emerald-500 text-lg flex-shrink-0"></i>
                    <div class="flex-1 font-medium">{{ session('success') }}</div>
                    <button type="button" class="text-emerald-500 hover:text-emerald-800 text-sm" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm flex items-start gap-3 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 text-lg flex-shrink-0"></i>
                    <div class="flex-1 font-medium">{{ session('error') }}</div>
                    <button type="button" class="text-red-500 hover:text-red-800 text-sm" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm flex items-start gap-3 shadow-sm">
                    <i class="bi bi-exclamation-circle-fill text-amber-500 text-lg flex-shrink-0"></i>
                    <div class="flex-1 font-medium">{{ session('warning') }}</div>
                    <button type="button" class="text-amber-500 hover:text-amber-800 text-sm" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm shadow-sm">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <i class="bi bi-x-circle-fill text-red-500 text-base"></i>
                        <span>Mohon perbaiki kesalahan berikut:</span>
                    </div>
                    <ul class="list-disc pl-7 space-y-0.5 text-xs">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- SCROLLABLE PAGE BODY -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            @yield('content')

            <!-- Global Page Footer -->
            <footer class="mt-12 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <div>
                    &copy; {{ date('Y') }} <strong>Pemuda MTA Perwakilan Sragen</strong>. Hak Cipta Dilindungi.
                </div>
                <div class="flex items-center gap-4">
                    <span>Sistem Basis Data v2.0 (Laravel + Tailwind)</span>
                </div>
            </footer>
        </main>
    </div>

    <!-- GLOBAL JAVASCRIPT HELPERS -->
    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        // User Menu Toggle
        function toggleUserMenu() {
            const menu = document.getElementById('userMenuDropdown');
            menu.classList.toggle('hidden');
        }

        // Close Dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const btn = document.getElementById('userMenuBtn');
            const menu = document.getElementById('userMenuDropdown');
            if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        // Global Modal Helpers
        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        // Close modal on ESC key
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('[data-modal]').forEach(m => {
                    if (!m.classList.contains('hidden')) {
                        m.classList.add('hidden');
                    }
                });
                document.body.classList.remove('overflow-hidden');
            }
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
