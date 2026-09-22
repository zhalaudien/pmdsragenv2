@extends('admin.layouts.main')

@section('title', 'Info Kegiatan Mobile Presensi')

@section('content')
<div class="space-y-6">

    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-red-100 text-red-700 border border-red-200 uppercase tracking-wider">Superadmin</span>
                <span class="text-xs font-semibold text-slate-400">• Presensi PMD Mobile</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5 mt-1">
                <span class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-lg shadow-2xs border border-red-100 flex-shrink-0">
                    <i class="bi bi-calendar-event"></i>
                </span>
                <span>Info Kegiatan Mobile Presensi</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola agenda kegiatan resmi Pemuda MTA Perwakilan Sragen dan maklumat siaran yang tampil pada Beranda aplikasi mobile Android Presensi PMD.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="button" onclick="openModal('modalMobilePreview')" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition shadow-sm flex items-center gap-2">
                <i class="bi bi-phone"></i>
                <span>Simulasi HP</span>
            </button>
            <button type="button" onclick="openModal('modalTambah')" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold text-xs shadow-md shadow-red-600/20 flex items-center gap-2 transition">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Tambah Agenda Kegiatan</span>
            </button>
        </div>
    </div>

    <!-- FLASH NOTIFICATIONS -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <i class="bi bi-check-circle-fill text-emerald-600 text-base flex-shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold shadow-sm">
            <div class="flex items-center gap-2 font-bold mb-1.5">
                <i class="bi bi-exclamation-triangle-fill text-red-600 text-base"></i>
                <span>Terdapat kesalahan pada input data:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-slate-700 font-normal ml-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- STATISTIC CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Total Agenda -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Total Agenda</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                    <i class="bi bi-calendar3"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">{{ number_format($totalKegiatan) }}</div>
                <span class="text-[11px] text-slate-400">Semua agenda terdaftar</span>
            </div>
        </div>

        <!-- Aktif di Mobile -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Aktif di Mobile</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="bi bi-phone-fill"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-emerald-600">{{ number_format($totalAktif) }}</div>
                <span class="text-[11px] text-emerald-600/80 font-medium">Tayang di aplikasi HP</span>
            </div>
        </div>

        <!-- Akan Datang -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Akan Datang</span>
                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-sky-600">{{ number_format($totalMendatang) }}</div>
                <span class="text-[11px] text-sky-600/80 font-medium">Agenda persiapan</span>
            </div>
        </div>

        <!-- Selesai -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Selesai</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-sm">
                    <i class="bi bi-check2-all"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-700">{{ number_format($totalSelesai) }}</div>
                <span class="text-[11px] text-slate-400">Telah terlaksana</span>
            </div>
        </div>
    </div>

    <!-- MAKLUMAT / SIARAN RESMI PERWAKILAN -->
    <div class="bg-gradient-to-br from-amber-50/80 via-white to-orange-50/50 rounded-2xl border border-amber-200/80 p-5 shadow-sm">
        <form action="{{ route('admin.kegiatan-perwakilan.broadcast') }}" method="POST">
            @csrf
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-base flex-shrink-0 shadow-2xs">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-900">Maklumat &amp; Pesan Siaran Resmi Perwakilan Sragen</h3>
                        <p class="text-[11px] text-slate-500">Pesan ini otomatis tampil sebagai banner pengumuman di Beranda aplikasi mobile Presensi PMD.</p>
                    </div>
                </div>
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold self-start sm:self-auto transition shadow-sm flex items-center gap-1.5 flex-shrink-0">
                    <i class="bi bi-send-fill text-[11px]"></i>
                    <span>Simpan &amp; Siarkan</span>
                </button>
            </div>
            <textarea name="api_broadcast_message" rows="2" class="w-full px-3.5 py-2.5 bg-white border border-amber-200 focus:border-amber-500 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/20 shadow-2xs transition" placeholder="Tuliskan maklumat resmi dari pengurus perwakilan yang ingin disebarkan ke seluruh sekretaris cabang...">{{ old('api_broadcast_message', $broadcastMessage) }}</textarea>
        </form>
    </div>

    <!-- FILTER & DATA SECTION -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <!-- Search & Filter Controls -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <form action="{{ route('admin.kegiatan-perwakilan.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[220px] max-w-md">
                    <i class="bi bi-search absolute left-3.5 top-2.5 text-xs text-slate-400"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama agenda, lokasi, atau pemateri..." class="w-full pl-9 pr-3.5 py-2 bg-white border border-slate-300 focus:border-red-600 focus:ring-2 focus:ring-red-500/20 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none shadow-2xs transition">
                </div>

                <!-- Kategori Filter -->
                <select name="kategori" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-300 focus:border-red-600 rounded-xl text-xs text-slate-700 focus:outline-none shadow-2xs">
                    <option value="semua">Semua Kategori</option>
                    @foreach($kategoriOptions as $kat)
                        <option value="{{ $kat }}" {{ request('kategori') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>

                <!-- Status Filter -->
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-300 focus:border-red-600 rounded-xl text-xs text-slate-700 focus:outline-none shadow-2xs">
                    <option value="semua">Semua Status</option>
                    <option value="Akan Datang" {{ request('status') === 'Akan Datang' ? 'selected' : '' }}>Akan Datang</option>
                    <option value="Segera" {{ request('status') === 'Segera' ? 'selected' : '' }}>Segera</option>
                    <option value="Berlangsung" {{ request('status') === 'Berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                    <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>

                <!-- Filter Visibilitas Mobile -->
                <select name="is_active" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-300 focus:border-red-600 rounded-xl text-xs text-slate-700 focus:outline-none shadow-2xs">
                    <option value="">Semua Visibilitas</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Tayang di HP</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Disembunyikan</option>
                </select>

                <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition shadow-2xs flex items-center gap-1.5">
                    <i class="bi bi-funnel-fill text-[11px]"></i>
                    <span>Filter</span>
                </button>

                @if(request()->hasAny(['q', 'kategori', 'status', 'is_active']))
                    <a href="{{ route('admin.kegiatan-perwakilan.index') }}" class="px-3 py-2 text-red-600 hover:text-red-700 text-xs font-bold flex items-center gap-1">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- 1. DESKTOP VIEW: Table (Hidden on mobile < md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                        <th class="px-4 py-3.5 min-w-[240px]">Agenda Kegiatan</th>
                        <th class="px-4 py-3.5 min-w-[170px]">Hari &amp; Tanggal</th>
                        <th class="px-4 py-3.5 min-w-[190px]">Lokasi &amp; Sasaran</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Tayang Mobile</th>
                        <th class="px-4 py-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kegiatanList as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <td class="px-4 py-4 text-center text-slate-400 font-mono">{{ $kegiatanList->firstItem() + $index }}</td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-900 text-xs group-hover:text-red-600 transition">{{ $item->nama_kegiatan }}</div>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md border {{ $item->kategori_badge }}">
                                        {{ $item->kategori }}
                                    </span>
                                    <span class="text-[10px] text-slate-500">• {{ $item->penyelenggara }}</span>
                                </div>
                                @if($item->pemateri)
                                    <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5">
                                        <i class="bi bi-mic-fill text-red-500 text-[10px]"></i>
                                        <span class="font-medium text-slate-700">{{ $item->pemateri }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-800">{{ $item->hari_tanggal ?: $item->tanggal->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                    <i class="bi bi-clock text-amber-500 text-[10px]"></i>
                                    <span>{{ $item->jam }}</span>
                                </div>
                                <div class="mt-1.5">
                                    @if($item->hari_tersisa > 0)
                                        <span class="inline-block px-2 py-0.5 bg-sky-50 border border-sky-200 text-sky-700 rounded-full text-[10px] font-bold">
                                            H-{{ $item->hari_tersisa }} Hari Lagi
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-500 rounded-full text-[10px] font-medium">
                                            Telah Lewat
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-xs text-slate-800 font-semibold truncate max-w-[220px]" title="{{ $item->lokasi }}">
                                    <i class="bi bi-geo-alt-fill text-rose-500 text-[11px] mr-0.5"></i>
                                    {{ $item->lokasi }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 truncate max-w-[220px]" title="{{ $item->target_peserta }}">
                                    <i class="bi bi-people-fill text-slate-400 text-[10px] mr-0.5"></i>
                                    {{ $item->target_peserta }}
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full border text-[10px] font-bold {{ $item->status_badge }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <form action="{{ route('admin.kegiatan-perwakilan.toggle', $item->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-full text-[11px] font-bold border transition shadow-2xs {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' }}" title="Klik untuk mengubah visibilitas di aplikasi mobile">
                                        {{ $item->is_active ? '✓ Tayang' : '✕ Sembunyi' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button -->
                                    <button type="button" onclick="editKegiatan({{ json_encode($item) }})" class="w-8 h-8 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center transition shadow-2xs" title="Edit Agenda">
                                        <i class="bi bi-pencil-square text-xs"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.kegiatan-perwakilan.delete', $item->id) }}" method="POST" onsubmit="return confirm('Hapus agenda kegiatan ini?')">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 flex items-center justify-center transition shadow-2xs" title="Hapus Agenda">
                                            <i class="bi bi-trash3 text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                                    <i class="bi bi-calendar-x"></i>
                                </div>
                                <div class="font-bold text-slate-700 text-sm">Belum Ada Agenda Kegiatan</div>
                                <p class="text-xs text-slate-400 mt-1">Tidak ada kegiatan yang sesuai dengan kriteria filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. MOBILE VIEW: Card List (Visible on smartphone < md) -->
        <div class="block md:hidden divide-y divide-slate-100">
            @forelse($kegiatanList as $index => $item)
                <div class="p-4 space-y-3 hover:bg-slate-50/60 transition">
                    <!-- Top Category & Countdown -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-md border {{ $item->kategori_badge }}">
                            {{ $item->kategori }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            @if($item->hari_tersisa > 0)
                                <span class="px-2 py-0.5 bg-sky-50 border border-sky-200 text-sky-700 rounded-full text-[10px] font-bold">
                                    H-{{ $item->hari_tersisa }} Hari
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-full text-[10px]">
                                    Selesai
                                </span>
                            @endif
                            <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold {{ $item->status_badge }}">
                                {{ $item->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Title -->
                    <div>
                        <h4 class="text-sm font-black text-slate-900 leading-snug">{{ $item->nama_kegiatan }}</h4>
                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $item->penyelenggara }}</div>
                    </div>

                    <!-- Date & Time -->
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-calendar-event text-red-500"></i>
                            <span class="font-semibold text-[11px] truncate">{{ $item->hari_tanggal ?: $item->tanggal->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-clock text-amber-500"></i>
                            <span class="text-[11px] truncate">{{ $item->jam }}</span>
                        </div>
                    </div>

                    <!-- Location & Speaker -->
                    <div class="space-y-1 text-xs">
                        <div class="flex items-center gap-2 text-slate-700 font-medium">
                            <i class="bi bi-geo-alt-fill text-rose-500 text-xs flex-shrink-0"></i>
                            <span class="truncate">{{ $item->lokasi }}</span>
                        </div>
                        @if($item->pemateri)
                            <div class="flex items-center gap-2 text-slate-600">
                                <i class="bi bi-mic-fill text-emerald-600 text-xs flex-shrink-0"></i>
                                <span class="truncate font-medium">{{ $item->pemateri }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-2 text-slate-500 text-[11px]">
                            <i class="bi bi-people text-slate-400 text-xs flex-shrink-0"></i>
                            <span class="truncate">{{ $item->target_peserta }}</span>
                        </div>
                    </div>

                    <!-- Mobile Action Footer -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                        <!-- Toggle Mobile Visibility -->
                        <form action="{{ route('admin.kegiatan-perwakilan.toggle', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[10px] font-bold border transition {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                <i class="bi {{ $item->is_active ? 'bi-check-circle-fill' : 'bi-eye-slash-fill' }}"></i>
                                <span>{{ $item->is_active ? 'Tayang di HP' : 'Sembunyi' }}</span>
                            </button>
                        </form>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick="editKegiatan({{ json_encode($item) }})" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1">
                                <i class="bi bi-pencil-square"></i>
                                <span>Edit</span>
                            </button>
                            <form action="{{ route('admin.kegiatan-perwakilan.delete', $item->id) }}" method="POST" onsubmit="return confirm('Hapus agenda kegiatan ini?')">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200 text-xs">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">
                    <i class="bi bi-calendar-x text-3xl text-slate-400 block mb-2"></i>
                    <div class="font-bold text-slate-700 text-xs">Tidak Ada Agenda Kegiatan</div>
                    <p class="text-[11px] text-slate-400 mt-1">Belum ada agenda yang sesuai kriteria.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($kegiatanList->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $kegiatanList->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ================= MODAL TAMBAH KEGIATAN ================= -->
<div id="modalTambah" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl border border-slate-200 w-full max-w-2xl my-8 overflow-hidden shadow-2xl animate-in fade-in zoom-in-95 duration-150">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-sm">
                    <i class="bi bi-plus-circle-fill"></i>
                </span>
                <h3 class="text-sm font-bold text-slate-900">Tambah Agenda Kegiatan Pemuda Perwakilan</h3>
            </div>
            <button type="button" onclick="closeModal('modalTambah')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.kegiatan-perwakilan.simpan') }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Kegiatan / Agenda <span class="text-red-500">*</span></label>
                <input type="text" name="nama_kegiatan" required placeholder="Contoh: Kajian Akbar Pemuda & Pemudi Se-Perwakilan Sragen" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kategori Kegiatan <span class="text-red-500">*</span></label>
                    <select name="kategori" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                        @foreach($kategoriOptions as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Kegiatan <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                        <option value="Akan Datang" selected>Akan Datang</option>
                        <option value="Segera">Segera</option>
                        <option value="Berlangsung">Berlangsung</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Kegiatan <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Hari &amp; Tanggal (Teks)</label>
                    <input type="text" name="hari_tanggal" placeholder="Otomatis jika kosong" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jam Pelaksanaan <span class="text-red-500">*</span></label>
                    <input type="text" name="jam" required placeholder="08.30 - 11.45 WIB" value="08.30 - Selesai" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Lokasi Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text" name="lokasi" required placeholder="Gedung Dakwah Pusat MTA Perwakilan Sragen" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Lengkap / Tautan Peta</label>
                    <input type="text" name="alamat_detail" placeholder="Jl. Raya Sukowati No. 42, Sragen" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Pembicara / Pemateri</label>
                    <input type="text" name="pemateri" placeholder="Ustadz Pembina Pemuda MTA Sragen" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Sasaran Peserta <span class="text-red-500">*</span></label>
                    <input type="text" name="target_peserta" required value="Seluruh Pemuda & Pemudi 70 Cabang se-Kabupaten Sragen" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Penyelenggara <span class="text-red-500">*</span></label>
                    <input type="text" name="penyelenggara" required value="Pengurus Pemuda MTA Perwakilan Sragen" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Narahubung (WhatsApp/HP)</label>
                    <input type="text" name="narahubung" placeholder="0812-2983-4412 (Biro Pemuda)" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi Kegiatan <span class="text-red-500">*</span></label>
                <textarea name="deskripsi" rows="3" required placeholder="Jelaskan gambaran umum, tema pengajian, dan rundown singkat..." class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Ketentuan &amp; Persiapan Peserta</label>
                <textarea name="catatan_ketentuan" rows="2" placeholder="Contoh: Berbusana rapi, membawa Al-Qur'an dan alat tulis..." class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="tambah_is_active" value="1" checked class="w-4 h-4 rounded text-red-600 focus:ring-red-500 border-slate-300">
                <label for="tambah_is_active" class="text-xs text-slate-700 font-semibold cursor-pointer">Langsung tayangkan pada aplikasi mobile Presensi PMD</label>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalTambah')" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-md shadow-red-600/20">
                    Simpan Agenda
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL EDIT KEGIATAN ================= -->
<div id="modalEdit" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl border border-slate-200 w-full max-w-2xl my-8 overflow-hidden shadow-2xl animate-in fade-in zoom-in-95 duration-150">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm">
                    <i class="bi bi-pencil-square"></i>
                </span>
                <h3 class="text-sm font-bold text-slate-900">Edit Agenda Kegiatan</h3>
            </div>
            <button type="button" onclick="closeModal('modalEdit')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form id="formEditKegiatan" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Kegiatan / Agenda <span class="text-red-500">*</span></label>
                <input type="text" id="edit_nama_kegiatan" name="nama_kegiatan" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kategori Kegiatan <span class="text-red-500">*</span></label>
                    <select id="edit_kategori" name="kategori" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                        @foreach($kategoriOptions as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Kegiatan <span class="text-red-500">*</span></label>
                    <select id="edit_status" name="status" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                        <option value="Akan Datang">Akan Datang</option>
                        <option value="Segera">Segera</option>
                        <option value="Berlangsung">Berlangsung</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Kegiatan <span class="text-red-500">*</span></label>
                    <input type="date" id="edit_tanggal" name="tanggal" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Hari &amp; Tanggal (Teks)</label>
                    <input type="text" id="edit_hari_tanggal" name="hari_tanggal" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jam Pelaksanaan <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_jam" name="jam" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Lokasi Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_lokasi" name="lokasi" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Lengkap / Peta</label>
                    <input type="text" id="edit_alamat_detail" name="alamat_detail" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Pembicara / Pemateri</label>
                    <input type="text" id="edit_pemateri" name="pemateri" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Sasaran Peserta <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_target_peserta" name="target_peserta" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Penyelenggara <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_penyelenggara" name="penyelenggara" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Narahubung (WhatsApp/HP)</label>
                    <input type="text" id="edit_narahubung" name="narahubung" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi Kegiatan <span class="text-red-500">*</span></label>
                <textarea id="edit_deskripsi" name="deskripsi" rows="3" required class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Ketentuan &amp; Persiapan Peserta</label>
                <textarea id="edit_catatan_ketentuan" name="catatan_ketentuan" rows="2" class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 rounded text-red-600 focus:ring-red-500 border-slate-300">
                <label for="edit_is_active" class="text-xs text-slate-700 font-semibold cursor-pointer">Tayangkan pada aplikasi mobile Presensi PMD</label>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalEdit')" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold transition shadow-md shadow-amber-600/20">
                    Perbarui Agenda
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL SIMULASI TAMPILAN HP (PREVIEW MOBILE) ================= -->
<div id="modalMobilePreview" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-slate-900 rounded-[40px] border-4 border-slate-700 w-full max-w-[380px] my-6 overflow-hidden shadow-2xl animate-in fade-in zoom-in-95 duration-150">
        <!-- Phone Top Speaker & Notch -->
        <div class="bg-slate-900 pt-3 pb-2 px-6 flex items-center justify-between">
            <span class="text-[10px] font-bold text-white tracking-tight">09:41</span>
            <div class="w-20 h-4 bg-slate-800 rounded-full flex items-center justify-center">
                <div class="w-3 h-3 rounded-full bg-slate-900"></div>
            </div>
            <div class="flex items-center gap-1 text-[10px] text-white">
                <i class="bi bi-wifi"></i>
                <i class="bi bi-battery-full"></i>
            </div>
        </div>

        <!-- Phone Header App Bar -->
        <div class="bg-red-600 text-white px-4 py-3 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="bi bi-megaphone-fill text-xs"></i>
                </div>
                <div>
                    <div class="text-xs font-black tracking-tight leading-tight">Pemuda MTA Sragen</div>
                    <div class="text-[9px] text-white/80">Informasi &amp; Agenda Perwakilan</div>
                </div>
            </div>
            <button type="button" onclick="closeModal('modalMobilePreview')" class="text-white/80 hover:text-white p-1">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>

        <!-- Phone Body Scrollable -->
        <div class="bg-slate-50 p-3.5 space-y-3 max-h-[520px] overflow-y-auto text-xs">
            <!-- Hero User Box -->
            <div class="p-3.5 rounded-2xl bg-gradient-to-br from-red-600 to-rose-700 text-white shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-white/80">Assalamu'alaikum,</div>
                        <div class="text-xs font-bold truncate">Sekretaris Cabang</div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-white/20 text-[9px] font-bold">Cabang Sragen Kota</span>
                </div>
                <div class="bg-white rounded-xl p-2.5 text-slate-800 flex items-center gap-2.5 shadow-2xs">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[11px] font-bold text-slate-900 leading-tight">Presensi Kajian Cabang</div>
                        <div class="text-[9px] text-slate-500 truncate">Buka sesi presensi pemuda cabang</div>
                    </div>
                    <i class="bi bi-chevron-right text-slate-400 text-xs"></i>
                </div>
            </div>

            <!-- Maklumat Banner -->
            @if(!empty($broadcastMessage))
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-[10px] text-amber-800">
                        <i class="bi bi-bell-fill text-amber-600"></i>
                        <span>Maklumat Pengurus Perwakilan</span>
                    </div>
                    <p class="text-[10px] text-amber-800/90 leading-relaxed">{{ $broadcastMessage }}</p>
                </div>
            @endif

            <!-- Section Title -->
            <div class="flex items-center justify-between pt-1">
                <div>
                    <div class="text-xs font-black text-slate-900">Agenda Perwakilan Mendatang</div>
                    <div class="text-[9px] text-slate-400">Jadwal resmi terdekat se-Sragen</div>
                </div>
                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">{{ $totalAktif }} Agenda</span>
            </div>

            <!-- Agenda Cards inside Phone -->
            @forelse($kegiatanList->where('is_active', true)->take(4) as $previewItem)
                <div class="bg-white rounded-2xl p-3 border border-slate-200 shadow-2xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold border {{ $previewItem->kategori_badge }}">
                            {{ $previewItem->kategori }}
                        </span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 bg-sky-50 text-sky-700 rounded-full border border-sky-200">
                            H-{{ $previewItem->hari_tersisa }} Hari
                        </span>
                    </div>
                    <div class="font-bold text-xs text-slate-900 leading-snug">{{ $previewItem->nama_kegiatan }}</div>
                    <div class="space-y-0.5 text-[10px] text-slate-500">
                        <div class="flex items-center gap-1.5">
                            <i class="bi bi-calendar3 text-red-500 text-[9px]"></i>
                            <span>{{ $previewItem->hari_tanggal ?: $previewItem->tanggal->format('d/m/Y') }} • {{ $previewItem->jam }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i class="bi bi-geo-alt-fill text-rose-500 text-[9px]"></i>
                            <span class="truncate">{{ $previewItem->lokasi }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-slate-400 text-xs">Belum ada agenda yang aktif di aplikasi mobile.</div>
            @endforelse
        </div>

        <!-- Phone Bottom Bar Home Indicator -->
        <div class="bg-slate-900 py-3 flex items-center justify-center">
            <div class="w-28 h-1 bg-slate-600 rounded-full"></div>
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function editKegiatan(data) {
        document.getElementById('formEditKegiatan').action = '/admin/kegiatan-perwakilan/update/' + data.id;
        document.getElementById('edit_nama_kegiatan').value = data.nama_kegiatan || '';
        document.getElementById('edit_kategori').value = data.kategori || 'Kajian Akbar';
        document.getElementById('edit_status').value = data.status || 'Akan Datang';
        
        // Format tanggal YYYY-MM-DD
        if (data.tanggal) {
            const d = new Date(data.tanggal);
            const formatted = d.toISOString().split('T')[0];
            document.getElementById('edit_tanggal').value = formatted;
        }

        document.getElementById('edit_hari_tanggal').value = data.hari_tanggal || '';
        document.getElementById('edit_jam').value = data.jam || '';
        document.getElementById('edit_lokasi').value = data.lokasi || '';
        document.getElementById('edit_alamat_detail').value = data.alamat_detail || '';
        document.getElementById('edit_pemateri').value = data.pemateri || '';
        document.getElementById('edit_target_peserta').value = data.target_peserta || '';
        document.getElementById('edit_penyelenggara').value = data.penyelenggara || '';
        document.getElementById('edit_narahubung').value = data.narahubung || '';
        document.getElementById('edit_deskripsi').value = data.deskripsi || '';
        document.getElementById('edit_catatan_ketentuan').value = data.catatan_ketentuan || '';
        document.getElementById('edit_is_active').checked = Boolean(data.is_active);

        openModal('modalEdit');
    }
</script>
@endsection
