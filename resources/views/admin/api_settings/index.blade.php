@extends('admin.layouts.main')

@section('title', 'Pengaturan API Mobile Presensi')

@section('content')

<!-- ALERTS -->
@if(session('success'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-xs font-semibold shadow-sm">
        <div class="flex items-center gap-2.5">
            <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold shadow-sm">
        <div class="flex items-center gap-2 mb-1.5 font-bold">
            <i class="bi bi-exclamation-triangle-fill text-red-600 text-base"></i>
            <span>Terdapat kesalahan pada isian form:</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-slate-700">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- HEADER & ACTIONS -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-red-100 text-red-700 border border-red-200 uppercase tracking-wider">Superadmin</span>
            <span class="text-xs font-semibold text-slate-400">• Presensi PMD Flutter</span>
        </div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">Pengaturan API Mobile Presensi</h2>
        <p class="text-xs text-slate-500 mt-0.5">Konfigurasi endpoint REST API, kebijakan sinkronisasi offline, versi aplikasi Android, dan kontrol sesi token perangkat cabang.</p>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
        <button type="button" onclick="copyBaseUrl()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition shadow-sm flex items-center gap-2">
            <i class="bi bi-link-45deg text-base"></i>
            <span>Salin Base URL API</span>
        </button>
        <form action="{{ route('admin.api-settings.reset') }}" method="POST" onsubmit="return confirm('Kembalikan semua pengaturan API ke nilai bawaan?')">
            @csrf
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 font-bold text-xs transition border border-amber-200 flex items-center gap-2">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Reset Bawaan</span>
            </button>
        </form>
    </div>
</div>

<!-- STATISTIC CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Status API -->
    <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl {{ ($settings['api_presensi_enabled'] ?? '1') === '1' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }} flex items-center justify-center text-xl flex-shrink-0">
            <i class="bi {{ ($settings['api_presensi_enabled'] ?? '1') === '1' ? 'bi-broadcast-pin' : 'bi-slash-circle' }}"></i>
        </div>
        <div class="min-w-0">
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status API Presensi</span>
            <div class="flex items-center gap-1.5 mt-0.5">
                <span class="inline-block w-2 h-2 rounded-full {{ ($settings['api_presensi_enabled'] ?? '1') === '1' ? 'bg-emerald-500 animate-pulse' : 'bg-red-500' }}"></span>
                <span class="text-sm font-black {{ ($settings['api_presensi_enabled'] ?? '1') === '1' ? 'text-emerald-700' : 'text-red-700' }}">
                    {{ ($settings['api_presensi_enabled'] ?? '1') === '1' ? 'ONLINE / AKTIF' : 'MAINTENANCE' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Sesi Perangkat Aktif -->
    <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl flex-shrink-0">
            <i class="bi bi-phone"></i>
        </div>
        <div class="min-w-0">
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Token Sesi Mobile</span>
            <div class="text-base font-black text-slate-800 mt-0.5">
                {{ number_format($totalTokens) }} <span class="text-xs font-normal text-slate-500">perangkat</span>
            </div>
        </div>
    </div>

    <!-- Total Sesi Kegiatan -->
    <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
            <i class="bi bi-calendar-check"></i>
        </div>
        <div class="min-w-0">
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sesi Kegiatan Presensi</span>
            <div class="text-base font-black text-slate-800 mt-0.5">
                {{ number_format($totalSessions) }} <span class="text-xs font-normal text-slate-500">agenda</span>
            </div>
        </div>
    </div>

    <!-- Total Presensi Dicatat -->
    <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl flex-shrink-0">
            <i class="bi bi-person-check-fill"></i>
        </div>
        <div class="min-w-0">
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kehadiran Tercatat</span>
            <div class="text-base font-black text-slate-800 mt-0.5">
                {{ number_format($totalPresensi) }} <span class="text-xs font-normal text-slate-500">kehadiran</span>
            </div>
        </div>
    </div>
</div>

<!-- FORM PENGATURAN API -->
<form action="{{ route('admin.api-settings.update') }}" method="POST" class="space-y-6 text-xs mb-8">
    @csrf

    <!-- SECTION 1: STATUS & MAINTENANCE -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">1</span>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Status Layanan &amp; Mode Pemeliharaan</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Status API Mobile</label>
                <select name="api_presensi_enabled" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 font-semibold">
                    <option value="1" {{ ($settings['api_presensi_enabled'] ?? '1') === '1' ? 'selected' : '' }}>
                        🟢 Aktif / Online (Layanan dapat diakses seluruh aplikasi mobile)
                    </option>
                    <option value="0" {{ ($settings['api_presensi_enabled'] ?? '1') === '0' ? 'selected' : '' }}>
                        🔴 Nonaktif / Maintenance Mode (Aplikasi menampilkan pesan pemeliharaan)
                    </option>
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Jika dinonaktifkan, seluruh request mobile (kecuali akun superadmin) akan ditolak dengan kode 503.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pesan Pemeliharaan (Jika Nonaktif)</label>
                <input type="text" name="api_maintenance_message" value="{{ $settings['api_maintenance_message'] ?? '' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" placeholder="Pesan saat sistem dalam perbaikan...">
                <p class="text-[11px] text-slate-500 mt-1">Pesan yang muncul pada dialog aplikasi saat maintenance.</p>
            </div>

            <div class="md:col-span-2">
                <label class="block font-bold text-slate-700 uppercase mb-1">Pesan Siaran / Pengumuman Mobile (Broadcast Banner)</label>
                <textarea name="api_broadcast_message" rows="2" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" placeholder="Contoh: Selamat bertugas kepada Sekretaris Cabang. Pastikan sinkronisasi presensi setelah kegiatan selesai...">{{ $settings['api_broadcast_message'] ?? '' }}</textarea>
                <p class="text-[11px] text-slate-500 mt-1">Teks berjalan atau banner pengumuman resmi yang akan tampil di halaman Dashboard aplikasi Flutter.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 2: VERSI APLIKASI & UNDUHAN -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">2</span>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Versi Aplikasi Mobile &amp; Distribusi APK</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Versi Minimum Aplikasi (Force Update)</label>
                <input type="text" name="api_min_app_version" value="{{ $settings['api_min_app_version'] ?? '1.0.0' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 font-mono" placeholder="1.0.0">
                <p class="text-[11px] text-slate-500 mt-1">Versi minimal ponsel yang diizinkan sinkronisasi.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Versi Rilis Terbaru (Latest Version)</label>
                <input type="text" name="api_latest_app_version" value="{{ $settings['api_latest_app_version'] ?? '1.0.0' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 font-mono" placeholder="1.0.0">
                <p class="text-[11px] text-slate-500 mt-1">Versi stabil terbaru yang dirilis untuk cabang.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Masa Berlaku Token Login (Hari)</label>
                <input type="number" name="api_token_expiration_days" value="{{ $settings['api_token_expiration_days'] ?? '90' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 font-mono" min="1" max="365">
                <p class="text-[11px] text-slate-500 mt-1">Batas waktu sebelum user harus login ulang.</p>
            </div>

            <div class="md:col-span-3">
                <label class="block font-bold text-slate-700 uppercase mb-1">URL Unduhan File APK Terbaru</label>
                <div class="relative">
                    <input type="url" name="api_apk_download_url" value="{{ $settings['api_apk_download_url'] ?? '' }}" class="w-full py-2.5 pl-10 pr-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 font-mono" placeholder="https://pmd.mtasragen.or.id/download/presensi-pmd.apk">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-download"></i>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Tautan langsung untuk mengunduh berkas installer APK Android bagi sekretaris cabang.</p>
            </div>
        </div>
    </div>

    <!-- SECTION 3: KEBIJAKAN SINKRONISASI & PRESET KETERANGAN -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">3</span>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Kebijakan Sinkronisasi Offline &amp; Preset Izin/Sakit</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Izinkan Sinkronisasi Offline (Bulk Sync)</label>
                <select name="api_allow_offline_sync" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 font-semibold">
                    <option value="1" {{ ($settings['api_allow_offline_sync'] ?? '1') === '1' ? 'selected' : '' }}>
                        🟢 Ya, Izinkan pengiriman data massal dari antrean offline ponsel
                    </option>
                    <option value="0" {{ ($settings['api_allow_offline_sync'] ?? '1') === '0' ? 'selected' : '' }}>
                        🔴 Kunci / Larang sinkronisasi offline (hanya presensi live realtime)
                    </option>
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Mengizinkan pengiriman kumpulan presensi dari memori lokal HP setelah tersambung internet.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Batas Maksimal Data per Bulk Sync</label>
                <input type="number" name="api_max_bulk_sync" value="{{ $settings['api_max_bulk_sync'] ?? '300' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 font-mono" min="10" max="1000">
                <p class="text-[11px] text-slate-500 mt-1">Maksimal record yang dapat dikirimkan dalam 1 kali request POST bulk sync.</p>
            </div>

            <div class="md:col-span-2">
                <label class="block font-bold text-slate-700 uppercase mb-1">Preset Pilihan Cepat Keterangan Izin &amp; Sakit (Quick Chips)</label>
                <textarea name="quick_chips" rows="5" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500" placeholder="Tuliskan 1 opsi per baris...">{{ implode("\n", $quickChips) }}</textarea>
                <p class="text-[11px] text-slate-500 mt-1">Tulis <strong>satu alasan per baris</strong>. Opsi ini otomatis muncul sebagai tombol cepat (quick chips) pada bottom sheet aplikasi mobile saat memilih Izin atau Sakit.</p>
            </div>
        </div>
    </div>

    <!-- SUBMIT -->
    <div class="flex justify-end pt-2">
        <button type="submit" class="px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            <span>Simpan Seluruh Pengaturan API</span>
        </button>
    </div>
</form>

<!-- SECTION 4: DAFTAR TOKEN & SESI PERANGKAT AKTIF -->
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4 mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-sky-100 text-sky-700 text-xs font-black flex items-center justify-center">4</span>
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Sesi &amp; Perangkat Mobile Terhubung</h3>
                <p class="text-[11px] text-slate-500">Daftar token aktif pengguna sekretaris cabang yang sedang masuk ke aplikasi Presensi PMD.</p>
            </div>
        </div>

        @if($tokens->count() > 0)
            <form action="{{ route('admin.api-settings.revoke-all-tokens') }}" method="POST" onsubmit="return confirm('Peringatan: Tindakan ini akan memaksa logout SELURUH aplikasi mobile presensi di semua cabang! Lanjutkan?')">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 font-bold text-[11px] transition border border-red-200 flex items-center gap-1.5">
                    <i class="bi bi-shield-x"></i>
                    <span>Cabut Semua Sesi (Force Logout)</span>
                </button>
            </form>
        @endif
    </div>

    @if($tokens->isEmpty())
        <div class="py-8 text-center text-slate-400">
            <i class="bi bi-phone-vibrate text-3xl mb-2 inline-block"></i>
            <p class="text-xs">Belum ada sesi login aktif dari aplikasi mobile.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] font-bold text-slate-400 uppercase bg-slate-50 border-y border-slate-100">
                    <tr>
                        <th class="py-2.5 px-3">Nama Perangkat / Device</th>
                        <th class="py-2.5 px-3">Petugas / Akun</th>
                        <th class="py-2.5 px-3">Cabang</th>
                        <th class="py-2.5 px-3">Terakhir Dipakai</th>
                        <th class="py-2.5 px-3">Waktu Login</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($tokens as $tok)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-2.5 px-3 font-semibold text-slate-800 flex items-center gap-2">
                                <i class="bi bi-phone text-slate-400"></i>
                                <span>{{ $tok->device_name ?: 'Android Device' }}</span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-slate-900">{{ $tok->user_name }}</span>
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $tok->username }}</span>
                            </td>
                            <td class="py-2.5 px-3 font-medium text-slate-600">
                                {{ $tok->cabang_name ?: 'Semua Cabang (Superadmin)' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-500">
                                {{ $tok->last_used_at ? \Carbon\Carbon::parse($tok->last_used_at)->diffForHumans() : 'Belum digunakan' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-500">
                                {{ \Carbon\Carbon::parse($tok->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                <form action="{{ route('admin.api-settings.revoke-token', $tok->id) }}" method="POST" onsubmit="return confirm('Cabut sesi perangkat ini?')">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition" title="Cabut Sesi Perangkat">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- SECTION 5: KATALOG & DOKUMENTASI ENDPOINT API -->
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-black flex items-center justify-center">5</span>
        <div>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Dokumentasi &amp; Katalog Endpoint REST API Mobile</h3>
            <p class="text-[11px] text-slate-500">Spesifikasi resmi protokol komunikasi data antara server dan aplikasi Flutter Android.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="text-[10px] font-bold text-slate-400 uppercase bg-slate-50 border-y border-slate-100">
                <tr>
                    <th class="py-2.5 px-3">Metode</th>
                    <th class="py-2.5 px-3">Endpoint URL</th>
                    <th class="py-2.5 px-3">Deskripsi &amp; Fungsi</th>
                    <th class="py-2.5 px-3">Otorisasi</th>
                    <th class="py-2.5 px-3 text-right">Salin</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                @foreach($endpoints as $ep)
                    @php
                        $badgeBg = match($ep['method']) {
                            'GET'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'POST' => 'bg-sky-50 text-sky-700 border-sky-200',
                            'PUT'  => 'bg-amber-50 text-amber-700 border-amber-200',
                            default=> 'bg-slate-50 text-slate-700 border-slate-200',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 rounded font-bold border {{ $badgeBg }}">
                                {{ $ep['method'] }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 font-bold text-slate-900">
                            {{ $ep['endpoint'] }}
                        </td>
                        <td class="py-2.5 px-3 font-sans text-xs text-slate-600">
                            {{ $ep['description'] }}
                        </td>
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 rounded font-sans text-[10px] {{ $ep['auth'] === 'Bearer Token' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-600' }}">
                                {{ $ep['auth'] }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-right font-sans">
                            <button type="button" onclick="copyText('{{ url($ep['endpoint']) }}')" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition" title="Salin URL Lengkap">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
function copyBaseUrl() {
    const url = "{{ url('/api/v1') }}";
    navigator.clipboard.writeText(url).then(() => {
        alert("Base URL API berhasil disalin ke clipboard:\n" + url);
    }).catch(() => {
        prompt("Salin Base URL API:", url);
    });
}

function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert("URL Endpoint berhasil disalin:\n" + text);
    }).catch(() => {
        prompt("Salin URL Endpoint:", text);
    });
}
</script>
@endsection
