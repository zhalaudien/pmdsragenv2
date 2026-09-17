@extends('layouts.app')

@section('title', 'Formulir Pendataan Pemuda | Pemuda MTA Perwakilan Sragen')

@section('content')

<div class="py-8 sm:py-12 bg-slate-50/60 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        
        <!-- TOP BREADCRUMB & HEADER BADGE -->
        <div class="flex items-center justify-between gap-2 mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition border border-slate-200 shadow-sm">
                <i class="bi bi-arrow-left text-red-600 font-bold"></i>
                <span>Kembali ke Beranda</span>
            </a>
            <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-sm">
                <i class="bi bi-shield-check text-emerald-600"></i>
                <span>Portal Basis Data Resmi Pemuda MTA Sragen</span>
            </div>
        </div>

        <!-- FORM HERO TITLE -->
        <div class="text-center mb-8 sm:mb-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-red-50 border border-red-200/80 text-red-700 text-xs font-bold mb-3 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                <span>Sistem Registrasi &amp; Pemutakhiran Kader Pemuda</span>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 tracking-tight">Formulir Pendataan Pemuda</h1>
            <p class="text-xs sm:text-sm text-slate-600 max-w-xl mx-auto mt-2.5 leading-relaxed">
                Silakan pilih <strong>Cabang MTA</strong> domisili/tempat mengaji Anda, lalu ketikkan nama untuk memverifikasi data terdaftar atau melanjutkan pendaftaran baru.
            </p>
        </div>

        <!-- STEPPER PROGRESS BAR (DESKTOP) -->
        <div class="hidden sm:block mb-8">
            <div class="relative">
                <!-- Connecting line behind steps -->
                <div class="absolute top-5 left-8 right-8 h-1 bg-slate-200 rounded-full z-0">
                    <div id="desktopProgressTrack" class="h-full bg-red-600 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>

                <!-- 7 Step indicators -->
                <div class="grid grid-cols-7 gap-1 text-center relative z-10">
                    @php
                        $stepList = [
                            1 => ['short' => 'Cabang & Diri', 'icon' => 'bi-person-badge'],
                            2 => ['short' => 'Alamat',        'icon' => 'bi-geo-alt'],
                            3 => ['short' => 'Pendidikan',    'icon' => 'bi-mortarboard'],
                            4 => ['short' => 'Pekerjaan',     'icon' => 'bi-briefcase'],
                            5 => ['short' => 'Elemen Dakwah', 'icon' => 'bi-flag'],
                            6 => ['short' => 'Potensi',       'icon' => 'bi-stars'],
                            7 => ['short' => 'Konfirmasi',    'icon' => 'bi-check2-circle'],
                        ];
                    @endphp
                    @foreach($stepList as $num => $stepInfo)
                        <div class="step-indicator flex flex-col items-center cursor-pointer group" onclick="goToStep({{ $num }})" id="step-indicator-{{ $num }}" title="Langkah {{ $num }}: {{ $stepInfo['short'] }}">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-xs transition-all duration-200 {{ $num === 1 ? 'bg-red-600 text-white shadow-lg shadow-red-200 ring-4 ring-red-100 scale-105' : 'bg-white text-slate-500 border border-slate-300 shadow-sm group-hover:border-slate-400' }}" id="step-circle-{{ $num }}">
                                {{ $num }}
                            </div>
                            <span class="text-[11px] mt-2 font-bold transition-colors truncate max-w-full {{ $num === 1 ? 'text-red-700' : 'text-slate-500 group-hover:text-slate-800' }}" id="step-label-{{ $num }}">{{ $stepInfo['short'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- MOBILE PROGRESS BAR -->
        <div class="sm:hidden mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between gap-4 text-xs">
            <div class="min-w-0">
                <span class="font-black text-red-600 block text-xs truncate" id="mobileStepText">Langkah 1 dari 7: Cabang &amp; Data Diri</span>
                <span class="text-[11px] text-slate-500 block mt-0.5" id="mobileStepSubtext">Pilih cabang dan lengkapi data pemuda</span>
            </div>
            <div class="w-20 sm:w-24 h-2.5 bg-slate-100 rounded-full overflow-hidden flex-shrink-0 border border-slate-200">
                <div id="mobileProgressBar" class="h-full bg-red-600 rounded-full transition-all duration-300" style="width: 14.28%"></div>
            </div>
        </div>

        <!-- MAIN FORM CARD -->
        <form action="{{ route('pendataan.simpan') }}" method="POST" enctype="multipart/form-data" id="pendataanForm" novalidate class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200 shadow-xl shadow-slate-200/50 space-y-6 scroll-mt-6">
            @csrf

            <!-- Hidden input for existing youth update and warga uuid -->
            <input type="hidden" name="existing_pemuda_id" id="input_existing_pemuda_id" value="{{ old('existing_pemuda_id', '') }}">
            <input type="hidden" name="mta_warga_uuid" id="input_mta_warga_uuid" value="{{ old('mta_warga_uuid', '') }}">

            <!-- SERVER NOTIFICATION BANNER (SESSION ERROR / VALIDATION FAILURES) -->
            @if(session('error') || $errors->any())
                <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-950 text-xs shadow-sm space-y-2 mb-2 animate-in fade-in duration-200">
                    <div class="flex items-start gap-3">
                        <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm mt-0.5">
                            <i class="bi bi-exclamation-octagon-fill text-base"></i>
                        </span>
                        <div class="space-y-1 flex-1">
                            <strong class="font-black text-rose-900 text-sm block">
                                {{ session('error') ?? 'Terdapat kesalahan pada isian formulir:' }}
                            </strong>
                            @if($errors->any())
                                <ul class="list-disc list-inside space-y-0.5 text-rose-800 font-medium pl-1">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- ALERT NOTIFICATION BANNER (FOR STEP VALIDATION ERRORS) -->
            <div id="step_alert_box" class="hidden p-4 rounded-2xl bg-rose-50 border-2 border-rose-200 text-rose-900 text-xs flex items-center justify-between gap-3 shadow-sm transition-all">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-xl bg-rose-600 text-white flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill text-sm"></i>
                    </span>
                    <span id="step_alert_message" class="font-bold text-slate-800">Mohon lengkapi seluruh isian wajib pada langkah ini.</span>
                </div>
                <button type="button" onclick="hideStepAlert()" class="w-6 h-6 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-800 flex items-center justify-center font-bold text-sm transition flex-shrink-0">&times;</button>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 1: CABANG & DATA DIRI -->
            <!-- ========================================================================= -->
            <div class="form-step" id="step-1">
                <!-- 1.1 PEMILIHAN CABANG DENGAN SEARCH LANGSUNG PADA DROPDOWN LIST -->
                <div class="p-5 sm:p-6 rounded-2xl bg-gradient-to-br from-slate-50 via-white to-red-50/40 border border-slate-200/80 shadow-sm mb-6">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                        <span class="w-8 h-8 rounded-xl bg-red-600 text-white font-black text-sm flex items-center justify-center shadow-md shadow-red-200">
                            <i class="bi bi-geo-alt-fill"></i>
                        </span>
                        <div>
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight">1. Tentukan Cabang MTA Tempat Mengaji</h4>
                            <p class="text-[11px] text-slate-500">Pilih cabang mengaji/domisili Anda terlebih dahulu untuk membuka formulir</p>
                        </div>
                    </div>

                    <!-- SEARCHABLE CABANG DROPDOWN COMPONENT -->
                    <div class="space-y-1.5 text-xs relative" id="cabang_dropdown_wrapper">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">
                                Cabang MTA Domisili / Binaan <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md font-semibold">70 Cabang MTA Se-Sragen</span>
                        </div>

                        <!-- Hidden native input to submit cabang_id -->
                        <input type="hidden" name="cabang_id" id="public_cabang_id" value="{{ old('cabang_id') }}" required>

                        <!-- Dropdown Button Trigger -->
                        @php
                            $selectedCabangObj = old('cabang_id') ? $cabangList->firstWhere('id', old('cabang_id')) : null;
                        @endphp
                        <button type="button" id="cabang_dropdown_btn" class="w-full py-3.5 px-4 rounded-xl border border-slate-300 bg-white hover:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-semibold text-slate-800 shadow-sm flex items-center justify-between transition text-left group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <i class="bi bi-building text-slate-400 group-hover:text-red-600 transition text-base flex-shrink-0"></i>
                                <span id="cabang_selected_label" class="truncate {{ $selectedCabangObj ? 'text-slate-900 font-bold' : 'text-slate-400 font-normal' }}">
                                    {{ $selectedCabangObj ? $selectedCabangObj->name : '-- Klik di sini untuk Memilih Cabang MTA (Tersedia Pencarian) --' }}
                                </span>
                            </div>
                            <i class="bi bi-chevron-down text-slate-400 text-xs ml-2 transition-transform duration-200 flex-shrink-0" id="cabang_dropdown_arrow"></i>
                        </button>

                        <!-- Dropdown Panel with Integrated Search Box -->
                        <div id="cabang_dropdown_panel" class="hidden absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-100">
                            <!-- Integrated Search Input -->
                            <div class="p-3 bg-slate-50 border-b border-slate-200">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 text-xs">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" id="cabang_search_input" placeholder="Cari cabang (contoh: Masaran, Gemolong, Tanon, Sragen Kota)..." autocomplete="off" class="w-full py-2.5 pl-10 pr-4 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-xs text-slate-800 placeholder:text-slate-400 font-medium">
                                </div>
                            </div>

                            <!-- Scrollable Cabang Items List -->
                            <div class="max-h-64 overflow-y-auto divide-y divide-slate-100" id="cabang_options_list">
                                @foreach($cabangList as $c)
                                    <div class="cabang-option-item px-4 py-3 hover:bg-red-50/80 cursor-pointer text-xs font-semibold text-slate-800 transition flex items-center justify-between group"
                                         data-id="{{ $c->id }}"
                                         data-name="{{ $c->name }}"
                                         data-code="{{ $c->code }}">
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-geo-alt text-slate-400 group-hover:text-red-600 transition text-xs"></i>
                                            <span class="group-hover:text-red-700 font-bold text-slate-800">{{ $c->name }}</span>
                                        </div>
                                        @if(!empty($c->code))
                                            <span class="text-[10px] text-slate-400 group-hover:text-red-600 font-mono bg-slate-100 group-hover:bg-red-100/60 px-2 py-0.5 rounded">{{ $c->code }}</span>
                                        @endif
                                    </div>
                                @endforeach
                                <div id="cabang_no_match" class="hidden p-6 text-center text-xs text-slate-400 font-normal">
                                    <i class="bi bi-search text-xl block text-slate-300 mb-1"></i>
                                    Cabang tidak ditemukan. Periksa kata kunci pencarian Anda.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1.2 STATUS MODE NOTIFICATION BANNER -->
                <div id="mode_indicator_box" class="mb-6">
                    <!-- Default: Prompt to select Cabang -->
                    <div id="mode_prompt_cabang" class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-amber-950 text-xs flex items-start sm:items-center gap-3 shadow-sm">
                        <span class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm shadow-sm">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </span>
                        <div class="leading-relaxed">
                            <strong class="font-bold text-amber-900">Perhatian:</strong> Silakan tentukan <strong>Cabang MTA</strong> Anda pada pilihan di atas terlebih dahulu. Setelah cabang dipilih, ketik nama lengkap Anda untuk mencari data di cabang tersebut.
                        </div>
                    </div>

                    <!-- State: New Registration Mode -->
                    <div id="mode_new_registration" class="hidden p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 text-xs flex items-center justify-between gap-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-red-600 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm shadow-sm">
                                <i class="bi bi-person-plus-fill"></i>
                            </span>
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-800 mb-0.5">Mode: Pendaftaran Pemuda Baru</span>
                                <div class="text-slate-600 text-[11px]">Ketik nama Anda di bawah. Jika belum pernah terdaftar di cabang ini, data akan disimpan sebagai kader baru.</div>
                            </div>
                        </div>
                    </div>

                    <!-- State: Update Existing Youth Mode (From PMD Database) -->
                    <div id="mode_update_existing" class="hidden p-4 rounded-2xl bg-emerald-50/80 border-2 border-emerald-300 text-emerald-950 text-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm shadow-sm">
                                <i class="bi bi-patch-check-fill"></i>
                            </span>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 text-emerald-900">Mode: Pembaruan Data Pemuda</span>
                                    <span class="text-emerald-800 font-semibold" id="mode_update_reg_no"></span>
                                </div>
                                <div class="text-slate-800 mt-1">
                                    Terhubung dengan data pemuda atas nama <strong id="mode_update_name" class="text-emerald-900 text-sm"></strong>. Seluruh formulir telah diisi otomatis. Silakan periksa atau perbarui isian Anda.
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="cancelUpdateMode()" class="px-3.5 py-1.5 rounded-xl bg-white border border-emerald-300 hover:bg-emerald-100 text-emerald-900 font-bold text-xs shadow-sm transition flex-shrink-0 flex items-center gap-1.5">
                            <i class="bi bi-arrow-repeat text-red-500"></i>
                            <span>Bukan Anda? / Daftar Baru</span>
                        </button>
                    </div>

                    <!-- State: Integration with Warga MTA Pusat -->
                    <div id="mode_warga_mta" class="hidden p-4 rounded-2xl bg-sky-50/80 border-2 border-sky-300 text-sky-950 text-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl bg-sky-600 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm shadow-sm">
                                <i class="bi bi-person-badge-fill"></i>
                            </span>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-200 text-sky-900">Mode: Terkoneksi Database Warga MTA Pusat</span>
                                </div>
                                <div class="text-slate-800 mt-1">
                                    Terhubung dengan data warga resmi MTA Pusat atas nama <strong id="mode_warga_name" class="text-sky-900 text-sm"></strong>. Profil telah disinkronkan dan akan <strong>langsung terverifikasi resmi</strong> saat dikirim.
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="cancelUpdateMode()" class="px-3.5 py-1.5 rounded-xl bg-white border border-sky-300 hover:bg-sky-100 text-sky-900 font-bold text-xs shadow-sm transition flex-shrink-0 flex items-center gap-1.5">
                            <i class="bi bi-arrow-repeat text-red-500"></i>
                            <span>Bukan Anda? / Daftar Baru</span>
                        </button>
                    </div>
                </div>

                <!-- 1.3 DATA DIRI PEMUDA -->
                <div class="flex items-center gap-2 pb-3 mb-5 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">2</span>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Identitas &amp; Data Diri Pemuda</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- NAMA LENGKAP WITH AUTOCOMPLETE (COMBINED PEMUDA & WARGA MTA) -->
                    <div class="sm:col-span-2 relative">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500">Pencarian otomatis di Cabang terpilih</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-person text-base"></i>
                            </div>
                            <input type="text" name="name" id="input_name" value="{{ old('name') }}" placeholder="Pilih Cabang terlebih dahulu..." disabled required autocomplete="off" class="w-full py-3 pl-10 pr-10 rounded-xl border border-slate-300 bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium transition shadow-sm">
                            
                            <!-- Search Loading Spinner -->
                            <div id="search_spinner" class="hidden absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="animate-spin h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1.5" id="name_helper_text">
                            <i class="bi bi-info-circle text-slate-400"></i>
                            <span>Pilih Cabang MTA terlebih dahulu sebelum mengetik nama.</span>
                        </p>

                        <!-- Autocomplete Dropdown List -->
                        <div id="autocomplete_dropdown" class="hidden absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden max-h-80 overflow-y-auto divide-y divide-slate-100">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select name="gender" id="input_gender" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                            <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki (Pemuda)</option>
                            <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan (Pemudi)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Status Pernikahan <span class="text-red-500">*</span>
                        </label>
                        <select name="marital_status" id="input_marital_status" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                            <option value="belum_menikah" {{ old('marital_status') == 'belum_menikah' ? 'selected' : '' }}>Belum Menikah (Lajang)</option>
                            <option value="sudah_menikah" {{ old('marital_status') == 'sudah_menikah' ? 'selected' : '' }}>Sudah Menikah</option>
                            <option value="duda" {{ old('marital_status') == 'duda' ? 'selected' : '' }}>Duda</option>
                            <option value="janda" {{ old('marital_status') == 'janda' ? 'selected' : '' }}>Janda</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Tempat Lahir <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="birth_place" id="input_birth_place" value="{{ old('birth_place') }}" placeholder="Kota / Kabupaten Lahir" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Tanggal Lahir <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="birth_date" id="input_birth_date" value="{{ old('birth_date') }}" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Golongan Darah</label>
                        <select name="blood_type" id="input_blood_type" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                            <option value="tidak_tahu" {{ old('blood_type') == 'tidak_tahu' ? 'selected' : '' }}>Tidak Tahu / Belum Cek</option>
                            <option value="A" {{ old('blood_type') == 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('blood_type') == 'B' ? 'selected' : '' }}>B</option>
                            <option value="AB" {{ old('blood_type') == 'AB' ? 'selected' : '' }}>AB</option>
                            <option value="O" {{ old('blood_type') == 'O' ? 'selected' : '' }}>O</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            No. WhatsApp / HP Aktif <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-whatsapp text-sm text-emerald-600"></i>
                            </div>
                            <input type="tel" name="phone" id="input_phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required class="w-full py-2.5 pl-10 pr-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Alamat Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-envelope text-sm"></i>
                            </div>
                            <input type="email" name="email" id="input_email" value="{{ old('email') }}" placeholder="nama@email.com (opsional)" class="w-full py-2.5 pl-10 pr-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Pas Foto Formal / Bebas Rapi <span class="text-slate-400 font-normal text-[11px]">(Wajib untuk kader laki-laki pada pendaftaran baru)</span>
                        </label>
                        
                        <!-- Existing photo preview if updating -->
                        <div id="existing_foto_box" class="hidden mb-3 p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                            <img id="existing_foto_img" src="" alt="Foto Pemuda" class="w-14 h-16 object-cover rounded-xl border border-slate-300 shadow-sm flex-shrink-0">
                            <div class="text-xs">
                                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="bi bi-check-circle-fill text-emerald-600"></i>
                                    Foto profil sudah tersimpan di sistem
                                </span>
                                <span class="text-slate-500 text-[11px] block mt-0.5">Unggah berkas baru di bawah jika Anda ingin memperbarui foto profil.</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition">
                            <input type="file" name="foto" id="input_foto" accept="image/*" class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                            <span class="text-[11px] text-slate-400 block mt-1.5">Format: JPG, PNG, atau WEBP. Ukuran maksimal 2 MB.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 2: ALAMAT DOMISILI -->
            <!-- ========================================================================= -->
            <div class="form-step hidden" id="step-2">
                <div class="flex items-center gap-2 pb-3 mb-5 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">2</span>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Alamat Domisili Tempat Tinggal Saat Ini</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Kecamatan di Sragen <span class="text-red-500">*</span>
                        </label>
                        <select name="district_id" id="public_district_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                            <option value="">-- Pilih Kecamatan --</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}" {{ old('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Desa / Kelurahan <span class="text-red-500">*</span>
                        </label>
                        <select name="village_id" id="public_village_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                            <option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Dukuh / Dusun / Kampung</label>
                        <input type="text" name="dusun" id="input_dusun" value="{{ old('dusun') }}" placeholder="Nama Dukuh / Dusun" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">RT</label>
                            <input type="text" name="rt" id="input_rt" value="{{ old('rt') }}" placeholder="01" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm text-center">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">RW</label>
                            <input type="text" name="rw" id="input_rw" value="{{ old('rw') }}" placeholder="02" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm text-center">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Alamat Lengkap / Patokan Rumah <span class="text-red-500">*</span>
                        </label>
                        <textarea name="address_detail" id="input_address_detail" rows="3" placeholder="Nama jalan, nomor rumah, atau patokan lokasi domisili..." required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">{{ old('address_detail') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 3: PENDIDIKAN TERAKHIR -->
            <!-- ========================================================================= -->
            <div class="form-step hidden" id="step-3">
                <div class="flex items-center gap-2 pb-3 mb-5 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">3</span>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Riwayat Pendidikan Terakhir</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Jenjang Pendidikan <span class="text-red-500">*</span>
                        </label>
                        <select name="education_level_id" id="input_education_level_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                            @foreach($educationLevels as $el)
                                <option value="{{ $el->id }}" {{ old('education_level_id') == $el->id ? 'selected' : '' }}>{{ $el->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Nama Sekolah / Kampus / Instansi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="school_name" id="input_school_name" value="{{ old('school_name') }}" placeholder="Contoh: SMA N 1 Sragen / UNS" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Jurusan / Program Studi</label>
                        <input type="text" name="major" id="input_major" value="{{ old('major') }}" placeholder="IPA / Teknik Mesin / Manajemen / dll" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Status Kelulusan <span class="text-red-500">*</span>
                        </label>
                        <select name="education_status" id="input_education_status" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                            <option value="lulus" {{ old('education_status') == 'lulus' ? 'selected' : '' }}>Sudah Lulus</option>
                            <option value="sedang_sekolah" {{ old('education_status') == 'sedang_sekolah' ? 'selected' : '' }}>Sedang Menempuh Pendidikan</option>
                            <option value="putus_sekolah" {{ old('education_status') == 'putus_sekolah' ? 'selected' : '' }}>Putus Sekolah</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Tahun Kelulusan</label>
                        <input type="number" name="graduation_year" id="input_graduation_year" value="{{ old('graduation_year') }}" placeholder="Contoh: 2022" min="1970" max="{{ date('Y') + 10 }}" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 4: PEKERJAAN & AKTIVITAS EKONOMI -->
            <!-- ========================================================================= -->
            <div class="form-step hidden" id="step-4">
                <div class="flex items-center gap-2 pb-3 mb-5 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">4</span>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Pekerjaan &amp; Aktivitas Ekonomi</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                            Status Pekerjaan <span class="text-red-500">*</span>
                        </label>
                        <select name="job_status_id" id="input_job_status_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                            @foreach($jobStatuses as $js)
                                <option value="{{ $js->id }}" {{ old('job_status_id') == $js->id ? 'selected' : '' }}>{{ $js->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Profesi / Jabatan</label>
                        <input type="text" name="job_title" id="input_job_title" value="{{ old('job_title') }}" placeholder="Karyawan, Guru, Pedagang, Wiraswasta, dll" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Nama Tempat Kerja / Instansi</label>
                        <input type="text" name="company_name" id="input_company_name" value="{{ old('company_name') }}" placeholder="Nama Perusahaan / Kantor / Toko / Tempat Usaha" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-medium shadow-sm">
                    </div>

                    <div class="sm:col-span-2 pt-2">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                            <span class="block font-bold text-slate-800 mb-2">Informasi Usaha Mandiri (Khusus Wirausaha):</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-600 text-[11px] mb-1">Bidang Usaha</label>
                                    <input type="text" name="business_field" id="input_business_field" value="{{ old('business_field') }}" placeholder="Kuliner, Bengkel, Konveksi, dll" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-xs shadow-sm">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-600 text-[11px] mb-1">Nama Brand / Usaha</label>
                                    <input type="text" name="business_name" id="input_business_name" value="{{ old('business_name') }}" placeholder="Nama Toko / Usaha Mandiri" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-xs shadow-sm">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block font-semibold text-slate-600 text-[11px] mb-1">Kontak / Medsos Bisnis</label>
                                    <input type="text" name="business_contact" id="input_business_contact" value="{{ old('business_contact') }}" placeholder="No. WhatsApp Bisnis / Akun Instagram Usaha" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-xs shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 5: ELEMEN DAKWAH RESMI (6 PILIHAN) -->
            <!-- ========================================================================= -->
            <div class="form-step hidden" id="step-5">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">5</span>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Elemen Dakwah yang Diikuti</h3>
                </div>

                <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                    Pilih satuan tugas atau elemen dakwah yang sedang atau pernah Anda ikuti di lingkungan MTA (dapat memilih lebih dari satu):
                </p>

                @php
                    $orgDetails = [
                        'SATGAS'     => ['icon' => 'bi-shield-shaded',     'color' => 'text-red-600',    'desc' => 'Satuan Tugas Pengamanan & Ketertiban Pengajian'],
                        'BANKOM'     => ['icon' => 'bi-broadcast-pin',     'color' => 'text-blue-600',   'desc' => 'Bantuan Komunikasi Radio & Informasi Lapangan'],
                        'SAR MTA'    => ['icon' => 'bi-heart-pulse-fill',  'color' => 'text-emerald-600','desc' => 'Relawan Search & Rescue serta Kemanusiaan'],
                        'TIM PARKIR' => ['icon' => 'bi-p-square-fill',     'color' => 'text-amber-600',  'desc' => 'Pengaturan Kendaraan & Ketertiban Lalu Lintas'],
                        'ELFATA'     => ['icon' => 'bi-journal-richtext',  'color' => 'text-purple-600', 'desc' => 'Majalah & Media Edukasi Generasi Muda Islam'],
                        'TIM IKHROM' => ['icon' => 'bi-cup-hot-fill',      'color' => 'text-rose-600',   'desc' => 'Pelayanan Jamuan & Penerimaan Tamu Pengajian'],
                    ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs" id="org_checkboxes_container">
                    @foreach($orgDetails as $orgName => $info)
                        <label class="relative flex items-start gap-3 p-4 rounded-2xl border-2 border-slate-200 hover:border-red-300 hover:bg-red-50/20 cursor-pointer transition shadow-sm group select-none has-[:checked]:border-red-600 has-[:checked]:bg-red-50/40">
                            <input type="checkbox" name="organizations[]" value="{{ $orgName }}" class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-500 focus:ring-offset-0 w-4 h-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <i class="bi {{ $info['icon'] }} {{ $info['color'] }} text-base"></i>
                                    <span class="font-black text-slate-900 text-sm group-hover:text-red-700 transition">{{ $orgName }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1 leading-snug">{{ $info['desc'] }}</p>
                            </div>
                        </label>
                    @endforeach

                    @if(!empty($customOrgs))
                        @foreach($customOrgs as $cOrg)
                            <label class="relative flex items-start gap-3 p-4 rounded-2xl border-2 border-slate-200 hover:border-red-300 hover:bg-red-50/20 cursor-pointer transition shadow-sm group select-none has-[:checked]:border-red-600 has-[:checked]:bg-red-50/40">
                                <input type="checkbox" name="organizations[]" value="{{ $cOrg }}" class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-500 focus:ring-offset-0 w-4 h-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <i class="bi bi-flag-fill text-red-600 text-base"></i>
                                        <span class="font-black text-slate-900 text-sm group-hover:text-red-700 transition">{{ $cOrg }}</span>
                                    </div>
                                    <span class="inline-block text-[10px] text-red-700 bg-red-100 px-2 py-0.5 rounded font-bold mt-1">Elemen Tambahan</span>
                                </div>
                            </label>
                        @endforeach
                    @endif
                </div>

                <!-- INPUT OPSI ELEMEN DAKWAH BARU / TIDAK TERSEDIA -->
                <div class="mt-5 p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-slate-50 via-red-50/20 to-slate-50 border border-slate-200/90 shadow-2xs">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-6 h-6 rounded-lg bg-red-100 text-red-600 flex items-center justify-center text-xs">
                            <i class="bi bi-plus-lg font-black"></i>
                        </span>
                        <label for="input_new_org" class="font-black text-slate-800 text-xs uppercase tracking-wider">
                            Elemen Tidak Tersedia? Tambahkan Elemen Baru
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-500 mb-3 leading-relaxed">
                        Jika satuan tugas atau elemen dakwah Anda belum ada pada pilihan di atas, ketik namanya di bawah lalu klik <strong>Tambahkan</strong> agar langsung tampil dan terpilih pada formulir.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-flag text-xs"></i>
                            </div>
                            <input type="text" id="input_new_org" name="custom_organization" placeholder="Ketik nama elemen baru (contoh: TIM LOGISTIK, KOKAM, PANDU)..." autocomplete="off" class="w-full py-2.5 pl-9 pr-3.5 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-xs font-semibold text-slate-800 placeholder:text-slate-400 shadow-2xs">
                        </div>
                        <button type="button" onclick="addNewOrganization()" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-1.5 transition flex-shrink-0">
                            <i class="bi bi-plus-circle"></i>
                            <span>Tambahkan</span>
                        </button>
                    </div>
                    <div id="new_org_feedback" class="hidden text-xs mt-2 font-semibold"></div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 6: KEAHLIAN & MINAT -->
            <!-- ========================================================================= -->
            <div class="form-step hidden" id="step-6">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">6</span>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Potensi Bakat, Keahlian &amp; Minat Diri</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                <i class="bi bi-tools text-red-600"></i>
                                <span>Bakat / Keahlian yang Dikuasai:</span>
                            </h4>
                            <span class="text-[10px] text-slate-400">Pilih yang sesuai</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto p-3.5 rounded-2xl bg-slate-50 border border-slate-200" id="skills_container">
                            @foreach($skills as $sk)
                                <label class="flex items-center gap-2 p-2 rounded-xl hover:bg-white cursor-pointer transition select-none has-[:checked]:bg-white has-[:checked]:shadow-sm has-[:checked]:font-bold">
                                    <input type="checkbox" name="skills[]" value="{{ $sk->id }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500 w-3.5 h-3.5">
                                    <span class="text-slate-700 text-[11px] truncate">{{ $sk->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                <i class="bi bi-lightbulb text-amber-500"></i>
                                <span>Minat yang Ingin Dipelajari:</span>
                            </h4>
                            <span class="text-[10px] text-slate-400">Pilih yang sesuai</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto p-3.5 rounded-2xl bg-slate-50 border border-slate-200" id="interests_container">
                            @foreach($interests as $int)
                                <label class="flex items-center gap-2 p-2 rounded-xl hover:bg-white cursor-pointer transition select-none has-[:checked]:bg-white has-[:checked]:shadow-sm has-[:checked]:font-bold">
                                    <input type="checkbox" name="interests[]" value="{{ $int->id }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500 w-3.5 h-3.5">
                                    <span class="text-slate-700 text-[11px] truncate">{{ $int->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STEP 7: KONFIRMASI & SUBMIT -->
            <!-- ========================================================================= -->
            <div class="form-step hidden" id="step-7">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">7</span>
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Pemeriksaan Ringkasan &amp; Konfirmasi Kirim</h3>
                </div>

                <!-- DYNAMIC SUMMARY CARD -->
                <div class="p-5 sm:p-6 rounded-3xl bg-gradient-to-br from-slate-50 via-white to-slate-50 border border-slate-200/90 mb-5 text-xs shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-xl bg-red-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                <i class="bi bi-card-checklist"></i>
                            </span>
                            <span class="font-black text-slate-900 uppercase tracking-wider text-xs">Ringkasan Data Formulir</span>
                        </div>
                        <div id="summary_mode_badge">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-700">
                        <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                            <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap Pemuda</dt>
                            <dd id="summary_name" class="font-black text-slate-900 text-sm mt-0.5">-</dd>
                        </div>
                        <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                            <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Cabang MTA Terpilih</dt>
                            <dd id="summary_cabang" class="font-bold text-slate-900 text-sm mt-0.5 text-red-700">-</dd>
                        </div>
                        <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                            <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin &amp; Status</dt>
                            <dd id="summary_gender_status" class="font-semibold text-slate-800 mt-0.5">-</dd>
                        </div>
                        <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                            <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">WhatsApp / No. Kontak</dt>
                            <dd id="summary_phone" class="font-semibold text-slate-800 mt-0.5">-</dd>
                        </div>
                        <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-2xs sm:col-span-2">
                            <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Domisili</dt>
                            <dd id="summary_address" class="font-semibold text-slate-800 mt-0.5">-</dd>
                        </div>
                        <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-2xs sm:col-span-2" id="summary_org_box">
                            <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Elemen Dakwah yang Dipilih</dt>
                            <dd id="summary_organizations" class="flex flex-wrap gap-1.5">-</dd>
                        </div>
                    </dl>
                </div>

                <div class="p-4 rounded-2xl bg-red-50/70 border border-red-200 text-xs text-red-950 mb-4 leading-relaxed">
                    <strong class="font-bold block mb-1 flex items-center gap-1.5 text-red-900">
                        <i class="bi bi-shield-lock-fill"></i>
                        Pernyataan Kebenaran Data:
                    </strong>
                    Dengan mengirimkan formulir ini, saya menyatakan dengan sesungguhnya bahwa seluruh data yang diisikan adalah benar dan valid untuk kepentingan basis data organisasi Pemuda MTA Perwakilan Sragen.
                </div>

                <label class="flex items-start gap-3 p-4 rounded-2xl border-2 border-slate-200 hover:border-red-300 hover:bg-slate-50 cursor-pointer text-xs transition shadow-sm select-none has-[:checked]:border-red-600 has-[:checked]:bg-red-50/30">
                    <input type="checkbox" id="confirm_agreement" required class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-500 w-4 h-4">
                    <span class="text-slate-800 font-bold leading-relaxed">Saya telah memeriksa ringkasan data di atas dengan teliti dan menyatakan data siap dikirimkan.</span>
                </label>
            </div>

            <!-- ========================================================================= -->
            <!-- NAVIGATION BUTTONS (BOTTOM CONTROLS) -->
            <!-- ========================================================================= -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between gap-3">
                <button type="button" id="btnPrev" onclick="prevStep()" class="hidden px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-2 shadow-sm">
                    <i class="bi bi-arrow-left"></i>
                    <span id="btnPrevText">Kembali</span>
                </button>

                <div class="text-[11px] font-bold text-slate-400 hidden sm:block" id="stepCounterText">
                    Langkah 1 dari 7
                </div>

                <div class="ml-auto flex items-center gap-2">
                    <button type="button" id="btnNext" onclick="nextStep()" class="px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md shadow-red-200 flex items-center gap-2">
                        <span id="btnNextText">Lanjut ke Alamat</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                    <button type="submit" id="btnSubmit" class="hidden px-8 py-3 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-black text-xs transition shadow-lg shadow-red-200 flex items-center gap-2">
                        <i class="bi bi-check-circle-fill" id="btnSubmitIcon"></i>
                        <span id="btnSubmitText">Kirim Pendaftaran</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
@php
    $initialStep = 1;
    if ($errors->any()) {
        if ($errors->hasAny(['district_id', 'village_id', 'dusun', 'rt', 'rw', 'address_detail'])) {
            $initialStep = 2;
        } elseif ($errors->hasAny(['education_level_id', 'school_name', 'major', 'education_status', 'graduation_year'])) {
            $initialStep = 3;
        } elseif ($errors->hasAny(['job_status_id', 'job_title', 'company_name', 'business_field', 'business_name', 'business_address', 'business_contact', 'business_social'])) {
            $initialStep = 4;
        } elseif ($errors->hasAny(['organizations', 'custom_organization'])) {
            $initialStep = 5;
        } elseif ($errors->hasAny(['skills', 'interests'])) {
            $initialStep = 6;
        } else {
            $initialStep = 1;
        }
    }
@endphp
<script>
    let currentStep = {{ $initialStep }};
    const totalSteps = 7;
    let searchDebounceTimer = null;

    // Step configuration
    const stepTitles = {
        1: { name: 'Cabang & Data Diri', next: 'Lanjut ke Alamat', prev: '' },
        2: { name: 'Alamat Domisili',   next: 'Lanjut ke Pendidikan', prev: 'Kembali ke Data Diri' },
        3: { name: 'Pendidikan',        next: 'Lanjut ke Pekerjaan', prev: 'Kembali ke Alamat' },
        4: { name: 'Pekerjaan',         next: 'Lanjut ke Elemen Dakwah', prev: 'Kembali ke Pendidikan' },
        5: { name: 'Elemen Dakwah',     next: 'Lanjut ke Keahlian & Minat', prev: 'Kembali ke Pekerjaan' },
        6: { name: 'Keahlian & Minat',  next: 'Lanjut ke Konfirmasi', prev: 'Kembali ke Elemen Dakwah' },
        7: { name: 'Konfirmasi & Kirim',next: '', prev: 'Kembali ke Keahlian' }
    };

    // Elements
    const cabWrapper         = document.getElementById('cabang_dropdown_wrapper');
    const cabBtn             = document.getElementById('cabang_dropdown_btn');
    const cabLabel           = document.getElementById('cabang_selected_label');
    const cabPanel           = document.getElementById('cabang_dropdown_panel');
    const cabSearchInput     = document.getElementById('cabang_search_input');
    const cabInputHidden     = document.getElementById('public_cabang_id');
    const cabArrow           = document.getElementById('cabang_dropdown_arrow');
    const cabItems           = document.querySelectorAll('.cabang-option-item');
    const cabNoMatch         = document.getElementById('cabang_no_match');

    const inputName          = document.getElementById('input_name');
    const inputExistingId    = document.getElementById('input_existing_pemuda_id');
    const inputMtaUuid       = document.getElementById('input_mta_warga_uuid');
    const dropdownList       = document.getElementById('autocomplete_dropdown');
    const searchSpinner      = document.getElementById('search_spinner');
    const nameHelper         = document.getElementById('name_helper_text');
    const promptCabang       = document.getElementById('mode_prompt_cabang');
    const modeNewReg         = document.getElementById('mode_new_registration');
    const modeUpdate         = document.getElementById('mode_update_existing');
    const modeWarga          = document.getElementById('mode_warga_mta');
    const updateNameSpan     = document.getElementById('mode_update_name');
    const updateRegNoSpan    = document.getElementById('mode_update_reg_no');
    const existingFotoBox    = document.getElementById('existing_foto_box');
    const existingFotoImg    = document.getElementById('existing_foto_img');
    const btnSubmitText      = document.getElementById('btnSubmitText');
    const stepAlertBox       = document.getElementById('step_alert_box');
    const stepAlertMsg       = document.getElementById('step_alert_message');

    // 1. ALERT NOTIFICATIONS
    function showStepAlert(message) {
        if (stepAlertBox && stepAlertMsg) {
            stepAlertMsg.textContent = message;
            stepAlertBox.classList.remove('hidden');
            stepAlertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            alert(message);
        }
    }

    function hideStepAlert() {
        if (stepAlertBox) {
            stepAlertBox.classList.add('hidden');
        }
    }

    // 2. SEARCHABLE CABANG DROPDOWN LOGIC
    if (cabBtn && cabPanel) {
        cabBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = !cabPanel.classList.contains('hidden');
            if (isOpen) {
                closeCabangDropdown();
            } else {
                openCabangDropdown();
            }
        });

        function openCabangDropdown() {
            cabPanel.classList.remove('hidden');
            if (cabArrow) cabArrow.classList.add('rotate-180');
            if (cabSearchInput) {
                cabSearchInput.value = '';
                filterCabangOptions('');
                setTimeout(() => cabSearchInput.focus(), 50);
            }
        }

        function closeCabangDropdown() {
            cabPanel.classList.add('hidden');
            if (cabArrow) cabArrow.classList.remove('rotate-180');
        }

        // Close when clicking outside
        document.addEventListener('click', function (e) {
            if (cabWrapper && !cabWrapper.contains(e.target)) {
                closeCabangDropdown();
            }
        });

        // Filter options when typing in search input
        if (cabSearchInput) {
            cabSearchInput.addEventListener('input', function () {
                filterCabangOptions(this.value.toLowerCase().trim());
            });

            cabSearchInput.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        function filterCabangOptions(query) {
            let matchCount = 0;
            cabItems.forEach(item => {
                const name = (item.dataset.name || '').toLowerCase();
                const code = (item.dataset.code || '').toLowerCase();
                if (!query || name.includes(query) || code.includes(query)) {
                    item.classList.remove('hidden');
                    matchCount++;
                } else {
                    item.classList.add('hidden');
                }
            });
            if (cabNoMatch) {
                cabNoMatch.classList.toggle('hidden', matchCount > 0);
            }
        }

        // Select item
        cabItems.forEach(item => {
            item.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;

                cabInputHidden.value = id;
                if (cabLabel) {
                    cabLabel.textContent = name;
                    cabLabel.className = 'truncate text-slate-900 font-bold';
                }

                closeCabangDropdown();
                if (inputExistingId.value || inputMtaUuid.value) {
                    cancelUpdateMode();
                }
                onCabangChanged();
            });
        });
    }

    // 3. CABANG CHANGED TRIGGER
    function onCabangChanged() {
        const cabangId = cabInputHidden.value;
        if (cabangId) {
            inputName.disabled = false;
            inputName.classList.remove('bg-slate-100');
            inputName.classList.add('bg-white');
            inputName.placeholder = 'Ketik nama lengkap Anda di sini...';
            nameHelper.innerHTML = '<i class="bi bi-search text-slate-400"></i><span>Ketik minimal 2 huruf untuk memeriksa data di cabang ini atau mendaftar baru.</span>';

            promptCabang.classList.add('hidden');
            if (!inputExistingId.value && !inputMtaUuid.value) {
                modeNewReg.classList.remove('hidden');
                modeUpdate.classList.add('hidden');
                if (modeWarga) modeWarga.classList.add('hidden');
                if (btnSubmitText) btnSubmitText.textContent = 'Kirim Pendaftaran Baru';
            }
        } else {
            inputName.disabled = true;
            inputName.classList.remove('bg-white');
            inputName.classList.add('bg-slate-100');
            inputName.placeholder = 'Pilih Cabang terlebih dahulu...';
            nameHelper.innerHTML = '<i class="bi bi-info-circle text-slate-400"></i><span>Pilih Cabang MTA terlebih dahulu sebelum mengetik nama.</span>';

            promptCabang.classList.remove('hidden');
            modeNewReg.classList.add('hidden');
            modeUpdate.classList.add('hidden');
            if (modeWarga) modeWarga.classList.add('hidden');
            dropdownList.classList.add('hidden');
            cancelUpdateMode();
        }
    }

    // 4. AUTOCOMPLETE PENCARIAN NAMA (COMBINE DATA PEMUDA & WARGA MTA)
    if (inputName) {
        inputName.addEventListener('input', function () {
            const cabangId = cabInputHidden.value;
            const query = this.value.trim();

            clearTimeout(searchDebounceTimer);

            if (!cabangId) {
                dropdownList.classList.add('hidden');
                return;
            }

            if (query.length < 2) {
                dropdownList.classList.add('hidden');
                return;
            }

            // Debounce 250ms
            searchDebounceTimer = setTimeout(() => {
                performSearch(cabangId, query);
            }, 250);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!inputName.contains(e.target) && !dropdownList.contains(e.target)) {
                dropdownList.classList.add('hidden');
            }
        });
    }

    function performSearch(cabangId, query) {
        searchSpinner.classList.remove('hidden');

        fetch(`{{ route('pendataan.search-nama') }}?cabang_id=${cabangId}&q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(response => {
                searchSpinner.classList.add('hidden');
                const items = response.data || [];
                renderDropdown(items, query);
            })
            .catch(() => {
                searchSpinner.classList.add('hidden');
                dropdownList.classList.add('hidden');
            });
    }

    function renderDropdown(items, query) {
        dropdownList.innerHTML = '';

        if (items.length > 0) {
            const header = document.createElement('div');
            header.className = 'px-4 py-2.5 bg-slate-50 text-[11px] font-bold text-slate-600 uppercase tracking-wider flex items-center justify-between border-b border-slate-100';
            header.innerHTML = `<span class="flex items-center gap-1.5"><i class="bi bi-people text-red-600"></i> Ditemukan ${items.length} Data di Cabang Ini</span><span class="text-slate-400 font-normal">Klik untuk memilih</span>`;
            dropdownList.appendChild(header);

            items.forEach(p => {
                const item = document.createElement('div');
                item.className = 'px-4 py-3 hover:bg-red-50/70 cursor-pointer transition flex items-center justify-between group border-b border-slate-100 last:border-0';

                const isWarga = (p.source === 'warga_mta');
                const badgeHtml = isWarga
                    ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800"><i class="bi bi-patch-check-fill mr-1 text-sky-600"></i>Warga MTA Pusat</span>`
                    : `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800"><i class="bi bi-person-check-fill mr-1 text-emerald-600"></i>Data Pemuda</span>`;

                item.innerHTML = `
                    <div>
                        <div class="font-bold text-slate-900 group-hover:text-red-700 text-sm flex items-center gap-2">
                            <span>${escapeHtml(p.name)}</span>
                            ${badgeHtml}
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold ${p.gender === 'L' ? 'bg-slate-100 text-slate-700' : 'bg-rose-100 text-rose-800'}">
                                ${escapeHtml(p.gender_text)}
                            </span>
                            ${p.birth_date ? `<span><i class="bi bi-calendar3 mr-1"></i>${escapeHtml(p.birth_date)}</span>` : ''}
                            ${p.birth_place ? `<span><i class="bi bi-geo-alt mr-0.5"></i>${escapeHtml(p.birth_place)}</span>` : ''}
                            ${p.phone ? `<span><i class="bi bi-telephone mr-0.5"></i>${escapeHtml(p.phone)}</span>` : ''}
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white group-hover:bg-red-600 group-hover:text-white text-xs font-bold text-slate-600 border border-slate-200 group-hover:border-red-600 transition shadow-2xs">
                        <span>Pilih</span>
                        <i class="bi bi-arrow-right"></i>
                    </span>
                `;

                item.addEventListener('click', () => {
                    if (isWarga) {
                        selectWargaMta(p.uuid, p.name);
                    } else {
                        selectExistingPemuda(p.id, p.name);
                    }
                });

                dropdownList.appendChild(item);
            });

            // Action: Continue as new registration
            const newOption = document.createElement('div');
            newOption.className = 'px-4 py-3 bg-slate-50/80 hover:bg-slate-100 text-slate-700 text-xs font-semibold cursor-pointer border-t border-slate-200 flex items-center justify-between';
            newOption.innerHTML = `
                <div class="flex items-center gap-2">
                    <i class="bi bi-plus-circle-fill text-red-600 text-sm"></i>
                    <span>Bukan Anda? Tetap daftar sebagai <strong>Pemuda Baru</strong> dengan nama ini</span>
                </div>
                <span class="text-red-600 font-bold text-xs">Lanjutkan &rarr;</span>
            `;
            newOption.addEventListener('click', () => {
                selectAsNewPemuda(query);
            });
            dropdownList.appendChild(newOption);
        } else {
            const noMatch = document.createElement('div');
            noMatch.className = 'p-5 text-center text-xs text-slate-500';
            noMatch.innerHTML = `
                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                    <i class="bi bi-person-x"></i>
                </div>
                <div class="font-bold text-slate-800 text-sm mb-1">Belum Tercatat di Cabang Ini</div>
                <p class="text-slate-500 text-xs mb-3 max-w-sm mx-auto">Nama "<strong>${escapeHtml(query)}</strong>" belum ada di basis data. Anda akan didaftarkan sebagai <strong>Kader Pemuda Baru</strong>.</p>
                <button type="button" class="px-4 py-2 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition shadow-sm inline-flex items-center gap-1.5 text-xs">
                    <i class="bi bi-check2"></i>
                    <span>Gunakan Nama Ini &amp; Lanjutkan</span>
                </button>
            `;
            noMatch.querySelector('button').addEventListener('click', () => {
                selectAsNewPemuda(query);
            });
            dropdownList.appendChild(noMatch);
        }

        dropdownList.classList.remove('hidden');
    }

    // 5. SELECT EXISTING PEMUDA (UPDATE PMD)
    function selectExistingPemuda(id, name) {
        const cabangId = cabInputHidden.value;
        dropdownList.classList.add('hidden');
        searchSpinner.classList.remove('hidden');

        fetch(`{{ url('pendataan/get-pemuda') }}/${id}?cabang_id=${cabangId}`)
            .then(res => res.json())
            .then(res => {
                searchSpinner.classList.add('hidden');
                if (res.status === 'success') {
                    populateFormWithData(res.data);
                } else {
                    showStepAlert(res.message || 'Gagal mengambil data pemuda.');
                }
            })
            .catch(() => {
                searchSpinner.classList.add('hidden');
                showStepAlert('Terjadi gangguan koneksi saat memuat data pemuda.');
            });
    }

    function populateFormWithData(p) {
        inputExistingId.value = p.id;
        inputMtaUuid.value = p.mta_warga_uuid || '';

        modeNewReg.classList.add('hidden');
        if (modeWarga) modeWarga.classList.add('hidden');
        modeUpdate.classList.remove('hidden');
        promptCabang.classList.add('hidden');

        updateNameSpan.textContent = p.name;
        updateRegNoSpan.textContent = p.registration_number ? `(No: ${p.registration_number})` : '';

        if (btnSubmitText) btnSubmitText.textContent = 'Simpan & Perbarui Data';

        // Step 1
        inputName.value = p.name || '';
        document.getElementById('input_gender').value = p.gender || 'L';
        document.getElementById('input_birth_place').value = p.birth_place || '';
        document.getElementById('input_birth_date').value = p.birth_date || '';
        document.getElementById('input_marital_status').value = p.marital_status || 'belum_menikah';
        document.getElementById('input_blood_type').value = p.blood_type || 'tidak_tahu';
        document.getElementById('input_phone').value = p.phone || '';
        document.getElementById('input_email').value = p.email || '';

        // Foto preview
        if (p.foto) {
            existingFotoImg.src = p.foto;
            existingFotoBox.classList.remove('hidden');
        } else {
            existingFotoBox.classList.add('hidden');
        }

        // Step 2 (Alamat)
        if (p.alamat) {
            const distSelect = document.getElementById('public_district_id');
            distSelect.value = p.alamat.district_id || '';
            loadVillagesAndSet(p.alamat.district_id, p.alamat.village_id);
            document.getElementById('input_dusun').value = p.alamat.dusun || '';
            document.getElementById('input_rt').value = p.alamat.rt || '';
            document.getElementById('input_rw').value = p.alamat.rw || '';
            document.getElementById('input_address_detail').value = p.alamat.address_detail || '';
        }

        // Step 3 (Pendidikan)
        if (p.pendidikan) {
            document.getElementById('input_education_level_id').value = p.pendidikan.education_level_id || '';
            document.getElementById('input_school_name').value = p.pendidikan.school_name || '';
            document.getElementById('input_major').value = p.pendidikan.major || '';
            document.getElementById('input_education_status').value = p.pendidikan.education_status || 'lulus';
            document.getElementById('input_graduation_year').value = p.pendidikan.graduation_year || '';
        }

        // Step 4 (Pekerjaan)
        if (p.pekerjaan) {
            document.getElementById('input_job_status_id').value = p.pekerjaan.job_status_id || '';
            document.getElementById('input_job_title').value = p.pekerjaan.job_title || '';
            document.getElementById('input_company_name').value = p.pekerjaan.company_name || '';
            document.getElementById('input_business_field').value = p.pekerjaan.business_field || '';
            document.getElementById('input_business_name').value = p.pekerjaan.business_name || '';
            document.getElementById('input_business_contact').value = p.pekerjaan.business_contact || '';
        }

        // Step 5 (Elemen Dakwah)
        const orgCheckboxes = document.querySelectorAll('input[name="organizations[]"]');
        orgCheckboxes.forEach(cb => {
            cb.checked = Array.isArray(p.organisasi) && p.organisasi.includes(cb.value);
        });
        if (Array.isArray(p.organisasi)) {
            p.organisasi.forEach(orgName => {
                const cleanName = (orgName || '').trim();
                if (!cleanName) return;
                let found = false;
                const currentCbs = document.querySelectorAll('input[name="organizations[]"]');
                currentCbs.forEach(cb => {
                    if (cb.value.toLowerCase().trim() === cleanName.toLowerCase()) {
                        cb.checked = true;
                        found = true;
                    }
                });
                if (!found) {
                    addNewOrganization(cleanName, true);
                }
            });
        }

        // Step 6 (Skills & Interests)
        const skillCheckboxes = document.querySelectorAll('input[name="skills[]"]');
        skillCheckboxes.forEach(cb => {
            const val = parseInt(cb.value, 10);
            cb.checked = Array.isArray(p.skills) && p.skills.includes(val);
        });

        const interestCheckboxes = document.querySelectorAll('input[name="interests[]"]');
        interestCheckboxes.forEach(cb => {
            const val = parseInt(cb.value, 10);
            cb.checked = Array.isArray(p.interests) && p.interests.includes(val);
        });
    }

    // 6. SELECT WARGA MTA PUSAT (INTEGRATE MTA CITIZEN TO PMD)
    function selectWargaMta(uuid, name) {
        const cabangId = cabInputHidden.value;
        dropdownList.classList.add('hidden');
        searchSpinner.classList.remove('hidden');

        fetch(`{{ url('pendataan/get-warga') }}/${uuid}?cabang_id=${cabangId}`)
            .then(res => res.json())
            .then(res => {
                searchSpinner.classList.add('hidden');
                if (res.status === 'success') {
                    populateFormWithWargaData(res.data);
                } else {
                    showStepAlert(res.message || 'Gagal memuat data warga MTA.');
                }
            })
            .catch(() => {
                searchSpinner.classList.add('hidden');
                showStepAlert('Terjadi gangguan koneksi saat memuat data warga MTA.');
            });
    }

    function populateFormWithWargaData(w) {
        inputExistingId.value = '';
        inputMtaUuid.value = w.mta_warga_uuid || '';

        modeNewReg.classList.add('hidden');
        modeUpdate.classList.add('hidden');
        promptCabang.classList.add('hidden');

        if (modeWarga) {
            document.getElementById('mode_warga_name').textContent = w.name;
            modeWarga.classList.remove('hidden');
        }

        if (btnSubmitText) btnSubmitText.textContent = 'Kirim Pendaftaran (Sinkron MTA)';

        // Step 1
        inputName.value = w.name || '';
        document.getElementById('input_gender').value = w.gender || 'L';
        if (w.birth_place) document.getElementById('input_birth_place').value = w.birth_place;
        if (w.birth_date) document.getElementById('input_birth_date').value = w.birth_date;
        if (w.marital_status) document.getElementById('input_marital_status').value = w.marital_status;
        if (w.blood_type) document.getElementById('input_blood_type').value = w.blood_type;
        if (w.phone) document.getElementById('input_phone').value = w.phone;

        // Foto preview if available from MTA Pusat
        if (w.foto) {
            existingFotoImg.src = w.foto;
            existingFotoBox.classList.remove('hidden');
        } else {
            existingFotoBox.classList.add('hidden');
        }

        // Step 2 (Alamat)
        if (w.alamat) {
            const distSelect = document.getElementById('public_district_id');
            if (w.alamat.district_id) {
                distSelect.value = w.alamat.district_id;
                loadVillagesAndSet(w.alamat.district_id, w.alamat.village_id);
            }
            if (w.alamat.dusun) document.getElementById('input_dusun').value = w.alamat.dusun;
            if (w.alamat.rt) document.getElementById('input_rt').value = w.alamat.rt;
            if (w.alamat.rw) document.getElementById('input_rw').value = w.alamat.rw;
            if (w.alamat.address_detail) document.getElementById('input_address_detail').value = w.alamat.address_detail;
        }

        // Step 4 (Pekerjaan)
        if (w.pekerjaan && w.pekerjaan.job_title) {
            document.getElementById('input_job_title').value = w.pekerjaan.job_title;
        }
    }

    // 7. SELECT AS NEW PEMUDA
    function selectAsNewPemuda(name) {
        dropdownList.classList.add('hidden');
        inputExistingId.value = '';
        inputMtaUuid.value = '';
        inputName.value = name;
        modeNewReg.classList.remove('hidden');
        modeUpdate.classList.add('hidden');
        if (modeWarga) modeWarga.classList.add('hidden');
        existingFotoBox.classList.add('hidden');
        if (btnSubmitText) btnSubmitText.textContent = 'Kirim Pendaftaran Baru';
    }

    // 8. CANCEL UPDATE MODE
    function cancelUpdateMode() {
        inputExistingId.value = '';
        inputMtaUuid.value = '';
        modeUpdate.classList.add('hidden');
        if (modeWarga) modeWarga.classList.add('hidden');

        if (cabInputHidden.value) {
            modeNewReg.classList.remove('hidden');
            if (btnSubmitText) btnSubmitText.textContent = 'Kirim Pendaftaran Baru';
        } else {
            promptCabang.classList.remove('hidden');
            modeNewReg.classList.add('hidden');
        }
        existingFotoBox.classList.add('hidden');
    }

    // 8.1 MANAJEMEN ELEMEN DAKWAH BARU / KUSTOM
    function addNewOrganization(nameFromParam, silent = false) {
        const input = document.getElementById('input_new_org');
        const feedback = document.getElementById('new_org_feedback');
        const orgName = (nameFromParam || (input ? input.value : '')).trim();

        if (!orgName) {
            if (!silent && feedback) {
                feedback.className = 'text-rose-600 text-xs mt-2 font-semibold flex items-center gap-1';
                feedback.innerHTML = '<i class="bi bi-exclamation-circle"></i> Silakan ketik nama elemen dakwah terlebih dahulu.';
                feedback.classList.remove('hidden');
            }
            return false;
        }

        const container = document.getElementById('org_checkboxes_container');
        if (!container) return false;

        // Cek apakah elemen dengan nama ini sudah ada di daftar (case-insensitive)
        const existingCbs = container.querySelectorAll('input[name="organizations[]"]');
        let foundCb = null;
        existingCbs.forEach(cb => {
            if (cb.value.toLowerCase().trim() === orgName.toLowerCase()) {
                foundCb = cb;
            }
        });

        if (foundCb) {
            foundCb.checked = true;
            foundCb.closest('label')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (!silent && feedback) {
                feedback.className = 'text-emerald-700 text-xs mt-2 font-semibold flex items-center gap-1';
                feedback.innerHTML = `<i class="bi bi-check-circle-fill"></i> Elemen "<strong>${escapeHtml(foundCb.value)}</strong>" sudah tersedia pada pilihan dan telah dicentang.`;
                feedback.classList.remove('hidden');
            }
            if (input) input.value = '';
            updateStep7Summary();
            return true;
        }

        // Buat kartu elemen baru secara dinamis
        const newCard = document.createElement('label');
        newCard.className = 'relative flex items-start gap-3 p-4 rounded-2xl border-2 border-red-600 bg-red-50/40 hover:border-red-600 hover:bg-red-50/50 cursor-pointer transition shadow-sm group select-none has-[:checked]:border-red-600 has-[:checked]:bg-red-50/40 animate-in fade-in zoom-in-95 duration-150';
        newCard.innerHTML = `
            <input type="checkbox" name="organizations[]" value="${escapeHtml(orgName)}" checked class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-500 focus:ring-offset-0 w-4 h-4">
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-1">
                    <div class="flex items-center gap-1.5">
                        <i class="bi bi-flag-fill text-red-600 text-base"></i>
                        <span class="font-black text-slate-900 text-sm group-hover:text-red-700 transition">${escapeHtml(orgName)}</span>
                    </div>
                    <button type="button" onclick="removeDynamicOrg(this, event)" title="Hapus elemen ini" class="text-slate-400 hover:text-rose-600 p-1 rounded-lg hover:bg-white transition text-xs">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
                <span class="inline-block text-[10px] text-red-700 bg-red-100 px-2 py-0.5 rounded font-bold mt-1">Elemen Baru</span>
            </div>
        `;

        const cb = newCard.querySelector('input[type="checkbox"]');
        if (cb) {
            cb.addEventListener('change', () => updateStep7Summary());
        }

        container.appendChild(newCard);

        if (input) input.value = '';
        if (!silent && feedback) {
            feedback.className = 'text-emerald-700 text-xs mt-2 font-semibold flex items-center gap-1';
            feedback.innerHTML = `<i class="bi bi-check-circle-fill"></i> Elemen baru "<strong>${escapeHtml(orgName)}</strong>" berhasil ditambahkan dan langsung tampil pada formulir!`;
            feedback.classList.remove('hidden');
        }

        updateStep7Summary();
        return true;
    }

    function removeDynamicOrg(btn, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const label = btn.closest('label');
        if (label) {
            label.remove();
            updateStep7Summary();
        }
    }

    // 9. UPDATE STEP VIEW & NAVIGATION
    function updateStepView() {
        hideStepAlert();

        for (let i = 1; i <= totalSteps; i++) {
            const stepEl = document.getElementById(`step-${i}`);
            const circle = document.getElementById(`step-circle-${i}`);
            const label  = document.getElementById(`step-label-${i}`);
            if (stepEl) {
                if (i === currentStep) {
                    stepEl.classList.remove('hidden');
                } else {
                    stepEl.classList.add('hidden');
                }
            }
            if (circle) {
                if (i === currentStep) {
                    circle.className = 'w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-xs transition-all duration-200 bg-red-600 text-white shadow-lg shadow-red-200 ring-4 ring-red-100 scale-105';
                    circle.innerHTML = `${i}`;
                    if (label) label.className = 'text-[11px] mt-2 font-black text-red-700 transition-colors truncate max-w-full';
                } else if (i < currentStep) {
                    circle.className = 'w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-xs transition-all duration-200 bg-emerald-600 text-white shadow-sm';
                    circle.innerHTML = '<i class="bi bi-check-lg text-base"></i>';
                    if (label) label.className = 'text-[11px] mt-2 font-bold text-emerald-700 transition-colors truncate max-w-full';
                } else {
                    circle.className = 'w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-xs transition-all duration-200 bg-white text-slate-500 border border-slate-300 shadow-sm group-hover:border-slate-400';
                    circle.innerHTML = `${i}`;
                    if (label) label.className = 'text-[11px] mt-2 font-bold text-slate-500 transition-colors truncate max-w-full';
                }
            }
        }

        // Desktop Progress Bar Track
        const track = document.getElementById('desktopProgressTrack');
        if (track) {
            track.style.width = `${((currentStep - 1) / (totalSteps - 1)) * 100}%`;
        }

        // Mobile Progress
        const mobileText = document.getElementById('mobileStepText');
        const mobileSub  = document.getElementById('mobileStepSubtext');
        const mobileBar  = document.getElementById('mobileProgressBar');
        if (mobileText) mobileText.textContent = `Langkah ${currentStep} dari ${totalSteps}: ${stepTitles[currentStep].name}`;
        if (mobileSub) mobileSub.textContent = `Bagian ${currentStep} dari ${totalSteps} formulir pendataan`;
        if (mobileBar) mobileBar.style.width = `${(currentStep / totalSteps) * 100}%`;

        // Desktop Counter
        const counterText = document.getElementById('stepCounterText');
        if (counterText) counterText.textContent = `Langkah ${currentStep} dari ${totalSteps}`;

        // Buttons
        const btnPrev = document.getElementById('btnPrev');
        const btnPrevText = document.getElementById('btnPrevText');
        const btnNext = document.getElementById('btnNext');
        const btnNextText = document.getElementById('btnNextText');
        const btnSubmit = document.getElementById('btnSubmit');

        if (currentStep === 1) {
            btnPrev.classList.add('hidden');
        } else {
            btnPrev.classList.remove('hidden');
            if (btnPrevText) btnPrevText.textContent = stepTitles[currentStep].prev || 'Kembali';
        }

        if (currentStep === totalSteps) {
            btnNext.classList.add('hidden');
            btnSubmit.classList.remove('hidden');
            updateStep7Summary();
        } else {
            btnNext.classList.remove('hidden');
            btnSubmit.classList.add('hidden');
            if (btnNextText) btnNextText.textContent = stepTitles[currentStep].next || 'Lanjutkan';
        }

        // Smooth scroll to form top
        const formEl = document.getElementById('pendataanForm');
        if (formEl) {
            formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // 10. STEP VALIDATION
    function validateStep(stepNumber) {
        if (stepNumber === 1) {
            if (!cabInputHidden.value) {
                showStepAlert('Silakan pilih Cabang MTA tempat mengaji Anda terlebih dahulu.');
                if (cabBtn) cabBtn.focus();
                return false;
            }
            if (!inputName.value.trim() || inputName.value.trim().length < 3) {
                showStepAlert('Silakan ketik Nama Lengkap Anda minimal 3 karakter.');
                inputName.focus();
                return false;
            }
            const birthPlace = document.getElementById('input_birth_place');
            if (!birthPlace.value.trim()) {
                showStepAlert('Silakan isi Tempat Lahir Anda.');
                birthPlace.focus();
                return false;
            }
            const birthDate = document.getElementById('input_birth_date');
            if (!birthDate.value) {
                showStepAlert('Silakan isi Tanggal Lahir Anda.');
                birthDate.focus();
                return false;
            }
            const phone = document.getElementById('input_phone');
            if (!phone.value.trim() || phone.value.trim().length < 9) {
                showStepAlert('Silakan isi Nomor WhatsApp / HP aktif minimal 9 digit.');
                phone.focus();
                return false;
            }
            return true;
        }

        if (stepNumber === 2) {
            const dist = document.getElementById('public_district_id');
            const vill = document.getElementById('public_village_id');
            const addr = document.getElementById('input_address_detail');
            if (!dist.value) {
                showStepAlert('Silakan pilih Kecamatan domisili Anda di Sragen.');
                dist.focus();
                return false;
            }
            if (!vill.value) {
                showStepAlert('Silakan pilih Desa / Kelurahan domisili Anda.');
                vill.focus();
                return false;
            }
            if (!addr.value.trim() || addr.value.trim().length < 5) {
                showStepAlert('Silakan lengkapi Alamat Lengkap / Patokan Rumah (minimal 5 karakter).');
                addr.focus();
                return false;
            }
            return true;
        }

        if (stepNumber === 3) {
            const school = document.getElementById('input_school_name');
            if (!school.value.trim()) {
                showStepAlert('Silakan isi Nama Lembaga / Sekolah / Kampus pendidikan terakhir Anda.');
                school.focus();
                return false;
            }
            return true;
        }

        if (stepNumber === 4) {
            const job = document.getElementById('input_job_status_id');
            if (!job.value) {
                showStepAlert('Silakan pilih Status Pekerjaan Anda.');
                job.focus();
                return false;
            }
            return true;
        }

        if (stepNumber === 5) {
            const inputNewOrg = document.getElementById('input_new_org');
            if (inputNewOrg && inputNewOrg.value.trim() !== '') {
                addNewOrganization();
            }
            return true;
        }

        return true;
    }

    function nextStep() {
        if (validateStep(currentStep)) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepView();
            }
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepView();
        }
    }

    function goToStep(step) {
        if (step === currentStep) return;
        if (step < currentStep) {
            currentStep = step;
            updateStepView();
            return;
        }
        for (let i = currentStep; i < step; i++) {
            if (!validateStep(i)) return;
        }
        currentStep = step;
        updateStepView();
    }

    // 11. STEP 7 SUMMARY GENERATOR
    function updateStep7Summary() {
        const summaryName    = document.getElementById('summary_name');
        const summaryCabang  = document.getElementById('summary_cabang');
        const summaryGender  = document.getElementById('summary_gender_status');
        const summaryPhone   = document.getElementById('summary_phone');
        const summaryAddr    = document.getElementById('summary_address');
        const summaryBadge   = document.getElementById('summary_mode_badge');
        const summaryOrgs    = document.getElementById('summary_organizations');

        if (summaryName) summaryName.textContent = inputName.value.trim() || '-';
        if (summaryCabang) summaryCabang.textContent = cabLabel ? cabLabel.textContent.trim() : '-';
        
        if (summaryGender) {
            const genderVal = document.getElementById('input_gender').value;
            const maritalVal = document.getElementById('input_marital_status').value;
            const bloodVal = document.getElementById('input_blood_type').value;
            const genderText = genderVal === 'L' ? 'Laki-laki (Pemuda)' : 'Perempuan (Pemudi)';
            const maritalText = maritalVal.replace('_', ' ');
            const bloodText = (bloodVal && bloodVal !== 'tidak_tahu') ? `Gol. Darah ${bloodVal}` : 'Gol. Darah -';
            summaryGender.textContent = `${genderText} • ${maritalText} • ${bloodText}`;
        }

        if (summaryPhone) summaryPhone.textContent = document.getElementById('input_phone').value || '-';
        
        if (summaryAddr) {
            const distSelect = document.getElementById('public_district_id');
            const villSelect = document.getElementById('public_village_id');
            const distText = distSelect.selectedIndex > 0 ? distSelect.options[distSelect.selectedIndex].text : '';
            const villText = villSelect.selectedIndex > 0 ? villSelect.options[villSelect.selectedIndex].text : '';
            const detailText = document.getElementById('input_address_detail').value.trim();
            summaryAddr.textContent = `${detailText ? detailText + ', ' : ''}${villText ? 'Ds. ' + villText + ', ' : ''}${distText ? 'Kec. ' + distText : '-'}`;
        }

        if (summaryBadge) {
            if (inputExistingId.value) {
                summaryBadge.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="bi bi-arrow-repeat text-emerald-600"></i> Mode Pembaruan Data</span>`;
            } else if (inputMtaUuid.value) {
                summaryBadge.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200"><i class="bi bi-patch-check-fill text-sky-600"></i> Terintegrasi Warga MTA Pusat</span>`;
            } else {
                summaryBadge.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-200 text-slate-800 border border-slate-300"><i class="bi bi-person-plus-fill text-slate-600"></i> Pendaftaran Pemuda Baru</span>`;
            }
        }

        // Selected organizations preview
        if (summaryOrgs) {
            const checkedOrgs = Array.from(document.querySelectorAll('input[name="organizations[]"]:checked')).map(cb => cb.value);
            if (checkedOrgs.length > 0) {
                summaryOrgs.innerHTML = checkedOrgs.map(org => `<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-red-100 text-red-800 border border-red-200"><i class="bi bi-check-circle-fill mr-1 text-red-600"></i>${org}</span>`).join('');
            } else {
                summaryOrgs.innerHTML = '<span class="text-slate-400 italic">Belum memilih elemen dakwah</span>';
            }
        }
    }

    // 12. DYNAMIC VILLAGES LOAD
    const distSelect = document.getElementById('public_district_id');
    const villSelect = document.getElementById('public_village_id');
    if (distSelect && villSelect) {
        distSelect.addEventListener('change', function () {
            loadVillagesAndSet(this.value, null);
        });
    }

    function loadVillagesAndSet(districtId, targetVillageId) {
        if (!villSelect) return;
        villSelect.innerHTML = '<option value="">-- Memuat Desa... --</option>';
        if (!districtId) {
            villSelect.innerHTML = '<option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>';
            return;
        }

        fetch(`{{ url('api/villages') }}/${districtId}`)
            .then(res => res.json())
            .then(data => {
                villSelect.innerHTML = '<option value="">-- Pilih Desa / Kelurahan --</option>';
                data.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = v.name;
                    if (targetVillageId && String(v.id) === String(targetVillageId)) {
                        opt.selected = true;
                    }
                    villSelect.appendChild(opt);
                });
            })
            .catch(() => {
                villSelect.innerHTML = '<option value="">-- Gagal Memuat Desa --</option>';
            });
    }

    // 13. FORM SUBMIT HANDLER
    const pendataanForm = document.getElementById('pendataanForm');
    if (pendataanForm) {
        pendataanForm.addEventListener('submit', function (e) {
            for (let step = 1; step <= 6; step++) {
                if (!validateStep(step)) {
                    e.preventDefault();
                    currentStep = step;
                    updateStepView();
                    return false;
                }
            }

            const agreement = document.getElementById('confirm_agreement');
            if (agreement && !agreement.checked) {
                e.preventDefault();
                showStepAlert('Silakan centang kotak pernyataan kebenaran data terlebih dahulu.');
                agreement.focus();
                return false;
            }

            const btnSubmit = document.getElementById('btnSubmit');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                const btnSubmitIcon = document.getElementById('btnSubmitIcon');
                if (btnSubmitIcon) {
                    btnSubmitIcon.className = 'spinner-border spinner-border-sm animate-spin inline-block w-4 h-4 border-2 rounded-full';
                }
            }
        });
    }

    // HTML escape utility
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Initialize state on load
    document.addEventListener('DOMContentLoaded', function () {
        if (cabInputHidden && cabInputHidden.value) {
            onCabangChanged();
        }

        if (inputExistingId && inputExistingId.value) {
            modeNewReg.classList.add('hidden');
            if (modeWarga) modeWarga.classList.add('hidden');
            modeUpdate.classList.remove('hidden');
            if (updateNameSpan) updateNameSpan.textContent = inputName.value;
            if (btnSubmitText) btnSubmitText.textContent = 'Simpan & Perbarui Data';
        } else if (inputMtaUuid && inputMtaUuid.value) {
            modeNewReg.classList.add('hidden');
            modeUpdate.classList.add('hidden');
            if (modeWarga) {
                const wargaNameSpan = document.getElementById('mode_warga_name');
                if (wargaNameSpan) wargaNameSpan.textContent = inputName.value;
                modeWarga.classList.remove('hidden');
            }
            if (btnSubmitText) btnSubmitText.textContent = 'Kirim Pendaftaran (Sinkron MTA)';
        }

        if (distSelect && distSelect.value) {
            const oldVillage = "{{ old('village_id') }}";
            loadVillagesAndSet(distSelect.value, oldVillage || null);
        }

        const inputNewOrg = document.getElementById('input_new_org');
        if (inputNewOrg) {
            inputNewOrg.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addNewOrganization();
                }
            });
        }

        @if($errors->any() || session('error'))
            updateStepView();
        @endif
    });
</script>
@endsection
