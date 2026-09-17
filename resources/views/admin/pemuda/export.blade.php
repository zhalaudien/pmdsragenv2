@extends('admin.layouts.main')

@section('title', 'Export Data Pemuda')

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <a href="{{ route('admin.pemuda.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-600 font-bold text-xs transition border border-slate-200 mb-2">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Daftar Pemuda</span>
        </a>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Export Kustom Data Pemuda</h2>
        <p class="text-xs text-slate-500 mt-0.5">Pilih kolom data, format file, dan filter yang ingin diekspor ke format Excel (XLSX) atau CSV.</p>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit" form="exportForm" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2">
            <i class="bi bi-download"></i>
            <span>Unduh Berkas Ekspor</span>
        </button>
    </div>
</div>

<form id="exportForm" action="{{ route('admin.pemuda.export.download') }}" method="POST" target="_blank" class="space-y-6">
    @csrf

    <!-- 1. PILIHAN KOLOM (DATA ELEMENTS) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-black flex items-center justify-center">1</span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pilih Kolom Spreadsheet</h3>
            </div>
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <span class="text-slate-400 font-semibold mr-1">Preset:</span>
                <button type="button" onclick="selectPreset('default')" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[11px] transition">
                    Standar
                </button>
                <button type="button" onclick="selectPreset('all')" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-[11px] transition">
                    Lengkap (Semua)
                </button>
                <button type="button" onclick="selectPreset('contact')" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-[11px] transition">
                    Kontak &amp; Alamat
                </button>
                <button type="button" onclick="selectPreset('potensi')" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-[11px] transition">
                    Bakat &amp; Potensi
                </button>
                <button type="button" onclick="deselectAllCols()" class="px-2.5 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-semibold text-[11px] transition">
                    Kosongkan
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-xs">
            @foreach($categorizedColumns as $catKey => $cat)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <h4 class="font-bold text-slate-800 mb-3 flex items-center gap-1.5 pb-2 border-b border-slate-200">
                        <i class="bi bi-folder-fill text-emerald-600"></i>
                        <span>{{ $cat['category_name'] ?? $cat['label'] ?? '' }}</span>
                    </h4>
                    <div class="space-y-2">
                        @foreach($cat['columns'] as $colKey => $colLabel)
                            <label class="flex items-center gap-2 p-1.5 hover:bg-white rounded-lg cursor-pointer">
                                <input type="checkbox" name="columns[]" value="{{ $colKey }}" class="col-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" {{ in_array($colKey, $presets['default'] ?? []) ? 'checked' : '' }}>
                                <span class="text-slate-700">{{ $colLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 2. FILTER DATA KRITERIA -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-2 pb-4 mb-4 border-b border-slate-100">
            <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-black flex items-center justify-center">2</span>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Kriteria Filter Ekspor</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Wilayah</label>
                <select name="wilayah_id" id="exportWilayah" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Semua Wilayah --</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Cabang</label>
                <select name="cabang_id" id="exportCabang" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Semua Cabang --</option>
                    @foreach($cabangList as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin</label>
                <select name="gender" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Semua Gender --</option>
                    <option value="L">Laki-laki (L)</option>
                    <option value="P">Perempuan (P)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Status Verifikasi</label>
                <select name="status_verifikasi" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Semua Status --</option>
                    <option value="verified">Hanya Terverifikasi</option>
                    <option value="pending">Belum Terverifikasi (Pending)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Jenjang Pendidikan</label>
                <select name="education_level_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Semua Jenjang --</option>
                    @foreach($educationLevels as $el)
                        <option value="{{ $el->id }}">{{ $el->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Status Pekerjaan</label>
                <select name="job_status_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Semua Pekerjaan --</option>
                    @foreach($jobStatuses as $js)
                        <option value="{{ $js->id }}">{{ $js->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Status Data</label>
                <select name="status_data" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="active">Data Aktif Saja</option>
                    <option value="all">Semua Data (Termasuk Arsip)</option>
                    <option value="archived">Data Arsip Saja</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Format Unduhan</label>
                <select name="format" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="csv">CSV (.csv)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- SUBMIT -->
    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('admin.pemuda.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
            Batal
        </a>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2">
            <i class="bi bi-file-earmark-excel-fill"></i>
            <span>Mulai Unduh File</span>
        </button>
    </div>
</form>

@endsection

@section('scripts')
<script>
    const presets = @json($presets);

    function selectPreset(name) {
        deselectAllCols();
        if (name === 'all') {
            document.querySelectorAll('.col-checkbox').forEach(cb => cb.checked = true);
            return;
        }
        if (presets[name]) {
            presets[name].forEach(col => {
                const cb = document.querySelector(`.col-checkbox[value="${col}"]`);
                if (cb) cb.checked = true;
            });
        }
    }

    function deselectAllCols() {
        document.querySelectorAll('.col-checkbox').forEach(cb => cb.checked = false);
    }

    const exportWilayah = document.getElementById('exportWilayah');
    const exportCabang  = document.getElementById('exportCabang');
    if (exportWilayah && exportCabang) {
        exportWilayah.addEventListener('change', function () {
            const wId = this.value;
            exportCabang.innerHTML = '<option value="">-- Semua Cabang --</option>';
            if (!wId) return;

            fetch(`{{ url('api/cabang') }}/${wId}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        exportCabang.appendChild(opt);
                    });
                });
        });
    }
</script>
@endsection
