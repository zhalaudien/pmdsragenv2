<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PmdSragenRoutesTest extends TestCase
{
    public function test_public_landing_page_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Pemuda MTA Perwakilan Sragen');
    }

    public function test_public_pendataan_page_accessible(): void
    {
        $response = $this->get('/pendataan');
        $response->assertStatus(200);
        $response->assertSee('Formulir Pendataan Pemuda');
    }

    public function test_admin_login_page_accessible(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk ke Dashboard');
    }

    public function test_unauthenticated_admin_access_redirects_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/admin/login');
    }

    public function test_api_cabang_endpoint(): void
    {
        $response = $this->getJson('/api/cabang/1');
        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_api_villages_endpoint(): void
    {
        $response = $this->getJson('/api/villages/1');
        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_superadmin_can_access_all_admin_pages(): void
    {
        $superadmin = User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
        $this->assertNotNull($superadmin, 'Superadmin user must exist');

        $this->actingAs($superadmin);

        // Dashboard
        $this->get('/admin/dashboard')->assertStatus(200);

        // Persebaran
        $this->get('/admin/dashboard/persebaran')->assertStatus(200);
        $this->get('/admin/persebaran')->assertStatus(200);

        // Pemuda CRUD & Utilities
        $this->get('/admin/pemuda')->assertStatus(200);
        $this->get('/admin/pemuda/tambah')->assertStatus(200);
        $this->get('/admin/pemuda/export')->assertStatus(200);
        $this->get('/admin/pemuda/import')->assertStatus(200);
        $this->get('/admin/pemuda/backup')->assertStatus(200);

        // Wilayah & Cabang
        $this->get('/admin/wilayah')->assertStatus(200);
        $this->get('/admin/cabang')->assertStatus(200);

        // Users
        $this->get('/admin/users')->assertStatus(200);

        // Warga MTA & Sync
        $this->get('/admin/warga-mta')->assertStatus(200);
        $this->get('/admin/mta-sync')->assertStatus(200);

        // Homepage Settings
        $this->get('/admin/homepage')->assertStatus(200);
    }

    public function test_admin_wilayah_cannot_access_user_management(): void
    {
        $adminWilayah = User::where('role_id', 2)->first() ?? User::where('username', 'admin_w1')->first();
        if ($adminWilayah) {
            $this->actingAs($adminWilayah);
            $response = $this->get('/admin/users');
            // Role middleware aborts with 403 or redirects
            $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_insecure_public_scraping_endpoints_are_disabled(): void
    {
        // Public youth detail by ID must not exist
        $this->getJson('/pendataan/pemuda-detail/1')->assertStatus(404);
        // Public citizen search must not exist
        $this->getJson('/pendataan/search-warga')->assertStatus(404);
    }

    public function test_api_cabang_does_not_leak_contact_info(): void
    {
        $response = $this->getJson('/api/cabang/1');
        $response->assertStatus(200);
        $data = $response->json();
        if (!empty($data)) {
            $first = $data[0];
            $this->assertArrayNotHasKey('no_wa', $first, 'Public dropdown must not expose leader WhatsApp numbers');
            $this->assertArrayNotHasKey('pimpinan_nama', $first, 'Public dropdown must not expose leader names');
        }
    }

    public function test_pendataan_sukses_with_session(): void
    {
        $response = $this->withSession([
            'sukses_data' => [
                'registration_number' => '8601202609169999',
                'name' => 'Fulan bin Fulan',
                'cabang_name' => 'Sragen Kota',
                'created_at' => '16/09/2026 20:50',
                'is_update' => false,
            ]
        ])->get('/pendataan/sukses');

        $response->assertStatus(200);
        $response->assertSee('8601202609169999');
        $response->assertSee('Fulan bin Fulan');
    }

    public function test_pendataan_search_nama_by_cabang(): void
    {
        // Without params returns empty list
        $response = $this->getJson('/pendataan/search-nama');
        $response->assertStatus(200);
        $this->assertEquals([], $response->json('data'));

        // With valid params
        $response2 = $this->getJson('/pendataan/search-nama?cabang_id=1&q=ahmad');
        $response2->assertStatus(200);
        $response2->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'name', 'gender', 'gender_text', 'birth_date', 'birth_place']
            ]
        ]);
    }

    public function test_pendataan_get_pemuda_data_scopes_by_cabang(): void
    {
        // Non-existent ID returns 404
        $this->getJson('/pendataan/get-pemuda/999999?cabang_id=1')->assertStatus(404);

        $pemuda = \App\Models\Pemuda::where('status_data', 'active')->first();
        if ($pemuda) {
            // Valid match with its cabang_id returns 200
            $res = $this->getJson('/pendataan/get-pemuda/' . $pemuda->id . '?cabang_id=' . $pemuda->cabang_id);
            $res->assertStatus(200);
            $res->assertJsonPath('status', 'success');
            $this->assertEquals($pemuda->name, $res->json('data.name'));
            $this->assertArrayNotHasKey('nik', $res->json('data'), 'No NIK should be exposed');

            // Mismatched cabang returns 404
            $wrongCabangId = $pemuda->cabang_id + 999;
            $this->getJson('/pendataan/get-pemuda/' . $pemuda->id . '?cabang_id=' . $wrongCabangId)->assertStatus(404);
        }
    }
}
