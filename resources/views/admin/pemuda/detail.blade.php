@extends('admin.layouts.main')

@section('title', 'Detail Pemuda: ' . $pemuda->name)

@section('content')

@php
    $isMale = ($pemuda->gender ?? 'L') === 'L';
    $photoUrl = null;
    if (!empty($pemuda->foto) && file_exists(public_path('uploads/pemuda/' . $pemuda->foto))) {
        $photoUrl = asset('uploads/pemuda/' . $pemuda->foto);
    } elseif (!empty($pemuda->mta_foto_url)) {
        $photoUrl = $pemuda->mta_foto_url;
    }
    $age = $pemuda->birth_date ? \Carbon\Carbon::parse($pemuda->birth_date)->age : null;
@endphp

<!-- BACK BUTTON -->
<div class="mb-4">
    <a href="{{ route('admin.pemuda.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-600 font-bold text-xs transition border border-slate-200">
        <i class="bi bi-arrow-left"></i>
        <span>Kembali ke Daftar Pemuda</span>
    </a>
</div>

<!-- PROFILE HERO HEADER CARD -->
<div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm mb-6">
    <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
        <!-- AVATAR / PHOTO -->
        <div class="flex-shrink-0 text-center">
            @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $pemuda->name }}" class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl object-cover shadow-md border-4 {{ $isMale ? 'border-blue-500/20' : 'border-pink-500/20' }}">
            @else
                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-gradient-to-tr {{ $isMale ? 'from-blue-600 to-indigo-600' : 'from-pink-600 to-rose-600' }} text-white font-black text-4xl flex items-center justify-center shadow-md">
                    {{ strtoupper(substr($pemuda->name, 0, 1)) }}
                </div>
            @endif
        </div>

        <!-- MAIN INFO -->
        <div class="flex-1 text-center md:text-left min-w-0">
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mb-2">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $pemuda->name }}</h2>
                @if($pemuda->status_verifikasi === 'verified')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                        <i class="bi bi-patch-check-fill text-emerald-600"></i> Terverifikasi Pusat
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">
                        <i class="bi bi-clock text-amber-600"></i> Belum Terverifikasi
                    </span>
                @endif
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full {{ $isMale ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }} text-[11px] font-bold">
                    <i class="bi {{ $isMale ? 'bi-gender-male' : 'bi-gender-female' }}"></i> {{ $isMale ? 'Laki-laki' : 'Perempuan' }}
                </span>
            </div>

            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-xs text-slate-500 mt-3">
                <div>
                    <span class="text-slate-400">No. Registrasi:</span>
                    <strong class="font-mono text-red-600 font-bold ml-1">{{ $pemuda->registration_number }}</strong>
                </div>
                <div>
                    <span class="text-slate-400">Cabang:</span>
                    <strong class="text-slate-800 ml-1">{{ $pemuda->cabang->name ?? '-' }} ({{ $pemuda->cabang->wilayah->name ?? '-' }})</strong>
                </div>
                <div>
                    <span class="text-slate-400">Terdaftar:</span>
                    <span class="text-slate-700 ml-1">{{ $pemuda->created_at ? $pemuda->created_at->format('d/m/Y H:i') : '-' }} WIB</span>
                </div>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="flex flex-wrap md:flex-col gap-2 flex-shrink-0">
            <a href="{{ route('admin.pemuda.edit', $pemuda->id) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs transition shadow-sm">
                <i class="bi bi-pencil-square"></i>
                <span>Edit Data</span>
            </a>
            <a href="{{ route('admin.pemuda.cetak', $pemuda->id) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition">
                <i class="bi bi-printer"></i>
                <span>Cetak Dokumen</span>
            </a>
            @if($pemuda->status_verifikasi !== 'verified')
                <form action="{{ route('admin.pemuda.verifikasi', $pemuda->id) }}" method="POST" onsubmit="return confirm('Verifikasi data pemuda ini sekarang?')">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm">
                        <i class="bi bi-patch-check-fill"></i>
                        <span>Verifikasi</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- DETAIL SECTIONS (GRID) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- 1. BIODATA DIRI -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-3">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="bi bi-person-lines-fill text-red-600 text-base"></i>
            <span>Biodata Pribadi</span>
        </h3>
        <dl class="divide-y divide-slate-100 text-xs">
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Nama Lengkap</dt>
                <dd class="font-bold text-slate-900">{{ $pemuda->name }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Tempat, Tanggal Lahir</dt>
                <dd class="text-slate-800 font-medium">
                    {{ $pemuda->birth_place ?? '-' }}, {{ $pemuda->birth_date ? \Carbon\Carbon::parse($pemuda->birth_date)->format('d F Y') : '-' }}
                    @if($age) ({{ $age }} tahun) @endif
                </dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Golongan Darah</dt>
                <dd class="font-bold text-red-600">{{ $pemuda->blood_type ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Status Pernikahan</dt>
                <dd class="font-medium text-slate-800">{{ ucfirst(str_replace('_', ' ', $pemuda->marital_status ?? '-')) }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">No. WhatsApp / HP</dt>
                <dd class="font-semibold text-emerald-600">
                    @if($pemuda->phone)
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $pemuda->phone)) }}" target="_blank" class="hover:underline flex items-center gap-1">
                            <i class="bi bi-whatsapp"></i> {{ $pemuda->phone }}
                        </a>
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Email</dt>
                <dd class="text-slate-800">{{ $pemuda->email ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    <!-- 2. ALAMAT LENGKAP -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-3">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="bi bi-geo-alt-fill text-indigo-600 text-base"></i>
            <span>Alamat Domisili</span>
        </h3>
        <dl class="divide-y divide-slate-100 text-xs">
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Kecamatan</dt>
                <dd class="font-semibold text-slate-800">{{ $pemuda->alamat->district->name ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Desa / Kelurahan</dt>
                <dd class="font-semibold text-slate-800">{{ $pemuda->alamat->village->name ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Dusun / Dukuh</dt>
                <dd class="text-slate-800">{{ $pemuda->alamat->dusun ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">RT / RW</dt>
                <dd class="text-slate-800">RT {{ $pemuda->alamat->rt ?? '-' }} / RW {{ $pemuda->alamat->rw ?? '-' }}</dd>
            </div>
            <div class="py-2">
                <dt class="text-slate-500 mb-1">Alamat Lengkap</dt>
                <dd class="text-slate-800 bg-slate-50 p-3 rounded-xl border border-slate-100 leading-relaxed">
                    {{ $pemuda->alamat->address_detail ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>

    <!-- 3. PENDIDIKAN & PEKERJAAN -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-3">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="bi bi-briefcase-fill text-emerald-600 text-base"></i>
            <span>Pendidikan &amp; Pekerjaan</span>
        </h3>
        <dl class="divide-y divide-slate-100 text-xs">
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Jenjang Pendidikan</dt>
                <dd class="font-bold text-slate-800">{{ $pemuda->pendidikan->educationLevel->name ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Lembaga / Sekolah</dt>
                <dd class="text-slate-800 font-semibold">{{ $pemuda->pendidikan->school_name ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Jurusan</dt>
                <dd class="text-slate-800">{{ $pemuda->pendidikan->major ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Status Pekerjaan</dt>
                <dd class="font-bold text-emerald-600">{{ $pemuda->pekerjaan->jobStatus->name ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Jabatan / Profesi</dt>
                <dd class="text-slate-800 font-medium">{{ $pemuda->pekerjaan->job_title ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-500">Tempat Kerja / Perusahaan</dt>
                <dd class="text-slate-800">{{ $pemuda->pekerjaan->company_name ?? '-' }}</dd>
            </div>
            @if(!empty($pemuda->pekerjaan->business_field))
                <div class="py-2 flex justify-between">
                    <dt class="text-slate-500">Bidang Usaha</dt>
                    <dd class="font-semibold text-amber-600">{{ $pemuda->pekerjaan->business_field }}</dd>
                </div>
            @endif
        </dl>
    </div>

    <!-- 4. ORGANISASI & POTENSI -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="bi bi-stars text-amber-500 text-base"></i>
            <span>Element Dakwah, Skill &amp; Minat</span>
        </h3>

        <!-- Element Dakwah -->
        <div>
            <h4 class="text-xs font-bold text-slate-700 mb-2">Element Dakwah Diikuti:</h4>
            <div class="flex flex-wrap gap-1.5">
                @forelse($pemuda->organisasi ?? [] as $org)
                    <span class="px-2.5 py-1 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-bold">
                        {{ $org->organization_name }}
                    </span>
                @empty
                    <span class="text-xs text-slate-400 italic">Belum mengikuti element organisasi</span>
                @endforelse
            </div>
        </div>

        <!-- Skills -->
        <div class="pt-2 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-700 mb-2">Keahlian &amp; Bakat:</h4>
            <div class="flex flex-wrap gap-1.5">
                @forelse($pemuda->skills ?? [] as $sk)
                    <span class="px-2.5 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold">
                        {{ $sk->name }}
                    </span>
                @empty
                    <span class="text-xs text-slate-400 italic">Tidak ada keahlian khusus terdata</span>
                @endforelse
            </div>
        </div>

        <!-- Interests -->
        <div class="pt-2 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-700 mb-2">Minat &amp; Ketertarikan:</h4>
            <div class="flex flex-wrap gap-1.5">
                @forelse($pemuda->interests ?? [] as $int)
                    <span class="px-2.5 py-1 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                        {{ $int->name }}
                    </span>
                @empty
                    <span class="text-xs text-slate-400 italic">Tidak ada minat terdata</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
