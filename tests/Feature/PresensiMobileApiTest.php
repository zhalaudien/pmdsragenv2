<?php

namespace Tests\Feature;

use App\Models\ApiSetting;
use App\Models\Cabang;
use App\Models\KegiatanPresensi;
use App\Models\Pemuda;
use App\Models\PresensiDetail;
use App\Models\User;
use Tests\TestCase;

class PresensiMobileApiTest extends TestCase
{
    protected ?User $adminCabang = null;
    protected ?User $superadmin = null;
    protected ?Cabang $cabang = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
        $this->cabang     = Cabang::first();

        // Cari atau gunakan user admin_cabang
        $this->adminCabang = User::whereNotNull('cabang_id')->where('status', 1)->first();

        if (!$this->adminCabang && $this->cabang) {
            $this->adminCabang = User::create([
                'name'      => 'Sekretaris Test',
                'username'  => 'sekretaris_test',
                'email'     => 'sekretaris@test.com',
                'password'  => bcrypt('password123'),
                'role_id'   => 3, // admin_cabang
                'cabang_id' => $this->cabang->id,
                'status'    => 1,
            ]);
        }

        // Pastikan setting default terisi
        ApiSetting::seedDefaults();
        ApiSetting::set('api_presensi_enabled', '1');
    }

    public function test_api_config_endpoint_is_public(): void
    {
        $response = $this->getJson('/api/v1/config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'is_online',
                    'maintenance_mode',
                    'min_app_version',
                    'latest_app_version',
                    'quick_chips',
                ],
                'meta',
            ]);

        $this->assertTrue($response->json('data.is_online'));
    }

    public function test_api_login_validation_fails_on_empty_request(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_api_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => $this->adminCabang->username,
            'password' => 'wrongpassword-xyz',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_api_login_success_and_returns_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login'       => $this->adminCabang->username,
            'password'    => 'password123', // if default seeder or fallback
            'device_name' => 'Flutter Test Device',
        ]);

        // Jika password di DB bukan password123, update password user test agar lulus
        if ($response->status() === 401) {
            $this->adminCabang->update(['password' => bcrypt('password123')]);
            $response = $this->postJson('/api/v1/auth/login', [
                'login'       => $this->adminCabang->username,
                'password'    => 'password123',
                'device_name' => 'Flutter Test Device',
            ]);
        }

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'username', 'cabang_id'],
                    'cabang' => ['id', 'name'],
                    'app_config',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_api_auth_me_with_bearer_token(): void
    {
        $token = $this->adminCabang->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'user' => [
                        'id' => $this->adminCabang->id,
                    ],
                ],
            ]);
    }

    public function test_api_cabang_pemuda_scope_isolation(): void
    {
        $token = $this->adminCabang->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/cabang/pemuda');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => [
                    'cabang' => ['id', 'name'],
                    'counts' => ['total', 'pemuda', 'pemudi'],
                ],
            ]);

        // Verifikasi semua pemuda dalam list memiliki cabang_id yang sama dengan user
        $list = $response->json('data');
        foreach ($list as $p) {
            $this->assertEquals($this->adminCabang->cabang_id, $p['cabang_id']);
        }
    }

    public function test_api_kegiatan_and_presensi_flow(): void
    {
        $token = $this->adminCabang->createToken('test_token')->plainTextToken;
        $cabangId = $this->adminCabang->cabang_id;

        // 1. Buat Sesi Kegiatan Presensi
        $createKegiatanResp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/kegiatan', [
                'nama_kegiatan'  => 'Pengajian Rutin Malam Ahad Testing',
                'tanggal'        => date('Y-m-d'),
                'jam_mulai'      => '19:30',
                'jam_selesai'    => '21:30',
                'lokasi'         => 'Masjid Al-Hidayah Testing',
                'pemateri'       => 'Ustadz Ahmad Fauzi',
                'target_peserta' => 'semua',
                'catatan'        => 'Testing sesi presensi',
            ]);

        $createKegiatanResp->assertStatus(201);
        $kegiatanId = $createKegiatanResp->json('data.id');
        $this->assertNotNull($kegiatanId);

        // 2. Ambil Daftar Kegiatan
        $listResp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/kegiatan');
        $listResp->assertStatus(200);

        // 3. Ambil Detail Kegiatan
        $detailResp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/kegiatan/{$kegiatanId}");
        $detailResp->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'kegiatan',
                    'summary',
                    'checklist',
                ],
            ]);

        // Ambil atau buat pemuda di cabang ini untuk presensi
        $pemuda = Pemuda::where('cabang_id', $cabangId)->first();
        if (!$pemuda) {
            $pemuda = Pemuda::create([
                'cabang_id'           => $cabangId,
                'registration_number' => Pemuda::generateRegistrationNumber($cabangId, '2000-01-01'),
                'name'                => 'Pemuda Test Presensi',
                'gender'              => 'L',
                'status_verifikasi'   => 'verified',
                'status_data'         => 'active',
            ]);
        }

        // 4. Catat Presensi Single (Realtime)
        $singleResp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/kegiatan/{$kegiatanId}/presensi/single", [
                'pemuda_id'        => $pemuda->id,
                'status_kehadiran' => 'hadir',
                'waktu_presensi'   => now()->toIso8601String(),
                'device_info'      => 'Xiaomi Redmi Note 12',
            ]);

        $singleResp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'presensi' => [
                        'status_kehadiran' => 'hadir',
                    ],
                ],
            ]);

        // 5. Catat Presensi Bulk (Offline Sync)
        $bulkResp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/kegiatan/{$kegiatanId}/presensi/bulk", [
                'device_info'   => 'Xiaomi Redmi Note 12',
                'presensi_list' => [
                    [
                        'pemuda_id'        => $pemuda->id,
                        'status_kehadiran' => 'izin',
                        'keterangan'       => 'Lembur kerja shift 2',
                        'waktu_presensi'   => now()->toIso8601String(),
                    ],
                ],
            ]);

        $bulkResp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'synced_count' => 1,
                ],
            ]);

        // 6. Ambil Rekap & Teks WhatsApp
        $rekapResp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/kegiatan/{$kegiatanId}/rekap");

        $rekapResp->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'kegiatan_id',
                    'summary',
                    'whatsapp_text',
                ],
            ]);

        $this->assertStringContainsString('*LAPORAN PRESENSI PEMUDA CABANG', $rekapResp->json('data.whatsapp_text'));
        $this->assertStringContainsString('*DAFTAR IZIN:*', $rekapResp->json('data.whatsapp_text'));

        // 7. Kunci / Ubah Status Selesai
        $statusResp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/v1/kegiatan/{$kegiatanId}/status", [
                'status' => 'selesai',
            ]);

        $statusResp->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'selesai',
                ],
            ]);
    }

    public function test_api_maintenance_mode_blocks_requests_with_503(): void
    {
        $token = $this->adminCabang->createToken('test_token')->plainTextToken;

        // Nonaktifkan API
        ApiSetting::set('api_presensi_enabled', '0');
        ApiSetting::set('api_maintenance_message', 'Server sedang upgrade database.');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/cabang/pemuda');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'Server sedang upgrade database.',
            ]);

        // Reset kembali
        ApiSetting::set('api_presensi_enabled', '1');
    }

    public function test_api_logout_revokes_token(): void
    {
        $token = $this->adminCabang->createToken('test_token')->plainTextToken;

        $logoutResp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $logoutResp->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Request berikutnya harus 401 Unauthorized karena token sudah dihapus di DB
        auth()->forgetGuards();

        $meResp = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $meResp->assertStatus(401);
    }

    public function test_superadmin_can_access_api_settings_web_page(): void
    {
        $this->actingAs($this->superadmin);

        $response = $this->get('/admin/api-settings');
        $response->assertStatus(200)
            ->assertSee('Pengaturan API Mobile Presensi')
            ->assertSee('Status API Mobile')
            ->assertSee('Versi Minimum Aplikasi');
    }

    public function test_superadmin_can_update_api_settings(): void
    {
        $this->actingAs($this->superadmin);

        $response = $this->post('/admin/api-settings/update', [
            'api_presensi_enabled'      => '1',
            'api_maintenance_message'   => 'Perbaikan server sedang berlangsung.',
            'api_min_app_version'       => '1.1.0',
            'api_latest_app_version'    => '1.2.0',
            'api_apk_download_url'      => 'https://pmd.mtasragen.or.id/download/presensi.apk',
            'api_broadcast_message'     => 'Pengumuman terbaru.',
            'api_allow_offline_sync'    => '1',
            'api_max_bulk_sync'         => '250',
            'api_token_expiration_days' => '60',
            'quick_chips'               => "Lembur Kerja\nSakit Demam\nLuar Kota",
        ]);

        $response->assertRedirect('/admin/api-settings');
        $this->assertEquals('1.1.0', ApiSetting::get('api_min_app_version'));
        $this->assertEquals('1.2.0', ApiSetting::get('api_latest_app_version'));
        $this->assertEquals('250', ApiSetting::get('api_max_bulk_sync'));
    }
}
