@extends('admin.layouts.main')

@section('title', 'Profil & Pengaturan Akun')

@section('content')

@php
    $userRole    = session('role') ?? auth()->user()?->role?->name;
    $wilayahName = session('wilayah_name') ?? ($user->wilayah?->name ?? null);
    $cabangName  = session('cabang_name') ?? ($user->cabang?->name ?? null);
    $userInitial = strtoupper(substr($user->name, 0, 1));
@endphp

<div class="space-y-6">

    <!-- HEADER JUDUL HALAMAN (BANNER) -->
    <div class="mb-5">
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-red-700 text-xs font-bold">
                <i class="bi bi-person-gear"></i> Pengaturan Akun
            </span>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-medium">
                {{ $user->role->description ?? ucfirst((string)$userRole) }}
            </span>
            <span class="text-xs text-slate-400 flex items-center gap-1">
                <i class="bi bi-dot"></i>
                <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600 transition">Dashboard</a>
                <i class="bi bi-chevron-right text-[9px]"></i>
                <span class="text-slate-600 font-semibold">Profil Saya</span>
            </span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-xl bg-red-600 text-white flex items-center justify-center text-base shadow-md flex-shrink-0">
                <i class="bi bi-person-circle"></i>
            </span>
            <span>Profil &amp; Pengaturan Akun</span>
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1.5 max-w-3xl leading-relaxed">
            Kelola data identitas pengguna, perbarui username &amp; email, ubah kata sandi, serta tinjau riwayat akses dan keterikatan data pemuda Anda.
        </p>
    </div>

    <!-- TOMBOL MENU & NAVIGASI CEPAT (TERPISAH DARI BANNER) -->
    <div class="mb-6 bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3.5 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-sm">
                    <i class="bi bi-grid-fill"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Tombol Menu &amp; Navigasi Cepat</h3>
                    <p class="text-[11px] text-slate-500">Pintasan navigasi modul dan pintasan keluar sesi aplikasi</p>
                </div>
            </div>
            <span class="text-[11px] text-slate-400 hidden sm:inline-flex items-center gap-1.5 font-medium">
                <i class="bi bi-lightning-charge-fill text-amber-500"></i> Menu Cepat
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Dashboard Pemuda -->
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 font-semibold text-xs transition border border-slate-200/90 shadow-2xs flex items-center gap-2">
                <i class="bi bi-pie-chart text-red-600 text-sm"></i>
                <span>Dashboard Pemuda</span>
            </a>

            <!-- Dashboard Presensi -->
            <a href="{{ route('admin.presensi.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50 text-slate-700 hover:text-indigo-800 font-semibold text-xs transition border border-slate-200/90 hover:border-indigo-300 shadow-2xs flex items-center gap-2">
                <i class="bi bi-phone-vibrate-fill text-indigo-600 text-sm"></i>
                <span>Dashboard Presensi</span>
            </a>

            @if($userRole === 'koordinator_gdm')
            <!-- Manajemen GDM -->
            <a href="{{ route('admin.gdm.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-amber-50 text-slate-700 hover:text-amber-800 font-semibold text-xs transition border border-slate-200/90 hover:border-amber-300 shadow-2xs flex items-center gap-2">
                <i class="bi bi-mortarboard-fill text-amber-500 text-sm"></i>
                <span>Manajemen GDM</span>
            </a>
            @endif

            <!-- Data Pemuda -->
            <a href="{{ route('admin.pemuda.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 font-semibold text-xs transition border border-slate-200/90 shadow-2xs flex items-center gap-2">
                <i class="bi bi-people-fill text-slate-500 text-sm"></i>
                <span>Data Pemuda</span>
            </a>

            @if($user->pemuda_id)
            <!-- Detail Pemuda Saya -->
            <a href="{{ route('admin.pemuda.detail', $user->pemuda_id) }}" class="px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs transition border border-emerald-200 shadow-2xs flex items-center gap-2">
                <i class="bi bi-person-badge-fill text-emerald-600 text-sm"></i>
                <span>Lihat Biodata Lengkap Pemuda</span>
            </a>
            @endif

            <!-- Keluar -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition border border-rose-200 shadow-2xs flex items-center gap-2" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
                    <i class="bi bi-box-arrow-right text-rose-600 text-sm"></i>
                    <span>Keluar Sistem</span>
                </button>
            </form>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs animate-in fade-in">
            <i class="bi bi-check-circle-fill text-emerald-600 text-base flex-shrink-0 mt-0.5"></i>
            <div class="flex-1 font-semibold leading-relaxed">
                {{ session('success') }}
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-xs">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs animate-in fade-in">
            <i class="bi bi-info-circle-fill text-sky-600 text-base flex-shrink-0 mt-0.5"></i>
            <div class="flex-1 font-semibold leading-relaxed">
                {{ session('info') }}
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-sky-500 hover:text-sky-700 text-xs">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs animate-in fade-in">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base flex-shrink-0 mt-0.5"></i>
            <div class="flex-1 font-semibold leading-relaxed">
                {{ session('error') }}
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-xs">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs animate-in fade-in">
            <i class="bi bi-exclamation-octagon-fill text-rose-600 text-base flex-shrink-0 mt-0.5"></i>
            <div class="flex-1">
                <div class="font-bold mb-1">Perhatian: Terjadi kesalahan validasi data</div>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-xs">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <!-- MAIN TWO COLUMN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN: PROFIL IDENTITAS & INFO BINDING (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- KARTU IDENTITAS PENGGUNA -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex flex-col items-center text-center">
                    <!-- Avatar Lingkaran Besar -->
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-red-600 to-amber-500 text-white font-black text-2xl flex items-center justify-center shadow-lg mb-3.5 border-4 border-white ring-2 ring-red-100">
                        {{ $userInitial }}
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">{{ $user->name }}</h3>
                    <p class="text-xs font-mono text-slate-500 mt-0.5 font-medium">{{ '@' . $user->username }}</p>

                    <!-- Role & Status Badge -->
                    <div class="flex flex-wrap items-center justify-center gap-1.5 mt-2.5">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-red-100 text-red-700 border border-red-200">
                            {{ $user->role->description ?? ucfirst((string)$userRole) }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Aktif</span>
                        </span>
                    </div>

                    <!-- Sumber Profil Badge -->
                    <div class="mt-3">
                        @if($user->sumber_data === 'pemuda')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-2xs">
                                <i class="bi bi-people-fill text-xs"></i>
                                <span>Terikat: Pemuda MTA Sragen</span>
                            </span>
                        @elseif($user->sumber_data === 'warga')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200 shadow-2xs">
                                <i class="bi bi-cloud-check-fill text-xs"></i>
                                <span>Terikat: Warga MTA Pusat</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="bi bi-pencil-square text-xs"></i>
                                <span>Akun Mandiri / Manual</span>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100 space-y-3 text-xs">
                    <!-- Email -->
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-1.5">
                            <i class="bi bi-envelope text-slate-400"></i> Email
                        </span>
                        <span class="font-semibold text-slate-800 truncate max-w-[180px]">{{ $user->email ?? '-' }}</span>
                    </div>

                    <!-- Lingkup Wilayah / Cabang -->
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-1.5">
                            <i class="bi bi-shield-lock text-slate-400"></i> Lingkup
                        </span>
                        <span class="font-semibold text-slate-800 truncate max-w-[180px]">
                            @if($userRole === 'superadmin')
                                Seluruh Sistem
                            @elseif($cabangName)
                                {{ $cabangName }}
                            @elseif($wilayahName)
                                {{ $wilayahName }}
                            @else
                                Seluruh Sragen
                            @endif
                        </span>
                    </div>

                    <!-- Terakhir Login -->
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-1.5">
                            <i class="bi bi-clock-history text-slate-400"></i> Login Terakhir
                        </span>
                        <span class="font-medium text-slate-600">
                            {{ $user->last_login ? $user->last_login->diffForHumans() : 'Sesi aktif saat ini' }}
                        </span>
                    </div>

                    <!-- Terdaftar Sejak -->
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-1.5">
                            <i class="bi bi-calendar-event text-slate-400"></i> Terdaftar
                        </span>
                        <span class="font-medium text-slate-600">
                            {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- DETAIL KETERIKATAN DATA PEMUDA (JIKA ADA) -->
            @if($user->pemuda)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-2 pb-3 mb-3 border-b border-slate-100">
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-xs font-bold">
                        <i class="bi bi-person-vcard-fill"></i>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Biodata Pemuda Terikat</h4>
                        <p class="text-[11px] text-slate-400">Data sensus kader yang terhubung ke akun Anda</p>
                    </div>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-slate-400 text-[11px] block">Nomor Registrasi:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $user->pemuda->registration_number ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[11px] block">Cabang Asal:</span>
                        <span class="font-semibold text-slate-800">{{ $user->pemuda->cabang->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[11px] block">Nomor WhatsApp / HP:</span>
                        <span class="font-semibold text-emerald-700">{{ $user->pemuda->phone ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[11px] block">Tempat, Tanggal Lahir:</span>
                        <span class="font-medium text-slate-700">
                            {{ $user->pemuda->birth_place ?? '-' }}, {{ $user->pemuda->birth_date ? $user->pemuda->birth_date->format('d/m/Y') : '-' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[11px] block">Status Verifikasi MTA:</span>
                        @if($user->pemuda->status_verifikasi === 'verified')
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700">
                                <i class="bi bi-patch-check-fill text-emerald-600"></i> Terverifikasi MTA Pusat
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700">
                                <i class="bi bi-clock-history text-amber-500"></i> Belum Terverifikasi
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('admin.pemuda.detail', $user->pemuda->id) }}" class="w-full flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition border border-slate-200/80">
                        <i class="bi bi-box-arrow-up-right text-xs"></i>
                        <span>Buka Lembar Sensus Pemuda</span>
                    </a>
                </div>
            </div>
            @endif

        </div>

        <!-- RIGHT COLUMN: FORM PENGATURAN (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- CARD 1: FORM PERBARUI PROFIL -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-base font-bold shadow-2xs">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">Perbarui Informasi Profil</h3>
                        <p class="text-xs text-slate-500">Ubah nama lengkap, alamat surel (email), dan kontak akun Anda</p>
                    </div>
                </div>

                <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required minlength="3" maxlength="100" class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-xs sm:text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition" placeholder="Masukkan nama lengkap Anda">
                        </div>
                        @if($errors->has('name'))
                            <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $errors->first('name') }}</p>
                        @endif
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alamat Email <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required maxlength="100" class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-xs sm:text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition" placeholder="contoh: nama@domain.com">
                        </div>
                        @if($errors->has('email'))
                            <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $errors->first('email') }}</p>
                        @endif
                        <p class="text-[11px] text-slate-400 mt-1">Digunakan untuk notifikasi sistem dan login alternatif.</p>
                    </div>

                    <!-- Nomor WhatsApp / HP jika terikat Pemuda -->
                    @if($user->pemuda)
                    <div>
                        <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor WhatsApp / HP
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-whatsapp text-emerald-600"></i>
                            </span>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->pemuda->phone) }}" maxlength="20" class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('phone') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-xs sm:text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition" placeholder="contoh: 081234567890">
                        </div>
                        @if($errors->has('phone'))
                            <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $errors->first('phone') }}</p>
                        @endif
                        <p class="text-[11px] text-slate-400 mt-1">Perubahan nomor kontak akan otomatis memperbarui data profil Pemuda terikat.</p>
                    </div>
                    @endif

                    <div class="pt-2 flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs sm:text-sm transition shadow-sm hover:shadow">
                            <i class="bi bi-check2-circle text-base"></i>
                            <span>Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- CARD 2: FORM GANTI USERNAME -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold shadow-2xs">
                        <i class="bi bi-at"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">Pergantian Username Akun</h3>
                        <p class="text-xs text-slate-500">Ubah ID masuk sistem (username) yang digunakan untuk masuk ke dashboard</p>
                    </div>
                </div>

                <form action="{{ route('admin.profile.update-username') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Info Username Saat Ini -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Username Saat Ini:</span>
                        <span class="font-mono font-bold text-slate-900 text-sm bg-white px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs">
                            {{ $user->username }}
                        </span>
                    </div>

                    <!-- Username Baru -->
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Username Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-mono font-bold">
                                @
                            </span>
                            <input type="text" name="username" id="username" value="{{ old('username') }}" required minlength="3" maxlength="50" class="w-full pl-8 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('username') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-xs sm:text-sm font-mono focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition" placeholder="username_baru">
                        </div>
                        @if($errors->has('username'))
                            <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $errors->first('username') }}</p>
                        @endif
                        <p class="text-[11px] text-slate-400 mt-1">Minimal 3 karakter. Hanya boleh berisi huruf, angka, tanda strip (-), dan garis bawah (_).</p>
                    </div>

                    <!-- Password Saat Ini (Konfirmasi Keamanan) -->
                    <div>
                        <label for="current_password_username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Password Saat Ini (Verifikasi Keamanan) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-shield-lock"></i>
                            </span>
                            <input type="password" name="current_password" id="current_password_username" required class="w-full pl-10 pr-10 py-2.5 rounded-xl border {{ $errors->has('current_password') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-xs sm:text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition" placeholder="Masukkan password saat ini untuk konfirmasi">
                            <button type="button" onclick="togglePasswordVisibility('current_password_username', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @if($errors->has('current_password'))
                            <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $errors->first('current_password') }}</p>
                        @endif
                        <p class="text-[11px] text-slate-400 mt-1">Konfirmasi password diperlukan untuk menjamin keamanan akun Anda.</p>
                    </div>

                    <div class="pt-2 flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs sm:text-sm transition shadow-sm hover:shadow">
                            <i class="bi bi-arrow-repeat text-base"></i>
                            <span>Perbarui Username</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- CARD 3: FORM GANTI PASSWORD -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center text-base font-bold shadow-2xs">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">Pergantian Kata Sandi (Password)</h3>
                        <p class="text-xs text-slate-500">Perbarui kata sandi akun secara berkala untuk menjaga keamanan sistem</p>
                    </div>
                </div>

                <form action="{{ route('admin.profile.update-password') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Password Saat Ini -->
                    <div>
                        <label for="current_password_pwd" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Password Saat Ini <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" name="current_password" id="current_password_pwd" required class="w-full pl-10 pr-10 py-2.5 rounded-xl border {{ $errors->has('password_current_error') || $errors->has('current_password') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-xs sm:text-sm focus:ring-2 focus:ring-slate-500/20 focus:border-slate-700 transition" placeholder="Masukkan password saat ini">
                            <button type="button" onclick="togglePasswordVisibility('current_password_pwd', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @if($errors->has('password_current_error'))
                            <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $errors->first('password_current_error') }}</p>
                        @elseif($errors->has('current_password'))
                            <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $errors->first('current_password') }}</p>
                        @endif
                    </div>

                    <!-- Password Baru -->
                    <div>
                        <label for="new_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-shield-check"></i>
                            </span>
                            <input type="password" name="password" id="new_password" required minlength="6" class="w-full pl-10 pr-10 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-xs sm:text-sm focus:ring-2 focus:ring-slate-500/20 focus:border-slate-700 transition" placeholder="Masukkan password baru (minimal 6 karakter)">
                            <button type="button" onclick="togglePasswordVisibility('new_password', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @if($errors->has('password'))
                            <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $errors->first('password') }}</p>
                        @endif
                        <p class="text-[11px] text-slate-400 mt-1">Minimal 6 karakter. Dianjurkan kombinasi huruf besar, kecil, dan angka.</p>
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-shield-lock-fill"></i>
                            </span>
                            <input type="password" name="password_confirmation" id="password_confirmation" required minlength="6" class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-slate-500/20 focus:border-slate-700 transition" placeholder="Ketik ulang password baru">
                            <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm transition shadow-sm hover:shadow">
                            <i class="bi bi-lock-fill text-base"></i>
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.className = 'bi bi-eye-slash text-slate-600';
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.className = 'bi bi-eye text-slate-400';
            }
        }
    }
</script>
@endsection
