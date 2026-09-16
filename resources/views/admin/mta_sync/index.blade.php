@extends('admin.layouts.main')

@section('title', 'Integrasi & Sinkronisasi API MTA')

@section('content')

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Integrasi API &amp; Sinkronisasi Warga MTA</h2>
        <p class="text-xs text-slate-500 mt-0.5">Penyelarasan basis data pemuda lokal Sragen dengan server pusat Majlis Tafsir Al-Qur'an (<code>api.mta.or.id</code>).</p>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" id="btnTestConn" onclick="runTestConnection()" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition border border-slate-200 shadow-sm flex items-center gap-2">
            <i class="bi bi-wifi" id="iconTestConn"></i>
            <span>Uji Koneksi API</span>
        </button>
        <button type="button" onclick="runSyncCabang()" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2">
            <i class="bi bi-arrow-repeat"></i>
            <span>Sinkron Data Cabang</span>
        </button>
    </div>
</div>

<!-- API STATUS BANNER -->
<div class="p-4 rounded-2xl mb-6 flex items-center justify-between text-xs {{ ($testConn['connected'] ?? false) ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-amber-50 border border-amber-200 text-amber-800' }}">
    <div class="flex items-center gap-3">
        <span class="w-3 h-3 rounded-full {{ ($testConn['connected'] ?? false) ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }}"></span>
        <div>
            <strong class="font-bold">Status Server API MTA:</strong>
            <span>{{ $testConn['message'] ?? 'Memeriksa...' }}</span>
        </div>
    </div>
    <div class="text-[11px] font-mono text-slate-500 hidden sm:block">
        Endpoint: api.mta.or.id
    </div>
</div>

<!-- STATS SUMMARY -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Cabang Tersinkron</div>
        <div class="text-2xl font-black text-slate-900 mt-1">{{ $syncedCabangCount }} / {{ $totalCabangCount }}</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Cabang lokal terhubung UUID</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Pemuda Terverifikasi</div>
        <div class="text-2xl font-black text-emerald-600 mt-1">{{ $syncedPemudaCount }} / {{ $totalPemudaCount }}</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Tercatat di API MTA</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Cabang di Pusat</div>
        <div class="text-2xl font-black text-sky-600 mt-1">{{ count($mtaSragenCabang) }} Cabang</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Perwakilan Sragen (86)</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase">Antrean Sinkronisasi</div>
        <div class="text-2xl font-black text-amber-600 mt-1">{{ $queueStatus['pending'] ?? 0 }} Antrean</div>
        <div class="text-[10px] text-slate-400 mt-0.5">Proses verifikasi massal</div>
    </div>
</div>

<!-- QUEUE BATCH SYNC CARD -->
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 mb-4 border-b border-slate-100">
        <div>
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-cpu text-red-600"></i>
                <span>Sinkronisasi Otomatis &amp; Verifikasi Massal</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Periksa seluruh nama dan tanggal lahir pemuda terhadap database MTA pusat secara bertahap.</p>
        </div>

        <button type="button" onclick="startBatchSync()" id="btnStartBatch" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2 self-start sm:self-auto">
            <i class="bi bi-play-fill text-base"></i>
            <span>Mulai Sinkronisasi Massal</span>
        </button>
    </div>

    <!-- PROGRESS BAR -->
    <div id="batchProgressWrapper" class="hidden space-y-2">
        <div class="flex justify-between text-xs font-semibold text-slate-700">
            <span id="batchProgressStatus">Memproses antrean data...</span>
            <span id="batchProgressPercent">0%</span>
        </div>
        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
            <div id="batchProgressBar" class="h-full bg-red-600 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>
</div>

<!-- LOGS TABLE -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="bi bi-journal-text text-sky-600"></i>
            <span>Log Aktivitas Sinkronisasi Terakhir</span>
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                    <th class="py-3 px-4">Waktu</th>
                    <th class="py-3 px-4">Aksi / Tipe</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Keterangan</th>
                    <th class="py-3 px-4">Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentLogs as $l)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">{{ $l->created_at ? $l->created_at->format('d/m/Y H:i:s') : '-' }}</td>
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $l->action ?? $l->type ?? 'Sync' }}</td>
                        <td class="py-3 px-4">
                            @if(($l->status ?? '') === 'success')
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">Sukses</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-800 font-bold text-[10px]">Gagal</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $l->message ?? $l->details ?? '-' }}</td>
                        <td class="py-3 px-4 text-slate-500">{{ $l->user->name ?? 'Sistem' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">Belum ada riwayat aktivitas sinkronisasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function runTestConnection() {
        const btn = document.getElementById('btnTestConn');
        const icon = document.getElementById('iconTestConn');
        icon.className = 'spinner-border spinner-border-sm';

        fetch(`{{ route('admin.mta-sync.test-connection') }}`)
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                location.reload();
            })
            .catch(() => alert('Gagal menghubungi server API.'))
            .finally(() => {
                icon.className = 'bi bi-wifi';
            });
    }

    function runSyncCabang() {
        if (!confirm('Sinkronkan seluruh nama dan UUID cabang dengan API pusat?')) return;
        
        fetch(`{{ route('admin.mta-sync.sync-cabang') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            alert(res.message || 'Sinkronisasi cabang berhasil.');
            location.reload();
        })
        .catch(err => alert('Terjadi kesalahan sinkronisasi.'));
    }

    function startBatchSync() {
        if (!confirm('Mulai antrean verifikasi otomatis seluruh data pemuda?')) return;

        const pWrap = document.getElementById('batchProgressWrapper');
        const pBar = document.getElementById('batchProgressBar');
        const pText = document.getElementById('batchProgressStatus');
        const pPercent = document.getElementById('batchProgressPercent');

        pWrap.classList.remove('hidden');
        pBar.style.width = '10%';
        pPercent.textContent = '10%';
        pText.textContent = 'Menginisialisasi antrean...';

        fetch(`{{ route('admin.mta-sync.queue-init') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            pBar.style.width = '100%';
            pPercent.textContent = '100%';
            pText.textContent = 'Sinkronisasi selesai!';
            setTimeout(() => location.reload(), 1200);
        })
        .catch(err => {
            pText.textContent = 'Gagal memproses sinkronisasi.';
        });
    }
</script>
@endsection
