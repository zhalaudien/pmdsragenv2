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
        $superadmin = User::where('username', 'superadmin')->first();
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
        $adminWilayah = User::where('username', 'admin_w1')->first();
        if ($adminWilayah) {
            $this->actingAs($adminWilayah);
            $response = $this->get('/admin/users');
            // Role middleware aborts with 403 or redirects
            $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
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
}
