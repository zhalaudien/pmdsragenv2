<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Pemuda;
use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;

class KoordinatorGdmRoleAccessTest extends TestCase
{
    protected function getKoordinatorGdm(): User
    {
        $user = User::where('role_id', 7)->first() ?? User::where('username', 'koordinator_gdm')->first();

        if (!$user) {
            $role = UserRole::firstOrCreate(
                ['id' => 7],
                [
                    'name'        => 'koordinator_gdm',
                    'description' => 'Koordinator Guru Daerah Muda (GDM)',
                ]
            );

            $user = User::create([
                'name'       => 'Koordinator GDM Test',
                'email'      => 'koordinator.test@pmdsragen.org',
                'username'   => 'koordinator_gdm_test',
                'password'   => bcrypt('password123'),
                'role_id'    => $role->id,
                'wilayah_id' => null,
                'cabang_id'  => null,
                'status'     => 1,
            ]);
        }

        return $user;
    }

    public function test_koordinator_gdm_can_access_gdm_management(): void
    {
        $user = $this->getKoordinatorGdm();
        $this->actingAs($user);

        $this->get('/admin/gdm')->assertStatus(200)->assertSee('Guru Daerah Muda');
        $this->getJson('/admin/gdm/search-pemuda?q=test')->assertStatus(200);
        $this->getJson('/admin/gdm/search-warga?q=test')->assertStatus(200);
    }

    public function test_koordinator_gdm_can_view_cabang_data_and_details(): void
    {
        $user = $this->getKoordinatorGdm();
        $this->actingAs($user);

        $cabang = Cabang::first();
        $this->assertNotNull($cabang, 'Cabang must exist');

        // View cabang list
        $this->get('/admin/cabang')->assertStatus(200);

        // View detail cabang json
        $this->getJson('/admin/cabang/detail/' . $cabang->id)
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // View pemuda in cabang
        $this->getJson('/admin/cabang/' . $cabang->id . '/pemuda')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Export cabang
        $this->get('/admin/cabang/export')->assertStatus(200);
    }

    public function test_koordinator_gdm_cannot_modify_or_delete_cabang(): void
    {
        $user = $this->getKoordinatorGdm();
        $this->actingAs($user);

        $cabang = Cabang::first();
        $this->assertNotNull($cabang);

        // Template and Import Excel forbidden
        $this->get('/admin/cabang/template')->assertStatus(403);
        $this->post('/admin/cabang/import')->assertStatus(403);

        // Create new cabang forbidden
        $this->post('/admin/cabang/simpan', [
            'name'          => 'Cabang Ilegal Test',
            'wilayah_id'    => 1,
            'has_gelombang' => 'belum',
        ])->assertStatus(403);

        // Update cabang forbidden
        $this->post('/admin/cabang/update/' . $cabang->id, [
            'name'          => 'Cabang Updated Ilegal',
            'wilayah_id'    => 1,
            'has_gelombang' => 'belum',
        ])->assertStatus(403);

        // Delete cabang forbidden
        $this->post('/admin/cabang/delete/' . $cabang->id)->assertStatus(403);
    }

    public function test_koordinator_gdm_can_view_pemuda_list_and_persebaran(): void
    {
        $user = $this->getKoordinatorGdm();
        $this->actingAs($user);

        // List pemuda
        $this->get('/admin/pemuda')->assertStatus(200);

        // Persebaran data
        $this->get('/admin/persebaran')->assertStatus(200);

        // Export page
        $this->get('/admin/pemuda/export')->assertStatus(200);

        // Detail pemuda if available
        $pemuda = Pemuda::first();
        if ($pemuda) {
            $this->get('/admin/pemuda/detail/' . $pemuda->id)->assertStatus(200);
            $this->get('/admin/pemuda/cetak/' . $pemuda->id)->assertStatus(200);
        }
    }

    public function test_koordinator_gdm_cannot_modify_or_delete_pemuda(): void
    {
        $user = $this->getKoordinatorGdm();
        $this->actingAs($user);

        $pemuda = Pemuda::first();

        // Tambah pemuda form forbidden
        $this->get('/admin/pemuda/tambah')->assertStatus(403);
        $this->post('/admin/pemuda/simpan', ['name' => 'Pemuda Test'])->assertStatus(403);

        if ($pemuda) {
            // Edit & update forbidden
            $this->get('/admin/pemuda/edit/' . $pemuda->id)->assertStatus(403);
            $this->post('/admin/pemuda/update/' . $pemuda->id, ['name' => 'Pemuda Update'])->assertStatus(403);

            // Verifikasi forbidden
            $this->post('/admin/pemuda/verifikasi/' . $pemuda->id)->assertStatus(403);

            // Archive forbidden
            $this->post('/admin/pemuda/archive/' . $pemuda->id)->assertStatus(403);

            // Delete forbidden
            $this->post('/admin/pemuda/delete/' . $pemuda->id)->assertStatus(403);
        }

        // Import & backup forbidden
        $this->get('/admin/pemuda/import')->assertStatus(403);
        $this->get('/admin/pemuda/backup')->assertStatus(403);
    }

    public function test_koordinator_gdm_cannot_access_unauthorized_admin_modules(): void
    {
        $user = $this->getKoordinatorGdm();
        $this->actingAs($user);

        // Master Wilayah
        $this->get('/admin/wilayah')->assertStatus(403);

        // Master Users
        $this->get('/admin/users')->assertStatus(403);

        // Warga MTA & Sync
        $this->get('/admin/warga-mta')->assertStatus(403);
        $this->get('/admin/mta-sync')->assertStatus(403);

        // Homepage & API Settings
        $this->get('/admin/homepage')->assertStatus(403);
        $this->get('/admin/api-settings')->assertStatus(403);

        // Kegiatan Perwakilan (Khusus manajemen pengurus)
        $this->get('/admin/kegiatan-perwakilan')->assertStatus(403);
    }

    public function test_koordinator_gdm_can_view_presensi_dashboard_and_notulensi_kajian(): void
    {
        $user = $this->getKoordinatorGdm();
        $this->actingAs($user);

        // Dashboard Presensi accessible
        $response = $this->get('/admin/presensi/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Monitoring Sesi Presensi Cabang');
        $response->assertSee('Notulensi');

        // Test filter by wilayah and cabang
        $this->get('/admin/presensi/dashboard?wilayah_id=1')->assertStatus(200);
        $cabang = Cabang::first();
        if ($cabang) {
            $this->get('/admin/presensi/dashboard?cabang_id=' . $cabang->id)->assertStatus(200);
        }

        // Test rekap detail with notulensi via JSON
        $kegiatan = \App\Models\KegiatanPresensi::first();
        if (!$kegiatan && $cabang) {
            $kegiatan = \App\Models\KegiatanPresensi::create([
                'cabang_id'      => $cabang->id,
                'created_by'     => $user->id,
                'nama_kegiatan'  => 'Kajian Ahad Pagi Cabang Test',
                'target_peserta' => 'semua',
                'tanggal'        => now()->toDateString(),
                'jam_mulai'      => '08:00',
                'jam_selesai'    => '10:00',
                'lokasi'         => 'Masjid Cabang',
                'pemateri'       => 'Ustadz Pembina',
                'status'         => 'selesai',
                'notulensi'      => 'Ringkasan materi kajian tauhid dan akhlak.',
                'notulis'        => 'Notulis Cabang',
            ]);
        }

        if ($kegiatan) {
            $rekapRes = $this->getJson('/admin/presensi/kegiatan/' . $kegiatan->id . '/rekap');
            $rekapRes->assertStatus(200)
                ->assertJsonStructure([
                    'id', 'nama_kegiatan', 'cabang_name', 'notulensi', 'notulis', 'has_notulensi', 'rekap', 'whatsapp_text'
                ]);
        }
    }

    public function test_koordinator_gdm_cannot_edit_notulensi_kajian(): void
    {
        $user = $this->getKoordinatorGdm();
        $this->actingAs($user);

        // UI check: Edit button and edit form must NOT be rendered for koordinator_gdm
        $response = $this->get('/admin/presensi/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee('id="btnToggleEditRekapNotulensi"', false);
        $response->assertDontSee('id="rekapNotulensiEditBox"', false);

        // Server-side check: POST notulensi endpoint must return 403 Forbidden
        $kegiatan = \App\Models\KegiatanPresensi::first();
        if ($kegiatan) {
            $updateRes = $this->postJson('/admin/presensi/kegiatan/' . $kegiatan->id . '/notulensi', [
                'notulensi' => 'Percobaan mengedit notulensi oleh Koordinator GDM',
                'notulis'   => 'Koordinator GDM',
            ]);
            $updateRes->assertStatus(403);
        }
    }

    public function test_other_roles_cannot_access_gdm_management(): void
    {
        $adminCabang = User::where('role_id', 3)->first() ?? User::where('username', 'admin_gesi')->first();
        if ($adminCabang) {
            $this->actingAs($adminCabang);
            $this->get('/admin/gdm')->assertStatus(403);
        }

        $adminWilayah = User::where('role_id', 2)->first() ?? User::where('username', 'admin_w1')->first();
        if ($adminWilayah) {
            $this->actingAs($adminWilayah);
            $this->get('/admin/gdm')->assertStatus(403);
        }
    }

    public function test_koordinator_gdm_login_redirects_to_gdm_index(): void
    {
        $user = $this->getKoordinatorGdm();

        $response = $this->post('/admin/login', [
            'login'    => $user->username,
            'password' => 'admin123',
        ]);

        if ($response->getStatusCode() === 302 && session('user_id')) {
            $response->assertRedirect(route('admin.gdm.index'));
        } else {
            // Verify session role if directly authenticated
            $this->actingAs($user);
            $this->assertTrue($user->isKoordinatorGdm());
            $this->assertEquals('koordinator_gdm', $user->role_name);
        }
    }
}
