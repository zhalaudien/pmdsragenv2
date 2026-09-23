@extends('admin.layouts.main')

@section('title', 'Kelola Konten Beranda')

@section('content')

<!-- HEADER & ACTIONS -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Kelola Konten Beranda (Landing Page)</h2>
        <p class="text-xs text-slate-500 mt-0.5">Sesuaikan teks hero, statistik, visi misi, program kerja, dan FAQ yang tampil di halaman depan.</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('home') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-800 text-white hover:bg-slate-700 font-bold text-xs transition shadow-sm flex items-center gap-2">
            <i class="bi bi-eye"></i>
            <span>Lihat Tampilan Website</span>
        </a>
        <form action="{{ route('admin.homepage.reset') }}" method="POST" onsubmit="return confirm('Reset semua pengaturan beranda ke teks bawaan pabrik?')">
            @csrf
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 font-bold text-xs transition border border-amber-200">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Reset Default</span>
            </button>
        </form>
    </div>
</div>

<form action="{{ route('admin.homepage.update') }}" method="POST" class="space-y-6 text-xs">
    @csrf

    <!-- 1. HERO SECTION -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">1</span>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Bagian Utama (Hero Section)</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block font-bold text-slate-700 uppercase mb-1">Badge Tagline Hero</label>
                <input type="text" name="hero_badge" value="{{ $settings['hero_badge'] ?? "Majlis Tafsir Al-Qur'an (MTA) Perwakilan Sragen" }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="md:col-span-2">
                <label class="block font-bold text-slate-700 uppercase mb-1">Judul Utama (Hero Title)</label>
                <input type="text" name="hero_title" value="{{ $settings['hero_title'] ?? 'Sistem Pendataan Pemuda MTA Perwakilan Sragen' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="md:col-span-2">
                <label class="block font-bold text-slate-700 uppercase mb-1">Subjudul / Keterangan Hero</label>
                <textarea name="hero_subtitle" rows="3" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">{{ $settings['hero_subtitle'] ?? 'Pusat basis data resmi pemuda MTA se-Kabupaten Sragen. Wadah pemetaan potensi, kaderisasi dakwah, dan kesiapsiagaan pengabdian di 4 Wilayah dan 61 Cabang.' }}</textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Teks Tombol Aksi</label>
                <input type="text" name="hero_btn_text" value="{{ $settings['hero_btn_text'] ?? 'Isi Form Pendataan Pemuda' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Statistik Bidang Pengabdian</label>
                <input type="text" name="stats_bidang_num" value="{{ $settings['stats_bidang_num'] ?? '5+' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>
        </div>
    </div>

    <!-- 2. VISI & MISI -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">2</span>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Visi &amp; Deskripsi Lembaga</h3>
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Teks Visi Pemuda MTA Sragen</label>
            <textarea name="visi_text" rows="3" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">{{ $settings['visi_text'] ?? 'Mewujudkan pemuda MTA yang bertaqwa, mandiri, berilmu, terampil, dan berdaya juang tinggi dalam dakwah Islam.' }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Tentang Kami (Paragraf 1)</label>
                <textarea name="tentang_desc_1" rows="3" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">{{ $settings['tentang_desc_1'] ?? '' }}</textarea>
            </div>
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Tentang Kami (Paragraf 2)</label>
                <textarea name="tentang_desc_2" rows="3" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">{{ $settings['tentang_desc_2'] ?? '' }}</textarea>
            </div>
        </div>
    </div>

    <!-- 3. KONTAK & FOOTER -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-black flex items-center justify-center">3</span>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Kontak Bantuan &amp; Alamat Sekretariat</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp Admin</label>
                <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '6281234567890' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Label Kontak WhatsApp</label>
                <input type="text" name="whatsapp_label" value="{{ $settings['whatsapp_label'] ?? 'Admin Pemuda MTA Sragen' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="md:col-span-2">
                <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Kantor / Sekretariat</label>
                <textarea name="alamat_kantor" rows="2" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">{{ $settings['alamat_kantor'] ?? 'Gedung MTA Perwakilan Sragen, Jl. Raya Sukowati, Sragen, Jawa Tengah' }}</textarea>
            </div>
        </div>
    </div>

    <!-- 8. KODE AKSES GURU DAERAH -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 text-xs font-black flex items-center justify-center">8</span>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Akses Pemantauan Guru Daerah</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Kode Akses Guru Daerah</label>
                <input type="text" name="kode_akses_guru_daerah" value="{{ $settings['kode_akses_guru_daerah'] ?? 'GURUPMD' }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500 font-mono font-bold tracking-wider text-red-600">
                <p class="text-[11px] text-slate-500 mt-1">Kode akses rahasia ini dimasukkan oleh Guru Daerah sebelum memilih cabang untuk memantau data pemuda cabang.</p>
            </div>
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Tautan Pemantauan Cabang</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ route('guru-daerah.index') }}" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-600 font-mono text-[11px]">
                    <a href="{{ route('guru-daerah.index') }}" target="_blank" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-300" title="Buka Halaman">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Halaman dapat diakses oleh Guru Daerah tanpa memerlukan akun user/password admin.</p>
            </div>
        </div>
    </div>

    <!-- SUBMIT -->
    <div class="flex justify-end pt-2">
        <button type="submit" class="px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            <span>Simpan Seluruh Perubahan</span>
        </button>
    </div>
</form>

@endsection
