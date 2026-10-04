<?php

namespace App\Console\Commands;

use App\Services\GdmCabangSyncService;
use Illuminate\Console\Command;

class SyncGdmCabangCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gdm:sync-cabang {--tahun= : Tahun penugasan yang disinkronkan (default: tahun sekarang)} {--no-alamat : Lewati sinkronisasi alamat domisili GDM}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan data penugasan Guru Daerah Muda (GDM) dengan Master Cabang dan Alamat Domisili Pemuda/Warga';

    /**
     * Execute the console command.
     */
    public function handle(GdmCabangSyncService $syncService): int
    {
        $tahun = $this->option('tahun') ? (int) $this->option('tahun') : (int) date('Y');
        $syncAlamat = !$this->option('no-alamat');

        $this->info("=== Sinkronisasi Penugasan GDM dengan Master Cabang (Tahun {$tahun}) ===");

        $result = $syncService->syncAll($tahun, $syncAlamat);

        if ($result['status'] === 'error') {
            $this->error("Sinkronisasi gagal: " . $result['message']);
            return Command::FAILURE;
        }

        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Tahun Penugasan', $result['tahun']],
                ['Cabang Disinkronkan', $result['synced_cabang_count']],
                ['Penugasan Disinkronkan (Jadwal/Hari/Jam)', $result['synced_penugasan_count']],
                ['Penugasan Baru Dibuat dari Ustadz Cabang', $result['created_penugasan_count']],
                ['Alamat Domisili Disinkronkan', $result['synced_alamat_count'] ?? 0],
            ]
        );

        if (!empty($result['logs'])) {
            $this->line('');
            $this->info('Rincian Log Sinkronisasi:');
            foreach ($result['logs'] as $log) {
                $this->line("  - {$log}");
            }
        }

        $this->info($result['message']);
        return Command::SUCCESS;
    }
}
