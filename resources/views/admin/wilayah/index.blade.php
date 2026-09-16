@extends('admin.layouts.main')

@section('title', 'Master Wilayah')

@section('content')

<!-- HEADER & ACTIONS -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Master Wilayah Koordinasi</h2>
        <p class="text-xs text-slate-500 mt-0.5">Kelola daftar 4 wilayah koordinasi pembinaan pemuda se-Kabupaten Sragen.</p>
    </div>

    <button type="button" onclick="openModal('modalAddWilayah')" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2 self-start sm:self-auto">
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Wilayah</span>
    </button>
</div>

<!-- WILAYAH LIST TABLE -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="bi bi-geo-alt-fill text-red-600"></i>
            <span>Daftar Wilayah (Total: {{ count($wilayahList) }})</span>
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                    <th class="py-3 px-4 w-12">ID</th>
                    <th class="py-3 px-4 w-32">Kode</th>
                    <th class="py-3 px-4">Nama Wilayah</th>
                    <th class="py-3 px-4">Deskripsi / Cakupan</th>
                    <th class="py-3 px-4 text-center">Total Cabang</th>
                    <th class="py-3 px-4 text-center">Total Pemuda</th>
                    <th class="py-3 px-4 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($wilayahList as $w)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="py-3 px-4 font-mono text-slate-400">{{ $w->id }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-red-50 text-red-700 font-mono font-bold text-xs border border-red-200">
                                {{ $w->code }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $w->name }}</td>
                        <td class="py-3 px-4 text-slate-500">{{ $w->description ?: '-' }}</td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('admin.cabang.index', ['wilayah_id' => $w->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-700 hover:bg-sky-100 font-semibold text-[11px] transition">
                                <i class="bi bi-diagram-3"></i> {{ $w->total_cabang }} Cabang
                            </a>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('admin.pemuda.index', ['wilayah_id' => $w->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-[11px] transition">
                                <i class="bi bi-people"></i> {{ $w->total_pemuda }} Pemuda
                            </a>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                <button type="button" onclick="editWilayah({{ json_encode($w) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-amber-50 hover:text-amber-600 text-slate-600 transition" title="Edit Wilayah">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('admin.wilayah.delete', $w->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus wilayah {{ $w->name }}?')">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-400 transition" title="Hapus Wilayah">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Belum ada data wilayah.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH WILAYAH -->
<div id="modalAddWilayah" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-plus-circle text-red-600"></i>
                <span>Tambah Wilayah Baru</span>
            </h3>
            <button type="button" onclick="closeModal('modalAddWilayah')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.wilayah.simpan') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Kode Wilayah <span class="text-red-500">*</span></label>
                <input type="text" name="code" placeholder="Contoh: WIL1" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 uppercase focus:ring-red-500 focus:border-red-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Wilayah <span class="text-red-500">*</span></label>
                <input type="text" name="name" placeholder="Contoh: Wilayah 1 (Sragen Barat)" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi / Cakupan Kecamatan</label>
                <textarea name="description" rows="3" placeholder="Masaran, Gemolong, Plupuh, dll" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalAddWilayah')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-md">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT WILAYAH -->
<div id="modalEditWilayah" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-pencil-square text-amber-500"></i>
                <span>Edit Data Wilayah</span>
            </h3>
            <button type="button" onclick="closeModal('modalEditWilayah')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="formEditWilayah" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Kode Wilayah <span class="text-red-500">*</span></label>
                <input type="text" name="code" id="editCode" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 uppercase focus:ring-red-500 focus:border-red-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Wilayah <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="editName" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi / Cakupan Kecamatan</label>
                <textarea name="description" id="editDescription" rows="3" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditWilayah')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold transition shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function editWilayah(w) {
        document.getElementById('formEditWilayah').action = `{{ url('admin/wilayah/update') }}/${w.id}`;
        document.getElementById('editCode').value = w.code;
        document.getElementById('editName').value = w.name;
        document.getElementById('editDescription').value = w.description || '';
        openModal('modalEditWilayah');
    }
</script>
@endsection
