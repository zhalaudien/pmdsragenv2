<?php

namespace Tests\Feature;

use App\Models\Cabang;
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
    }

    public function test_cabang_detail_endpoint_returns_all_database_attributes(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $cabang = Cabang::create([
            'wilayah_id'       => $wilayah->id,
            'code'             => 'TESTDETAIL' . rand(100, 999),
            'name'             => 'Cabang Detail Test ' . rand(100, 999),
            'description'      => 'Catatan pengujian detail cabang',
            'alamat'           => 'Jl. Uji Coba No. 123',
            'maps_url'         => 'https://maps.google.com/?q=test',
            'pimpinan_nama'    => 'Ust. Pimpinan Pengujian',
            'no_wa'            => '081234567899',
            'has_gelombang'    => 'sudah',
            'gelombang_hari'   => 'Ahad Pagi',
            'gelombang_jam'    => '06:00 - 07:30 WIB',
            'gelombang_ustadz' => 'Ust. Ustadz Penguji',
        ]);

        $response = $this->get(route('admin.cabang.detail', $cabang->id));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data'   => [
                'id'               => $cabang->id,
                'wilayah_id'       => $wilayah->id,
                'code'             => $cabang->code,
                'name'             => $cabang->name,
                'description'      => 'Catatan pengujian detail cabang',
                'alamat'           => 'Jl. Uji Coba No. 123',
                'maps_url'         => 'https://maps.google.com/?q=test',
                'pimpinan_nama'    => 'Ust. Pimpinan Pengujian',
                'no_wa'            => '081234567899',
                'has_gelombang'    => 'sudah',
                'gelombang_hari'   => 'Ahad Pagi',
                'gelombang_jam'    => '06:00 - 07:30 WIB',
                'gelombang_ustadz' => 'Ust. Ustadz Penguji',
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
            'wilayah_id'       => $wilayah->id,
            'code'             => $uniqueCode,
            'name'             => $name,
            'pimpinan_nama'    => 'Ust. Awal',
            'no_wa'            => '0811111111',
            'alamat'           => 'Alamat Awal',
            'maps_url'         => 'https://maps.google.com/?q=awal',
            'description'      => 'Deskripsi Awal',
            'has_gelombang'    => 'sudah',
            'gelombang_hari'   => 'Sabtu Sore',
            'gelombang_jam'    => '16:00 - 17:30 WIB',
            'gelombang_ustadz' => 'Ust. Ahmad',
        ]);

        $responseCreate->assertRedirect(route('admin.cabang.index'));
        $created = Cabang::where('code', $uniqueCode)->first();
        $this->assertNotNull($created);
        $this->assertEquals('https://maps.google.com/?q=awal', $created->maps_url);
        $this->assertEquals('Deskripsi Awal', $created->description);
        $this->assertEquals('Sabtu Sore', $created->gelombang_hari);

        // 2. Update
        $responseUpdate = $this->post(route('admin.cabang.update', $created->id), [
            'wilayah_id'       => $wilayah->id,
            'code'             => $uniqueCode,
            'name'             => $name . ' Diperbarui',
            'pimpinan_nama'    => 'Ust. Diperbarui',
            'no_wa'            => '0822222222',
            'alamat'           => 'Alamat Diperbarui',
            'maps_url'         => 'https://maps.google.com/?q=diperbarui',
            'description'      => 'Deskripsi Diperbarui',
            'has_gelombang'    => 'sudah',
            'gelombang_hari'   => 'Ahad Pagi',
            'gelombang_jam'    => '06:00 - 07:30 WIB',
            'gelombang_ustadz' => 'Ust. Fauzi',
        ]);

        $responseUpdate->assertRedirect(route('admin.cabang.index'));

        $updated = Cabang::find($created->id);
        $this->assertEquals($name . ' Diperbarui', $updated->name);
        $this->assertEquals('https://maps.google.com/?q=diperbarui', $updated->maps_url);
        $this->assertEquals('Deskripsi Diperbarui', $updated->description);
        $this->assertEquals('Ust. Diperbarui', $updated->pimpinan_nama);
        $this->assertEquals('0822222222', $updated->no_wa);
        $this->assertEquals('Ahad Pagi', $updated->gelombang_hari);

        // Cleanup
        $updated->delete();
    }
}
