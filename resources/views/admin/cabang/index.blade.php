@extends('admin.layouts.main')

@section('title', 'Manajemen Cabang')

@section('content')

<!-- HEADER & ACTIONS -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Manajemen Cabang Binaan</h2>
        <p class="text-xs text-slate-500 mt-0.5">Kelola informasi 61+ cabang MTA, jadwal gelombang pembinaan, dan lokasi peta se-Kabupaten Sragen.</p>
    </div>

    <button type="button" onclick="openModal('modalAddCabang')" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2 self-start sm:self-auto">
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Cabang Baru</span>
    </button>
</div>

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
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Sudah Bergelombang</div>
            <div class="text-xl font-black text-emerald-600">{{ number_format($totalSudahGelombang) }} Cabang</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
            <i class="bi bi-clock"></i>
        </div>
        <div>
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Belum Bergelombang</div>
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
            <label class="block font-bold text-slate-700 uppercase mb-1">Status Gelombang</label>
            <select name="has_gelombang" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                <option value="">-- Semua Status --</option>
                <option value="sudah" {{ ($selectedGelombang ?? '') === 'sudah' ? 'selected' : '' }}>Sudah Bergelombang</option>
                <option value="belum" {{ ($selectedGelombang ?? '') === 'belum' ? 'selected' : '' }}>Belum Bergelombang</option>
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
                    <th class="py-3 px-4">Gelombang Pengajian</th>
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
                                    <i class="bi bi-check-circle-fill"></i> Sudah Bergelombang
                                </span>
                                @if($c->gelombang_hari)
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $c->gelombang_hari }} - {{ $c->gelombang_jam }}</div>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold text-[10px]">
                                    Belum Bergelombang
                                </span>
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
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95">
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
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
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

                <div class="col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Cabang</label>
                    <textarea name="alamat" rows="2" placeholder="Dukuh, Desa, Kec..." class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Gelombang <span class="text-red-500">*</span></label>
                    <select name="has_gelombang" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        <option value="belum">Belum Bergelombang</option>
                        <option value="sudah">Sudah Bergelombang</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Hari Gelombang</label>
                    <input type="text" name="gelombang_hari" placeholder="Ahad Pagi" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jam Gelombang</label>
                    <input type="text" name="gelombang_jam" placeholder="06:00 - 07:30" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Ustadz Pengisi</label>
                    <input type="text" name="gelombang_ustadz" placeholder="Nama Ustadz" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalAddCabang')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-md">Simpan Cabang</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT CABANG -->
<div id="modalEditCabang" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95">
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
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
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

                <div class="col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Cabang</label>
                    <textarea name="alamat" id="editAlamat" rows="2" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Gelombang <span class="text-red-500">*</span></label>
                    <select name="has_gelombang" id="editHasGelombang" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        <option value="belum">Belum Bergelombang</option>
                        <option value="sudah">Sudah Bergelombang</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Hari Gelombang</label>
                    <input type="text" name="gelombang_hari" id="editGelombangHari" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jam Gelombang</label>
                    <input type="text" name="gelombang_jam" id="editGelombangJam" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Ustadz Pengisi</label>
                    <input type="text" name="gelombang_ustadz" id="editGelombangUstadz" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditCabang')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold transition shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DETAIL CABANG -->
<div id="modalDetailCabang" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 text-xs">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-info-circle text-sky-600"></i>
                <span id="detailCabangTitle">Detail Cabang</span>
            </h3>
            <button type="button" onclick="closeModal('modalDetailCabang')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="detailCabangBody" class="space-y-2.5 divide-y divide-slate-100">
            <!-- Content loaded via JS -->
        </div>

        <div class="pt-4 mt-4 border-t border-slate-100 text-right">
            <button type="button" onclick="closeModal('modalDetailCabang')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Tutup</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function editCabang(c) {
        document.getElementById('formEditCabang').action = `{{ url('admin/cabang/update') }}/${c.id}`;
        document.getElementById('editWilayahId').value = c.wilayah_id;
        document.getElementById('editCabangName').value = c.name;
        document.getElementById('editCabangCode').value = c.code || '';
        document.getElementById('editPimpinanNama').value = c.pimpinan_nama || '';
        document.getElementById('editNoWa').value = c.no_wa || '';
        document.getElementById('editAlamat').value = c.alamat || '';
        document.getElementById('editHasGelombang').value = c.has_gelombang || 'belum';
        document.getElementById('editGelombangHari').value = c.gelombang_hari || '';
        document.getElementById('editGelombangJam').value = c.gelombang_jam || '';
        document.getElementById('editGelombangUstadz').value = c.gelombang_ustadz || '';
        openModal('modalEditCabang');
    }

    function viewDetailCabang(id) {
        fetch(`{{ url('admin/cabang/detail') }}/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const c = res.data;
                    document.getElementById('detailCabangTitle').textContent = c.name;
                    document.getElementById('detailCabangBody').innerHTML = `
                        <div class="py-1.5 flex justify-between"><span class="text-slate-400">Wilayah:</span> <strong class="text-slate-800">${c.wilayah?.name || '-'}</strong></div>
                        <div class="py-1.5 flex justify-between"><span class="text-slate-400">Kode:</span> <strong class="font-mono text-slate-800">${c.code || '-'}</strong></div>
                        <div class="py-1.5 flex justify-between"><span class="text-slate-400">Pimpinan:</span> <strong class="text-slate-800">${c.pimpinan_nama || '-'}</strong></div>
                        <div class="py-1.5 flex justify-between"><span class="text-slate-400">No. WhatsApp:</span> <span class="text-emerald-600 font-semibold">${c.no_wa || '-'}</span></div>
                        <div class="py-1.5 flex justify-between"><span class="text-slate-400">Status Gelombang:</span> <span class="font-bold ${c.has_gelombang === 'sudah' ? 'text-emerald-600' : 'text-slate-500'}">${c.has_gelombang === 'sudah' ? 'Sudah Bergelombang' : 'Belum'}</span></div>
                        <div class="py-1.5 flex justify-between"><span class="text-slate-400">Jadwal:</span> <span>${c.gelombang_hari || '-'} (${c.gelombang_jam || '-'})</span></div>
                        <div class="py-1.5 flex justify-between"><span class="text-slate-400">Ustadz:</span> <span>${c.gelombang_ustadz || '-'}</span></div>
                        <div class="py-1.5 flex justify-between"><span class="text-slate-400">Total Pemuda:</span> <strong class="text-red-600">${c.total_pemuda || 0} Pemuda</strong></div>
                        <div class="py-1.5"><span class="text-slate-400 block mb-1">Alamat:</span> <div class="bg-slate-50 p-2 rounded-lg border text-slate-700">${c.alamat || '-'}</div></div>
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
