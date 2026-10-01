@extends('admin.layouts.main')

@section('title', 'Manajemen Cabang')

@section('content')

<!-- HEADER JUDUL HALAMAN -->
<div class="mb-5">
    <div class="flex flex-wrap items-center gap-2 mb-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-red-700 text-xs font-bold">
            <i class="bi bi-diagram-3-fill"></i> Master Cabang Binaan
        </span>
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-medium">
            Kabupaten Sragen
        </span>
    </div>
    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Cabang Binaan</h2>
    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl">Kelola informasi 61+ cabang MTA, jadwal kajian pemuda, dan lokasi peta se-Kabupaten Sragen.</p>
</div>

<!-- TOMBOL MENU & NAVIGASI CEPAT (TERPISAH DARI BANNER) -->
<div class="mb-6 bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3.5 pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-sm">
                <i class="bi bi-grid-fill"></i>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Tombol Menu &amp; Navigasi Cepat</h3>
                <p class="text-[11px] text-slate-500">Pintasan aksi penambahan cabang, impor-ekspor file Excel, dan navigasi data</p>
            </div>
        </div>
        <span class="text-[11px] text-slate-400 hidden sm:inline-flex items-center gap-1.5 font-medium">
            <i class="bi bi-lightning-charge-fill text-amber-500"></i> Menu Cepat
        </span>
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        <!-- Tambah Cabang Baru -->
        <button type="button" onclick="openModal('modalAddCabang')" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-sm hover:shadow flex items-center gap-2">
            <i class="bi bi-plus-lg text-sm"></i>
            <span>Tambah Cabang Baru</span>
        </button>

        <!-- Import Excel -->
        <button type="button" onclick="openModal('modalImportCabang')" class="px-3.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition shadow-sm hover:shadow flex items-center gap-2" title="Import data cabang dari file Excel">
            <i class="bi bi-file-earmark-arrow-up-fill text-sm"></i>
            <span>Import Excel</span>
        </button>

        <!-- Export Excel -->
        <a href="{{ route('admin.cabang.export', request()->query()) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-semibold text-xs transition border border-slate-200/90 hover:border-emerald-300 shadow-2xs flex items-center gap-2" title="Export data cabang ke file Excel (.xlsx)">
            <i class="bi bi-file-earmark-excel-fill text-emerald-500 text-sm"></i>
            <span>Export Excel</span>
        </a>

        <!-- Master Wilayah -->
        <a href="{{ route('admin.wilayah.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 font-semibold text-xs transition border border-slate-200/90 shadow-2xs">
            <i class="bi bi-geo-alt-fill text-slate-500 text-sm"></i>
            <span>Master Wilayah</span>
        </a>

        <!-- Kelola Data Pemuda -->
        <a href="{{ route('admin.pemuda.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 font-semibold text-xs transition border border-slate-200/90 shadow-2xs">
            <i class="bi bi-people-fill text-slate-500 text-sm"></i>
            <span>Data Pemuda</span>
        </a>
    </div>
</div>

@if(session('import_result'))
    @php $res = session('import_result'); @endphp
    <div class="mb-6 p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm animate-in fade-in">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl {{ ($res['error_count'] ?? 0) === 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center font-bold text-lg flex-shrink-0">
                    <i class="bi {{ ($res['error_count'] ?? 0) === 0 ? 'bi-check2-circle' : 'bi-exclamation-triangle' }}"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-800">Hasil Pemrosesan Import Data Cabang</h4>
                    <p class="text-xs text-slate-500">{{ $res['message'] ?? 'Import telah selesai diproses.' }}</p>
                </div>
            </div>
            <span class="text-[11px] font-mono text-slate-400">Total Baris: {{ $res['total_rows'] ?? 0 }}</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs mb-3">
            <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-100">
                <span class="text-[10px] uppercase font-bold text-emerald-700 block">Ditambahkan</span>
                <span class="text-lg font-black text-emerald-600">{{ $res['inserted_count'] ?? 0 }}</span>
            </div>
            <div class="p-2.5 rounded-xl bg-sky-50 border border-sky-100">
                <span class="text-[10px] uppercase font-bold text-sky-700 block">Diperbarui</span>
                <span class="text-lg font-black text-sky-600">{{ $res['updated_count'] ?? 0 }}</span>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70">
                <span class="text-[10px] uppercase font-bold text-slate-600 block">Dilewati</span>
                <span class="text-lg font-black text-slate-600">{{ $res['skipped_count'] ?? 0 }}</span>
            </div>
            <div class="p-2.5 rounded-xl {{ ($res['error_count'] ?? 0) > 0 ? 'bg-rose-50 border-rose-100' : 'bg-slate-50 border-slate-200/70' }} border">
                <span class="text-[10px] uppercase font-bold {{ ($res['error_count'] ?? 0) > 0 ? 'text-rose-700' : 'text-slate-600' }} block">Gagal / Error</span>
                <span class="text-lg font-black {{ ($res['error_count'] ?? 0) > 0 ? 'text-rose-600' : 'text-slate-600' }}">{{ $res['error_count'] ?? 0 }}</span>
            </div>
        </div>

        @if(!empty($res['errors']))
            <details class="text-xs group">
                <summary class="cursor-pointer font-bold text-rose-700 hover:text-rose-800 flex items-center gap-1.5 p-2 rounded-xl bg-rose-50/60 border border-rose-100 select-none">
                    <i class="bi bi-exclamation-circle"></i>
                    <span>Lihat Rincian Baris yang Bermasalah ({{ count($res['errors']) }})</span>
                </summary>
                <div class="mt-2 overflow-x-auto max-h-48 overflow-y-auto border border-rose-200 rounded-xl">
                    <table class="w-full text-left text-[11px]">
                        <thead class="bg-rose-100/70 text-rose-800 font-bold sticky top-0">
                            <tr>
                                <th class="p-2">Baris</th>
                                <th class="p-2">Nama Cabang</th>
                                <th class="p-2">Keterangan Kesalahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100 bg-white">
                            @foreach($res['errors'] as $err)
                                <tr>
                                    <td class="p-2 font-mono font-bold text-slate-600">Baris {{ $err['row'] ?? '-' }}</td>
                                    <td class="p-2 font-semibold text-slate-800">{{ $err['cabang'] ?? '-' }}</td>
                                    <td class="p-2 text-rose-600">{{ $err['reason'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endif
    </div>
@endif

<!-- STATS SUMMARY CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
            <i class="bi bi-diagram-3-fill"></i>
        </div>
        <div>
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Total Cabang</div>
            <div class="text-xl font-black text-slate-900">{{ number_format($totalCabang) }} Cabang</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
            <i class="bi bi-check2-circle"></i>
        </div>
        <div>
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Sudah Ada Kajian Pemuda</div>
            <div class="text-xl font-black text-emerald-600">{{ number_format($totalSudahGelombang) }} Cabang</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
            <i class="bi bi-clock"></i>
        </div>
        <div>
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Belum Ada Kajian Pemuda</div>
            <div class="text-xl font-black text-amber-600">{{ number_format($totalBelumGelombang) }} Cabang</div>
        </div>
    </div>
</div>

<!-- FILTER CARD -->
<div class="mb-6 rounded-3xl bg-white p-5 border border-slate-200/80 shadow-sm">
    <form action="{{ route('admin.cabang.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs items-end">
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Cari Cabang</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Nama cabang, pimpinan, alamat..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Wilayah</label>
            <select name="wilayah_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                <option value="">-- Semua Wilayah --</option>
                @foreach($wilayahList as $w)
                    <option value="{{ $w->id }}" {{ ($selectedW ?? '') == $w->id ? 'selected' : '' }}>
                        {{ $w->name }} ({{ $w->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Status Kajian Pemuda</label>
            <select name="has_gelombang" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                <option value="">-- Semua Status --</option>
                <option value="sudah" {{ ($selectedGelombang ?? '') === 'sudah' ? 'selected' : '' }}>Sudah Ada Kajian</option>
                <option value="belum" {{ ($selectedGelombang ?? '') === 'belum' ? 'selected' : '' }}>Belum Ada Kajian</option>
            </select>
        </div>

        <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
        <div class="flex gap-2">
            <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                <i class="bi bi-filter"></i> Saring
            </button>
            <a href="{{ route('admin.cabang.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition" title="Reset Filter">
                <i class="bi bi-arrow-clockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- CABANG LIST TABLE -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-wrap gap-3 items-center justify-between">
        <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
            <span>Ditemukan <span class="text-red-600 font-extrabold">{{ number_format($cabangList->total()) }}</span> cabang</span>
            @if($cabangList->total() > 0)
                <span class="text-slate-400 font-normal">| Hal. {{ $cabangList->currentPage() }} dari {{ $cabangList->lastPage() }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2 text-xs">
            <label for="perPageCabang" class="text-slate-500 font-medium hidden sm:inline">Tampilkan:</label>
            <select id="perPageCabang" onchange="changePerPageCabang(this.value)" class="py-1 px-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-700 font-semibold focus:ring-red-500 focus:border-red-500 text-xs">
                @foreach([10, 20, 50, 100] as $opt)
                    <option value="{{ $opt }}" {{ (request('per_page', 20) == $opt) ? 'selected' : '' }}>{{ $opt }} / hal</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                    <th class="py-3 px-4">Nama Cabang</th>
                    <th class="py-3 px-4">Wilayah</th>
                    <th class="py-3 px-4">Pimpinan &amp; Kontak</th>
                    <th class="py-3 px-4">Kajian Pemuda</th>
                    <th class="py-3 px-4 text-center">Total Pemuda</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($cabangList as $c)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $c->name }}</div>
                            @if($c->code)
                                <span class="text-[10px] font-mono text-slate-400">Kode: {{ $c->code }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-700">
                            {{ $c->wilayah->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800">{{ $c->pimpinan_nama ?: '-' }}</div>
                            @if($c->no_wa)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $c->no_wa)) }}" target="_blank" class="text-emerald-600 hover:underline flex items-center gap-1 text-[11px]">
                                    <i class="bi bi-whatsapp"></i> {{ $c->no_wa }}
                                </a>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($c->has_gelombang === 'sudah')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                    <i class="bi bi-check-circle-fill"></i> Sudah Ada Kajian
                                </span>
                                @if($c->gelombang_hari || $c->gelombang_jam)
                                    <div class="text-[11px] text-slate-600 mt-0.5 font-medium">{{ $c->gelombang_hari ?: '-' }} &bull; {{ $c->gelombang_jam ?: '-' }}</div>
                                @endif
                                @if($c->gelombang_ustadz)
                                    <div class="text-[10px] text-slate-400 mt-0.5"><i class="bi bi-person-fill"></i> {{ $c->gelombang_ustadz }}</div>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold text-[10px]">
                                    Belum Ada Kajian
                                </span>
                            @endif
                            @if($c->ketua_pemuda)
                                <div class="text-[10px] text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-md px-2 py-0.5 mt-1 font-semibold flex items-center gap-1 max-w-[200px] truncate" title="Ketua Pemuda: {{ $c->ketua_pemuda }}">
                                    <i class="bi bi-person-badge-fill text-indigo-500 flex-shrink-0"></i>
                                    <span class="truncate">Ketua: {{ $c->ketua_pemuda }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('admin.pemuda.index', ['cabang_id' => $c->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[11px] transition">
                                <i class="bi bi-people"></i> {{ $c->total_pemuda }} Pemuda
                            </a>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                <button type="button" onclick="viewDetailCabang({{ $c->id }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-sky-50 hover:text-sky-600 text-slate-600 transition" title="Detail Cabang">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" onclick="editCabang({{ json_encode($c) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-amber-50 hover:text-amber-600 text-slate-600 transition" title="Edit Cabang">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('admin.cabang.delete', $c->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus cabang {{ $c->name }}?')">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-400 transition" title="Hapus Cabang">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data cabang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
        {{ $cabangList->links() }}
    </div>
</div>

<!-- MODAL TAMBAH CABANG -->
<div id="modalAddCabang" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-plus-circle text-red-600"></i>
                <span>Tambah Cabang Baru</span>
            </h3>
            <button type="button" onclick="closeModal('modalAddCabang')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.cabang.simpan') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Wilayah <span class="text-red-500">*</span></label>
                    <select name="wilayah_id" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Pilih Wilayah --</option>
                        @foreach($wilayahList as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Cabang <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="Masaran 1" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kode Cabang</label>
                    <input type="text" name="code" placeholder="MSR1" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 uppercase focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Pimpinan / Ketua</label>
                    <input type="text" name="pimpinan_nama" placeholder="Ust. ..." class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">No. WhatsApp</label>
                    <input type="tel" name="no_wa" placeholder="08xxxxxxxxxx" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Cabang</label>
                    <textarea name="alamat" rows="2" placeholder="Dukuh, Desa, RT/RW, Kec..." class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tautan / Link Google Maps (URL)</label>
                    <input type="url" name="maps_url" placeholder="https://maps.app.goo.gl/..." class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi / Catatan Cabang</label>
                    <textarea name="description" rows="2" placeholder="Catatan mengenai operasional cabang, agenda khusus, dll..." class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>

                <!-- Bagian Kajian Pemuda -->
                <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                    <h4 class="text-[11px] font-bold text-red-700 uppercase tracking-wider flex items-center gap-1.5 mb-1">
                        <i class="bi bi-book-half"></i> Sesi Kajian Pemuda Cabang
                    </h4>
                    <p class="text-[10px] text-slate-400">Informasi pelaksanaan pengajian / kajian khusus pemuda di cabang ini</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Kajian Pemuda <span class="text-red-500">*</span></label>
                    <select name="has_gelombang" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        <option value="belum">Belum Ada Kajian</option>
                        <option value="sudah">Sudah Ada Kajian</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Hari Kajian</label>
                    <input type="text" name="gelombang_hari" placeholder="Contoh: Ahad Pagi" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jam / Waktu Kajian</label>
                    <input type="text" name="gelombang_jam" placeholder="Contoh: 06:00 - 07:30 WIB" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Ustadz Pengampu Kajian</label>
                    <input type="text" name="gelombang_ustadz" placeholder="Contoh: Ust. Ahmad Fauzi" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <!-- Bagian Pengurus / Koordinator Pemuda Cabang -->
                <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                    <h4 class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider flex items-center gap-1.5 mb-1">
                        <i class="bi bi-people-fill"></i> Pengurus / Koordinator Pemuda (Kajian Gelombang)
                    </h4>
                    <p class="text-[10px] text-slate-400">Susunan pengurus pemuda cabang: Ketua, Sekretaris, dan Bendahara (KSB)</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Ketua / Koordinator Pemuda</label>
                    <input type="text" name="ketua_pemuda" placeholder="Nama Ketua Pemuda" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">No. WhatsApp Pemuda</label>
                    <input type="tel" name="no_wa_pemuda" placeholder="08xxxxxxxxxx" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Sekretaris Pemuda</label>
                    <input type="text" name="sekretaris_pemuda" placeholder="Nama Sekretaris Pemuda" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Bendahara Pemuda</label>
                    <input type="text" name="bendahara_pemuda" placeholder="Nama Bendahara Pemuda" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalAddCabang')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-md">Simpan Cabang</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT CABANG -->
<div id="modalEditCabang" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-pencil-square text-amber-500"></i>
                <span>Edit Data Cabang</span>
            </h3>
            <button type="button" onclick="closeModal('modalEditCabang')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="formEditCabang" method="POST" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Wilayah <span class="text-red-500">*</span></label>
                    <select name="wilayah_id" id="editWilayahId" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        @foreach($wilayahList as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Cabang <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="editCabangName" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kode Cabang</label>
                    <input type="text" name="code" id="editCabangCode" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 uppercase focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Pimpinan / Ketua</label>
                    <input type="text" name="pimpinan_nama" id="editPimpinanNama" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">No. WhatsApp</label>
                    <input type="tel" name="no_wa" id="editNoWa" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Cabang</label>
                    <textarea name="alamat" id="editAlamat" rows="2" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tautan / Link Google Maps (URL)</label>
                    <input type="url" name="maps_url" id="editMapsUrl" placeholder="https://maps.app.goo.gl/..." class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi / Catatan Cabang</label>
                    <textarea name="description" id="editDescription" rows="2" placeholder="Catatan mengenai operasional cabang, agenda khusus, dll..." class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>

                <!-- Bagian Kajian Pemuda -->
                <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                    <h4 class="text-[11px] font-bold text-amber-700 uppercase tracking-wider flex items-center gap-1.5 mb-1">
                        <i class="bi bi-book-half"></i> Sesi Kajian Pemuda Cabang
                    </h4>
                    <p class="text-[10px] text-slate-400">Informasi pelaksanaan pengajian / kajian khusus pemuda di cabang ini</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Kajian Pemuda <span class="text-red-500">*</span></label>
                    <select name="has_gelombang" id="editHasGelombang" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        <option value="belum">Belum Ada Kajian</option>
                        <option value="sudah">Sudah Ada Kajian</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Hari Kajian</label>
                    <input type="text" name="gelombang_hari" id="editGelombangHari" placeholder="Contoh: Ahad Pagi" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jam / Waktu Kajian</label>
                    <input type="text" name="gelombang_jam" id="editGelombangJam" placeholder="Contoh: 06:00 - 07:30 WIB" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Ustadz Pengampu Kajian</label>
                    <input type="text" name="gelombang_ustadz" id="editGelombangUstadz" placeholder="Contoh: Ust. Ahmad Fauzi" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <!-- Bagian Pengurus / Koordinator Pemuda Cabang -->
                <div class="sm:col-span-2 pt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider flex items-center gap-1.5 mb-0.5">
                            <i class="bi bi-people-fill"></i> Pengurus / Koordinator Pemuda (Kajian Gelombang)
                        </h4>
                        <p class="text-[10px] text-slate-400">Pilih dari data pemuda cabang atau ketik nama secara manual</p>
                    </div>
                    <div id="editPemudaLoadingBadge" class="text-[10px] px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-semibold flex items-center gap-1">
                        <i class="bi bi-arrow-repeat animate-spin"></i> Memuat data pemuda...
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-700 uppercase">Ketua / Koordinator</label>
                        <button type="button" id="btnToggleManualKetua" onclick="toggleManualPengurus('Ketua')" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold transition">
                            <i class="bi bi-pencil"></i> Ketik Manual
                        </button>
                    </div>
                    <div id="wrapperSelectKetua">
                        <select id="editSelectKetuaPemuda" onchange="onSelectPengurusChange('Ketua')" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                            <option value="">-- Pilih dari Pemuda Cabang --</option>
                        </select>
                    </div>
                    <div id="wrapperInputKetua" class="hidden">
                        <input type="text" id="editInputKetuaPemuda" placeholder="Nama Ketua Pemuda" oninput="onInputPengurusChange('Ketua')" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                    </div>
                    <input type="hidden" name="ketua_pemuda" id="editKetuaPemuda">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">No. WhatsApp Pemuda</label>
                    <input type="tel" name="no_wa_pemuda" id="editNoWaPemuda" placeholder="08xxxxxxxxxx" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                    <p id="hintNoWaPemudaAuto" class="text-[10px] text-emerald-600 mt-1 hidden flex items-center gap-1 font-medium">
                        <i class="bi bi-check-circle-fill"></i> Kontak otomatis terisi dari profil pemuda terpilih
                    </p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-700 uppercase">Sekretaris Pemuda</label>
                        <button type="button" id="btnToggleManualSekretaris" onclick="toggleManualPengurus('Sekretaris')" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold transition">
                            <i class="bi bi-pencil"></i> Ketik Manual
                        </button>
                    </div>
                    <div id="wrapperSelectSekretaris">
                        <select id="editSelectSekretarisPemuda" onchange="onSelectPengurusChange('Sekretaris')" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                            <option value="">-- Pilih dari Pemuda Cabang --</option>
                        </select>
                    </div>
                    <div id="wrapperInputSekretaris" class="hidden">
                        <input type="text" id="editInputSekretarisPemuda" placeholder="Nama Sekretaris Pemuda" oninput="onInputPengurusChange('Sekretaris')" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                    </div>
                    <input type="hidden" name="sekretaris_pemuda" id="editSekretarisPemuda">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-700 uppercase">Bendahara Pemuda</label>
                        <button type="button" id="btnToggleManualBendahara" onclick="toggleManualPengurus('Bendahara')" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold transition">
                            <i class="bi bi-pencil"></i> Ketik Manual
                        </button>
                    </div>
                    <div id="wrapperSelectBendahara">
                        <select id="editSelectBendaharaPemuda" onchange="onSelectPengurusChange('Bendahara')" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                            <option value="">-- Pilih dari Pemuda Cabang --</option>
                        </select>
                    </div>
                    <div id="wrapperInputBendahara" class="hidden">
                        <input type="text" id="editInputBendaharaPemuda" placeholder="Nama Bendahara Pemuda" oninput="onInputPengurusChange('Bendahara')" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                    </div>
                    <input type="hidden" name="bendahara_pemuda" id="editBendaharaPemuda">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditCabang')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold transition shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DETAIL CABANG -->
<div id="modalDetailCabang" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 text-xs max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-info-circle text-sky-600"></i>
                <span id="detailCabangTitle">Detail Cabang</span>
            </h3>
            <button type="button" onclick="closeModal('modalDetailCabang')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="detailCabangBody" class="space-y-3">
            <!-- Content loaded via JS viewDetailCabang -->
        </div>

        <div class="pt-4 mt-4 border-t border-slate-100 text-right">
            <button type="button" onclick="closeModal('modalDetailCabang')" class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL IMPORT CABANG -->
<div id="modalImportCabang" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 text-xs">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-file-earmark-arrow-up-fill text-indigo-600"></i>
                <span>Import Data Master Cabang dari Excel</span>
            </h3>
            <button type="button" onclick="closeModal('modalImportCabang')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Panduan & Download Template -->
        <div class="mb-4 p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-2.5">
            <div class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                    1
                </div>
                <div>
                    <span class="font-bold text-indigo-900 block">Unduh Format Template Excel</span>
                    <p class="text-[11px] text-indigo-700 leading-relaxed">
                        Gunakan file template Excel resmi agar format kolom, penamaan wilayah, dan struktur data sesuai dengan sistem.
                    </p>
                    <a href="{{ route('admin.cabang.template') }}" class="inline-flex items-center gap-1.5 mt-2 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] transition shadow-xs">
                        <i class="bi bi-download"></i>
                        <span>Unduh Template Excel (.xlsx)</span>
                    </a>
                </div>
            </div>

            <div class="flex items-start gap-2.5 pt-2 border-t border-indigo-100/80">
                <div class="w-6 h-6 rounded-lg bg-slate-700 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                    2
                </div>
                <div class="text-[11px] text-slate-600 leading-relaxed">
                    <span class="font-bold text-slate-800 block">Isi Data &amp; Unggah</span>
                    Isi data cabang pada sheet <em>"Format Import Cabang"</em>. Kolom <strong>Nama Cabang</strong>, <strong>Wilayah</strong>, dan <strong>Status Kajian Pemuda</strong> wajib diisi. Sheet <em>"Referensi Wilayah"</em> memuat daftar kode dan nama wilayah yang valid.
                </div>
            </div>
        </div>

        <!-- Form Upload -->
        <form action="{{ route('admin.cabang.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="handleImportCabangSubmit(this)">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1.5">Pilih File Excel (.xlsx / .xls) <span class="text-red-500">*</span></label>
                <div class="relative border-2 border-dashed border-slate-300 hover:border-indigo-500 rounded-2xl p-5 text-center bg-slate-50 hover:bg-indigo-50/30 transition cursor-pointer" onclick="document.getElementById('file_excel_cabang').click()">
                    <input type="file" name="file_excel" id="file_excel_cabang" accept=".xlsx,.xls" required class="hidden" onchange="displayCabangFileName(this)">
                    <div id="fileUploadPrompt">
                        <i class="bi bi-cloud-arrow-up text-3xl text-indigo-500 block mb-1"></i>
                        <span class="font-bold text-slate-700 block text-xs">Klik untuk memilih file Excel</span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Mendukung format .xlsx dan .xls (Maks. 10 MB)</span>
                    </div>
                    <div id="fileSelectedDisplay" class="hidden">
                        <i class="bi bi-file-earmark-spreadsheet text-3xl text-emerald-600 block mb-1"></i>
                        <span id="selectedFileName" class="font-bold text-slate-800 block text-xs truncate max-w-xs mx-auto">nama_file.xlsx</span>
                        <span id="selectedFileSize" class="text-[10px] text-slate-500 block mt-0.5">0 KB</span>
                    </div>
                </div>
            </div>

            <!-- Option Upsert -->
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start gap-2.5">
                <input type="checkbox" name="update_existing" id="chk_update_existing" value="1" checked class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="chk_update_existing" class="text-[11px] text-slate-700 cursor-pointer select-none leading-relaxed">
                    <strong>Perbarui data jika cabang sudah ada (Upsert)</strong><br>
                    <span class="text-slate-500">Jika nama cabang atau kode cabang sudah terdaftar di database, sistem akan memperbarui informasinya alih-alih menduplikasi data.</span>
                </label>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalImportCabang')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" id="btnSubmitImportCabang" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition shadow-md flex items-center gap-1.5">
                    <i class="bi bi-upload"></i>
                    <span>Mulai Import Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function escapeHtmlCabang(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function displayCabangFileName(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('fileUploadPrompt').classList.add('hidden');
            document.getElementById('fileSelectedDisplay').classList.remove('hidden');
            document.getElementById('selectedFileName').textContent = file.name;
            document.getElementById('selectedFileSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
        }
    }

    function handleImportCabangSubmit(form) {
        const btn = document.getElementById('btnSubmitImportCabang');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> <span>Mengimpor Data...</span>';
        }
    }

    function editCabang(c) {
        document.getElementById('formEditCabang').action = `{{ url('admin/cabang/update') }}/${c.id}`;
        document.getElementById('editWilayahId').value = c.wilayah_id;
        document.getElementById('editCabangName').value = c.name;
        document.getElementById('editCabangCode').value = c.code || '';
        document.getElementById('editPimpinanNama').value = c.pimpinan_nama || '';
        document.getElementById('editNoWa').value = c.no_wa || '';
        document.getElementById('editAlamat').value = c.alamat || '';
        document.getElementById('editMapsUrl').value = c.maps_url || '';
        document.getElementById('editDescription').value = c.description || '';
        document.getElementById('editHasGelombang').value = c.has_gelombang || 'belum';
        document.getElementById('editGelombangHari').value = c.gelombang_hari || '';
        document.getElementById('editGelombangJam').value = c.gelombang_jam || '';
        document.getElementById('editGelombangUstadz').value = c.gelombang_ustadz || '';
        document.getElementById('editKetuaPemuda').value = c.ketua_pemuda || '';
        document.getElementById('editNoWaPemuda').value = c.no_wa_pemuda || '';
        document.getElementById('editSekretarisPemuda').value = c.sekretaris_pemuda || '';
        document.getElementById('editBendaharaPemuda').value = c.bendahara_pemuda || '';
        document.getElementById('editInputKetuaPemuda').value = c.ketua_pemuda || '';
        document.getElementById('editInputSekretarisPemuda').value = c.sekretaris_pemuda || '';
        document.getElementById('editInputBendaharaPemuda').value = c.bendahara_pemuda || '';

        loadCabangPemudaOptions(c);
        openModal('modalEditCabang');
    }

    let activeCabangPemudaRequestId = 0;

    function loadCabangPemudaOptions(c) {
        const requestId = ++activeCabangPemudaRequestId;
        const badge = document.getElementById('editPemudaLoadingBadge');
        const hintWa = document.getElementById('hintNoWaPemudaAuto');
        if (hintWa) hintWa.classList.add('hidden');

        if (badge) {
            badge.className = 'text-[10px] px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200/70 font-semibold flex items-center gap-1';
            badge.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Memuat data pemuda...';
        }

        // Reset selects to loading
        ['Ketua', 'Sekretaris', 'Bendahara'].forEach(role => {
            const sel = document.getElementById(`editSelect${role}Pemuda`);
            if (sel) {
                sel.innerHTML = '<option value="">Memuat data pemuda...</option>';
                sel.disabled = true;
            }
        });

        fetch(`{{ url('admin/cabang') }}/${c.id}/pemuda`)
            .then(res => res.json())
            .then(res => {
                if (requestId !== activeCabangPemudaRequestId) return;

                if (res.status === 'success') {
                    const list = res.data || [];

                    if (badge) {
                        if (list.length > 0) {
                            badge.className = 'text-[10px] px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/70 font-semibold flex items-center gap-1';
                            badge.innerHTML = `<i class="bi bi-people-fill"></i> ${list.length} Pemuda Cabang`;
                        } else {
                            badge.className = 'text-[10px] px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200/70 font-semibold flex items-center gap-1';
                            badge.innerHTML = '<i class="bi bi-info-circle"></i> Belum ada pemuda terdaftar';
                        }
                    }

                    setupPengurusRole('Ketua', list, c.ketua_pemuda || '');
                    setupPengurusRole('Sekretaris', list, c.sekretaris_pemuda || '');
                    setupPengurusRole('Bendahara', list, c.bendahara_pemuda || '');

                    // Check if current Ketua has phone match
                    if (c.ketua_pemuda && list.length > 0) {
                        const matched = list.find(p => p.name === c.ketua_pemuda);
                        if (matched && matched.phone && matched.phone === c.no_wa_pemuda) {
                            if (hintWa) hintWa.classList.remove('hidden');
                        }
                    }
                } else {
                    if (badge) {
                        badge.className = 'text-[10px] px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200/70 font-semibold flex items-center gap-1';
                        badge.innerHTML = '<i class="bi bi-exclamation-circle"></i> Gagal memuat';
                    }
                    ['Ketua', 'Sekretaris', 'Bendahara'].forEach(role => setManualMode(role, true));
                }
            })
            .catch(() => {
                if (requestId !== activeCabangPemudaRequestId) return;
                if (badge) {
                    badge.className = 'text-[10px] px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200/70 font-semibold flex items-center gap-1';
                    badge.innerHTML = '<i class="bi bi-exclamation-circle"></i> Gangguan koneksi';
                }
                ['Ketua', 'Sekretaris', 'Bendahara'].forEach(role => setManualMode(role, true));
            });
    }

    function setupPengurusRole(role, list, currentValue) {
        const select = document.getElementById(`editSelect${role}Pemuda`);
        const input = document.getElementById(`editInput${role}Pemuda`);
        const hidden = document.getElementById(`edit${role}Pemuda`);

        if (!select || !input || !hidden) return;

        select.innerHTML = '';
        select.disabled = false;

        if (list.length === 0) {
            setManualMode(role, true, 'Cabang ini belum memiliki data pemuda terdaftar');
            return;
        }

        const defOpt = document.createElement('option');
        defOpt.value = '';
        defOpt.textContent = '-- Pilih dari Data Pemuda Cabang --';
        select.appendChild(defOpt);

        let isCurrentMatched = false;

        if (currentValue && !list.some(p => p.name === currentValue)) {
            const curOpt = document.createElement('option');
            curOpt.value = currentValue;
            curOpt.textContent = `${currentValue} (Tersimpan Saat Ini)`;
            curOpt.selected = true;
            select.appendChild(curOpt);
            isCurrentMatched = true;
        }

        list.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.name;
            opt.dataset.phone = p.phone || '';
            const genderLabel = p.gender === 'L' ? 'Ikhwan' : 'Akhwat';
            const phoneLabel = p.phone ? ` • ${p.phone}` : '';
            opt.textContent = `${p.name} (${genderLabel}${phoneLabel})`;
            if (!isCurrentMatched && p.name === currentValue) {
                opt.selected = true;
                isCurrentMatched = true;
            }
            select.appendChild(opt);
        });

        const manualOpt = document.createElement('option');
        manualOpt.value = '__manual__';
        manualOpt.textContent = '-- [Ketik Nama Manual / Lainnya] --';
        select.appendChild(manualOpt);

        setManualMode(role, false);
    }

    function onSelectPengurusChange(role) {
        const select = document.getElementById(`editSelect${role}Pemuda`);
        const input = document.getElementById(`editInput${role}Pemuda`);
        const hidden = document.getElementById(`edit${role}Pemuda`);

        if (!select || !input || !hidden) return;

        if (select.value === '__manual__') {
            setManualMode(role, true);
            input.focus();
            return;
        }

        hidden.value = select.value;
        input.value = select.value;

        if (role === 'Ketua') {
            const selectedOpt = select.options[select.selectedIndex];
            const phone = selectedOpt?.dataset?.phone || '';
            const noWaInput = document.getElementById('editNoWaPemuda');
            const hint = document.getElementById('hintNoWaPemudaAuto');

            if (phone && noWaInput) {
                noWaInput.value = phone;
                if (hint) hint.classList.remove('hidden');
            } else if (hint) {
                hint.classList.add('hidden');
            }
        }
    }

    function onInputPengurusChange(role) {
        const input = document.getElementById(`editInput${role}Pemuda`);
        const hidden = document.getElementById(`edit${role}Pemuda`);
        if (input && hidden) {
            hidden.value = input.value;
        }
    }

    function toggleManualPengurus(role) {
        const wrapperInput = document.getElementById(`wrapperInput${role}`);
        const isCurrentlyManual = wrapperInput && !wrapperInput.classList.contains('hidden');
        setManualMode(role, !isCurrentlyManual);
    }

    function setManualMode(role, isManual, placeholderMsg = '') {
        const wrapperSelect = document.getElementById(`wrapperSelect${role}`);
        const wrapperInput = document.getElementById(`wrapperInput${role}`);
        const btn = document.getElementById(`btnToggleManual${role}`);
        const input = document.getElementById(`editInput${role}Pemuda`);
        const select = document.getElementById(`editSelect${role}Pemuda`);
        const hidden = document.getElementById(`edit${role}Pemuda`);

        if (!wrapperSelect || !wrapperInput) return;

        if (isManual) {
            wrapperSelect.classList.add('hidden');
            wrapperInput.classList.remove('hidden');
            if (btn) btn.innerHTML = '<i class="bi bi-list-ul"></i> Pilih dari Daftar';
            if (placeholderMsg && input) input.placeholder = placeholderMsg;
            if (input && hidden) hidden.value = input.value;
        } else {
            wrapperInput.classList.add('hidden');
            wrapperSelect.classList.remove('hidden');
            if (btn) btn.innerHTML = '<i class="bi bi-pencil"></i> Ketik Manual';
            if (select && hidden) {
                if (select.value === '__manual__') {
                    select.value = '';
                }
                hidden.value = select.value;
            }
        }
    }

    function viewDetailCabang(id) {
        fetch(`{{ url('admin/cabang/detail') }}/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const c = res.data;
                    const safeName = escapeHtmlCabang(c.name);
                    const safeCode = escapeHtmlCabang(c.code);
                    const safeWilayah = escapeHtmlCabang(c.wilayah?.name || '-');
                    const safeWilayahCode = escapeHtmlCabang(c.wilayah?.code || '');
                    const safePimpinan = escapeHtmlCabang(c.pimpinan_nama || '-');
                    const safeNoWa = escapeHtmlCabang(c.no_wa || '-');
                    const safeAlamat = escapeHtmlCabang(c.alamat || '-');
                    const safeMapsUrl = c.maps_url ? escapeHtmlCabang(c.maps_url) : null;
                    const safeDesc = escapeHtmlCabang(c.description || '');
                    const safeHari = escapeHtmlCabang(c.gelombang_hari || '-');
                    const safeJam = escapeHtmlCabang(c.gelombang_jam || '-');
                    const safeUstadz = escapeHtmlCabang(c.gelombang_ustadz || '-');
                    const safeKetuaPemuda = escapeHtmlCabang(c.ketua_pemuda || '-');
                    const safeSekretarisPemuda = escapeHtmlCabang(c.sekretaris_pemuda || '-');
                    const safeBendaharaPemuda = escapeHtmlCabang(c.bendahara_pemuda || '-');
                    const safeNoWaPemuda = escapeHtmlCabang(c.no_wa_pemuda || '-');
                    const isSudah = c.has_gelombang === 'sudah';
                    const pemudaCount = c.total_pemuda || 0;
                    const mtaUuid = escapeHtmlCabang(c.mta_uuid || '');
                    const mtaSync = c.mta_last_synced_at ? escapeHtmlCabang(c.mta_last_synced_at) : '';

                    let waLink = '';
                    if (c.no_wa) {
                        const cleanPhone = c.no_wa.replace(/\D/g, '').replace(/^0/, '62');
                        waLink = `https://wa.me/${cleanPhone}`;
                    }

                    let waPemudaLink = '';
                    if (c.no_wa_pemuda) {
                        const cleanPhonePemuda = c.no_wa_pemuda.replace(/\D/g, '').replace(/^0/, '62');
                        waPemudaLink = `https://wa.me/${cleanPhonePemuda}`;
                    }

                    document.getElementById('detailCabangTitle').textContent = c.name;
                    document.getElementById('detailCabangBody').innerHTML = `
                        <div class="space-y-3.5">
                            <!-- Identitas Cabang & Wilayah -->
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <div class="grid grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Nama Cabang</span>
                                        <span class="font-bold text-slate-900 text-sm">${safeName}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Kode Cabang</span>
                                        <span class="font-mono font-bold text-slate-700 bg-white px-2 py-0.5 rounded border inline-block text-[11px]">${safeCode || '<span class="text-slate-400 font-normal italic">Tidak ada kode</span>'}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Wilayah</span>
                                        <span class="font-semibold text-slate-800">${safeWilayah} ${safeWilayahCode ? `(${safeWilayahCode})` : ''}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Total Pemuda Terdaftar</span>
                                        <a href="{{ route('admin.pemuda.index') }}?cabang_id=${c.id}" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs transition border border-emerald-200/70">
                                            <i class="bi bi-people-fill"></i> ${pemudaCount} Pemuda
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Kepemimpinan & Kontak Cabang -->
                            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                                <h4 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <i class="bi bi-person-badge text-indigo-600"></i> Pimpinan &amp; Kontak Cabang
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Pimpinan / Ketua:</span>
                                        <strong class="text-slate-800">${safePimpinan}</strong>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Nomor WhatsApp:</span>
                                        ${waLink ? `
                                            <a href="${waLink}" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 hover:underline font-semibold">
                                                <i class="bi bi-whatsapp"></i> ${safeNoWa}
                                            </a>
                                        ` : `
                                            <span class="text-slate-600">${safeNoWa}</span>
                                        `}
                                    </div>
                                </div>
                            </div>

                            <!-- Pengurus / Koordinator Pemuda Cabang -->
                            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                                <h4 class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <i class="bi bi-people-fill"></i> Pengurus / Koordinator Pemuda (Kajian Gelombang)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Ketua / Koordinator:</span>
                                        <strong class="text-slate-800">${safeKetuaPemuda}</strong>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Nomor WhatsApp Pemuda:</span>
                                        ${waPemudaLink ? `
                                            <a href="${waPemudaLink}" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 hover:underline font-semibold">
                                                <i class="bi bi-whatsapp"></i> ${safeNoWaPemuda}
                                            </a>
                                        ` : `
                                            <span class="text-slate-600">${safeNoWaPemuda}</span>
                                        `}
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Sekretaris Pemuda:</span>
                                        <span class="font-semibold text-slate-800">${safeSekretarisPemuda}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Bendahara Pemuda:</span>
                                        <span class="font-semibold text-slate-800">${safeBendaharaPemuda}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Lokasi & Peta Cabang -->
                            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                                <h4 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <i class="bi bi-geo-alt-fill text-rose-600"></i> Lokasi Cabang &amp; Peta
                                </h4>
                                <div class="text-xs space-y-2">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block mb-0.5">Alamat Lengkap / Sekretariat:</span>
                                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/60 text-slate-700 text-[11px] leading-relaxed">
                                            ${safeAlamat}
                                        </div>
                                    </div>
                                    ${safeMapsUrl ? `
                                        <div class="flex items-center justify-between pt-1">
                                            <span class="text-[10px] text-slate-400">Tautan Google Maps:</span>
                                            <a href="${safeMapsUrl}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-[11px] transition border border-rose-200/80">
                                                <i class="bi bi-map-fill"></i> Buka Google Maps <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                            </a>
                                        </div>
                                    ` : `
                                        <div class="text-[11px] text-slate-400 italic">Belum ada tautan Google Maps.</div>
                                    `}
                                </div>
                            </div>

                            <!-- Sesi Kajian Pemuda Cabang -->
                            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                                <div class="flex items-center justify-between mb-2 pb-1.5 border-b border-slate-100">
                                    <h4 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="bi bi-book-half text-emerald-600"></i> Sesi Kajian Pemuda
                                    </h4>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full ${isSudah ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'} font-bold text-[10px]">
                                        <i class="bi ${isSudah ? 'bi-check-circle-fill' : 'bi-dash-circle'}"></i>
                                        ${isSudah ? 'Sudah Ada Kajian' : 'Belum Ada Kajian'}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs pt-1">
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                        <span class="text-[10px] text-slate-400 block font-medium">Hari Kajian</span>
                                        <span class="font-bold text-slate-800">${safeHari}</span>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                        <span class="text-[10px] text-slate-400 block font-medium">Waktu / Jam</span>
                                        <span class="font-bold text-slate-800">${safeJam}</span>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 sm:col-span-1">
                                        <span class="text-[10px] text-slate-400 block font-medium">Ustadz Pengampu</span>
                                        <span class="font-bold text-slate-800">${safeUstadz}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Deskripsi / Catatan Tambahan -->
                            ${safeDesc ? `
                            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
                                <h4 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                    <i class="bi bi-card-text text-amber-600"></i> Deskripsi &amp; Catatan
                                </h4>
                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-slate-700 text-[11px] leading-relaxed">
                                    ${safeDesc}
                                </div>
                            </div>
                            ` : ''}

                            <!-- Integrasi MTA Pusat -->
                            <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/70 text-[11px]">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1.5 font-semibold text-slate-600">
                                        <i class="bi bi-shield-check text-indigo-500"></i> Integrasi MTA Pusat
                                    </span>
                                    <span class="font-mono text-[10px] text-slate-500">${mtaUuid || '<span class="text-slate-400 italic">Belum terhubung</span>'}</span>
                                </div>
                                ${mtaSync ? `
                                <div class="mt-1 text-[10px] text-slate-400">
                                    Terakhir sinkronisasi: ${mtaSync}
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                    openModal('modalDetailCabang');
                }
            });
    }
    function changePerPageCabang(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
</script>
@endsection
