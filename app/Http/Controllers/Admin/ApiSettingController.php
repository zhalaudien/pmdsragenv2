<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiSetting;
use App\Models\KegiatanPresensi;
use App\Models\PresensiDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiSettingController extends Controller
{
    /**
     * Tampilkan halaman pengelolaan Seting API Presensi Mobile
     */
    public function index()
    {
        $settings = ApiSetting::getAllSettings();

        // Decode quick chips
        $quickChips = json_decode($settings['api_quick_chips'] ?? '[]', true);
        if (!is_array($quickChips)) {
            $quickChips = [];
        }

        // Statistik
        $totalSessions = KegiatanPresensi::count();
        $totalPresensi = PresensiDetail::count();
        $totalTokens   = DB::table('personal_access_tokens')->count();

        // Daftar token mobile aktif (terbaru)
        $tokens = DB::table('personal_access_tokens')
            ->join('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->leftJoin('cabang', 'users.cabang_id', '=', 'cabang.id')
            ->select([
                'personal_access_tokens.id',
                'personal_access_tokens.name as device_name',
                'personal_access_tokens.last_used_at',
                'personal_access_tokens.created_at',
                'users.name as user_name',
                'users.username',
                'cabang.name as cabang_name',
            ])
            ->orderBy('personal_access_tokens.created_at', 'DESC')
            ->limit(20)
            ->get();

        // Katalog endpoint API
        $endpoints = $this->getEndpointCatalog();

        return view('admin.api_settings.index', [
            'title'         => 'Pengaturan API Mobile Presensi',
            'settings'      => $settings,
            'quickChips'    => $quickChips,
            'totalSessions' => $totalSessions,
            'totalPresensi' => $totalPresensi,
            'totalTokens'   => $totalTokens,
            'tokens'        => $tokens,
            'endpoints'     => $endpoints,
        ]);
    }

    /**
     * Simpan pembaruan pengaturan API
     */
    public function update(Request $request)
    {
        $request->validate([
            'api_presensi_enabled'      => 'required|in:0,1',
            'api_maintenance_message'   => 'nullable|string|max:500',
            'api_min_app_version'       => 'required|string|max:20',
            'api_latest_app_version'    => 'required|string|max:20',
            'api_apk_download_url'      => 'nullable|url|max:500',
            'api_broadcast_message'     => 'nullable|string|max:1000',
            'api_allow_offline_sync'    => 'required|in:0,1',
            'api_max_bulk_sync'         => 'required|integer|min:10|max:1000',
            'api_token_expiration_days' => 'required|integer|min:1|max:365',
        ], [
            'api_min_app_version.required'    => 'Versi minimum aplikasi wajib diisi.',
            'api_latest_app_version.required' => 'Versi terbaru aplikasi wajib diisi.',
            'api_apk_download_url.url'        => 'Format URL unduhan APK harus berupa URL valid (http/https).',
            'api_max_bulk_sync.required'      => 'Batas bulk sync wajib diisi.',
            'api_token_expiration_days.required' => 'Masa aktif token login wajib diisi.',
        ]);

        ApiSetting::set('api_presensi_enabled', $request->input('api_presensi_enabled'));
        ApiSetting::set('api_maintenance_message', trim((string)$request->input('api_maintenance_message')));
        ApiSetting::set('api_min_app_version', trim((string)$request->input('api_min_app_version')));
        ApiSetting::set('api_latest_app_version', trim((string)$request->input('api_latest_app_version')));
        ApiSetting::set('api_apk_download_url', trim((string)$request->input('api_apk_download_url')));
        ApiSetting::set('api_broadcast_message', trim((string)$request->input('api_broadcast_message')));
        ApiSetting::set('api_allow_offline_sync', $request->input('api_allow_offline_sync'));
        ApiSetting::set('api_max_bulk_sync', $request->input('api_max_bulk_sync'));
        ApiSetting::set('api_token_expiration_days', $request->input('api_token_expiration_days'));

        // Quick chips
        $chipsRaw = $request->input('quick_chips', []);
        if (is_string($chipsRaw)) {
            $chipsList = array_filter(array_map('trim', explode("\n", $chipsRaw)));
        } elseif (is_array($chipsRaw)) {
            $chipsList = array_values(array_filter(array_map('trim', $chipsRaw)));
        } else {
            $chipsList = [];
        }
        ApiSetting::set('api_quick_chips', json_encode(array_values($chipsList), JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.api-settings.index')->with('success', 'Pengaturan API Presensi Mobile berhasil disimpan.');
    }

    /**
     * Cabut / hapus satu token akses tertentu
     */
    public function revokeToken($id)
    {
        DB::table('personal_access_tokens')->where('id', $id)->delete();

        return redirect()->route('admin.api-settings.index')->with('success', 'Sesi perangkat / token berhasil dicabut.');
    }

    /**
     * Cabut semua token akses (Force logout seluruh aplikasi mobile)
     */
    public function revokeAllTokens()
    {
        $count = DB::table('personal_access_tokens')->count();
        DB::table('personal_access_tokens')->truncate();

        return redirect()->route('admin.api-settings.index')->with('success', "Seluruh sesi perangkat ({$count} token) berhasil dicabut.");
    }

    /**
     * Kembalikan pengaturan ke nilai standar
     */
    public function resetDefaults()
    {
        ApiSetting::seedDefaults();

        foreach (ApiSetting::getDefaults() as $key => $meta) {
            ApiSetting::set($key, $meta['value']);
        }

        return redirect()->route('admin.api-settings.index')->with('success', 'Pengaturan API berhasil dikembalikan ke nilai bawaan pabrik.');
    }

    /**
     * Daftar katalog endpoint REST API untuk dokumentasi superadmin
     */
    private function getEndpointCatalog(): array
    {
        return [
            [
                'method'      => 'GET',
                'endpoint'    => '/api/v1/config',
                'scope'       => 'Publik',
                'description' => 'Konfigurasi aplikasi mobile (status online, versi minimum, broadcast info, quick chips).',
                'auth'        => 'None',
            ],
            [
                'method'      => 'POST',
                'endpoint'    => '/api/v1/auth/login',
                'scope'       => 'Publik',
                'description' => 'Login sekretaris cabang menggunakan username/email & password. Mengembalikan Bearer Token.',
                'auth'        => 'None',
            ],
            [
                'method'      => 'POST',
                'endpoint'    => '/api/v1/auth/logout',
                'scope'       => 'Sesi User',
                'description' => 'Revoke / hapus token otentikasi aktif pada perangkat.',
                'auth'        => 'Bearer Token',
            ],
            [
                'method'      => 'GET',
                'endpoint'    => '/api/v1/auth/me',
                'scope'       => 'Sesi User',
                'description' => 'Ambil profil user aktif beserta rincian cabang yang dikelola.',
                'auth'        => 'Bearer Token',
            ],
            [
                'method'      => 'GET',
                'endpoint'    => '/api/v1/cabang/pemuda',
                'scope'       => 'Scope Cabang',
                'description' => 'Ambil seluruh data pemuda aktif di cabang untuk instant search & cache lokal SQLite/Hive.',
                'auth'        => 'Bearer Token',
            ],
            [
                'method'      => 'GET',
                'endpoint'    => '/api/v1/kegiatan',
                'scope'       => 'Scope Cabang',
                'description' => 'Daftar sesi agenda presensi cabang beserta ringkasan statistik kehadiran.',
                'auth'        => 'Bearer Token',
            ],
            [
                'method'      => 'POST',
                'endpoint'    => '/api/v1/kegiatan',
                'scope'       => 'Scope Cabang',
                'description' => 'Buat sesi kegiatan/pengajian presensi baru untuk cabang yang dikelola.',
                'auth'        => 'Bearer Token',
            ],
            [
                'method'      => 'GET',
                'endpoint'    => '/api/v1/kegiatan/{id}',
                'scope'       => 'Scope Cabang',
                'description' => 'Detail kegiatan presensi beserta seluruh checklist anggota pemuda di cabang tersebut.',
                'auth'        => 'Bearer Token',
            ],
            [
                'method'      => 'PUT',
                'endpoint'    => '/api/v1/kegiatan/{id}/status',
                'scope'       => 'Scope Cabang',
                'description' => 'Kunci / selesaikan sesi kegiatan presensi agar data kehadiran menjadi final.',
                'auth'        => 'Bearer Token',
            ],
            [
                'method'      => 'POST',
                'endpoint'    => '/api/v1/kegiatan/{id}/presensi/single',
                'scope'       => 'Scope Cabang',
                'description' => 'Pencatatan / perubahan status kehadiran realtime untuk 1 orang pemuda (Hadir/Izin/Sakit/Alpa).',
                'auth'        => 'Bearer Token',
            ],
            [
                'method'      => 'POST',
                'endpoint'    => '/api/v1/kegiatan/{id}/presensi/bulk',
                'scope'       => 'Scope Cabang',
                'description' => 'Sinkronisasi massal antrean presensi offline yang tersimpan di memori ponsel Flutter.',
                'auth'        => 'Bearer Token',
            ],
            [
                'method'      => 'GET',
                'endpoint'    => '/api/v1/kegiatan/{id}/rekap',
                'scope'       => 'Scope Cabang',
                'description' => 'Data statistik kehadiran & format teks laporan siap kirim ke WhatsApp Pengurus.',
                'auth'        => 'Bearer Token',
            ],
        ];
    }
}
