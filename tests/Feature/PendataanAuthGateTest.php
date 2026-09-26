<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Pemuda;
use App\Models\EducationLevel;
use App\Models\JobStatus;
use App\Models\District;
use App\Models\Village;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PendataanAuthGateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pendataan_index_renders_auth_view_when_unauthenticated(): void
    {
        $response = $this->get('/pendataan');
        $response->assertStatus(200);
        $response->assertSee('Langkah 1: Verifikasi &amp; Autentikasi Pemuda', false);
        $response->assertSee('1. Cabang MTA Tempat Mengaji');
        $response->assertSee('2. Nama Lengkap');
        $response->assertSee('3. Tanggal Lahir');
        $response->assertSee('Masuk ke Formulir Pendataan');
    }

    public function test_pendataan_form_redirects_to_auth_when_unauthenticated(): void
    {
        // Akses langsung ke form pendataan tanpa autentikasi awal harus ditolak
        $response = $this->get('/pendataan/form');
        $response->assertRedirect('/pendataan');
        $response->assertSessionHas('error');
    }

    public function test_search_nama_returns_results_with_birth_date_raw(): void
    {
        $cabang = Cabang::first();
        $this->assertNotNull($cabang);

        $uniqueName = 'Ahmad Test Unik ' . uniqid();
        $birthDate = '2003-08-14';

        $pemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'TEST-AUTH-' . uniqid(),
            'name'                => $uniqueName,
            'gender'              => 'L',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => $birthDate,
            'phone'               => '081234567899',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
        ]);

        $response = $this->getJson("/pendataan/search-nama?cabang_id={$cabang->id}&q=" . urlencode($uniqueName));
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals($pemuda->id, $data[0]['id']);
        $this->assertEquals($uniqueName, $data[0]['name']);
        $this->assertEquals($birthDate, $data[0]['birth_date_raw']);
        $this->assertEquals('14/08/2003', $data[0]['birth_date']);
        $this->assertEquals('pemuda', $data[0]['source']);
        $this->assertArrayHasKey('age', $data[0]);
        $this->assertArrayHasKey('age_text', $data[0]);
        $this->assertNotNull($data[0]['age']);
    }

    public function test_auth_with_existing_pemuda_logs_in_as_update_mode(): void
    {
        $cabang = Cabang::first();
        $uniqueName = 'Budi Santoso ' . uniqid();
        $birthDate = '2001-05-20';

        $pemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'PMD-EX-' . uniqid(),
            'name'                => $uniqueName,
            'gender'              => 'L',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => $birthDate,
            'phone'               => '081233334444',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
        ]);

        $payload = [
            'cabang_id'  => $cabang->id,
            'name'       => $uniqueName,
            'birth_date' => $birthDate,
        ];

        $response = $this->post('/pendataan/auth', $payload);
        $response->assertRedirect('/pendataan/form');

        // Verify session
        $this->assertTrue(session()->has('pendataan_auth'));
        $auth = session('pendataan_auth');
        $this->assertTrue($auth['authenticated']);
        $this->assertEquals('update', $auth['mode']);
        $this->assertEquals($pemuda->id, $auth['pemuda_id']);
        $this->assertEquals($cabang->id, $auth['cabang_id']);
        $this->assertEquals($uniqueName, $auth['name']);
        $this->assertEquals($birthDate, $auth['birth_date']);
    }

    public function test_auth_with_selected_suggestion_id_logs_in_as_update_mode(): void
    {
        $cabang = Cabang::first();
        $uniqueName = 'Sugesti User ' . uniqid();
        $birthDate = '2002-11-10';

        $pemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'PMD-SUG-' . uniqid(),
            'name'                => $uniqueName,
            'gender'              => 'L',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => $birthDate,
            'phone'               => '081255556666',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
        ]);

        $payload = [
            'cabang_id'       => $cabang->id,
            'name'            => $uniqueName,
            'birth_date'      => $birthDate,
            'selected_id'     => $pemuda->id,
            'selected_source' => 'pemuda',
        ];

        $response = $this->post('/pendataan/auth', $payload);
        $response->assertRedirect('/pendataan/form');

        $auth = session('pendataan_auth');
        $this->assertEquals('update', $auth['mode']);
        $this->assertEquals($pemuda->id, $auth['pemuda_id']);
    }

    public function test_auth_with_selected_suggestion_rejects_when_manual_birth_date_mismatches(): void
    {
        $cabang = Cabang::first();
        $uniqueName = 'Mismatched User ' . uniqid();
        $correctBirthDate = '2002-11-10';
        $wrongBirthDate   = '1999-01-01';

        $pemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'PMD-MIS-' . uniqid(),
            'name'                => $uniqueName,
            'gender'              => 'L',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => $correctBirthDate,
            'phone'               => '081255557777',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
        ]);

        $payload = [
            'cabang_id'       => $cabang->id,
            'name'            => $uniqueName,
            'birth_date'      => $wrongBirthDate,
            'selected_id'     => $pemuda->id,
            'selected_source' => 'pemuda',
        ];

        $response = $this->post('/pendataan/auth', $payload);
        $response->assertSessionHas('error');
        $this->assertFalse(session()->has('pendataan_auth'));
    }

    public function test_auth_with_new_name_logs_in_as_create_mode(): void
    {
        $cabang = Cabang::first();
        $newName = 'Kader Baru ' . uniqid();
        $birthDate = '2004-12-25';

        $payload = [
            'cabang_id'  => $cabang->id,
            'name'       => $newName,
            'birth_date' => $birthDate,
        ];

        $response = $this->post('/pendataan/auth', $payload);
        $response->assertRedirect('/pendataan/form');

        $this->assertTrue(session()->has('pendataan_auth'));
        $auth = session('pendataan_auth');
        $this->assertTrue($auth['authenticated']);
        $this->assertEquals('create', $auth['mode']);
        $this->assertNull($auth['pemuda_id']);
        $this->assertEquals($newName, $auth['name']);
        $this->assertEquals($birthDate, $auth['birth_date']);
    }

    public function test_authenticated_user_can_access_pendataan_form(): void
    {
        $cabang = Cabang::first();
        $name = 'Akses Form Test ' . uniqid();

        $sessionData = [
            'authenticated'    => true,
            'mode'             => 'create',
            'pemuda_id'        => null,
            'cabang_id'        => $cabang->id,
            'cabang_name'      => $cabang->name,
            'name'             => $name,
            'birth_date'       => '2002-06-15',
            'gender'           => null,
            'mta_warga_uuid'   => null,
            'authenticated_at' => now()->timestamp,
        ];

        $response = $this->withSession(['pendataan_auth' => $sessionData])->get('/pendataan/form');
        $response->assertStatus(200);
        $response->assertSee($name);
        $response->assertSee($cabang->name);
        $response->assertSee('Mode: Pendaftaran Pemuda Baru');
        $response->assertSee('Ganti Identitas / Keluar');
    }

    public function test_authenticated_user_visiting_index_is_redirected_to_form(): void
    {
        $cabang = Cabang::first();

        $sessionData = [
            'authenticated'    => true,
            'mode'             => 'create',
            'pemuda_id'        => null,
            'cabang_id'        => $cabang->id,
            'cabang_name'      => $cabang->name,
            'name'             => 'Auto Redirect User',
            'birth_date'       => '2001-01-01',
            'gender'           => null,
            'mta_warga_uuid'   => null,
            'authenticated_at' => now()->timestamp,
        ];

        $response = $this->withSession(['pendataan_auth' => $sessionData])->get('/pendataan');
        $response->assertRedirect('/pendataan/form');
    }

    public function test_logout_pemuda_clears_session_and_redirects_to_auth(): void
    {
        $sessionData = [
            'authenticated' => true,
            'mode'          => 'create',
            'cabang_id'     => 1,
            'name'          => 'Logout Test',
            'birth_date'    => '2000-01-01',
        ];

        $response = $this->withSession(['pendataan_auth' => $sessionData])->get('/pendataan/keluar');
        $response->assertRedirect('/pendataan');
        $this->assertFalse(session()->has('pendataan_auth'));
    }

    public function test_update_mode_loads_existing_pemuda_data_in_form(): void
    {
        $cabang = Cabang::first();
        $name = 'Existing Data Form ' . uniqid();
        $birthDate = '2000-10-10';

        $pemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'PMD-FORM-' . uniqid(),
            'name'                => $name,
            'gender'              => 'L',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => $birthDate,
            'phone'               => '081234567890',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
        ]);

        $sessionData = [
            'authenticated'    => true,
            'mode'             => 'update',
            'pemuda_id'        => $pemuda->id,
            'cabang_id'        => $cabang->id,
            'cabang_name'      => $cabang->name,
            'name'             => $name,
            'birth_date'       => $birthDate,
            'gender'           => 'L',
            'mta_warga_uuid'   => null,
            'authenticated_at' => now()->timestamp,
        ];

        $response = $this->withSession(['pendataan_auth' => $sessionData])->get('/pendataan/form');
        $response->assertStatus(200);
        $response->assertSee('Mode: Pembaruan Data Pemuda');
        $response->assertSee($name);
    }
}
