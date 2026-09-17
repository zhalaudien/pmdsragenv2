@extends('admin.layouts.main')

@section('title', $title ?? 'Persebaran Data Pemuda')

@section('content')

@php
    $userRole    = session('role') ?? auth()->user()?->role?->name;
    $wilayahName = session('wilayah_name') ?? (session('wilayah_id') ? 'Wilayah ' . session('wilayah_id') : null);
    $cabangName  = session('cabang_name') ?? (session('cabang_id') ? 'Cabang ' . session('cabang_id') : null);

    $totalYouth       = (int) ($stats['totalYouth'] ?? 0);
    $genderL          = (int) ($stats['genderData']['L'] ?? 0);
    $genderP          = (int) ($stats['genderData']['P'] ?? 0);
    $percentL         = $stats['genderData']['percentL'] ?? ($totalYouth > 0 ? round(($genderL / $totalYouth) * 100, 1) : 0);
    $percentP         = $stats['genderData']['percentP'] ?? ($totalYouth > 0 ? round(($genderP / $totalYouth) * 100, 1) : 0);

    $totalWithOrg     = (int) ($stats['totalWithOrg'] ?? 0);
    $percentOrg       = $totalYouth > 0 ? round(($totalWithOrg / $totalYouth) * 100, 1) : 0;

    $eduStatusActive  = (int) ($stats['eduStatusData']['sedang_menempuh']['total'] ?? 0);
    $percentActiveEdu = $totalYouth > 0 ? round(($eduStatusActive / $totalYouth) * 100, 1) : 0;

    $totalWithSkill   = (int) ($stats['totalWithSkill'] ?? 0);
    $percentSkill     = $totalYouth > 0 ? round(($totalWithSkill / $totalYouth) * 100, 1) : 0;

    $totalWirausaha   = (int) ($stats['totalWirausaha'] ?? 0);
    $percentWirausaha = $totalYouth > 0 ? round(($totalWirausaha / $totalYouth) * 100, 1) : 0;

    $totalWithBlood    = (int) ($stats['totalWithBlood'] ?? 0);
    $percentWithBlood  = $totalYouth > 0 ? round(($totalWithBlood / $totalYouth) * 100, 1) : 0;

    $totalVerified    = (int) ($stats['verifData']['verified'] ?? 0);
    $percentVerified  = $totalYouth > 0 ? round(($totalVerified / $totalYouth) * 100, 1) : 0;
    
    $avgAge           = $stats['avgAge'] ?? 0;
@endphp

<!-- 1. HEADER HERO BANNER -->
<div class="mb-6 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-6 sm:p-8 text-white shadow-xl border border-slate-700/50 relative overflow-hidden">
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-amber-200 mb-3">
                <i class="bi bi-pie-chart-fill text-amber-400"></i>
                @if ($userRole === 'superadmin')
                    Persebaran Data &bull; Seluruh Kabupaten Sragen
                @elseif ($userRole === 'admin_pemuda')
                    Persebaran Data &bull; Khusus Laki-laki (L)
                @elseif ($userRole === 'admin_pemudi')
                    Persebaran Data &bull; Khusus Perempuan (P)
                @elseif ($userRole === 'admin_wilayah' || $userRole === 'admin_wilayah_pemuda')
                    Persebaran Data &bull; {{ $wilayahName }}
                @else
                    Persebaran Data &bull; {{ $cabangName }}
                @endif
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-2">
                Persebaran Data Pemuda MTA Sragen 📊
            </h2>
            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                Visualisasi analitik &amp; demografi menyeluruh dari database: geografis wilayah &amp; cabang, element dakwah, jenjang pendidikan, potensi bakat/keahlian, minat, profesi &amp; wirausaha, serta data medis golongan darah.
            </p>
        </div>

        <div class="flex flex-wrap gap-2.5 lg:justify-end flex-shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition border border-slate-700">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.pemuda.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md">
                <i class="bi bi-table"></i>
                <span>Kelola Data</span>
            </a>
            <a href="{{ route('admin.pemuda.export') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs transition shadow-md">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span>Export Excel</span>
            </a>
        </div>
    </div>
</div>

<!-- 2. INTERACTIVE FILTER CARD -->
<div class="mb-6 rounded-3xl bg-white p-5 sm:p-6 border border-slate-200/80 shadow-sm">
    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
        <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="bi bi-funnel-fill text-red-600"></i>
            <span>Filter Interaktif Persebaran Data</span>
        </h3>
        @if (!empty($filters['wilayah_id']) || !empty($filters['cabang_id']) || !empty($filters['blood_type']) || (!empty($filters['gender']) && !in_array($userRole, ['admin_pemuda', 'admin_pemudi', 'admin_wilayah_pemuda'], true)))
            <span class="px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-[10px] font-bold border border-red-200">
                Filter Aktif Diterapkan
            </span>
        @endif
    </div>

    <form method="GET" action="{{ route('admin.persebaran') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
        <!-- Wilayah -->
        <div>
            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Wilayah</label>
            @if(in_array($userRole, ['superadmin', 'admin_pemuda', 'admin_pemudi'], true))
                <select name="wilayah_id" id="filterWilayah" class="w-full text-xs rounded-xl border-slate-300 bg-slate-50 py-2 px-3 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Semua Wilayah --</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ (!empty($filters['wilayah_id']) && (int)$filters['wilayah_id'] === (int)$w->id) ? 'selected' : '' }}>
                            {{ $w->code }} - {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="text" class="w-full text-xs rounded-xl border-slate-200 bg-slate-100 py-2 px-3 text-slate-500" value="{{ $wilayahName }}" readonly>
            @endif
        </div>

        <!-- Cabang -->
        <div>
            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Cabang</label>
            @if($userRole === 'admin_cabang')
                <input type="text" class="w-full text-xs rounded-xl border-slate-200 bg-slate-100 py-2 px-3 text-slate-500" value="{{ $cabangName }}" readonly>
            @else
                <select name="cabang_id" id="filterCabang" class="w-full text-xs rounded-xl border-slate-300 bg-slate-50 py-2 px-3 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Semua Cabang --</option>
                    @foreach($cabangList as $c)
                        <option value="{{ $c->id }}" {{ (!empty($filters['cabang_id']) && (int)$filters['cabang_id'] === (int)$c->id) ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>

        <!-- Gender -->
        <div>
            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Gender</label>
            @if(in_array($userRole, ['admin_wilayah_pemuda', 'admin_pemuda'], true))
                <input type="text" class="w-full text-xs rounded-xl border-slate-200 bg-blue-50 py-2 px-3 text-blue-700 font-bold" value="Laki-laki (L)" readonly>
            @elseif($userRole === 'admin_pemudi')
                <input type="text" class="w-full text-xs rounded-xl border-slate-200 bg-pink-50 py-2 px-3 text-pink-700 font-bold" value="Perempuan (P)" readonly>
            @else
                <select name="gender" class="w-full text-xs rounded-xl border-slate-300 bg-slate-50 py-2 px-3 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Semua Gender --</option>
                    <option value="L" {{ (($filters['gender'] ?? '') === 'L') ? 'selected' : '' }}>Laki-laki (L)</option>
                    <option value="P" {{ (($filters['gender'] ?? '') === 'P') ? 'selected' : '' }}>Perempuan (P)</option>
                </select>
            @endif
        </div>

        <!-- Golongan Darah -->
        <div>
            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Golongan Darah</label>
            <select name="blood_type" class="w-full text-xs rounded-xl border-slate-300 bg-slate-50 py-2 px-3 focus:ring-red-500 focus:border-red-500">
                <option value="">-- Semua Gol. Darah --</option>
                <option value="A" {{ (($filters['blood_type'] ?? '') === 'A') ? 'selected' : '' }}>Golongan A</option>
                <option value="B" {{ (($filters['blood_type'] ?? '') === 'B') ? 'selected' : '' }}>Golongan B</option>
                <option value="AB" {{ (($filters['blood_type'] ?? '') === 'AB') ? 'selected' : '' }}>Golongan AB</option>
                <option value="O" {{ (($filters['blood_type'] ?? '') === 'O') ? 'selected' : '' }}>Golongan O</option>
                <option value="unknown" {{ (($filters['blood_type'] ?? '') === 'unknown') ? 'selected' : '' }}>Belum Tercatat</option>
            </select>
        </div>

        <!-- Status Data -->
        <div>
            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Status Data</label>
            <select name="status_data" class="w-full text-xs rounded-xl border-slate-300 bg-slate-50 py-2 px-3 focus:ring-red-500 focus:border-red-500">
                <option value="active" {{ (($filters['status_data'] ?? 'active') === 'active') ? 'selected' : '' }}>Data Aktif</option>
                <option value="all" {{ (($filters['status_data'] ?? '') === 'all') ? 'selected' : '' }}>Semua Data</option>
                <option value="archived" {{ (($filters['status_data'] ?? '') === 'archived') ? 'selected' : '' }}>Diarsipkan</option>
            </select>
        </div>

        <!-- Submit & Reset -->
        <div class="flex gap-2">
            <button type="submit" class="flex-1 py-2 px-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                <i class="bi bi-filter"></i>
                <span>Terapkan</span>
            </button>
            <a href="{{ route('admin.persebaran') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition flex items-center justify-center" title="Reset Filter">
                <i class="bi bi-arrow-clockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- 3. HIGHLIGHT METRICS (8 CARDS - 2 ROWS) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Total Pemuda -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Total Pemuda</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">{{ number_format($totalYouth) }}</div>
            <div class="text-[10px] text-slate-600 font-medium">L: {{ number_format($genderL) }} ({{ $percentL }}%) &bull; P: {{ number_format($genderP) }} ({{ $percentP }}%)</div>
        </div>
    </div>

    <!-- Element Dakwah -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-diagram-3-fill"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Ikut Element / Org</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">{{ number_format($totalWithOrg) }}</div>
            <div class="text-[10px] text-indigo-600 font-semibold">{{ $percentOrg }}% dari total pemuda</div>
        </div>
    </div>

    <!-- Pendidikan Aktif -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Sedang Sekolah / Kuliah</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">{{ number_format($eduStatusActive) }}</div>
            <div class="text-[10px] text-sky-600 font-semibold">{{ $percentActiveEdu }}% menempuh studi</div>
        </div>
    </div>

    <!-- Punya Keahlian / Skill -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-lightning-charge-fill"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Memiliki Keahlian</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">{{ number_format($totalWithSkill) }}</div>
            <div class="text-[10px] text-amber-600 font-semibold">{{ $percentSkill }}% terdata punya skill</div>
        </div>
    </div>

    <!-- Pelaku Wirausaha -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-shop"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Pelaku Wirausaha</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">{{ number_format($totalWirausaha) }}</div>
            <div class="text-[10px] text-emerald-600 font-semibold">{{ $percentWirausaha }}% berwirausaha</div>
        </div>
    </div>

    <!-- Rata-rata Usia -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-calendar-heart-fill"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Rata-Rata Usia</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">{{ $avgAge > 0 ? $avgAge . ' Th' : '-' }}</div>
            <div class="text-[10px] text-purple-600 font-semibold">Kelompok usia produktif</div>
        </div>
    </div>

    <!-- Golongan Darah Terdata -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-droplet-fill"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Gol. Darah Terdata</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">{{ number_format($totalWithBlood) }}</div>
            <div class="text-[10px] text-rose-600 font-semibold">{{ $percentWithBlood }}% siap donor PMI</div>
        </div>
    </div>

    <!-- Terverifikasi MTA Pusat -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-patch-check-fill"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Terverifikasi API MTA</div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">{{ number_format($totalVerified) }}</div>
            <div class="text-[10px] text-teal-600 font-semibold">{{ $percentVerified }}% data valid tercatat</div>
        </div>
    </div>
</div>

<!-- SECTION 1: SEBARAN GEOGRAFIS & STRUKTUR WILAYAH - CABANG -->
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="w-2.5 h-6 bg-red-600 rounded-full inline-block"></span>
        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">1. Sebaran Geografis, Wilayah &amp; Cabang Binaan</h3>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart Sebaran per Wilayah -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-red-600"></i>
                        <span>Distribusi Pemuda per Wilayah</span>
                    </h4>
                    <span class="text-[11px] text-slate-400 font-medium">4 Wilayah MTA</span>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="chartWilayah"></canvas>
                </div>
            </div>

            <!-- Wilayah Pills Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4 pt-4 border-t border-slate-100">
                @php $wColors = ['border-blue-200 bg-blue-50/50 text-blue-700', 'border-emerald-200 bg-emerald-50/50 text-emerald-700', 'border-amber-200 bg-amber-50/50 text-amber-700', 'border-purple-200 bg-purple-50/50 text-purple-700']; @endphp
                @forelse($stats['wilayahStats'] ?? [] as $idx => $w)
                    <div class="p-2.5 rounded-xl border {{ $wColors[$idx % 4] }} text-center">
                        <div class="text-[11px] font-bold truncate">{{ $w['name'] }}</div>
                        <div class="text-base font-black text-slate-900">{{ number_format($w['total']) }}</div>
                        <div class="text-[10px] text-slate-500">Pemuda</div>
                    </div>
                @empty
                    <div class="col-span-4 text-center text-xs text-slate-400 py-2">Tidak ada data wilayah</div>
                @endforelse
            </div>
        </div>

        <!-- Top 10 Cabang Terbanyak -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-bar-chart-steps text-indigo-600"></i>
                        <span>Top 10 Cabang Terbanyak</span>
                    </h4>
                    <span class="text-[11px] text-slate-400 font-medium">Rangking Cabang</span>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="chartTopCabang"></canvas>
                </div>
            </div>

            <!-- Quick Table / Top 5 Info -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap gap-1.5 justify-center">
                @foreach(array_slice($stats['topCabangStats'] ?? [], 0, 5) as $idx => $c)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold">
                        <span class="w-4 h-4 rounded-full bg-slate-800 text-white text-[9px] flex items-center justify-center font-bold">{{ $idx + 1 }}</span>
                        {{ $c['name'] }} ({{ $c['total'] }})
                    </span>
                @endforeach
            </div>
        </div>

        <!-- Sebaran per Kecamatan di Sragen -->
        <div class="lg:col-span-12 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-pin-map-fill text-emerald-600"></i>
                    <span>Sebaran Domisili / Alamat per Kecamatan di Kabupaten Sragen</span>
                </h4>
                <span class="text-[11px] text-slate-500 font-medium">{{ count($stats['districtStats'] ?? []) }} Kecamatan Terdata</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @forelse($stats['districtStats'] ?? [] as $d)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50/40 transition">
                        <div class="text-[11px] font-bold text-slate-700 uppercase truncate" title="{{ $d['name'] ?? '' }}">
                            {{ $d['name'] ?? '' }}
                        </div>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-lg font-black text-slate-900">{{ number_format($d['total']) }}</span>
                            <span class="text-[10px] text-slate-400">pemuda</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-6 text-center text-xs text-slate-400 py-3">Belum ada data kecamatan</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- SECTION 2: DEMOGRAFI USIA, GOLONGAN DARAH & STATUS PERSONAL -->
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">2. Demografi Usia, Golongan Darah &amp; Status Personal</h3>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart Usia -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-calendar-range-fill text-amber-500"></i>
                        <span>Distribusi Kelompok Usia</span>
                    </h4>
                    <span class="text-[11px] text-amber-600 font-bold bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                        Rata-rata: {{ $avgAge > 0 ? $avgAge . ' Tahun' : '-' }}
                    </span>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="chartAge"></canvas>
                </div>
            </div>

            <!-- Age Bracket Badges -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-1.5 mt-4 pt-4 border-t border-slate-100 text-center">
                @foreach($stats['ageData'] ?? [] as $agKey => $ag)
                    @if($agKey !== 'unknown' || ($ag['total'] ?? 0) > 0)
                        @php
                            $agPercent = $totalYouth > 0 ? round((($ag['total'] ?? 0) / $totalYouth) * 100, 1) : 0;
                        @endphp
                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="text-[10px] font-bold text-slate-600 truncate" title="{{ $ag['label'] }}">{{ $ag['label'] }}</div>
                            <div class="text-sm font-black text-amber-600">{{ number_format($ag['total']) }}</div>
                            <div class="text-[9px] text-slate-400">{{ $agPercent }}%</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Chart Golongan Darah & Kesiapan Donor -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-droplet-fill text-rose-600"></i>
                        <span>Golongan Darah &amp; Potensi Donor PMI</span>
                    </h4>
                    <span class="text-[11px] text-rose-600 font-bold bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200">
                        {{ $percentWithBlood }}% Terdata
                    </span>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="chartBlood"></canvas>
                </div>
            </div>

            <!-- Blood Compatibility Quick Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4 pt-4 border-t border-slate-100">
                <div class="p-2 rounded-xl bg-red-50 border border-red-200 text-center">
                    <span class="inline-block px-1.5 py-0.5 rounded bg-red-600 text-white text-[10px] font-black">Gol. A</span>
                    <div class="text-base font-black text-slate-900 mt-0.5">{{ $stats['bloodTypeData']['A'] ?? 0 }}</div>
                    <div class="text-[9px] text-slate-500">Donor: A, AB</div>
                </div>
                <div class="p-2 rounded-xl bg-blue-50 border border-blue-200 text-center">
                    <span class="inline-block px-1.5 py-0.5 rounded bg-blue-600 text-white text-[10px] font-black">Gol. B</span>
                    <div class="text-base font-black text-slate-900 mt-0.5">{{ $stats['bloodTypeData']['B'] ?? 0 }}</div>
                    <div class="text-[9px] text-slate-500">Donor: B, AB</div>
                </div>
                <div class="p-2 rounded-xl bg-purple-50 border border-purple-200 text-center">
                    <span class="inline-block px-1.5 py-0.5 rounded bg-purple-600 text-white text-[10px] font-black">Gol. AB</span>
                    <div class="text-base font-black text-slate-900 mt-0.5">{{ $stats['bloodTypeData']['AB'] ?? 0 }}</div>
                    <div class="text-[9px] text-slate-500">Resipien Univ.</div>
                </div>
                <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                    <span class="inline-block px-1.5 py-0.5 rounded bg-emerald-600 text-white text-[10px] font-black">Gol. O</span>
                    <div class="text-base font-black text-slate-900 mt-0.5">{{ $stats['bloodTypeData']['O'] ?? 0 }}</div>
                    <div class="text-[9px] text-slate-500">Donor Univ.</div>
                </div>
            </div>
        </div>

        <!-- Status Pernikahan & Status Verifikasi API MTA -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-heart-fill text-pink-500"></i>
                        <span>Status Pernikahan Pemuda</span>
                    </h4>
                </div>
                <div class="h-56">
                    <canvas id="chartMarital"></canvas>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-slate-100 text-center">
                <div class="p-2 rounded-xl bg-slate-50">
                    <div class="text-[10px] font-semibold text-slate-500">Belum Menikah</div>
                    <div class="text-base font-black text-blue-600">{{ number_format($stats['maritalData']['belum_menikah'] ?? 0) }}</div>
                </div>
                <div class="p-2 rounded-xl bg-slate-50">
                    <div class="text-[10px] font-semibold text-slate-500">Sudah Menikah</div>
                    <div class="text-base font-black text-emerald-600">{{ number_format($stats['maritalData']['sudah_menikah'] ?? 0) }}</div>
                </div>
                <div class="p-2 rounded-xl bg-slate-50">
                    <div class="text-[10px] font-semibold text-slate-500">Duda / Janda</div>
                    <div class="text-base font-black text-purple-600">{{ number_format(($stats['maritalData']['duda'] ?? 0) + ($stats['maritalData']['janda'] ?? 0)) }}</div>
                </div>
            </div>
        </div>

        <!-- Status Verifikasi API Pusat -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-shield-check text-teal-600"></i>
                        <span>Status Verifikasi Sinkronisasi API MTA Pusat</span>
                    </h4>
                    <span class="text-[11px] text-teal-600 font-bold bg-teal-50 px-2 py-0.5 rounded-full border border-teal-200">
                        Otomatis Sistem
                    </span>
                </div>
                <div class="h-56">
                    <canvas id="chartVerif"></canvas>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-slate-100 text-center">
                <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-100">
                    <div class="text-[10px] font-bold text-emerald-700">Terverifikasi Pusat</div>
                    <div class="text-base font-black text-emerald-800">{{ number_format($stats['verifData']['verified'] ?? 0) }}</div>
                    <div class="text-[9px] text-emerald-600">{{ $percentVerified }}% data valid</div>
                </div>
                <div class="p-2 rounded-xl bg-amber-50 border border-amber-100">
                    <div class="text-[10px] font-bold text-amber-700">Belum Terverifikasi / Pending</div>
                    <div class="text-base font-black text-amber-800">{{ number_format($stats['verifData']['pending'] ?? 0) }}</div>
                    <div class="text-[9px] text-amber-600">{{ $totalYouth > 0 ? round((($stats['verifData']['pending'] ?? 0) / $totalYouth) * 100, 1) : 0 }}% perlu verifikasi</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 3: PENDIDIKAN & AKADEMIK -->
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="w-2.5 h-6 bg-sky-600 rounded-full inline-block"></span>
        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">3. Jenjang Pendidikan, Status &amp; Riwayat Akademik</h3>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart Jenjang Pendidikan -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-mortarboard text-sky-600"></i>
                        <span>Jenjang Pendidikan Terakhir</span>
                    </h4>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="chartEduLevel"></canvas>
                </div>
            </div>
            <!-- Quick Edu Grid -->
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 mt-4 pt-4 border-t border-slate-100 text-center">
                @foreach($stats['eduLevelStats'] ?? [] as $el)
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-600 truncate" title="{{ $el['name'] }}">{{ $el['name'] }}</div>
                        <div class="text-sm font-black text-sky-600">{{ number_format($el['total']) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chart Status Pendidikan Aktif vs Selesai -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-book-half text-sky-600"></i>
                        <span>Status Pendidikan Saat Ini</span>
                    </h4>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="chartEduStatus"></canvas>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-100 text-center">
                <div class="p-2.5 rounded-xl bg-sky-50 border border-sky-100">
                    <div class="text-[11px] font-bold text-sky-700">Sedang Belajar</div>
                    <div class="text-lg font-black text-sky-900">{{ number_format($stats['eduStatusData']['sedang_menempuh']['total'] ?? 0) }}</div>
                    <div class="text-[10px] text-sky-600">{{ $stats['eduStatusData']['sedang_menempuh']['percent'] ?? 0 }}% aktif sekolah/kuliah</div>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-700">Lulus / Tidak Sekolah</div>
                    <div class="text-lg font-black text-slate-900">{{ number_format($stats['eduStatusData']['sudah_lulus']['total'] ?? 0) }}</div>
                    <div class="text-[10px] text-slate-500">{{ $stats['eduStatusData']['sudah_lulus']['percent'] ?? 0 }}% telah selesai</div>
                </div>
            </div>
        </div>

        <!-- Top Sekolah & Top Jurusan -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-building text-slate-700"></i>
                    <span>Top 10 Lembaga Pendidikan / Kampus / Sekolah</span>
                </h4>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Berdasarkan Data</span>
            </div>
            <div class="space-y-2">
                @forelse($stats['topSchools'] ?? [] as $idx => $sch)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-5 h-5 rounded-md bg-sky-100 text-sky-800 text-[10px] font-extrabold flex items-center justify-center flex-shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <span class="text-xs font-bold text-slate-800 uppercase truncate" title="{{ $sch['school_name'] }}">
                                {{ $sch['school_name'] }}
                            </span>
                        </div>
                        <span class="text-xs font-black text-slate-900 flex-shrink-0 ml-2">
                            {{ number_format($sch['total']) }} <span class="text-[10px] font-normal text-slate-400">pemuda</span>
                        </span>
                    </div>
                @empty
                    <div class="text-center text-xs text-slate-400 py-4">Belum ada data sekolah</div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-journal-bookmark-fill text-indigo-600"></i>
                    <span>Jurusan / Program Studi Terdata</span>
                </h4>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Peminatan</span>
            </div>
            <div class="space-y-2">
                @forelse($stats['topMajors'] ?? [] as $idx => $maj)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50/50 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-5 h-5 rounded-md bg-indigo-100 text-indigo-800 text-[10px] font-extrabold flex items-center justify-center flex-shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <span class="text-xs font-bold text-slate-800 uppercase truncate" title="{{ $maj['major_name'] }}">
                                {{ $maj['major_name'] }}
                            </span>
                        </div>
                        <span class="text-xs font-black text-slate-900 flex-shrink-0 ml-2">
                            {{ number_format($maj['total']) }} <span class="text-[10px] font-normal text-slate-400">pemuda</span>
                        </span>
                    </div>
                @empty
                    <div class="text-center text-xs text-slate-400 py-4">Belum ada data jurusan</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- SECTION 4: POTENSI DAKWAH, KEAHLIAN & MINAT -->
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="w-2.5 h-6 bg-amber-500 rounded-full inline-block"></span>
        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">4. Element Dakwah, Bakat / Keahlian &amp; Minat Pemuda</h3>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Element Dakwah MTA -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-shield-shaded text-indigo-600"></i>
                        <span>Keikutsertaan Element Dakwah / Organisasi MTA</span>
                    </h4>
                    <span class="text-[11px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200">
                        {{ $percentOrg }}% Tergabung
                    </span>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="chartOrg"></canvas>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-4 pt-4 border-t border-slate-100 text-center">
                @foreach($stats['orgStats'] ?? [] as $os)
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-600 truncate" title="{{ $os['name'] }}">{{ $os['name'] }}</div>
                        <div class="text-sm font-black text-indigo-600">{{ number_format($os['total']) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Tingkat Kemahiran & Top Keahlian -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-lightning-charge-fill text-amber-500"></i>
                        <span>Bakat &amp; Keahlian Khusus Pemuda</span>
                    </h4>
                    <span class="text-[11px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                        {{ $totalWithSkill }} Pemuda
                    </span>
                </div>

                <!-- Skill Levels Bar -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-center">
                        <div class="text-[10px] font-bold text-amber-700">Pemula (Basic)</div>
                        <div class="text-base font-black text-amber-800">{{ number_format($stats['skillLevelData']['pemula']['total'] ?? 0) }}</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-sky-50 border border-sky-200 text-center">
                        <div class="text-[10px] font-bold text-sky-700">Menengah (Interm.)</div>
                        <div class="text-base font-black text-sky-800">{{ number_format($stats['skillLevelData']['menengah']['total'] ?? 0) }}</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                        <div class="text-[10px] font-bold text-emerald-700">Mahir (Advanced)</div>
                        <div class="text-base font-black text-emerald-800">{{ number_format($stats['skillLevelData']['mahir']['total'] ?? 0) }}</div>
                    </div>
                </div>

                <!-- Top Skills List -->
                <div class="space-y-2">
                    @forelse($stats['topSkills'] ?? [] as $sk)
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50">
                            <span class="text-xs font-semibold text-slate-800 flex items-center gap-2 truncate">
                                <i class="bi bi-check2-circle text-amber-500"></i>
                                {{ $sk['name'] }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-xs font-bold flex-shrink-0">
                                {{ number_format($sk['total']) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-xs text-slate-400 py-3">Belum ada data keahlian</div>
                    @endforelse
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-center text-xs text-slate-400">
                Data keahlian didapatkan dari form isian pendataan bakat &amp; skill pemuda.
            </div>
        </div>

        <!-- Minat & Potensi Pengembangan Diri -->
        <div class="lg:col-span-12 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-stars text-amber-500"></i>
                    <span>Sebaran Minat &amp; Potensi Pengembangan Diri Pemuda</span>
                </h4>
                <span class="text-[11px] text-slate-400 font-medium">Berdasarkan Preferensi</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                @forelse($stats['topInterests'] ?? [] as $in)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-amber-50/50 hover:border-amber-200 transition flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-800 truncate" title="{{ $in['name'] }}">
                                {{ $in['name'] }}
                            </div>
                            <div class="text-[10px] text-slate-400">Minat &amp; Potensi</div>
                        </div>
                        <span class="w-7 h-7 rounded-xl bg-amber-100 text-amber-800 text-xs font-black flex items-center justify-center flex-shrink-0">
                            {{ $in['total'] }}
                        </span>
                    </div>
                @empty
                    <div class="col-span-5 text-center text-xs text-slate-400 py-3">Belum ada data minat</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- SECTION 5: PROFESI, PEKERJAAN & WIRAUSAHA -->
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="w-2.5 h-6 bg-emerald-600 rounded-full inline-block"></span>
        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">5. Status Pekerjaan &amp; Potensi Wirausaha Pemuda</h3>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Status Pekerjaan -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-briefcase-fill text-emerald-600"></i>
                        <span>Kategori Status Pekerjaan</span>
                    </h4>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="chartJob"></canvas>
                </div>
            </div>
            <!-- Job Badges -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4 pt-4 border-t border-slate-100 text-center">
                @foreach($stats['jobStats'] ?? [] as $js)
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-600 truncate" title="{{ $js['name'] }}">{{ $js['name'] }}</div>
                        <div class="text-sm font-black text-emerald-600">{{ number_format($js['total']) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Ragam Bidang Wirausaha -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-shop-window text-emerald-600"></i>
                        <span>Bidang Usaha Pelaku Wirausaha</span>
                    </h4>
                    <span class="text-[11px] text-emerald-700 font-bold bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                        {{ $totalWirausaha }} Pemilik Usaha
                    </span>
                </div>

                <div class="space-y-2.5">
                    @forelse($stats['topBizFields'] ?? [] as $idx => $biz)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-emerald-50/40 border border-emerald-100 hover:bg-emerald-50 transition">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                    <i class="bi bi-tag-fill text-xs"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 uppercase truncate">
                                    {{ $biz['name'] }}
                                </span>
                            </div>
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-600 text-white text-xs font-black flex-shrink-0">
                                {{ number_format($biz['total']) }} unit
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-xs text-slate-400 py-6">
                            Belum ada rincian bidang wirausaha pemuda
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-center text-xs text-slate-400">
                Peluang kolaborasi dan jaringan ekonomi mandiri antar pemuda MTA Sragen.
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Chart color palette helpers
        const brandRed    = '#dc2626';
        const brandIndigo = '#4f46e5';
        const brandSky    = '#0284c7';
        const brandAmber  = '#d97706';
        const brandGreen  = '#10b981';
        const brandPurple = '#8b5cf6';
        const brandPink   = '#ec4899';
        const brandSlate  = '#94a3b8';

        // 1. Chart Sebaran Wilayah
        const wilayahLabels = @json(array_column($stats['wilayahStats'] ?? [], 'name'));
        const wilayahTotals = @json(array_column($stats['wilayahStats'] ?? [], 'total'));
        const ctxWilayah = document.getElementById('chartWilayah');
        if (ctxWilayah && wilayahLabels.length > 0) {
            new Chart(ctxWilayah, {
                type: 'bar',
                data: {
                    labels: wilayahLabels,
                    datasets: [{
                        label: 'Jumlah Pemuda',
                        data: wilayahTotals,
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
                        borderRadius: 8,
                        barThickness: 36
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} Pemuda`
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: '#f1f5f9' },
                            ticks: { precision: 0 }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 2. Chart Top 10 Cabang
        const cabangLabels = @json(array_column($stats['topCabangStats'] ?? [], 'name'));
        const cabangTotals = @json(array_column($stats['topCabangStats'] ?? [], 'total'));
        const ctxCabang = document.getElementById('chartTopCabang');
        if (ctxCabang && cabangLabels.length > 0) {
            new Chart(ctxCabang, {
                type: 'bar',
                data: {
                    labels: cabangLabels,
                    datasets: [{
                        label: 'Jumlah Pemuda',
                        data: cabangTotals,
                        backgroundColor: '#6366f1',
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.x} Pemuda`
                            }
                        }
                    },
                    scales: {
                        x: { 
                            beginAtZero: true, 
                            grid: { color: '#f1f5f9' },
                            ticks: { precision: 0 }
                        },
                        y: { grid: { display: false } }
                    }
                }
            });
        }

        // 3. Chart Usia
        const ageLabels = @json(array_column($stats['ageData'] ?? [], 'label'));
        const ageTotals = @json(array_column($stats['ageData'] ?? [], 'total'));
        const ctxAge = document.getElementById('chartAge');
        if (ctxAge && ageLabels.length > 0) {
            new Chart(ctxAge, {
                type: 'bar',
                data: {
                    labels: ageLabels,
                    datasets: [{
                        label: 'Pemuda',
                        data: ageTotals,
                        backgroundColor: '#f59e0b',
                        borderRadius: 8,
                        barThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} Pemuda`
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: '#f1f5f9' },
                            ticks: { precision: 0 }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 4. Chart Golongan Darah
        const bloodLabels = ['Golongan A', 'Golongan B', 'Golongan AB', 'Golongan O', 'Belum Tercatat'];
        const bloodTotals = [
            {{ (int) ($stats['bloodTypeData']['A'] ?? 0) }},
            {{ (int) ($stats['bloodTypeData']['B'] ?? 0) }},
            {{ (int) ($stats['bloodTypeData']['AB'] ?? 0) }},
            {{ (int) ($stats['bloodTypeData']['O'] ?? 0) }},
            {{ (int) ($stats['bloodTypeData']['unknown'] ?? 0) }}
        ];
        const ctxBlood = document.getElementById('chartBlood');
        if (ctxBlood) {
            new Chart(ctxBlood, {
                type: 'doughnut',
                data: {
                    labels: bloodLabels,
                    datasets: [{
                        data: bloodTotals,
                        backgroundColor: ['#ef4444', '#3b82f6', '#8b5cf6', '#10b981', '#cbd5e1'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.raw} Pemuda`
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // 5. Chart Status Pernikahan
        const maritalLabels = ['Belum Menikah', 'Sudah Menikah', 'Duda / Janda'];
        const maritalTotals = [
            {{ (int) ($stats['maritalData']['belum_menikah'] ?? 0) }},
            {{ (int) ($stats['maritalData']['sudah_menikah'] ?? 0) }},
            {{ (int) (($stats['maritalData']['duda'] ?? 0) + ($stats['maritalData']['janda'] ?? 0)) }}
        ];
        const ctxMarital = document.getElementById('chartMarital');
        if (ctxMarital) {
            new Chart(ctxMarital, {
                type: 'doughnut',
                data: {
                    labels: maritalLabels,
                    datasets: [{
                        data: maritalTotals,
                        backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.raw} Pemuda`
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // 6. Chart Status Verifikasi API MTA
        const verifLabels = ['Terverifikasi Pusat', 'Belum Terverifikasi (Pending)'];
        const verifTotals = [
            {{ (int) ($stats['verifData']['verified'] ?? 0) }},
            {{ (int) ($stats['verifData']['pending'] ?? 0) }}
        ];
        const ctxVerif = document.getElementById('chartVerif');
        if (ctxVerif) {
            new Chart(ctxVerif, {
                type: 'doughnut',
                data: {
                    labels: verifLabels,
                    datasets: [{
                        data: verifTotals,
                        backgroundColor: ['#10b981', '#f59e0b'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.raw} Pemuda`
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // 7. Chart Jenjang Pendidikan
        const eduLabels = @json(array_column($stats['eduLevelStats'] ?? [], 'name'));
        const eduTotals = @json(array_column($stats['eduLevelStats'] ?? [], 'total'));
        const ctxEduLevel = document.getElementById('chartEduLevel');
        if (ctxEduLevel && eduLabels.length > 0) {
            new Chart(ctxEduLevel, {
                type: 'bar',
                data: {
                    labels: eduLabels,
                    datasets: [{
                        label: 'Pemuda',
                        data: eduTotals,
                        backgroundColor: '#0284c7',
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.x} Pemuda`
                            }
                        }
                    },
                    scales: {
                        x: { 
                            beginAtZero: true, 
                            grid: { color: '#f1f5f9' },
                            ticks: { precision: 0 }
                        },
                        y: { grid: { display: false } }
                    }
                }
            });
        }

        // 8. Chart Status Pendidikan Aktif vs Lulus
        const ctxEduStatus = document.getElementById('chartEduStatus');
        if (ctxEduStatus) {
            new Chart(ctxEduStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Sedang Sekolah / Kuliah', 'Lulus / Tidak Sekolah'],
                    datasets: [{
                        data: [
                            {{ (int) ($stats['eduStatusData']['sedang_menempuh']['total'] ?? 0) }},
                            {{ (int) ($stats['eduStatusData']['sudah_lulus']['total'] ?? 0) }}
                        ],
                        backgroundColor: ['#0284c7', '#94a3b8'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.raw} Pemuda`
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // 9. Chart Element Dakwah / Organisasi MTA
        const orgLabels = @json(array_column($stats['orgStats'] ?? [], 'name'));
        const orgTotals = @json(array_column($stats['orgStats'] ?? [], 'total'));
        const ctxOrg = document.getElementById('chartOrg');
        if (ctxOrg && orgLabels.length > 0) {
            new Chart(ctxOrg, {
                type: 'bar',
                data: {
                    labels: orgLabels,
                    datasets: [{
                        label: 'Jumlah Pemuda',
                        data: orgTotals,
                        backgroundColor: '#4f46e5',
                        borderRadius: 8,
                        barThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} Pemuda`
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: '#f1f5f9' },
                            ticks: { precision: 0 }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 10. Chart Status Pekerjaan
        const jobLabels = @json(array_column($stats['jobStats'] ?? [], 'name'));
        const jobTotals = @json(array_column($stats['jobStats'] ?? [], 'total'));
        const ctxJob = document.getElementById('chartJob');
        if (ctxJob && jobLabels.length > 0) {
            new Chart(ctxJob, {
                type: 'bar',
                data: {
                    labels: jobLabels,
                    datasets: [{
                        label: 'Pemuda',
                        data: jobTotals,
                        backgroundColor: '#059669',
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.x} Pemuda`
                            }
                        }
                    },
                    scales: {
                        x: { 
                            beginAtZero: true, 
                            grid: { color: '#f1f5f9' },
                            ticks: { precision: 0 }
                        },
                        y: { grid: { display: false } }
                    }
                }
            });
        }

        // Dynamic AJAX Wilayah -> Cabang
        const filterWilayah = document.getElementById('filterWilayah');
        const filterCabang  = document.getElementById('filterCabang');
        if (filterWilayah && filterCabang) {
            filterWilayah.addEventListener('change', function () {
                const wId = this.value;
                filterCabang.innerHTML = '<option value="">-- Semua Cabang --</option>';
                if (!wId) return;

                fetch(`{{ url('api/cabang') }}/${wId}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = c.name;
                            filterCabang.appendChild(opt);
                        });
                    })
                    .catch(err => console.error('Error fetching cabang:', err));
            });
        }
    });
</script>
@endsection
