<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Pemuda;
use App\Models\Wilayah;
use App\Models\HomepageSetting;
use Tests\TestCase;

class GuruDaerahMonitoringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Pastikan setting default tersedia
        HomepageSetting::setSetting('kode_akses_guru_daerah', 'GURUPMD', 'guru_daerah', 'text', 'Kode Akses Guru Daerah');
    }

    public function test_unauthenticated_user_sees_auth_gate(): void
    {
        $response = $this->get(route('guru-daerah.index'));

        $response->assertStatus(200);
        $response->assertViewIs('guru_daerah.auth');
        $response->assertSee('Portal Guru Daerah');
        $response->assertSee('Kode Akses Guru Daerah');
        $response->assertSee('Pilih Cabang Yang Dipantau');
    }

    public function test_invalid_access_code_is_rejected(): void
    {
        $cabang = Cabang::first();
        $this->assertNotNull($cabang, 'Harus ada data cabang');

        $response = $this->post(route('guru-daerah.verify'), [
            'access_code' => 'KODESALAH123',
            'cabang_id'   => $cabang->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertFalse(session('guru_daerah_authenticated', false));
    }

    public function test_valid_access_code_authenticates_and_redirects(): void
    {
        $cabang = Cabang::first();
        $this->assertNotNull($cabang, 'Harus ada data cabang');

        $response = $this->post(route('guru-daerah.verify'), [
            'access_code' => 'GURUPMD',
            'cabang_id'   => $cabang->id,
        ]);

        $response->assertRedirect(route('guru-daerah.index'));
        $response->assertSessionHas('guru_daerah_authenticated', true);
        $response->assertSessionHas('guru_daerah_cabang_id', $cabang->id);
    }

    public function test_case_insensitive_access_code_is_accepted(): void
    {
        $cabang = Cabang::first();

        $response = $this->post(route('guru-daerah.verify'), [
            'access_code' => 'gurupmd',
            'cabang_id'   => $cabang->id,
        ]);

        $response->assertRedirect(route('guru-daerah.index'));
        $response->assertSessionHas('guru_daerah_authenticated', true);
    }

    public function test_pmdsragen_alternative_code_is_accepted(): void
    {
        $cabang = Cabang::first();

        $response = $this->post(route('guru-daerah.verify'), [
            'access_code' => 'pmdsragen',
            'cabang_id'   => $cabang->id,
        ]);

        $response->assertRedirect(route('guru-daerah.index'));
        $response->assertSessionHas('guru_daerah_authenticated', true);
    }

    public function test_authenticated_user_sees_monitoring_dashboard(): void
    {
        // Cari cabang yang memiliki data pemuda
        $cabangWithPemuda = Pemuda::select('cabang_id')->groupBy('cabang_id')->first();
        $cabangId = $cabangWithPemuda ? $cabangWithPemuda->cabang_id : Cabang::first()->id;
        $cabang = Cabang::find($cabangId);

        $response = $this->withSession([
            'guru_daerah_authenticated' => true,
            'guru_daerah_cabang_id'     => $cabangId,
        ])->get(route('guru-daerah.index'));

        $response->assertStatus(200);
        $response->assertViewIs('guru_daerah.monitoring');
        $response->assertSee('Cabang ' . $cabang->name);
        $response->assertSee('Mode Pemantauan Guru Daerah');
        $response->assertSee('Total Terdata');
        $response->assertSee('Sudah Komplit');
        $response->assertSee('Belum Komplit');
    }

    public function test_pemuda_completeness_evaluation_calculates_properly(): void
    {
        $pemuda = Pemuda::with(['alamat', 'pendidikan', 'pekerjaan', 'organisasi', 'skills', 'interests'])->first();
        $this->assertNotNull($pemuda, 'Harus ada data pemuda');

        $comp = Pemuda::evaluateCompleteness($pemuda);

        $this->assertIsArray($comp);
        $this->assertArrayHasKey('percentage', $comp);
        $this->assertArrayHasKey('is_complete', $comp);
        $this->assertArrayHasKey('status_label', $comp);
        $this->assertArrayHasKey('aspects', $comp);
        $this->assertArrayHasKey('missing_items', $comp);

        $this->assertGreaterThanOrEqual(0, $comp['percentage']);
        $this->assertLessThanOrEqual(100, $comp['percentage']);
        $this->assertIsBool($comp['is_complete']);
    }

    public function test_switch_cabang_updates_active_session(): void
    {
        $cabangs = Cabang::take(2)->get();
        $this->assertCount(2, $cabangs, 'Minimal butuh 2 cabang untuk test switcher');

        $response = $this->withSession([
            'guru_daerah_authenticated' => true,
            'guru_daerah_cabang_id'     => $cabangs[0]->id,
        ])->post(route('guru-daerah.switch-cabang'), [
            'cabang_id' => $cabangs[1]->id,
        ]);

        $response->assertRedirect(route('guru-daerah.index'));
        $this->assertEquals($cabangs[1]->id, session('guru_daerah_cabang_id'));
    }

    public function test_detail_pemuda_json_endpoint(): void
    {
        $pemuda = Pemuda::first();
        $this->assertNotNull($pemuda);

        $response = $this->withSession([
            'guru_daerah_authenticated' => true,
            'guru_daerah_cabang_id'     => $pemuda->cabang_id,
        ])->get(route('guru-daerah.detail', $pemuda->id));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data'   => [
                'id'                  => $pemuda->id,
                'name'                => $pemuda->name,
                'registration_number' => $pemuda->registration_number,
            ],
        ]);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'name',
                'registration_number',
                'gender',
                'completeness' => [
                    'percentage',
                    'is_complete',
                    'status_label',
                    'aspects',
                    'missing_items',
                ],
            ],
        ]);
    }

    public function test_logout_clears_guru_daerah_session(): void
    {
        $response = $this->withSession([
            'guru_daerah_authenticated' => true,
            'guru_daerah_cabang_id'     => 1,
        ])->post(route('guru-daerah.logout'));

        $response->assertRedirect(route('guru-daerah.index'));
        $this->assertNull(session('guru_daerah_authenticated'));
        $this->assertNull(session('guru_daerah_cabang_id'));
    }
}
