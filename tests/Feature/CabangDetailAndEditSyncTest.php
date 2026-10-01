<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Pemuda;
use App\Models\User;
use App\Models\Wilayah;
use Tests\TestCase;

class CabangDetailAndEditSyncTest extends TestCase
{
    protected function getSuperadmin(): User
    {
        return User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
    }

    public function test_cabang_index_displays_kajian_pemuda_terminology_and_all_modal_fields(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get(route('admin.cabang.index'));
        $response->assertStatus(200);

        // Terminology checks
        $response->assertSee('jadwal kajian pemuda');
        $response->assertSee('Sudah Ada Kajian Pemuda');
        $response->assertSee('Belum Ada Kajian Pemuda');
        $response->assertSee('Status Kajian Pemuda');
        $response->assertSee('Kajian Pemuda');

        // Modal fields checks: maps_url, description, kajian pemuda
        $response->assertSee('name="maps_url"', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('id="editMapsUrl"', false);
        $response->assertSee('id="editDescription"', false);
        $response->assertSee('Hari Kajian');
        $response->assertSee('Jam / Waktu Kajian');
        $response->assertSee('Ustadz Pengampu Kajian');

        // Pengurus / koordinator pemuda fields & dynamic selection components
        $response->assertSee('name="ketua_pemuda"', false);
        $response->assertSee('name="sekretaris_pemuda"', false);
        $response->assertSee('name="bendahara_pemuda"', false);
        $response->assertSee('name="no_wa_pemuda"', false);
        $response->assertSee('id="editKetuaPemuda"', false);
        $response->assertSee('id="editSekretarisPemuda"', false);
        $response->assertSee('id="editBendaharaPemuda"', false);
        $response->assertSee('id="editNoWaPemuda"', false);
        $response->assertSee('id="editSelectKetuaPemuda"', false);
        $response->assertSee('id="editSelectSekretarisPemuda"', false);
        $response->assertSee('id="editSelectBendaharaPemuda"', false);
        $response->assertSee('id="editPemudaLoadingBadge"', false);
        $response->assertSee('id="hintNoWaPemudaAuto"', false);
        $response->assertSee('Pengurus / Koordinator Pemuda');
    }

    public function test_cabang_detail_endpoint_returns_all_database_attributes(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $cabang = Cabang::create([
            'wilayah_id'        => $wilayah->id,
            'code'              => 'TESTDETAIL' . rand(100, 999),
            'name'              => 'Cabang Detail Test ' . rand(100, 999),
            'description'       => 'Catatan pengujian detail cabang',
            'alamat'            => 'Jl. Uji Coba No. 123',
            'maps_url'          => 'https://maps.google.com/?q=test',
            'pimpinan_nama'     => 'Ust. Pimpinan Pengujian',
            'no_wa'             => '081234567899',
            'has_gelombang'     => 'sudah',
            'gelombang_hari'    => 'Ahad Pagi',
            'gelombang_jam'     => '06:00 - 07:30 WIB',
            'gelombang_ustadz'   => 'Ust. Ustadz Penguji',
            'ketua_pemuda'      => 'Ahmad Fauzi (Ketua)',
            'sekretaris_pemuda' => 'Budi Santoso (Sekretaris)',
            'bendahara_pemuda'  => 'Citra Dewi (Bendahara)',
            'no_wa_pemuda'      => '081234567800',
        ]);

        $response = $this->get(route('admin.cabang.detail', $cabang->id));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data'   => [
                'id'                => $cabang->id,
                'wilayah_id'        => $wilayah->id,
                'code'              => $cabang->code,
                'name'              => $cabang->name,
                'description'       => 'Catatan pengujian detail cabang',
                'alamat'            => 'Jl. Uji Coba No. 123',
                'maps_url'          => 'https://maps.google.com/?q=test',
                'pimpinan_nama'     => 'Ust. Pimpinan Pengujian',
                'no_wa'             => '081234567899',
                'has_gelombang'     => 'sudah',
                'gelombang_hari'    => 'Ahad Pagi',
                'gelombang_jam'     => '06:00 - 07:30 WIB',
                'gelombang_ustadz'   => 'Ust. Ustadz Penguji',
                'ketua_pemuda'      => 'Ahmad Fauzi (Ketua)',
                'sekretaris_pemuda' => 'Budi Santoso (Sekretaris)',
                'bendahara_pemuda'  => 'Citra Dewi (Bendahara)',
                'no_wa_pemuda'      => '081234567800',
            ],
        ]);

        $cabang->delete();
    }

    public function test_superadmin_can_create_and_update_cabang_with_all_database_fields(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $uniqueCode = 'TESTCRUD' . rand(100, 999);
        $name = 'Cabang Test CRUD ' . rand(100, 999);

        // 1. Create
        $responseCreate = $this->post(route('admin.cabang.simpan'), [
            'wilayah_id'        => $wilayah->id,
            'code'              => $uniqueCode,
            'name'              => $name,
            'pimpinan_nama'     => 'Ust. Awal',
            'no_wa'             => '0811111111',
            'alamat'            => 'Alamat Awal',
            'maps_url'          => 'https://maps.google.com/?q=awal',
            'description'       => 'Deskripsi Awal',
            'has_gelombang'     => 'sudah',
            'gelombang_hari'    => 'Sabtu Sore',
            'gelombang_jam'     => '16:00 - 17:30 WIB',
            'gelombang_ustadz'   => 'Ust. Ahmad',
            'ketua_pemuda'      => 'Ketua Pemuda Awal',
            'sekretaris_pemuda' => 'Sekretaris Pemuda Awal',
            'bendahara_pemuda'  => 'Bendahara Pemuda Awal',
            'no_wa_pemuda'      => '081111111100',
        ]);

        $responseCreate->assertRedirect(route('admin.cabang.index'));
        $created = Cabang::where('code', $uniqueCode)->first();
        $this->assertNotNull($created);
        $this->assertEquals('https://maps.google.com/?q=awal', $created->maps_url);
        $this->assertEquals('Deskripsi Awal', $created->description);
        $this->assertEquals('Sabtu Sore', $created->gelombang_hari);
        $this->assertEquals('Ketua Pemuda Awal', $created->ketua_pemuda);
        $this->assertEquals('Sekretaris Pemuda Awal', $created->sekretaris_pemuda);
        $this->assertEquals('Bendahara Pemuda Awal', $created->bendahara_pemuda);
        $this->assertEquals('081111111100', $created->no_wa_pemuda);

        // 2. Update
        $responseUpdate = $this->post(route('admin.cabang.update', $created->id), [
            'wilayah_id'        => $wilayah->id,
            'code'              => $uniqueCode,
            'name'              => $name . ' Diperbarui',
            'pimpinan_nama'     => 'Ust. Diperbarui',
            'no_wa'             => '0822222222',
            'alamat'            => 'Alamat Diperbarui',
            'maps_url'          => 'https://maps.google.com/?q=diperbarui',
            'description'       => 'Deskripsi Diperbarui',
            'has_gelombang'     => 'sudah',
            'gelombang_hari'    => 'Ahad Pagi',
            'gelombang_jam'     => '06:00 - 07:30 WIB',
            'gelombang_ustadz'   => 'Ust. Fauzi',
            'ketua_pemuda'      => 'Ketua Pemuda Diperbarui',
            'sekretaris_pemuda' => 'Sekretaris Pemuda Diperbarui',
            'bendahara_pemuda'  => 'Bendahara Pemuda Diperbarui',
            'no_wa_pemuda'      => '082222222200',
        ]);

        $responseUpdate->assertRedirect(route('admin.cabang.index'));

        $updated = Cabang::find($created->id);
        $this->assertEquals($name . ' Diperbarui', $updated->name);
        $this->assertEquals('https://maps.google.com/?q=diperbarui', $updated->maps_url);
        $this->assertEquals('Deskripsi Diperbarui', $updated->description);
        $this->assertEquals('Ust. Diperbarui', $updated->pimpinan_nama);
        $this->assertEquals('0822222222', $updated->no_wa);
        $this->assertEquals('Ahad Pagi', $updated->gelombang_hari);
        $this->assertEquals('Ketua Pemuda Diperbarui', $updated->ketua_pemuda);
        $this->assertEquals('Sekretaris Pemuda Diperbarui', $updated->sekretaris_pemuda);
        $this->assertEquals('Bendahara Pemuda Diperbarui', $updated->bendahara_pemuda);
        $this->assertEquals('082222222200', $updated->no_wa_pemuda);

        // Cleanup
        $updated->delete();
    }

    public function test_cabang_pemuda_endpoint_returns_list_of_pemuda_for_the_branch(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $cabang = Cabang::create([
            'wilayah_id' => $wilayah->id,
            'code'       => 'PEMUDAEP' . rand(100, 999),
            'name'       => 'Cabang Pemuda Test ' . rand(100, 999),
        ]);

        $otherCabang = Cabang::create([
            'wilayah_id' => $wilayah->id,
            'code'       => 'OTHER' . rand(100, 999),
            'name'       => 'Cabang Lain ' . rand(100, 999),
        ]);

        // Active pemuda in this branch
        $pemuda1 = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'REG' . rand(10000, 99999),
            'name'                => 'Ahmad Fauzi Test',
            'gender'              => 'L',
            'phone'               => '081234567890',
            'status_data'         => 'active',
            'status_verifikasi'   => 'verified',
        ]);

        $pemuda2 = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'REG' . rand(10000, 99999),
            'name'                => 'Budi Santoso Test',
            'gender'              => 'L',
            'phone'               => '081298765432',
            'status_data'         => 'active',
            'status_verifikasi'   => 'verified',
        ]);

        // Archived pemuda (should be excluded)
        $archivedPemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'REG' . rand(10000, 99999),
            'name'                => 'Zul Archived Test',
            'gender'              => 'L',
            'phone'               => '081200000000',
            'status_data'         => 'archived',
            'status_verifikasi'   => 'verified',
        ]);

        // Other branch pemuda (should be excluded)
        $otherPemuda = Pemuda::create([
            'cabang_id'           => $otherCabang->id,
            'registration_number' => 'REG' . rand(10000, 99999),
            'name'                => 'Orang Lain Test',
            'gender'              => 'L',
            'phone'               => '081299999999',
            'status_data'         => 'active',
            'status_verifikasi'   => 'verified',
        ]);

        $response = $this->get(route('admin.cabang.pemuda', $cabang->id));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'cabang' => [
                'id'   => $cabang->id,
                'name' => $cabang->name,
            ],
        ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        $names = array_column($data, 'name');
        $this->assertContains('Ahmad Fauzi Test', $names);
        $this->assertContains('Budi Santoso Test', $names);
        $this->assertNotContains('Zul Archived Test', $names);
        $this->assertNotContains('Orang Lain Test', $names);

        // Cleanup
        $pemuda1->delete();
        $pemuda2->delete();
        $archivedPemuda->delete();
        $otherPemuda->delete();
        $cabang->delete();
        $otherCabang->delete();
    }

    public function test_cabang_pemuda_endpoint_returns_404_for_non_existent_cabang(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get(route('admin.cabang.pemuda', 999999));
        $response->assertStatus(404);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'Data cabang tidak ditemukan.',
        ]);
    }

    public function test_non_superadmin_cannot_access_cabang_pemuda_endpoint(): void
    {
        $nonSuperadmin = User::where('role_id', '!=', 1)->first();
        if (!$nonSuperadmin) {
            $this->markTestSkipped('Non-superadmin user not found.');
        }

        $this->actingAs($nonSuperadmin);

        $cabang = Cabang::first();
        $response = $this->get(route('admin.cabang.pemuda', $cabang->id));
        $response->assertStatus(403);
    }
}
