<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MtaSyncService;

class MtaSyncQueueCommand extends Command
{
    protected $signature = 'mta:sync-queue 
                            {--cabang= : ID Cabang tertentu untuk disinkronkan}
                            {--only-pending=1 : Hanya sinkronkan data yang masih berstatus pending (1/0)}
                            {--init : Inisialisasi antrian baru sebelum memproses}';

    protected $description = 'Proses antrian sinkronisasi data pemuda dengan API MTA Pusat (Laju: 40 data / menit)';

    public function handle(MtaSyncService $syncService): int
    {
        $cabangId    = $this->option('cabang') ? (int) $this->option('cabang') : null;
        $onlyPending = $this->option('only-pending') !== '0';
        $shouldInit  = (bool) $this->option('init');

        $this->info("==================================================");
        $this->line(" ANTRIAN SINKRONISASI PEMUDA DENGAN API MTA PUSAT ");
        $this->comment(" Batas Laju Aman: 40 data / menit (1.5 detik/item)");
        $this->info("==================================================");

        if ($shouldInit) {
            $this->line("Menginisialisasi antrian baru...");
            $initRes = $syncService->initSyncQueue($cabangId, $onlyPending, null, true);
            if (!$initRes['success']) {
                $this->error($initRes['message']);
                return 1;
            }
            $this->info("Antrian berhasil disiapkan: {$initRes['total']} data. Estimasi: {$initRes['estimated_time']}");
        }

        $status = $syncService->getQueueStatus();
        $summary = $status['summary'];

        if ($summary['remaining'] === 0) {
            $this->warn("Tidak ada antrian pending. Gunakan opsi --init untuk membuat antrian baru.");
            return 0;
        }

        $this->line("Memulai pemrosesan antrian tersisa ({$summary['remaining']} data)...");

        $processedInRun = 0;

        while (true) {
            $result = $syncService->processNextQueueItem();

            if ($result['finished'] ?? false) {
                $this->newLine();
                $this->info("SELESAI! " . ($result['message'] ?? ''));
                $final = $result['summary'];
                $this->comment("Total: {$final['total']} | Terverifikasi: {$final['verified']} | Belum Terverifikasi: {$final['pending_unverified']} | Gagal: {$final['failed']}");
                break;
            }

            if (!empty($result['rate_limited'])) {
                $retryAfter = $result['retry_after'] ?? 10;
                $this->error("[RATELIMIT 429] Batas kuota tercapai. Menunggu {$retryAfter} detik...");
                sleep($retryAfter);
                continue;
            }

            $item = $result['item'];
            $statusLabel = $item['result'] === 'verified' ? 'TERVERIFIKASI' : ($item['result'] === 'pending' ? 'BELUM TERDATA' : 'GAGAL');

            $processedInRun++;
            $sum = $result['summary'];
            $msg = sprintf(
                "[%s/%s - %s%%] %s (#%s) [%s]: %s",
                $sum['processed'],
                $sum['total'],
                $sum['percent'],
                $item['name'] ?? '-',
                $item['pemuda_id'],
                $statusLabel,
                $item['message']
            );

            if ($item['result'] === 'verified') {
                $this->info($msg);
            } elseif ($item['result'] === 'pending') {
                $this->warn($msg);
            } else {
                $this->error($msg);
            }

            usleep(1500000); // 1.5 detik
        }

        return 0;
    }
}
