<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\User;
use App\Services\MtaApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WargaMtaSecurityTest extends TestCase
{
    public function test_mta_api_service_locks_perwakilan_to_sragen(): void
    {
        $service = new MtaApiService();
        $sragenUuid = '3246792b-f0a7-48ca-95fa-379e3bee777d';

        $this->assertSame($sragenUuid, $service->getSragenUuid());

        Http::fake([
            'api.mta.or.id/api/v1/warga*' => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'uuid'       => 'sragen-uuid-1',
                        'nama'       => 'Fulan Sragen',
                        'kelamin'    => 'L',
                        'perwakilan' => 'Sragen',
                        'cabang'     => 'Masaran 1',
                    ],
                ],
                'meta'    => [
                    'page'     => 1,
                    'per_page' => 20,
                    'total'    => 1,
                ],
            ], 200),
        ]);

        $result = $service->getWargaList(['cabang' => 'some-cabang']);
        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) use ($sragenUuid) {
            return str_contains($request->url(), '/warga') &&
                   isset($request->data()['perwakilan']) &&
                   $request->data()['perwakilan'] === $sragenUuid;
        });
    }

    public function test_warga_mta_index_filters_out_non_sragen_data(): void
    {
        $superadmin = User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
        $this->assertNotNull($superadmin);

        $sragenUuid = '3246792b-f0a7-48ca-95fa-379e3bee777d';

        Http::fake([
            'api.mta.or.id/api/v1/warga*' => Http::response([
                'success' => true,
                'data'    => [
                    [
                        'uuid'            => 'sragen-1',
                        'nama'            => 'Warga Asli Sragen',
                        'kelamin'         => 'L',
                        'perwakilan'      => 'Sragen',
                        'perwakilan_uuid' => $sragenUuid,
                        'cabang'          => 'Sragen Kota',
                    ],
                    [
                        'uuid'            => 'ngawi-1',
                        'nama'            => 'Warga Luar Kota',
                        'kelamin'         => 'L',
                        'perwakilan'      => 'Ngawi',
                        'perwakilan_uuid' => 'foreign-perwakilan-uuid',
                        'cabang'          => 'Ngawi 1',
                    ],
                ],
                'meta'    => [
                    'page'     => 1,
                    'per_page' => 20,
                    'total'    => 2,
                ],
            ], 200),
            'api.mta.or.id/api/v1/perwakilan/' . $sragenUuid => Http::response([
                'success' => true,
                'data'    => [
                    'uuid'   => $sragenUuid,
                    'nama'   => 'Perwakilan Sragen',
                    'cabang' => [
                        ['uuid' => 'cab-1', 'nama' => 'Sragen Kota', 'kode' => '86.1'],
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($superadmin);

        $response = $this->get('/admin/warga-mta');
        $response->assertStatus(200);
        $response->assertSee('Warga Asli Sragen');
        $response->assertDontSee('Warga Luar Kota');
    }

    public function test_warga_mta_detail_blocks_non_sragen_citizen(): void
    {
        $superadmin = User::where('role_id', 1)->first();
        $this->assertNotNull($superadmin);

        Http::fake([
            'api.mta.or.id/api/v1/warga/foreign-uuid' => Http::response([
                'success' => true,
                'data'    => [
                    'uuid'            => 'foreign-uuid',
                    'nama'            => 'Orang Non Sragen',
                    'kelamin'         => 'L',
                    'perwakilan'      => 'Surabaya',
                    'perwakilan_uuid' => 'foreign-perwakilan-uuid',
                    'kabupaten'       => 'Kota Surabaya',
                ],
            ], 200),
        ]);

        $this->actingAs($superadmin);

        $response = $this->get('/admin/warga-mta/detail/foreign-uuid');
        $response->assertRedirect('/admin/warga-mta');
        $response->assertSessionHas('error', 'Hanya data warga dari Perwakilan Sragen yang diizinkan untuk diakses.');
    }

    public function test_import_warga_blocks_non_sragen_citizen(): void
    {
        $superadmin = User::where('role_id', 1)->first();
        $this->assertNotNull($superadmin);

        Http::fake([
            'api.mta.or.id/api/v1/warga/foreign-uuid' => Http::response([
                'success' => true,
                'data'    => [
                    'uuid'            => 'foreign-uuid',
                    'nama'            => 'Orang Non Sragen',
                    'kelamin'         => 'L',
                    'perwakilan'      => 'Semarang',
                    'perwakilan_uuid' => 'foreign-perwakilan-uuid',
                    'kabupaten'       => 'Kota Semarang',
                ],
            ], 200),
        ]);

        $this->actingAs($superadmin);

        $cabang = Cabang::first();
        $this->assertNotNull($cabang);

        $response = $this->post('/admin/warga-mta/import', [
            'warga_uuid' => 'foreign-uuid',
            'cabang_id'  => $cabang->id,
        ]);

        $response->assertSessionHas('error', 'Hanya data warga dari Perwakilan Sragen yang dapat diimpor ke sistem ini.');
    }

    public function test_import_warga_auto_resolves_cabang_from_mta_pusat(): void
    {
        $superadmin = User::where('role_id', 1)->first();
        $this->assertNotNull($superadmin);

        $cabang = Cabang::first();
        $this->assertNotNull($cabang);

        $sragenUuid = '3246792b-f0a7-48ca-95fa-379e3bee777d';

        Http::fake([
            'api.mta.or.id/api/v1/warga/auto-cbg-uuid' => Http::response([
                'success' => true,
                'data'    => [
                    'uuid'            => 'auto-cbg-uuid',
                    'nama'            => 'Pemuda Auto Cabang Test',
                    'kelamin'         => 'L',
                    'perwakilan'      => 'Sragen',
                    'perwakilan_uuid' => $sragenUuid,
                    'cabang'          => $cabang->name,
                    'cabang_uuid'     => $cabang->mta_uuid,
                    'lahir'           => '2001-05-10',
                    'nohp'            => '081234567890',
                    'status'          => 'Warga',
                ],
            ], 200),
        ]);

        $this->actingAs($superadmin);

        // POST without cabang_id!
        $response = $this->post('/admin/warga-mta/import', [
            'warga_uuid' => 'auto-cbg-uuid',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $pemuda = \App\Models\Pemuda::where('mta_warga_uuid', 'auto-cbg-uuid')->first();
        $this->assertNotNull($pemuda, 'Pemuda should be created automatically');
        $this->assertSame((int) $cabang->id, (int) $pemuda->cabang_id, 'Cabang ID should match the branch from MTA Pusat');
        $this->assertSame('verified', $pemuda->status_verifikasi);
    }
}
