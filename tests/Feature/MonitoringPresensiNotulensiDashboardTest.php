<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\KegiatanPresensi;
use App\Models\User;
use Tests\TestCase;

class MonitoringPresensiNotulensiDashboardTest extends TestCase
{
    protected function getSuperadmin(): User
    {
        return User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
    }

    protected function getAdminCabang(): ?User
    {
        return User::where('role_id', 3)->whereNotNull('cabang_id')->first();
    }

    public function test_superadmin_dashboard_pemuda_does_not_display_monitoring_presensi_section(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);

        // Youth dashboard should not have monitoring presensi & notulensi widgets/modals
        $response->assertDontSee('Monitoring Presensi &amp; Notulensi Kajian Cabang', false);
        $response->assertDontSee('modalKajianNotulensi');
        $response->assertDontSee('Agenda Kajian &amp; Presensi Cabang Terbaru', false);

        // But quick navigation button to Dashboard Presensi remains
        $response->assertSee('Dashboard Presensi');
    }

    public function test_presensi_dashboard_displays_notulensi_and_kegiatan_features(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get('/admin/presensi/dashboard');
        $response->assertStatus(200);

        $response->assertSee('Notulensi');
        $response->assertSee('modalRekap');
    }

    public function test_superadmin_can_update_notulensi_via_ajax_endpoint(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabang = Cabang::first();
        $this->assertNotNull($cabang, 'Cabang harus ada di database');

        $kegiatan = KegiatanPresensi::create([
            'cabang_id'      => $cabang->id,
            'nama_kegiatan'  => 'Kajian Ahad Pagi Uji Coba',
            'tanggal'        => now()->toDateString(),
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '09:30',
            'lokasi'         => 'Masjid Cabang Test',
            'pemateri'       => 'Ust. Fulan',
            'target_peserta' => 'semua',
            'status'         => 'selesai',
            'created_by'     => $superadmin->id,
        ]);

        $notulensiText = "1. Poin materi kajian tauhid dan keikhlasan beramal.\n2. Ajakan menghadiri pengajian akbar perwakilan.\n3. Rencana musyawarah cabang pekan depan.";
        $notulisName = 'Budi Santoso';

        $response = $this->postJson(route('admin.presensi.kegiatan.notulensi', $kegiatan->id), [
            'notulensi' => $notulensiText,
            'notulis'   => $notulisName,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'   => true,
            'notulensi' => $notulensiText,
            'notulis'   => $notulisName,
        ]);

        $this->assertDatabaseHas('kegiatan_presensi', [
            'id'        => $kegiatan->id,
            'notulensi' => $notulensiText,
            'notulis'   => $notulisName,
        ]);

        // Cleanup
        $kegiatan->delete();
    }

    public function test_admin_cabang_cannot_update_notulensi_of_other_cabang(): void
    {
        $adminCabang = $this->getAdminCabang();
        if (!$adminCabang) {
            $this->markTestSkipped('Admin cabang tidak ditemukan di database.');
        }

        $otherCabang = Cabang::where('id', '!=', $adminCabang->cabang_id)->first();
        if (!$otherCabang) {
            $this->markTestSkipped('Cabang lain tidak ditemukan.');
        }

        $superadmin = $this->getSuperadmin();
        $kegiatanLain = KegiatanPresensi::create([
            'cabang_id'      => $otherCabang->id,
            'nama_kegiatan'  => 'Kajian Cabang Lain',
            'tanggal'        => now()->toDateString(),
            'created_by'     => $superadmin->id,
        ]);

        $this->actingAs($adminCabang);

        $response = $this->postJson(route('admin.presensi.kegiatan.notulensi', $kegiatanLain->id), [
            'notulensi' => 'Mencoba meretas notulensi cabang lain',
            'notulis'   => 'Hacker',
        ]);

        $response->assertStatus(403);

        // Cleanup
        $kegiatanLain->delete();
    }

    public function test_whatsapp_text_generator_includes_notulensi_when_present(): void
    {
        $superadmin = $this->getSuperadmin();
        $cabang = Cabang::first();

        $kegiatan = KegiatanPresensi::create([
            'cabang_id'      => $cabang->id,
            'nama_kegiatan'  => 'Kajian Pemuda Rutin',
            'tanggal'        => now()->toDateString(),
            'jam_mulai'      => '19:30',
            'pemateri'       => 'Ust. Ahmad',
            'notulensi'      => 'Membahas bab adab penuntut ilmu.',
            'notulis'        => 'Zaid',
            'created_by'     => $superadmin->id,
        ]);

        $waText = $kegiatan->generateWhatsAppText();

        $this->assertStringContainsString('*NOTULENSI / RANGKUMAN KAJIAN:*', $waText);
        $this->assertStringContainsString('Notulis: Zaid', $waText);
        $this->assertStringContainsString('Membahas bab adab penuntut ilmu.', $waText);

        // Cleanup
        $kegiatan->delete();
    }
}
