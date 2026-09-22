@extends('admin.layouts.main')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
                <i class="bi bi-calendar-event text-red-500"></i>
                <span>Kelola Info Kegiatan Pemuda (Mobile Presensi)</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Kelola informasi agenda kegiatan Pemuda MTA Perwakilan Sragen yang tampil pada tab Beranda aplikasi mobile Android Presensi PMD.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openModal('modalTambah')" class="px-4 py-2 bg-gradient-to-r from-red-600 to-amber-600 hover:from-red-500 hover:to-amber-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-red-600/30 flex items-center gap-2 transition">
                <i class="bi bi-plus-circle-fill text-sm"></i>
                <span>Tambah Agenda Kegiatan</span>
            </button>
        </div>
    </div>

    <!-- Flash Notification -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-emerald-400 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs">
            <div class="flex items-center gap-2 font-bold mb-1">
                <i class="bi bi-exclamation-triangle-fill text-rose-400"></i>
                <span>Terdapat kesalahan pengisian data:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 ml-2">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Stat Overview Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 bg-slate-850/80 rounded-2xl border border-slate-750 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 font-medium">Total Agenda</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <i class="bi bi-calendar3"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-white">{{ $totalKegiatan }}</div>
            <span class="text-[11px] text-slate-500">Semua kegiatan terdata</span>
        </div>

        <div class="p-4 bg-slate-850/80 rounded-2xl border border-slate-750 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 font-medium">Aktif di Mobile</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="bi bi-phone-fill"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-400">{{ $totalAktif }}</div>
            <span class="text-[11px] text-emerald-500/80">Tampil di aplikasi</span>
        </div>

        <div class="p-4 bg-slate-850/80 rounded-2xl border border-slate-750 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 font-medium">Akan Datang</span>
                <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-sky-400">{{ $totalMendatang }}</div>
            <span class="text-[11px] text-sky-500/80">Agenda persiapan</span>
        </div>

        <div class="p-4 bg-slate-850/80 rounded-2xl border border-slate-750 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 font-medium">Selesai</span>
                <div class="w-8 h-8 rounded-lg bg-slate-500/10 text-slate-400 flex items-center justify-center">
                    <i class="bi bi-check2-all"></i>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-300">{{ $totalSelesai }}</div>
            <span class="text-[11px] text-slate-500">Telah terlaksana</span>
        </div>
    </div>

    <!-- Card: Maklumat / Siaran Resmi Perwakilan -->
    <div class="p-5 bg-gradient-to-r from-amber-500/10 via-slate-850 to-slate-850 rounded-2xl border border-amber-500/30">
        <form action="{{ route('admin.kegiatan-perwakilan.broadcast') }}" method="POST">
            @csrf
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-sm">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white">Maklumat &amp; Pesan Siaran Resmi Perwakilan Sragen</h3>
                        <p class="text-[11px] text-slate-400">Pesan ini tampil sebagai pengumuman berjalan/banner di Beranda aplikasi mobile Presensi PMD.</p>
                    </div>
                </div>
                <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white rounded-lg text-xs font-semibold self-start sm:self-auto transition flex items-center gap-1.5">
                    <i class="bi bi-send-fill text-[11px]"></i>
                    <span>Simpan &amp; Siarkan</span>
                </button>
            </div>
            <textarea name="api_broadcast_message" rows="2" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 focus:border-amber-500 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-500 transition" placeholder="Tuliskan maklumat resmi dari pengurus perwakilan...">{{ old('api_broadcast_message', $broadcastMessage) }}</textarea>
        </form>
    </div>

    <!-- Filter & Table Card -->
    <div class="bg-slate-850/80 rounded-2xl border border-slate-750 overflow-hidden shadow-sm">
        <!-- Search & Filter Bar -->
        <div class="p-4 border-b border-slate-750 flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
            <form action="{{ route('admin.kegiatan-perwakilan.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <i class="bi bi-search absolute left-3 top-2.5 text-xs text-slate-500"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama agenda, lokasi, atau pemateri..." class="w-full pl-9 pr-3 py-2 bg-slate-900 border border-slate-700 focus:border-red-500 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none transition">
                </div>

                <!-- Kategori Filter -->
                <select name="kategori" onchange="this.form.submit()" class="px-3 py-2 bg-slate-900 border border-slate-700 focus:border-red-500 rounded-xl text-xs text-slate-300 focus:outline-none">
                    <option value="semua">Semua Kategori</option>
                    @foreach($kategoriOptions as $kat)
                        <option value="{{ $kat }}" {{ request('kategori') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>

                <!-- Status Filter -->
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-900 border border-slate-700 focus:border-red-500 rounded-xl text-xs text-slate-300 focus:outline-none">
                    <option value="semua">Semua Status</option>
                    <option value="Akan Datang" {{ request('status') === 'Akan Datang' ? 'selected' : '' }}>Akan Datang</option>
                    <option value="Segera" {{ request('status') === 'Segera' ? 'selected' : '' }}>Segera</option>
                    <option value="Berlangsung" {{ request('status') === 'Berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                    <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>

                <!-- Filter Aktif -->
                <select name="is_active" onchange="this.form.submit()" class="px-3 py-2 bg-slate-900 border border-slate-700 focus:border-red-500 rounded-xl text-xs text-slate-300 focus:outline-none">
                    <option value="">Semua Visibilitas</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Tampil di HP</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Disembunyikan</option>
                </select>

                <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition">
                    Filter
                </button>

                @if(request()->hasAny(['q', 'kategori', 'status', 'is_active']))
                    <a href="{{ route('admin.kegiatan-perwakilan.index') }}" class="px-3 py-2 text-rose-400 hover:text-rose-300 text-xs font-semibold">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/60 text-slate-400 text-[11px] uppercase tracking-wider font-semibold border-b border-slate-750">
                    <tr>
                        <th class="px-4 py-3 w-10">No</th>
                        <th class="px-4 py-3 min-w-[220px]">Agenda Kegiatan</th>
                        <th class="px-4 py-3 min-w-[170px]">Hari &amp; Tanggal</th>
                        <th class="px-4 py-3 min-w-[180px]">Lokasi &amp; Sasaran</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Tampil Mobile</th>
                        <th class="px-4 py-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-750/70">
                    @forelse($kegiatanList as $index => $item)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3.5 text-slate-500 font-mono">{{ $kegiatanList->firstItem() + $index }}</td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-white text-xs hover:text-red-400 transition">{{ $item->nama_kegiatan }}</div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md border {{ $item->kategori_badge }}">
                                        {{ $item->kategori }}
                                    </span>
                                    <span class="text-[10px] text-slate-500">• {{ $item->penyelenggara }}</span>
                                </div>
                                @if($item->pemateri)
                                    <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                                        <i class="bi bi-mic-fill text-red-400 text-[10px]"></i>
                                        <span>{{ $item->pemateri }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-200">{{ $item->hari_tanggal ?: $item->tanggal->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                    <i class="bi bi-clock text-amber-400 text-[10px]"></i>
                                    <span>{{ $item->jam }}</span>
                                </div>
                                <div class="mt-1">
                                    @if($item->hari_tersisa > 0)
                                        <span class="inline-block px-1.5 py-0.5 bg-sky-500/10 border border-sky-500/20 text-sky-400 rounded text-[10px] font-bold">
                                            H-{{ $item->hari_tersisa }} Hari Lagi
                                        </span>
                                    @else
                                        <span class="inline-block px-1.5 py-0.5 bg-slate-700/50 text-slate-400 rounded text-[10px]">
                                            Telah Lewat
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-xs text-slate-200 font-medium truncate max-w-[200px]" title="{{ $item->lokasi }}">
                                    <i class="bi bi-geo-alt-fill text-rose-400 text-[10px]"></i>
                                    {{ $item->lokasi }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1 truncate max-w-[200px]" title="{{ $item->target_peserta }}">
                                    <i class="bi bi-people-fill text-slate-500 text-[10px]"></i>
                                    {{ $item->target_peserta }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full border text-[10px] font-bold {{ $item->status_badge }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <form action="{{ route('admin.kegiatan-perwakilan.toggle', $item->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold border transition {{ $item->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-slate-700/30 text-slate-500 border-slate-600 hover:text-slate-400' }}" title="Klik untuk mengubah status tayang di mobile">
                                        {{ $item->is_active ? '✓ Tayang' : '✕ Sembunyi' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button -->
                                    <button type="button" onclick="editKegiatan({{ json_encode($item) }})" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition" title="Edit Agenda">
                                        <i class="bi bi-pencil-square text-xs"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.kegiatan-perwakilan.delete', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda kegiatan ini?')">
                                        @csrf
                                        <button type="submit" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-rose-600/30 text-slate-400 hover:text-rose-400 flex items-center justify-center transition" title="Hapus Agenda">
                                            <i class="bi bi-trash3 text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                <i class="bi bi-calendar-x text-3xl mb-2 block text-slate-600"></i>
                                <span>Belum ada agenda kegiatan perwakilan yang sesuai kriteria filter.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kegiatanList->hasPages())
            <div class="p-4 border-t border-slate-750">
                {{ $kegiatanList->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Kegiatan -->
<div id="modalTambah" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-slate-850 border border-slate-750 rounded-2xl w-full max-w-2xl my-8 overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-750 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-red-500"></i>
                <span>Tambah Agenda Kegiatan Pemuda Perwakilan</span>
            </h3>
            <button type="button" onclick="closeModal('modalTambah')" class="text-slate-400 hover:text-white">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.kegiatan-perwakilan.simpan') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Kegiatan / Agenda <span class="text-red-400">*</span></label>
                <input type="text" name="nama_kegiatan" required placeholder="Contoh: Kajian Akbar Pemuda & Pemudi Se-Perwakilan Sragen" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori Kegiatan <span class="text-red-400">*</span></label>
                    <select name="kategori" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                        @foreach($kategoriOptions as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Status Kegiatan <span class="text-red-400">*</span></label>
                    <select name="status" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                        <option value="Akan Datang" selected>Akan Datang</option>
                        <option value="Segera">Segera</option>
                        <option value="Berlangsung">Berlangsung</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Kegiatan <span class="text-red-400">*</span></label>
                    <input type="date" name="tanggal" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Hari &amp; Tanggal (Teks)</label>
                    <input type="text" name="hari_tanggal" placeholder="Otomatis jika kosong" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Jam Pelaksanaan <span class="text-red-400">*</span></label>
                    <input type="text" name="jam" required placeholder="08.30 - 11.45 WIB" value="08.30 - Selesai" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Lokasi Kegiatan <span class="text-red-400">*</span></label>
                    <input type="text" name="lokasi" required placeholder="Gedung Dakwah Pusat MTA Perwakilan Sragen" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Lengkap / Peta</label>
                    <input type="text" name="alamat_detail" placeholder="Jl. Raya Sukowati No. 42, Kebakkramat" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Pembicara / Pemateri</label>
                    <input type="text" name="pemateri" placeholder="Ustadz Pembina Pemuda MTA Sragen" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Sasaran Peserta <span class="text-red-400">*</span></label>
                    <input type="text" name="target_peserta" required value="Seluruh Pemuda & Pemudi 70 Cabang se-Kabupaten Sragen" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Penyelenggara <span class="text-red-400">*</span></label>
                    <input type="text" name="penyelenggara" required value="Pengurus Pemuda MTA Perwakilan Sragen" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Narahubung (WhatsApp/HP)</label>
                    <input type="text" name="narahubung" placeholder="0812-2983-4412 (Biro Pemuda)" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi Kegiatan <span class="text-red-400">*</span></label>
                <textarea name="deskripsi" rows="3" required placeholder="Jelaskan gambaran umum, tujuan, dan agenda kegiatan..." class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Ketentuan &amp; Persiapan Peserta</label>
                <textarea name="catatan_ketentuan" rows="2" placeholder="Contoh: Berbusana rapi, membawa Al-Qur'an dan alat tulis..." class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="tambah_is_active" value="1" checked class="w-4 h-4 rounded text-red-600 focus:ring-red-500 bg-slate-900 border-slate-700">
                <label for="tambah_is_active" class="text-xs text-slate-300 font-semibold cursor-pointer">Langsung publikasikan &amp; tampilkan di aplikasi mobile Presensi PMD</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-750">
                <button type="button" onclick="closeModal('modalTambah')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-red-600/30">
                    Simpan Agenda
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kegiatan -->
<div id="modalEdit" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-slate-850 border border-slate-750 rounded-2xl w-full max-w-2xl my-8 overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-750 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="bi bi-pencil-square text-amber-500"></i>
                <span>Edit Agenda Kegiatan</span>
            </h3>
            <button type="button" onclick="closeModal('modalEdit')" class="text-slate-400 hover:text-white">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form id="formEditKegiatan" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Kegiatan / Agenda <span class="text-red-400">*</span></label>
                <input type="text" id="edit_nama_kegiatan" name="nama_kegiatan" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori Kegiatan <span class="text-red-400">*</span></label>
                    <select id="edit_kategori" name="kategori" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                        @foreach($kategoriOptions as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Status Kegiatan <span class="text-red-400">*</span></label>
                    <select id="edit_status" name="status" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                        <option value="Akan Datang">Akan Datang</option>
                        <option value="Segera">Segera</option>
                        <option value="Berlangsung">Berlangsung</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Kegiatan <span class="text-red-400">*</span></label>
                    <input type="date" id="edit_tanggal" name="tanggal" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Hari &amp; Tanggal (Teks)</label>
                    <input type="text" id="edit_hari_tanggal" name="hari_tanggal" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Jam Pelaksanaan <span class="text-red-400">*</span></label>
                    <input type="text" id="edit_jam" name="jam" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Lokasi Kegiatan <span class="text-red-400">*</span></label>
                    <input type="text" id="edit_lokasi" name="lokasi" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Lengkap / Peta</label>
                    <input type="text" id="edit_alamat_detail" name="alamat_detail" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Pembicara / Pemateri</label>
                    <input type="text" id="edit_pemateri" name="pemateri" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Sasaran Peserta <span class="text-red-400">*</span></label>
                    <input type="text" id="edit_target_peserta" name="target_peserta" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Penyelenggara <span class="text-red-400">*</span></label>
                    <input type="text" id="edit_penyelenggara" name="penyelenggara" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Narahubung (WhatsApp/HP)</label>
                    <input type="text" id="edit_narahubung" name="narahubung" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi Kegiatan <span class="text-red-400">*</span></label>
                <textarea id="edit_deskripsi" name="deskripsi" rows="3" required class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Ketentuan &amp; Persiapan Peserta</label>
                <textarea id="edit_catatan_ketentuan" name="catatan_ketentuan" rows="2" class="w-full px-3 py-2 bg-slate-900 border border-slate-750 rounded-xl text-xs text-white focus:border-red-500 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 rounded text-red-600 focus:ring-red-500 bg-slate-900 border-slate-700">
                <label for="edit_is_active" class="text-xs text-slate-300 font-semibold cursor-pointer">Tampilkan di aplikasi mobile Presensi PMD</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-750">
                <button type="button" onclick="closeModal('modalEdit')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-amber-600/30">
                    Perbarui Agenda
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
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
        document.getElementById('edit_is_active').checked = !!data.is_active;

        openModal('modalEdit');
    }
</script>
@endsection
