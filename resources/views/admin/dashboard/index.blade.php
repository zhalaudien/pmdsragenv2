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

<!-- CHARTS SECTION (GRID 2 COLUMNS) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
    <!-- Chart Sebaran Wilayah -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-bar-chart-fill text-red-600"></i>
                <span>Sebaran Pemuda per Wilayah</span>
            </h3>
            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-[11px] font-medium text-slate-600">Sragen</span>
        </div>
        <div class="h-64 sm:h-72">
            <canvas id="chartWilayah"></canvas>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4">
            @php $palette = ['text-blue-600', 'text-emerald-600', 'text-amber-600', 'text-purple-600']; @endphp
            @foreach($stats['wilayahStats'] ?? [] as $idx => $w)
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                    <div class="text-[11px] font-bold {{ $palette[$idx % 4] }}">{{ $w['name'] }}</div>
                    <div class="text-lg font-black text-slate-900">{{ number_format($w['total']) }}</div>
                    <div class="text-[10px] text-slate-400">{{ $w['code'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Chart Gender & Status Pernikahan -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-pie-chart-fill text-sky-600"></i>
                <span>Gender &amp; Status Pernikahan</span>
            </h3>
        </div>
        <div class="grid grid-cols-2 gap-4 items-center">
            <div class="text-center">
                <div class="h-36 sm:h-44">
                    <canvas id="chartGender"></canvas>
                </div>
                <div class="mt-2 text-xs font-bold text-slate-700">Jenis Kelamin</div>
                <div class="flex justify-center gap-3 text-[11px] mt-1">
                    <span class="text-blue-600 font-semibold">L: {{ $stats['genderData']['L'] ?? 0 }}</span>
                    <span class="text-pink-600 font-semibold">P: {{ $stats['genderData']['P'] ?? 0 }}</span>
                </div>
            </div>
            <div class="text-center border-l border-slate-100 pl-4">
                <div class="h-36 sm:h-44">
                    <canvas id="chartMarital"></canvas>
                </div>
                <div class="mt-2 text-xs font-bold text-slate-700">Status Nikah</div>
                <div class="flex justify-center gap-2 text-[11px] mt-1">
                    <span class="text-emerald-600 font-semibold">Lajang: {{ $stats['maritalData']['lajang'] ?? 0 }}</span>
                    <span class="text-amber-600 font-semibold">Menikah: {{ $stats['maritalData']['menikah'] ?? 0 }}</span>
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
                @forelse($stats['recentPemuda'] ?? [] as $p)
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
        // Data Wilayah
        const wilayahLabels = {!! json_encode(array_column($stats['wilayahStats'] ?? [], 'name')) !!};
        const wilayahTotals = {!! json_encode(array_column($stats['wilayahStats'] ?? [], 'total')) !!};

        const ctxWilayah = document.getElementById('chartWilayah');
        if (ctxWilayah) {
            new Chart(ctxWilayah, {
                type: 'bar',
                data: {
                    labels: wilayahLabels,
                    datasets: [{
                        label: 'Jumlah Pemuda',
                        data: wilayahTotals,
                        backgroundColor: ['#dc2626', '#059669', '#d97706', '#7c3aed'],
                        borderRadius: 8,
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

        // Gender Chart
        const ctxGender = document.getElementById('chartGender');
        if (ctxGender) {
            new Chart(ctxGender, {
                type: 'doughnut',
                data: {
                    labels: ['Laki-laki', 'Perempuan'],
                    datasets: [{
                        data: [{{ $stats['genderData']['L'] ?? 0 }}, {{ $stats['genderData']['P'] ?? 0 }}],
                        backgroundColor: ['#2563eb', '#ec4899'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '68%'
                }
            });
        }

        // Marital Chart
        const ctxMarital = document.getElementById('chartMarital');
        if (ctxMarital) {
            new Chart(ctxMarital, {
                type: 'doughnut',
                data: {
                    labels: ['Lajang', 'Menikah'],
                    datasets: [{
                        data: [{{ $stats['maritalData']['lajang'] ?? 0 }}, {{ $stats['maritalData']['menikah'] ?? 0 }}],
                        backgroundColor: ['#10b981', '#f59e0b'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '68%'
                }
            });
        }

        // Education Chart
        const eduLabels = {!! json_encode(array_column($stats['educationStats'] ?? [], 'name')) !!};
        const eduTotals = {!! json_encode(array_column($stats['educationStats'] ?? [], 'total')) !!};
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
                        borderRadius: 6,
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

        // Job Chart
        const jobLabels = {!! json_encode(array_column($stats['jobStats'] ?? [], 'name')) !!};
        const jobTotals = {!! json_encode(array_column($stats['jobStats'] ?? [], 'total')) !!};
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
                        borderRadius: 6,
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
    });
</script>
@endsection
