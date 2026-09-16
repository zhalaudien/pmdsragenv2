@extends('admin.layouts.main')

@section('title', 'Backup & Reset Data Pemuda')

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <a href="{{ route('admin.pemuda.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-600 font-bold text-xs transition border border-slate-200 mb-2">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Daftar Pemuda</span>
        </a>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Backup &amp; Reset Data Pemuda</h2>
        <p class="text-xs text-slate-500 mt-0.5">Cadangkan basis data ke arsip JSON/SQL atau bersihkan data secara massal (Khusus Super Administrator).</p>
    </div>
</div>

<!-- STATS SUMMARY (CARDS) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Data Pemuda</div>
        <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($countsSummary['pemuda'] ?? 0) }}</div>
        <div class="text-[11px] text-slate-500 mt-0.5">Total pendaftar</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Data Alamat</div>
        <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($countsSummary['alamat'] ?? 0) }}</div>
        <div class="text-[11px] text-slate-500 mt-0.5">Baris domisili</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Pendidikan &amp; Pekerjaan</div>
        <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format(($countsSummary['pendidikan'] ?? 0) + ($countsSummary['pekerjaan'] ?? 0)) }}</div>
        <div class="text-[11px] text-slate-500 mt-0.5">Rekam profil</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Keahlian &amp; Minat</div>
        <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format(($countsSummary['skills'] ?? 0) + ($countsSummary['interests'] ?? 0)) }}</div>
        <div class="text-[11px] text-slate-500 mt-0.5">Pemetaan relasi</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- GENERATE BACKUP -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
            <i class="bi bi-database-add text-emerald-600 text-base"></i>
            <span>Buat Cadangan Baru (Generate Backup)</span>
        </h3>

        <form action="{{ route('admin.pemuda.backup.generate') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Format Cadangan</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="format" value="json" checked class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <div class="font-bold text-slate-800">JSON Archive (.json)</div>
                            <div class="text-[10px] text-slate-400">Mudah diproses kembali via script</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="format" value="sql" class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <div class="font-bold text-slate-800">SQL DDL/DML (.sql)</div>
                            <div class="text-[10px] text-slate-400">Siap restore via MySQL / phpMyAdmin</div>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Catatan Cadangan (Opsional)</label>
                <input type="text" name="notes" placeholder="Contoh: Backup sebelum import batch cabang Masaran" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2">
                <i class="bi bi-play-circle-fill"></i>
                <span>Generate File Backup</span>
            </button>
        </form>

        <!-- DAFTAR FILE BACKUP TERSIMPAN -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Berkas Cadangan Tersimpan</h4>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-slate-700 font-bold uppercase text-[10px]">
                            <th class="py-2.5 px-3">Nama Berkas</th>
                            <th class="py-2.5 px-3">Ukuran</th>
                            <th class="py-2.5 px-3">Tanggal Dibuat</th>
                            <th class="py-2.5 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($savedBackups as $b)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-800">{{ $b['filename'] }}</td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $b['size_human'] ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $b['created_at'] ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('admin.pemuda.backup.download', $b['filename']) }}" class="p-1 px-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[11px] transition">
                                            <i class="bi bi-download"></i> Unduh
                                        </a>
                                        <form action="{{ route('admin.pemuda.backup.delete', $b['filename']) }}" method="POST" class="inline" onsubmit="return confirm('Hapus file backup ini?')">
                                            @csrf
                                            <button type="submit" class="p-1 px-2 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 font-bold text-[11px] transition">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Belum ada file backup tersimpan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DANGER ZONE: RESET DATA -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-red-200 shadow-sm space-y-4 text-xs">
        <div class="p-3 rounded-2xl bg-red-50 border border-red-200">
            <h3 class="text-xs font-bold text-red-900 uppercase tracking-wider flex items-center gap-2 mb-1">
                <i class="bi bi-exclamation-triangle-fill text-red-600 text-base"></i>
                <span>Zona Bahaya (Danger Zone)</span>
            </h3>
            <p class="text-[11px] text-red-700 leading-relaxed">
                Tindakan di bawah ini bersifat permanen dan tidak dapat dibatalkan. Pastikan Anda sudah membuat berkas cadangan sebelum melakukan pembersihan data.
            </p>
        </div>

        <form action="{{ route('admin.pemuda.hapus-semua') }}" method="POST" onsubmit="return confirm('PERINGATAN KERAS: Semua data pemuda dan relasinya akan dihapus bersih! Ketik OK untuk melanjutkan.')" class="space-y-4 pt-2">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Konfirmasi Kata Sandi Superadmin</label>
                <input type="password" name="password" placeholder="Masukkan password akun Anda" required class="w-full py-2.5 px-3 rounded-xl border border-red-300 bg-red-50/50 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Ketik Konfirmasi</label>
                <input type="text" name="confirmation_text" placeholder="Ketik: HAPUS SEMUA DATA" required class="w-full py-2.5 px-3 rounded-xl border border-red-300 bg-red-50/50 focus:ring-red-500 focus:border-red-500">
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-red-700 hover:bg-red-800 text-white font-bold text-xs transition shadow-md flex items-center justify-center gap-2">
                <i class="bi bi-trash3-fill"></i>
                <span>Hapus Bersih Seluruh Data Pemuda</span>
            </button>
        </form>
    </div>
</div>

@endsection
