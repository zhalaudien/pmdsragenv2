<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\District;
use App\Models\EducationLevel;
use App\Models\JobStatus;
use App\Models\Pemuda;
use App\Models\Village;
use App\Services\MtaApiService;
use App\Services\MtaSyncService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MtaSyncDataPullTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sync_warga_to_pemuda_pulls_all_matching_database_fields(): void
    {
        $cabang = Cabang::first();
        $this->assertNotNull($cabang);

        $district = District::where('regency_id', 3314)->first();
        $village = Village::where('district_id', $district->id)->first();

        $wargaUuid = 'test-uuid-' . uniqid();
        $wargaData = [
            'uuid'            => $wargaUuid,
            'nama'            => 'Ahmad Syakir Al-Mutaqin',
            'kelamin'         => 'L',
            'nohp'            => '081234567890',
            'status'          => 'Warga',
            'menikah'         => 'Sudah Menikah',
            'goldar'          => 'O',
            'tempat_lahir'    => 'Sragen',
            'lahir'           => '1998-05-12',
            'email'           => 'syakir@example.com',
            'alamat'          => 'Dk. Karanganyar RT 03 RW 04',
            'alamat_rtrw'     => 'RT 03 / RW 04',
            'desa'            => $village->name,
            'kecamatan'       => $district->name,
            'kabupaten'       => 'Kab. Sragen',
            'provinsi'        => 'Jawa Tengah',
            'pendidikan'      => 'Sarjana S1 Teknik Informatika',
            'sekolah'         => 'Universitas Sebelas Maret',
            'pekerjaan'       => 'Wirausaha Toko Komputer',
            'foto'            => 'https://api.mta.or.id/uploads/foto-syakir.jpg',
            'ayah'            => 'Bapak Syakir',
            'ayah_uuid'       => 'ayah-uuid-1234',
            'ibu'             => 'Ibu Syakir',
            'ibu_uuid'        => 'ibu-uuid-5678',
            'cabang'          => $cabang->name,
            'cabang_uuid'     => $cabang->mta_uuid,
            'perwakilan'      => 'Sragen',
            'perwakilan_uuid' => '3246792b-f0a7-48ca-95fa-379e3bee777d',
        ];

        $syncService = new MtaSyncService();
        $res = $syncService->syncWargaToPemuda($wargaData, $cabang->id);

        $this->assertTrue($res['success']);
        $this->assertSame('created', $res['action']);

        $pemuda = Pemuda::with(['alamat', 'pendidikan', 'pekerjaan'])->find($res['pemuda_id']);
        $this->assertNotNull($pemuda);

        // Verify Pemuda Core Fields
        $this->assertSame('Ahmad Syakir Al-Mutaqin', $pemuda->name);
        $this->assertSame('L', $pemuda->gender);
        $this->assertSame('1998-05-12', $pemuda->birth_date->format('Y-m-d'));
        $this->assertSame('Sragen', $pemuda->birth_place);
        $this->assertSame('081234567890', $pemuda->phone);
        $this->assertSame('syakir@example.com', $pemuda->email);
        $this->assertSame('sudah_menikah', $pemuda->marital_status);
        $this->assertSame('O', $pemuda->blood_type);
        $this->assertSame('verified', $pemuda->status_verifikasi);
        $this->assertSame('active', $pemuda->status_data);
        $this->assertSame($wargaUuid, $pemuda->mta_warga_uuid);
        $this->assertSame('Warga', $pemuda->mta_status_warga);
        $this->assertSame('ayah-uuid-1234', $pemuda->mta_ayah_uuid);
        $this->assertSame('ibu-uuid-5678', $pemuda->mta_ibu_uuid);
        $this->assertSame('https://api.mta.or.id/uploads/foto-syakir.jpg', $pemuda->mta_foto_url);
        $this->assertNotNull($pemuda->mta_synced_at);

        // Verify Alamat Fields
        $this->assertNotNull($pemuda->alamat);
        $this->assertSame((int) $district->id, (int) $pemuda->alamat->district_id);
        $this->assertSame((int) $village->id, (int) $pemuda->alamat->village_id);
        $this->assertSame('03', $pemuda->alamat->rt);
        $this->assertSame('04', $pemuda->alamat->rw);
        $this->assertStringContainsString('Dk. Karanganyar', $pemuda->alamat->address_detail);

        // Verify Pendidikan Fields (S1 => 5)
        $this->assertNotNull($pemuda->pendidikan);
        $this->assertSame(5, (int) $pemuda->pendidikan->education_level_id);
        $this->assertSame('Universitas Sebelas Maret', $pemuda->pendidikan->school_name);
        $this->assertSame('lulus', $pemuda->pendidikan->education_status);

        // Verify Pekerjaan Fields (Wirausaha => 5)
        $this->assertNotNull($pemuda->pekerjaan);
        $this->assertSame(5, (int) $pemuda->pekerjaan->job_status_id);
        $this->assertSame('Wirausaha Toko Komputer', $pemuda->pekerjaan->job_title);
    }

    public function test_sync_single_pemuda_pulls_all_matching_database_fields(): void
    {
        $cabang = Cabang::first();
        $this->assertNotNull($cabang);

        $district = District::where('regency_id', 3314)->first();
        $village = Village::where('district_id', $district->id)->first();

        $wargaUuid = 'sync-single-' . uniqid();

        // Create a basic pending pemuda
        $pemuda = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'REG-' . uniqid(),
            'name'                => 'Budi Santoso',
            'gender'              => 'L',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => '1995-10-20',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
            'mta_warga_uuid'      => $wargaUuid,
        ]);

        $fullWargaDetail = [
            'uuid'            => $wargaUuid,
            'nama'            => 'Budi Santoso',
            'kelamin'         => 'L',
            'nohp'            => '085712349999',
            'status'          => 'Warga',
            'menikah'         => 'Menikah',
            'goldar'          => 'B',
            'tempat_lahir'    => 'Sragen',
            'lahir'           => '1995-10-20',
            'email'           => 'budisantoso@mta.or.id',
            'alamat'          => 'Jl. Pahlawan RT 01 RW 02',
            'alamat_rtrw'     => '01/02',
            'desa'            => $village->name,
            'kecamatan'       => $district->name,
            'kabupaten'       => 'Sragen',
            'provinsi'        => 'Jawa Tengah',
            'pendidikan'      => 'SMA Negeri 1 Sragen',
            'sekolah'         => 'SMA Negeri 1 Sragen',
            'pekerjaan'       => 'Pegawai Negeri Sipil',
            'foto'            => 'https://api.mta.or.id/uploads/foto-budi.jpg',
            'ayah'            => 'Santoso',
            'ayah_uuid'       => 'ayah-budi-999',
            'ibu'             => 'Siti',
            'ibu_uuid'        => 'ibu-budi-888',
        ];

        Http::fake([
            'api.mta.or.id/api/v1/warga/' . $wargaUuid => Http::response([
                'success' => true,
                'data'    => $fullWargaDetail,
            ], 200),
        ]);

        $apiService = new MtaApiService();
        $syncService = new MtaSyncService($apiService);

        $res = $syncService->syncSinglePemuda($pemuda->id);
        $this->assertTrue($res['success']);
        $this->assertTrue($res['matched']);

        $refreshed = Pemuda::with(['alamat', 'pendidikan', 'pekerjaan'])->find($pemuda->id);

        // Core fields
        $this->assertSame('verified', $refreshed->status_verifikasi);
        $this->assertSame('sudah_menikah', $refreshed->marital_status);
        $this->assertSame('B', $refreshed->blood_type);
        $this->assertSame('085712349999', $refreshed->phone);
        $this->assertSame('budisantoso@mta.or.id', $refreshed->email);
        $this->assertSame('ayah-budi-999', $refreshed->mta_ayah_uuid);
        $this->assertSame('ibu-budi-888', $refreshed->mta_ibu_uuid);
        $this->assertSame('https://api.mta.or.id/uploads/foto-budi.jpg', $refreshed->mta_foto_url);

        // Alamat
        $this->assertNotNull($refreshed->alamat);
        $this->assertSame((int) $district->id, (int) $refreshed->alamat->district_id);
        $this->assertSame((int) $village->id, (int) $refreshed->alamat->village_id);
        $this->assertSame('01', $refreshed->alamat->rt);
        $this->assertSame('02', $refreshed->alamat->rw);

        // Pendidikan (SMA => 3)
        $this->assertNotNull($refreshed->pendidikan);
        $this->assertSame(3, (int) $refreshed->pendidikan->education_level_id);
        $this->assertSame('SMA Negeri 1 Sragen', $refreshed->pendidikan->school_name);

        // Pekerjaan (PNS => 4)
        $this->assertNotNull($refreshed->pekerjaan);
        $this->assertSame(4, (int) $refreshed->pekerjaan->job_status_id);
        $this->assertSame('Pegawai Negeri Sipil', $refreshed->pekerjaan->job_title);
    }

    public function test_get_warga_data_endpoint_returns_all_pemuda_fields(): void
    {
        $cabang = Cabang::first();
        $district = District::where('regency_id', 3314)->first();
        $village = Village::where('district_id', $district->id)->first();

        $uuid = 'get-warga-uuid-' . uniqid();

        Http::fake([
            'api.mta.or.id/api/v1/warga/' . $uuid => Http::response([
                'success' => true,
                'data'    => [
                    'uuid'         => $uuid,
                    'nama'         => 'Dewi Permata',
                    'kelamin'      => 'P',
                    'nohp'         => '082211335577',
                    'lahir'        => '2000-08-15',
                    'tempat_lahir' => 'Sragen',
                    'goldar'       => 'AB',
                    'menikah'      => 'Belum Menikah',
                    'email'        => 'dewi@example.com',
                    'alamat'       => 'Dukuh Asri RT 02 RW 01',
                    'alamat_rtrw'  => 'RT 02 / RW 01',
                    'desa'         => $village->name,
                    'kecamatan'    => $district->name,
                    'kabupaten'    => 'Sragen',
                    'provinsi'     => 'Jawa Tengah',
                    'pendidikan'   => 'Diploma D3 Akuntansi',
                    'sekolah'      => 'Politeknik Negeri',
                    'pekerjaan'    => 'Karyawan Swasta Keuangan',
                    'foto'         => 'https://api.mta.or.id/uploads/foto-dewi.jpg',
                    'ayah_uuid'    => 'ayah-dewi-1',
                    'ibu_uuid'     => 'ibu-dewi-2',
                    'cabang'       => $cabang->name,
                    'cabang_uuid'  => $cabang->mta_uuid,
                ],
            ], 200),
        ]);

        $response = $this->getJson("/pendataan/get-warga/{$uuid}?cabang_id={$cabang->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $data = $response->json('data');

        $this->assertSame($uuid, $data['mta_warga_uuid']);
        $this->assertSame('ayah-dewi-1', $data['mta_ayah_uuid']);
        $this->assertSame('ibu-dewi-2', $data['mta_ibu_uuid']);
        $this->assertSame('https://api.mta.or.id/uploads/foto-dewi.jpg', $data['mta_foto_url']);
        $this->assertSame('Dewi Permata', $data['name']);
        $this->assertSame('P', $data['gender']);
        $this->assertSame('2000-08-15', $data['birth_date']);
        $this->assertSame('Sragen', $data['birth_place']);
        $this->assertSame('082211335577', $data['phone']);
        $this->assertSame('dewi@example.com', $data['email']);
        $this->assertSame('belum_menikah', $data['marital_status']);
        $this->assertSame('AB', $data['blood_type']);

        // Alamat
        $this->assertSame((int) $district->id, (int) $data['alamat']['district_id']);
        $this->assertSame((int) $village->id, (int) $data['alamat']['village_id']);
        $this->assertSame('02', $data['alamat']['rt']);
        $this->assertSame('01', $data['alamat']['rw']);

        // Pendidikan (D3 => 4)
        $this->assertSame(4, (int) $data['pendidikan']['education_level_id']);
        $this->assertSame('Politeknik Negeri', $data['pendidikan']['school_name']);

        // Pekerjaan (Karyawan Swasta => 3)
        $this->assertSame(3, (int) $data['pekerjaan']['job_status_id']);
        $this->assertSame('Karyawan Swasta Keuangan', $data['pekerjaan']['job_title']);
    }

    public function test_sync_warga_to_pemuda_updates_existing_pemuda_and_relations(): void
    {
        $cabang = Cabang::first();
        $district = District::where('regency_id', 3314)->first();
        $village = Village::where('district_id', $district->id)->first();

        $wargaUuid = 'update-existing-' . uniqid();

        // Create existing pemuda
        $existing = Pemuda::create([
            'cabang_id'           => $cabang->id,
            'registration_number' => 'REG-' . uniqid(),
            'name'                => 'Siti Aminah',
            'gender'              => 'P',
            'marital_status'      => 'belum_menikah',
            'birth_place'         => 'Sragen',
            'birth_date'          => '1997-04-10',
            'phone'               => '081111111111',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
            'mta_warga_uuid'      => $wargaUuid,
        ]);

        $wargaData = [
            'uuid'            => $wargaUuid,
            'nama'            => 'Siti Aminah',
            'kelamin'         => 'P',
            'nohp'            => '082222222222',
            'status'          => 'Warga',
            'menikah'         => 'Sudah Menikah',
            'goldar'          => 'A',
            'tempat_lahir'    => 'Sragen',
            'lahir'           => '1997-04-10',
            'email'           => 'sitiaminah@mta.or.id',
            'alamat'          => 'Desa Pilang RT 04 RW 02',
            'alamat_rtrw'     => 'RT 04 / RW 02',
            'desa'            => $village->name,
            'kecamatan'       => $district->name,
            'kabupaten'       => 'Sragen',
            'provinsi'        => 'Jawa Tengah',
            'pendidikan'      => 'Sarjana S1 Pendidikan',
            'sekolah'         => 'Universitas Terbuka',
            'pekerjaan'       => 'Guru Swasta',
            'foto'            => 'https://api.mta.or.id/uploads/foto-siti.jpg',
            'ayah_uuid'       => 'ayah-siti-uuid',
            'ibu_uuid'        => 'ibu-siti-uuid',
            'cabang'          => $cabang->name,
            'cabang_uuid'     => $cabang->mta_uuid,
        ];

        $syncService = new MtaSyncService();
        $res = $syncService->syncWargaToPemuda($wargaData, $cabang->id);

        $this->assertTrue($res['success']);
        $this->assertSame('updated', $res['action']);
        $this->assertSame($existing->id, $res['pemuda_id']);

        $refreshed = Pemuda::with(['alamat', 'pendidikan', 'pekerjaan'])->find($existing->id);
        $this->assertSame('verified', $refreshed->status_verifikasi);
        $this->assertSame('sudah_menikah', $refreshed->marital_status);
        $this->assertSame('A', $refreshed->blood_type);
        $this->assertSame('082222222222', $refreshed->phone);
        $this->assertSame('sitiaminah@mta.or.id', $refreshed->email);
        $this->assertSame('ayah-siti-uuid', $refreshed->mta_ayah_uuid);
        $this->assertSame('ibu-siti-uuid', $refreshed->mta_ibu_uuid);
        $this->assertSame('https://api.mta.or.id/uploads/foto-siti.jpg', $refreshed->mta_foto_url);

        // Alamat updated
        $this->assertNotNull($refreshed->alamat);
        $this->assertSame('04', $refreshed->alamat->rt);
        $this->assertSame('02', $refreshed->alamat->rw);

        // Pendidikan updated
        $this->assertNotNull($refreshed->pendidikan);
        $this->assertSame(5, (int) $refreshed->pendidikan->education_level_id);
        $this->assertSame('Universitas Terbuka', $refreshed->pendidikan->school_name);

        // Pekerjaan updated
        $this->assertNotNull($refreshed->pekerjaan);
        $this->assertSame('Guru Swasta', $refreshed->pekerjaan->job_title);
    }

    public function test_submit_pendataan_with_mta_data_saves_all_mta_fields(): void
    {
        $cabang = Cabang::first();
        $eduLevel = EducationLevel::first();
        $jobStatus = JobStatus::first();
        $district = District::first();
        $village = Village::where('district_id', $district->id)->first();

        $mtaWargaUuid = 'warga-mta-' . uniqid();
        $mtaAyahUuid  = 'ayah-mta-' . uniqid();
        $mtaIbuUuid   = 'ibu-mta-' . uniqid();
        $mtaFotoUrl   = 'https://api.mta.or.id/uploads/foto-online.jpg';

        $payload = [
            'cabang_id'          => $cabang->id,
            'mta_warga_uuid'     => $mtaWargaUuid,
            'mta_ayah_uuid'      => $mtaAyahUuid,
            'mta_ibu_uuid'       => $mtaIbuUuid,
            'mta_foto_url'       => $mtaFotoUrl,
            'name'               => 'Pemuda Online Sync',
            'gender'             => 'P',
            'marital_status'     => 'belum_menikah',
            'blood_type'         => 'B',
            'birth_place'        => 'Sragen',
            'birth_date'         => '2001-09-09',
            'phone'              => '081298765432',
            'email'              => 'online@mta.or.id',
            'district_id'        => $district->id,
            'village_id'         => $village->id,
            'address_detail'     => 'Jl. Pahlawan No. 45',
            'education_level_id' => $eduLevel->id,
            'school_name'        => 'Universitas Terbuka',
            'education_status'   => 'lulus',
            'job_status_id'      => $jobStatus->id,
        ];

        $res = $this->from('/pendataan')->post('/pendataan/simpan', $payload);
        $res->assertRedirect('/pendataan/sukses');

        $created = Pemuda::where('mta_warga_uuid', $mtaWargaUuid)->first();
        $this->assertNotNull($created);
        $this->assertSame('verified', $created->status_verifikasi);
        $this->assertSame($mtaAyahUuid, $created->mta_ayah_uuid);
        $this->assertSame($mtaIbuUuid, $created->mta_ibu_uuid);
        $this->assertSame($mtaFotoUrl, $created->mta_foto_url);
        $this->assertNotNull($created->mta_synced_at);
    }
}
