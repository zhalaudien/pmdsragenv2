@extends('layouts.app')

@section('title', 'Formulir Pendataan Pemuda | Pemuda MTA Perwakilan Sragen')

@section('content')

<div class="py-8 sm:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <!-- TOP BREADCRUMB -->
        <div class="flex items-center justify-between gap-2 mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-600 font-bold text-xs transition border border-slate-200">
                <i class="bi bi-arrow-left"></i>
                <span>Beranda</span>
            </a>
            <div class="text-xs font-semibold text-slate-400">
                Database Resmi Pemuda MTA Sragen
            </div>
        </div>

        <!-- FORM HERO TITLE -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-red-700 text-xs font-bold mb-3">
                <i class="bi bi-patch-check-fill"></i>
                <span>Sistem Registrasi Mandiri Pemuda</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Formulir Pendataan Pemuda</h1>
            <p class="text-xs sm:text-sm text-slate-500 max-w-xl mx-auto mt-2 leading-relaxed">
                Lengkapi profil dan potensi diri Anda untuk pemetaan dakwah, kaderisasi, dan pengabdian pemuda MTA se-Kabupaten Sragen.
            </p>
        </div>

        <!-- STEPPER PROGRESS BAR (DESKTOP) -->
        <div class="hidden sm:grid grid-cols-8 gap-2 mb-8 text-center text-[10px] font-bold">
            @php
                $steps = [
                    1 => 'Data Diri',
                    2 => 'Alamat',
                    3 => 'Cabang',
                    4 => 'Pendidikan',
                    5 => 'Pekerjaan',
                    6 => 'Organisasi',
                    7 => 'Keahlian',
                    8 => 'Kirim',
                ];
            @endphp
            @foreach($steps as $num => $label)
                <div class="step-indicator flex flex-col items-center gap-1.5 cursor-pointer" onclick="goToStep({{ $num }})" id="step-indicator-{{ $num }}">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs transition {{ $num === 1 ? 'bg-red-600 text-white shadow-md' : 'bg-slate-200 text-slate-600' }}" id="step-circle-{{ $num }}">
                        {{ $num }}
                    </div>
                    <span class="text-slate-600 truncate max-w-full">{{ $label }}</span>
                </div>
            @endforeach
        </div>

        <!-- MOBILE PROGRESS -->
        <div class="sm:hidden mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between text-xs">
            <span class="font-bold text-red-600" id="mobileStepText">Langkah 1 dari 8: Data Diri</span>
            <div class="w-24 h-2 bg-slate-100 rounded-full overflow-hidden">
                <div id="mobileProgressBar" class="h-full bg-red-600 rounded-full transition-all duration-300" style="width: 12.5%"></div>
            </div>
        </div>

        <!-- MAIN FORM -->
        <form action="{{ route('pendataan.simpan') }}" method="POST" enctype="multipart/form-data" id="pendataanForm" class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xl space-y-6">
            @csrf

            <!-- STEP 1: DATA PRIBADI -->
            <div class="form-step" id="step-1">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">1</span>
                    <span>Data Diri Pemuda</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="input_name" placeholder="Sesuai KTP / Akta Kelahiran" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">NIK (Nomor Induk Kependudukan)</label>
                        <input type="text" name="nik" id="input_nik" placeholder="16 digit NIK KTP" maxlength="16" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="gender" id="input_gender" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="L">Laki-laki (Pemuda)</option>
                            <option value="P">Perempuan (Pemudi)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Tempat Lahir <span class="text-red-500">*</span></label>
                        <input type="text" name="birth_place" placeholder="Kota / Kabupaten" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                        <input type="date" name="birth_date" id="input_birth_date" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Status Pernikahan <span class="text-red-500">*</span></label>
                        <select name="marital_status" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="belum_menikah">Belum Menikah (Lajang)</option>
                            <option value="sudah_menikah">Sudah Menikah</option>
                            <option value="duda">Duda</option>
                            <option value="janda">Janda</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Golongan Darah</label>
                        <select name="blood_type" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="tidak_tahu">Tidak Tahu / Belum Cek</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O">O</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">No. WhatsApp / HP Aktif <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" placeholder="08xxxxxxxxxx" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Email</label>
                        <input type="email" name="email" placeholder="nama@email.com" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase mb-1">Foto Formal / Bebas Rapi (Opsional)</label>
                        <input type="file" name="foto" accept="image/*" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-600 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                    </div>
                </div>
            </div>

            <!-- STEP 2: ALAMAT DOMISILI -->
            <div class="form-step hidden" id="step-2">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">2</span>
                    <span>Alamat Tempat Tinggal Saat Ini</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Kecamatan di Sragen <span class="text-red-500">*</span></label>
                        <select name="district_id" id="public_district_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="">-- Pilih Kecamatan --</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Desa / Kelurahan <span class="text-red-500">*</span></label>
                        <select name="village_id" id="public_village_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Dukuh / Dusun / Kampung</label>
                        <input type="text" name="dusun" placeholder="Nama Dusun" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">RT</label>
                            <input type="text" name="rt" placeholder="01" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">RW</label>
                            <input type="text" name="rw" placeholder="02" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Lengkap / Patokan Rumah <span class="text-red-500">*</span></label>
                        <textarea name="address_detail" rows="2" placeholder="Nama jalan, gang, nomor rumah atau patokan..." required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm"></textarea>
                    </div>
                </div>
            </div>

            <!-- STEP 3: CABANG KEANGGOTAAN MTA -->
            <div class="form-step hidden" id="step-3">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">3</span>
                    <span>Asal Cabang Binaan MTA</span>
                </h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Wilayah MTA <span class="text-red-500">*</span></label>
                        <select id="public_wilayah_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="">-- Pilih Wilayah --</option>
                            @foreach($wilayahList as $w)
                                <option value="{{ $w['id'] }}">{{ $w['name'] }} ({{ $w['code'] }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Cabang Tempat Mengaji / Domisili <span class="text-red-500">*</span></label>
                        <select name="cabang_id" id="public_cabang_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="">-- Pilih Wilayah Terlebih Dahulu --</option>
                            @foreach($cabangList as $c)
                                <option value="{{ $c->id }}" data-wilayah="{{ $c->wilayah_id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- STEP 4: PENDIDIKAN -->
            <div class="form-step hidden" id="step-4">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">4</span>
                    <span>Pendidikan Terakhir</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Jenjang Pendidikan <span class="text-red-500">*</span></label>
                        <select name="education_level_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                            @foreach($educationLevels as $el)
                                <option value="{{ $el->id }}">{{ $el->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lembaga / Sekolah / Kampus <span class="text-red-500">*</span></label>
                        <input type="text" name="school_name" placeholder="Contoh: SMA N 1 Sragen / UNS" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Jurusan / Program Studi</label>
                        <input type="text" name="major" placeholder="IPA / Teknik Mesin / dll" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Status Kelulusan <span class="text-red-500">*</span></label>
                        <select name="education_status" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="lulus">Sudah Lulus</option>
                            <option value="sedang_sekolah">Sedang Menempuh Pendidikan</option>
                            <option value="putus_sekolah">Putus Sekolah</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- STEP 5: PEKERJAAN -->
            <div class="form-step hidden" id="step-5">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">5</span>
                    <span>Pekerjaan &amp; Aktivitas Ekonomi</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Status Pekerjaan <span class="text-red-500">*</span></label>
                        <select name="job_status_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                            @foreach($jobStatuses as $js)
                                <option value="{{ $js->id }}">{{ $js->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Profesi / Jabatan</label>
                        <input type="text" name="job_title" placeholder="Karyawan, Guru, Pedagang, dll" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Nama Tempat Kerja / Instansi</label>
                        <input type="text" name="company_name" placeholder="Nama Perusahaan / Kantor / Toko" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Bidang Usaha (Jika Wiraswasta)</label>
                        <input type="text" name="business_field" placeholder="Kuliner, Bengkel, Konveksi, dll" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>
                </div>
            </div>

            <!-- STEP 6: ORGANISASI / ELEMENT DAKWAH -->
            <div class="form-step hidden" id="step-6">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">6</span>
                    <span>Element Dakwah &amp; Organisasi yang Diikuti</span>
                </h3>

                <p class="text-xs text-slate-500 mb-3">Pilih satuan tugas atau element pengabdian yang sedang atau pernah Anda ikuti di MTA:</p>

                @php
                    $orgs = ['SATGAS', 'BANKOM', 'IKHROM', 'TIM MEDIS', 'SAR MTA', 'TAPAK SUCI', 'PANAHAN', 'PENGURUS CABANG', 'RELAWAN LAINNYA'];
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                    @foreach($orgs as $org)
                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="organizations[]" value="{{ $org }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                            <span class="font-bold text-slate-800">{{ $org }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- STEP 7: KEAHLIAN & MINAT -->
            <div class="form-step hidden" id="step-7">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">7</span>
                    <span>Keahlian &amp; Minat Potensi Diri</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                    <div>
                        <h4 class="font-bold text-slate-800 mb-2">Bakat / Keahlian yang Dikuasai:</h4>
                        <div class="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto p-3 rounded-2xl bg-slate-50 border border-slate-200">
                            @foreach($skills as $sk)
                                <label class="flex items-center gap-2 p-1 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="skills[]" value="{{ $sk->id }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    <span class="text-slate-700">{{ $sk->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h4 class="font-bold text-slate-800 mb-2">Minat / Bidang yang Ingin Dipelajari:</h4>
                        <div class="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto p-3 rounded-2xl bg-slate-50 border border-slate-200">
                            @foreach($interests as $int)
                                <label class="flex items-center gap-2 p-1 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="interests[]" value="{{ $int->id }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    <span class="text-slate-700">{{ $int->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 8: KONFIRMASI & SUBMIT -->
            <div class="form-step hidden" id="step-8">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">8</span>
                    <span>Konfirmasi &amp; Pengiriman Formulir</span>
                </h3>

                <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-xs text-red-900 mb-4 leading-relaxed">
                    <strong class="block mb-1 font-bold">Pernyataan Kebenaran Data:</strong>
                    Dengan mengirimkan formulir ini, saya menyatakan bahwa seluruh data yang saya isikan adalah benar dan dapat dipertanggungjawabkan untuk keperluan basis data Pemuda MTA Perwakilan Sragen.
                </div>

                <label class="flex items-start gap-2.5 p-3 rounded-2xl border border-slate-200 hover:bg-slate-50 cursor-pointer text-xs">
                    <input type="checkbox" required class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-500">
                    <span class="text-slate-700 font-semibold">Saya telah memeriksa seluruh data di atas dan siap mengirimkan formulir pendataan.</span>
                </label>
            </div>

            <!-- NAVIGATION BUTTONS -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <button type="button" id="btnPrev" onclick="prevStep()" class="hidden px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    <i class="bi bi-arrow-left mr-1"></i> Kembali
                </button>

                <div class="ml-auto flex items-center gap-2">
                    <button type="button" id="btnNext" onclick="nextStep()" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2">
                        <span>Lanjutkan</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                    <button type="submit" id="btnSubmit" class="hidden px-8 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold text-xs transition shadow-lg flex items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Kirim Pendaftaran</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 8;

    function updateStepView() {
        for (let i = 1; i <= totalSteps; i++) {
            const stepEl = document.getElementById(`step-${i}`);
            const circle = document.getElementById(`step-circle-${i}`);
            if (stepEl) {
                if (i === currentStep) {
                    stepEl.classList.remove('hidden');
                } else {
                    stepEl.classList.add('hidden');
                }
            }
            if (circle) {
                if (i === currentStep) {
                    circle.className = 'w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs transition bg-red-600 text-white shadow-md';
                } else if (i < currentStep) {
                    circle.className = 'w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs transition bg-emerald-600 text-white';
                } else {
                    circle.className = 'w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs transition bg-slate-200 text-slate-600';
                }
            }
        }

        // Mobile
        const mobileText = document.getElementById('mobileStepText');
        const mobileBar = document.getElementById('mobileProgressBar');
        if (mobileText) mobileText.textContent = `Langkah ${currentStep} dari ${totalSteps}`;
        if (mobileBar) mobileBar.style.width = `${(currentStep / totalSteps) * 100}%`;

        // Buttons
        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');
        const btnSubmit = document.getElementById('btnSubmit');

        if (currentStep === 1) {
            btnPrev.classList.add('hidden');
        } else {
            btnPrev.classList.remove('hidden');
        }

        if (currentStep === totalSteps) {
            btnNext.classList.add('hidden');
            btnSubmit.classList.remove('hidden');
        } else {
            btnNext.classList.remove('hidden');
            btnSubmit.classList.add('hidden');
        }

        window.scrollTo({ top: 120, behavior: 'smooth' });
    }

    function nextStep() {
        if (currentStep < totalSteps) {
            currentStep++;
            updateStepView();
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepView();
        }
    }

    function goToStep(step) {
        if (step >= 1 && step <= totalSteps) {
            currentStep = step;
            updateStepView();
        }
    }

    // Dynamic village dropdown
    const distSelect = document.getElementById('public_district_id');
    const villSelect = document.getElementById('public_village_id');
    if (distSelect && villSelect) {
        distSelect.addEventListener('change', function () {
            const distId = this.value;
            villSelect.innerHTML = '<option value="">-- Memuat Desa... --</option>';
            if (!distId) return;

            fetch(`{{ url('api/villages') }}/${distId}`)
                .then(res => res.json())
                .then(data => {
                    villSelect.innerHTML = '<option value="">-- Pilih Desa/Kelurahan --</option>';
                    data.forEach(v => {
                        const opt = document.createElement('option');
                        opt.value = v.id;
                        opt.textContent = v.name;
                        villSelect.appendChild(opt);
                    });
                });
        });
    }

    // Dynamic cabang filter based on wilayah
    const wilSelect = document.getElementById('public_wilayah_id');
    const cabSelect = document.getElementById('public_cabang_id');
    if (wilSelect && cabSelect) {
        wilSelect.addEventListener('change', function () {
            const wId = this.value;
            const options = cabSelect.querySelectorAll('option');
            options.forEach(opt => {
                if (!opt.value) return;
                const optWil = opt.getAttribute('data-wilayah');
                if (!wId || optWil === wId) {
                    opt.style.display = 'block';
                } else {
                    opt.style.display = 'none';
                }
            });
            cabSelect.value = '';
        });
    }
</script>
@endsection
