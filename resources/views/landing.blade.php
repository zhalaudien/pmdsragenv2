@extends('layouts.app')

@section('title', 'Pemuda MTA Perwakilan Sragen | Sistem Pendataan Pemuda')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
@endsection

@section('content')

<!-- ===================================================
     1. HERO SECTION (FOKUS, ELEGAN & TUNGGAL CTA)
     =================================================== -->
<section class="hero-elegant text-center text-lg-start">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7">
                <div class="hero-badge-pill mb-3">
                    <span class="badge-dot-live"></span>
                    <span>{{ $settings['hero_badge'] ?? "Majlis Tafsir Al-Qur'an (MTA) • Perwakilan Sragen" }}</span>
                </div>

                <h1 class="hero-title mb-3">
                    {{ $settings['hero_title'] ?? 'Sistem Pendataan Pemuda MTA Perwakilan Sragen' }}
                </h1>

                <p class="hero-subtitle mb-4 pe-lg-3">
                    {!! nl2br(e($settings['hero_subtitle'] ?? 'Pusat basis data terpadu pemuda dan pemudi di 4 Wilayah dan 61 Cabang se-Kabupaten Sragen. Wadah pemetaan potensi, kaderisasi dakwah, dan kesiapsiagaan pengabdian.')) !!}
                </p>

                <!-- Tombol Tunggal Utama (Satu Aksi Jelas) -->
                <div class="d-flex flex-column flex-sm-row align-items-center align-items-lg-start gap-3 mb-3">
                    <a href="{{ route('pendataan.index') }}" class="hero-btn-primary">
                        <span>{{ $settings['hero_btn_text'] ?? 'Mulai Isi Formulir Pendataan' }}</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="hero-trust-badge justify-content-center justify-content-lg-start">
                    <i class="bi bi-shield-check text-warning"></i>
                    <span>Formulir Terbuka • Pengisian ~3 Menit • Tanpa Registrasi Akun</span>
                </div>
            </div>

            <!-- Kartu Ikhtisar Sederhana (Desktop) -->
            <div class="col-lg-5 d-none d-lg-block">
                <div class="hero-summary-card">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ asset('icons/pemudamta.png') }}" alt="Logo Pemuda MTA" style="width: 36px; height: 36px; object-fit: contain;">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Sistem Basis Data</h6>
                                <small class="text-muted">Pemuda MTA Sragen</small>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill small">
                            <i class="bi bi-check-circle-fill me-1"></i> Aktif &amp; Online
                        </span>
                    </div>

                    <div class="hero-summary-row">
                        <div class="hero-summary-icon">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">4 Wilayah Koordinasi</div>
                            <div class="text-muted" style="font-size: 0.8rem;">Mencakup 61 cabang binaan di seluruh Sragen</div>
                        </div>
                    </div>

                    <div class="hero-summary-row">
                        <div class="hero-summary-icon">
                            <i class="bi bi-person-vcard-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Nomor Registrasi Resmi</div>
                            <div class="text-muted" style="font-size: 0.8rem;">Bukti sah tanda keikutsertaan pemuda terdata</div>
                        </div>
                    </div>

                    <div class="hero-summary-row">
                        <div class="hero-summary-icon">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">Data Aman &amp; Terlindungi</div>
                            <div class="text-muted" style="font-size: 0.8rem;">Akses internal pengurus untuk kaderisasi dakwah</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================================================
     2. STATISTIK RINGKAS
     =================================================== -->
<section class="stats-strip">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-num">{{ $totalWilayah }}</div>
                    <div class="stat-label">Wilayah Koordinasi</div>
                    <div class="stat-desc">Wilayah 1 s.d 4 se-Sragen</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-num">{{ $totalCabang }}+</div>
                    <div class="stat-label">Cabang Binaan</div>
                    <div class="stat-desc">Di 20 Kecamatan</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-num">{{ number_format($totalPemuda) }}</div>
                    <div class="stat-label">Pemuda Terdata</div>
                    <div class="stat-desc">{{ number_format($totalVerified) }} Terverifikasi</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-num">100%</div>
                    <div class="stat-label">Online &amp; Terpadu</div>
                    <div class="stat-desc">Terintegrasi MTA Pusat</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================================================
     3. ALUR 3 LANGKAH PENDATAAN
     =================================================== -->
<section id="alur" class="section-clean" style="background-color: #f8fafc;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag"><i class="bi bi-ui-checks-grid"></i> Panduan Pengisian</span>
            <h2 class="section-title mb-2">3 Langkah Mudah Pengisian Data</h2>
            <p class="section-subtitle">Pengisian dapat diselesaikan dalam beberapa menit langsung melalui ponsel cerdas Anda.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="step-card-elegant text-start">
                    <div class="step-number">01</div>
                    <h5 class="step-title">Pilih Cabang &amp; Biodata</h5>
                    <p class="step-desc">Tentukan cabang asal tempat Anda berdomisili atau beraktivitas, lalu lengkapi nama dan kontak WhatsApp.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card-elegant text-start">
                    <div class="step-number">02</div>
                    <h5 class="step-title">Lengkapi Potensi &amp; Profesi</h5>
                    <p class="step-desc">Isi riwayat pendidikan, status pekerjaan, serta minat keahlian dakwah (seperti Satgas, Bankom, Ikhrom, atau keilmuan).</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card-elegant text-start">
                    <div class="step-number">03</div>
                    <h5 class="step-title">Terima Nomor Registrasi</h5>
                    <p class="step-desc">Sistem langsung menerbitkan bukti Nomor Registrasi resmi Pemuda MTA sebagai tanda profil Anda telah terdata.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================================================
     4. STRUKTUR 4 WILAYAH KOORDINASI
     =================================================== -->
<section id="wilayah" class="section-clean bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag"><i class="bi bi-diagram-3-fill"></i> Cakupan Daerah</span>
            <h2 class="section-title mb-2">4 Wilayah Koordinasi di Sragen</h2>
            <p class="section-subtitle">Pembagian wilayah pembinaan pemuda MTA untuk memastikan koordinasi dan kaderisasi berjalan merata.</p>
        </div>

        <div class="row g-3">
            @if (!empty($wilayahList) && count($wilayahList) > 0)
                @foreach ($wilayahList as $wil)
                    @php
                        $cabangCol = is_array($wil) ? collect($wil['cabang'] ?? []) : ($wil->cabang ?? collect());
                        $cCount = $cabangCol->count();
                        $wCode = is_array($wil) ? ($wil['code'] ?? '') : $wil->code;
                        $wName = is_array($wil) ? ($wil['name'] ?? '') : $wil->name;
                        $wDesc = is_array($wil) ? ($wil['description'] ?? '') : $wil->description;
                    @endphp
                    <div class="col-md-6 col-lg-3">
                        <div class="wilayah-card-clean">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="wilayah-badge">{{ $wCode }}</span>
                                <span class="wilayah-count"><i class="bi bi-geo-alt text-danger me-1"></i> {{ $cCount }} Cabang</span>
                            </div>
                            <h5 class="wilayah-name">{{ $wName }}</h5>
                            <p class="wilayah-desc">
                                {{ $wDesc ?: 'Koordinasi pembinaan cabang pemuda MTA di area ini.' }}
                            </p>
                            @if ($cCount > 0)
                                <div class="cabang-preview-box mt-auto">
                                    <span class="fw-semibold text-dark d-block mb-1" style="font-size: 0.76rem;">Contoh Cabang:</span>
                                    <div class="text-truncate text-muted" style="font-size: 0.78rem;">
                                        @foreach ($cabangCol->take(3) as $cItem)
                                            {{ is_array($cItem) ? ($cItem['name'] ?? '') : ($cItem->name ?? '') }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                        @if ($cCount > 3)
                                            <span class="text-secondary">&amp; {{ $cCount - 3 }} lainnya</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-md-6 col-lg-3">
                    <div class="wilayah-card-clean">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="wilayah-badge">W01</span>
                            <span class="wilayah-count">16 Cabang</span>
                        </div>
                        <h5 class="wilayah-name">Wilayah 1</h5>
                        <p class="wilayah-desc">Mencakup cabang-cabang binaan pemuda MTA di kawasan utara Bengawan Solo.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- ===================================================
     5. PUSAT BANTUAN & KONTAK
     =================================================== -->
<section id="bantuan" class="section-clean" style="background-color: #f8fafc;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag"><i class="bi bi-headset"></i> Layanan Bantuan</span>
            <h2 class="section-title mb-2">Pusat Informasi &amp; Kontak</h2>
            <p class="section-subtitle">Pengurus dan helpdesk siap membantu jika Anda membutuhkan panduan atau mengalami kendala pengisian.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Card Sekretariat -->
            <div class="col-md-6 col-lg-5">
                <div class="help-card-clean text-start">
                    <div class="help-icon-circle icon-maroon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Kantor Sekretariat</h5>
                    <p class="text-muted small mb-2 lh-base">
                        {!! nl2br(e($settings['alamat_kantor'] ?? "Gedung Perwakilan MTA Sragen\nJl. Raya Sukowati, Kabupaten Sragen, Jawa Tengah")) !!}
                    </p>
                    <small class="text-secondary d-block mt-3 border-top pt-2">
                        <i class="bi bi-info-circle me-1"></i> Pusat koordinasi kepemudaan tingkat perwakilan
                    </small>
                </div>
            </div>

            <!-- Card WhatsApp Helpdesk -->
            <div class="col-md-6 col-lg-5">
                <div class="help-card-clean text-start d-flex flex-column justify-content-between">
                    <div>
                        <div class="help-icon-circle icon-green">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Helpdesk WhatsApp</h5>
                        <p class="text-muted small mb-3 lh-base">
                            Pertanyaan seputar pencarian cabang, verifikasi identitas, atau perbaikan data dapat dikonsultasikan melalui tim helpdesk.
                        </p>
                    </div>
                    <div>
                        @php
                            $waNum = preg_replace('/[^0-9]/', '', (string) ($settings['whatsapp_number'] ?? '6281234567890'));
                        @endphp
                        <a href="https://wa.me/{{ $waNum }}?text=Assalamu%27alaikum%2C%20saya%20ingin%20bertanya%20seputar%20pendataan%20pemuda%20MTA%20Sragen" target="_blank" class="btn-whatsapp-elegant">
                            <i class="bi bi-whatsapp fs-6"></i>
                            <span>Hubungi Helpdesk WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
