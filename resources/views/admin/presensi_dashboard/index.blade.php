@extends('admin.layouts.main')

@section('title', $title ?? 'Dashboard API & Presensi PMD Mobile')

@section('content')

@php
    $userRole    = session('role') ?? auth()->user()?->role?->name;
    $wilayahName = session('wilayah_name') ?? (session('wilayah_id') ? 'Wilayah ' . session('wilayah_id') : null);
    $cabangName  = session('cabang_name') ?? (session('cabang_id') ? 'Cabang ' . session('cabang_id') : null);
@endphp

<!-- WELCOME HERO BANNER (KHUSUS JUDUL & STATUS) -->
<div class="mb-6 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-6 sm:p-8 text-white shadow-xl border border-slate-700/50 relative overflow-hidden">
    <!-- Decorative background glow -->
    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -top-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10">
        <!-- Badges header -->
        <div class="flex flex-wrap items-center gap-2 mb-3.5">
            @if($apiStatus)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 font-bold text-xs shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    REST API Mobile Online
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/20 border border-rose-500/30 text-rose-300 font-bold text-xs shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                    Mode Pemeliharaan (Maintenance)
                </span>
            @endif

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-slate-200 text-xs font-semibold">
                <i class="bi bi-android2 text-emerald-400"></i>
                Flutter App v{{ $settings['api_latest_app_version'] ?? '1.0.0' }}
                <span class="text-[10px] text-slate-400">(Min: v{{ $settings['api_min_app_version'] ?? '1.0.0' }})</span>
            </span>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 text-xs font-semibold">
                <i class="bi bi-shield-check"></i>
                @if($userRole === 'superadmin')
                    Superadmin &bull; Seluruh Sragen
                @elseif($cabangName)
                    {{ $cabangName }}
                @elseif($wilayahName)
                    {{ $wilayahName }}
                @else
                    {{ ucfirst((string)$userRole) }}
                @endif
            </span>
        </div>

        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white mb-2.5 flex items-center gap-2.5">
            <span>Dashboard Management API &amp; Mobile Presensi PMD</span>
            <span class="text-xl sm:text-2xl lg:text-3xl">📱</span>
        </h2>
        <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-4xl">
            Pusat pengawasan operasional server REST API Flutter, monitoring real-time catatan kehadiran pengajian cabang se-Kabupaten Sragen, sinkronisasi offline/online, serta manajemen sesi perangkat aktif.
        </p>
    </div>
</div>

<!-- TOMBOL MENU AKSI CEPAT PRESENSI & API (TERPISAH DARI BANNER JUDUL) -->
<div class="mb-6 bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3.5 pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                <i class="bi bi-grid-fill"></i>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Tombol Menu &amp; Navigasi Cepat</h3>
                <p class="text-[11px] text-slate-500">Pintasan menu operasional presensi, manajemen API, agenda perwakilan, dan aplikasi mobile</p>
            </div>
        </div>
        <span class="text-[11px] text-slate-400 hidden sm:inline-flex items-center gap-1.5 font-medium">
            <i class="bi bi-lightning-charge-fill text-amber-500"></i> Menu Cepat
        </span>
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        <!-- Tombol Dashboard Pemuda -->
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-red-50 text-slate-700 hover:text-red-700 font-semibold text-xs transition border border-slate-200/90 hover:border-red-300 shadow-2xs">
            <i class="bi bi-people-fill text-red-500 text-sm"></i>
            <span>Dashboard Pemuda</span>
        </a>

        @if($userRole === 'superadmin')
            <!-- Tombol Reset Pra-Launching -->
            <a href="{{ route('admin.api-settings.index') }}#launchResetCard" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white font-bold text-xs transition shadow-sm hover:shadow">
                <i class="bi bi-rocket-takeoff-fill text-sm"></i>
                <span>Reset Pra-Launching</span>
            </a>

            <!-- Tombol Seting API Presensi -->
            <a href="{{ route('admin.api-settings.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition shadow-sm hover:shadow">
                <i class="bi bi-sliders2-vertical text-sm"></i>
                <span>Seting API Presensi</span>
            </a>
        @endif

        <!-- Tombol Agenda Perwakilan -->
        <a href="{{ route('admin.kegiatan-perwakilan.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-amber-50 text-slate-700 hover:text-amber-800 font-semibold text-xs transition border border-slate-200/90 hover:border-amber-300 shadow-2xs">
            <i class="bi bi-calendar-event-fill text-amber-500 text-sm"></i>
            <span>Agenda Perwakilan</span>
        </a>

        @if(!empty($settings['api_apk_download_url']))
            <!-- Tombol Unduh APK -->
            <a href="{{ $settings['api_apk_download_url'] }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm hover:shadow">
                <i class="bi bi-download text-sm"></i>
                <span>Unduh APK Mobile</span>
            </a>
        @endif

        <!-- Tombol Config API (JSON) -->
        <a href="{{ route('api.v1.config') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-sky-50 text-slate-700 hover:text-sky-700 font-semibold text-xs transition border border-slate-200/90 hover:border-sky-300 shadow-2xs">
            <i class="bi bi-braces text-sky-600 text-sm"></i>
            <span>Config API (JSON)</span>
        </a>
    </div>
</div>

<!-- BROADCAST BANNER JIKA ADA -->
@if(!empty($settings['api_broadcast_message']))
    <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/90 text-amber-900 text-xs sm:text-sm flex items-start gap-3 shadow-sm">
        <div class="w-8 h-8 rounded-xl bg-amber-200/60 text-amber-800 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="bi bi-megaphone-fill text-base"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-0.5">Pesan Siaran Mobile Aktif (Broadcast Banner):</div>
            <div class="text-slate-700 leading-relaxed text-xs">{{ $settings['api_broadcast_message'] }}</div>
        </div>
        @if($userRole === 'superadmin')
            <a href="{{ route('admin.api-settings.index') }}" class="text-xs font-bold text-amber-800 hover:text-amber-950 underline flex-shrink-0 self-center">
                Edit Pesan
            </a>
        @endif
    </div>
@endif

<!-- ROW 1: PRIMARY METRICS CARDS -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Card 1: API Server Status -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Status Server API</span>
            <div class="w-10 h-10 rounded-xl {{ $apiStatus ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                <i class="bi {{ $apiStatus ? 'bi-cloud-check-fill' : 'bi-cloud-slash-fill' }} text-xl"></i>
            </div>
        </div>
        <div>
            <div class="text-xl sm:text-2xl font-black {{ $apiStatus ? 'text-emerald-600' : 'text-rose-600' }} tracking-tight">
                {{ $apiStatus ? 'ONLINE / AKTIF' : 'MAINTENANCE' }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5">
                <i class="bi bi-arrow-repeat text-indigo-500"></i>
                <span>Sync Offline: <strong class="text-slate-800">{{ ($settings['api_allow_offline_sync'] ?? '1') === '1' ? 'Diizinkan' : 'Nonaktif' }}</strong></span>
            </div>
        </div>
        @if($userRole === 'superadmin')
            <a href="{{ route('admin.api-settings.index') }}" class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-indigo-600 font-semibold hover:text-indigo-700">
                <span>Pengaturan Server API</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        @else
            <div class="mt-3 pt-2.5 border-t border-slate-100 text-[11px] text-slate-400">
                Layanan API Terhubung
            </div>
        @endif
    </div>

    <!-- Card 2: Sesi Kegiatan Presensi -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Sesi Kegiatan Presensi</span>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="bi bi-calendar2-check-fill text-xl"></i>
            </div>
        </div>
        <div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                {{ number_format($totalKegiatan) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ $kegiatanSelesai }} Selesai
                </span>
                &bull;
                <span class="inline-flex items-center gap-1 text-amber-600 font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ $kegiatanBerlangsung }} Aktif
                </span>
            </div>
        </div>
        <a href="#tabelKegiatan" class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-indigo-600 font-semibold hover:text-indigo-700">
            <span>Lihat Sesi Presensi</span>
            <i class="bi bi-arrow-down"></i>
        </a>
    </div>

    <!-- Card 3: Kehadiran Pemuda Tercatat -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Total Rekap Kehadiran</span>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i class="bi bi-person-check-fill text-xl"></i>
            </div>
        </div>
        <div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 tracking-tight">
                {{ number_format($totalPresensiRecorded) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1">
                Tingkat Hadir: <strong class="text-emerald-600">{{ $persentaseHadir }}%</strong>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-emerald-700 font-semibold">
            <span>{{ number_format($kehadiranHadir) }} Pemuda Hadir</span>
            <i class="bi bi-check2-circle"></i>
        </div>
    </div>

    <!-- Card 4: Sesi Mobile Aktif -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Perangkat Mobile Aktif</span>
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                <i class="bi bi-phone-vibrate-fill text-xl"></i>
            </div>
        </div>
        <div>
            <div class="text-2xl sm:text-3xl font-black text-sky-600 tracking-tight">
                {{ number_format($totalTokens) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1">
                Aktif 7 hari: <strong class="text-slate-800">{{ $activeTokens7Days }} perangkat</strong>
            </div>
        </div>
        <a href="#tabelPerangkat" class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-sky-600 font-semibold hover:text-sky-700">
            <span>Daftar Sesi Perangkat</span>
            <i class="bi bi-arrow-down"></i>
        </a>
    </div>
</div>

<!-- ROW 2: STATUS KEHADIRAN RINCI (4 STATUS CARDS WITH MINI PROGRESS) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $hadirPct = $totalPresensiRecorded > 0 ? round(($kehadiranHadir / $totalPresensiRecorded) * 100, 1) : 0;
        $izinPct  = $totalPresensiRecorded > 0 ? round(($kehadiranIzin / $totalPresensiRecorded) * 100, 1) : 0;
        $sakitPct = $totalPresensiRecorded > 0 ? round(($kehadiranSakit / $totalPresensiRecorded) * 100, 1) : 0;
        $alpaPct  = $totalPresensiRecorded > 0 ? round(($kehadiranAlpa / $totalPresensiRecorded) * 100, 1) : 0;
    @endphp

    <!-- Hadir Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Hadir</span>
            </div>
            <span class="text-xs font-bold text-emerald-600">{{ $hadirPct }}%</span>
        </div>
        <div class="my-2">
            <div class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($kehadiranHadir) }}</div>
            <div class="text-[11px] text-slate-400">Pemuda Tercatat Hadir</div>
        </div>
        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $hadirPct }}%"></div>
        </div>
    </div>

    <!-- Izin Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Izin</span>
            </div>
            <span class="text-xs font-bold text-sky-600">{{ $izinPct }}%</span>
        </div>
        <div class="my-2">
            <div class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($kehadiranIzin) }}</div>
            <div class="text-[11px] text-slate-400">Disertai Alasan Izin</div>
        </div>
        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
            <div class="bg-sky-500 h-1.5 rounded-full" style="width: {{ $izinPct }}%"></div>
        </div>
    </div>

    <!-- Sakit Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Sakit</span>
            </div>
            <span class="text-xs font-bold text-amber-600">{{ $sakitPct }}%</span>
        </div>
        <div class="my-2">
            <div class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($kehadiranSakit) }}</div>
            <div class="text-[11px] text-slate-400">Kondisi Kurang Sehat</div>
        </div>
        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
            <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $sakitPct }}%"></div>
        </div>
    </div>

    <!-- Alpa Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Alpa</span>
            </div>
            <span class="text-xs font-bold text-rose-600">{{ $alpaPct }}%</span>
        </div>
        <div class="my-2">
            <div class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($kehadiranAlpa) }}</div>
            <div class="text-[11px] text-slate-400">Tanpa Keterangan</div>
        </div>
        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
            <div class="bg-rose-500 h-1.5 rounded-full" style="width: {{ $alpaPct }}%"></div>
        </div>
    </div>
</div>

<!-- CHARTS & SUMMARY SECTION (GRID 12 COLUMNS) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <!-- Chart: Sebaran Kegiatan Presensi per Wilayah -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-bar-chart-fill text-indigo-600"></i>
                <span>Distribusi Kegiatan Presensi per Wilayah</span>
            </h3>
            <span class="text-[11px] text-slate-400 font-medium">Kab. Sragen</span>
        </div>

        <div class="h-64 sm:h-72">
            <canvas id="chartPresensiWilayah"></canvas>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4">
            @php $colors = ['text-blue-600', 'text-emerald-600', 'text-amber-600', 'text-purple-600']; @endphp
            @foreach($wilayahStats as $idx => $w)
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                    <div class="text-[11px] font-bold {{ $colors[$idx % 4] }}">{{ $w->name }}</div>
                    <div class="text-lg font-black text-slate-900">{{ number_format($w->total_kegiatan ?? 0) }}</div>
                    <div class="text-[10px] text-slate-400">Sesi Kegiatan</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Chart: Komposisi Status Kehadiran & Detail Mobile -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-pie-chart-fill text-emerald-600"></i>
                <span>Komposisi Status Kehadiran</span>
            </h3>
            <span class="text-[11px] text-slate-400 font-medium">Rekap Total</span>
        </div>

        <div class="flex flex-col items-center justify-center my-auto">
            <div class="h-44 sm:h-52 w-full max-w-[220px]">
                <canvas id="chartKehadiranDonut"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-x-6 gap-y-1.5 mt-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-md bg-emerald-500"></span>
                    <span class="text-slate-600">Hadir: <strong>{{ number_format($kehadiranHadir) }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-md bg-sky-500"></span>
                    <span class="text-slate-600">Izin: <strong>{{ number_format($kehadiranIzin) }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-md bg-amber-500"></span>
                    <span class="text-slate-600">Sakit: <strong>{{ number_format($kehadiranSakit) }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-md bg-rose-500"></span>
                    <span class="text-slate-600">Alpa: <strong>{{ number_format($kehadiranAlpa) }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Info Card Sinkronisasi & Parameter Mobile -->
        <div class="mt-4 p-3.5 rounded-2xl bg-indigo-50/60 border border-indigo-100/80 text-xs text-indigo-950 flex items-center justify-between">
            <div>
                <div class="font-bold flex items-center gap-1.5">
                    <i class="bi bi-phone"></i>
                    <span>Parameter Sinkronisasi Mobile</span>
                </div>
                <div class="text-[11px] text-indigo-700 mt-0.5">
                    Max bulk: <strong>{{ $settings['api_max_bulk_sync'] ?? 300 }} item</strong> &bull; Token: <strong>{{ $settings['api_token_expiration_days'] ?? 90 }} hari</strong>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs font-black text-indigo-700">{{ $cabangAktifCount }}</span>
                <span class="block text-[10px] text-indigo-600">Cabang Terdata</span>
            </div>
        </div>
    </div>
</div>

<!-- SECTION TABEL 1: DAFTAR SESI KEGIATAN PRESENSI DARI CABANG -->
<div id="tabelKegiatan" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
    <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-calendar2-range-fill text-indigo-600"></i>
                <span>Monitoring Sesi Presensi Cabang</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftar agenda kegiatan dan pengajian yang dicatat dari aplikasi mobile oleh sekretaris cabang</p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.presensi.dashboard') }}" class="flex flex-wrap items-center gap-2">
            @if($userRole === 'superadmin')
                <select name="wilayah_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Wilayah</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ ($filters['wilayah_id'] ?? '') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            @endif

            @if(in_array($userRole, ['superadmin', 'admin_pemuda', 'admin_pemudi', 'admin_wilayah'], true))
                <select name="cabang_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 max-w-[160px]">
                    <option value="">Semua Cabang</option>
                    @foreach($cabangList as $c)
                        <option value="{{ $c->id }}" {{ ($filters['cabang_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                <option value="berlangsung" {{ ($filters['status'] ?? '') === 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                <option value="selesai" {{ ($filters['status'] ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
            </select>

            <div class="relative">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari kegiatan/cabang..." class="px-3 py-1.5 pl-8 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 w-44">
                <i class="bi bi-search absolute left-2.5 top-2 text-slate-400 text-xs"></i>
            </div>

            <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition">
                Filter
            </button>
            @if(!empty(array_filter($filters)))
                <a href="{{ route('admin.presensi.dashboard') }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs transition" title="Reset filter">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/75 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                    <th class="py-3 px-4">Cabang &amp; Wilayah</th>
                    <th class="py-3 px-4">Nama Agenda Kegiatan</th>
                    <th class="py-3 px-4">Hari / Tanggal</th>
                    <th class="py-3 px-4">Waktu &amp; Tempat</th>
                    <th class="py-3 px-4">Pemateri</th>
                    <th class="py-3 px-4 text-center">Kehadiran</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi &amp; Rekap</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentKegiatan as $keg)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $keg->cabang?->name ?? 'Cabang #' . $keg->cabang_id }}</div>
                            <div class="text-[11px] text-slate-500">{{ $keg->cabang?->wilayah?->name ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-indigo-700">{{ $keg->nama_kegiatan }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 font-medium text-slate-600 uppercase">{{ $keg->target_peserta }}</span>
                                &bull; Oleh: {{ $keg->creator?->name ?? 'Admin' }}
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-medium">
                            <div class="flex items-center gap-1.5">
                                <i class="bi bi-calendar3 text-indigo-500"></i>
                                <span>{{ $keg->tanggal ? $keg->tanggal->translatedFormat('d M Y') : '-' }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            @if($keg->jam_mulai)
                                <div class="font-medium text-slate-800">{{ substr($keg->jam_mulai, 0, 5) }} {{ $keg->jam_selesai ? '- ' . substr($keg->jam_selesai, 0, 5) : 'WIB' }}</div>
                            @endif
                            <div class="text-[11px] text-slate-500 truncate max-w-[150px]" title="{{ $keg->lokasi }}">{{ $keg->lokasi ?: '-' }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-700">
                            {{ $keg->pemateri ?: '-' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $hadir = $keg->hadir_count ?? 0;
                                $recorded = $keg->total_recorded_count ?? 0;
                            @endphp
                            <div class="inline-flex flex-col items-center">
                                <span class="text-xs font-bold text-emerald-600">{{ $hadir }} Hadir</span>
                                <span class="text-[10px] text-slate-400">dari {{ $recorded }} tercatat</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($keg->status === 'selesai')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    <i class="bi bi-check-circle-fill"></i> Selesai
                                </span>
                            @elseif($keg->status === 'berlangsung')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold animate-pulse">
                                    <i class="bi bi-record-circle"></i> Berlangsung
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold">
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button type="button" onclick="showRekapModal({{ $keg->id }})" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 text-xs font-bold transition shadow-xs">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Rekap &amp; WA</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-slate-400">
                            <i class="bi bi-calendar-x text-3xl block mb-2"></i>
                            Belum ada catatan sesi kegiatan presensi yang sesuai kriteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($recentKegiatan->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $recentKegiatan->links() }}
        </div>
    @endif
</div>

<!-- SECTION TABEL 2: PERANGKAT MOBILE TERHUBUNG (TOKEN SANCTUM) -->
<div id="tabelPerangkat" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
    <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-phone-fill text-sky-600"></i>
                <span>Sesi Perangkat Mobile Aktif (Sanctum Tokens)</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Monitoring token login sekretaris cabang yang tersambung dari aplikasi mobile Flutter</p>
        </div>
        @if($userRole === 'superadmin')
            <a href="{{ route('admin.api-settings.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition self-start sm:self-auto">
                <span>Kelola Token di Pengaturan</span>
                <i class="bi bi-arrow-right text-xs"></i>
            </a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/75 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                    <th class="py-3 px-4">User / Sekretaris</th>
                    <th class="py-3 px-4">Cabang</th>
                    <th class="py-3 px-4">Perangkat (Device Name)</th>
                    <th class="py-3 px-4">Terakhir Aktif</th>
                    <th class="py-3 px-4">Login Pertama</th>
                    <th class="py-3 px-4 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentTokens as $tok)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $tok->user_name }}</div>
                            <div class="text-[10px] text-slate-500">{{ '@' . $tok->username }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-medium">
                            {{ $tok->cabang_name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                            <i class="bi bi-phone text-slate-400 mr-1"></i>
                            {{ $tok->device_name }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            @if($tok->last_used_at)
                                <span class="text-slate-800 font-medium">{{ \Carbon\Carbon::parse($tok->last_used_at)->diffForHumans() }}</span>
                                <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($tok->last_used_at)->format('d/m/Y H:i') }}</div>
                            @else
                                <span class="text-slate-400 italic">Belum request API</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-500 text-[11px]">
                            {{ \Carbon\Carbon::parse($tok->created_at)->format('d M Y H:i') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($tok->last_used_at && \Carbon\Carbon::parse($tok->last_used_at)->gt(now()->subDays(7)))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-medium">
                                    Siaga
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            <i class="bi bi-phone-slash text-3xl block mb-2"></i>
                            Belum ada perangkat mobile yang login dan terhubung.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL REKAP PRESENSI & FORMAT WHATSAPP -->
<div id="modalRekap" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div>
                <h3 id="modalKegiatanTitle" class="text-base sm:text-lg font-bold text-slate-900">Rekap Kehadiran Presensi</h3>
                <p id="modalKegiatanSub" class="text-xs text-slate-500">Memuat rincian kegiatan...</p>
            </div>
            <button type="button" onclick="closeRekapModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <div id="modalLoading" class="py-12 text-center text-slate-400">
            <i class="bi bi-arrow-repeat animate-spin text-3xl block mb-2 text-indigo-600"></i>
            <span>Mengambil data rekap presensi...</span>
        </div>

        <div id="modalContent" class="hidden space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-4 gap-2 text-center">
                <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-100">
                    <span class="text-[10px] uppercase font-bold text-emerald-700">Hadir</span>
                    <div id="rekapHadirVal" class="text-xl font-black text-emerald-600">0</div>
                    <span id="rekapHadirPct" class="text-[10px] text-emerald-600 font-bold">0%</span>
                </div>
                <div class="p-3 rounded-2xl bg-sky-50 border border-sky-100">
                    <span class="text-[10px] uppercase font-bold text-sky-700">Izin</span>
                    <div id="rekapIzinVal" class="text-xl font-black text-sky-600">0</div>
                    <span class="text-[10px] text-sky-600">Orang</span>
                </div>
                <div class="p-3 rounded-2xl bg-amber-50 border border-amber-100">
                    <span class="text-[10px] uppercase font-bold text-amber-700">Sakit</span>
                    <div id="rekapSakitVal" class="text-xl font-black text-amber-600">0</div>
                    <span class="text-[10px] text-amber-600">Orang</span>
                </div>
                <div class="p-3 rounded-2xl bg-rose-50 border border-rose-100">
                    <span class="text-[10px] uppercase font-bold text-rose-700">Alpa</span>
                    <div id="rekapAlpaVal" class="text-xl font-black text-rose-600">0</div>
                    <span class="text-[10px] text-rose-600">Orang</span>
                </div>
            </div>

            <!-- List Izin & Sakit if any -->
            <div id="rekapDetailLists" class="space-y-3"></div>

            <!-- Format Teks WhatsApp -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="bi bi-whatsapp text-emerald-600 text-sm"></i>
                        <span>Format Teks Laporan WhatsApp:</span>
                    </label>
                    <button type="button" id="btnCopyWa" onclick="copyWhatsAppReport()" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Salin Teks WA</span>
                    </button>
                </div>
                <textarea id="rekapWaTextarea" readonly rows="8" class="w-full p-3 font-mono text-xs text-slate-800 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeRekapModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Chart Kegiatan Presensi per Wilayah
        const wilayahLabels = @json($wilayahStats->pluck('name'));
        const wilayahTotals = @json($wilayahStats->pluck('total_kegiatan'));
        const ctxWilayah = document.getElementById('chartPresensiWilayah');

        if (ctxWilayah) {
            new Chart(ctxWilayah, {
                type: 'bar',
                data: {
                    labels: wilayahLabels,
                    datasets: [{
                        label: 'Sesi Presensi',
                        data: wilayahTotals,
                        backgroundColor: ['#4f46e5', '#059669', '#d97706', '#7c3aed'],
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.parsed.y} Sesi Kegiatan`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        }

        // 2. Chart Komposisi Kehadiran (Donut)
        const ctxKehadiran = document.getElementById('chartKehadiranDonut');
        if (ctxKehadiran) {
            const h = {{ $kehadiranHadir }};
            const i = {{ $kehadiranIzin }};
            const s = {{ $kehadiranSakit }};
            const a = {{ $kehadiranAlpa }};
            const tot = h + i + s + a;

            new Chart(ctxKehadiran, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                    datasets: [{
                        data: tot > 0 ? [h, i, s, a] : [1],
                        backgroundColor: tot > 0 ? ['#10b981', '#0ea5e9', '#f59e0b', '#f43f5e'] : ['#e2e8f0'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: tot > 0,
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed;
                                    const pct = tot > 0 ? ((val / tot) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${val} (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });

    // Modal Rekap Handlers
    function showRekapModal(id) {
        const modal = document.getElementById('modalRekap');
        const loading = document.getElementById('modalLoading');
        const content = document.getElementById('modalContent');
        const titleEl = document.getElementById('modalKegiatanTitle');
        const subEl = document.getElementById('modalKegiatanSub');

        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');

        fetch(`{{ url('admin/presensi/kegiatan') }}/${id}/rekap`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat data');
            return res.json();
        })
        .then(data => {
            titleEl.textContent = data.nama_kegiatan;
            subEl.textContent = `${data.cabang_name} (${data.wilayah_name}) • ${data.hari_tanggal}`;

            document.getElementById('rekapHadirVal').textContent = data.rekap.hadir;
            document.getElementById('rekapHadirPct').textContent = `${data.rekap.persentase_hadir}%`;
            document.getElementById('rekapIzinVal').textContent = data.rekap.izin;
            document.getElementById('rekapSakitVal').textContent = data.rekap.sakit;
            document.getElementById('rekapAlpaVal').textContent = data.rekap.alpa;

            // Lists
            let listHtml = '';
            if (data.rekap.daftar_izin && data.rekap.daftar_izin.length > 0) {
                listHtml += `<div class="p-3 rounded-2xl bg-sky-50/70 border border-sky-100 text-xs">
                    <span class="font-bold text-sky-800 block mb-1">Daftar Pemuda Izin (${data.rekap.daftar_izin.length}):</span>
                    <ul class="list-disc pl-4 space-y-0.5 text-slate-700">`;
                data.rekap.daftar_izin.forEach(item => {
                    listHtml += `<li><strong>${item.name}</strong> - <em>${item.keterangan}</em></li>`;
                });
                listHtml += `</ul></div>`;
            }

            if (data.rekap.daftar_sakit && data.rekap.daftar_sakit.length > 0) {
                listHtml += `<div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-100 text-xs">
                    <span class="font-bold text-amber-800 block mb-1">Daftar Pemuda Sakit (${data.rekap.daftar_sakit.length}):</span>
                    <ul class="list-disc pl-4 space-y-0.5 text-slate-700">`;
                data.rekap.daftar_sakit.forEach(item => {
                    listHtml += `<li><strong>${item.name}</strong> - <em>${item.keterangan}</em></li>`;
                });
                listHtml += `</ul></div>`;
            }

            document.getElementById('rekapDetailLists').innerHTML = listHtml;
            document.getElementById('rekapWaTextarea').value = data.whatsapp_text;

            loading.classList.add('hidden');
            content.classList.remove('hidden');
        })
        .catch(err => {
            loading.innerHTML = `<span class="text-rose-500 font-semibold"><i class="bi bi-exclamation-triangle"></i> Gagal memuat rekap. Silakan coba kembali.</span>`;
        });
    }

    function closeRekapModal() {
        document.getElementById('modalRekap').classList.add('hidden');
    }

    function copyWhatsAppReport() {
        const ta = document.getElementById('rekapWaTextarea');
        const btn = document.getElementById('btnCopyWa');
        ta.select();
        ta.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(ta.value).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = `<i class="bi bi-check2"></i> <span>Tersalin!</span>`;
            btn.classList.replace('bg-emerald-600', 'bg-slate-800');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.replace('bg-slate-800', 'bg-emerald-600');
            }, 2000);
        });
    }
</script>
@endsection
