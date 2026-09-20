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
        <div class="text-2xl font-black text-amber-600 mt-1" id="topCardPendingCount">{{ $queueStatus['summary']['pending'] ?? 0 }} Antrean</div>
        <div class="text-[10px] text-slate-400 mt-0.5" id="topCardProcessedCount">{{ $queueStatus['summary']['processed'] ?? 0 }} diproses / {{ $queueStatus['summary']['total'] ?? 0 }} total</div>
    </div>
</div>

<!-- QUEUE BATCH SYNC CARD -->
<div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm mb-6" id="syncMainCard">
    <!-- CARD HEADER -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-base font-bold shadow-sm">
                    <i class="bi bi-cpu-fill"></i>
                </span>
                <h3 class="text-base font-black text-slate-900 tracking-tight">Antrean Sinkronisasi &amp; Verifikasi Massal MTA Pusat</h3>
            </div>
            <p class="text-xs text-slate-500 mt-1">Penyelarasan otomatis seluruh biodata pemuda lokal Sragen dengan basis data server pusat MTA secara bertahap dan aman.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 text-sky-700 text-xs font-bold border border-sky-200/60 shadow-xs">
                <i class="bi bi-speedometer2 text-sky-600"></i>
                <span>Laju: 40 Data / Menit (1.5 dtk/item)</span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200/60 shadow-xs">
                <i class="bi bi-cup-hot-fill text-amber-600"></i>
                <span>Jeda: Istirahat 10 Detik / 40 Data</span>
            </span>
        </div>
    </div>

    <!-- QUEUE SETUP & CONTROLS (IDLE STATE) -->
    <div id="queueSetupSection" class="pt-5 pb-2">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-5">
                <label for="queueCabangId" class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Target Cabang:</label>
                <select id="queueCabangId" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    <option value="">-- Seluruh Cabang (Semua Wilayah Sragen) --</option>
                    @foreach($localCabang as $c)
                        <option value="{{ $c->id }}">Cabang {{ $c->name }} ({{ $c->wilayah->name ?? 'Wilayah' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4">
                <label for="queueOnlyPending" class="block text-xs font-bold text-slate-700 mb-1.5">Kriteria Pemuda:</label>
                <select id="queueOnlyPending" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    <option value="1">Hanya yang Belum Terverifikasi (Pending) - Disarankan</option>
                    <option value="0">Semua Data Pemuda (Sinkronkan Ulang)</option>
                </select>
            </div>

            <div class="md:col-span-3 flex items-center gap-2">
                @if(($queueStatus['summary']['pending'] ?? 0) > 0)
                    <button type="button" onclick="resumeBatchSync()" id="btnResumeQueue" class="w-full px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition shadow-md flex items-center justify-center gap-2">
                        <i class="bi bi-play-circle-fill text-sm"></i>
                        <span>Lanjutkan ({{ $queueStatus['summary']['pending'] }})</span>
                    </button>
                    <button type="button" onclick="startBatchSync(true)" id="btnNewQueue" title="Buat antrean baru dari awal" class="p-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 font-bold text-xs transition shadow-sm flex items-center justify-center">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                @else
                    <button type="button" onclick="startBatchSync(false)" id="btnStartBatch" class="w-full px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center justify-center gap-2">
                        <i class="bi bi-play-circle-fill text-sm"></i>
                        <span>Mulai Sinkronisasi</span>
                    </button>
                @endif
            </div>
        </div>

        @if(($queueStatus['summary']['pending'] ?? 0) > 0)
            <div class="mt-3 p-3 rounded-xl bg-amber-50/80 border border-amber-200/80 flex items-center justify-between text-xs text-amber-800">
                <div class="flex items-center gap-2">
                    <i class="bi bi-info-circle-fill text-amber-600"></i>
                    <span>Terdapat <strong>{{ $queueStatus['summary']['pending'] }} data antrean aktif</strong> yang belum selesai diproses. Anda dapat langsung melanjutkannya atau membuat antrean baru.</span>
                </div>
                <button type="button" onclick="cancelBatchSync()" class="text-[11px] font-bold text-red-600 hover:underline">Hapus Antrean</button>
            </div>
        @endif
    </div>

    <!-- ACTIVE PROCESS & PROGRESS VIEW -->
    <div id="queueActiveWrapper" class="hidden pt-5 space-y-5">
        <!-- STATUS BANNER -->
        <div id="queueStatusBanner" class="p-4 rounded-2xl border transition-all duration-300 flex items-center justify-between text-xs bg-sky-50 border-sky-200 text-sky-800">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-sky-500"></span>
                </span>
                <div>
                    <strong class="font-bold block text-sm" id="queueStatusTitle">Memproses Antrean Data...</strong>
                    <span id="queueStatusDesc" class="text-xs text-slate-600">Mengirim permintaan verifikasi ke API MTA Pusat (Laju 40 data / menit).</span>
                </div>
            </div>

            <!-- REST COUNTDOWN BADGE -->
            <div id="queueRestBadge" class="hidden items-center gap-2 bg-amber-500 text-white px-3 py-1.5 rounded-xl font-bold shadow-sm">
                <i class="bi bi-cup-hot-fill"></i>
                <span>Jeda Istirahat: <span id="queueRestCountdown" class="font-mono text-base">10s</span></span>
            </div>
        </div>

        <!-- PROGRESS BAR & TIME REMAINING -->
        <div class="space-y-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs font-semibold text-slate-700">
                <div class="flex items-center gap-2">
                    <span>Progres:</span>
                    <span id="lblProcessed" class="font-bold text-slate-900">0</span>
                    <span>dari</span>
                    <span id="lblTotal" class="font-bold text-slate-900">0</span>
                    <span>data (</span><span id="lblPercent" class="font-black text-red-600">0%</span><span>)</span>
                </div>
                <div class="text-[11px] text-slate-500">
                    <i class="bi bi-clock-history mr-1"></i>Estimasi Sisa Waktu: <strong id="lblEstimatedTime" class="text-slate-800 font-mono">-</strong>
                </div>
            </div>

            <div class="w-full h-4 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200/60 shadow-inner">
                <div id="queueProgressBar" class="h-full bg-gradient-to-r from-red-600 via-rose-500 to-emerald-500 rounded-full transition-all duration-300 flex items-center justify-end pr-2 text-[10px] font-bold text-white shadow-sm" style="width: 0%;">
                    <span id="barPercentText">0%</span>
                </div>
            </div>
        </div>

        <!-- LIVE METRIC CARDS -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200/70">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sudah Diproses</div>
                <div class="text-xl font-black text-slate-900 mt-0.5">
                    <span id="metricProcessed">0</span> <span class="text-xs font-normal text-slate-400">/ <span id="metricTotal">0</span></span>
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">Sisa: <strong id="metricRemaining" class="text-slate-600">0</strong> data</div>
            </div>

            <div class="bg-emerald-50/60 rounded-2xl p-3.5 border border-emerald-200/60">
                <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-1">
                    <i class="bi bi-patch-check-fill"></i> Terverifikasi
                </div>
                <div class="text-xl font-black text-emerald-700 mt-0.5" id="metricVerified">0</div>
                <div class="text-[10px] text-emerald-600/80 mt-0.5">Cocok data MTA Pusat</div>
            </div>

            <div class="bg-amber-50/60 rounded-2xl p-3.5 border border-amber-200/60">
                <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider flex items-center gap-1">
                    <i class="bi bi-exclamation-circle-fill"></i> Belum Terdata
                </div>
                <div class="text-xl font-black text-amber-700 mt-0.5" id="metricPending">0</div>
                <div class="text-[10px] text-amber-600/80 mt-0.5">Tidak ditemukan di MTA</div>
            </div>

            <div class="bg-rose-50/60 rounded-2xl p-3.5 border border-rose-200/60">
                <div class="text-[10px] font-bold text-rose-600 uppercase tracking-wider flex items-center gap-1">
                    <i class="bi bi-x-circle-fill"></i> Gagal / Error
                </div>
                <div class="text-xl font-black text-rose-700 mt-0.5" id="metricFailed">0</div>
                <div class="text-[10px] text-rose-600/80 mt-0.5">Gangguan jaringan</div>
            </div>
        </div>

        <!-- CURRENT PROCESSING ITEM SPOTLIGHT -->
        <div class="bg-slate-50/90 border border-slate-200/80 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="space-y-0.5">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="bi bi-person-bounding-box text-slate-500"></i>
                    <span>Data Pemuda Sedang / Terakhir Diproses</span>
                </div>
                <div class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span id="currItemName" class="text-slate-900">-</span>
                    <span id="currItemCabang" class="text-xs font-semibold text-slate-500">(-)</span>
                </div>
                <div id="currItemMessage" class="text-xs text-slate-500 font-mono text-[11px]">-</div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span id="currItemBadge" class="px-3 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700 shadow-xs">
                    Menunggu...
                </span>
            </div>
        </div>

        <!-- PROCESS CONTROLS -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
            <div class="flex items-center gap-2">
                <button type="button" id="btnPauseResume" onclick="togglePauseSync()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition shadow-sm flex items-center gap-2">
                    <i class="bi bi-pause-fill" id="iconPauseResume"></i>
                    <span id="textPauseResume">Jeda Sementara</span>
                </button>
                <button type="button" onclick="cancelBatchSync()" class="px-4 py-2 rounded-xl bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 font-bold text-xs transition shadow-sm flex items-center gap-2">
                    <i class="bi bi-stop-circle"></i>
                    <span>Batalkan Antrean</span>
                </button>
            </div>

            <button type="button" id="btnReloadPage" onclick="location.reload()" class="hidden px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm flex items-center gap-2">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Selesai / Segarkan Halaman</span>
            </button>
        </div>

        <!-- REAL-TIME ACTIVITY STREAM -->
        <div class="mt-4 border border-slate-200/80 rounded-2xl overflow-hidden">
            <div class="bg-slate-50 px-4 py-3 border-b border-slate-200/80 flex items-center justify-between">
                <div class="text-xs font-bold text-slate-700 flex items-center gap-2">
                    <i class="bi bi-activity text-red-600"></i>
                    <span>Aktivitas Sinkronisasi Real-Time</span>
                </div>
                <span class="text-[10px] text-slate-400 font-mono">Laju: 40/mnt • Jeda 10s tiap 40</span>
            </div>
            <div class="max-h-60 overflow-y-auto overflow-x-auto divide-y divide-slate-100 text-xs bg-white" id="liveActivityLog">
                <div id="liveActivityEmpty" class="py-6 text-center text-slate-400 text-xs">
                    Belum ada data diproses dalam sesi ini.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LOGS TABLE -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="bi bi-journal-text text-sky-600"></i>
            <span>Log Aktivitas Sinkronisasi Terakhir</span>
        </h3>
        <span class="text-[11px] text-slate-400">Tercatat di sistem database</span>
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
    const csrfToken = '{{ csrf_token() }}';

    // State Variables
    let isRunning = false;
    let isPaused = false;
    let isResting = false;
    let itemsProcessedInSession = 0;
    let hasRestedForCurrentBatch = false;

    // Rate Limit & Rest Configuration:
    // 40 data / menit = 1 data per 1.5 detik (1500 ms)
    // Istirahat 10 detik setiap 40 data
    const RATE_DELAY_MS = 1500;
    const REST_EVERY_ITEMS = 40;
    const REST_SECONDS = 10;

    function runTestConnection() {
        const btn = document.getElementById('btnTestConn');
        const icon = document.getElementById('iconTestConn');
        icon.className = 'spinner-border spinner-border-sm';

        fetch(`{{ route('admin.mta-sync.test-connection') }}`)
            .then(res => res.json())
            .then(res => {
                alert(res.data?.message || res.message || 'Koneksi API berhasil diuji.');
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
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => {
            alert('Sinkronisasi cabang selesai diproses.');
            location.reload();
        })
        .catch(err => alert('Terjadi kesalahan sinkronisasi cabang.'));
    }

    // Helper Sleep
    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // UI State Handlers
    function showActiveProcessUI() {
        document.getElementById('queueActiveWrapper').classList.remove('hidden');
        document.getElementById('queueSetupSection').classList.add('opacity-50', 'pointer-events-none');
    }

    function hideActiveProcessUI() {
        document.getElementById('queueActiveWrapper').classList.add('hidden');
        document.getElementById('queueSetupSection').classList.remove('opacity-50', 'pointer-events-none');
    }

    // Start Batch Sync (New Queue)
    async function startBatchSync(forceReset = false) {
        const cabangId = document.getElementById('queueCabangId').value;
        const onlyPending = document.getElementById('queueOnlyPending').value;

        const confirmMsg = forceReset
            ? 'Buat antrean baru dari awal dan mulai proses sinkronisasi 40 data/menit?'
            : 'Mulai antrean sinkronisasi dan verifikasi data pemuda dengan MTA Pusat?';

        if (!confirm(confirmMsg)) return;

        showActiveProcessUI();
        setBannerState('init', 'Menyiapkan Antrean Data...', 'Mengambil data pemuda lokal untuk dimasukkan ke antrean...');

        try {
            const initRes = await fetch(`{{ route('admin.mta-sync.queue-init') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    cabang_id: cabangId || null,
                    only_pending: onlyPending === '1'
                })
            });

            const data = await initRes.json();

            if (!data.success) {
                alert(data.message || 'Gagal menyiapkan antrean sinkronisasi.');
                hideActiveProcessUI();
                return;
            }

            // Update UI Counters
            updateSummaryUI(data.summary);
            itemsProcessedInSession = 0;
            hasRestedForCurrentBatch = false;

            // Start Processing Loop
            startProcessingLoop();

        } catch (err) {
            alert('Terjadi kesalahan saat menginisialisasi antrean: ' + err.message);
            hideActiveProcessUI();
        }
    }

    // Resume Existing Queue
    async function resumeBatchSync() {
        showActiveProcessUI();
        setBannerState('init', 'Memuat Antrean Tersisa...', 'Mengambil status antrean aktif...');

        try {
            const statusRes = await fetch(`{{ route('admin.mta-sync.queue-status') }}`);
            const data = await statusRes.json();

            updateSummaryUI(data.summary);
            itemsProcessedInSession = 0;
            hasRestedForCurrentBatch = false;

            startProcessingLoop();
        } catch (err) {
            alert('Gagal mengambil status antrean: ' + err.message);
            hideActiveProcessUI();
        }
    }

    // Core Processing Loop
    async function startProcessingLoop() {
        isRunning = true;
        isPaused = false;
        isResting = false;

        document.getElementById('btnPauseResume').classList.remove('hidden');
        document.getElementById('btnReloadPage').classList.add('hidden');
        updatePauseResumeButtonUI();

        setBannerState('processing', 'Sedang Memproses Antrean...', 'Memvalidasi data pemuda ke API MTA Pusat (Laju: 40 data / menit).');

        while (isRunning) {
            // Check Paused State
            if (isPaused) {
                await sleep(400);
                continue;
            }

            // Check Rest Requirement: Setiap 40 data yang diproses, istirahat selama 10 detik
            if (itemsProcessedInSession > 0 && itemsProcessedInSession % REST_EVERY_ITEMS === 0 && !hasRestedForCurrentBatch) {
                await doRestCountdown(REST_SECONDS);
                hasRestedForCurrentBatch = true;
                continue;
            }

            try {
                // Request next item to process
                const res = await fetch(`{{ route('admin.mta-sync.queue-process-item') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await res.json();

                if (result.finished) {
                    handleSyncFinished(result.summary);
                    break;
                }

                itemsProcessedInSession++;
                hasRestedForCurrentBatch = false;

                // Update UI with processed item & latest summary
                updateItemUI(result.item);
                updateSummaryUI(result.summary);
                appendLiveLog(result.item);

                // Delay 1.5 detik (40 data per menit)
                await sleep(RATE_DELAY_MS);

            } catch (err) {
                console.error('Error processing item:', err);
                // Jeda 2 detik jika terjadi error jaringan sebelum mencoba item berikutnya
                await sleep(2000);
            }
        }
    }

    // Rest Countdown: 10 Detik setiap 40 data
    async function doRestCountdown(seconds) {
        isResting = true;
        setBannerState('resting', '☕ Sedang Istirahat 10 Detik (Batch 40 Data Selesai)', 'Memberi jeda aman ke server MTA Pusat setelah memproses 40 data...');
        
        const badge = document.getElementById('queueRestBadge');
        const countdownEl = document.getElementById('queueRestCountdown');
        badge.classList.remove('hidden');
        badge.classList.add('flex');

        for (let s = seconds; s >= 0; s--) {
            if (!isRunning) break;
            countdownEl.textContent = s + 's';
            await sleep(1000);
        }

        badge.classList.add('hidden');
        badge.classList.remove('flex');
        isResting = false;

        if (isRunning && !isPaused) {
            setBannerState('processing', 'Melanjutkan Pemrosesan Antrean...', 'Memvalidasi data pemuda ke API MTA Pusat (Laju: 40 data / menit).');
        }
    }

    // Toggle Pause / Resume
    function togglePauseSync() {
        if (!isRunning) return;

        isPaused = !isPaused;
        updatePauseResumeButtonUI();

        if (isPaused) {
            setBannerState('paused', 'Antrean Dijeda', 'Sinkronisasi dijeda oleh pengguna. Klik "Lanjutkan" untuk meneruskan.');
        } else {
            setBannerState('processing', 'Melanjutkan Pemrosesan Antrean...', 'Memvalidasi data pemuda ke API MTA Pusat (Laju: 40 data / menit).');
        }
    }

    function updatePauseResumeButtonUI() {
        const btn = document.getElementById('btnPauseResume');
        const icon = document.getElementById('iconPauseResume');
        const text = document.getElementById('textPauseResume');

        if (isPaused) {
            btn.className = 'px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition shadow-sm flex items-center gap-2';
            icon.className = 'bi bi-play-fill text-sm';
            text.textContent = 'Lanjutkan Sinkronisasi';
        } else {
            btn.className = 'px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition shadow-sm flex items-center gap-2';
            icon.className = 'bi bi-pause-fill text-sm';
            text.textContent = 'Jeda Sementara';
        }
    }

    // Cancel Queue
    async function cancelBatchSync() {
        if (!confirm('Yakin ingin membatalkan dan menghentikan seluruh sisa antrean sinkronisasi?')) return;

        isRunning = false;
        isPaused = false;
        isResting = false;

        try {
            const res = await fetch(`{{ route('admin.mta-sync.queue-cancel') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            setBannerState('canceled', 'Antrean Dibatalkan', data.message || 'Antrean sinkronisasi berhasil dihentikan.');
            document.getElementById('btnPauseResume').classList.add('hidden');
            document.getElementById('btnReloadPage').classList.remove('hidden');
        } catch (err) {
            alert('Gagal membatalkan antrean: ' + err.message);
        }
    }

    // Finished Handler
    function handleSyncFinished(summary) {
        isRunning = false;
        isPaused = false;
        isResting = false;

        updateSummaryUI(summary);
        setBannerState('completed', '🎉 Sinkronisasi Selesai!', `Seluruh ${summary.total} data pemuda berhasil diproses. Terverifikasi: ${summary.verified}, Belum Terdata: ${summary.pending_unverified}, Gagal: ${summary.failed}.`);

        document.getElementById('btnPauseResume').classList.add('hidden');
        document.getElementById('btnReloadPage').classList.remove('hidden');
        document.getElementById('queueProgressBar').style.width = '100%';
        document.getElementById('barPercentText').textContent = '100%';
        document.getElementById('lblPercent').textContent = '100%';
    }

    // Banner State Styler
    function setBannerState(state, title, desc) {
        const banner = document.getElementById('queueStatusBanner');
        const titleEl = document.getElementById('queueStatusTitle');
        const descEl = document.getElementById('queueStatusDesc');

        titleEl.textContent = title;
        descEl.textContent = desc;

        // Reset classes
        banner.className = 'p-4 rounded-2xl border transition-all duration-300 flex items-center justify-between text-xs ';

        if (state === 'resting') {
            banner.className += 'bg-amber-50 border-amber-300 text-amber-900 shadow-sm';
        } else if (state === 'paused') {
            banner.className += 'bg-yellow-50 border-yellow-300 text-yellow-800';
        } else if (state === 'completed') {
            banner.className += 'bg-emerald-50 border-emerald-300 text-emerald-800';
        } else if (state === 'canceled') {
            banner.className += 'bg-slate-100 border-slate-300 text-slate-700';
        } else {
            banner.className += 'bg-sky-50 border-sky-200 text-sky-800';
        }
    }

    // Update Summary & Progress Bar
    function updateSummaryUI(summary) {
        if (!summary) return;

        const total = summary.total || 0;
        const processed = summary.processed || 0;
        const remaining = summary.remaining || 0;
        const percent = summary.percent || 0;

        document.getElementById('lblProcessed').textContent = processed;
        document.getElementById('lblTotal').textContent = total;
        document.getElementById('lblPercent').textContent = percent + '%';
        document.getElementById('lblEstimatedTime').textContent = summary.estimated_formatted || '-';

        const pBar = document.getElementById('queueProgressBar');
        pBar.style.width = Math.min(100, Math.max(0, percent)) + '%';
        document.getElementById('barPercentText').textContent = percent + '%';

        document.getElementById('metricProcessed').textContent = processed;
        document.getElementById('metricTotal').textContent = total;
        document.getElementById('metricRemaining').textContent = remaining;
        document.getElementById('metricVerified').textContent = summary.verified || 0;
        document.getElementById('metricPending').textContent = summary.pending_unverified || 0;
        document.getElementById('metricFailed').textContent = summary.failed || 0;

        // Top Summary Cards
        const topPending = document.getElementById('topCardPendingCount');
        const topProcessed = document.getElementById('topCardProcessedCount');
        if (topPending) topPending.textContent = remaining + ' Antrean';
        if (topProcessed) topProcessed.textContent = `${processed} diproses / ${total} total`;
    }

    // Update Current Spotlight Item
    function updateItemUI(item) {
        if (!item) return;

        document.getElementById('currItemName').textContent = item.name || '-';
        document.getElementById('currItemCabang').textContent = `(${item.cabang_name || 'Cabang -'})`;
        document.getElementById('currItemMessage').textContent = item.message || '-';

        const badge = document.getElementById('currItemBadge');
        if (item.result === 'verified') {
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200';
            badge.innerHTML = '<i class="bi bi-patch-check-fill mr-1"></i> Terverifikasi';
        } else if (item.result === 'pending') {
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200';
            badge.innerHTML = '<i class="bi bi-info-circle mr-1"></i> Belum Terdata';
        } else {
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200';
            badge.innerHTML = '<i class="bi bi-x-circle mr-1"></i> Gagal';
        }
    }

    // Append to Real-Time Live Activity Feed
    function appendLiveLog(item) {
        const feed = document.getElementById('liveActivityLog');
        const emptyMsg = document.getElementById('liveActivityEmpty');
        if (emptyMsg) emptyMsg.remove();

        const row = document.createElement('div');
        row.className = 'p-3 flex items-center justify-between gap-3 text-xs hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0';

        let badgeHtml = '';
        if (item.result === 'verified') {
            badgeHtml = '<span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] whitespace-nowrap"><i class="bi bi-patch-check-fill mr-1"></i>Terverifikasi</span>';
        } else if (item.result === 'pending') {
            badgeHtml = '<span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px] whitespace-nowrap"><i class="bi bi-info-circle mr-1"></i>Belum Terdata</span>';
        } else {
            badgeHtml = '<span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px] whitespace-nowrap"><i class="bi bi-x-circle mr-1"></i>Gagal</span>';
        }

        row.innerHTML = `
            <div class="flex items-center gap-3 overflow-hidden">
                <span class="font-mono text-[10px] text-slate-400 whitespace-nowrap">${item.processed_at || new Date().toLocaleTimeString('id-ID')}</span>
                <div class="truncate">
                    <strong class="text-slate-900 font-bold mr-1">${item.name || '-'}</strong>
                    <span class="text-slate-400 text-[11px]">(${item.cabang_name || '-'})</span>
                    <div class="text-[11px] text-slate-500 truncate">${item.message || '-'}</div>
                </div>
            </div>
            <div>${badgeHtml}</div>
        `;

        feed.insertBefore(row, feed.firstChild);

        // Keep maximum 50 rows in memory
        while (feed.children.length > 50) {
            feed.removeChild(feed.lastChild);
        }
    }

    // On Page Load: Pre-load recent processed items if available
    document.addEventListener('DOMContentLoaded', () => {
        @if(!empty($queueStatus['recent_processed']))
            const recent = @json($queueStatus['recent_processed']);
            if (recent && recent.length > 0) {
                recent.forEach(item => appendLiveLog(item));
            }
        @endif
    });
</script>
@endsection
