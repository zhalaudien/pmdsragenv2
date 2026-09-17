<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Pemuda;
use App\Models\Alamat;
use App\Models\Pendidikan;
use App\Models\Pekerjaan;
use App\Models\Organisasi;
use App\Models\EducationLevel;
use App\Models\JobStatus;
use App\Models\Skill;
use App\Models\Interest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PendataanFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_search_nama_returns_matching_pemuda_in_cabang(): void
    {
        $cabangA = Cabang::first();
        $this->assertNotNull($cabangA);

        $uniqueName = 'PemudaTestUnik ' . uniqid();

        $pemuda = Pemuda::create([
            'cabang_id'           => $cabangA->id,
            'registration_number' => 'TEST-' . uniqid(),
            'name'                => $uniqueName,
            'gender'              => 'L',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => '2000-01-01',
            'phone'               => '081234567890',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
        ]);

        // Search with matching cabang and name
        $response = $this->getJson("/pendataan/search-nama?cabang_id={$cabangA->id}&q=" . urlencode($uniqueName));
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $data = $response->json('data');
        $this->assertEquals($pemuda->id, $data[0]['id']);
        $this->assertEquals($uniqueName, $data[0]['name']);
        $this->assertEquals('L', $data[0]['gender']);
        $this->assertEquals('pemuda', $data[0]['source']);
        $this->assertEquals('Data Pemuda', $data[0]['badge']);

        // Search with another cabang_id should NOT find this pemuda
        $cabangB = Cabang::where('id', '!=', $cabangA->id)->first();
        if ($cabangB) {
            $responseB = $this->getJson("/pendataan/search-nama?cabang_id={$cabangB->id}&q=" . urlencode($uniqueName));
            $responseB->assertStatus(200);
            $this->assertEmpty($responseB->json('data'), 'Pemuda should not be found under a different cabang');
        }
    }

    public function test_get_pemuda_data_requires_matching_cabang(): void
    {
        $cabangA = Cabang::first();
        $uniqueName = 'PemudaDetailTest ' . uniqid();

        $pemuda = Pemuda::create([
            'cabang_id'           => $cabangA->id,
            'registration_number' => 'TEST-' . uniqid(),
            'name'                => $uniqueName,
            'gender'              => 'P',
            'marital_status'      => 'belum_menikah',
            'blood_type'          => 'O',
            'birth_place'         => 'Sragen',
            'birth_date'          => '2001-05-15',
            'phone'               => '081987654321',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
        ]);

        // Success when cabang_id matches
        $res = $this->getJson("/pendataan/get-pemuda/{$pemuda->id}?cabang_id={$cabangA->id}");
        $res->assertStatus(200);
        $res->assertJsonPath('status', 'success');
        $res->assertJsonPath('data.name', $uniqueName);
        $res->assertJsonPath('data.gender', 'P');
        $res->assertJsonPath('data.blood_type', 'O');

        // 404 when cabang_id does not match
        $cabangB = Cabang::where('id', '!=', $cabangA->id)->first();
        if ($cabangB) {
            $resFail = $this->getJson("/pendataan/get-pemuda/{$pemuda->id}?cabang_id={$cabangB->id}");
            $resFail->assertStatus(404);
        }
    }

    public function test_submit_new_pemuda_registration(): void
    {
        $cabang = Cabang::first();
        $eduLevel = EducationLevel::first() ?? EducationLevel::create(['name' => 'SMA/SMK', 'level_order' => 1]);
        $jobStatus = JobStatus::first() ?? JobStatus::create(['name' => 'Karyawan Swasta']);
        $district = \App\Models\District::first();
        $village = \App\Models\Village::where('district_id', $district->id)->first();

        $name = 'New Youth ' . uniqid();

        $payload = [
            'cabang_id'          => $cabang->id,
            'name'               => $name,
            'gender'             => 'P', // 'P' doesn't strictly require photo upload
            'marital_status'     => 'belum_menikah',
            'blood_type'         => 'A',
            'birth_place'        => 'Sragen',
            'birth_date'         => '2002-03-20',
            'phone'              => '082112345678',
            'email'              => 'newyouth@example.com',
            'district_id'        => $district->id,
            'village_id'         => $village->id,
            'address_detail'     => 'Jl. Raya Sukowati No. 10',
            'education_level_id' => $eduLevel->id,
            'school_name'        => 'SMAN 1 Sragen',
            'education_status'   => 'lulus',
            'job_status_id'      => $jobStatus->id,
            'organizations'      => ['SATGAS', 'BANKOM'],
        ];

        $response = $this->from('/pendataan')->post('/pendataan/simpan', $payload);
        $response->assertRedirect('/pendataan/sukses');

        $created = Pemuda::where('name', $name)->where('cabang_id', $cabang->id)->first();
        $this->assertNotNull($created, 'New Pemuda record must be created');
        $this->assertEquals('P', $created->gender);
        $this->assertEquals('pending', $created->status_verifikasi);

        // Verify relationships
        $this->assertNotNull($created->alamat);
        $this->assertEquals('Jl. Raya Sukowati No. 10', $created->alamat->address_detail);
        $this->assertNotNull($created->pendidikan);
        $this->assertEquals('SMAN 1 Sragen', $created->pendidikan->school_name);
        $this->assertCount(2, $created->organisasi);
    }

    public function test_submit_update_existing_pemuda(): void
    {
        $cabang = Cabang::first();
        $eduLevel = EducationLevel::first() ?? EducationLevel::create(['name' => 'SMA/SMK', 'level_order' => 1]);
        $jobStatus = JobStatus::first() ?? JobStatus::create(['name' => 'Karyawan Swasta']);
        $district = \App\Models\District::first();
        $village = \App\Models\Village::where('district_id', $district->id)->first();

        // 1. Create an existing pemuda
        $existingPemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'PMD-' . uniqid(),
            'name'                => 'Nama Awal Pemuda',
            'gender'              => 'P',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => '1999-07-07',
            'phone'               => '081234567890',
            'status_verifikasi'   => 'verified',
            'status_data'         => 'active',
        ]);

        $initialId = $existingPemuda->id;

        // 2. Submit form with existing_pemuda_id to update
        $updatedPayload = [
            'existing_pemuda_id' => $initialId,
            'cabang_id'          => $cabang->id,
            'name'               => 'Nama Awal Pemuda (Updated)',
            'gender'             => 'P',
            'marital_status'     => 'sudah_menikah',
            'blood_type'         => 'B',
            'birth_place'        => 'Surakarta',
            'birth_date'         => '1999-07-07',
            'phone'              => '081299998888',
            'email'              => 'updated@example.com',
            'district_id'        => $district->id,
            'village_id'         => $village->id,
            'address_detail'     => 'Alamat Baru RT 01 RW 02',
            'education_level_id' => $eduLevel->id,
            'school_name'        => 'Universitas Sebelas Maret',
            'education_status'   => 'lulus',
            'job_status_id'      => $jobStatus->id,
            'organizations'      => ['IKHROM'],
        ];

        $response = $this->from('/pendataan')->post('/pendataan/simpan', $updatedPayload);
        $response->assertRedirect('/pendataan/sukses');

        // Assert record is updated, not duplicated
        $refreshed = Pemuda::find($initialId);
        $this->assertEquals('Nama Awal Pemuda (Updated)', $refreshed->name);
        $this->assertEquals('sudah_menikah', $refreshed->marital_status);
        $this->assertEquals('081299998888', $refreshed->phone);
        $this->assertEquals('B', $refreshed->blood_type);

        // Alamat updated
        $this->assertEquals('Alamat Baru RT 01 RW 02', $refreshed->alamat->address_detail);
        // Organisasi updated
        $this->assertCount(1, $refreshed->organisasi);
        $this->assertEquals('IKHROM', $refreshed->organisasi->first()->organization_name);

        // Verify session flash
        $this->assertTrue(session('sukses_data')['is_update']);
    }

    public function test_cross_cabang_update_is_isolated(): void
    {
        $cabangA = Cabang::first();
        $cabangB = Cabang::where('id', '!=', $cabangA->id)->first();
        if (!$cabangB) {
            $this->assertTrue(true);
            return;
        }

        $eduLevel = EducationLevel::first() ?? EducationLevel::create(['name' => 'SMA/SMK', 'level_order' => 1]);
        $jobStatus = JobStatus::first() ?? JobStatus::create(['name' => 'Karyawan Swasta']);
        $district = \App\Models\District::first();
        $village = \App\Models\Village::where('district_id', $district->id)->first();

        // Pemuda belongs to Cabang A
        $pemudaA = Pemuda::create([
            'cabang_id'           => $cabangA->id,
            'registration_number' => 'PMD-' . uniqid(),
            'name'                => 'Pemuda Cabang A',
            'gender'              => 'P',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => '1999-01-01',
            'phone'               => '081234567890',
            'status_verifikasi'   => 'verified',
            'status_data'         => 'active',
        ]);

        // Submit form pretending to update Pemuda A, but submitting under Cabang B
        $payload = [
            'existing_pemuda_id' => $pemudaA->id,
            'cabang_id'          => $cabangB->id,
            'name'               => 'Pemuda Cabang A Diubah Ke B',
            'gender'             => 'P',
            'marital_status'     => 'sudah_menikah',
            'blood_type'         => 'B',
            'birth_place'        => 'Sragen',
            'birth_date'         => '1999-01-01',
            'phone'              => '081299990000',
            'district_id'        => $district->id,
            'village_id'         => $village->id,
            'address_detail'     => 'Alamat Tes Isolasi',
            'education_level_id' => $eduLevel->id,
            'school_name'        => 'SMAN 1',
            'education_status'   => 'lulus',
            'job_status_id'      => $jobStatus->id,
        ];

        $response = $this->from('/pendataan')->post('/pendataan/simpan', $payload);
        $response->assertRedirect('/pendataan/sukses');

        // Verify Pemuda A was NOT modified
        $freshA = Pemuda::find($pemudaA->id);
        $this->assertEquals('Pemuda Cabang A', $freshA->name);
        $this->assertEquals($cabangA->id, $freshA->cabang_id);
    }

    public function test_get_warga_data_validates_cabang_parameter(): void
    {
        // 1. Calling without cabang_id returns 400
        $res = $this->getJson('/pendataan/get-warga/fake-uuid-1234');
        $res->assertStatus(400);
        $res->assertJsonPath('status', 'error');

        // 2. Calling with non-existent cabang_id returns 400
        $res2 = $this->getJson('/pendataan/get-warga/fake-uuid-1234?cabang_id=999999');
        $res2->assertStatus(400);
        $res2->assertJsonPath('status', 'error');
    }

    public function test_submit_new_pemuda_with_mta_uuid_is_auto_verified(): void
    {
        $cabang = Cabang::first();
        $eduLevel = EducationLevel::first() ?? EducationLevel::create(['name' => 'SMA/SMK', 'level_order' => 1]);
        $jobStatus = JobStatus::first() ?? JobStatus::create(['name' => 'Karyawan Swasta']);
        $district = \App\Models\District::first();
        $village = \App\Models\Village::where('district_id', $district->id)->first();

        $name = 'Warga MTA Synced ' . uniqid();
        $fakeMtaUuid = 'mta-uuid-' . uniqid();

        $payload = [
            'cabang_id'          => $cabang->id,
            'mta_warga_uuid'     => $fakeMtaUuid,
            'name'               => $name,
            'gender'             => 'P',
            'marital_status'     => 'sudah_menikah',
            'blood_type'         => 'B',
            'birth_place'        => 'Sragen',
            'birth_date'         => '1998-10-10',
            'phone'              => '081233445566',
            'district_id'        => $district->id,
            'village_id'         => $village->id,
            'address_detail'     => 'Jl. Test MTA No. 1',
            'education_level_id' => $eduLevel->id,
            'school_name'        => 'SMAN 2 Sragen',
            'education_status'   => 'lulus',
            'job_status_id'      => $jobStatus->id,
            'organizations'      => ['SATGAS', 'ELFATA'],
        ];

        $response = $this->from('/pendataan')->post('/pendataan/simpan', $payload);
        $response->assertRedirect('/pendataan/sukses');

        $created = Pemuda::where('name', $name)->where('cabang_id', $cabang->id)->first();
        $this->assertNotNull($created);
        $this->assertEquals($fakeMtaUuid, $created->mta_warga_uuid);
        $this->assertEquals('verified', $created->status_verifikasi, 'Data with MTA UUID must be auto-verified');
        $this->assertNotNull($created->mta_synced_at);

        // Verify organizations
        $orgNames = $created->organisasi->pluck('organization_name')->all();
        $this->assertContains('SATGAS', $orgNames);
        $this->assertContains('ELFATA', $orgNames);
    }

    public function test_submit_update_pemuda_retains_verified_when_linked_to_mta(): void
    {
        $cabang = Cabang::first();
        $eduLevel = EducationLevel::first() ?? EducationLevel::create(['name' => 'SMA/SMK', 'level_order' => 1]);
        $jobStatus = JobStatus::first() ?? JobStatus::create(['name' => 'Karyawan Swasta']);
        $district = \App\Models\District::first();
        $village = \App\Models\Village::where('district_id', $district->id)->first();

        $fakeMtaUuid = 'mta-uuid-' . uniqid();

        // Existing pemuda already linked to MTA
        $existing = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'PMD-' . uniqid(),
            'name'                => 'Warga MTA Update Test',
            'gender'              => 'P',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => '1995-05-05',
            'phone'               => '081234000111',
            'status_verifikasi'   => 'verified',
            'status_data'         => 'active',
            'mta_warga_uuid'      => $fakeMtaUuid,
            'mta_synced_at'       => now(),
        ]);

        $payload = [
            'existing_pemuda_id' => $existing->id,
            'cabang_id'          => $cabang->id,
            'name'               => 'Warga MTA Update Test (Name Changed)',
            'gender'             => 'P',
            'marital_status'     => 'sudah_menikah',
            'blood_type'         => 'O',
            'birth_place'        => 'Sragen',
            'birth_date'         => '1995-05-05',
            'phone'              => '081234000222',
            'district_id'        => $district->id,
            'village_id'         => $village->id,
            'address_detail'     => 'Jl. Baru No. 12',
            'education_level_id' => $eduLevel->id,
            'school_name'        => 'SMK 1',
            'education_status'   => 'lulus',
            'job_status_id'      => $jobStatus->id,
            'organizations'      => ['BANKOM'],
        ];

        $response = $this->from('/pendataan')->post('/pendataan/simpan', $payload);
        $response->assertRedirect('/pendataan/sukses');

        $refreshed = Pemuda::find($existing->id);
        $this->assertEquals('verified', $refreshed->status_verifikasi, 'Verified status must be retained because it is linked to MTA');
        $this->assertEquals($fakeMtaUuid, $refreshed->mta_warga_uuid);
    }

    public function test_custom_element_dakwah_can_be_saved_and_displayed_on_form(): void
    {
        $cabang = Cabang::first();
        $eduLevel = EducationLevel::first() ?? EducationLevel::create(['name' => 'SMA/SMK', 'level_order' => 1]);
        $jobStatus = JobStatus::first() ?? JobStatus::create(['name' => 'Karyawan Swasta']);
        $district = \App\Models\District::first();
        $village = \App\Models\Village::where('district_id', $district->id)->first();

        $name = 'Youth Custom Org ' . uniqid();
        $customOrgName = 'TIM LOGISTIK KHUSUS';

        $payload = [
            'cabang_id'           => $cabang->id,
            'name'                => $name,
            'gender'              => 'P',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => '2001-01-01',
            'phone'               => '081234567000',
            'district_id'         => $district->id,
            'village_id'          => $village->id,
            'address_detail'      => 'Jl. Test Custom Org',
            'education_level_id'  => $eduLevel->id,
            'school_name'         => 'SMA 1',
            'education_status'    => 'lulus',
            'job_status_id'       => $jobStatus->id,
            'organizations'       => ['SATGAS'],
            'custom_organization' => $customOrgName,
        ];

        $response = $this->from('/pendataan')->post('/pendataan/simpan', $payload);
        $response->assertRedirect('/pendataan/sukses');

        $created = Pemuda::where('name', $name)->first();
        $this->assertNotNull($created);

        $orgNames = $created->organisasi->pluck('organization_name')->all();
        $this->assertContains('SATGAS', $orgNames);
        $this->assertContains($customOrgName, $orgNames);

        // Verify the new custom element appears when opening /pendataan
        $getForm = $this->get('/pendataan');
        $getForm->assertStatus(200);
        $getForm->assertSee($customOrgName);
    }

    public function test_update_male_pemuda_without_existing_foto_succeeds(): void
    {
        $cabang = Cabang::first();
        $eduLevel = EducationLevel::first() ?? EducationLevel::create(['name' => 'SMA/SMK', 'level_order' => 1]);
        $jobStatus = JobStatus::first() ?? JobStatus::create(['name' => 'Karyawan Swasta']);
        $district = \App\Models\District::first();
        $village = \App\Models\Village::where('district_id', $district->id)->first();

        // Male pemuda without photo (like 727 existing records in DB)
        $existingMale = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'PMD-MALE-' . uniqid(),
            'name'                => 'Pemuda Pria Lama',
            'gender'              => 'L',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => '2000-01-01',
            'phone'               => '081234567890',
            'foto'                => null,
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
        ]);

        $payload = [
            'existing_pemuda_id' => $existingMale->id,
            'cabang_id'          => $cabang->id,
            'name'               => 'Pemuda Pria Lama (Updated)',
            'gender'             => 'L',
            'marital_status'     => 'sudah_menikah',
            'blood_type'         => 'O',
            'birth_place'        => 'Sragen',
            'birth_date'         => '2000-01-01',
            'phone'              => '081234567890',
            'district_id'        => $district->id,
            'village_id'         => $village->id,
            'address_detail'     => 'Alamat Baru Pemuda Pria No 12',
            'education_level_id' => $eduLevel->id,
            'school_name'        => 'SMAN 1 Sragen',
            'education_status'   => 'lulus',
            'job_status_id'      => $jobStatus->id,
            'organizations'      => ['SATGAS'],
        ];

        // Updating WITHOUT uploading a photo MUST NOT loop back or fail
        $response = $this->from('/pendataan')->post('/pendataan/simpan', $payload);
        $response->assertRedirect('/pendataan/sukses');

        $refreshed = Pemuda::find($existingMale->id);
        $this->assertEquals('Pemuda Pria Lama (Updated)', $refreshed->name);
        $this->assertEquals('sudah_menikah', $refreshed->marital_status);
        $this->assertNull($refreshed->foto, 'Foto should remain null if not uploaded during update');
    }

    public function test_new_male_pemuda_without_foto_is_redirected_with_visible_error(): void
    {
        $cabang = Cabang::first();
        $eduLevel = EducationLevel::first() ?? EducationLevel::create(['name' => 'SMA/SMK', 'level_order' => 1]);
        $jobStatus = JobStatus::first() ?? JobStatus::create(['name' => 'Karyawan Swasta']);
        $district = \App\Models\District::first();
        $village = \App\Models\Village::where('district_id', $district->id)->first();

        // New male youth registration WITHOUT photo
        $payload = [
            'cabang_id'          => $cabang->id,
            'name'               => 'Pemuda Baru Pria ' . uniqid(),
            'gender'             => 'L',
            'marital_status'     => 'belum_menikah',
            'birth_place'        => 'Sragen',
            'birth_date'         => '2002-02-02',
            'phone'              => '081234567111',
            'district_id'        => $district->id,
            'village_id'         => $village->id,
            'address_detail'     => 'Alamat Pemuda Baru No 99',
            'education_level_id' => $eduLevel->id,
            'school_name'        => 'SMAN 2 Sragen',
            'education_status'   => 'lulus',
            'job_status_id'      => $jobStatus->id,
        ];

        $response = $this->from('/pendataan')->post('/pendataan/simpan', $payload);
        $response->assertRedirect('/pendataan');
        $response->assertSessionHas('error');
        $response->assertSessionHasErrors(['foto']);

        // Follow redirect to ensure error banner is rendered in the HTML view
        $follow = $this->get('/pendataan');
        $follow->assertStatus(200);
        $follow->assertSee('Pas foto profil wajib diunggah untuk pendaftaran pemuda baru laki-laki.');
    }
}


