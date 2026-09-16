@extends('admin.layouts.main')

@section('title', $title ?? 'Persebaran Data Pemuda')

@section('content')

@php
    $userRole    = session('role') ?? auth()->user()?->role?->name;
    $wilayahName = session('wilayah_name') ?? (session('wilayah_id') ? 'Wilayah ' . session('wilayah_id') : null);
    $cabangName  = session('cabang_name') ?? (session('cabang_id') ? 'Cabang ' . session('cabang_id') : null);

    $totalYouth       = $stats['totalYouth'] ?? 0;
    $totalWithOrg     = $stats['totalWithOrg'] ?? 0;
    $percentOrg       = $totalYouth > 0 ? round(($totalWithOrg / $totalYouth) * 100, 1) : 0;

    $eduStatusActive  = $stats['eduStatusData']['sedang_menempuh']['total'] ?? 0;
    $percentActiveEdu = $totalYouth > 0 ? round(($eduStatusActive / $totalYouth) * 100, 1) : 0;

    $totalWithSkill   = $stats['totalWithSkill'] ?? 0;
    $percentSkill     = $totalYouth > 0 ? round(($totalWithSkill / $totalYouth) * 100, 1) : 0;

    $totalWirausaha   = $stats['totalWirausaha'] ?? 0;
    $percentWirausaha = $totalYouth > 0 ? round(($totalWirausaha / $totalYouth) * 100, 1) : 0;
@endphp

<!-- HEADER HERO BANNER -->
<div class="mb-6 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-6 sm:p-8 text-white shadow-xl border border-slate-700/50">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
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
                Analisis visual sebaran element dakwah, jenjang pendidikan, keahlian &amp; potensi, minat, pekerjaan, serta demografi usia dan golongan darah.
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
                <span>Export Data</span>
            </a>
        </div>
    </div>
</div>

<!-- INTERACTIVE FILTER CARD -->
<div class="mb-6 rounded-3xl bg-white p-5 sm:p-6 border border-slate-200/80 shadow-sm">
    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
        <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="bi bi-funnel-fill text-red-600"></i>
            <span>Filter Interaktif Persebaran Data</span>
        </h3>
        @if (!empty($filters['wilayah_id']) || !empty($filters['cabang_id']) || !empty($filters['blood_type']) || (!empty($filters['gender']) && !in_array($userRole, ['admin_pemuda', 'admin_pemudi', 'admin_wilayah_pemuda'], true)))
            <span class="px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-[10px] font-bold border border-red-200">
                Filter Aktif
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
            <a href="{{ route('admin.persebaran') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition" title="Reset Filter">
                <i class="bi bi-arrow-clockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- DEMOGRAPHY OVERVIEW METRICS (4 CARDS) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-diagram-3-fill"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Ikut Element/Org</div>
            <div class="text-xl font-black text-slate-900">{{ number_format($totalWithOrg) }}</div>
            <div class="text-[11px] text-indigo-600 font-semibold">{{ $percentOrg }}% dari total pemuda</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Sedang Belajar/Kuliah</div>
            <div class="text-xl font-black text-slate-900">{{ number_format($eduStatusActive) }}</div>
            <div class="text-[11px] text-sky-600 font-semibold">{{ $percentActiveEdu }}% dari total pemuda</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-lightning-charge-fill"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Memiliki Keahlian</div>
            <div class="text-xl font-black text-slate-900">{{ number_format($totalWithSkill) }}</div>
            <div class="text-[11px] text-amber-600 font-semibold">{{ $percentSkill }}% dari total pemuda</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
            <i class="bi bi-shop"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Pelaku Wirausaha</div>
            <div class="text-xl font-black text-slate-900">{{ number_format($totalWirausaha) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold">{{ $percentWirausaha }}% dari total pemuda</div>
        </div>
    </div>
</div>

<!-- CHARTS GRID -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <!-- Chart Element / Organisasi -->
    <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-shield-check text-indigo-600"></i>
                <span>Element Dakwah / Organisasi</span>
            </h3>
        </div>
        <div class="h-64 sm:h-72">
            <canvas id="chartOrg"></canvas>
        </div>
    </div>

    <!-- Chart Usia / Demografi -->
    <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-calendar-event text-amber-600"></i>
                <span>Distribusi Kelompok Usia</span>
            </h3>
        </div>
        <div class="h-64 sm:h-72">
            <canvas id="chartAge"></canvas>
        </div>
    </div>

    <!-- Chart Jenjang Pendidikan -->
    <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-mortarboard text-sky-600"></i>
                <span>Jenjang Pendidikan Terakhir</span>
            </h3>
        </div>
        <div class="h-64 sm:h-72">
            <canvas id="chartEducation"></canvas>
        </div>
    </div>

    <!-- Chart Golongan Darah -->
    <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-droplet-fill text-red-600"></i>
                <span>Distribusi Golongan Darah</span>
            </h3>
        </div>
        <div class="h-64 sm:h-72">
            <canvas id="chartBlood"></canvas>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Element Org Chart
        const orgLabels = {!! json_encode(array_keys($stats['orgData'] ?? [])) !!};
        const orgTotals = {!! json_encode(array_values($stats['orgData'] ?? [])) !!};
        const ctxOrg = document.getElementById('chartOrg');
        if (ctxOrg) {
            new Chart(ctxOrg, {
                type: 'bar',
                data: {
                    labels: orgLabels,
                    datasets: [{
                        label: 'Jumlah Pemuda',
                        data: orgTotals,
                        backgroundColor: '#4f46e5',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Age Groups Chart
        const ageLabels = {!! json_encode(array_keys($stats['ageGroups'] ?? [])) !!};
        const ageTotals = {!! json_encode(array_values($stats['ageGroups'] ?? [])) !!};
        const ctxAge = document.getElementById('chartAge');
        if (ctxAge) {
            new Chart(ctxAge, {
                type: 'bar',
                data: {
                    labels: ageLabels,
                    datasets: [{
                        label: 'Pemuda',
                        data: ageTotals,
                        backgroundColor: '#d97706',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Education Chart
        const eduLabels = {!! json_encode(array_column($stats['educationStats'] ?? [], 'name')) !!};
        const eduTotals = {!! json_encode(array_column($stats['educationStats'] ?? [], 'total')) !!};
        const ctxEdu = document.getElementById('chartEducation');
        if (ctxEdu) {
            new Chart(ctxEdu, {
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
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                        y: { grid: { display: false } }
                    }
                }
            });
        }

        // Blood Chart
        const bloodLabels = ['A', 'B', 'AB', 'O', 'Belum Tahu'];
        const bloodTotals = [
            {{ $stats['bloodTypeData']['A'] ?? 0 }},
            {{ $stats['bloodTypeData']['B'] ?? 0 }},
            {{ $stats['bloodTypeData']['AB'] ?? 0 }},
            {{ $stats['bloodTypeData']['O'] ?? 0 }},
            {{ $stats['bloodTypeData']['unknown'] ?? 0 }}
        ];
        const ctxBlood = document.getElementById('chartBlood');
        if (ctxBlood) {
            new Chart(ctxBlood, {
                type: 'doughnut',
                data: {
                    labels: bloodLabels,
                    datasets: [{
                        data: bloodTotals,
                        backgroundColor: ['#ef4444', '#f97316', '#8b5cf6', '#10b981', '#94a3b8'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    cutout: '60%'
                }
            });
        }

        // AJAX dynamic filter cabang by wilayah
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
                    .catch(err => console.error(err));
            });
        }
    });
</script>
@endsection
