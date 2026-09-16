@extends('admin.layouts.main')

@section('title', 'Data Warga MTA Sragen (API Pusat)')

@section('content')

@php
    $currPage   = (int) ($meta['page'] ?? 1);
    $perPage    = (int) ($meta['per_page'] ?? 20);
    $totalRows  = (int) ($meta['total'] ?? count($wargaList ?? []));
    $totalPages = (int) ($meta['total_pages'] ?? max(1, (int) ceil($totalRows / max(1, $perPage))));
    $search     = $search ?? '';
    $cabang     = $cabang ?? $selectedCabang ?? '';
    $kelamin    = $kelamin ?? $selectedKelamin ?? '';
    $status     = $status ?? $selectedStatus ?? '';
    $statusPmd  = $statusPmd ?? $selectedStatusPmd ?? '';
    $cabangList = $cabangList ?? [];

    $buildPageUrl = function($targetPage) use ($search, $cabang, $kelamin, $status, $statusPmd, $perPage) {
        $params = array_filter([
            'search'     => $search,
            'cabang'     => $cabang,
            'kelamin'    => $kelamin,
            'status'     => $status,
            'status_pmd' => $statusPmd,
            'per_page'   => $perPage,
            'page'       => $targetPage
        ]);
        return route('admin.warga-mta.index', $params);
    };
@endphp

<!-- HEADER & ACTIONS -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Data Warga MTA — Perwakilan Sragen</h2>
        <p class="text-xs text-slate-500 mt-0.5">Eksplorasi dan import data warga langsung dari server REST API Pusat (<code>api.mta.or.id</code>).</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('admin.mta-sync.index') }}" class="px-4 py-2.5 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 font-bold text-xs transition border border-sky-200 flex items-center gap-2">
            <i class="bi bi-arrow-repeat"></i>
            <span>Sinkronisasi API</span>
        </a>
        <a href="{{ route('admin.pemuda.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 text-white hover:bg-slate-700 font-bold text-xs transition shadow-sm flex items-center gap-2">
            <i class="bi bi-people"></i>
            <span>Data Pemuda</span>
        </a>
    </div>
</div>

@if($apiError)
    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-3">
        <i class="bi bi-exclamation-triangle-fill text-amber-500 text-lg flex-shrink-0 mt-0.5"></i>
        <div>
            <strong class="font-bold block mb-0.5">Peringatan Koneksi API MTA:</strong>
            <span>{{ $apiError }}</span>
        </div>
    </div>
@endif

<!-- STATS OVERVIEW -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Total Warga Sragen</div>
        <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalRows) }}</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Database Pusat MTA</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Cabang di Sragen</div>
        <div class="text-2xl font-black text-sky-600 mt-1">{{ count($cabangList) }} Cabang</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Perwakilan Sragen</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Tersinkron di PMD</div>
        <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($stats['totalPemudaSyncedMta'] ?? 0) }}</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Terverifikasi otomatis</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Total Pemuda PMD</div>
        <div class="text-2xl font-black text-red-600 mt-1">{{ number_format($stats['totalPemudaLokal'] ?? 0) }}</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Basis data lokal</div>
    </div>
</div>

<!-- FILTER SEARCH CARD -->
<div class="mb-6 rounded-3xl bg-white p-5 border border-slate-200/80 shadow-sm">
    <form action="{{ route('admin.warga-mta.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs items-end">
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Cari Nama / NIK</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama warga, NIK..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Cabang MTA</label>
            <select name="cabang" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                <option value="">-- Semua Cabang --</option>
                @foreach($cabangList as $c)
                    <option value="{{ $c['uuid'] ?? $c['name'] }}" {{ ($cabang === ($c['uuid'] ?? $c['name'])) ? 'selected' : '' }}>
                        {{ $c['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin</label>
            <select name="kelamin" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                <option value="">-- Semua Gender --</option>
                <option value="L" {{ $kelamin === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                <option value="P" {{ $kelamin === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                <i class="bi bi-filter"></i> Saring
            </button>
            <a href="{{ route('admin.warga-mta.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition" title="Reset Filter">
                <i class="bi bi-arrow-clockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- WARGA MTA TABLE -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                    <th class="py-3 px-4">Nama Lengkap</th>
                    <th class="py-3 px-4">Gender &amp; TTL</th>
                    <th class="py-3 px-4">Cabang MTA</th>
                    <th class="py-3 px-4">Alamat Domisili</th>
                    <th class="py-3 px-4">Status Sinkron PMD</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($wargaList as $w)
                    @php
                        $uuid = $w['uuid'] ?? $w['id'] ?? '';
                        $isSynced = !empty($w['pemuda_id']);
                    @endphp
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $w['nama'] ?? $w['name'] ?? '-' }}</div>
                            <span class="text-[10px] font-mono text-slate-400">NIK: {{ $w['nik'] ?? '-' }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1">
                                <span class="font-bold {{ ($w['kelamin'] ?? 'L') === 'L' ? 'text-blue-600' : 'text-pink-600' }}">
                                    {{ ($w['kelamin'] ?? 'L') === 'L' ? 'L' : 'P' }}
                                </span>
                                <span class="text-slate-500 text-[11px]">
                                    &bull; {{ $w['tempat_lahir'] ?? '' }}, {{ $w['tanggal_lahir'] ?? '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-700">
                            {{ $w['cabang_nama'] ?? $w['cabang'] ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-slate-500 text-[11px]">
                            {{ $w['alamat'] ?? '-' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($isSynced)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    <i class="bi bi-check-circle-fill"></i> Sudah Terdaftar
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px]">
                                    Belum Diimpor
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                @if(!empty($uuid))
                                    <a href="{{ route('admin.warga-mta.detail', $uuid) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-sky-50 hover:text-sky-600 text-slate-600 transition" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                                @if(!$isSynced && !empty($uuid))
                                    <button type="button" onclick="openImportModal('{{ $uuid }}', '{{ addslashes($w['nama'] ?? '') }}')" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold transition" title="Import ke Data Pemuda">
                                        <i class="bi bi-plus-circle-fill"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data warga MTA yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    @if($totalPages > 1)
        <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-xs">
            <span class="text-slate-500">Halaman {{ $currPage }} dari {{ $totalPages }}</span>
            <div class="flex items-center gap-1">
                @if($currPage > 1)
                    <a href="{{ $buildPageUrl($currPage - 1) }}" class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold">Sebelumnya</a>
                @endif
                @if($currPage < $totalPages)
                    <a href="{{ $buildPageUrl($currPage + 1) }}" class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold">Selanjutnya</a>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- MODAL IMPORT WARGA TO PEMUDA -->
<div id="modalImportWarga" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 text-xs">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-cloud-arrow-down text-emerald-600"></i>
                <span>Import ke Database Pemuda</span>
            </h3>
            <button type="button" onclick="closeModal('modalImportWarga')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.warga-mta.import') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="warga_uuid" id="importWargaUuid">

            <div>
                <span class="text-slate-400 block mb-1">Nama Warga:</span>
                <div class="font-bold text-slate-900 text-sm" id="importWargaNama">-</div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Cabang Pemuda Lokal <span class="text-red-500">*</span></label>
                <select name="cabang_id" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($cabangList as $c)
                        @if(isset($c['id']))
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalImportWarga')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-md">Import Sekarang</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openImportModal(uuid, nama) {
        document.getElementById('importWargaUuid').value = uuid;
        document.getElementById('importWargaNama').textContent = nama;
        openModal('modalImportWarga');
    }
</script>
@endsection
