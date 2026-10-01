<?php

namespace Tests\Feature;

use App\Models\KegiatanPerwakilan;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class KegiatanFlyerUploadTest extends TestCase
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
            ['username' => 'test_superadmin_flyer'],
            [
                'name'     => 'Superadmin Test Flyer',
                'email'    => 'superadmin_flyer@test.com',
                'password' => bcrypt('password123'),
                'role_id'  => $roleSuperadmin->id,
                'status'   => 1,
            ]
        );
    }

    public function test_superadmin_can_view_flyer_upload_field_in_kegiatan_index_page(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->get(route('admin.kegiatan-perwakilan.index'));

        $response->assertStatus(200);
        $response->assertSee('multipart/form-data', false);
        $response->assertSee('name="flyer"', false);
        $response->assertSee('Flyer Kegiatan', false);
        $response->assertSee('modalPreviewFlyer', false);
    }

    public function test_superadmin_can_create_kegiatan_with_flyer_upload(): void
    {
        $file = UploadedFile::fake()->image('poster_kajian.jpg', 800, 1000);

        $response = $this->actingAs($this->superadmin)
            ->post(route('admin.kegiatan-perwakilan.simpan'), [
                'nama_kegiatan' => 'Kajian Spesial Pemuda Sukowati',
                'tanggal'       => '2026-10-15',
                'jam'           => '19.30 - 21.30 WIB',
                'narahubung'    => '081234567890',
                'kategori'      => 'Kajian Akbar',
                'lokasi'        => 'Gedung Dakwah Sragen',
                'flyer'         => $file,
                'is_active'     => 1,
            ]);

        $response->assertRedirect(route('admin.kegiatan-perwakilan.index'));
        $response->assertSessionHas('success');

        $kegiatan = KegiatanPerwakilan::where('nama_kegiatan', 'Kajian Spesial Pemuda Sukowati')->first();
        $this->assertNotNull($kegiatan);
        $this->assertNotNull($kegiatan->flyer);
        $this->assertFileExists(public_path('uploads/kegiatan/' . $kegiatan->flyer));
        $this->assertNotNull($kegiatan->flyer_url);
        $this->assertStringContainsString($kegiatan->flyer, $kegiatan->flyer_url);

        // Cleanup
        if (file_exists(public_path('uploads/kegiatan/' . $kegiatan->flyer))) {
            @unlink(public_path('uploads/kegiatan/' . $kegiatan->flyer));
        }
        $kegiatan->delete();
    }

    public function test_superadmin_can_update_and_replace_flyer(): void
    {
        $file1 = UploadedFile::fake()->image('flyer_awal.jpg', 600, 800);

        $this->actingAs($this->superadmin)
            ->post(route('admin.kegiatan-perwakilan.simpan'), [
                'nama_kegiatan' => 'Kajian Rutin Pemuda Test Update',
                'tanggal'       => '2026-10-20',
                'jam'           => '19.30 - 21.00 WIB',
                'flyer'         => $file1,
            ]);

        $kegiatan = KegiatanPerwakilan::where('nama_kegiatan', 'Kajian Rutin Pemuda Test Update')->first();
        $oldFlyer = $kegiatan->flyer;
        $this->assertFileExists(public_path('uploads/kegiatan/' . $oldFlyer));

        // Replace with new flyer
        $file2 = UploadedFile::fake()->image('flyer_revisi.png', 800, 1200);

        $response = $this->actingAs($this->superadmin)
            ->post(route('admin.kegiatan-perwakilan.update', $kegiatan->id), [
                'nama_kegiatan' => 'Kajian Rutin Pemuda Test Update Revisi',
                'tanggal'       => '2026-10-20',
                'jam'           => '19.30 - 21.00 WIB',
                'flyer'         => $file2,
            ]);

        $response->assertRedirect(route('admin.kegiatan-perwakilan.index'));

        $kegiatan->refresh();
        $newFlyer = $kegiatan->flyer;

        // Old file must be deleted, new file must exist
        $this->assertNotEquals($oldFlyer, $newFlyer);
        $this->assertFileDoesNotExist(public_path('uploads/kegiatan/' . $oldFlyer));
        $this->assertFileExists(public_path('uploads/kegiatan/' . $newFlyer));

        // Cleanup
        if (file_exists(public_path('uploads/kegiatan/' . $newFlyer))) {
            @unlink(public_path('uploads/kegiatan/' . $newFlyer));
        }
        $kegiatan->delete();
    }

    public function test_superadmin_can_delete_flyer_via_hapus_flyer_checkbox(): void
    {
        $file = UploadedFile::fake()->image('flyer_to_delete.jpg', 600, 800);

        $this->actingAs($this->superadmin)
            ->post(route('admin.kegiatan-perwakilan.simpan'), [
                'nama_kegiatan' => 'Kajian Hapus Flyer Test',
                'tanggal'       => '2026-10-25',
                'jam'           => '19.30 - 21.00 WIB',
                'flyer'         => $file,
            ]);

        $kegiatan = KegiatanPerwakilan::where('nama_kegiatan', 'Kajian Hapus Flyer Test')->first();
        $flyerPath = public_path('uploads/kegiatan/' . $kegiatan->flyer);
        $this->assertFileExists($flyerPath);

        // Send update with hapus_flyer = 1
        $response = $this->actingAs($this->superadmin)
            ->post(route('admin.kegiatan-perwakilan.update', $kegiatan->id), [
                'nama_kegiatan' => 'Kajian Hapus Flyer Test',
                'tanggal'       => '2026-10-25',
                'jam'           => '19.30 - 21.00 WIB',
                'hapus_flyer'   => 1,
            ]);

        $response->assertRedirect(route('admin.kegiatan-perwakilan.index'));

        $kegiatan->refresh();
        $this->assertNull($kegiatan->flyer);
        $this->assertNull($kegiatan->flyer_url);
        $this->assertFileDoesNotExist($flyerPath);

        $kegiatan->delete();
    }

    public function test_deleting_kegiatan_deletes_its_flyer_file(): void
    {
        $file = UploadedFile::fake()->image('flyer_delete_kegiatan.jpg', 600, 800);

        $this->actingAs($this->superadmin)
            ->post(route('admin.kegiatan-perwakilan.simpan'), [
                'nama_kegiatan' => 'Kajian Delete Record Test',
                'tanggal'       => '2026-10-28',
                'jam'           => '19.30 - 21.00 WIB',
                'flyer'         => $file,
            ]);

        $kegiatan = KegiatanPerwakilan::where('nama_kegiatan', 'Kajian Delete Record Test')->first();
        $flyerPath = public_path('uploads/kegiatan/' . $kegiatan->flyer);
        $this->assertFileExists($flyerPath);

        // Delete kegiatan
        $response = $this->actingAs($this->superadmin)
            ->post(route('admin.kegiatan-perwakilan.delete', $kegiatan->id));

        $response->assertRedirect(route('admin.kegiatan-perwakilan.index'));
        $this->assertDatabaseMissing('kegiatan_perwakilan', ['id' => $kegiatan->id]);
        $this->assertFileDoesNotExist($flyerPath);
    }

    public function test_api_perwakilan_kegiatan_returns_flyer_and_flyer_url(): void
    {
        $kegiatan = KegiatanPerwakilan::create([
            'nama_kegiatan' => 'Kajian Akbar Mobile Presensi',
            'kategori'      => 'Kajian Akbar',
            'tanggal'       => '2026-10-30',
            'hari_tanggal'  => 'Jumat, 30 Oktober 2026',
            'jam'           => '19.30 WIB',
            'lokasi'        => 'Gedung Dakwah MTA Sragen',
            'deskripsi'     => 'Kajian akbar pemuda MTA.',
            'flyer'         => 'test_sample_flyer.jpg',
            'is_active'     => true,
        ]);

        file_put_contents(public_path('uploads/kegiatan/test_sample_flyer.jpg'), 'sample');

        $response = $this->getJson('/api/v1/perwakilan/kegiatan');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'nama_kegiatan',
                    'tanggal',
                    'flyer',
                    'flyer_url',
                ]
            ]
        ]);

        $data = collect($response->json('data'))->firstWhere('id', $kegiatan->id);
        $this->assertNotNull($data);
        $this->assertEquals('test_sample_flyer.jpg', $data['flyer']);
        $this->assertStringContainsString('uploads/kegiatan/test_sample_flyer.jpg', $data['flyer_url']);

        // Cleanup
        @unlink(public_path('uploads/kegiatan/test_sample_flyer.jpg'));
        $kegiatan->delete();
    }
}
