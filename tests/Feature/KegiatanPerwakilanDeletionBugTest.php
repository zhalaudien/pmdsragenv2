<?php

namespace Tests\Feature;

use App\Models\KegiatanPerwakilan;
use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;

class KegiatanPerwakilanDeletionBugTest extends TestCase
{
    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $roleSuperadmin = UserRole::firstOrCreate(['id' => 1], [
            'name'         => 'superadmin',
            'display_name' => 'Superadmin',
        ]);

        $this->superadmin = User::firstOrCreate(
            ['username' => 'test_superadmin_kegiatan'],
            [
                'name'     => 'Superadmin Test Kegiatan',
                'email'    => 'superadmin_kegiatan@test.com',
                'password' => bcrypt('password123'),
                'role_id'  => $roleSuperadmin->id,
                'status'   => 1,
            ]
        );
    }

    public function test_deleting_all_activities_leaves_database_empty_and_does_not_reseed(): void
    {
        // 1. Pastikan ada data awal
        KegiatanPerwakilan::seedDefaults(true);
        $this->assertGreaterThan(0, KegiatanPerwakilan::count());

        // 2. Hapus semua satu per satu
        $all = KegiatanPerwakilan::all();
        foreach ($all as $item) {
            $this->actingAs($this->superadmin)
                ->post(route('admin.kegiatan-perwakilan.delete', $item->id));
        }

        // Pastikan tabel kosong
        $this->assertEquals(0, KegiatanPerwakilan::count(), 'Tabel harus kosong setelah semua item dihapus');

        // 3. Akses kembali halaman index admin
        $resp = $this->actingAs($this->superadmin)
            ->get(route('admin.kegiatan-perwakilan.index'));

        $resp->assertStatus(200);
        $resp->assertSee('Tidak Ada Agenda Kegiatan');

        // 4. Pastikan data TIDAK kembali lagi
        $this->assertEquals(0, KegiatanPerwakilan::count(), 'Data tidak boleh di-reseed otomatis saat tabel kosong');
    }

    public function test_api_mobile_does_not_reseed_when_empty(): void
    {
        // Kosongkan tabel
        KegiatanPerwakilan::query()->delete();
        $this->assertEquals(0, KegiatanPerwakilan::count());

        // Akses endpoint API mobile
        $resp = $this->getJson('/api/v1/perwakilan/kegiatan');

        $resp->assertStatus(200);
        $resp->assertJson([
            'success' => true,
            'data'    => [],
        ]);

        // Pastikan tabel tetap kosong
        $this->assertEquals(0, KegiatanPerwakilan::count(), 'API mobile tidak boleh me-reseed otomatis saat tabel kosong');
    }

    public function test_hapus_semua_endpoint_deletes_all_records(): void
    {
        // Pastikan ada data
        KegiatanPerwakilan::seedDefaults(true);
        $this->assertGreaterThan(0, KegiatanPerwakilan::count());

        // Jalankan hapus semua
        $resp = $this->actingAs($this->superadmin)
            ->post(route('admin.kegiatan-perwakilan.hapus-semua'));

        $resp->assertRedirect(route('admin.kegiatan-perwakilan.index'));
        $resp->assertSessionHas('success');

        // Verifikasi database kosong
        $this->assertEquals(0, KegiatanPerwakilan::count());

        // Request ke index
        $indexResp = $this->actingAs($this->superadmin)->get(route('admin.kegiatan-perwakilan.index'));
        $indexResp->assertStatus(200);
        $this->assertEquals(0, KegiatanPerwakilan::count());
    }

    public function test_reset_defaults_endpoint_restores_default_records(): void
    {
        // Kosongkan tabel
        KegiatanPerwakilan::query()->delete();
        $this->assertEquals(0, KegiatanPerwakilan::count());

        // Panggil reset defaults
        $resp = $this->actingAs($this->superadmin)
            ->post(route('admin.kegiatan-perwakilan.reset-defaults'));

        $resp->assertRedirect(route('admin.kegiatan-perwakilan.index'));
        $resp->assertSessionHas('success');

        // Verifikasi data default kembali
        $this->assertGreaterThanOrEqual(5, KegiatanPerwakilan::count());
    }
}
