@extends('admin.layouts.main')

@section('title', 'Import Data Pemuda dari Excel')

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <a href="{{ route('admin.pemuda.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-600 font-bold text-xs transition border border-slate-200 mb-2">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Daftar Pemuda</span>
        </a>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Import Data Pemuda dari Excel</h2>
        <p class="text-xs text-slate-500 mt-0.5">Unggah berkas spreadsheet (.xlsx, .xls, atau .csv) untuk memasukkan data pemuda secara massal.</p>
    </div>

    <a href="{{ route('admin.pemuda.template-import') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2">
        <i class="bi bi-file-earmark-arrow-down"></i>
        <span>Unduh Template Excel</span>
    </a>
</div>

<!-- IMPORT ERROR DETAILS -->
@if(session('import_errors'))
    <div class="mb-6 rounded-3xl bg-red-50 border border-red-200 p-5 text-xs text-red-800">
        <div class="flex items-center gap-2 font-bold mb-2">
            <i class="bi bi-exclamation-triangle-fill text-red-600 text-base"></i>
            <span>Detail Kesalahan Validasi Baris Excel ({{ count(session('import_errors')) }} Isu):</span>
        </div>
        <div class="max-h-60 overflow-y-auto space-y-1 pl-4 pr-2">
            @foreach(session('import_errors') as $err)
                <div class="flex items-start gap-2 text-red-700">
                    <i class="bi bi-x-circle text-red-500 flex-shrink-0 mt-0.5"></i>
                    <span>{{ $err }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- LEFT: FORM UPLOAD -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
            <i class="bi bi-cloud-arrow-up text-red-600 text-base"></i>
            <span>Unggah Berkas Spreadsheet</span>
        </h3>

        <!-- DOWNLOAD TEMPLATE CALLOUT -->
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div>
                <h4 class="font-bold text-emerald-900 flex items-center gap-1.5 mb-1">
                    <i class="bi bi-file-earmark-excel-fill text-emerald-600"></i>
                    <span>1. Gunakan Format Template Resmi</span>
                </h4>
                <p class="text-emerald-700 text-[11px] leading-relaxed">
                    Wajib: <strong>Nama Lengkap</strong>, <strong>Cabang</strong>, <strong>Jenis Kelamin (L/P)</strong>, dan <strong>Tanggal Lahir</strong>.
                </p>
            </div>
            <a href="{{ route('admin.pemuda.template-import') }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition whitespace-nowrap self-start sm:self-auto shadow-sm">
                Unduh Format (.xlsx)
            </a>
        </div>

        <form action="{{ route('admin.pemuda.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-5 text-xs">
            @csrf

            <!-- FILE INPUT -->
            <div>
                <label for="file_excel" class="block font-bold text-slate-700 uppercase mb-1">
                    2. Pilih File Excel / CSV <span class="text-red-500">*</span>
                </label>
                <input type="file" name="file_excel" id="file_excel" accept=".xlsx, .xls, .csv" required
                       class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-600 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                <p class="text-[11px] text-slate-400 mt-1">Ukuran berkas maksimal 10MB.</p>
            </div>

            <!-- OPTIONS -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <h4 class="font-bold text-slate-700 uppercase text-[11px]">Opsi Proses Import</h4>
                
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" name="skip_errors" value="1" checked class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-500">
                    <div>
                        <span class="font-bold text-slate-800">Lewati Baris Error</span>
                        <p class="text-[11px] text-slate-500">Tetap simpan baris data yang valid meskipun ada baris lain yang gagal.</p>
                    </div>
                </label>

                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" name="update_duplicates" value="1" class="mt-0.5 rounded border-slate-300 text-red-600 focus:ring-red-500">
                    <div>
                        <span class="font-bold text-slate-800">Perbarui Data Duplikat</span>
                        <p class="text-[11px] text-slate-500">Jika nama, cabang &amp; tanggal lahir sama, perbarui data yang sudah ada.</p>
                    </div>
                </label>
            </div>

            <!-- SUBMIT -->
            <div class="pt-2">
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center justify-center gap-2">
                    <i class="bi bi-cloud-arrow-up-fill text-base"></i>
                    <span>Mulai Proses Import Data</span>
                </button>
            </div>
        </form>
    </div>

    <!-- RIGHT: INSTRUCTIONS -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4 text-xs">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
            <i class="bi bi-info-circle-fill text-sky-600 text-base"></i>
            <span>Petunjuk Pengisian Data</span>
        </h3>

        <div class="space-y-3 text-slate-600 leading-relaxed text-[11px]">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <strong class="text-slate-800 block mb-1">Kolom Cabang:</strong>
                Tuliskan nama cabang persis seperti di sistem (contoh: <em>Masaran 1</em>, <em>Gesi</em>, <em>Sragen Kota</em>).
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <strong class="text-slate-800 block mb-1">Format Tanggal Lahir:</strong>
                Gunakan format standar tahun-bulan-tanggal <code>YYYY-MM-DD</code> (contoh: <em>2000-08-17</em>) atau format date pada Microsoft Excel.
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <strong class="text-slate-800 block mb-1">Jenis Kelamin:</strong>
                Cukup isi huruf <code>L</code> (Laki-laki) atau <code>P</code> (Perempuan).
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <strong class="text-slate-800 block mb-1">Keahlian &amp; Minat:</strong>
                Dapat dipisahkan dengan tanda koma (contoh: <em>Desain Grafis, Web Developer, Public Speaking</em>).
            </div>
        </div>
    </div>
</div>

@endsection
