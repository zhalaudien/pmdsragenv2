<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\GdmPenugasan;
use App\Models\GuruDaerahMuda;
use App\Models\Pemuda;
use App\Models\User;
use App\Models\Wilayah;
use Tests\TestCase;

class GuruDaerahMudaDashboardTest extends TestCase
{
    protected function getSuperadmin(): User
    {
        return User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
    }

    public function test_superadmin_can_access_gdm_dashboard_and_see_kpi_cards(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get(route('admin.gdm.index'));
        $response->assertStatus(200);

        // Assert Headers & KPI Cards
        $response->assertSee('Manajemen Guru Daerah Muda (GDM)');
        $response->assertSee('Total GDM');
        $response->assertSee('Status Aktif');
        $response->assertSee('Penugasan Aktif');
        $response->assertSee('Cabang Sasaran');
        $response->assertSee('Tambah GDM Baru');

        // Assert Modals
        $response->assertSee('id="modalTambahGdm"', false);
        $response->assertSee('id="modalEditGdm"', false);
        $response->assertSee('id="modalDetailGdm"', false);
        $response->assertSee('id="modalQuickPenugasan"', false);

        // Assert Data Source Tabs
        $response->assertSee('Dari Data Pemuda');
        $response->assertSee('Dari Warga MTA');
        $response->assertSee('Input Manual');
    }

    public function test_superadmin_can_create_gdm_manually_with_initial_penugasan(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabangAsal  = Cabang::first();
        $cabangTugas = Cabang::skip(1)->first() ?? $cabangAsal;

        $uniqueName = 'Ustadz Muda Test ' . rand(1000, 9999);

        $response = $this->post(route('admin.gdm.simpan'), [
            'nama'                 => $uniqueName,
            'tempat_lahir'         => 'Sragen',
            'tanggal_lahir'        => '1998-05-12',
            'cabang_id'            => $cabangAsal->id,
            'alamat'               => 'Dk. Pilangsari RT 01, Ngrampal',
            'no_wa'                => '081234567891',
            'status'               => 'aktif',
            'sumber_data'          => 'manual',
            'catatan'              => 'Kader mubaligh lulusan ponpes',
            // Penugasan awal
            'penugasan_tahun'      => 2026,
            'penugasan_cabang_id'  => $cabangTugas->id,
            'penugasan_hari'       => 'Ahad Pagi',
            'penugasan_jam'        => '06:00 - 07:30 WIB',
            'penugasan_status'     => 'aktif',
            'penugasan_keterangan' => 'Kajian gelombang rutin pemuda',
        ]);

        $response->assertRedirect(route('admin.gdm.index'));
        $response->assertSessionHas('success');

        $created = GuruDaerahMuda::where('nama', $uniqueName)->first();
        $this->assertNotNull($created);
        $this->assertEquals('Sragen', $created->tempat_lahir);
        $this->assertEquals('1998-05-12', $created->tanggal_lahir->format('Y-m-d'));
        $this->assertEquals($cabangAsal->id, $created->cabang_id);
        $this->assertEquals('081234567891', $created->no_wa);
        $this->assertEquals('manual', $created->sumber_data);

        // Assert Penugasan was also created
        $penugasan = GdmPenugasan::where('gdm_id', $created->id)->first();
        $this->assertNotNull($penugasan);
        $this->assertEquals(2026, $penugasan->tahun);
        $this->assertEquals($cabangTugas->id, $penugasan->cabang_id);
        $this->assertEquals('Ahad Pagi', $penugasan->hari_kajian);

        // Cleanup
        $created->delete();
    }

    public function test_superadmin_can_create_gdm_from_pemuda(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabang = Cabang::first();

        // Create Pemuda
        $pemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'REGGDM' . rand(1000, 9999),
            'name'                => 'Kader Pemuda Dai ' . rand(1000, 9999),
            'gender'              => 'L',
            'birth_place'         => 'Surakarta',
            'birth_date'          => '1999-08-20',
            'phone'               => '089988776655',
            'status_data'         => 'active',
            'status_verifikasi'   => 'verified',
        ]);

        $response = $this->post(route('admin.gdm.simpan'), [
            'nama'          => $pemuda->name,
            'tempat_lahir'  => $pemuda->birth_place,
            'tanggal_lahir' => '1999-08-20',
            'cabang_id'     => $cabang->id,
            'alamat'        => 'Solo',
            'no_wa'         => $pemuda->phone,
            'status'        => 'aktif',
            'sumber_data'   => 'pemuda',
            'pemuda_id'     => $pemuda->id,
        ]);

        $response->assertRedirect(route('admin.gdm.index'));

        $gdm = GuruDaerahMuda::where('pemuda_id', $pemuda->id)->first();
        $this->assertNotNull($gdm);
        $this->assertEquals('pemuda', $gdm->sumber_data);
        $this->assertEquals($pemuda->name, $gdm->nama);

        // Cleanup
        $gdm->delete();
        $pemuda->delete();
    }

    public function test_superadmin_can_update_gdm_and_delete_gdm(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabang = Cabang::first();

        $gdm = GuruDaerahMuda::create([
            'nama'          => 'GDM Update Test ' . rand(100, 999),
            'tempat_lahir'  => 'Karanganyar',
            'tanggal_lahir' => '1997-03-15',
            'cabang_id'     => $cabang->id,
            'no_wa'         => '081233334444',
            'status'        => 'aktif',
            'sumber_data'   => 'manual',
        ]);

        // Update
        $responseUpdate = $this->post(route('admin.gdm.update', $gdm->id), [
            'nama'          => $gdm->nama . ' Diperbarui',
            'tempat_lahir'  => 'Sragen Kota',
            'tanggal_lahir' => '1997-03-15',
            'cabang_id'     => $cabang->id,
            'alamat'        => 'Alamat Baru Diperbarui',
            'no_wa'         => '081255556666',
            'status'        => 'nonaktif',
            'sumber_data'   => 'manual',
        ]);

        $responseUpdate->assertRedirect(route('admin.gdm.index'));

        $updated = GuruDaerahMuda::find($gdm->id);
        $this->assertEquals($gdm->nama . ' Diperbarui', $updated->nama);
        $this->assertEquals('Sragen Kota', $updated->tempat_lahir);
        $this->assertEquals('081255556666', $updated->no_wa);
        $this->assertEquals('nonaktif', $updated->status);

        // Delete
        $responseDelete = $this->post(route('admin.gdm.delete', $gdm->id));
        $responseDelete->assertRedirect(route('admin.gdm.index'));

        $this->assertNull(GuruDaerahMuda::find($gdm->id));
    }

    public function test_superadmin_can_add_update_and_delete_riwayat_penugasan_kajian(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabang = Cabang::first();

        $gdm = GuruDaerahMuda::create([
            'nama'        => 'GDM Penugasan Test ' . rand(100, 999),
            'cabang_id'   => $cabang->id,
            'status'      => 'aktif',
            'sumber_data' => 'manual',
        ]);

        // 1. Tambah Penugasan
        $responseTambah = $this->post(route('admin.gdm.penugasan.simpan', $gdm->id), [
            'tahun'       => 2025,
            'cabang_id'   => $cabang->id,
            'hari_kajian' => 'Sabtu Sore',
            'jam_kajian'  => '16:00 - 17:30 WIB',
            'status'      => 'aktif',
            'keterangan'  => 'Kajian tafsir pemuda',
        ]);

        $responseTambah->assertStatus(302);

        $penugasan = GdmPenugasan::where('gdm_id', $gdm->id)->first();
        $this->assertNotNull($penugasan);
        $this->assertEquals(2025, $penugasan->tahun);
        $this->assertEquals('Sabtu Sore', $penugasan->hari_kajian);

        // 2. Update Penugasan
        $responseUpdate = $this->post(route('admin.gdm.penugasan.update', $penugasan->id), [
            'tahun'       => 2026,
            'cabang_id'   => $cabang->id,
            'hari_kajian' => 'Ahad Pagi',
            'jam_kajian'  => '06:00 - 07:30 WIB',
            'status'      => 'selesai',
            'keterangan'  => 'Tugas telah tuntas',
        ]);

        $responseUpdate->assertStatus(302);
        $penugasan->refresh();
        $this->assertEquals(2026, $penugasan->tahun);
        $this->assertEquals('selesai', $penugasan->status);

        // 3. Delete Penugasan
        $responseDelete = $this->post(route('admin.gdm.penugasan.delete', $penugasan->id));
        $responseDelete->assertStatus(302);
        $this->assertNull(GdmPenugasan::find($penugasan->id));

        // Cleanup
        $gdm->delete();
    }

    public function test_search_pemuda_endpoint_returns_matching_pemuda(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabang = Cabang::first();

        $uniqueQ = 'UniqueSearchKader' . rand(100, 999);
        $pemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'REG' . rand(10000, 99999),
            'name'                => $uniqueQ . ' Fauzan',
            'gender'              => 'L',
            'phone'               => '081299998888',
            'status_data'         => 'active',
            'status_verifikasi'   => 'verified',
        ]);

        $response = $this->get(route('admin.gdm.search-pemuda', ['q' => $uniqueQ]));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals($pemuda->name, $data[0]['nama']);

        // Cleanup
        $pemuda->delete();
    }

    public function test_search_unified_endpoint_for_gdm(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabang = Cabang::first();

        $uniqueQ = 'UnifiedGdm' . rand(100, 999);
        $pemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'REG-GDM' . rand(10000, 99999),
            'name'                => $uniqueQ . ' Hidayat',
            'gender'              => 'L',
            'phone'               => '081299997777',
            'status_data'         => 'active',
            'status_verifikasi'   => 'verified',
        ]);

        $response = $this->get(route('admin.gdm.search-unified', ['q' => $uniqueQ]));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals($pemuda->name, $data[0]['nama']);
        $this->assertEquals('pemuda', $data[0]['sumber_data']);

        // Cleanup
        $pemuda->delete();
    }

    public function test_gdm_detail_endpoint_returns_json_with_riwayat_penugasan(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabang = Cabang::first();

        $gdm = GuruDaerahMuda::create([
            'nama'          => 'GDM Detail Test ' . rand(100, 999),
            'tempat_lahir'  => 'Sragen',
            'tanggal_lahir' => '1995-10-01',
            'cabang_id'     => $cabang->id,
            'no_wa'         => '081234567800',
            'status'        => 'aktif',
            'sumber_data'   => 'manual',
        ]);

        $penugasan = GdmPenugasan::create([
            'gdm_id'      => $gdm->id,
            'tahun'       => 2026,
            'cabang_id'   => $cabang->id,
            'hari_kajian' => 'Ahad Pagi',
            'status'      => 'aktif',
        ]);

        $response = $this->get(route('admin.gdm.detail', $gdm->id));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data'   => [
                'id'          => $gdm->id,
                'nama'        => $gdm->nama,
                'cabang_id'   => $cabang->id,
                'cabang_name' => $cabang->name,
                'penugasan'   => [
                    [
                        'id'          => $penugasan->id,
                        'tahun'       => 2026,
                        'cabang_id'   => $cabang->id,
                        'cabang_name' => $cabang->name,
                    ],
                ],
            ],
        ]);

        // Cleanup
        $gdm->delete();
    }

    public function test_unauthenticated_user_cannot_access_gdm_dashboard(): void
    {
        $response = $this->get(route('admin.gdm.index'));
        $response->assertRedirect(route('login'));
    }
}
