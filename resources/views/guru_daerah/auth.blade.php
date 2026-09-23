@extends('layouts.app')

@section('title', 'Akses Pemantauan Pendataan Pemuda — Guru Daerah')

@section('content')
<div class="py-5 bg-slate-50 min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8 col-sm-10">

                <!-- Header Brand Card -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white shadow-sm border p-3 mb-3" style="width: 72px; height: 72px;">
                        <img src="{{ asset('icons/pemudamta.png') }}" alt="Logo Pemuda MTA" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold small text-uppercase mb-2 d-inline-block">
                        <i class="bi bi-shield-check me-1"></i> Portal Guru Daerah
                    </span>
                    <h3 class="fw-bold text-dark mb-1">Pemantauan Pendataan Cabang</h3>
                    <p class="text-muted small">
                        Masukkan kode akses dan pilih cabang binaan untuk memantau kelengkapan serta progres pendataan pemuda.
                    </p>
                </div>

                <!-- Form Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-gradient-pmd text-white p-4 border-0" style="background: linear-gradient(135deg, #700f2b 0%, #991b1b 50%, #dc2626 100%);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-white bg-opacity-20 p-2.5 d-flex align-items-center justify-content-center">
                                <i class="bi bi-person-badge text-white fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-white">Verifikasi Akses Pemantauan</h5>
                                <small class="text-white-50">Khusus Guru Daerah / Pembina Cabang</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">

                        <!-- Flash Messages -->
                        @if(session('error'))
                        <div class="alert alert-danger border-0 rounded-3 d-flex align-items-start gap-2 mb-4 py-3 small shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="d-block mb-1">Akses Ditolak:</strong>
                                {{ session('error') }}
                            </div>
                        </div>
                        @endif

                        @if(session('info'))
                        <div class="alert alert-info border-0 rounded-3 d-flex align-items-center gap-2 mb-4 py-2.5 small" role="alert">
                            <i class="bi bi-info-circle-fill text-info flex-shrink-0"></i>
                            <div>{{ session('info') }}</div>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 mb-4 py-2.5 small">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form action="{{ route('guru-daerah.verify') }}" method="POST">
                            @csrf

                            <!-- 1. Kode Akses -->
                            <div class="mb-3">
                                <label for="access_code" class="form-label fw-bold text-dark small mb-1">
                                    <i class="bi bi-key-fill text-danger me-1"></i> Kode Akses Guru Daerah <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control form-control-lg rounded-start-3 fs-6 @error('access_code') is-invalid @enderror" 
                                           id="access_code" 
                                           name="access_code" 
                                           placeholder="Masukkan kode akses..." 
                                           required 
                                           autocomplete="current-password"
                                           value="{{ old('access_code') }}">
                                    <button class="btn btn-outline-secondary rounded-end-3 px-3" 
                                            type="button" 
                                            id="togglePasswordBtn" 
                                            onclick="toggleAccessCode()" 
                                            title="Tampilkan / Sembunyikan Kode">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text text-muted" style="font-size: 0.78rem;">
                                    <i class="bi bi-shield-lock me-1"></i> Kode akses resmi diperoleh dari Pengurus / Admin Pemuda MTA Perwakilan Sragen.
                                </div>
                            </div>

                            <!-- 2. Pilihan Cabang -->
                            <div class="mb-4">
                                <label for="cabang_id" class="form-label fw-bold text-dark small mb-1">
                                    <i class="bi bi-diagram-3-fill text-danger me-1"></i> Pilih Cabang Yang Dipantau <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg rounded-3 fs-6 @error('cabang_id') is-invalid @enderror" 
                                        id="cabang_id" 
                                        name="cabang_id" 
                                        required>
                                    <option value="" disabled {{ old('cabang_id') ? '' : 'selected' }}>-- Pilih Cabang Binaan --</option>
                                    @foreach($wilayahList as $wil)
                                        <optgroup label="{{ $wil->name }} ({{ count($wil->cabang) }} Cabang)">
                                            @foreach($wil->cabang as $cbg)
                                                <option value="{{ $cbg->id }}" {{ old('cabang_id') == $cbg->id ? 'selected' : '' }}>
                                                    {{ $cbg->code ? '[' . $cbg->code . '] ' : '' }}{{ $cbg->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted" style="font-size: 0.78rem;">
                                    <i class="bi bi-info-circle me-1"></i> Anda dapat beralih ke cabang lain kapan saja setelah berhasil masuk.
                                </div>
                            </div>

                            <!-- Tombol Masuk -->
                            <div class="d-grid mb-2">
                                <button type="submit" class="btn btn-danger btn-lg rounded-3 fw-bold py-2.5 shadow-sm d-flex align-items-center justify-content-center gap-2" style="background: #dc2626;">
                                    <span>Buka Pemantauan Data</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </form>

                    </div>

                    <!-- Footer Card -->
                    <div class="card-footer bg-light border-0 py-3 px-4 text-center">
                        <small class="text-muted">
                            Kembali ke <a href="{{ route('home') }}" class="text-danger text-decoration-none fw-semibold">Halaman Utama</a> 
                            atau isi <a href="{{ route('pendataan.index') }}" class="text-danger text-decoration-none fw-semibold">Formulir Pendataan</a>
                        </small>
                    </div>
                </div>

                <!-- Bantuan Box -->
                <div class="text-center mt-4">
                    <p class="text-muted small mb-1">Belum memiliki kode akses Guru Daerah?</p>
                    <a href="https://wa.me/6281234567890?text={{ rawurlencode('Assalamu\'alaikum, mohon informasi kode akses Guru Daerah untuk pemantauan pendataan pemuda cabang.') }}" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                        <i class="bi bi-whatsapp text-success me-1"></i> Hubungi Admin Perwakilan
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function toggleAccessCode() {
    const input = document.getElementById('access_code');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
@endsection
