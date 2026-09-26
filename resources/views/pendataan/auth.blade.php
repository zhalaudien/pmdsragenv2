@extends('layouts.app')

@section('title', 'Autentikasi & Verifikasi Pemuda | Sistem Pendataan Pemuda MTA Sragen')

@section('content')
<div class="py-8 sm:py-12 bg-slate-50/70 min-h-screen">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">

        <!-- TOP BREADCRUMB & HEADER BADGE -->
        <div class="flex items-center justify-between gap-2 mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition border border-slate-200 shadow-2xs">
                <i class="bi bi-arrow-left text-red-600 font-bold"></i>
                <span>Kembali ke Beranda</span>
            </a>
            <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
                <i class="bi bi-shield-check text-emerald-600"></i>
                <span>Portal Basis Data Resmi Pemuda MTA Sragen</span>
            </div>
        </div>

        <!-- AUTHENTICATION HERO TITLE -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-red-600 to-rose-700 text-white shadow-lg shadow-red-200 mb-4 p-3 border border-red-500">
                <img src="{{ asset('icons/pemudamta.png') }}" alt="Logo Pemuda MTA" class="w-full h-full object-contain filter drop-shadow">
            </div>
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-red-50 border border-red-200 text-red-700 text-xs font-bold mb-2 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                <span>Langkah 1: Verifikasi &amp; Autentikasi Pemuda</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Formulir Pendataan Pemuda</h1>
            <p class="text-xs sm:text-sm text-slate-600 max-w-lg mx-auto mt-2 leading-relaxed">
                Tentukan <strong>Cabang MTA</strong> tempat mengaji, masukkan <strong>Nama Lengkap</strong>, dan <strong>Tanggal Lahir</strong> Anda untuk masuk ke formulir.
            </p>
        </div>

        <!-- SMART SEARCH / ANTI-DUPLIKASI NOTICE -->
        <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-red-50 via-white to-amber-50/60 border border-red-200/80 text-xs text-slate-700 shadow-sm mb-6 flex items-start gap-3.5">
            <span class="w-9 h-9 rounded-xl bg-red-600 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-red-200 text-base mt-0.5">
                <i class="bi bi-person-check-fill"></i>
            </span>
            <div class="space-y-1">
                <strong class="font-bold text-slate-900 text-sm block">Pencegahan Data Ganda (Anti-Duplikasi):</strong>
                <p class="text-slate-600 leading-relaxed text-[11px] sm:text-xs">
                    Ketik <strong>minimal 4 huruf nama</strong> untuk menampilkan sugesti data pemuda yang sudah ada atau warga binaan MTA Pusat di cabang Anda. 
                    Jika data Anda sudah ada, sistem akan otomatis beralih ke mode <strong>Pembaruan / Update Data</strong>. Jika belum ada, Anda dapat melanjutkan sebagai <strong>Pendaftaran Pemuda Baru</strong>.
                </p>
            </div>
        </div>

        <!-- NOTIFICATION ALERTS -->
        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-950 text-xs shadow-sm flex items-start gap-3 mb-6 animate-in fade-in">
                <span class="w-7 h-7 rounded-lg bg-rose-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </span>
                <div class="flex-1">
                    <strong class="font-bold block text-rose-900 mb-0.5">Akses Ditolak:</strong>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 rounded-2xl bg-sky-50 border-2 border-sky-300 text-sky-950 text-xs shadow-sm flex items-start gap-3 mb-6 animate-in fade-in">
                <span class="w-7 h-7 rounded-lg bg-sky-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="bi bi-info-circle-fill"></i>
                </span>
                <div class="flex-1 leading-relaxed">
                    {!! session('info') !!}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-950 text-xs shadow-sm space-y-1 mb-6">
                <div class="font-bold text-rose-900 flex items-center gap-1.5">
                    <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
                    <span>Terdapat data yang belum sesuai:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-800 font-medium pl-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- MAIN AUTH CARD -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xl shadow-slate-200/50">
            <form action="{{ route('pendataan.auth') }}" method="POST" id="authForm" novalidate class="space-y-6">
                @csrf

                <!-- Hidden inputs for autocomplete selection -->
                <input type="hidden" name="selected_id" id="selected_id" value="{{ old('selected_id', '') }}">
                <input type="hidden" name="selected_uuid" id="selected_uuid" value="{{ old('selected_uuid', '') }}">
                <input type="hidden" name="selected_source" id="selected_source" value="{{ old('selected_source', '') }}">

                <!-- 1. PILIH CABANG MTA -->
                <div class="space-y-1.5 text-xs relative" id="cabang_wrapper">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-800 uppercase tracking-wider text-[11px]">
                            1. Cabang MTA Tempat Mengaji <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md font-semibold">
                            {{ $cabangList->count() }} Cabang Se-Sragen
                        </span>
                    </div>

                    @php
                        $preselectedId = old('cabang_id', $selectedCabangId ?? 0);
                        $preselectedCabang = $preselectedId ? $cabangList->firstWhere('id', $preselectedId) : null;
                    @endphp

                    <!-- Hidden native input to submit cabang_id -->
                    <input type="hidden" name="cabang_id" id="public_cabang_id" value="{{ $preselectedId ?: '' }}" required>

                    <!-- Dropdown Button Trigger -->
                    <button type="button" id="cabang_dropdown_btn" class="w-full py-3.5 px-4 rounded-2xl border border-slate-300 bg-white hover:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-semibold text-slate-800 shadow-2xs flex items-center justify-between transition text-left group">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <i class="bi bi-geo-alt-fill text-slate-400 group-hover:text-red-600 transition text-base flex-shrink-0"></i>
                            <span id="cabang_selected_label" class="truncate {{ $preselectedCabang ? 'text-slate-900 font-bold' : 'text-slate-400 font-normal' }}">
                                {{ $preselectedCabang ? $preselectedCabang->name : '-- Klik di sini untuk Memilih Cabang MTA (Tersedia Pencarian) --' }}
                            </span>
                        </div>
                        <i class="bi bi-chevron-down text-slate-400 text-xs ml-2 transition-transform duration-200 flex-shrink-0" id="cabang_dropdown_arrow"></i>
                    </button>

                    <!-- Dropdown Panel with Integrated Search Box -->
                    <div id="cabang_dropdown_panel" class="hidden absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-100">
                        <div class="p-3 bg-slate-50 border-b border-slate-200">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 text-xs">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="cabang_search_input" placeholder="Cari nama cabang (contoh: Masaran, Gemolong, Tanon, Sragen Kota)..." autocomplete="off" class="w-full py-2.5 pl-10 pr-4 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-xs text-slate-800 placeholder:text-slate-400 font-medium">
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

                <!-- 2. NAMA LENGKAP DENGAN AUTOCOMPLETE SUGGESTION >= 4 HURUF -->
                <div class="space-y-1.5 text-xs relative" id="nama_wrapper">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-800 uppercase tracking-wider text-[11px]">
                            2. Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-500">Pencarian sugesti saat ketik &ge; 4 huruf</span>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="bi bi-person text-base"></i>
                        </div>
                        <input type="text" 
                               name="name" 
                               id="input_name" 
                               value="{{ old('name') }}" 
                               placeholder="{{ $preselectedCabang ? 'Ketik minimal 4 huruf nama Anda...' : 'Pilih Cabang terlebih dahulu...' }}" 
                               {{ $preselectedCabang ? '' : 'disabled' }} 
                               required 
                               autocomplete="off" 
                               class="w-full py-3.5 pl-10 pr-10 rounded-2xl border border-slate-300 {{ $preselectedCabang ? 'bg-white' : 'bg-slate-100' }} focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-semibold text-slate-800 transition shadow-2xs">

                        <!-- Search Loading Spinner -->
                        <div id="search_spinner" class="hidden absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="animate-spin h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5" id="name_helper_text">
                        <i class="bi bi-info-circle text-slate-400"></i>
                        <span>{{ $preselectedCabang ? 'Ketik minimal 4 huruf untuk menampilkan sugesti data pemuda & warga MTA di cabang ini.' : 'Pilih Cabang MTA terlebih dahulu sebelum mengetik nama.' }}</span>
                    </p>

                    <!-- SELECTED DATA CONFIRMATION BOX -->
                    <div id="selected_indicator_box" class="hidden mt-2 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950 text-xs flex items-center justify-between gap-3 animate-in fade-in">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 text-sm">
                                <i class="bi bi-check-lg"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <strong id="selected_name_display" class="font-bold text-emerald-950 truncate"></strong>
                                    <span id="selected_badge_display" class="inline-flex items-center px-2 py-0.2 rounded text-[10px] font-bold bg-emerald-200 text-emerald-900"></span>
                                </div>
                                <div class="text-[11px] text-emerald-800 mt-0.5" id="selected_note_display">
                                    Nama terpilih. Silakan masukkan tanggal lahir Anda di bawah ini secara manual untuk verifikasi identitas.
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="resetSelectedData()" class="px-2.5 py-1 rounded-xl bg-white border border-emerald-300 hover:bg-emerald-100 text-emerald-900 font-bold text-[11px] transition flex-shrink-0 flex items-center gap-1 shadow-2xs">
                            <i class="bi bi-x-circle text-rose-500"></i>
                            <span>Batal / Ganti</span>
                        </button>
                    </div>

                    <!-- Autocomplete Dropdown List -->
                    <div id="autocomplete_dropdown" class="hidden absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden max-h-80 overflow-y-auto divide-y divide-slate-100">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- 3. TANGGAL LAHIR (TANGGAL, BULAN, TAHUN MAKSIMAL 40 TAHUN) -->
                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-800 uppercase tracking-wider text-[11px]">
                            3. Tanggal Lahir <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-500">Pilih Tanggal, Bulan, dan Tahun</span>
                    </div>

                    @php
                        $oldBirthDate = old('birth_date', '');
                        $initDay   = '';
                        $initMonth = '';
                        $initYear  = '';
                        if (!empty($oldBirthDate)) {
                            $parts = explode('-', $oldBirthDate);
                            if (count($parts) === 3) {
                                $initYear  = $parts[0];
                                $initMonth = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                                $initDay   = str_pad($parts[2], 2, '0', STR_PAD_LEFT);
                            }
                        }
                        $currentYear = (int) date('Y');
                        $minYear     = $currentYear - 40;
                        $monthList   = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                    @endphp

                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        <!-- TANGGAL -->
                        <div>
                            <label for="birth_day" class="block text-[11px] font-semibold text-slate-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="birth_day" class="w-full py-3.5 px-3 rounded-2xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-xs sm:text-sm font-semibold text-slate-800 shadow-2xs appearance-none cursor-pointer">
                                    <option value="">-- Tgl --</option>
                                    @for($d = 1; $d <= 31; $d++)
                                        @php $dVal = sprintf('%02d', $d); @endphp
                                        <option value="{{ $dVal }}" {{ $initDay === $dVal ? 'selected' : '' }}>{{ $d }}</option>
                                    @endfor
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- BULAN -->
                        <div>
                            <label for="birth_month" class="block text-[11px] font-semibold text-slate-600 mb-1">Bulan <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="birth_month" class="w-full py-3.5 px-2.5 sm:px-3 rounded-2xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-xs sm:text-sm font-semibold text-slate-800 shadow-2xs appearance-none cursor-pointer">
                                    <option value="">-- Bulan --</option>
                                    @foreach($monthList as $mNum => $mName)
                                        <option value="{{ $mNum }}" {{ $initMonth === $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- TAHUN (Maksimal 40 tahun dari sekarang) -->
                        <div>
                            <label for="birth_year" class="block text-[11px] font-semibold text-slate-600 mb-1">Tahun <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="birth_year" class="w-full py-3.5 px-3 rounded-2xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-xs sm:text-sm font-semibold text-slate-800 shadow-2xs appearance-none cursor-pointer">
                                    <option value="">-- Tahun --</option>
                                    @for($y = $currentYear; $y >= $minYear; $y--)
                                        <option value="{{ $y }}" {{ (string)$initYear === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden input to submit birth_date as YYYY-MM-DD -->
                    <input type="hidden" name="birth_date" id="input_birth_date" value="{{ $oldBirthDate }}" required>

                    <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5">
                        <i class="bi bi-shield-lock text-slate-400"></i>
                        <span>Pilih tanggal, bulan, dan tahun kelahiran secara manual untuk verifikasi kecocokan identitas.</span>
                    </p>
                </div>

                <!-- 4. SUBMIT LOGIN BUTTON -->
                <div class="pt-2">
                    <button type="submit" id="btn_submit_auth" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-700 hover:to-rose-800 text-white font-bold text-sm sm:text-base shadow-lg shadow-red-500/25 transition duration-200 flex items-center justify-center gap-2 group cursor-pointer">
                        <i class="bi bi-box-arrow-in-right text-lg transition-transform group-hover:translate-x-1"></i>
                        <span id="btn_submit_text">Masuk ke Formulir Pendataan</span>
                    </button>
                    <div class="text-center mt-3">
                        <span class="text-[11px] text-slate-500">
                            <i class="bi bi-shield-check text-emerald-600 mr-1"></i> Data Anda aman dan hanya digunakan untuk kepentingan pengkaderan Pemuda MTA Sragen.
                        </span>
                    </div>
                </div>

            </form>
        </div>

        <!-- HELP / CONTACT CARD -->
        <div class="mt-6 p-4 rounded-2xl bg-white border border-slate-200 text-xs text-slate-600 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <i class="bi bi-question-circle-fill text-red-600 text-lg"></i>
                <span>Mengalami kendala saat verifikasi nama atau cabang?</span>
            </div>
            <a href="https://wa.me/6281234567890?text={{ rawurlencode('Assalamu\'alaikum, saya mengalami kendala saat verifikasi pendataan pemuda MTA Sragen.') }}" target="_blank" class="inline-flex items-center gap-1.5 text-red-600 hover:text-red-700 font-bold transition">
                <i class="bi bi-whatsapp text-emerald-600"></i>
                <span>Hubungi Bantuan WA</span>
                <i class="bi bi-arrow-right text-xs"></i>
            </a>
        </div>

    </div>
</div>

<!-- JAVASCRIPT LOGIC -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Elements
        const cabWrapper       = document.getElementById('cabang_wrapper');
        const cabBtn           = document.getElementById('cabang_dropdown_btn');
        const cabArrow         = document.getElementById('cabang_dropdown_arrow');
        const cabPanel         = document.getElementById('cabang_dropdown_panel');
        const cabSearchInput   = document.getElementById('cabang_search_input');
        const cabOptions       = document.querySelectorAll('.cabang-option-item');
        const cabNoMatch       = document.getElementById('cabang_no_match');
        const cabInputHidden   = document.getElementById('public_cabang_id');
        const cabLabel         = document.getElementById('cabang_selected_label');

        const inputName        = document.getElementById('input_name');
        const inputBirthDate   = document.getElementById('input_birth_date');
        const birthDaySelect   = document.getElementById('birth_day');
        const birthMonthSelect = document.getElementById('birth_month');
        const birthYearSelect  = document.getElementById('birth_year');

        const nameHelper       = document.getElementById('name_helper_text');
        const searchSpinner    = document.getElementById('search_spinner');
        const dropdownList     = document.getElementById('autocomplete_dropdown');

        const selectedIdInput  = document.getElementById('selected_id');
        const selectedUuidInput= document.getElementById('selected_uuid');
        const selectedSrcInput = document.getElementById('selected_source');

        const selectedBox      = document.getElementById('selected_indicator_box');
        const selectedNameDisp = document.getElementById('selected_name_display');
        const selectedBadgeDisp= document.getElementById('selected_badge_display');
        const selectedNoteDisp = document.getElementById('selected_note_display');

        const btnSubmit        = document.getElementById('btn_submit_auth');
        const btnSubmitText    = document.getElementById('btn_submit_text');
        const authForm         = document.getElementById('authForm');

        let searchDebounceTimer = null;

        // 1. SEARCHABLE CABANG DROPDOWN TOGGLE
        if (cabBtn && cabPanel) {
            cabBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isHidden = cabPanel.classList.contains('hidden');
                if (isHidden) {
                    openCabangDropdown();
                } else {
                    closeCabangDropdown();
                }
            });

            document.addEventListener('click', function (e) {
                if (!cabWrapper.contains(e.target)) {
                    closeCabangDropdown();
                }
            });
        }

        function openCabangDropdown() {
            cabPanel.classList.remove('hidden');
            cabArrow.classList.add('rotate-180');
            cabSearchInput.value = '';
            filterCabang('');
            setTimeout(() => cabSearchInput.focus(), 50);
        }

        function closeCabangDropdown() {
            cabPanel.classList.add('hidden');
            cabArrow.classList.remove('rotate-180');
        }

        if (cabSearchInput) {
            cabSearchInput.addEventListener('input', function () {
                filterCabang(this.value.trim());
            });
        }

        function filterCabang(term) {
            const query = term.toLowerCase();
            let matchCount = 0;

            cabOptions.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                const code = (item.getAttribute('data-code') || '').toLowerCase();
                if (name.includes(query) || code.includes(query)) {
                    item.classList.remove('hidden');
                    matchCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            if (matchCount === 0) {
                cabNoMatch.classList.remove('hidden');
            } else {
                cabNoMatch.classList.add('hidden');
            }
        }

        // CABANG ITEM CLICK
        cabOptions.forEach(item => {
            item.addEventListener('click', function () {
                const id   = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                cabInputHidden.value = id;
                if (cabLabel) {
                    cabLabel.textContent = name;
                    cabLabel.className = 'truncate text-slate-900 font-bold';
                }

                closeCabangDropdown();
                onCabangChanged();
            });
        });

        function onCabangChanged() {
            const cabangId = cabInputHidden.value;
            if (cabangId) {
                inputName.disabled = false;
                inputName.classList.remove('bg-slate-100');
                inputName.classList.add('bg-white');
                inputName.placeholder = 'Ketik minimal 4 huruf nama Anda...';
                nameHelper.innerHTML = '<i class="bi bi-search text-slate-400"></i><span>Ketik minimal 4 huruf nama untuk menampilkan sugesti data pemuda &amp; warga MTA di cabang ini.</span>';
                inputName.focus();
            } else {
                inputName.disabled = true;
                inputName.classList.remove('bg-white');
                inputName.classList.add('bg-slate-100');
                inputName.placeholder = 'Pilih Cabang terlebih dahulu...';
                nameHelper.innerHTML = '<i class="bi bi-info-circle text-slate-400"></i><span>Pilih Cabang MTA terlebih dahulu sebelum mengetik nama.</span>';
                dropdownList.classList.add('hidden');
                resetSelectedData();
            }
        }

        // 2. AUTOCOMPLETE SUGGESTION >= 4 HURUF
        if (inputName) {
            inputName.addEventListener('input', function () {
                const cabangId = cabInputHidden.value;
                const query    = this.value.trim();

                clearTimeout(searchDebounceTimer);

                // Jika pengguna mengubah ketikan setelah memilih, reset status terpilih
                if (selectedIdInput.value || selectedUuidInput.value) {
                    resetSelectedData(false);
                }

                if (!cabangId) {
                    dropdownList.classList.add('hidden');
                    return;
                }

                // Sesuai requirement: sugesti muncul saat mengetik minimal 4 huruf
                if (query.length < 4) {
                    dropdownList.classList.add('hidden');
                    return;
                }

                searchDebounceTimer = setTimeout(() => {
                    performSearch(cabangId, query);
                }, 250);
            });

            // Close dropdown when click outside
            document.addEventListener('click', function (e) {
                if (!inputName.contains(e.target) && !dropdownList.contains(e.target)) {
                    dropdownList.classList.add('hidden');
                }
            });
        }

        function resolveFetchUrl(rawUrl) {
            if (window.location.protocol === 'https:' && rawUrl.startsWith('http:')) {
                return rawUrl.replace(/^http:/, 'https:');
            }
            return rawUrl;
        }

        function performSearch(cabangId, query) {
            searchSpinner.classList.remove('hidden');

            const searchUrl = resolveFetchUrl(`{{ route('pendataan.search-nama') }}?cabang_id=${cabangId}&q=${encodeURIComponent(query)}`);

            fetch(searchUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(response => {
                searchSpinner.classList.add('hidden');
                const items = response.data || [];
                renderDropdown(items, query);
            })
            .catch(err => {
                console.error('[Autocomplete Error]:', err);
                searchSpinner.classList.add('hidden');
                renderDropdown([], query);
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

                    const isFemale = (p.gender === 'P');
                    const genderCode = isFemale ? 'P' : 'L';
                    const genderText = isFemale ? 'Perempuan (P)' : 'Laki-laki (L)';
                    const genderBadgeClass = isFemale
                        ? 'bg-rose-100 text-rose-700 border-rose-200'
                        : 'bg-blue-100 text-blue-700 border-blue-200';
                    const avatarBg = isFemale
                        ? 'bg-rose-50 text-rose-600 group-hover:bg-rose-100'
                        : 'bg-blue-50 text-blue-600 group-hover:bg-blue-100';

                    const ageText = (p.age !== null && p.age !== undefined && p.age !== '')
                        ? `${escapeHtml(p.age_text || (p.age + ' tahun'))}`
                        : 'Umur belum tercatat';

                    item.innerHTML = `
                        <div class="flex items-center gap-3 min-w-0 pr-2">
                            <span class="w-8 h-8 rounded-xl ${avatarBg} flex items-center justify-center flex-shrink-0 transition text-xs font-black">
                                ${genderCode}
                            </span>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 group-hover:text-red-700 text-sm truncate flex items-center gap-1.5">
                                    <span>${escapeHtml(p.name)}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black border ${genderBadgeClass}">
                                        ${genderCode}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 font-medium text-slate-600">
                                        <i class="bi bi-hourglass-split text-red-500 text-[10px]"></i>
                                        <span>${ageText}</span>
                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-[10px] font-semibold ${isFemale ? 'text-rose-600' : 'text-blue-600'}">
                                        ${genderText}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white group-hover:bg-red-600 group-hover:text-white text-xs font-bold text-slate-600 border border-slate-200 group-hover:border-red-600 transition shadow-2xs flex-shrink-0">
                            <span>Pilih</span>
                            <i class="bi bi-arrow-right"></i>
                        </span>
                    `;

                    item.addEventListener('click', () => {
                        selectSuggestion(p);
                    });

                    dropdownList.appendChild(item);
                });

                // Opsi pendaftaran baru jika bukan yang di daftar
                const newOption = document.createElement('div');
                newOption.className = 'px-4 py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold cursor-pointer border-t border-slate-200 flex items-center justify-between';
                newOption.innerHTML = `
                    <div class="flex items-center gap-2">
                        <i class="bi bi-plus-circle-fill text-red-600 text-sm"></i>
                        <span>Bukan Anda? Tetap daftar sebagai <strong>Pemuda Baru</strong> dengan nama ini</span>
                    </div>
                    <span class="text-red-600 font-bold text-xs">Lanjutkan &rarr;</span>
                `;
                newOption.addEventListener('click', () => {
                    selectAsNew(query);
                });
                dropdownList.appendChild(newOption);

            } else {
                const noMatch = document.createElement('div');
                noMatch.className = 'p-5 text-center text-xs text-slate-500';
                noMatch.innerHTML = `
                    <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                        <i class="bi bi-person-x"></i>
                    </div>
                    <div class="font-bold text-slate-800 text-sm mb-1">Belum Ada di Basis Data Cabang Ini</div>
                    <p class="text-slate-500 text-xs mb-3 max-w-sm mx-auto">Nama "<strong>${escapeHtml(query)}</strong>" belum ada di data pemuda maupun warga MTA cabang ini. Anda akan didaftarkan sebagai <strong>Kader Pemuda Baru</strong>.</p>
                    <button type="button" class="px-4 py-2 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition shadow-sm inline-flex items-center gap-1.5 text-xs">
                        <i class="bi bi-check2"></i>
                        <span>Gunakan Nama Ini &amp; Masukkan Tanggal Lahir</span>
                    </button>
                `;
                noMatch.querySelector('button').addEventListener('click', () => {
                    selectAsNew(query);
                });
                dropdownList.appendChild(noMatch);
            }

            dropdownList.classList.remove('hidden');
        }

        // Helper Sinkronisasi 3 Dropdown Tanggal Lahir (Tanggal, Bulan, Tahun)
        function updateBirthDateFromDropdowns() {
            adjustDaysInMonth();
            const d = birthDaySelect.value;
            const m = birthMonthSelect.value;
            const y = birthYearSelect.value;

            if (d && m && y) {
                inputBirthDate.value = `${y}-${m}-${d}`;
            } else {
                inputBirthDate.value = '';
            }
        }

        function adjustDaysInMonth() {
            if (!birthMonthSelect || !birthYearSelect || !birthDaySelect) return;
            const m = parseInt(birthMonthSelect.value, 10);
            const y = parseInt(birthYearSelect.value, 10);
            if (!m) return;

            const year = y || 2024;
            const daysInMonth = new Date(year, m, 0).getDate();

            Array.from(birthDaySelect.options).forEach(opt => {
                if (!opt.value) return;
                const d = parseInt(opt.value, 10);
                if (d > daysInMonth) {
                    opt.hidden = true;
                    opt.disabled = true;
                } else {
                    opt.hidden = false;
                    opt.disabled = false;
                }
            });

            if (parseInt(birthDaySelect.value, 10) > daysInMonth) {
                birthDaySelect.value = String(daysInMonth).padStart(2, '0');
            }
        }

        function syncDropdownsFromBirthDate(dateStr) {
            if (!birthDaySelect || !birthMonthSelect || !birthYearSelect) return;
            if (!dateStr) {
                birthDaySelect.value   = '';
                birthMonthSelect.value = '';
                birthYearSelect.value  = '';
                adjustDaysInMonth();
                return;
            }

            const parts = dateStr.split('-');
            if (parts.length === 3) {
                const y = parts[0];
                const m = parts[1].padStart(2, '0');
                const d = parts[2].padStart(2, '0');

                if (birthYearSelect.querySelector(`option[value="${y}"]`)) {
                    birthYearSelect.value = y;
                }
                if (birthMonthSelect.querySelector(`option[value="${m}"]`)) {
                    birthMonthSelect.value = m;
                }
                adjustDaysInMonth();
                if (birthDaySelect.querySelector(`option[value="${d}"]`)) {
                    birthDaySelect.value = d;
                }
            }
        }

        if (birthDaySelect && birthMonthSelect && birthYearSelect) {
            birthDaySelect.addEventListener('change', updateBirthDateFromDropdowns);
            birthMonthSelect.addEventListener('change', updateBirthDateFromDropdowns);
            birthYearSelect.addEventListener('change', updateBirthDateFromDropdowns);

            // Inisialisasi awal jika ada nilai lama
            if (inputBirthDate.value) {
                syncDropdownsFromBirthDate(inputBirthDate.value);
            }
        }

        // 3. AKSI KETIKA MEMILIH DATA DARI SUGESTI
        function selectSuggestion(p) {
            dropdownList.classList.add('hidden');
            inputName.value = p.name;

            // PENTING: Tanggal lahir wajib diinput manual oleh pengguna (tidak diisi otomatis)
            inputBirthDate.value = '';
            syncDropdownsFromBirthDate('');

            const isWarga = (p.source === 'warga_mta');

            if (isWarga) {
                selectedIdInput.value   = '';
                selectedUuidInput.value = p.uuid || '';
                selectedSrcInput.value  = 'warga_mta';

                selectedBadgeDisp.className   = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-200 text-sky-900';
                selectedBadgeDisp.innerHTML   = '<i class="bi bi-patch-check-fill mr-1"></i> Warga MTA Pusat';
                selectedNoteDisp.textContent  = 'Nama terpilih. Silakan pilih tanggal, bulan, dan tahun lahir Anda di bawah ini secara manual untuk verifikasi identitas.';
                btnSubmitText.textContent     = 'Lanjutkan & Sinkronkan Data';
            } else {
                selectedIdInput.value   = p.id || '';
                selectedUuidInput.value = p.uuid || '';
                selectedSrcInput.value  = 'pemuda';

                selectedBadgeDisp.className   = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 text-emerald-900';
                selectedBadgeDisp.innerHTML   = '<i class="bi bi-person-check-fill mr-1"></i> Data Pemuda Terdaftar';
                selectedNoteDisp.textContent  = 'Nama terpilih. Silakan pilih tanggal, bulan, dan tahun lahir Anda di bawah ini secara manual untuk verifikasi identitas.';
                btnSubmitText.textContent     = 'Lanjutkan & Perbarui Data Pemuda';
            }

            const genderBadgeText = p.gender ? ` [${p.gender}]` : '';
            const ageLabel = (p.age !== null && p.age !== undefined && p.age !== '') ? ` (${p.age} th)` : '';
            selectedNameDisp.textContent = p.name + genderBadgeText + ageLabel;
            selectedBox.classList.remove('hidden');

            // Beri fokus ke dropdown tanggal lahir agar user menginputkannya secara manual
            if (birthDaySelect) {
                birthDaySelect.focus();
            }
        }

        function selectAsNew(query) {
            dropdownList.classList.add('hidden');
            inputName.value = query;
            resetSelectedData(false);
            btnSubmitText.textContent = 'Lanjutkan & Daftar Pemuda Baru';
            if (birthDaySelect) {
                birthDaySelect.focus();
            }
        }

        window.resetSelectedData = function (clearName = true) {
            selectedIdInput.value   = '';
            selectedUuidInput.value = '';
            selectedSrcInput.value  = '';
            selectedBox.classList.add('hidden');
            btnSubmitText.textContent = 'Masuk ke Formulir Pendataan';
            if (clearName) {
                inputName.value = '';
                inputBirthDate.value = '';
                syncDropdownsFromBirthDate('');
                inputName.focus();
            }
        };

        // Form submit validation & spinner
        authForm.addEventListener('submit', function (e) {
            const cabangId  = cabInputHidden.value;
            const nameVal   = inputName.value.trim();
            const birthDate = inputBirthDate.value;

            if (!cabangId) {
                e.preventDefault();
                alert('Silakan pilih Cabang MTA tempat mengaji Anda terlebih dahulu.');
                openCabangDropdown();
                return;
            }

            if (nameVal.length < 2) {
                e.preventDefault();
                alert('Silakan masukkan nama lengkap Anda.');
                inputName.focus();
                return;
            }

            if (!birthDate) {
                e.preventDefault();
                alert('Silakan pilih tanggal, bulan, dan tahun lahir Anda secara lengkap.');
                if (!birthDaySelect.value) {
                    birthDaySelect.focus();
                } else if (!birthMonthSelect.value) {
                    birthMonthSelect.focus();
                } else {
                    birthYearSelect.focus();
                }
                return;
            }

            btnSubmit.disabled = true;
            btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
            btnSubmit.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Memverifikasi Identitas...</span>
            `;
        });

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    });
</script>
@endsection
