@extends('layouts.app')

@section('title', 'Pemantauan Pendataan — ' . $activeCabang->name)

@section('styles')
<style>
    .card-stat-guru {
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(0,0,0,0.06);
    }
    .card-stat-guru:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
    }
    .avatar-pemuda {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .avatar-initial {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
    }
    .progress-guru {
        height: 10px;
        border-radius: 8px;
        background-color: #e2e8f0;
        overflow: hidden;
    }
    .mini-badge-check {
        font-size: 0.68rem;
        padding: 2px 6px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 2px;
        font-weight: 600;
    }
    .mini-badge-check.valid {
        background-color: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .mini-badge-check.invalid {
        background-color: #fff1f2;
        color: #9f1239;
        border: 1px solid #fecdd3;
    }
    @media print {
        .no-print, .top-utility-bar, .navbar-pmd, .footer-pmd, .switcher-box, .action-bar-guru {
            display: none !important;
        }
        body, main {
            background: #fff !important;
            padding: 0 !important;
        }
        .card, .table {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
    }
</style>
@endsection

@section('content')
<div class="py-4 bg-light min-vh-100">
    <div class="container-fluid px-lg-5 px-3">

        <!-- =========================================================
             1. HEADER BANNER & CABANG SWITCHER
             ========================================================= -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-body p-4 text-white position-relative" style="background: linear-gradient(135deg, #700f2b 0%, #991b1b 50%, #dc2626 100%);">
                <div class="row align-items-center gy-3">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-white bg-opacity-20 text-white border border-white border-opacity-25 px-3 py-1 rounded-pill small">
                                <i class="bi bi-shield-check me-1"></i> Mode Pemantauan Guru Daerah
                            </span>
                            <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill fw-bold small">
                                {{ $activeCabang->wilayah ? $activeCabang->wilayah->name : 'Wilayah MTA' }}
                            </span>
                        </div>
                        <h2 class="fw-bold mb-1 text-white">
                            Cabang {{ $activeCabang->name }}
                            @if($activeCabang->code)
                                <span class="fs-5 opacity-75 fw-normal">({{ $activeCabang->code }})</span>
                            @endif
                        </h2>
                        <p class="text-white-50 mb-0 small pe-lg-4">
                            {{ $activeCabang->alamat ?: 'Alamat sekretariat cabang belum tercatat.' }}
                            @if($activeCabang->pimpinan_nama)
                                • Pimpinan: <span class="text-white fw-semibold">{{ $activeCabang->pimpinan_nama }}</span>
                            @endif
                            @if($activeCabang->no_wa)
                                (<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $activeCabang->no_wa) }}" target="_blank" class="text-warning text-decoration-none"><i class="bi bi-whatsapp"></i> {{ $activeCabang->no_wa }}</a>)
                            @endif
                        </p>
                    </div>

                    <!-- Quick Switcher Cabang & Logout -->
                    <div class="col-lg-5 text-lg-end no-print">
                        <div class="d-inline-flex flex-column flex-sm-row align-items-sm-center gap-2 bg-white bg-opacity-10 p-2 rounded-3 border border-white border-opacity-20">
                            <!-- Switcher Form -->
                            <form action="{{ route('guru-daerah.switch-cabang') }}" method="POST" class="d-flex align-items-center gap-2 m-0">
                                @csrf
                                <select name="cabang_id" class="form-select form-select-sm rounded-pill border-0 shadow-sm" style="min-width: 200px;" onchange="this.form.submit()">
                                    @php
                                        $groupedCabangs = $allCabangs->groupBy(fn($c) => $c->wilayah ? $c->wilayah->name : 'Lainnya');
                                    @endphp
                                    @foreach($groupedCabangs as $wilName => $cList)
                                        <optgroup label="{{ $wilName }}">
                                            @foreach($cList as $c)
                                                <option value="{{ $c->id }}" {{ $c->id == $activeCabang->id ? 'selected' : '' }}>
                                                    {{ $c->code ? '[' . $c->code . '] ' : '' }}{{ $c->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </form>

                            <!-- Logout Button -->
                            <form action="{{ route('guru-daerah.logout') }}" method="POST" class="m-0" onsubmit="return confirm('Keluar dari sesi pemantauan Guru Daerah?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-light rounded-pill px-3 text-nowrap d-flex align-items-center gap-1 shadow-sm" title="Keluar Sesi">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             2. FLASH ALERTS
             ========================================================= -->
        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4 py-3 small no-print">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        @if(session('info'))
        <div class="alert alert-info border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4 py-3 small no-print">
            <i class="bi bi-info-circle-fill text-info fs-5"></i>
            <div>{{ session('info') }}</div>
        </div>
        @endif

        <!-- =========================================================
             3. RINGKASAN STATISTIK (METRIC CARDS)
             ========================================================= -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Pemuda -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-stat-guru bg-white p-3 h-100 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Total Terdata</span>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary">
                            <i class="bi bi-people-fill fs-6"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['total_pemuda']) }}</h3>
                    <small class="text-muted" style="font-size: 0.75rem;">Pemuda & Pemudi Cabang</small>
                </div>
            </div>

            <!-- 2. Data Komplit -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-stat-guru bg-white p-3 h-100 shadow-sm border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-success small fw-bold">Sudah Komplit</span>
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success">
                            <i class="bi bi-patch-check-fill fs-6"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold text-success mb-0">{{ number_format($stats['total_komplit']) }}</h3>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill small fw-bold">{{ $stats['persen_komplit'] }}%</span>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Data profil ≥ 80% lengkap</small>
                </div>
            </div>

            <!-- 3. Belum Komplit -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-stat-guru bg-white p-3 h-100 shadow-sm border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-warning text-dark small fw-bold">Belum Komplit</span>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 text-warning">
                            <i class="bi bi-exclamation-circle-fill fs-6"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold text-warning text-dark mb-0">{{ number_format($stats['total_belum_komplit']) }}</h3>
                        @php
                            $persenBelum = $stats['total_pemuda'] > 0 ? round(($stats['total_belum_komplit'] / $stats['total_pemuda']) * 100, 1) : 0;
                        @endphp
                        <span class="badge bg-warning bg-opacity-10 text-dark rounded-pill small fw-bold">{{ $persenBelum }}%</span>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Perlu dilengkapi/diingatkan</small>
                </div>
            </div>

            <!-- 4. Rata-Rata Progres Cabang -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-stat-guru bg-white p-3 h-100 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Rata-rata Progres</span>
                        <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info">
                            <i class="bi bi-graph-up fs-6"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ $stats['avg_progress'] }}%</h3>
                    <div class="progress progress-guru">
                        <div class="progress-bar {{ $stats['avg_progress'] >= 80 ? 'bg-success' : ($stats['avg_progress'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                             role="progressbar" 
                             style="width: {{ $stats['avg_progress'] }}%;" 
                             aria-valuenow="{{ $stats['avg_progress'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            <!-- 5. Gender -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-stat-guru bg-white p-3 h-100 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Jenis Kelamin</span>
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-2 text-secondary">
                            <i class="bi bi-gender-ambiguous fs-6"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small">
                            <span class="text-primary fw-bold">L: {{ $stats['total_laki'] }}</span>
                        </div>
                        <span class="text-muted opacity-50">|</span>
                        <div class="small">
                            <span class="text-danger fw-bold">P: {{ $stats['total_perempuan'] }}</span>
                        </div>
                    </div>
                    <small class="text-muted mt-1" style="font-size: 0.75rem;">Pemuda (L) &amp; Pemudi (P)</small>
                </div>
            </div>

            <!-- 6. Status Verifikasi MTA -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-stat-guru bg-white p-3 h-100 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Sinkron MTA Pusat</span>
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success">
                            <i class="bi bi-database-check fs-6"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small">
                            <span class="text-success fw-bold">Sync: {{ $stats['total_verified'] }}</span>
                        </div>
                        <span class="text-muted opacity-50">|</span>
                        <div class="small">
                            <span class="text-warning text-dark fw-bold">Belum: {{ $stats['total_pending'] }}</span>
                        </div>
                    </div>
                    <small class="text-muted mt-1" style="font-size: 0.75rem;">Validitas database pusat</small>
                </div>
            </div>
        </div>

        <!-- =========================================================
             4. CATATAN KEKURANGAN CABANG (PANDUAN GURU DAERAH)
             ========================================================= -->
        @if($stats['total_pemuda'] > 0 && ($stats['kurang_foto'] > 0 || $stats['kurang_pendidikan'] > 0 || $stats['kurang_pekerjaan'] > 0 || $stats['kurang_organisasi'] > 0))
        <div class="alert alert-light border shadow-sm rounded-4 p-3 mb-4 no-print">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-20 p-2 text-warning flex-shrink-0">
                        <i class="bi bi-lightbulb-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Item yang Sering Belum Dilengkapi di Cabang Ini:</h6>
                        <div class="d-flex flex-wrap gap-2 small">
                            @if($stats['kurang_foto'] > 0)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-camera me-1"></i> {{ $stats['kurang_foto'] }} Pemuda Belum Pas Foto
                                </span>
                            @endif
                            @if($stats['kurang_pendidikan'] > 0)
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-mortarboard me-1"></i> {{ $stats['kurang_pendidikan'] }} Pendidikan Belum Lengkap / Masih Tanda Strip (-)
                                </span>
                            @endif
                            @if($stats['kurang_pekerjaan'] > 0)
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-briefcase me-1"></i> {{ $stats['kurang_pekerjaan'] }} Belum Detail Profesi/Pekerjaan
                                </span>
                            @endif
                            @if($stats['kurang_organisasi'] > 0)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-people me-1"></i> {{ $stats['kurang_organisasi'] }} Belum Memilih Elemen Dakwah
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="copyFormLink()">
                        <i class="bi bi-link-45deg me-1"></i> Salin Link Formulir Cabang
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Cetak Rekap
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- =========================================================
             5. TOOLBAR PENCARIAN & FILTER
             ========================================================= -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 no-print">
            <div class="card-body p-3">
                <form action="{{ route('guru-daerah.index') }}" method="GET" class="row g-2 align-items-center">
                    
                    <!-- Search Input -->
                    <div class="col-lg-5 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   name="q" 
                                   class="form-control border-start-0 rounded-end-pill ps-0 text-sm" 
                                   placeholder="Cari nama pemuda, no registrasi, HP, dusun/alamat..." 
                                   value="{{ $filters['q'] }}">
                        </div>
                    </div>

                    <!-- Filter Status Kelengkapan -->
                    <div class="col-lg-3 col-md-3 col-6">
                        <select name="status" class="form-select rounded-pill text-sm" onchange="this.form.submit()">
                            <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Semua Status Kelengkapan</option>
                            <option value="komplit" {{ $filters['status'] === 'komplit' ? 'selected' : '' }}>✓ Hanya Sudah Komplit (≥80%)</option>
                            <option value="belum_komplit" {{ $filters['status'] === 'belum_komplit' ? 'selected' : '' }}>⚠ Hanya Belum Komplit (&lt;80%)</option>
                        </select>
                    </div>

                    <!-- Filter Gender -->
                    <div class="col-lg-2 col-md-3 col-6">
                        <select name="gender" class="form-select rounded-pill text-sm" onchange="this.form.submit()">
                            <option value="all" {{ $filters['gender'] === 'all' ? 'selected' : '' }}>Semua Gender (L &amp; P)</option>
                            <option value="L" {{ $filters['gender'] === 'L' ? 'selected' : '' }}>Khusus Laki-laki (L)</option>
                            <option value="P" {{ $filters['gender'] === 'P' ? 'selected' : '' }}>Khusus Perempuan (P)</option>
                        </select>
                    </div>

                    <!-- Tombol Reset / Submit -->
                    <div class="col-lg-2 col-md-12 d-flex gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill px-3 w-100 text-sm fw-semibold shadow-sm">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        @if($filters['q'] || $filters['status'] !== 'all' || $filters['gender'] !== 'all')
                            <a href="{{ route('guru-daerah.index') }}" class="btn btn-outline-secondary rounded-pill px-3 text-sm" title="Reset Filter">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- =========================================================
             6. TABEL DAFTAR PEMUDA CABANG
             ========================================================= -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white p-3.5 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill text-danger fs-5"></i>
                    <h5 class="fw-bold text-dark mb-0">Daftar Pemuda &amp; Progres Data</h5>
                    <span class="badge bg-secondary rounded-pill ms-1">{{ count($pemudaList) }} Pemuda</span>
                </div>
                <div class="small text-muted d-none d-sm-block">
                    Klik tombol <strong>Detail</strong> atau <strong>WA</strong> untuk follow up pemuda.
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablePemudaGuru">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 50px;">No</th>
                            <th style="min-width: 250px;">Identitas Pemuda</th>
                            <th style="min-width: 140px;">Gender &amp; Usia</th>
                            <th style="min-width: 160px;">Kontak &amp; Alamat</th>
                            <th style="min-width: 260px;">Progres &amp; Kelengkapan Data</th>
                            <th class="text-center pe-4" style="min-width: 130px;">Aksi Guru Daerah</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($pemudaList as $index => $p)
                        @php
                            $comp = $p->completeness_data;
                            $aspects = $comp['aspects'];
                            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$p->phone);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                            $avatarUrl = $p->foto ? asset('uploads/pemuda/' . $p->foto) : ($p->mta_foto_url ?: null);
                            $initial = strtoupper(substr(trim($p->name), 0, 1));
                        @endphp
                        <tr>
                            <!-- No -->
                            <td class="ps-4 text-muted small fw-semibold">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Identitas Pemuda -->
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $p->name }}" class="avatar-pemuda">
                                    @else
                                        <div class="avatar-initial {{ $p->gender === 'L' ? 'bg-primary text-white' : 'bg-danger text-white' }}">
                                            {{ $initial }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark text-capitalize mb-0.5" style="font-size: 0.95rem;">
                                            {{ $p->name }}
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                                                {{ $p->registration_number }}
                                            </span>
                                            @if($p->status_verifikasi === 'verified')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.68rem;" title="Data cocok dengan Database MTA Pusat">
                                                    <i class="bi bi-check-circle-fill me-0.5"></i> Terverifikasi MTA
                                                </span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25" style="font-size: 0.68rem;" title="Belum tersinkronisasi">
                                                    <i class="bi bi-clock me-0.5"></i> Pending Sync
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Gender & Usia -->
                            <td>
                                <div>
                                    @if($p->gender === 'L')
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5 small">
                                            <i class="bi bi-gender-male me-1"></i> Laki-laki
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5 small">
                                            <i class="bi bi-gender-female me-1"></i> Perempuan
                                        </span>
                                    @endif
                                </div>
                                <div class="text-muted small mt-1">
                                    @if($p->birth_date)
                                        <i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($p->birth_date)->age }} th 
                                        <span class="opacity-50">({{ \Carbon\Carbon::parse($p->birth_date)->format('d/m/Y') }})</span>
                                    @else
                                        <span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> Tgl Lahir Kosong</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Kontak & Alamat -->
                            <td>
                                <div class="small fw-semibold text-dark mb-1">
                                    @if($p->phone)
                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="text-success text-decoration-none">
                                            <i class="bi bi-whatsapp me-1"></i> {{ $p->phone }}
                                        </a>
                                    @else
                                        <span class="text-danger"><i class="bi bi-telephone-x me-1"></i> Belum ada no WA</span>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size: 0.78rem;">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $p->alamat?->village?->name ? $p->alamat->village->name . ', ' : '' }}
                                    {{ $p->alamat?->district?->name ?: ($p->alamat?->dusun ?: 'Alamat belum lengkap') }}
                                </div>
                            </td>

                            <!-- Progres & Kelengkapan Data -->
                            <td>
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="badge {{ $comp['badge_bootstrap'] }} px-2 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                            @if($comp['is_complete'])
                                                <i class="bi bi-check2-circle me-1"></i> Komplit
                                            @else
                                                <i class="bi bi-exclamation-circle me-1"></i> Belum Komplit
                                            @endif
                                        </span>
                                        <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $comp['percentage'] }}%</span>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        {{ $comp['status_label'] }}
                                    </small>
                                </div>

                                <!-- Progress Bar -->
                                <div class="progress progress-guru mb-2">
                                    <div class="progress-bar {{ $comp['percentage'] >= 80 ? 'bg-success' : ($comp['percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                         role="progressbar" 
                                         style="width: {{ $comp['percentage'] }}%;" 
                                         aria-valuenow="{{ $comp['percentage'] }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100"></div>
                                </div>

                                <!-- Mini Checklist Icons -->
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="mini-badge-check {{ $aspects['biodata']['filled'] ? 'valid' : 'invalid' }}" title="Biodata: {{ $aspects['biodata']['filled'] ? 'Lengkap' : 'Kurang' }}">
                                        <i class="bi {{ $aspects['biodata']['filled'] ? 'bi-check' : 'bi-x' }}"></i> Bio
                                    </span>
                                    <span class="mini-badge-check {{ $aspects['alamat']['filled'] ? 'valid' : 'invalid' }}" title="Alamat: {{ $aspects['alamat']['filled'] ? 'Lengkap' : 'Kurang' }}">
                                        <i class="bi {{ $aspects['alamat']['filled'] ? 'bi-check' : 'bi-x' }}"></i> Alamat
                                    </span>
                                    <span class="mini-badge-check {{ $aspects['pendidikan']['filled'] ? 'valid' : 'invalid' }}" title="Pendidikan: {{ $aspects['pendidikan']['filled'] ? 'Lengkap' : 'Kurang' }}">
                                        <i class="bi {{ $aspects['pendidikan']['filled'] ? 'bi-check' : 'bi-x' }}"></i> Didik
                                    </span>
                                    <span class="mini-badge-check {{ $aspects['pekerjaan']['filled'] ? 'valid' : 'invalid' }}" title="Pekerjaan: {{ $aspects['pekerjaan']['filled'] ? 'Lengkap' : 'Kurang' }}">
                                        <i class="bi {{ $aspects['pekerjaan']['filled'] ? 'bi-check' : 'bi-x' }}"></i> Kerja
                                    </span>
                                    <span class="mini-badge-check {{ $aspects['organisasi']['filled'] ? 'valid' : 'invalid' }}" title="Elemen Dakwah: {{ $aspects['organisasi']['filled'] ? 'Terdaftar' : 'Belum Ada' }}">
                                        <i class="bi {{ $aspects['organisasi']['filled'] ? 'bi-check' : 'bi-x' }}"></i> Dakwah
                                    </span>
                                    <span class="mini-badge-check {{ $aspects['minat_skill']['filled'] ? 'valid' : 'invalid' }}" title="Keahlian & Minat: {{ $aspects['minat_skill']['filled'] ? 'Ada' : 'Belum Ada' }}">
                                        <i class="bi {{ $aspects['minat_skill']['filled'] ? 'bi-check' : 'bi-x' }}"></i> Skill
                                    </span>
                                    <span class="mini-badge-check {{ (!empty($p->foto) || !empty($p->mta_foto_url)) ? 'valid' : 'invalid' }}" title="Pas Foto Profil: {{ (!empty($p->foto) || !empty($p->mta_foto_url)) ? 'Sudah Ada' : 'Belum Ada' }}">
                                        <i class="bi {{ (!empty($p->foto) || !empty($p->mta_foto_url)) ? 'bi-check' : 'bi-x' }}"></i> Foto
                                    </span>
                                </div>

                                <!-- Ringkasan Item Kurang -->
                                @if(!empty($comp['missing_items']))
                                    <div class="text-danger mt-1.5" style="font-size: 0.72rem;">
                                        <i class="bi bi-info-circle me-1"></i> 
                                        Kurang: {{ implode(', ', array_slice($comp['missing_items'], 0, 2)) }}
                                        @if(count($comp['missing_items']) > 2)
                                            <span class="text-muted">(+{{ count($comp['missing_items']) - 2 }} lainnya)</span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Aksi Guru Daerah -->
                            <td class="text-center pe-4 no-print">
                                <div class="d-flex align-items-center justify-content-center gap-1.5">
                                    <!-- Tombol Detail Progres -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 text-nowrap d-flex align-items-center gap-1 shadow-sm"
                                            onclick="openDetailPemuda({{ $p->id }})"
                                            title="Buka Checklist & Rincian Data">
                                        <i class="bi bi-eye"></i>
                                        <span>Detail</span>
                                    </button>

                                    <!-- Tombol WA Pengingat -->
                                    @if($p->phone)
                                        @php
                                            $missingTxt = !empty($comp['missing_items']) ? implode(", ", $comp['missing_items']) : 'kelengkapan umum';
                                            $formUrl = route('pendataan.index') . '?cabang_id=' . $activeCabang->id;
                                            $waText = "Assalamu'alaikum wr. wb. Saudaraku {$p->name},\n\nKami dari Guru Daerah / Tim Pendataan Pemuda MTA Perwakilan Sragen menginfokan bahwa data pendataan antum untuk Cabang {$activeCabang->name} saat ini berstatus *{$comp['status_label']}* ({$comp['percentage']}%).\n\nItem yang masih perlu dilengkapi:\n- {$missingTxt}\n\nMohon kesediaannya untuk memperbarui melalui link formulir berikut:\n{$formUrl}\n\nJazakumullah khairan katsiran.";
                                        @endphp
                                        <a href="https://api.whatsapp.com/send?phone={{ $cleanPhone }}&text={{ rawurlencode($waText) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                           style="width: 32px; height: 32px;" 
                                           title="Kirim Pesan Pengingat WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <div class="rounded-circle bg-light p-3 d-inline-flex mb-3 text-muted">
                                        <i class="bi bi-search fs-1"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark">Tidak Ada Data Pemuda Ditemukan</h5>
                                    <p class="text-muted small mb-3">
                                        @if($filters['q'] || $filters['status'] !== 'all' || $filters['gender'] !== 'all')
                                            Tidak ada pemuda yang cocok dengan kriteria pencarian dan filter Anda.
                                        @else
                                            Belum ada data pemuda yang terdaftar pada Cabang {{ $activeCabang->name }}.
                                        @endif
                                    </p>
                                    @if($filters['q'] || $filters['status'] !== 'all' || $filters['gender'] !== 'all')
                                        <a href="{{ route('guru-daerah.index') }}" class="btn btn-sm btn-secondary rounded-pill px-4">
                                            Reset Filter
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-danger rounded-pill px-4" onclick="copyFormLink()">
                                            <i class="bi bi-share me-1"></i> Salin Link Formulir Cabang Ini
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Card -->
            <div class="card-footer bg-white border-top p-3 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
                <small class="text-muted">
                    Menampilkan <strong>{{ count($pemudaList) }}</strong> dari total <strong>{{ $stats['total_pemuda'] }}</strong> pemuda di Cabang {{ $activeCabang->name }}.
                </small>
                <div class="d-flex align-items-center gap-2 no-print">
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
                        <i class="bi bi-arrow-up me-1"></i> Ke Atas
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- =========================================================
     7. MODAL DETAIL PROGRES PEMUDA (INTERAKTIF & CHECKLIST)
     ========================================================= -->
<div class="modal fade" id="detailPemudaModal" tabindex="-1" aria-labelledby="detailPemudaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div id="modalAvatarBox">
                        <div class="avatar-initial bg-danger text-white rounded-circle fs-4" id="modalInitial" style="width: 54px; height: 54px;">
                            P
                        </div>
                        <img src="" alt="" id="modalImg" class="rounded-circle border shadow-sm d-none" style="width: 54px; height: 54px; object-fit: cover;">
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0.5" id="modalName">Nama Pemuda</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-secondary border font-monospace small" id="modalRegNum">86...</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary small" id="modalGender">Laki-laki</span>
                            <span class="badge bg-success bg-opacity-10 text-success small" id="modalSyncStatus">Terverifikasi MTA</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                
                <!-- Loading State -->
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-danger mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-muted small">Memuat rincian data pemuda...</div>
                </div>

                <!-- Modal Content Container -->
                <div id="modalContent" class="d-none">

                    <!-- Progress Header Card -->
                    <div class="card border rounded-3 p-3 mb-4 bg-light">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <span class="fw-bold text-dark" id="modalCompletenessLabel">Status Kelengkapan</span>
                                <small class="text-muted d-block" id="modalCabangWilayah">Cabang Masaran 2 • Wilayah 3</small>
                            </div>
                            <h3 class="fw-bold mb-0 text-success" id="modalPercentage">85%</h3>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 8px;">
                            <div class="progress-bar bg-success" id="modalProgressBar" role="progressbar" style="width: 85%;"></div>
                        </div>
                    </div>

                    <!-- Peringatan Item Yang Masih Kurang -->
                    <div class="alert alert-warning border-0 rounded-3 p-3 mb-4 d-none" id="modalMissingBox">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0 mt-0.5"></i>
                            <div class="w-100">
                                <h6 class="fw-bold text-dark mb-1">Item yang Masih Kurang / Belum Dilengkapi:</h6>
                                <ul class="mb-0 ps-3 small text-dark" id="modalMissingList">
                                    <!-- Dynamic Items -->
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- 6 Rincian Aspek Checklist -->
                    <h6 class="fw-bold text-dark mb-3">Rincian Kelengkapan Per Aspek:</h6>
                    <div class="row g-3 mb-4">
                        
                        <!-- 1. Biodata -->
                        <div class="col-md-6">
                            <div class="card border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold small text-dark"><i class="bi bi-person me-1 text-danger"></i> 1. Biodata Pribadi</span>
                                    <span class="badge" id="badgeBio">Lengkap</span>
                                </div>
                                <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-1" id="detailBio">
                                    <li>TTL: <span class="text-dark fw-semibold" id="bioTtl">-</span></li>
                                    <li>No. WA: <span class="text-dark fw-semibold" id="bioPhone">-</span></li>
                                    <li>Status Nikah: <span class="text-dark fw-semibold" id="bioMarital">-</span></li>
                                    <li>Gol. Darah: <span class="text-dark fw-semibold" id="bioBlood">-</span></li>
                                    <li>Pas Foto: <span class="text-dark fw-semibold" id="bioFoto">-</span></li>
                                </ul>
                            </div>
                        </div>

                        <!-- 2. Alamat -->
                        <div class="col-md-6">
                            <div class="card border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold small text-dark"><i class="bi bi-geo-alt me-1 text-danger"></i> 2. Alamat Domisili</span>
                                    <span class="badge" id="badgeAlamat">Lengkap</span>
                                </div>
                                <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-1" id="detailAlamat">
                                    <li>Kecamatan: <span class="text-dark fw-semibold" id="alamatKec">-</span></li>
                                    <li>Desa/Kel: <span class="text-dark fw-semibold" id="alamatDesa">-</span></li>
                                    <li>RT / RW: <span class="text-dark fw-semibold" id="alamatRtRw">-</span></li>
                                    <li>Detail: <span class="text-dark fw-semibold" id="alamatDetail">-</span></li>
                                </ul>
                            </div>
                        </div>

                        <!-- 3. Pendidikan -->
                        <div class="col-md-6">
                            <div class="card border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold small text-dark"><i class="bi bi-mortarboard me-1 text-danger"></i> 3. Pendidikan</span>
                                    <span class="badge" id="badgePendidikan">Lengkap</span>
                                </div>
                                <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-1" id="detailPendidikan">
                                    <li>Jenjang: <span class="text-dark fw-semibold" id="didikLevel">-</span></li>
                                    <li>Sekolah/Kampus: <span class="text-dark fw-semibold" id="didikSchool">-</span></li>
                                    <li>Jurusan: <span class="text-dark fw-semibold" id="didikMajor">-</span></li>
                                    <li>Status: <span class="text-dark fw-semibold" id="didikStatus">-</span></li>
                                </ul>
                            </div>
                        </div>

                        <!-- 4. Pekerjaan -->
                        <div class="col-md-6">
                            <div class="card border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold small text-dark"><i class="bi bi-briefcase me-1 text-danger"></i> 4. Pekerjaan</span>
                                    <span class="badge" id="badgePekerjaan">Lengkap</span>
                                </div>
                                <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-1" id="detailPekerjaan">
                                    <li>Status Kerja: <span class="text-dark fw-semibold" id="kerjaStatus">-</span></li>
                                    <li>Profesi: <span class="text-dark fw-semibold" id="kerjaTitle">-</span></li>
                                    <li>Instansi/Usaha: <span class="text-dark fw-semibold" id="kerjaCompany">-</span></li>
                                </ul>
                            </div>
                        </div>

                        <!-- 5. Elemen Dakwah -->
                        <div class="col-md-6">
                            <div class="card border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold small text-dark"><i class="bi bi-people me-1 text-danger"></i> 5. Elemen Dakwah MTA</span>
                                    <span class="badge" id="badgeOrganisasi">Ada</span>
                                </div>
                                <div id="modalOrgsList" class="d-flex flex-wrap gap-1 small text-muted">
                                    <!-- Dynamic chips -->
                                </div>
                            </div>
                        </div>

                        <!-- 6. Minat & Keahlian -->
                        <div class="col-md-6">
                            <div class="card border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold small text-dark"><i class="bi bi-lightning-charge me-1 text-danger"></i> 6. Keahlian &amp; Minat</span>
                                    <span class="badge" id="badgeMinatSkill">Ada</span>
                                </div>
                                <div id="modalSkillsList" class="d-flex flex-wrap gap-1 small text-muted mb-1">
                                    <!-- Dynamic chips -->
                                </div>
                                <div id="modalInterestsList" class="d-flex flex-wrap gap-1 small text-muted">
                                    <!-- Dynamic chips -->
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex gap-2">
                    <a href="#" id="modalWaBtn" target="_blank" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm d-none">
                        <i class="bi bi-whatsapp me-1"></i> Kirim Pengingat WA
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- =========================================================
     8. JAVASCRIPT LOGIC (DETAIL MODAL & COPY LINK)
     ========================================================= -->
<script>
function copyFormLink() {
    const formUrl = "{{ route('pendataan.index') }}?cabang_id={{ $activeCabang->id }}";
    navigator.clipboard.writeText(formUrl).then(() => {
        alert("Link formulir pendataan untuk Cabang {{ $activeCabang->name }} berhasil disalin:\n" + formUrl + "\n\nSilakan bagikan ke grup WhatsApp pengurus / pemuda cabang.");
    }).catch(err => {
        prompt("Salin link formulir cabang berikut:", formUrl);
    });
}

function openDetailPemuda(id) {
    const modal = new bootstrap.Modal(document.getElementById('detailPemudaModal'));
    modal.show();

    document.getElementById('modalLoading').classList.remove('d-none');
    document.getElementById('modalContent').classList.add('d-none');
    document.getElementById('modalWaBtn').classList.add('d-none');

    fetch(`{{ url('pantau-pemuda/detail') }}/${id}`)
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') {
                alert(res.message || 'Gagal mengambil detail data pemuda.');
                modal.hide();
                return;
            }

            const data = res.data;
            const comp = data.completeness;
            const aspects = comp.aspects;

            // Header
            document.getElementById('modalName').textContent = data.name;
            document.getElementById('modalRegNum').textContent = data.registration_number;
            document.getElementById('modalGender').textContent = data.gender_text;
            document.getElementById('modalCabangWilayah').textContent = `Cabang ${data.cabang_name || '{{ $activeCabang->name }}'} • ${data.wilayah_name || ''}`;

            if (data.status_verifikasi === 'verified') {
                document.getElementById('modalSyncStatus').className = 'badge bg-success bg-opacity-10 text-success small';
                document.getElementById('modalSyncStatus').innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Terverifikasi MTA';
            } else {
                document.getElementById('modalSyncStatus').className = 'badge bg-warning bg-opacity-10 text-dark small';
                document.getElementById('modalSyncStatus').innerHTML = '<i class="bi bi-clock me-1"></i> Pending Sync';
            }

            // Avatar
            const imgEl = document.getElementById('modalImg');
            const initEl = document.getElementById('modalInitial');
            if (data.foto_url) {
                imgEl.src = data.foto_url;
                imgEl.classList.remove('d-none');
                initEl.classList.add('d-none');
            } else {
                imgEl.classList.add('d-none');
                initEl.classList.remove('d-none');
                initEl.textContent = (data.name || 'P').charAt(0).toUpperCase();
            }

            // Progress Header
            document.getElementById('modalPercentage').textContent = `${comp.percentage}%`;
            document.getElementById('modalCompletenessLabel').textContent = `Status: ${comp.status_label}`;
            const pBar = document.getElementById('modalProgressBar');
            pBar.style.width = `${comp.percentage}%`;
            pBar.className = `progress-bar ${comp.percentage >= 80 ? 'bg-success' : (comp.percentage >= 50 ? 'bg-warning' : 'bg-danger')}`;

            // Missing List
            const missingBox = document.getElementById('modalMissingBox');
            const missingList = document.getElementById('modalMissingList');
            missingList.innerHTML = '';
            if (comp.missing_items && comp.missing_items.length > 0) {
                missingBox.classList.remove('d-none');
                comp.missing_items.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = item;
                    missingList.appendChild(li);
                });
            } else {
                missingBox.classList.add('d-none');
            }

            // 1. Biodata
            const badgeBio = document.getElementById('badgeBio');
            badgeBio.textContent = aspects.biodata.filled ? 'Lengkap' : 'Kurang';
            badgeBio.className = `badge ${aspects.biodata.filled ? 'bg-success' : 'bg-warning text-dark'}`;
            document.getElementById('bioTtl').textContent = `${data.birth_place || '-'}, ${data.birth_date || '-'} (${data.age ? data.age + ' th' : '-'})`;
            document.getElementById('bioPhone').textContent = data.phone || 'Kosong';
            document.getElementById('bioMarital').textContent = data.marital_status || '-';
            document.getElementById('bioBlood').textContent = data.blood_type || '-';
            document.getElementById('bioFoto').textContent = data.foto_url ? 'Sudah Ada' : 'Belum Ada';

            // 2. Alamat
            const badgeAlamat = document.getElementById('badgeAlamat');
            badgeAlamat.textContent = aspects.alamat.filled ? 'Lengkap' : 'Kurang';
            badgeAlamat.className = `badge ${aspects.alamat.filled ? 'bg-success' : 'bg-warning text-dark'}`;
            document.getElementById('alamatKec').textContent = data.alamat.district || '-';
            document.getElementById('alamatDesa').textContent = data.alamat.village || '-';
            document.getElementById('alamatRtRw').textContent = data.alamat.rt_rw || (data.alamat.dusun || '-');
            document.getElementById('alamatDetail').textContent = data.alamat.detail || '-';

            // 3. Pendidikan
            const badgePendidikan = document.getElementById('badgePendidikan');
            badgePendidikan.textContent = aspects.pendidikan.filled ? 'Lengkap' : 'Kurang';
            badgePendidikan.className = `badge ${aspects.pendidikan.filled ? 'bg-success' : 'bg-warning text-dark'}`;
            document.getElementById('didikLevel').textContent = data.pendidikan.level || '-';
            document.getElementById('didikSchool').textContent = data.pendidikan.school || '-';
            document.getElementById('didikMajor').textContent = data.pendidikan.major || '-';
            document.getElementById('didikStatus').textContent = data.pendidikan.status || '-';

            // 4. Pekerjaan
            const badgePekerjaan = document.getElementById('badgePekerjaan');
            badgePekerjaan.textContent = aspects.pekerjaan.filled ? 'Lengkap' : 'Kurang';
            badgePekerjaan.className = `badge ${aspects.pekerjaan.filled ? 'bg-success' : 'bg-warning text-dark'}`;
            document.getElementById('kerjaStatus').textContent = data.pekerjaan.status || '-';
            document.getElementById('kerjaTitle').textContent = data.pekerjaan.title || '-';
            document.getElementById('kerjaCompany').textContent = data.pekerjaan.company || (data.pekerjaan.business || '-');

            // 5. Elemen Dakwah
            const badgeOrg = document.getElementById('badgeOrganisasi');
            badgeOrg.textContent = aspects.organisasi.filled ? 'Terdaftar' : 'Belum Ada';
            badgeOrg.className = `badge ${aspects.organisasi.filled ? 'bg-success' : 'bg-secondary'}`;
            const orgsBox = document.getElementById('modalOrgsList');
            orgsBox.innerHTML = '';
            if (data.organisasi && data.organisasi.length > 0) {
                data.organisasi.forEach(o => {
                    const span = document.createElement('span');
                    span.className = 'badge bg-light text-dark border px-2 py-1';
                    span.textContent = o.toUpperCase();
                    orgsBox.appendChild(span);
                });
            } else {
                orgsBox.innerHTML = '<span class="text-danger">Belum memilih elemen dakwah MTA</span>';
            }

            // 6. Skills & Interests
            const badgeMinat = document.getElementById('badgeMinatSkill');
            badgeMinat.textContent = aspects.minat_skill.filled ? 'Ada' : 'Belum Ada';
            badgeMinat.className = `badge ${aspects.minat_skill.filled ? 'bg-success' : 'bg-secondary'}`;
            const skillsBox = document.getElementById('modalSkillsList');
            skillsBox.innerHTML = '';
            if (data.skills && data.skills.length > 0) {
                data.skills.forEach(s => {
                    const span = document.createElement('span');
                    span.className = 'badge bg-info bg-opacity-10 text-dark border px-2 py-1';
                    span.textContent = s;
                    skillsBox.appendChild(span);
                });
            }
            const interestsBox = document.getElementById('modalInterestsList');
            interestsBox.innerHTML = '';
            if (data.interests && data.interests.length > 0) {
                data.interests.forEach(i => {
                    const span = document.createElement('span');
                    span.className = 'badge bg-warning bg-opacity-10 text-dark border px-2 py-1';
                    span.textContent = i;
                    interestsBox.appendChild(span);
                });
            }
            if ((!data.skills || data.skills.length === 0) && (!data.interests || data.interests.length === 0)) {
                skillsBox.innerHTML = '<span class="text-muted">Belum memilih keahlian maupun minat</span>';
            }

            // WA Button
            const waBtn = document.getElementById('modalWaBtn');
            if (data.wa_url) {
                waBtn.href = data.wa_url;
                waBtn.classList.remove('d-none');
            } else {
                waBtn.classList.add('d-none');
            }

            document.getElementById('modalLoading').classList.add('d-none');
            document.getElementById('modalContent').classList.remove('d-none');
        })
        .catch(err => {
            alert('Terjadi kesalahan saat memuat data.');
            modal.hide();
        });
}
</script>
@endsection
