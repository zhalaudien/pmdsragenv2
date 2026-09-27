<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\KegiatanPresensi;
use App\Models\PresensiDetail;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiDashboardSeparationTest extends TestCase
{
    protected function getSuperadmin(): User
    {
        return User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
    }

    public function test_unauthenticated_user_cannot_access_presensi_dashboard(): void
    {
        $response = $this->get('/admin/presensi/dashboard');
        $response->assertRedirect('/admin/login');
    }

    public function test_alias_presensi_dashboard_redirects_correctly(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get('/admin/presensi-dashboard');
        $response->assertRedirect(route('admin.presensi.dashboard'));
    }

    public function test_superadmin_can_access_presensi_dashboard_and_see_api_metrics(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get('/admin/presensi/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Management API &amp; Mobile Presensi PMD', false);
        $response->assertSee('Status Server API');
        $response->assertSee('Sesi Kegiatan Presensi');
        $response->assertSee('Total Rekap Kehadiran');
        $response->assertSee('Perangkat Mobile Aktif');
        $response->assertSee('Seting API Presensi');
        $response->assertSee('Dashboard Pemuda');
    }

    public function test_superadmin_can_access_pemuda_dashboard_with_dedicated_branding(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Sistem Pendataan Pemuda');
        $response->assertSee('Total Pemuda');
        $response->assertSee('Terverifikasi (Pusat)');
        $response->assertSee('Dashboard Presensi');
    }

    public function test_presensi_kegiatan_rekap_endpoint_returns_json(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $kegiatan = KegiatanPresensi::first();
        if (!$kegiatan) {
            $cabang = Cabang::first();
            $kegiatan = KegiatanPresensi::create([
                'cabang_id'      => $cabang->id,
                'nama_kegiatan'  => 'Kajian Rutin Test Rekap',
                'tanggal'        => now()->toDateString(),
                'status'         => 'selesai',
                'target_peserta' => 'semua',
                'created_by'     => $superadmin->id,
            ]);
        }

        $response = $this->getJson("/admin/presensi/kegiatan/{$kegiatan->id}/rekap");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'nama_kegiatan',
            'cabang_name',
            'wilayah_name',
            'tanggal',
            'hari_tanggal',
            'status',
            'rekap' => [
                'total_pemuda',
                'hadir',
                'izin',
                'sakit',
                'alpa',
                'persentase_hadir',
            ],
            'whatsapp_text',
        ]);
    }

    public function test_admin_cabang_cannot_access_other_cabang_rekap(): void
    {
        $adminCabang = User::where('role_id', 3)->whereNotNull('cabang_id')->first();
        if (!$adminCabang) {
            $this->markTestSkipped('No admin cabang user available in database.');
        }

        // Find or create kegiatan in a different cabang
        $otherCabang = Cabang::where('id', '!=', $adminCabang->cabang_id)->first();
        if (!$otherCabang) {
            $this->markTestSkipped('No other cabang available in database.');
        }

        $otherKegiatan = KegiatanPresensi::where('cabang_id', $otherCabang->id)->first();
        if (!$otherKegiatan) {
            $otherKegiatan = KegiatanPresensi::create([
                'cabang_id'      => $otherCabang->id,
                'nama_kegiatan'  => 'Kegiatan Cabang Lain',
                'tanggal'        => now()->toDateString(),
                'status'         => 'selesai',
                'target_peserta' => 'semua',
                'created_by'     => $adminCabang->id,
            ]);
        }

        $this->actingAs($adminCabang);
        // Set session cabang_id if needed
        session(['role' => 'admin_cabang', 'cabang_id' => $adminCabang->cabang_id]);

        $response = $this->getJson("/admin/presensi/kegiatan/{$otherKegiatan->id}/rekap");
        $response->assertStatus(403);
    }

    public function test_prelaunch_reset_fails_with_invalid_confirmation(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->post(route('admin.api-settings.prelaunch-reset'), [
            'confirm_text'   => 'SALAH-KETIK',
            'reset_presensi' => 1,
        ]);

        $response->assertRedirect(route('admin.api-settings.index'));
        $response->assertSessionHas('error');
    }

    public function test_prelaunch_reset_fails_without_selected_options(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->post(route('admin.api-settings.prelaunch-reset'), [
            'confirm_text' => 'RESET-LAUNCHING',
        ]);

        $response->assertRedirect(route('admin.api-settings.index'));
        $response->assertSessionHas('warning');
    }

    public function test_prelaunch_reset_clears_presensi_and_tokens_while_preserving_master_pemuda(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabang = Cabang::first();

        // Ensure dummy kegiatan & presensi exists
        $kegiatan = KegiatanPresensi::create([
            'cabang_id'      => $cabang->id,
            'nama_kegiatan'  => 'Kegiatan Test Launching',
            'tanggal'        => now()->toDateString(),
            'status'         => 'selesai',
            'target_peserta' => 'semua',
            'created_by'     => $superadmin->id,
        ]);

        // Get initial pemuda count
        $initialPemudaCount = \App\Models\Pemuda::count();

        // Perform Pre-Launch Reset
        $response = $this->post(route('admin.api-settings.prelaunch-reset'), [
            'confirm_text'   => 'RESET-LAUNCHING',
            'reset_presensi' => 1,
            'reset_tokens'   => 1,
            'reset_settings' => 1,
        ]);

        $response->assertRedirect(route('admin.api-settings.index'));
        $response->assertSessionHas('success');

        // Assert presensi and kegiatan are cleared
        $this->assertEquals(0, KegiatanPresensi::count());
        $this->assertEquals(0, PresensiDetail::count());
        $this->assertEquals(0, \Illuminate\Support\Facades\DB::table('personal_access_tokens')->count());

        // Master Pemuda MUST remain intact!
        $this->assertEquals($initialPemudaCount, \App\Models\Pemuda::count());
    }
}
