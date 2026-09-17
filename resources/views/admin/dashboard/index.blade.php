@extends('admin.layouts.main')

@section('title', $title ?? 'Dashboard Admin')

@section('content')

@php
    $userRole    = session('role') ?? auth()->user()?->role?->name;
    $wilayahName = session('wilayah_name') ?? (session('wilayah_id') ? 'Wilayah ' . session('wilayah_id') : null);
    $cabangName  = session('cabang_name') ?? (session('cabang_id') ? 'Cabang ' . session('cabang_id') : null);
@endphp

<!-- WELCOME HERO BANNER -->
<div class="mb-6 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-red-950 p-6 sm:p-8 text-white shadow-xl border border-slate-700/50 relative overflow-hidden">
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-red-200 mb-3">
                <i class="bi bi-shield-check text-red-400"></i>
                @if ($userRole === 'superadmin')
                    Dashboard Super Administrator &bull; Seluruh Sistem
                @elseif ($userRole === 'admin_pemuda')
                    Dashboard Admin Pemuda (L) &bull; Seluruh Sragen
                @elseif ($userRole === 'admin_pemudi')
                    Dashboard Admin Pemudi (P) &bull; Seluruh Sragen
                @elseif ($userRole === 'admin_wilayah')
                    Dashboard Admin Wilayah &bull; {{ $wilayahName }}
                @elseif ($userRole === 'admin_wilayah_pemuda')
                    Dashboard Admin Wilayah Pemuda (L) &bull; {{ $wilayahName }}
                @else
                    Dashboard Admin Cabang &bull; {{ $cabangName }}
                @endif
            </div>

            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-2">
                Selamat Datang, {{ $user['name'] ?? auth()->user()?->name ?? 'Administrator' }}! 👋
            </h2>
            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                @if ($userRole === 'superadmin')
                    Pantau seluruh data pemuda, distribusi wilayah &amp; cabang, jenjang pendidikan, pekerjaan, serta verifikasi pendaftaran di Kabupaten Sragen.
                @elseif ($userRole === 'admin_pemuda')
                    Kelola, analisis, dan verifikasi seluruh data pemuda berjenis kelamin <strong>Laki-laki</strong> di seluruh wilayah &amp; cabang Kabupaten Sragen.
                @elseif ($userRole === 'admin_pemudi')
                    Kelola, analisis, dan verifikasi seluruh data pemudi berjenis kelamin <strong>Perempuan</strong> di seluruh wilayah &amp; cabang Kabupaten Sragen.
                @elseif ($userRole === 'admin_wilayah' || $userRole === 'admin_wilayah_pemuda')
                    Pantau dan analisis sebaran data pemuda pada seluruh cabang dalam lingkup <strong>{{ $wilayahName }}</strong>.
                @else
                    Kelola, input, dan verifikasi data pemuda khusus pada lingkup <strong>{{ $cabangName }}</strong>.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-2.5 lg:justify-end flex-shrink-0">
            <a href="{{ route('admin.persebaran') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs transition shadow-md">
                <i class="bi bi-pie-chart-fill"></i>
                <span>Persebaran Data</span>
            </a>
            <a href="{{ route('admin.pemuda.tambah') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md">
                <i class="bi bi-person-plus-fill"></i>
                <span>Tambah Pemuda</span>
            </a>
            <a href="{{ route('admin.pemuda.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition border border-slate-700">
                <i class="bi bi-table"></i>
                <span>Kelola Data</span>
            </a>
            <a href="{{ route('admin.pemuda.export') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition border border-slate-700">
                <i class="bi bi-file-earmark-excel-fill text-emerald-400"></i>
                <span>Export Excel</span>
            </a>
        </div>
    </div>
</div>

<!-- STATS SUMMARY CARDS (ROW 1) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Card Total -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-700">Total Pemuda</span>
            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                <i class="bi bi-people-fill text-lg"></i>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            {{ number_format($stats['summary']['total'] ?? 0) }}
        </div>
        <a href="{{ route('admin.pemuda.index') }}" class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-red-600 font-semibold hover:text-red-700">
            <span>Lihat Semua</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <!-- Card Verified -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-700">Terverifikasi (Pusat)</span>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i class="bi bi-patch-check-fill text-lg"></i>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-emerald-600 tracking-tight">
            {{ number_format($stats['summary']['verified'] ?? 0) }}
        </div>
        <a href="{{ route('admin.pemuda.index', ['status_verifikasi' => 'verified']) }}" class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-emerald-600 font-semibold hover:text-emerald-700">
            <span>Data Terverifikasi</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <!-- Card Pending -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-700">Belum Terverifikasi</span>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class="bi bi-clock-history text-lg"></i>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-amber-600 tracking-tight">
            {{ number_format($stats['summary']['pending'] ?? 0) }}
        </div>
        <a href="{{ route('admin.pemuda.index', ['status_verifikasi' => 'pending']) }}" class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-amber-600 font-semibold hover:text-amber-700">
            <span>Data Pending</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <!-- Card Cabang -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-700">Cabang Binaan</span>
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                <i class="bi bi-diagram-3-fill text-lg"></i>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-sky-600 tracking-tight">
            {{ number_format($stats['totalCabang'] ?? 0) }}
        </div>
        <a href="{{ $userRole === 'superadmin' ? route('admin.cabang.index') : route('admin.pemuda.index') }}" class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-sky-600 font-semibold hover:text-sky-700">
            <span>Kelola Cabang</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<!-- STATS SUMMARY (ROW 2) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
            <i class="bi bi-geo-alt-fill text-lg"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Wilayah</div>
            <div class="text-base font-bold text-slate-900">{{ number_format($stats['totalWilayah'] ?? 4) }} Wilayah</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
            <i class="bi bi-shield-lock-fill text-lg"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Pengguna &amp; Akses</div>
            <div class="text-base font-bold text-slate-900">{{ number_format($stats['totalUsers'] ?? 0) }} User</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
            <i class="bi bi-gender-male text-lg"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Laki-Laki</div>
            <div class="text-base font-bold text-slate-900">{{ number_format($stats['genderData']['L'] ?? 0) }} Pemuda</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center flex-shrink-0">
            <i class="bi bi-gender-female text-lg"></i>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-semibold text-slate-700 uppercase">Perempuan</div>
            <div class="text-base font-bold text-slate-900">{{ number_format($stats['genderData']['P'] ?? 0) }} Pemudi</div>
        </div>
    </div>
</div>

@php
    $isWilayahRole = in_array($userRole, ['admin_wilayah', 'admin_wilayah_pemuda'], true);
    $isCabangRole  = ($userRole === 'admin_cabang');
@endphp

<!-- CHARTS SECTION (GRID 2 COLUMNS) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <!-- Chart Sebaran Wilayah / Cabang -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-bar-chart-fill text-red-600"></i>
                <span id="wilayahChartTitle">
                    @if($isWilayahRole)
                        Sebaran Pemuda per Cabang ({{ $wilayahName }})
                    @elseif($isCabangRole)
                        Statistik Pemuda Cabang ({{ $cabangName }})
                    @else
                        Sebaran Pemuda per Wilayah
                    @endif
                </span>
            </h3>
            <div class="flex items-center gap-1.5">
                @if(!$isWilayahRole && !$isCabangRole)
                    <div class="inline-flex rounded-xl p-0.5 bg-slate-100 border border-slate-200 text-[11px] font-semibold">
                        <button type="button" id="btnChartWilayah" onclick="switchWilayahChart('wilayah')" class="px-2.5 py-1 rounded-lg transition bg-white text-slate-900 shadow-xs">
                            Wilayah
                        </button>
                        <button type="button" id="btnChartCabang" onclick="switchWilayahChart('cabang')" class="px-2.5 py-1 rounded-lg transition text-slate-500 hover:text-slate-800">
                            Top 10 Cabang
                        </button>
                    </div>
                @else
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-[11px] font-medium text-slate-600">
                        {{ $isWilayahRole ? $wilayahName : $cabangName }}
                    </span>
                @endif
            </div>
        </div>

        <div class="h-64 sm:h-72">
            <canvas id="chartWilayah"></canvas>
        </div>

        <!-- Summary Grid under chart -->
        @if($isWilayahRole)
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mt-4">
                @foreach(array_slice($stats['topCabangStats'] ?? [], 0, 5) as $idx => $c)
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                        <div class="text-[11px] font-bold text-slate-700 truncate" title="{{ $c['name'] }}">{{ $c['name'] }}</div>
                        <div class="text-base font-black text-red-600">{{ number_format($c['total']) }}</div>
                        <div class="text-[10px] text-slate-400">Pemuda</div>
                    </div>
                @endforeach
            </div>
        @elseif(!$isCabangRole)
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4" id="wilayahSummaryGrid">
                @php $palette = ['text-blue-600', 'text-emerald-600', 'text-amber-600', 'text-purple-600']; @endphp
                @foreach($stats['wilayahStats'] ?? [] as $idx => $w)
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                        <div class="text-[11px] font-bold {{ $palette[$idx % 4] }}">{{ $w['name'] }}</div>
                        <div class="text-lg font-black text-slate-900">{{ number_format($w['total']) }}</div>
                        <div class="text-[10px] text-slate-400">{{ $w['code'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Chart Gender & Status Pernikahan -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-pie-chart-fill text-sky-600"></i>
                <span>Gender &amp; Status Pernikahan</span>
            </h3>
            <span class="text-[11px] text-slate-400 font-medium">Demografi</span>
        </div>
        <div class="grid grid-cols-2 gap-4 items-center">
            <div class="text-center">
                <div class="h-36 sm:h-44">
                    <canvas id="chartGender"></canvas>
                </div>
                <div class="mt-2 text-xs font-bold text-slate-700">Jenis Kelamin</div>
                <div class="flex justify-center gap-3 text-[11px] mt-1">
                    <span class="text-blue-600 font-semibold">L: {{ number_format($stats['genderData']['L'] ?? 0) }}</span>
                    <span class="text-pink-600 font-semibold">P: {{ number_format($stats['genderData']['P'] ?? 0) }}</span>
                </div>
            </div>
            <div class="text-center border-l border-slate-100 pl-4">
                <div class="h-36 sm:h-44">
                    <canvas id="chartMarital"></canvas>
                </div>
                <div class="mt-2 text-xs font-bold text-slate-700">Status Pernikahan</div>
                <div class="flex flex-wrap justify-center gap-2 text-[10px] mt-1">
                    <span class="text-emerald-600 font-semibold">Belum: {{ number_format($stats['maritalData']['belum_menikah'] ?? $stats['maritalData']['lajang'] ?? 0) }}</span>
                    <span class="text-amber-600 font-semibold">Nikah: {{ number_format($stats['maritalData']['sudah_menikah'] ?? $stats['maritalData']['menikah'] ?? 0) }}</span>
                    @if((($stats['maritalData']['duda'] ?? 0) + ($stats['maritalData']['janda'] ?? 0)) > 0)
                        <span class="text-purple-600 font-semibold">Duda/Janda: {{ number_format(($stats['maritalData']['duda'] ?? 0) + ($stats['maritalData']['janda'] ?? 0)) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHARTS SECTION ROW 2: PENDIDIKAN & PEKERJAAN -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-mortarboard-fill text-indigo-600"></i>
                <span>Distribusi Tingkat Pendidikan</span>
            </h3>
            <span class="text-[11px] text-slate-400 font-medium">Jenjang</span>
        </div>
        <div class="h-64 sm:h-72">
            <canvas id="chartEducation"></canvas>
        </div>
    </div>

    <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-briefcase-fill text-emerald-600"></i>
                <span>Status Pekerjaan</span>
            </h3>
            <span class="text-[11px] text-slate-400 font-medium">Profesi</span>
        </div>
        <div class="h-64 sm:h-72">
            <canvas id="chartJob"></canvas>
        </div>
    </div>
</div>

<!-- RECENT REGISTRATIONS TABLE -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-clock-history text-red-600"></i>
                <span>Pendaftaran Pemuda Terbaru</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Data registrasi pemuda yang baru masuk ke dalam sistem</p>
        </div>
        <a href="{{ route('admin.pemuda.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition self-start sm:self-auto">
            <span>Lihat Semua Data</span>
            <i class="bi bi-arrow-right text-xs"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/75 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                    <th class="py-3 px-4">No. Registrasi</th>
                    <th class="py-3 px-4">Nama Lengkap</th>
                    <th class="py-3 px-4">Gender</th>
                    <th class="py-3 px-4">Cabang</th>
                    <th class="py-3 px-4">Wilayah</th>
                    <th class="py-3 px-4">Verifikasi</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($stats['recentPemuda'] ?? $stats['recentRegistrations'] ?? $stats['recentUpdates'] ?? [] as $p)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-4 font-mono font-bold text-red-600">{{ $p['registration_number'] }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $p['name'] }}</td>
                        <td class="py-3 px-4">
                            @if($p['gender'] === 'L')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[10px] font-bold">
                                    <i class="bi bi-gender-male"></i> L
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-pink-50 text-pink-700 text-[10px] font-bold">
                                    <i class="bi bi-gender-female"></i> P
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $p['cabang_name'] ?? '-' }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $p['wilayah_name'] ?? '-' }}</td>
                        <td class="py-3 px-4">
                            @if(($p['status_verifikasi'] ?? '') === 'verified')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">
                                    <i class="bi bi-check-circle-fill"></i> Terverifikasi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">
                                    <i class="bi bi-clock"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('admin.pemuda.detail', $p['id']) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-700 text-[11px] font-semibold transition">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            <i class="bi bi-inbox text-3xl block mb-2"></i>
                            Belum ada data pendaftaran pemuda terbaru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isWilayahRole = {{ in_array($userRole, ['admin_wilayah', 'admin_wilayah_pemuda'], true) ? 'true' : 'false' }};
        const isCabangRole  = {{ $userRole === 'admin_cabang' ? 'true' : 'false' }};

        // 1. Data Wilayah & Top Cabang
        const wilayahLabels   = @json(array_column($stats['wilayahStats'] ?? [], 'name'));
        const wilayahTotals   = @json(array_column($stats['wilayahStats'] ?? [], 'total'));
        const topCabangLabels = @json(array_column($stats['topCabangStats'] ?? [], 'name'));
        const topCabangTotals = @json(array_column($stats['topCabangStats'] ?? [], 'total'));

        let chartWilayahInstance = null;
        const ctxWilayah = document.getElementById('chartWilayah');

        if (ctxWilayah) {
            const initialLabels = isWilayahRole ? topCabangLabels : wilayahLabels;
            const initialTotals = isWilayahRole ? topCabangTotals : wilayahTotals;
            const initialColors = isWilayahRole 
                ? ['#dc2626', '#059669', '#d97706', '#7c3aed', '#0284c7', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16']
                : ['#dc2626', '#059669', '#d97706', '#7c3aed'];

            chartWilayahInstance = new Chart(ctxWilayah, {
                type: 'bar',
                data: {
                    labels: initialLabels,
                    datasets: [{
                        label: 'Jumlah Pemuda',
                        data: initialTotals,
                        backgroundColor: initialColors.slice(0, initialLabels.length),
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
                                    return ` ${context.parsed.y} Pemuda`;
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
                            ticks: {
                                font: { size: 11 },
                                maxRotation: isWilayahRole ? 45 : 0
                            }
                        }
                    }
                }
            });
        }

        window.switchWilayahChart = function(type) {
            if (!chartWilayahInstance) return;

            const btnW = document.getElementById('btnChartWilayah');
            const btnC = document.getElementById('btnChartCabang');
            const titleEl = document.getElementById('wilayahChartTitle');

            if (type === 'cabang') {
                chartWilayahInstance.data.labels = topCabangLabels;
                chartWilayahInstance.data.datasets[0].data = topCabangTotals;
                chartWilayahInstance.data.datasets[0].backgroundColor = [
                    '#dc2626', '#059669', '#d97706', '#7c3aed', '#0284c7',
                    '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16'
                ].slice(0, topCabangLabels.length);
                chartWilayahInstance.options.scales.x.ticks.maxRotation = 45;
                if (titleEl) titleEl.textContent = 'Top 10 Cabang dengan Pemuda Terbanyak';

                if (btnW && btnC) {
                    btnC.className = 'px-2.5 py-1 rounded-lg transition bg-white text-slate-900 shadow-xs';
                    btnW.className = 'px-2.5 py-1 rounded-lg transition text-slate-500 hover:text-slate-800';
                }
            } else {
                chartWilayahInstance.data.labels = wilayahLabels;
                chartWilayahInstance.data.datasets[0].data = wilayahTotals;
                chartWilayahInstance.data.datasets[0].backgroundColor = ['#dc2626', '#059669', '#d97706', '#7c3aed'];
                chartWilayahInstance.options.scales.x.ticks.maxRotation = 0;
                if (titleEl) titleEl.textContent = 'Sebaran Pemuda per Wilayah';

                if (btnW && btnC) {
                    btnW.className = 'px-2.5 py-1 rounded-lg transition bg-white text-slate-900 shadow-xs';
                    btnC.className = 'px-2.5 py-1 rounded-lg transition text-slate-500 hover:text-slate-800';
                }
            }
            chartWilayahInstance.update();
        };

        // 2. Gender Chart
        const ctxGender = document.getElementById('chartGender');
        if (ctxGender) {
            const lCount = {{ (int) ($stats['genderData']['L'] ?? 0) }};
            const pCount = {{ (int) ($stats['genderData']['P'] ?? 0) }};
            const genderTotal = lCount + pCount;

            new Chart(ctxGender, {
                type: 'doughnut',
                data: {
                    labels: ['Laki-laki', 'Perempuan'],
                    datasets: [{
                        data: genderTotal > 0 ? [lCount, pCount] : [1],
                        backgroundColor: genderTotal > 0 ? ['#2563eb', '#ec4899'] : ['#e2e8f0'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: genderTotal > 0,
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed;
                                    const pct = genderTotal > 0 ? ((val / genderTotal) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${val} (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        }

        // 3. Marital Chart
        const ctxMarital = document.getElementById('chartMarital');
        if (ctxMarital) {
            const belumMenikah = {{ (int) ($stats['maritalData']['belum_menikah'] ?? $stats['maritalData']['lajang'] ?? 0) }};
            const sudahMenikah = {{ (int) ($stats['maritalData']['sudah_menikah'] ?? $stats['maritalData']['menikah'] ?? 0) }};
            const duda = {{ (int) ($stats['maritalData']['duda'] ?? 0) }};
            const janda = {{ (int) ($stats['maritalData']['janda'] ?? 0) }};
            const maritalTotal = belumMenikah + sudahMenikah + duda + janda;

            const maritalLabels = ['Belum Menikah', 'Sudah Menikah'];
            const maritalTotals = [belumMenikah, sudahMenikah];
            const maritalColors = ['#10b981', '#f59e0b'];

            if (duda > 0) {
                maritalLabels.push('Duda');
                maritalTotals.push(duda);
                maritalColors.push('#8b5cf6');
            }
            if (janda > 0) {
                maritalLabels.push('Janda');
                maritalTotals.push(janda);
                maritalColors.push('#ec4899');
            }

            new Chart(ctxMarital, {
                type: 'doughnut',
                data: {
                    labels: maritalTotal > 0 ? maritalLabels : ['Belum Ada Data'],
                    datasets: [{
                        data: maritalTotal > 0 ? maritalTotals : [1],
                        backgroundColor: maritalTotal > 0 ? maritalColors : ['#e2e8f0'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: maritalTotal > 0,
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed;
                                    const pct = maritalTotal > 0 ? ((val / maritalTotal) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${val} (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        }

        // 4. Education Chart
        const eduLabels = @json(array_column($stats['educationStats'] ?? [], 'name'));
        const eduTotals = @json(array_column($stats['educationStats'] ?? [], 'total'));
        const ctxEducation = document.getElementById('chartEducation');
        if (ctxEducation) {
            new Chart(ctxEducation, {
                type: 'bar',
                data: {
                    labels: eduLabels,
                    datasets: [{
                        label: 'Pemuda',
                        data: eduTotals,
                        backgroundColor: '#4f46e5',
                        hoverBackgroundColor: '#4338ca',
                        borderRadius: 6,
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
                                label: function(context) {
                                    return ` ${context.parsed.x} Pemuda`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: '#f1f5f9' }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        }

        // 5. Job Chart
        const jobLabels = @json(array_column($stats['jobStats'] ?? [], 'name'));
        const jobTotals = @json(array_column($stats['jobStats'] ?? [], 'total'));
        const ctxJob = document.getElementById('chartJob');
        if (ctxJob) {
            new Chart(ctxJob, {
                type: 'bar',
                data: {
                    labels: jobLabels,
                    datasets: [{
                        label: 'Pemuda',
                        data: jobTotals,
                        backgroundColor: '#059669',
                        hoverBackgroundColor: '#047857',
                        borderRadius: 6,
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
                                label: function(context) {
                                    return ` ${context.parsed.x} Pemuda`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: '#f1f5f9' }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
