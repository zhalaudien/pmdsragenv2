@extends('layouts.app')

@section('title', !empty($data['is_update']) ? 'Pembaruan Data Berhasil - Pemuda MTA Sragen' : 'Pendaftaran Berhasil - Pemuda MTA Sragen')

@section('content')
<div class="min-h-[80vh] bg-gradient-to-b from-slate-50 to-slate-100/60 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Breadcrumb & Back button -->
        <div class="flex items-center justify-between mb-6">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full border border-slate-300 bg-white text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
            <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-200">
                <i class="bi bi-check2-circle"></i> {{ !empty($data['is_update']) ? 'Pembaruan Sukses' : 'Pendaftaran Sukses' }}
            </span>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden print:shadow-none print:border-none">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-red-600 via-red-700 to-rose-700 px-6 py-8 text-center text-white relative">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white text-emerald-600 shadow-lg mb-4 ring-8 ring-white/20 animate-bounce">
                    <i class="bi bi-patch-check-fill text-4xl text-emerald-600"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white mb-2">
                    {{ !empty($data['is_update']) ? 'Data Pemuda Berhasil Diperbarui!' : 'Pendaftaran Pemuda Berhasil!' }}
                </h1>
                <p class="text-red-100 text-sm max-w-xl mx-auto">
                    {{ !empty($data['is_update']) 
                        ? 'Data profil dan potensi diri Anda telah berhasil diselaraskan dalam sistem basis data Pemuda MTA Perwakilan Sragen.' 
                        : 'Data profil dan potensi diri Anda telah tersimpan dengan aman dalam sistem basis data Pemuda MTA Perwakilan Sragen.' 
                    }}
                </p>
            </div>

            <!-- Content Body -->
            <div class="p-6 sm:p-8 space-y-6">
                <!-- Registration Number Box -->
                <div class="bg-slate-50 border-2 border-dashed border-red-200 rounded-xl p-6 text-center">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Nomor Registrasi Resmi Pemuda:</div>
                    <div class="flex items-center justify-center gap-2">
                        <span id="regNumber" class="font-mono text-2xl sm:text-3xl font-extrabold text-red-600 tracking-wider">
                            {{ $data['registration_number'] ?? session('registration_number') ?? 'PMD-' . date('Ymd') . '-0001' }}
                        </span>
                        <button type="button" onclick="copyRegNumber()" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Salin Nomor Registrasi">
                            <i class="bi bi-clipboard text-lg" id="copyIcon"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-2 flex items-center justify-center gap-1">
                        <i class="bi bi-info-circle text-red-500"></i> Simpan nomor registrasi ini sebagai bukti resmi pendataan Anda.
                    </p>
                </div>

                <!-- Summary Info -->
                <div class="border border-slate-200 rounded-xl p-5 bg-white space-y-3">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
                        <i class="bi bi-person-vcard text-red-600"></i> Ringkasan Bukti Pendataan
                    </h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="p-3 bg-slate-50 rounded-lg">
                            <dt class="text-xs text-slate-500 font-medium">Nama Lengkap</dt>
                            <dd class="text-sm font-semibold text-slate-900 mt-0.5">{{ $data['name'] ?? '-' }}</dd>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg">
                            <dt class="text-xs text-slate-500 font-medium">Cabang / Wilayah</dt>
                            <dd class="text-sm font-semibold text-slate-900 mt-0.5">{{ $data['cabang_name'] ?? '-' }}</dd>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg">
                            <dt class="text-xs text-slate-500 font-medium">Waktu Registrasi</dt>
                            <dd class="text-sm font-semibold text-slate-900 mt-0.5">{{ $data['created_at'] ?? now()->format('d/m/Y H:i') }} WIB</dd>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg">
                            <dt class="text-xs text-slate-500 font-medium">Status Entri</dt>
                            <dd class="text-sm font-semibold mt-0.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <i class="bi bi-shield-check mr-1"></i> Tersimpan di Database
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Next Steps / Important Notes -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 space-y-1.5">
                    <div class="font-bold flex items-center gap-1.5 text-amber-900">
                        <i class="bi bi-lightbulb-fill text-amber-500"></i> Langkah Selanjutnya:
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-amber-800 pl-1">
                        <li>Data Anda akan diverifikasi oleh Admin Cabang / Wilayah masing-masing.</li>
                        <li>Pastikan nomor WhatsApp Anda aktif untuk menerima notifikasi dan agenda kegiatan pemuda.</li>
                        <li>Simpan bukti tanda terima ini dengan menekan tombol <strong>Cetak / Simpan PDF</strong> di bawah ini.</li>
                    </ul>
                </div>

                <!-- Action Buttons (Hidden on Print) -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2 print:hidden">
                    <button type="button" onclick="window.print()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 bg-white font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition">
                        <i class="bi bi-printer text-base"></i> Cetak / Simpan PDF
                    </button>
                    <a href="{{ route('pendataan.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 font-semibold text-sm text-white shadow-sm transition">
                        <i class="bi bi-plus-circle text-base"></i> Input Data Baru
                    </a>
                    <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 font-semibold text-sm text-white shadow-md shadow-red-500/20 transition">
                        <i class="bi bi-house-door text-base"></i> Ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyRegNumber() {
    const regText = document.getElementById('regNumber').innerText.trim();
    navigator.clipboard.writeText(regText).then(() => {
        const icon = document.getElementById('copyIcon');
        icon.className = 'bi bi-check-lg text-emerald-600 text-lg';
        setTimeout(() => {
            icon.className = 'bi bi-clipboard text-lg';
        }, 2000);
    });
}
</script>
@endsection
