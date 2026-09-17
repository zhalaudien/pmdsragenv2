@extends('admin.layouts.main')

@section('title', 'Manajemen Data Pemuda')

@section('content')

@php
    $userRole = session('role') ?? auth()->user()?->role?->name;
    $currVerif = $filters['status_verifikasi'] ?? '';
    $currStatusData = $filters['status_data'] ?? 'active';
@endphp

<!-- HEADER & ACTIONS -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Manajemen Data Pemuda</h2>
        <p class="text-xs text-slate-500 mt-0.5">Kelola, verifikasi, saring, dan cetak data pemuda se-Kabupaten Sragen.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        @if($userRole === 'superadmin')
            <a href="{{ route('admin.mta-sync.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 font-bold text-xs transition border border-sky-200">
                <i class="bi bi-arrow-repeat"></i>
                <span>Sinkron MTA</span>
            </a>
            <a href="{{ route('admin.pemuda.import') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs transition border border-emerald-200">
                <i class="bi bi-file-earmark-arrow-up"></i>
                <span>Import Excel</span>
            </a>
            <a href="{{ route('admin.pemuda.backup') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold text-xs transition border border-amber-200">
                <i class="bi bi-database-fill-gear"></i>
                <span>Backup</span>
            </a>
        @endif
        <a href="{{ route('admin.pemuda.export', array_filter($filters, fn($v) => $v !== null && $v !== '')) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 text-slate-200 hover:bg-slate-700 font-bold text-xs transition border border-slate-700">
            <i class="bi bi-file-earmark-excel text-emerald-400"></i>
            <span>Export Excel</span>
        </a>
        <a href="{{ route('admin.pemuda.tambah') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md">
            <i class="bi bi-person-plus-fill"></i>
            <span>Tambah Pemuda</span>
        </a>
    </div>
</div>

<!-- STATUS TABS -->
<div class="flex flex-wrap gap-2 mb-4 border-b border-slate-200 pb-2">
    <a href="{{ route('admin.pemuda.index', ['status_data' => 'active']) }}" 
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ (empty($currVerif) && $currStatusData === 'active') ? 'bg-red-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
        <i class="bi bi-people-fill"></i>
        <span>Semua Aktif</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ (empty($currVerif) && $currStatusData === 'active') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
            {{ number_format($summary['active'] ?? 0) }}
        </span>
    </a>

    <a href="{{ route('admin.pemuda.index', ['status_verifikasi' => 'verified', 'status_data' => 'active']) }}" 
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ ($currVerif === 'verified') ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200' }}">
        <i class="bi bi-patch-check-fill"></i>
        <span>Terverifikasi</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ ($currVerif === 'verified') ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
            {{ number_format($summary['verified'] ?? 0) }}
        </span>
    </a>

    <a href="{{ route('admin.pemuda.index', ['status_verifikasi' => 'pending', 'status_data' => 'active']) }}" 
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ ($currVerif === 'pending') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200' }}">
        <i class="bi bi-clock-history"></i>
        <span>Belum Terverifikasi</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ ($currVerif === 'pending') ? 'bg-black/15 text-slate-950' : 'bg-amber-100 text-amber-800' }}">
            {{ number_format($summary['pending'] ?? 0) }}
        </span>
    </a>

    <a href="{{ route('admin.pemuda.index', ['status_data' => 'archived']) }}" 
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ ($currStatusData === 'archived') ? 'bg-slate-700 text-white shadow-sm' : 'bg-white text-slate-500 hover:bg-slate-100 border border-slate-200' }}">
        <i class="bi bi-archive-fill"></i>
        <span>Diarsipkan</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ ($currStatusData === 'archived') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
            {{ number_format($summary['archived'] ?? 0) }}
        </span>
    </a>
</div>

<!-- FILTER CARD -->
<div class="mb-6 rounded-3xl bg-white p-5 border border-slate-200/80 shadow-sm">
    <form action="{{ route('admin.pemuda.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs items-end">
        <input type="hidden" name="status_verifikasi" value="{{ $currVerif }}">
        <input type="hidden" name="status_data" value="{{ $currStatusData }}">
        <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">

        <!-- Search Keyword -->
        <div class="sm:col-span-2">
            <label class="block font-bold text-slate-700 uppercase mb-1">Cari Pemuda</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama lengkap, No. Registrasi, kontak..." 
                       class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>
        </div>

        <!-- Wilayah -->
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Wilayah</label>
            @if(in_array($userRole, ['superadmin', 'admin_pemuda', 'admin_pemudi'], true))
                <select name="wilayah_id" id="filterWilayah" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Semua Wilayah --</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ ($filters['wilayah_id'] ?? '') == $w->id ? 'selected' : '' }}>
                            {{ $w->name }} ({{ $w->code }})
                        </option>
                    @endforeach
                </select>
            @else
                <input type="text" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-500" value="{{ session('wilayah_name') }}" readonly>
            @endif
        </div>

        <!-- Cabang -->
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Cabang</label>
            @if($userRole === 'admin_cabang')
                <input type="text" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-500" value="{{ session('cabang_name') }}" readonly>
            @else
                <select name="cabang_id" id="filterCabang" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Semua Cabang --</option>
                    @foreach($cabangList as $c)
                        <option value="{{ $c->id }}" {{ ($filters['cabang_id'] ?? '') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>

        <!-- Gender -->
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Gender</label>
            @if(in_array($userRole, ['admin_wilayah_pemuda', 'admin_pemuda'], true))
                <input type="text" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-blue-50 text-blue-700 font-bold" value="Laki-laki (L)" readonly>
            @elseif($userRole === 'admin_pemudi')
                <input type="text" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-pink-50 text-pink-700 font-bold" value="Perempuan (P)" readonly>
            @else
                <select name="gender" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Semua Gender --</option>
                    <option value="L" {{ ($filters['gender'] ?? '') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                    <option value="P" {{ ($filters['gender'] ?? '') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                </select>
            @endif
        </div>

        <!-- Golongan Darah -->
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Golongan Darah</label>
            <select name="blood_type" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                <option value="">-- Semua Gol. Darah --</option>
                <option value="A" {{ ($filters['blood_type'] ?? '') === 'A' ? 'selected' : '' }}>A</option>
                <option value="B" {{ ($filters['blood_type'] ?? '') === 'B' ? 'selected' : '' }}>B</option>
                <option value="AB" {{ ($filters['blood_type'] ?? '') === 'AB' ? 'selected' : '' }}>AB</option>
                <option value="O" {{ ($filters['blood_type'] ?? '') === 'O' ? 'selected' : '' }}>O</option>
            </select>
        </div>

        <!-- Pendidikan -->
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Pendidikan</label>
            <select name="education_level_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                <option value="">-- Semua Pendidikan --</option>
                @foreach($educationLevels as $el)
                    <option value="{{ $el->id }}" {{ ($filters['education_level_id'] ?? '') == $el->id ? 'selected' : '' }}>{{ $el->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Pekerjaan -->
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Pekerjaan</label>
            <select name="job_status_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                <option value="">-- Semua Pekerjaan --</option>
                @foreach($jobStatuses as $js)
                    <option value="{{ $js->id }}" {{ ($filters['job_status_id'] ?? '') == $js->id ? 'selected' : '' }}>{{ $js->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Buttons -->
        <div class="sm:col-span-2 lg:col-span-4 flex justify-end gap-2 pt-2">
            <a href="{{ route('admin.pemuda.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition flex items-center gap-1">
                <i class="bi bi-arrow-clockwise"></i> Reset
            </a>
            <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-sm flex items-center gap-1.5">
                <i class="bi bi-filter"></i> Terapkan Filter
            </button>
        </div>
    </form>
</div>

<!-- PEMUDA TABLE CARD -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-wrap gap-3 items-center justify-between">
        <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
            <span>Ditemukan <span class="text-red-600 font-extrabold">{{ number_format($pemudaList->total()) }}</span> data pemuda</span>
            @if($pemudaList->total() > 0)
                <span class="text-slate-400 font-normal">| Hal. {{ $pemudaList->currentPage() }} dari {{ $pemudaList->lastPage() }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2 text-xs">
            <label for="perPageSelect" class="text-slate-500 font-medium hidden sm:inline">Tampilkan:</label>
            <select id="perPageSelect" onchange="changePerPage(this.value)" class="py-1 px-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-700 font-semibold focus:ring-red-500 focus:border-red-500 text-xs">
                @foreach([10, 15, 25, 50, 100] as $option)
                    <option value="{{ $option }}" {{ (request('per_page', 15) == $option) ? 'selected' : '' }}>{{ $option }} / hal</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                    <th class="py-3 px-4">No. Registrasi</th>
                    <th class="py-3 px-4">Nama Lengkap</th>
                    <th class="py-3 px-4">Gender &amp; Usia</th>
                    <th class="py-3 px-4">Cabang / Wilayah</th>
                    <th class="py-3 px-4">Kontak</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pemudaList as $p)
                    @php
                        $age = $p->birth_date ? \Carbon\Carbon::parse($p->birth_date)->age : null;
                    @endphp
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="py-3 px-4 font-mono font-bold text-red-600">
                            {{ $p->registration_number }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $p->name }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1.5">
                                @if($p->gender === 'L')
                                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[10px] font-bold">
                                        <i class="bi bi-gender-male"></i> L
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-pink-50 text-pink-700 text-[10px] font-bold">
                                        <i class="bi bi-gender-female"></i> P
                                    </span>
                                @endif
                                @if($age)
                                    <span class="text-slate-600 font-medium">{{ $age }} thn</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800">{{ $p->cabang->name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $p->cabang->wilayah->name ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($p->phone)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $p->phone)) }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                                    <i class="bi bi-whatsapp"></i> {{ $p->phone }}
                                </a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($p->status_verifikasi === 'verified')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">
                                    <i class="bi bi-check-circle-fill"></i> Terverifikasi
                                </span>
                            @else
                                <form action="{{ route('admin.pemuda.verifikasi', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('Verifikasi data pemuda ini sekarang?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 hover:bg-amber-200 text-amber-800 text-[10px] font-bold transition">
                                        <i class="bi bi-clock"></i> Verifikasi Sekarang
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('admin.pemuda.detail', $p->id) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-600 transition" title="Detail Data">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.pemuda.edit', $p->id) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-amber-50 hover:text-amber-600 text-slate-600 transition" title="Edit Data">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="{{ route('admin.pemuda.cetak', $p->id) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 transition" title="Cetak Biodata">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <form action="{{ route('admin.pemuda.delete', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data pemuda ini?')">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-red-100 hover:text-red-700 text-slate-400 transition" title="Hapus Data">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="bi bi-inbox text-4xl block mb-2 text-slate-300"></i>
                            Tidak ada data pemuda yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
        {{ $pemudaList->links() }}
    </div>
</div>

@endsection

@section('scripts')
<script>
    const filterWilayah = document.getElementById('filterWilayah');
    const filterCabang  = document.getElementById('filterCabang');
    if (filterWilayah && filterCabang) {
        filterWilayah.addEventListener('change', function () {
            const wId = this.value;
            filterCabang.innerHTML = '<option value="">-- Semua Cabang --</option>';
            if (!wId) return;

            fetch(`{{ url('api/cabang') }}/${wId}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        filterCabang.appendChild(opt);
                    });
                })
                .catch(err => console.error(err));
        });
    }
    function changePerPage(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
</script>
@endsection
