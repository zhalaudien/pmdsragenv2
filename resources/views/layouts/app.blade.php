<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pemuda MTA Perwakilan Sragen | Pusat Informasi & Pendataan')</title>
    <meta name="description" content="Pusat Informasi dan Sistem Pendataan Pemuda MTA Perwakilan Sragen. Menghimpun potensi dan karya pemuda di 4 Wilayah dan 61 Cabang se-Kabupaten Sragen.">

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

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- App Global Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">

    <!-- Tailwind CSS Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')
</head>

<body>

    <!-- Top Utility Bar -->
    <div class="top-utility-bar d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3 top-utility-left">
                <span><i class="bi bi-geo-alt-fill text-warning me-1"></i> Perwakilan MTA Sragen, Jawa Tengah</span>
                <span class="opacity-50">|</span>
                <span><i class="bi bi-patch-check-fill text-warning me-1"></i> Sistem Basis Data Resmi Pemuda</span>
            </div>
            <div class="d-flex align-items-center gap-3 top-utility-right">
                <a href="{{ route('guru-daerah.index') }}" class="top-utility-link"><i class="bi bi-person-check me-1 text-warning"></i> Guru Daerah</a>
                <span class="opacity-50">|</span>
                <a href="{{ route('login') }}" class="top-utility-link"><i class="bi bi-person-lock me-1"></i> Portal Admin</a>
                <span class="opacity-50">|</span>
                <a href="https://wa.me/6281234567890" target="_blank" class="top-utility-link"><i class="bi bi-whatsapp text-success me-1"></i> Bantuan WA</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-pmd sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <div class="navbar-brand-icon">
                    <img src="{{ asset('icons/pemudamta.png') }}" alt="Logo Pemuda MTA" class="navbar-brand-img">
                </div>
                <div>
                    <span class="navbar-brand-title">Pemuda MTA Perwakilan Sragen</span>
                    <span class="navbar-brand-subtitle">Sistem Informasi &amp; Pendataan Pemuda</span>
                </div>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navMenu">
                <ul class="navbar-nav align-items-lg-center gap-lg-1 my-2 my-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active fw-semibold' : '' }}" href="{{ url('/') }}">
                            <i class="bi bi-house-door me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/#alur') }}">
                            <i class="bi bi-ui-checks-grid me-1"></i> Alur Pendataan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/#wilayah') }}">
                            <i class="bi bi-diagram-3 me-1"></i> 4 Wilayah &amp; Cabang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/#bantuan') }}">
                            <i class="bi bi-headset me-1"></i> Bantuan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('pantau-pemuda*') ? 'active fw-semibold text-warning' : '' }}" href="{{ route('guru-daerah.index') }}">
                            <i class="bi bi-person-check me-1"></i> Pantau Cabang
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2 my-1 my-lg-0">
                        <a class="btn btn-warning btn-sm rounded-pill px-3 py-1 fw-bold shadow-sm {{ request()->is('pendataan*') ? 'border border-2 border-white' : '' }}" href="{{ route('pendataan.index') }}">
                            <i class="bi bi-pencil-square me-1"></i> Form Pendataan
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-pmd pt-5 pb-3">
        <div class="container">
            <div class="row g-4 pb-4 border-bottom">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rounded-circle bg-white border p-1 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 40px; height: 40px;">
                            <img src="{{ asset('icons/pemudamta.png') }}" alt="Logo Pemuda MTA" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Pemuda MTA Perwakilan Sragen</h6>
                            <small class="text-muted">Majlis Tafsir Al-Qur'an (MTA)</small>
                        </div>
                    </div>
                    <p class="text-muted small pe-lg-3 mb-3">
                        Wadah pengkaderan dan pengembangan generasi muda berlandaskan Al-Qur'an dan As-Sunnah. Menghimpun potensi pemuda di 4 Wilayah dan 61 Cabang di seluruh wilayah Kabupaten Sragen.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="https://mta.or.id" target="_blank" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Website MTA Pusat">
                            <i class="bi bi-globe"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Instagram Pemuda">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://youtube.com" target="_blank" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="YouTube MTA TV">
                            <i class="bi bi-youtube"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="fw-bold text-dark mb-3">Navigasi</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="{{ url('/') }}" class="text-decoration-none text-muted hover-red"><i class="bi bi-chevron-right me-1 text-danger small"></i> Beranda</a></li>
                        <li><a href="{{ url('/#alur') }}" class="text-decoration-none text-muted hover-red"><i class="bi bi-chevron-right me-1 text-danger small"></i> Alur Pendataan</a></li>
                        <li><a href="{{ url('/#wilayah') }}" class="text-decoration-none text-muted hover-red"><i class="bi bi-chevron-right me-1 text-danger small"></i> 4 Wilayah &amp; Cabang</a></li>
                        <li><a href="{{ url('/#bantuan') }}" class="text-decoration-none text-muted hover-red"><i class="bi bi-chevron-right me-1 text-danger small"></i> Pusat Bantuan</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-6">
                    <h6 class="fw-bold text-dark mb-3">Layanan & Akses</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="{{ route('pendataan.index') }}" class="text-decoration-none text-danger fw-semibold"><i class="bi bi-pencil-square me-1"></i> Form Pendataan Pemuda</a></li>
                        <li><a href="{{ route('guru-daerah.index') }}" class="text-decoration-none text-muted hover-red"><i class="bi bi-person-check me-1 text-danger small"></i> Pemantauan Guru Daerah</a></li>
                        <li><a href="{{ url('/#alur') }}" class="text-decoration-none text-muted hover-red"><i class="bi bi-chevron-right me-1 text-danger small"></i> Alur Pendaftaran</a></li>
                        <li><a href="{{ route('login') }}" class="text-decoration-none text-muted hover-red"><i class="bi bi-shield-lock me-1 text-danger small"></i> Login Pengurus / Admin</a></li>
                        <li><a href="javascript:void(0)" onclick="if(window.triggerPwaInstall) window.triggerPwaInstall();" class="text-decoration-none text-muted hover-red"><i class="bi bi-phone me-1 text-danger small"></i> Pasang Aplikasi di HP</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold text-dark mb-3">Sekretariat & Kontak</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 text-muted mb-0">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-geo-alt-fill text-danger mt-1"></i>
                            <span>Gedung Perwakilan MTA Sragen, Jl. Raya Sukowati, Sragen, Jawa Tengah</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-whatsapp text-success"></i>
                            <span>Layanan Helpdesk WhatsApp</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-envelope-fill text-danger"></i>
                            <span>pemudamta.sragen@gmail.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row align-items-center gy-2 pt-3">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 small text-muted">&copy; {{ date('Y') }} <strong>Pemuda MTA Perwakilan Sragen</strong>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <span class="badge bg-light text-secondary border px-3 py-2 small">
                        <i class="bi bi-shield-check text-success me-1"></i> Sistem Basis Data Terenkripsi & Terintegrasi
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="mobile-bottom-nav" aria-label="Mobile Navigation">
        <a href="{{ url('/') }}" class="mobile-nav-item {{ request()->is('/') ? 'active' : '' }}">
            <i class="bi bi-house-door{{ request()->is('/') ? '-fill' : '' }}"></i>
            <span>Beranda</span>
        </a>
        <a href="{{ url('/#alur') }}" class="mobile-nav-item">
            <i class="bi bi-ui-checks-grid"></i>
            <span>Alur</span>
        </a>
        <a href="{{ route('pendataan.index') }}" class="mobile-nav-item mobile-nav-fab {{ request()->is('pendataan*') ? 'active' : '' }}">
            <div class="fab-icon-wrap">
                <i class="bi bi-pencil-square"></i>
            </div>
            <span>Daftar</span>
        </a>
        <a href="{{ url('/#wilayah') }}" class="mobile-nav-item">
            <i class="bi bi-diagram-3"></i>
            <span>Wilayah</span>
        </a>
        <a href="{{ url('/#bantuan') }}" class="mobile-nav-item">
            <i class="bi bi-headset"></i>
            <span>Bantuan</span>
        </a>
    </nav>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/pwa-install.js') }}"></script>
    @yield('scripts')
</body>

</html>
