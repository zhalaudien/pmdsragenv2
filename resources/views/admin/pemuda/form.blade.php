@extends('admin.layouts.main')

@section('title', $title ?? ($isEdit ? 'Edit Data Pemuda' : 'Tambah Data Pemuda'))

@section('content')

@php
    $actionUrl = $isEdit ? route('admin.pemuda.update', $pemuda->id) : route('admin.pemuda.simpan');
    $userRole  = session('role') ?? auth()->user()?->role?->name;

    $selectedSkills = $isEdit && isset($pemuda->skills) ? $pemuda->skills->pluck('id')->toArray() : [];
    $selectedInterests = $isEdit && isset($pemuda->interests) ? $pemuda->interests->pluck('id')->toArray() : [];
    $selectedOrgs = $isEdit && isset($pemuda->organisasi) ? $pemuda->organisasi->pluck('organization_name')->toArray() : [];
@endphp

<!-- BACK BUTTON -->
<div class="mb-4">
    <a href="{{ route('admin.pemuda.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-600 font-bold text-xs transition border border-slate-200">
        <i class="bi bi-arrow-left"></i>
        <span>Kembali ke Daftar Pemuda</span>
    </a>
</div>

<!-- MAIN FORM CARD -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi {{ $isEdit ? 'bi-pencil-square text-amber-500' : 'bi-person-plus-fill text-red-600' }}"></i>
                <span>{{ $title }}</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi formulir di bawah ini dengan teliti dan benar.</p>
        </div>
    </div>

    <form action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-8">
        @csrf

        <!-- 1. WILAYAH & CABANG -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">1</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Wilayah &amp; Cabang Keanggotaan</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                @if($userRole === 'admin_cabang')
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Wilayah</label>
                        <input type="text" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-600" value="{{ session('wilayah_name') }}" readonly>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Cabang <span class="text-red-500">*</span></label>
                        <input type="text" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-600" value="{{ session('cabang_name') }}" readonly>
                        <input type="hidden" name="cabang_id" value="{{ session('cabang_id') }}">
                    </div>
                @elseif(in_array($userRole, ['admin_wilayah', 'admin_wilayah_pemuda'], true))
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Wilayah</label>
                        <input type="text" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-600" value="{{ session('wilayah_name') }}" readonly>
                    </div>
                    <div>
                        <label for="cabang_id" class="block font-bold text-slate-700 uppercase mb-1">Pilih Cabang <span class="text-red-500">*</span></label>
                        <select name="cabang_id" id="cabang_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabangList as $c)
                                <option value="{{ $c->id }}" {{ old('cabang_id', $pemuda->cabang_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label for="wilayah_id" class="block font-bold text-slate-700 uppercase mb-1">Pilih Wilayah <span class="text-red-500">*</span></label>
                        <select id="wilayah_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                            <option value="">-- Pilih Wilayah --</option>
                            @foreach($wilayahList as $w)
                                <option value="{{ $w->id }}" {{ ($pemuda->cabang->wilayah_id ?? '') == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }} ({{ $w->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="cabang_id" class="block font-bold text-slate-700 uppercase mb-1">Pilih Cabang <span class="text-red-500">*</span></label>
                        <select name="cabang_id" id="cabang_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                            <option value="">-- Pilih Wilayah Terlebih Dahulu --</option>
                            @foreach($cabangList as $c)
                                <option value="{{ $c->id }}" {{ old('cabang_id', $pemuda->cabang_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. DATA PRIBADI -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">2</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Biodata Diri Pemuda</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                <!-- Nama -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $pemuda->name ?? '') }}" placeholder="Sesuai KTP" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                </div>

                <!-- Gender -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                        <option value="L" {{ old('gender', $pemuda->gender ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender', $pemuda->gender ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <!-- Status Pernikahan -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Pernikahan <span class="text-red-500">*</span></label>
                    <select name="marital_status" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                        <option value="belum_menikah" {{ old('marital_status', $pemuda->marital_status ?? '') === 'belum_menikah' ? 'selected' : '' }}>Belum Menikah (Lajang)</option>
                        <option value="sudah_menikah" {{ old('marital_status', $pemuda->marital_status ?? '') === 'sudah_menikah' ? 'selected' : '' }}>Sudah Menikah</option>
                        <option value="duda" {{ old('marital_status', $pemuda->marital_status ?? '') === 'duda' ? 'selected' : '' }}>Duda</option>
                        <option value="janda" {{ old('marital_status', $pemuda->marital_status ?? '') === 'janda' ? 'selected' : '' }}>Janda</option>
                    </select>
                </div>

                <!-- Golongan Darah -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Golongan Darah</label>
                    <select name="blood_type" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        <option value="tidak_tahu" {{ old('blood_type', $pemuda->blood_type ?? '') === 'tidak_tahu' ? 'selected' : '' }}>Tidak Tahu / Belum Cek</option>
                        <option value="A" {{ old('blood_type', $pemuda->blood_type ?? '') === 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ old('blood_type', $pemuda->blood_type ?? '') === 'B' ? 'selected' : '' }}>B</option>
                        <option value="AB" {{ old('blood_type', $pemuda->blood_type ?? '') === 'AB' ? 'selected' : '' }}>AB</option>
                        <option value="O" {{ old('blood_type', $pemuda->blood_type ?? '') === 'O' ? 'selected' : '' }}>O</option>
                    </select>
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tempat Lahir <span class="text-red-500">*</span></label>
                    <input type="text" name="birth_place" value="{{ old('birth_place', $pemuda->birth_place ?? '') }}" placeholder="Kota / Kabupaten" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', isset($pemuda->birth_date) ? \Carbon\Carbon::parse($pemuda->birth_date)->format('Y-m-d') : '') }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                </div>

                <!-- WhatsApp -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">No. WhatsApp / HP <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone', $pemuda->phone ?? '') }}" placeholder="08xxxxxxxxxx" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                </div>

                <!-- Email -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $pemuda->email ?? '') }}" placeholder="alamat@email.com" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <!-- Foto Profil -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Foto Pemuda (JPG, PNG max 2MB)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-600 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                    @if(!empty($pemuda->foto))
                        <div class="mt-2 flex items-center gap-2">
                            <img src="{{ asset('uploads/pemuda/' . $pemuda->foto) }}" alt="Foto" class="w-12 h-14 object-cover rounded-lg border">
                            <span class="text-[11px] text-slate-500">Foto saat ini terpasang</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. ALAMAT DOMISILI -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">3</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Alamat Domisili</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <!-- Kecamatan -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kecamatan <span class="text-red-500">*</span></label>
                    <select name="district_id" id="district_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                        <option value="">-- Pilih Kecamatan --</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}" {{ old('district_id', $pemuda->alamat->district_id ?? '') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Desa / Kelurahan -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Desa / Kelurahan <span class="text-red-500">*</span></label>
                    <select name="village_id" id="village_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                        <option value="">-- Pilih Desa/Kelurahan --</option>
                        @if(isset($villages))
                            @foreach($villages as $v)
                                <option value="{{ $v->id }}" {{ old('village_id', $pemuda->alamat->village_id ?? '') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Dusun -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Dusun / Kampung</label>
                    <input type="text" name="dusun" value="{{ old('dusun', $pemuda->alamat->dusun ?? '') }}" placeholder="Nama Dukuh / Dusun" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <!-- RT / RW -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">RT</label>
                        <input type="text" name="rt" value="{{ old('rt', $pemuda->alamat->rt ?? '') }}" placeholder="01" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">RW</label>
                        <input type="text" name="rw" value="{{ old('rw', $pemuda->alamat->rw ?? '') }}" placeholder="02" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>

                <!-- Detail Alamat -->
                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                    <textarea name="address_detail" rows="2" placeholder="Nama Jalan, nomor rumah, atau patokan lokasi" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>{{ old('address_detail', $pemuda->alamat->address_detail ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 4. PENDIDIKAN -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">4</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pendidikan</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jenjang Pendidikan <span class="text-red-500">*</span></label>
                    <select name="education_level_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                        @foreach($educationLevels as $el)
                            <option value="{{ $el->id }}" {{ old('education_level_id', $pemuda->pendidikan->education_level_id ?? '') == $el->id ? 'selected' : '' }}>{{ $el->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lembaga / Sekolah <span class="text-red-500">*</span></label>
                    <input type="text" name="school_name" value="{{ old('school_name', $pemuda->pendidikan->school_name ?? '') }}" placeholder="Nama Sekolah / Perguruan Tinggi" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jurusan / Program Studi</label>
                    <input type="text" name="major" value="{{ old('major', $pemuda->pendidikan->major ?? '') }}" placeholder="IPA / Teknik Mesin / dll" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Pendidikan <span class="text-red-500">*</span></label>
                    <select name="education_status" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                        <option value="lulus" {{ old('education_status', $pemuda->pendidikan->education_status ?? '') === 'lulus' ? 'selected' : '' }}>Sudah Lulus</option>
                        <option value="sedang_sekolah" {{ old('education_status', $pemuda->pendidikan->education_status ?? '') === 'sedang_sekolah' ? 'selected' : '' }}>Sedang Menempuh Pendidikan</option>
                        <option value="putus_sekolah" {{ old('education_status', $pemuda->pendidikan->education_status ?? '') === 'putus_sekolah' ? 'selected' : '' }}>Putus Sekolah</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 5. PEKERJAAN -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">5</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pekerjaan &amp; Wirausaha</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Pekerjaan <span class="text-red-500">*</span></label>
                    <select name="job_status_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" required>
                        @foreach($jobStatuses as $js)
                            <option value="{{ $js->id }}" {{ old('job_status_id', $pemuda->pekerjaan->job_status_id ?? '') == $js->id ? 'selected' : '' }}>{{ $js->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Profesi / Jabatan</label>
                    <input type="text" name="job_title" value="{{ old('job_title', $pemuda->pekerjaan->job_title ?? '') }}" placeholder="Staff / Guru / Pedagang" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Tempat Kerja / Instansi</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $pemuda->pekerjaan->company_name ?? '') }}" placeholder="PT / Toko / Sekolah" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Bidang Usaha (Jika Wirausaha)</label>
                    <input type="text" name="business_field" value="{{ old('business_field', $pemuda->pekerjaan->business_field ?? '') }}" placeholder="Kuliner, Jasa, Konveksi, dll" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- 6. ORGANISASI & ELEMENT DAKWAH -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">6</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Element Dakwah &amp; Organisasi yang Diikuti</h3>
            </div>

            @php
                $defaultOrgs = ['SATGAS', 'BANKOM', 'SAR MTA', 'TIM PARKIR', 'ELFATA', 'TIM IKHROM'];
                $allOrgOptions = array_values(array_unique(array_map('strtoupper', array_merge($defaultOrgs, $customOrgs ?? [], $selectedOrgs ?? []))));
                $selectedOrgsUpper = array_map('strtoupper', $selectedOrgs ?? []);
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 text-xs">
                @foreach($allOrgOptions as $org)
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="organizations[]" value="{{ strtoupper($org) }}" {{ in_array(strtoupper($org), $selectedOrgsUpper) ? 'checked' : '' }} class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                        <span class="font-semibold text-slate-700 uppercase tracking-wide">{{ strtoupper($org) }}</span>
                        @if(!in_array(strtoupper($org), $defaultOrgs))
                            <span class="text-[9px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-bold ml-auto uppercase">Kustom</span>
                        @endif
                    </label>
                @endforeach
            </div>
            
            <div class="mt-3 flex items-center gap-2 text-xs">
                <div class="relative w-full sm:w-96">
                    <input type="text" name="custom_organization" placeholder="Ketik nama elemen lainnya jika tidak ada di atas..." class="w-full py-2 pl-3 pr-3 text-xs rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-red-500 focus:border-red-500 uppercase placeholder:normal-case">
                </div>
                <span class="text-[11px] text-slate-400">Otomatis tersimpan &amp; terpilih jika diisi</span>
            </div>
        </div>

        <!-- 7. KEAHLIAN & MINAT -->
        <div>
            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">7</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Keahlian &amp; Minat Potensi Diri</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div>
                    <h4 class="font-bold text-slate-700 mb-2">Keahlian / Skill:</h4>
                    <div class="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto p-2 border rounded-xl bg-slate-50">
                        @foreach($skills as $sk)
                            <label class="flex items-center gap-2 p-1.5 hover:bg-white rounded cursor-pointer">
                                <input type="checkbox" name="skills[]" value="{{ $sk->id }}" {{ in_array($sk->id, $selectedSkills) ? 'checked' : '' }} class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                <span>{{ $sk->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-slate-700 mb-2">Minat / Ketertarikan:</h4>
                    <div class="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto p-2 border rounded-xl bg-slate-50">
                        @foreach($interests as $int)
                            <label class="flex items-center gap-2 p-1.5 hover:bg-white rounded cursor-pointer">
                                <input type="checkbox" name="interests[]" value="{{ $int->id }}" {{ in_array($int->id, $selectedInterests) ? 'checked' : '' }} class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                <span>{{ $int->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. STATUS VERIFIKASI & DATA (ADMIN ONLY) -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
            <div class="flex items-center gap-2 pb-2 mb-3 border-b border-slate-200">
                <span class="w-6 h-6 rounded-full bg-slate-800 text-white text-xs font-black flex items-center justify-center">8</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Status Administrasi Data</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Verifikasi</label>
                    <select name="status_verifikasi" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-white focus:ring-red-500 focus:border-red-500">
                        <option value="pending" {{ old('status_verifikasi', $pemuda->status_verifikasi ?? 'pending') === 'pending' ? 'selected' : '' }}>Belum Terverifikasi (Pending)</option>
                        <option value="verified" {{ old('status_verifikasi', $pemuda->status_verifikasi ?? '') === 'verified' ? 'selected' : '' }}>Terverifikasi (Pusat)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Data</label>
                    <select name="status_data" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-white focus:ring-red-500 focus:border-red-500">
                        <option value="active" {{ old('status_data', $pemuda->status_data ?? 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="archived" {{ old('status_data', $pemuda->status_data ?? '') === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SUBMIT BUTTONS -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('admin.pemuda.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data Pemuda' }}</span>
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // AJAX update desa based on kecamatan
        const districtSelect = document.getElementById('district_id');
        const villageSelect  = document.getElementById('village_id');

        if (districtSelect && villageSelect) {
            districtSelect.addEventListener('change', function () {
                const distId = this.value;
                villageSelect.innerHTML = '<option value="">-- Memuat Desa... --</option>';
                if (!distId) {
                    villageSelect.innerHTML = '<option value="">-- Pilih Desa/Kelurahan --</option>';
                    return;
                }

                fetch(`{{ url('admin/ajax/villages') }}/${distId}`)
                    .then(res => res.json())
                    .then(data => {
                        villageSelect.innerHTML = '<option value="">-- Pilih Desa/Kelurahan --</option>';
                        data.forEach(v => {
                            const opt = document.createElement('option');
                            opt.value = v.id;
                            opt.textContent = v.name;
                            villageSelect.appendChild(opt);
                        });
                    })
                    .catch(() => {
                        villageSelect.innerHTML = '<option value="">-- Gagal memuat data --</option>';
                    });
            });
        }

        // AJAX update cabang based on wilayah
        const wilayahSelect = document.getElementById('wilayah_id');
        const cabangSelect  = document.getElementById('cabang_id');

        if (wilayahSelect && cabangSelect) {
            wilayahSelect.addEventListener('change', function () {
                const wId = this.value;
                cabangSelect.innerHTML = '<option value="">-- Memuat Cabang... --</option>';
                if (!wId) {
                    cabangSelect.innerHTML = '<option value="">-- Pilih Wilayah Terlebih Dahulu --</option>';
                    return;
                }

                fetch(`{{ url('admin/ajax/cabang') }}/${wId}`)
                    .then(res => res.json())
                    .then(data => {
                        cabangSelect.innerHTML = '<option value="">-- Pilih Cabang --</option>';
                        data.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = c.name;
                            cabangSelect.appendChild(opt);
                        });
                    })
                    .catch(() => {
                        cabangSelect.innerHTML = '<option value="">-- Gagal memuat cabang --</option>';
                    });
            });
        }
    });
</script>
@endsection
