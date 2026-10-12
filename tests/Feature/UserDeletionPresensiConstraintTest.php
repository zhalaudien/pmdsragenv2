<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\KegiatanPresensi;
use App\Models\Pemuda;
use App\Models\PresensiDetail;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserDeletionPresensiConstraintTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_delete_user_who_created_kegiatan_presensi_and_presensi_detail(): void
    {
        $superadmin = User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
        $this->assertNotNull($superadmin, 'Superadmin must exist');

        $cabangRole = UserRole::firstOrCreate(['name' => 'admin_cabang'], ['description' => 'Admin Cabang']);

        $targetUser = User::create([
            'name'        => 'Target User To Delete',
            'email'       => 'target_del_' . uniqid() . '@example.com',
            'username'    => 'target_del_' . uniqid(),
            'password'    => Hash::make('password123'),
            'role_id'     => $cabangRole->id,
            'status'      => 1,
            'sumber_data' => 'manual',
        ]);

        // Create personal access token
        $targetUser->createToken('Test Mobile Token');

        $cabang = Cabang::first();
        $this->assertNotNull($cabang, 'Cabang must exist in database');

        // Create kegiatan presensi created by targetUser
        $kegiatan = KegiatanPresensi::create([
            'cabang_id'      => $cabang->id,
            'nama_kegiatan'  => 'Pengajian Pemuda Rutin',
            'tanggal'        => now()->toDateString(),
            'target_peserta' => 'semua',
            'status'         => 'selesai',
            'created_by'     => $targetUser->id,
        ]);

        $pemuda = Pemuda::first();
        if ($pemuda) {
            // Create presensi detail created by targetUser
            $detail = PresensiDetail::create([
                'kegiatan_presensi_id' => $kegiatan->id,
                'pemuda_id'            => $pemuda->id,
                'status_kehadiran'     => 'hadir',
                'created_by'           => $targetUser->id,
            ]);
        }

        // Acting as superadmin and call delete endpoint (POST)
        $this->actingAs($superadmin);

        $response = $this->post(route('admin.users.delete', $targetUser->id));
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Verify target user is deleted
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);

        // Verify kegiatan_presensi and presensi_detail remain in DB but created_by is null
        $kegiatan->refresh();
        $this->assertNull($kegiatan->created_by);

        if (isset($detail)) {
            $detail->refresh();
            $this->assertNull($detail->created_by);
        }
    }
}
