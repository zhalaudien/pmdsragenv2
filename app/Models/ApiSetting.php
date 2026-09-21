<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiSetting extends Model
{
    protected $table = 'api_settings';

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'description',
    ];

    public static function getDefaults(): array
    {
        return [
            'api_presensi_enabled' => [
                'group'       => 'general',
                'label'       => 'Status API Mobile Presensi',
                'type'        => 'boolean',
                'value'       => '1',
                'description' => 'Jika dinonaktifkan, aplikasi mobile presensi akan menampilkan pesan pemeliharaan.',
            ],
            'api_maintenance_message' => [
                'group'       => 'general',
                'label'       => 'Pesan Mode Pemeliharaan',
                'type'        => 'textarea',
                'value'       => 'Layanan API Presensi PMD sedang dalam pemeliharaan sistem berkala. Silakan coba kembali beberapa saat lagi.',
                'description' => 'Pesan yang akan ditampilkan kepada pengguna aplikasi mobile saat API dinonaktifkan.',
            ],
            'api_min_app_version' => [
                'group'       => 'versioning',
                'label'       => 'Versi Minimum Aplikasi (Force Update)',
                'type'        => 'text',
                'value'       => '1.0.0',
                'description' => 'Versi minimal aplikasi mobile Android yang diizinkan mengakses API.',
            ],
            'api_latest_app_version' => [
                'group'       => 'versioning',
                'label'       => 'Versi Terbaru Aplikasi (Latest Release)',
                'type'        => 'text',
                'value'       => '1.0.0',
                'description' => 'Versi rilis terkini dari aplikasi Android Presensi PMD.',
            ],
            'api_apk_download_url' => [
                'group'       => 'versioning',
                'label'       => 'URL Unduhan APK Terbaru',
                'type'        => 'text',
                'value'       => 'https://pmd.mtasragen.or.id/download/presensi-pmd-latest.apk',
                'description' => 'Link direct download file APK atau landing page unduhan aplikasi.',
            ],
            'api_broadcast_message' => [
                'group'       => 'general',
                'label'       => 'Pengumuman / Pesan Siaran Mobile',
                'type'        => 'textarea',
                'value'       => 'Selamat bertugas kepada Sekretaris Cabang. Pastikan selalu sinkronkan data presensi setelah kegiatan selesai.',
                'description' => 'Pengumuman teks berjalan atau banner yang muncul di dashboard aplikasi mobile.',
            ],
            'api_allow_offline_sync' => [
                'group'       => 'sync',
                'label'       => 'Izinkan Sinkronisasi Offline (Bulk Sync)',
                'type'        => 'boolean',
                'value'       => '1',
                'description' => 'Mengizinkan pengiriman presensi massal dari antrean lokal SQLite/Hive aplikasi mobile.',
            ],
            'api_max_bulk_sync' => [
                'group'       => 'sync',
                'label'       => 'Batas Maksimal Data per Bulk Sync',
                'type'        => 'number',
                'value'       => '300',
                'description' => 'Jumlah maksimal item presensi dalam satu payload request bulk sync.',
            ],
            'api_token_expiration_days' => [
                'group'       => 'security',
                'label'       => 'Masa Berlaku Token Login (Hari)',
                'type'        => 'number',
                'value'       => '90',
                'description' => 'Masa aktif Bearer Token login mobile sebelum harus login ulang.',
            ],
            'api_quick_chips' => [
                'group'       => 'presensi',
                'label'       => 'Preset Pilihan Keterangan Izin & Sakit (Quick Chips)',
                'type'        => 'json',
                'value'       => json_encode([
                    'Lembur / Shift Kerja',
                    'Tugas Belajar / Kuliah',
                    'Sedang di Luar Kota',
                    'Acara Keluarga',
                    'Kondisi Kurang Sehat / Sakit',
                    'Urusan Mendesak',
                ], JSON_UNESCAPED_UNICODE),
                'description' => 'Daftar pilihan tombol cepat alasan izin/sakit pada modal mobile.',
            ],
        ];
    }

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if ($setting) {
            return $setting->value;
        }

        $defaults = static::getDefaults();
        return $defaults[$key]['value'] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        $defaults = static::getDefaults();
        $def = $defaults[$key] ?? [];

        static::updateOrCreate(
            ['key' => $key],
            [
                'value'       => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value,
                'group'       => $def['group'] ?? 'general',
                'label'       => $def['label'] ?? ucwords(str_replace(['api_', '_'], ['', ' '], $key)),
                'type'        => $def['type'] ?? 'text',
                'description' => $def['description'] ?? null,
            ]
        );
    }

    public static function getAllSettings(): array
    {
        $defaults = static::getDefaults();
        $all = static::all()->keyBy('key');

        $result = [];
        foreach ($defaults as $key => $meta) {
            if (isset($all[$key])) {
                $result[$key] = $all[$key]->value;
            } else {
                $result[$key] = $meta['value'];
            }
        }

        return $result;
    }

    public static function seedDefaults(): void
    {
        foreach (static::getDefaults() as $key => $meta) {
            static::firstOrCreate(
                ['key' => $key],
                [
                    'group'       => $meta['group'],
                    'value'       => $meta['value'],
                    'type'        => $meta['type'],
                    'label'       => $meta['label'],
                    'description' => $meta['description'],
                ]
            );
        }
    }
}
