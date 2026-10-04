<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\GdmPenugasan;
use App\Models\GuruDaerahMuda;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\GdmCabangSyncService;
use Tests\TestCase;

class GdmCabangSyncTest extends TestCase
{
    protected function getSuperadmin(): User
    {
        return User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
    }

    public function test_artisan_command_syncs_gdm_penugasan_and_cabang(): void
    {
        $this->artisan('gdm:sync-cabang')
            ->assertExitCode(0)
            ->expectsOutputToContain('Sinkronisasi Penugasan GDM dengan Master Cabang');
    }

    public function test_cabang_sync_endpoint_triggers_sync_service(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->post(route('admin.cabang.sync-gdm'));
        $response->assertRedirect(route('admin.cabang.index'));
        $response->assertSessionHas('success');
    }

    public function test_gdm_sync_endpoint_triggers_sync_service(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->post(route('admin.gdm.sync-cabang'));
        $response->assertRedirect(route('admin.gdm.index'));
        $response->assertSessionHas('success');
    }

    public function test_adding_penugasan_to_gdm_automatically_syncs_to_cabang(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $cabang = Cabang::create([
            'wilayah_id'     => $wilayah->id,
            'name'           => 'Cabang Auto Sync Test ' . rand(1000, 9999),
            'has_gelombang'  => 'belum',
            'gelombang_hari' => 'Sabtu Pagi',
            'gelombang_jam'  => '06:00 - 07:30 WIB',
        ]);

        $gdm = GuruDaerahMuda::create([
            'nama'        => 'Ust. Kader Test ' . rand(100, 999),
            'cabang_id'   => $cabang->id,
            'status'      => 'aktif',
            'sumber_data' => 'manual',
        ]);

        // Tambah penugasan GDM
        $response = $this->post(route('admin.gdm.penugasan.simpan', $gdm->id), [
            'tahun'       => 2026,
            'cabang_id'   => $cabang->id,
            'hari_kajian' => 'Ahad Pagi',
            'jam_kajian'  => '06:00 WIB',
            'status'      => 'aktif',
            'keterangan'  => 'Penugasan sync otomatis',
        ]);

        $response->assertStatus(302);

        $cabang->refresh();
        $this->assertEquals('sudah', $cabang->has_gelombang);
        $this->assertEquals($gdm->nama, $cabang->gelombang_ustadz);
        $this->assertEquals('Ahad Pagi', $cabang->gelombang_hari);
        $this->assertEquals('06:00 WIB', $cabang->gelombang_jam);

        // Cleanup
        $gdm->delete();
        $cabang->delete();
    }

    public function test_updating_penugasan_syncs_to_new_and_old_cabang(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $cabang1 = Cabang::create([
            'wilayah_id'     => $wilayah->id,
            'name'           => 'Cabang 1 Test ' . rand(1000, 9999),
            'has_gelombang'  => 'sudah',
            'gelombang_hari' => 'Ahad',
        ]);

        $cabang2 = Cabang::create([
            'wilayah_id'     => $wilayah->id,
            'name'           => 'Cabang 2 Test ' . rand(1000, 9999),
            'has_gelombang'  => 'belum',
        ]);

        $gdm = GuruDaerahMuda::create([
            'nama'        => 'Ust. Pindah Tugas ' . rand(100, 999),
            'cabang_id'   => $cabang1->id,
            'status'      => 'aktif',
            'sumber_data' => 'manual',
        ]);

        $penugasan = GdmPenugasan::create([
            'gdm_id'      => $gdm->id,
            'tahun'       => 2026,
            'cabang_id'   => $cabang1->id,
            'hari_kajian' => 'Ahad Pagi',
            'jam_kajian'  => '06:00 WIB',
            'status'      => 'aktif',
        ]);

        app(GdmCabangSyncService::class)->syncPenugasanToCabang($penugasan);

        $cabang1->refresh();
        $this->assertEquals($gdm->nama, $cabang1->gelombang_ustadz);

        // Update penugasan pindah ke cabang2
        $this->post(route('admin.gdm.penugasan.update', $penugasan->id), [
            'tahun'       => 2026,
            'cabang_id'   => $cabang2->id,
            'hari_kajian' => 'Senin Sore',
            'jam_kajian'  => '16:00 WIB',
            'status'      => 'aktif',
        ]);

        $cabang1->refresh();
        $cabang2->refresh();

        // Cabang 1 ustadz should be refreshed (null because GDM moved away)
        $this->assertNull($cabang1->gelombang_ustadz);

        // Cabang 2 ustadz should be updated with this GDM
        $this->assertEquals($gdm->nama, $cabang2->gelombang_ustadz);
        $this->assertEquals('sudah', $cabang2->has_gelombang);
        $this->assertEquals('Senin Sore', $cabang2->gelombang_hari);

        // Cleanup
        $gdm->delete();
        $cabang1->delete();
        $cabang2->delete();
    }

    public function test_deleting_penugasan_refreshes_cabang_ustadz(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $cabang = Cabang::create([
            'wilayah_id'       => $wilayah->id,
            'name'             => 'Cabang Delete Sync Test ' . rand(1000, 9999),
            'has_gelombang'    => 'sudah',
            'gelombang_ustadz' => 'Ust. Mau Dihapus',
        ]);

        $gdm = GuruDaerahMuda::create([
            'nama'        => 'Ust. Mau Dihapus',
            'cabang_id'   => $cabang->id,
            'status'      => 'aktif',
            'sumber_data' => 'manual',
        ]);

        $penugasan = GdmPenugasan::create([
            'gdm_id'    => $gdm->id,
            'tahun'     => 2026,
            'cabang_id' => $cabang->id,
            'status'    => 'aktif',
        ]);

        // Delete penugasan
        $this->post(route('admin.gdm.penugasan.delete', $penugasan->id));

        $cabang->refresh();
        $this->assertNull($cabang->gelombang_ustadz);

        // Cleanup
        $gdm->delete();
        $cabang->delete();
    }

    public function test_cabang_detail_endpoint_returns_gdm_bertugas_array(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $cabang = Cabang::create([
            'wilayah_id' => $wilayah->id,
            'name'       => 'Cabang Detail API Test ' . rand(1000, 9999),
        ]);

        $gdm = GuruDaerahMuda::create([
            'nama'        => 'Ust. Penugasan Detail ' . rand(100, 999),
            'cabang_id'   => $cabang->id,
            'no_wa'       => '081234567890',
            'status'      => 'aktif',
            'sumber_data' => 'manual',
        ]);

        $penugasan = GdmPenugasan::create([
            'gdm_id'      => $gdm->id,
            'tahun'       => 2026,
            'cabang_id'   => $cabang->id,
            'hari_kajian' => 'Ahad Pagi',
            'jam_kajian'  => '06:00 WIB',
            'status'      => 'aktif',
        ]);

        $response = $this->get(route('admin.cabang.detail', $cabang->id));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data'   => [
                'id'           => $cabang->id,
                'gdm_bertugas' => [
                    [
                        'id'          => $penugasan->id,
                        'gdm_id'      => $gdm->id,
                        'nama'        => $gdm->nama,
                        'no_wa'       => '081234567890',
                        'tahun'       => 2026,
                        'hari_kajian' => 'Ahad Pagi',
                        'jam_kajian'  => '06:00 WIB',
                        'status'      => 'aktif',
                    ],
                ],
            ],
        ]);

        // Cleanup
        $gdm->delete();
        $cabang->delete();
    }

    public function test_saving_cabang_with_gdm_id_syncs_penugasan(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $gdm = GuruDaerahMuda::create([
            'nama'        => 'Ust. Pilihan Cabang ' . rand(100, 999),
            'status'      => 'aktif',
            'sumber_data' => 'manual',
        ]);

        $uniqueName = 'Cabang Form GDM Test ' . rand(1000, 9999);

        $response = $this->post(route('admin.cabang.simpan'), [
            'wilayah_id'     => $wilayah->id,
            'name'           => $uniqueName,
            'has_gelombang'  => 'sudah',
            'gelombang_hari' => 'Jumat Sore',
            'gelombang_jam'  => '16:00 WIB',
            'gdm_id'         => $gdm->id,
        ]);

        $response->assertRedirect(route('admin.cabang.index'));

        $createdCabang = Cabang::where('name', $uniqueName)->first();
        $this->assertNotNull($createdCabang);
        $this->assertEquals($gdm->nama, $createdCabang->gelombang_ustadz);

        // Assert penugasan was created
        $penugasan = GdmPenugasan::where('gdm_id', $gdm->id)
            ->where('cabang_id', $createdCabang->id)
            ->first();

        $this->assertNotNull($penugasan);
        $this->assertEquals('aktif', $penugasan->status);
        $this->assertEquals('Jumat Sore', $penugasan->hari_kajian);

        // Cleanup
        $gdm->delete();
        $createdCabang->delete();
    }

    public function test_cabang_and_gdm_index_pages_show_sync_buttons(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        // Cabang index
        $resCabang = $this->get(route('admin.cabang.index'));
        $resCabang->assertStatus(200);
        $resCabang->assertSee('Sinkronkan GDM');
        $resCabang->assertSee(route('admin.cabang.sync-gdm'));

        // GDM index
        $resGdm = $this->get(route('admin.gdm.index'));
        $resGdm->assertStatus(200);
        $resGdm->assertSee('Sinkronkan Master Cabang');
        $resGdm->assertSee(route('admin.gdm.sync-cabang'));
        $resGdm->assertSee('Sinkronkan Alamat');
        $resGdm->assertSee(route('admin.gdm.sync-alamat'));
    }

    public function test_gdm_address_syncs_from_pemuda_source(): void
    {
        $pemuda = \App\Models\Pemuda::with('alamat')->first();
        if (!$pemuda) {
            $this->markTestSkipped('Tidak ada data pemuda untuk pengujian');
        }

        $gdm = GuruDaerahMuda::create([
            'nama'        => 'Ust. Sync Alamat Tester ' . rand(100, 999),
            'pemuda_id'   => $pemuda->id,
            'status'      => 'aktif',
            'sumber_data' => 'pemuda',
            'alamat'      => null,
        ]);

        $this->assertNull($gdm->alamat);

        $changed = $gdm->syncAlamatFromSource();
        $this->assertTrue($changed);

        $gdm->refresh();
        $this->assertNotNull($gdm->alamat);
        $this->assertEquals($pemuda->alamat?->alamat_lengkap, $gdm->alamat);

        // Cleanup
        $gdm->delete();
    }

    public function test_gdm_sync_alamat_batch_endpoint(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->post(route('admin.gdm.sync-alamat'));
        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_gdm_sync_alamat_single_endpoint(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $pemuda = \App\Models\Pemuda::with('alamat')->first();
        if (!$pemuda) {
            $this->markTestSkipped('Tidak ada data pemuda untuk pengujian');
        }

        $gdm = GuruDaerahMuda::create([
            'nama'        => 'Ust. Single Sync ' . rand(100, 999),
            'pemuda_id'   => $pemuda->id,
            'status'      => 'aktif',
            'sumber_data' => 'pemuda',
            'alamat'      => null,
        ]);

        $response = $this->postJson(route('admin.gdm.sync-alamat-single', $gdm->id));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data'   => [
                'id'                  => $gdm->id,
                'sumber_alamat_label' => 'Tersinkron Pemuda',
            ],
        ]);

        $gdm->refresh();
        $this->assertEquals($pemuda->alamat?->alamat_lengkap, $gdm->alamat);

        // Cleanup
        $gdm->delete();
    }

    public function test_search_pemuda_returns_formatted_address(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $pemuda = \App\Models\Pemuda::with('alamat')->first();
        if (!$pemuda) {
            $this->markTestSkipped('Tidak ada data pemuda');
        }

        $response = $this->getJson(route('admin.gdm.search-pemuda', ['q' => substr($pemuda->name, 0, 4)]));
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        $matched = collect($data)->firstWhere('id', $pemuda->id);
        $this->assertNotNull($matched);
        $this->assertEquals($pemuda->alamat?->alamat_lengkap ?: '', $matched['alamat']);
    }
}

