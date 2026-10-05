<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Pemuda;
use App\Models\User;
use App\Models\UserRole;
use App\Services\MtaApiService;
use Mockery;
use Tests\TestCase;

class UserCreationProfileBindingTest extends TestCase
{
    protected function getSuperadmin(): User
    {
        $user = User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
        $this->assertNotNull($user, 'Superadmin user must exist');
        return $user;
    }

    public function test_search_unified_endpoint_returns_combined_pemuda_and_warga(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $mockApi = Mockery::mock(MtaApiService::class);
        $mockApi->shouldReceive('searchWarga')
            ->once()
            ->andReturn([
                'success' => true,
                'data'    => [
                    [
                        'uuid'        => 'uuid-unified-warga-1234',
                        'nama'        => 'Ahmad Warga Unified',
                        'cabang'      => 'Sragen Kota',
                        'telepon'     => '081234567899',
                        'status'      => 'Warga MTA',
                    ],
                ],
            ]);

        $this->app->instance(MtaApiService::class, $mockApi);

        // Get an active pemuda
        $pemuda = Pemuda::where('status_data', '!=', 'archived')->first();
        $query = $pemuda ? substr($pemuda->name, 0, 3) : 'Ahm';

        $response = $this->getJson(route('admin.users.search-unified', ['q' => $query]));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => [
                    'sumber_data',
                    'sumber_label',
                    'id',
                    'uuid',
                    'nama',
                    'cabang_id',
                    'cabang_name',
                    'wilayah_id',
                    'wilayah_name',
                    'has_account',
                ],
            ],
        ]);
    }

    public function test_search_pemuda_endpoint_returns_json_and_account_status(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        // Get an existing pemuda
        $pemuda = Pemuda::where('status_data', '!=', 'archived')->first();
        $this->assertNotNull($pemuda, 'Active Pemuda must exist for testing');

        $query = substr($pemuda->name, 0, 4);
        $response = $this->getJson(route('admin.users.search-pemuda', ['q' => $query]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => [
                    'id',
                    'nama',
                    'gender',
                    'gender_label',
                    'cabang_id',
                    'cabang_name',
                    'wilayah_id',
                    'wilayah_name',
                    'email',
                    'phone',
                    'reg_no',
                    'mta_uuid',
                    'has_account',
                ],
            ],
        ]);
    }

    public function test_search_pemuda_forbidden_for_non_superadmin(): void
    {
        $adminCabang = User::where('role_id', 3)->first() ?? User::where('username', 'admin_c1')->first();
        if ($adminCabang) {
            $this->actingAs($adminCabang);
            $response = $this->getJson(route('admin.users.search-pemuda', ['q' => 'Ahmad']));
            $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
        }
    }

    public function test_search_warga_endpoint_returns_json_with_mocked_api(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $mockApi = Mockery::mock(MtaApiService::class);
        $mockApi->shouldReceive('searchWarga')
            ->once()
            ->andReturn([
                'success' => true,
                'data'    => [
                    [
                        'uuid'        => 'uuid-test-warga-1234',
                        'nama'        => 'Ahmad Warga Test',
                        'cabang'      => 'Sragen Kota',
                        'telepon'     => '081234567890',
                        'status'      => 'Warga MTA',
                    ],
                ],
            ]);

        $this->app->instance(MtaApiService::class, $mockApi);

        $response = $this->getJson(route('admin.users.search-warga', ['q' => 'Ahmad']));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data'   => [
                [
                    'uuid' => 'uuid-test-warga-1234',
                    'nama' => 'Ahmad Warga Test',
                ],
            ],
        ]);
    }

    public function test_superadmin_can_create_user_from_pemuda(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        // Find a pemuda without a user account
        $existingPemudaUserIds = User::whereNotNull('pemuda_id')->pluck('pemuda_id')->all();
        $pemuda = Pemuda::whereNotIn('id', $existingPemudaUserIds)
            ->where('status_data', '!=', 'archived')
            ->first();

        $this->assertNotNull($pemuda, 'Must find a pemuda without user account');

        $username = 'test_pemuda_usr_' . time();
        $email    = 'pemuda_' . time() . '@example.test';

        $payload = [
            'sumber_data' => 'pemuda',
            'pemuda_id'   => $pemuda->id,
            'name'        => $pemuda->name,
            'username'    => $username,
            'password'    => 'password123',
            'email'       => $email,
            'role_id'     => 3, // admin_cabang
            'cabang_id'   => $pemuda->cabang_id,
            'status'      => 1,
        ];

        $response = $this->post(route('admin.users.simpan'), $payload);
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'username'    => $username,
            'email'       => $email,
            'pemuda_id'   => $pemuda->id,
            'sumber_data' => 'pemuda',
        ]);

        // Clean up
        User::where('username', $username)->delete();
    }

    public function test_cannot_create_duplicate_user_for_same_pemuda(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        // Pick a pemuda and create one user first
        $existingPemudaUserIds = User::whereNotNull('pemuda_id')->pluck('pemuda_id')->all();
        $pemuda = Pemuda::whereNotIn('id', $existingPemudaUserIds)
            ->where('status_data', '!=', 'archived')
            ->first();

        $this->assertNotNull($pemuda);

        $createdUser = User::create([
            'name'        => $pemuda->name,
            'username'    => 'user_uniq_1_' . time(),
            'email'       => 'uniq1_' . time() . '@example.test',
            'password'    => bcrypt('password123'),
            'role_id'     => 3,
            'cabang_id'   => $pemuda->cabang_id,
            'pemuda_id'   => $pemuda->id,
            'sumber_data' => 'pemuda',
            'status'      => 1,
        ]);

        // Attempt to create another user with same pemuda_id
        $payload = [
            'sumber_data' => 'pemuda',
            'pemuda_id'   => $pemuda->id,
            'name'        => $pemuda->name,
            'username'    => 'user_uniq_2_' . time(),
            'password'    => 'password123',
            'email'       => 'uniq2_' . time() . '@example.test',
            'role_id'     => 3,
            'cabang_id'   => $pemuda->cabang_id,
            'status'      => 1,
        ];

        $response = $this->post(route('admin.users.simpan'), $payload);
        $response->assertSessionHas('error');

        // Clean up
        $createdUser->delete();
    }

    public function test_superadmin_can_create_user_from_warga_mta(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wargaUuid = 'warga-test-uuid-' . time();
        $username  = 'test_warga_usr_' . time();
        $email     = 'warga_' . time() . '@example.test';

        $payload = [
            'sumber_data'    => 'warga',
            'mta_warga_uuid' => $wargaUuid,
            'name'           => 'Budi Warga MTA',
            'username'       => $username,
            'password'       => 'password123',
            'email'          => $email,
            'role_id'        => 7, // koordinator_gdm
            'status'         => 1,
        ];

        $response = $this->post(route('admin.users.simpan'), $payload);
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'username'       => $username,
            'email'          => $email,
            'mta_warga_uuid' => $wargaUuid,
            'sumber_data'    => 'warga',
            'pemuda_id'      => null,
        ]);

        // Clean up
        User::where('username', $username)->delete();
    }

    public function test_cannot_create_user_without_selecting_profile(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        // Missing sumber_data
        $response = $this->post(route('admin.users.simpan'), [
            'name'     => 'Tanpa Profil',
            'username' => 'tanpa_profil',
            'password' => 'password123',
            'email'    => 'tanpa_profil@example.test',
            'role_id'  => 1,
        ]);

        $response->assertSessionHasErrors(['sumber_data']);
    }
}
