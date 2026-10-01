@extends('admin.layouts.main')

@section('content')
<div class="space-y-6">

    <!-- HEADER TITLE & BREADCRUMB -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600 transition">Dashboard</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-600 font-semibold">Guru Daerah Muda</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-red-600 text-white flex items-center justify-center text-sm shadow-md">
                    <i class="bi bi-mortarboard-fill"></i>
                </span>
                <span>Manajemen Guru Daerah Muda (GDM)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl">
                Kelola data kader mubaligh muda (GDM), asal cabang, biodata, serta riwayat penugasan kajian pemuda cabang se-Kabupaten Sragen.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <button type="button" onclick="openModalTambahGdm()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md hover:shadow-lg flex-shrink-0">
                <i class="bi bi-person-plus-fill"></i>
                <span>Tambah GDM Baru</span>
            </button>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs animate-in fade-in">
            <i class="bi bi-check-circle-fill text-emerald-600 text-base flex-shrink-0 mt-0.5"></i>
            <div class="flex-1 font-semibold leading-relaxed">
                {{ session('success') }}
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-xs">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs animate-in fade-in">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base flex-shrink-0 mt-0.5"></i>
            <div class="flex-1 font-semibold leading-relaxed">
                {{ session('error') }}
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-xs">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <!-- KPI STATS CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total GDM -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total GDM</span>
                <span class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-mortarboard-fill"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalGdm) }}</div>
            <div class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-500 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                <span>Kader mubaligh muda</span>
            </div>
        </div>

        <!-- GDM Aktif -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Aktif</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-person-check-fill"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalGdmAktif) }}</div>
            <div class="mt-1 flex items-center gap-1.5 text-[11px] text-emerald-700 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Siap bertugas kajian</span>
            </div>
        </div>

        <!-- Penugasan Aktif -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penugasan Aktif</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-calendar-check-fill"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalPenugasanAktif) }}</div>
            <div class="mt-1 flex items-center gap-1.5 text-[11px] text-indigo-700 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                <span>Kajian aktif terjadwal</span>
            </div>
        </div>

        <!-- Cabang Sasaran Kajian -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cabang Sasaran</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-geo-alt-fill"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalCabangSasaran) }}</div>
            <div class="mt-1 flex items-center gap-1.5 text-[11px] text-amber-700 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                <span>Dari {{ number_format($totalRiwayat) }} riwayat tugas</span>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH CARD -->
    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
        <form action="{{ route('admin.gdm.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs items-end">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block font-bold text-slate-700 uppercase mb-1">Cari GDM</label>
                <div class="relative">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama, tempat lahir, alamat, WA..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                </div>
            </div>

            <!-- Cabang Asal -->
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Cabang Asal</label>
                <select name="cabang_asal_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                    <option value="">Semua Cabang Asal</option>
                    @foreach($cabangList as $c)
                        <option value="{{ $c->id }}" {{ (string)$selectedCabangAsal === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Cabang Tempat Penugasan -->
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Tempat Penugasan</label>
                <select name="cabang_penugasan_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                    <option value="">Semua Cabang Tugas</option>
                    @foreach($cabangList as $c)
                        <option value="{{ $c->id }}" {{ (string)$selectedCabangTugas === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tahun Penugasan -->
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Tahun Penugasan</label>
                <select name="tahun" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 text-xs">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $th)
                        <option value="{{ $th }}" {{ (string)$selectedTahun === (string)$th ? 'selected' : '' }}>
                            Tahun {{ $th }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="bi bi-filter"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('admin.gdm.index') }}" class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition flex items-center justify-center" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- MAIN TABLE / CARD CONTAINER -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Daftar Guru Daerah Muda (GDM)</h3>
                <p class="text-xs text-slate-400">Total <span class="font-bold text-slate-700">{{ $gdmList->total() }}</span> kader GDM terdaftar</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">Tampilkan:</span>
                <select onchange="changePerPageGdm(this.value)" class="py-1 px-2.5 rounded-lg border border-slate-300 text-xs bg-slate-50">
                    <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama &amp; Kontak</th>
                        <th class="py-3 px-4">Tempat, Tanggal Lahir</th>
                        <th class="py-3 px-4">Cabang Asal</th>
                        <th class="py-3 px-4">Alamat Domisili</th>
                        <th class="py-3 px-4">Penugasan Terkini</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($gdmList as $index => $gdm)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                {{ $gdmList->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                    <span>{{ $gdm->nama }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($gdm->no_wa)
                                        <a href="{{ $gdm->wa_link }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 hover:text-emerald-700 font-semibold" title="Chat WhatsApp">
                                            <i class="bi bi-whatsapp"></i> {{ $gdm->no_wa }}
                                        </a>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Tanpa nomor WA</span>
                                    @endif

                                    <!-- Sumber Data Badge -->
                                    @if($gdm->sumber_data === 'pemuda')
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-50 text-sky-700 border border-sky-200" title="Diambil dari Data Pemuda">
                                            <i class="bi bi-people-fill text-[8px]"></i> Pemuda
                                        </span>
                                    @elseif($gdm->sumber_data === 'warga')
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200" title="Diambil dari Warga MTA Pusat">
                                            <i class="bi bi-cloud-check-fill text-[8px]"></i> Warga MTA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600" title="Input Manual">
                                            Manual
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">{{ $gdm->ttl }}</div>
                                @if($gdm->usia !== null)
                                    <span class="text-[10px] text-slate-400 font-semibold mt-0.5 block">Usia: {{ $gdm->usia }} thn</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                @if($gdm->cabang)
                                    <div>{{ $gdm->cabang->name }}</div>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $gdm->cabang->wilayah?->name ?? '-' }}</span>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-[11px] text-slate-600 max-w-xs truncate" title="{{ $gdm->alamat }}">
                                {{ $gdm->alamat ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @php
                                    $penugasanTerbaru = $gdm->penugasan->first();
                                    $totalPenugasan = $gdm->penugasan->count();
                                @endphp
                                @if($penugasanTerbaru)
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[10px] {{ $penugasanTerbaru->status === 'aktif' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/70' : 'bg-slate-100 text-slate-600' }}">
                                            <i class="bi bi-geo-alt-fill"></i>
                                            <span>{{ $penugasanTerbaru->cabang?->name ?? 'Cabang' }} ({{ $penugasanTerbaru->tahun }})</span>
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <button type="button" onclick="viewDetailGdm({{ $gdm->id }})" class="text-[10px] text-indigo-600 hover:underline font-semibold">
                                            Lihat {{ $totalPenugasan }} Riwayat Penugasan
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[10px] text-slate-400 italic">Belum ada penugasan</span>
                                    <div class="mt-1">
                                        <button type="button" onclick="openQuickPenugasanModal({{ $gdm->id }}, '{{ addslashes($gdm->nama) }}')" class="text-[10px] text-indigo-600 hover:underline font-bold">
                                            + Tambah Penugasan
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($gdm->status === 'aktif')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                        <i class="bi bi-check-circle-fill"></i> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold text-[10px]">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <!-- Detail & Riwayat -->
                                    <button type="button" onclick="viewDetailGdm({{ $gdm->id }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-sky-50 hover:text-sky-600 text-slate-600 transition" title="Lihat Detail &amp; Riwayat Penugasan">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <!-- Tambah Penugasan Cepat -->
                                    <button type="button" onclick="openQuickPenugasanModal({{ $gdm->id }}, '{{ addslashes($gdm->nama) }}')" class="p-1.5 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 transition" title="Tambah Penugasan Kajian">
                                        <i class="bi bi-calendar-plus"></i>
                                    </button>
                                    <!-- Edit -->
                                    <button type="button" onclick="editGdm({{ json_encode($gdm) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-amber-50 hover:text-amber-600 text-slate-600 transition" title="Edit Biodata GDM">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <!-- Delete -->
                                    <form action="{{ route('admin.gdm.delete', $gdm->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus GDM {{ addslashes($gdm->nama) }} dan seluruh riwayat penugasannya?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 transition" title="Hapus GDM">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="bi bi-mortarboard text-4xl block mb-2 text-slate-300"></i>
                                <span>Belum ada data Guru Daerah Muda yang sesuai dengan filter pencarian.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $gdmList->links() }}
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 1: TAMBAH GDM BARU (DENGAN PILIHAN SUMBER DATA)     -->
<!-- ======================================================== -->
<div id="modalTambahGdm" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 max-h-[92vh] overflow-y-auto text-xs">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-person-plus-fill text-red-600"></i>
                <span>Tambah Guru Daerah Muda (GDM) Baru</span>
            </h3>
            <button type="button" onclick="closeModal('modalTambahGdm')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- TABS SUMBER DATA: PEMUDA, WARGA MTA, ATAU MANUAL -->
        <div class="mb-4 p-3 bg-slate-50 rounded-2xl border border-slate-200/80">
            <label class="block font-bold text-slate-700 uppercase mb-2 text-[10px]">Pilih Cara Input / Sumber Data GDM:</label>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="switchGdmSourceTab('pemuda')" id="btnTabSumberPemuda" class="py-2 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition bg-white text-indigo-700 border border-indigo-300 shadow-2xs">
                    <i class="bi bi-people-fill"></i>
                    <span>Dari Data Pemuda</span>
                </button>
                <button type="button" onclick="switchGdmSourceTab('warga')" id="btnTabSumberWarga" class="py-2 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition bg-slate-100 text-slate-600 hover:bg-white border border-transparent">
                    <i class="bi bi-cloud-arrow-down-fill"></i>
                    <span>Dari Warga MTA</span>
                </button>
                <button type="button" onclick="switchGdmSourceTab('manual')" id="btnTabSumberManual" class="py-2 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition bg-slate-100 text-slate-600 hover:bg-white border border-transparent">
                    <i class="bi bi-pencil-square"></i>
                    <span>Input Manual</span>
                </button>
            </div>

            <!-- Area Search Pemuda -->
            <div id="sectionSearchPemuda" class="mt-3 pt-3 border-t border-slate-200">
                <div class="relative">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="inputSearchPemuda" oninput="debounceSearchPemuda(this.value)" placeholder="Ketik minimal 2 huruf nama, no registrasi, atau no WA pemuda..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-indigo-200 bg-white focus:ring-indigo-500 focus:border-indigo-500 text-xs">
                </div>
                <div id="searchResultsPemuda" class="mt-2 max-h-48 overflow-y-auto space-y-1.5 hidden">
                    <!-- Loaded via JS -->
                </div>
            </div>

            <!-- Area Search Warga MTA -->
            <div id="sectionSearchWarga" class="mt-3 pt-3 border-t border-slate-200 hidden">
                <div class="relative">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="inputSearchWarga" oninput="debounceSearchWarga(this.value)" placeholder="Ketik minimal 3 huruf nama warga MTA Pusat..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-indigo-200 bg-white focus:ring-indigo-500 focus:border-indigo-500 text-xs">
                </div>
                <div id="searchResultsWarga" class="mt-2 max-h-48 overflow-y-auto space-y-1.5 hidden">
                    <!-- Loaded via JS -->
                </div>
            </div>
        </div>

        <form action="{{ route('admin.gdm.simpan') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="sumber_data" id="tambahSumberData" value="manual">
            <input type="hidden" name="pemuda_id" id="tambahPemudaId">
            <input type="hidden" name="mta_warga_uuid" id="tambahMtaUuid">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap GDM <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="tambahNama" placeholder="Nama Lengkap Guru Daerah Muda" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="tambahTempatLahir" placeholder="Kota/Kabupaten Lahir" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tambahTanggalLahir" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Cabang Asal</label>
                    <select name="cabang_id" id="tambahCabangId" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Pilih Cabang Asal --</option>
                        @foreach($cabangList as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->wilayah?->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp / HP</label>
                    <input type="tel" name="no_wa" id="tambahNoWa" placeholder="08xxxxxxxxxx" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" id="tambahAlamat" rows="2" placeholder="Dukuh, Desa, RT/RW, Kecamatan, Kabupaten..." class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status GDM</label>
                    <select name="status" id="tambahStatus" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        <option value="aktif">Aktif (Siap Bertugas)</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Catatan / Keterangan</label>
                    <input type="text" name="catatan" id="tambahCatatan" placeholder="Catatan kaderisasi, keahlian materi, dll." class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <!-- SECTION PENUGASAN AWAL (OPSIONAL) -->
            <div class="pt-3 border-t border-slate-100">
                <div class="p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <h4 class="text-[11px] font-bold text-indigo-900 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi bi-calendar2-check-fill text-indigo-600"></i> Penugasan Kajian Perdana (Opsional)
                        </h4>
                        <span class="text-[10px] text-indigo-700">Dapat ditambahkan sekarang atau nanti</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block font-bold text-indigo-950 uppercase mb-1 text-[10px]">Tahun Penugasan</label>
                            <input type="number" name="penugasan_tahun" value="{{ date('Y') }}" min="2000" max="2100" class="w-full py-2 px-3 rounded-xl border border-indigo-200 bg-white text-xs">
                        </div>

                        <div>
                            <label class="block font-bold text-indigo-950 uppercase mb-1 text-[10px]">Cabang Tempat Penugasan</label>
                            <select name="penugasan_cabang_id" class="w-full py-2 px-3 rounded-xl border border-indigo-200 bg-white text-xs">
                                <option value="">-- Pilih Cabang Kajian Tujuan --</option>
                                @foreach($cabangList as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->wilayah?->name ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-indigo-950 uppercase mb-1 text-[10px]">Hari Kajian</label>
                            <input type="text" name="penugasan_hari" placeholder="Contoh: Ahad Pagi" class="w-full py-2 px-3 rounded-xl border border-indigo-200 bg-white text-xs">
                        </div>

                        <div>
                            <label class="block font-bold text-indigo-950 uppercase mb-1 text-[10px]">Waktu / Jam Kajian</label>
                            <input type="text" name="penugasan_jam" placeholder="Contoh: 06:00 - 07:30 WIB" class="w-full py-2 px-3 rounded-xl border border-indigo-200 bg-white text-xs">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalTambahGdm')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-md">Simpan GDM Baru</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: EDIT GURU DAERAH MUDA                            -->
<!-- ======================================================== -->
<div id="modalEditGdm" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 max-h-[92vh] overflow-y-auto text-xs">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-pencil-square text-amber-500"></i>
                <span>Edit Data Guru Daerah Muda</span>
            </h3>
            <button type="button" onclick="closeModal('modalEditGdm')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="formEditGdm" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="sumber_data" id="editSumberData">
            <input type="hidden" name="pemuda_id" id="editPemudaId">
            <input type="hidden" name="mta_warga_uuid" id="editMtaUuid">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap GDM <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="editNama" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="editTempatLahir" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="editTanggalLahir" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Cabang Asal</label>
                    <select name="cabang_id" id="editCabangId" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Pilih Cabang Asal --</option>
                        @foreach($cabangList as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->wilayah?->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp / HP</label>
                    <input type="tel" name="no_wa" id="editNoWa" placeholder="08xxxxxxxxxx" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" id="editAlamat" rows="2" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status GDM</label>
                    <select name="status" id="editStatus" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                        <option value="aktif">Aktif (Siap Bertugas)</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Catatan Tambahan</label>
                    <input type="text" name="catatan" id="editCatatan" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditGdm')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold transition shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 3: DETAIL & RIWAYAT PENUGASAN KAJIAN GDM            -->
<!-- ======================================================== -->
<div id="modalDetailGdm" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 max-h-[92vh] overflow-y-auto text-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-mortarboard-fill text-indigo-600"></i>
                <span id="detailGdmTitle">Detail &amp; Riwayat Penugasan GDM</span>
            </h3>
            <button type="button" onclick="closeModal('modalDetailGdm')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="detailGdmBody" class="space-y-4">
            <!-- Loaded via JS viewDetailGdm -->
        </div>

        <div class="pt-3 border-t border-slate-100 text-right">
            <button type="button" onclick="closeModal('modalDetailGdm')" class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Tutup</button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 4: TAMBAH PENUGASAN CEPAT                          -->
<!-- ======================================================== -->
<div id="modalQuickPenugasan" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 text-xs">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-calendar-plus text-indigo-600"></i>
                <span>Tambah Penugasan Kajian</span>
            </h3>
            <button type="button" onclick="closeModal('modalQuickPenugasan')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="p-2.5 rounded-xl bg-indigo-50/70 border border-indigo-100 text-indigo-900 mb-3 font-semibold" id="quickPenugasanGdmName">
            -
        </div>

        <form id="formQuickPenugasan" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Tahun Penugasan <span class="text-red-500">*</span></label>
                <input type="number" name="tahun" id="quickPenugasanTahun" value="{{ date('Y') }}" min="2000" max="2100" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 text-xs">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Cabang Kajian Tujuan <span class="text-red-500">*</span></label>
                <select name="cabang_id" id="quickPenugasanCabangId" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 text-xs">
                    <option value="">-- Pilih Cabang Kajian --</option>
                    @foreach($cabangList as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->wilayah?->name ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Hari Kajian</label>
                    <input type="text" name="hari_kajian" id="quickPenugasanHari" placeholder="Ahad Pagi" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Waktu / Jam</label>
                    <input type="text" name="jam_kajian" id="quickPenugasanJam" placeholder="06:00 WIB" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 text-xs">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Status Penugasan</label>
                <select name="status" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 text-xs">
                    <option value="aktif">Aktif (Sedang Berjalan)</option>
                    <option value="selesai">Selesai</option>
                    <option value="ditarik">Ditarik / Dibatalkan</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Keterangan / Catatan Penugasan</label>
                <input type="text" name="keterangan" placeholder="Materi khusus, evaluasi penugasan..." class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 text-xs">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalQuickPenugasan')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition shadow-md">Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function changePerPageGdm(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    function openModalTambahGdm() {
        switchGdmSourceTab('pemuda');
        openModal('modalTambahGdm');
    }

    function switchGdmSourceTab(type) {
        const btnPemuda = document.getElementById('btnTabSumberPemuda');
        const btnWarga  = document.getElementById('btnTabSumberWarga');
        const btnManual = document.getElementById('btnTabSumberManual');
        const secPemuda = document.getElementById('sectionSearchPemuda');
        const secWarga  = document.getElementById('sectionSearchWarga');
        const hiddenSumber = document.getElementById('tambahSumberData');

        hiddenSumber.value = type;

        [btnPemuda, btnWarga, btnManual].forEach(btn => {
            btn.className = 'py-2 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition bg-slate-100 text-slate-600 hover:bg-white border border-transparent';
        });

        secPemuda.classList.add('hidden');
        secWarga.classList.add('hidden');

        if (type === 'pemuda') {
            btnPemuda.className = 'py-2 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition bg-white text-indigo-700 border border-indigo-300 shadow-2xs';
            secPemuda.classList.remove('hidden');
            document.getElementById('inputSearchPemuda').focus();
        } else if (type === 'warga') {
            btnWarga.className = 'py-2 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition bg-white text-indigo-700 border border-indigo-300 shadow-2xs';
            secWarga.classList.remove('hidden');
            document.getElementById('inputSearchWarga').focus();
        } else {
            btnManual.className = 'py-2 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition bg-white text-indigo-700 border border-indigo-300 shadow-2xs';
        }
    }

    let searchPemudaTimer = null;
    function debounceSearchPemuda(val) {
        clearTimeout(searchPemudaTimer);
        const resultsBox = document.getElementById('searchResultsPemuda');

        if (val.trim().length < 2) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
            return;
        }

        resultsBox.classList.remove('hidden');
        resultsBox.innerHTML = '<div class="p-3 text-center text-slate-400"><i class="bi bi-arrow-repeat animate-spin"></i> Mencari data pemuda...</div>';

        searchPemudaTimer = setTimeout(() => {
            fetch(`{{ route('admin.gdm.search-pemuda') }}?q=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(res => {
                    const list = res.data || [];
                    if (list.length === 0) {
                        resultsBox.innerHTML = '<div class="p-3 text-center text-slate-400 italic">Tidak ditemukan pemuda yang cocok.</div>';
                        return;
                    }

                    resultsBox.innerHTML = list.map(p => `
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200 hover:border-indigo-300 transition flex items-center justify-between gap-3 shadow-2xs">
                            <div>
                                <div class="font-bold text-slate-800 text-xs">${escapeHtml(p.nama)} (${p.gender_label})</div>
                                <div class="text-[10px] text-slate-400">
                                    <span>${p.cabang_name}</span> &bull; <span>${p.tempat_lahir || '-'}, ${p.tanggal_lahir_formatted || '-'}</span>
                                    ${p.no_wa ? ` &bull; <span class="text-emerald-600 font-semibold">${p.no_wa}</span>` : ''}
                                </div>
                            </div>
                            <button type="button" onclick="selectPemudaForGdm(${JSON.stringify(p).replace(/"/g, '&quot;')})" class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white font-bold text-[10px] transition flex-shrink-0">
                                Pilih Pemuda
                            </button>
                        </div>
                    `).join('');
                })
                .catch(() => {
                    resultsBox.innerHTML = '<div class="p-3 text-center text-rose-500">Gagal mencari pemuda.</div>';
                });
        }, 300);
    }

    function selectPemudaForGdm(p) {
        document.getElementById('tambahSumberData').value = 'pemuda';
        document.getElementById('tambahPemudaId').value = p.id;
        document.getElementById('tambahMtaUuid').value = p.mta_uuid || '';
        document.getElementById('tambahNama').value = p.nama || '';
        document.getElementById('tambahTempatLahir').value = p.tempat_lahir || '';
        document.getElementById('tambahTanggalLahir').value = p.tanggal_lahir || '';
        if (p.cabang_id) {
            document.getElementById('tambahCabangId').value = p.cabang_id;
        }
        document.getElementById('tambahNoWa').value = p.no_wa || '';
        document.getElementById('tambahAlamat').value = p.alamat || '';
        document.getElementById('searchResultsPemuda').classList.add('hidden');

        showToastNotification(`Data pemuda "${p.nama}" berhasil dimuat ke formulir.`);
    }

    let searchWargaTimer = null;
    function debounceSearchWarga(val) {
        clearTimeout(searchWargaTimer);
        const resultsBox = document.getElementById('searchResultsWarga');

        if (val.trim().length < 3) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
            return;
        }

        resultsBox.classList.remove('hidden');
        resultsBox.innerHTML = '<div class="p-3 text-center text-slate-400"><i class="bi bi-arrow-repeat animate-spin"></i> Mencari ke server MTA Pusat...</div>';

        searchWargaTimer = setTimeout(() => {
            fetch(`{{ route('admin.gdm.search-warga') }}?q=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(res => {
                    const list = res.data || [];
                    if (list.length === 0) {
                        resultsBox.innerHTML = '<div class="p-3 text-center text-slate-400 italic">Tidak ditemukan warga MTA yang cocok.</div>';
                        return;
                    }

                    resultsBox.innerHTML = list.map(w => `
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200 hover:border-indigo-300 transition flex items-center justify-between gap-3 shadow-2xs">
                            <div>
                                <div class="font-bold text-slate-800 text-xs">${escapeHtml(w.nama)}</div>
                                <div class="text-[10px] text-slate-400">
                                    <span>${w.cabang_name}</span> &bull; <span>${w.tempat_lahir || '-'}, ${w.tanggal_lahir || '-'}</span>
                                    ${w.no_wa ? ` &bull; <span class="text-emerald-600 font-semibold">${w.no_wa}</span>` : ''}
                                </div>
                            </div>
                            <button type="button" onclick="selectWargaForGdm(${JSON.stringify(w).replace(/"/g, '&quot;')})" class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white font-bold text-[10px] transition flex-shrink-0">
                                Pilih Warga
                            </button>
                        </div>
                    `).join('');
                })
                .catch(() => {
                    resultsBox.innerHTML = '<div class="p-3 text-center text-rose-500">Gagal mencari warga MTA.</div>';
                });
        }, 400);
    }

    function selectWargaForGdm(w) {
        document.getElementById('tambahSumberData').value = 'warga';
        document.getElementById('tambahPemudaId').value = '';
        document.getElementById('tambahMtaUuid').value = w.uuid || '';
        document.getElementById('tambahNama').value = w.nama || '';
        document.getElementById('tambahTempatLahir').value = w.tempat_lahir || '';
        document.getElementById('tambahTanggalLahir').value = w.tanggal_lahir || '';
        if (w.cabang_id) {
            document.getElementById('tambahCabangId').value = w.cabang_id;
        }
        document.getElementById('tambahNoWa').value = w.no_wa || '';
        document.getElementById('tambahAlamat').value = w.alamat || '';
        document.getElementById('searchResultsWarga').classList.add('hidden');

        showToastNotification(`Data warga "${w.nama}" berhasil dimuat ke formulir.`);
    }

    function editGdm(g) {
        document.getElementById('formEditGdm').action = `{{ url('admin/gdm/update') }}/${g.id}`;
        document.getElementById('editNama').value = g.nama || '';
        document.getElementById('editTempatLahir').value = g.tempat_lahir || '';
        document.getElementById('editTanggalLahir').value = g.tanggal_lahir ? g.tanggal_lahir.substring(0, 10) : '';
        document.getElementById('editCabangId').value = g.cabang_id || '';
        document.getElementById('editNoWa').value = g.no_wa || '';
        document.getElementById('editAlamat').value = g.alamat || '';
        document.getElementById('editStatus').value = g.status || 'aktif';
        document.getElementById('editCatatan').value = g.catatan || '';
        document.getElementById('editSumberData').value = g.sumber_data || 'manual';
        document.getElementById('editPemudaId').value = g.pemuda_id || '';
        document.getElementById('editMtaUuid').value = g.mta_warga_uuid || '';

        openModal('modalEditGdm');
    }

    function openQuickPenugasanModal(id, nama) {
        document.getElementById('formQuickPenugasan').action = `{{ url('admin/gdm') }}/${id}/penugasan`;
        document.getElementById('quickPenugasanGdmName').innerHTML = `<i class="bi bi-mortarboard-fill"></i> ${escapeHtml(nama)}`;
        openModal('modalQuickPenugasan');
    }

    function viewDetailGdm(id) {
        const body = document.getElementById('detailGdmBody');
        body.innerHTML = '<div class="py-8 text-center text-slate-400"><i class="bi bi-arrow-repeat animate-spin text-2xl block mb-2 text-indigo-600"></i> Memuat detail GDM dan riwayat penugasan...</div>';
        openModal('modalDetailGdm');

        fetch(`{{ url('admin/gdm/detail') }}/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'success') {
                    body.innerHTML = '<div class="p-4 text-center text-rose-500 font-semibold">Gagal memuat data GDM.</div>';
                    return;
                }

                const g = res.data;
                document.getElementById('detailGdmTitle').textContent = `Detail GDM: ${g.nama}`;

                let sumberBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Manual</span>';
                if (g.sumber_data === 'pemuda') {
                    sumberBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200"><i class="bi bi-people-fill"></i> Terdaftar di Pemuda</span>';
                } else if (g.sumber_data === 'warga') {
                    sumberBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"><i class="bi bi-cloud-check-fill"></i> Sinkron Warga MTA</span>';
                }

                const penugasanList = g.penugasan || [];
                let penugasanHtml = '';

                if (penugasanList.length === 0) {
                    penugasanHtml = `
                        <div class="py-6 text-center text-slate-400 bg-slate-50 rounded-2xl border border-slate-100">
                            <i class="bi bi-calendar-x text-2xl block mb-1 text-slate-300"></i>
                            <span>Belum ada riwayat penugasan kajian untuk GDM ini.</span>
                        </div>
                    `;
                } else {
                    penugasanHtml = `
                        <div class="space-y-2">
                            ${penugasanList.map(p => `
                                <div class="p-3 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 flex flex-col items-center justify-center flex-shrink-0 font-bold">
                                            <span class="text-[9px] uppercase leading-none">THN</span>
                                            <span class="text-xs leading-none mt-0.5">${p.tahun}</span>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs flex items-center gap-2">
                                                <span>Cabang ${escapeHtml(p.cabang_name)}</span>
                                                <span class="text-[10px] text-slate-400 font-normal">(${escapeHtml(p.wilayah_name)})</span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                                <span><i class="bi bi-clock"></i> ${escapeHtml(p.hari_kajian)} &bull; ${escapeHtml(p.jam_kajian)}</span>
                                                ${p.keterangan ? `<span>&bull; <i class="bi bi-chat-text"></i> ${escapeHtml(p.keterangan)}</span>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] ${p.status === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600'}">
                                            ${p.status.toUpperCase()}
                                        </span>
                                        <button type="button" onclick="hapusPenugasanGdm(${p.id}, ${g.id})" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 transition" title="Hapus Riwayat Penugasan">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                }

                body.innerHTML = `
                    <div class="space-y-4">
                        <!-- Profil Card -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-black text-slate-900">${escapeHtml(g.nama)}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        ${sumberBadge}
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold ${g.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200 text-slate-700'}">
                                            ${g.status === 'aktif' ? 'AKTIF' : 'NONAKTIF'}
                                        </span>
                                    </div>
                                </div>
                                ${g.wa_link ? `
                                    <a href="${g.wa_link}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition shadow-sm">
                                        <i class="bi bi-whatsapp"></i> Hubungi WA
                                    </a>
                                ` : ''}
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 pt-3 border-t border-slate-200 text-xs">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Tempat, Tanggal Lahir</span>
                                    <span class="font-bold text-slate-800">${escapeHtml(g.ttl)} ${g.usia ? `(${g.usia} thn)` : ''}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Cabang Asal</span>
                                    <span class="font-bold text-slate-800">${escapeHtml(g.cabang_name || '-')}</span>
                                    <span class="text-[10px] text-slate-400 block">${escapeHtml(g.wilayah_name || '-')}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Nomor WhatsApp</span>
                                    <span class="font-bold text-slate-800">${escapeHtml(g.no_wa || '-')}</span>
                                </div>
                                <div class="sm:col-span-3">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Alamat Domisili</span>
                                    <span class="font-medium text-slate-700">${escapeHtml(g.alamat || '-')}</span>
                                </div>
                                ${g.catatan ? `
                                <div class="sm:col-span-3">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Catatan Kader</span>
                                    <span class="font-medium text-slate-700">${escapeHtml(g.catatan)}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Riwayat Penugasan Kajian Cabang -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                                <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="bi bi-calendar2-check-fill text-indigo-600"></i> Riwayat Penugasan Kajian Cabang (${penugasanList.length})
                                </h4>
                                <button type="button" onclick="toggleFormTambahPenugasanDetail()" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1">
                                    <i class="bi bi-plus-circle-fill"></i> Tambah Penugasan
                                </button>
                            </div>

                            <!-- Inline Form Tambah Penugasan -->
                            <div id="boxTambahPenugasanDetail" class="hidden p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-200">
                                <form id="formTambahPenugasanDetail" onsubmit="submitPenugasanDetail(event, ${g.id})" class="space-y-2.5">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                        <div>
                                            <label class="block font-bold text-indigo-950 uppercase text-[10px] mb-0.5">Tahun Penugasan</label>
                                            <input type="number" name="tahun" value="{{ date('Y') }}" min="2000" max="2100" required class="w-full py-1.5 px-2.5 rounded-lg border border-indigo-200 bg-white">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-indigo-950 uppercase text-[10px] mb-0.5">Cabang Kajian Tujuan</label>
                                            <select name="cabang_id" required class="w-full py-1.5 px-2.5 rounded-lg border border-indigo-200 bg-white">
                                                <option value="">-- Pilih Cabang --</option>
                                                @foreach($cabangList as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->wilayah?->name ?? '-' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-bold text-indigo-950 uppercase text-[10px] mb-0.5">Hari Kajian</label>
                                            <input type="text" name="hari_kajian" placeholder="Ahad Pagi" class="w-full py-1.5 px-2.5 rounded-lg border border-indigo-200 bg-white">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-indigo-950 uppercase text-[10px] mb-0.5">Waktu / Jam</label>
                                            <input type="text" name="jam_kajian" placeholder="06:00 WIB" class="w-full py-1.5 px-2.5 rounded-lg border border-indigo-200 bg-white">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block font-bold text-indigo-950 uppercase text-[10px] mb-0.5">Status &amp; Keterangan</label>
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                <select name="status" class="py-1.5 px-2.5 rounded-lg border border-indigo-200 bg-white text-xs">
                                                    <option value="aktif">Aktif</option>
                                                    <option value="selesai">Selesai</option>
                                                    <option value="ditarik">Ditarik</option>
                                                </select>
                                                <input type="text" name="keterangan" placeholder="Keterangan materi/surat tugas..." class="sm:col-span-2 py-1.5 px-2.5 rounded-lg border border-indigo-200 bg-white text-xs">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="button" onclick="toggleFormTambahPenugasanDetail()" class="px-3 py-1 rounded-lg bg-slate-200 text-slate-700 font-bold text-xs">Batal</button>
                                        <button type="submit" class="px-4 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm">Simpan Penugasan</button>
                                    </div>
                                </form>
                            </div>

                            ${penugasanHtml}
                        </div>
                    </div>
                `;
            })
            .catch(() => {
                body.innerHTML = '<div class="p-4 text-center text-rose-500 font-semibold">Terjadi kesalahan koneksi saat memuat data GDM.</div>';
            });
    }

    function toggleFormTambahPenugasanDetail() {
        const box = document.getElementById('boxTambahPenugasanDetail');
        if (box) {
            box.classList.toggle('hidden');
        }
    }

    function submitPenugasanDetail(e, gdmId) {
        e.preventDefault();
        const form = document.getElementById('formTambahPenugasanDetail');
        const formData = new FormData(form);

        fetch(`{{ url('admin/gdm') }}/${gdmId}/penugasan`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                showToastNotification('Penugasan kajian berhasil ditambahkan.');
                viewDetailGdm(gdmId);
            } else {
                alert(res.message || 'Gagal menambahkan penugasan.');
            }
        })
        .catch(() => {
            alert('Terjadi kesalahan saat menyimpan penugasan.');
        });
    }

    function hapusPenugasanGdm(penugasanId, gdmId) {
        if (!confirm('Apakah Anda yakin ingin menghapus riwayat penugasan ini?')) {
            return;
        }

        fetch(`{{ url('admin/gdm/penugasan') }}/${penugasanId}/delete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                showToastNotification('Riwayat penugasan berhasil dihapus.');
                viewDetailGdm(gdmId);
            } else {
                alert('Gagal menghapus penugasan.');
            }
        })
        .catch(() => {
            alert('Terjadi kesalahan saat menghapus penugasan.');
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showToastNotification(msg) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-5 right-5 z-50 px-4 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-xl animate-in slide-in-from-bottom-3 flex items-center gap-2 border border-slate-700';
        toast.innerHTML = `<i class="bi bi-check-circle-fill text-emerald-400"></i> ${escapeHtml(msg)}`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.remove();
        }, 3500);
    }
</script>
@endsection
