@extends('admin.layouts.main')

@section('title', 'Detail Warga: ' . ($warga['nama'] ?? 'MTA'))

@section('content')

@php
    $isMale  = strtoupper($warga['kelamin'] ?? 'L') === 'L';
    $isLocal = !empty($localPemuda);
    $waNumber = !empty($warga['nohp']) ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $warga['nohp'])) : null;
@endphp

<!-- BACK BUTTON -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <a href="{{ route('admin.warga-mta.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-600 font-bold text-xs transition border border-slate-200">
        <i class="bi bi-arrow-left"></i>
        <span>Kembali ke Data Warga</span>
    </a>

    <div>
        @if($isLocal)
            <a href="{{ route('admin.pemuda.detail', $localPemuda->id) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>Buka di Basis Data Pemuda</span>
            </a>
        @else
            <button type="button" onclick="openModal('modalImportSingle')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm">
                <i class="bi bi-person-plus-fill"></i>
                <span>Daftarkan ke Pemuda PMD</span>
            </button>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- LEFT: PROFILE CARD -->
    <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm text-center">
        @if(!empty($warga['foto']) && !str_contains($warga['foto'], 'default.png'))
            <img src="{{ $warga['foto'] }}" alt="{{ $warga['nama'] ?? 'MTA' }}" class="w-24 h-24 mx-auto mb-4 rounded-3xl object-cover shadow-sm border-2 {{ $isMale ? 'border-blue-500/20' : 'border-pink-500/20' }}">
        @else
            <div class="w-24 h-24 mx-auto mb-4 rounded-3xl bg-slate-50 border-2 {{ $isMale ? 'border-blue-500/20 text-blue-600' : 'border-pink-500/20 text-pink-600' }} flex items-center justify-center text-4xl shadow-sm">
                <i class="bi bi-person-fill"></i>
            </div>
        @endif

        <h3 class="text-lg font-black text-slate-900 tracking-tight mb-1">{{ $warga['nama'] ?? '-' }}</h3>
        <div class="flex items-center justify-center gap-1.5 mb-4">
            <span class="px-2.5 py-0.5 rounded-full {{ $isMale ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }} text-[10px] font-bold">
                {{ $isMale ? 'Putra (L)' : 'Putri (P)' }}
            </span>
            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold">
                {{ $warga['status'] ?? 'Warga' }}
            </span>
        </div>

        <dl class="divide-y divide-slate-100 text-xs text-left mb-5">
            <div class="py-2 flex justify-between">
                <dt class="text-slate-400">Nomor Warga:</dt>
                <dd class="font-mono font-bold text-slate-800">{{ $warga['nomor'] ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-400">Cabang MTA:</dt>
                <dd class="font-semibold text-red-600">{{ $warga['cabang'] ?? '-' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-400">Perwakilan:</dt>
                <dd class="font-medium text-slate-700">{{ $warga['perwakilan'] ?? 'Sragen' }}</dd>
            </div>
            <div class="py-2 flex justify-between">
                <dt class="text-slate-400">Status di PMD:</dt>
                <dd>
                    @if($isLocal)
                        <span class="text-emerald-600 font-bold">Terdaftar ({{ $localPemuda->registration_number }})</span>
                    @else
                        <span class="text-amber-600 font-bold">Belum Terdaftar</span>
                    @endif
                </dd>
            </div>
        </dl>

        @if($waNumber)
            <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm flex items-center justify-center gap-2">
                <i class="bi bi-whatsapp"></i>
                <span>Hubungi via WhatsApp</span>
            </a>
        @endif
    </div>

    <!-- RIGHT: DETAILS TABLE -->
    <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4 text-xs">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
            <i class="bi bi-info-circle text-sky-600 text-base"></i>
            <span>Data Lengkap Warga (API Pusat)</span>
        </h3>

        <dl class="divide-y divide-slate-100 text-xs">
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Tempat, Tanggal Lahir</dt>
                <dd class="col-span-2 font-medium text-slate-800">
                    {{ $warga['tempat_lahir'] ?? 'Sragen' }}, {{ !empty($warga['lahir']) ? \Carbon\Carbon::parse($warga['lahir'])->format('d F Y') : ($warga['tanggal_lahir'] ?? '-') }}
                    @if(!empty($warga['usia'])) <span class="text-slate-500 font-normal">({{ $warga['usia'] }} tahun)</span> @endif
                </dd>
            </div>
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Nomor Kontak / HP</dt>
                <dd class="col-span-2 font-semibold text-slate-800">{{ $warga['nohp'] ?? '-' }}</dd>
            </div>
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Status Pernikahan</dt>
                <dd class="col-span-2 text-slate-800 font-medium">{{ $warga['menikah'] ?? 'Belum Menikah' }}</dd>
            </div>
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Golongan Darah</dt>
                <dd class="col-span-2 text-slate-800 font-bold {{ !empty($warga['goldar']) ? 'text-red-600' : '' }}">{{ $warga['goldar'] ?? '-' }}</dd>
            </div>
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Alamat Lengkap</dt>
                <dd class="col-span-2 text-slate-700 leading-relaxed">{{ $warga['alamat'] ?? '-' }}</dd>
            </div>
            @if(!empty($warga['alamat_rtrw']))
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">RT / RW</dt>
                <dd class="col-span-2 text-slate-800">{{ $warga['alamat_rtrw'] }}</dd>
            </div>
            @endif
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Desa &amp; Kecamatan</dt>
                <dd class="col-span-2 text-slate-800">{{ $warga['desa'] ?? '-' }}, Kec. {{ $warga['kecamatan'] ?? '-' }}</dd>
            </div>
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Kabupaten &amp; Provinsi</dt>
                <dd class="col-span-2 text-slate-800">{{ $warga['kabupaten'] ?? 'Sragen' }}, {{ $warga['provinsi'] ?? 'Jawa Tengah' }}</dd>
            </div>
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Pendidikan Terakhir</dt>
                <dd class="col-span-2 text-slate-800">{{ $warga['pendidikan'] ?? '-' }} @if(!empty($warga['sekolah'])) ({{ $warga['sekolah'] }}) @endif</dd>
            </div>
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Pekerjaan</dt>
                <dd class="col-span-2 text-slate-800 font-medium">{{ $warga['pekerjaan'] ?? '-' }}</dd>
            </div>
            @if(!empty($warga['ayah']))
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Nama Ayah</dt>
                <dd class="col-span-2 text-slate-800">{{ $warga['ayah'] }}</dd>
            </div>
            @endif
            @if(!empty($warga['ibu']))
            <div class="py-2.5 grid grid-cols-3">
                <dt class="text-slate-400">Nama Ibu</dt>
                <dd class="col-span-2 text-slate-800">{{ $warga['ibu'] }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>

<!-- MODAL IMPORT SINGLE -->
@if(!$isLocal)
<div id="modalImportSingle" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 text-xs">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-person-plus text-emerald-600"></i>
                <span>Import ke Database Pemuda</span>
            </h3>
            <button type="button" onclick="closeModal('modalImportSingle')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.warga-mta.import') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="warga_uuid" value="{{ $warga['uuid'] ?? $warga['id'] ?? '' }}">

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Nama Warga:</span>
                    <span class="font-bold text-slate-900">{{ $warga['nama'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Cabang (MTA Pusat):</span>
                    <span class="font-bold text-emerald-700 flex items-center gap-1">
                        <i class="bi bi-geo-alt-fill text-emerald-500"></i>
                        <span>{{ $warga['cabang'] ?? '-' }}</span>
                    </span>
                </div>
                @if(!empty($warga['nomor']))
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">No. Warga:</span>
                    <span class="font-mono font-bold text-slate-700">{{ $warga['nomor'] }}</span>
                </div>
                @endif
            </div>

            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-[11px] flex items-start gap-2">
                <i class="bi bi-info-circle-fill text-emerald-600 flex-shrink-0 mt-0.5"></i>
                <span>Data pemuda akan otomatis dimasukkan ke cabang <strong>{{ $warga['cabang'] ?? 'MTA Pusat' }}</strong> sesuai basis data pusat tanpa perlu memilih cabang secara manual.</span>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalImportSingle')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-md flex items-center gap-1.5">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Daftarkan Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
