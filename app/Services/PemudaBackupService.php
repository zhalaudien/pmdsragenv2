<?php

namespace App\Services;

use App\Models\Pemuda;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PemudaBackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups/');
        $this->ensureBackupDirExists();
    }

    protected function ensureBackupDirExists(): void
    {
        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0755, true);
        }

        $htaccess = $this->backupDir . '.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Deny from all\n");
        }
    }

    public function getBackupDir(): string
    {
        return $this->backupDir;
    }

    public function getRelatedTables(): array
    {
        $tables = [
            'pemuda',
            'alamat',
            'pendidikan',
            'pekerjaan',
            'organisasi',
            'pemuda_skills',
            'pemuda_interests',
        ];

        if (Schema::hasTable('mta_sync_queue')) {
            $tables[] = 'mta_sync_queue';
        }

        return $tables;
    }

    public function getCountsSummary(): array
    {
        $summary = [];
        $tables = $this->getRelatedTables();

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl)) {
                $summary[$tbl] = DB::table($tbl)->count();
            } else {
                $summary[$tbl] = 0;
            }
        }

        return $summary;
    }

    protected function formatSqlValue(mixed $val): string
    {
        if ($val === null) {
            return 'NULL';
        }
        if (is_int($val) || is_float($val)) {
            return (string) $val;
        }
        return "'" . addslashes((string) $val) . "'";
    }

    public function generateSqlBackup(bool $saveToServer = false, string $prefix = 'backup_pemuda_'): array
    {
        $timestamp = date('Ymd_His');
        $filename  = $prefix . $timestamp . '.sql';
        $counts    = $this->getCountsSummary();

        $sql = "-- ========================================================\n";
        $sql .= "-- BACKUP DATA PEMUDA MTA KABUPATEN SRAGEN\n";
        $sql .= "-- Waktu Pembuatan : " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Total Pemuda    : " . number_format($counts['pemuda'] ?? 0) . " data\n";
        $sql .= "-- Sistem          : Sistem Pendataan Pemuda MTA Sragen v2\n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "SET NAMES utf8mb4;\n\n";

        $tables = $this->getRelatedTables();

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)->get()->map(fn($item) => (array) $item)->toArray();
            $totalRows = count($rows);

            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Data untuk tabel `{$table}` ({$totalRows} baris)\n";
            $sql .= "-- --------------------------------------------------------\n";

            if ($totalRows === 0) {
                $sql .= "-- Tidak ada data pada tabel `{$table}`\n\n";
                continue;
            }

            $sql .= "TRUNCATE TABLE `{$table}`;\n";

            $columns = array_keys($rows[0]);
            $colList = '`' . implode('`, `', $columns) . '`';

            $chunkSize = 100;
            $chunks = array_chunk($rows, $chunkSize);

            foreach ($chunks as $chunk) {
                $valuesList = [];
                foreach ($chunk as $row) {
                    $rowVals = [];
                    foreach ($columns as $col) {
                        $rowVals[] = $this->formatSqlValue($row[$col] ?? null);
                    }
                    $valuesList[] = '(' . implode(', ', $rowVals) . ')';
                }

                $sql .= "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $valuesList) . ";\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $sql .= "-- SELESAI RESTORE DATA PEMUDA\n";

        $filePath = null;
        if ($saveToServer) {
            $filePath = $this->backupDir . $filename;
            file_put_contents($filePath, $sql);
        }

        return [
            'filename'  => $filename,
            'content'   => $sql,
            'filepath'  => $filePath,
            'size'      => strlen($sql),
            'size_fmt'  => $this->formatBytes(strlen($sql)),
            'counts'    => $counts,
            'type'      => 'sql',
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    public function generateJsonBackup(bool $saveToServer = false, string $prefix = 'backup_pemuda_'): array
    {
        $timestamp = date('Ymd_His');
        $filename  = $prefix . $timestamp . '.json';
        $counts    = $this->getCountsSummary();

        $exportData = [
            'metadata' => [
                'system'     => 'Sistem Pendataan Pemuda MTA Sragen v2',
                'created_at' => date('Y-m-d H:i:s'),
                'counts'     => $counts,
            ],
            'tables'   => [],
        ];

        $tables = $this->getRelatedTables();
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $exportData['tables'][$table] = DB::table($table)->get()->map(fn($item) => (array) $item)->toArray();
            }
        }

        $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $filePath = null;
        if ($saveToServer) {
            $filePath = $this->backupDir . $filename;
            file_put_contents($filePath, $jsonContent);
        }

        return [
            'filename'  => $filename,
            'content'   => $jsonContent,
            'filepath'  => $filePath,
            'size'      => strlen($jsonContent),
            'size_fmt'  => $this->formatBytes(strlen($jsonContent)),
            'counts'    => $counts,
            'type'      => 'json',
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    public function listSavedBackups(): array
    {
        $files = glob($this->backupDir . 'backup_pemuda_*.*');
        $backups = [];

        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['sql', 'json', 'xlsx'], true)) {
                continue;
            }

            $basename = basename($file);
            $size     = filesize($file);
            $mtime    = filemtime($file);

            $backups[] = [
                'filename'   => $basename,
                'path'       => $file,
                'extension'  => $ext,
                'size'       => $size,
                'size_fmt'   => $this->formatBytes($size),
                'created_at' => date('Y-m-d H:i:s', $mtime),
            ];
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    public function deleteBackupFile(string $filename): bool
    {
        $clean = basename($filename);
        $path  = $this->backupDir . $clean;

        if (file_exists($path) && is_file($path)) {
            return @unlink($path);
        }

        return false;
    }

    public function getBackupFilePath(string $filename): ?string
    {
        $clean = basename($filename);
        $path  = $this->backupDir . $clean;

        if (file_exists($path) && is_file($path)) {
            return $path;
        }

        return null;
    }

    public function clearAllYouthData(?int $userId = null, string $confirmation = ''): array
    {
        if (trim($confirmation) !== 'HAPUS SEMUA DATA PEMUDA') {
            return [
                'success' => false,
                'message' => 'Konfirmasi tidak sesuai. Harap ketik persis: "HAPUS SEMUA DATA PEMUDA".',
            ];
        }

        $countsBefore = $this->getCountsSummary();

        // 1. Generate auto-backup before delete
        $backup = $this->generateSqlBackup(true, 'backup_sebelum_hapus_');

        // 2. Clear tables
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

            $tables = $this->getRelatedTables();
            foreach ($tables as $tbl) {
                if (Schema::hasTable($tbl)) {
                    DB::table($tbl)->truncate();
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            DB::commit();

            return [
                'success'       => true,
                'message'       => 'Seluruh data pemuda berhasil dihapus secara permanen. Backup otomatis telah disimpan.',
                'counts_before' => $countsBefore,
                'backup_file'   => $backup['filename'],
            ];
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ];
        }
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
