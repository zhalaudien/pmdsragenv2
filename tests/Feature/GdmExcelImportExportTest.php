<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\GdmPenugasan;
use App\Models\GuruDaerahMuda;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wilayah;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class GdmExcelImportExportTest extends TestCase
{
    protected function getSuperadmin(): User
    {
        return User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
    }

    protected function getKoordinatorGdm(): User
    {
        $user = User::where('role_id', 7)->first() ?? User::where('username', 'koordinator_gdm')->first();

        if (!$user) {
            $role = UserRole::firstOrCreate(
                ['id' => 7],
                [
                    'name'        => 'koordinator_gdm',
                    'description' => 'Koordinator Guru Daerah Muda (GDM)',
                ]
            );

            $user = User::create([
                'name'       => 'Koordinator GDM Test',
                'email'      => 'koordinator.gdm.test@pmdsragen.org',
                'username'   => 'koordinator_gdm_test',
                'password'   => bcrypt('password123'),
                'role_id'    => $role->id,
                'is_active'  => true,
            ]);
        }

        return $user;
    }

    protected function getUnauthorizedUser(): ?User
    {
        return User::whereNotIn('role_id', [1, 7])->first();
    }

    public function test_superadmin_and_koordinator_gdm_can_see_export_and_import_buttons_on_gdm_page(): void
    {
        // 1. Test Superadmin
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get(route('admin.gdm.index'));
        $response->assertStatus(200);
        $response->assertSee('Export Excel');
        $response->assertSee('Import Excel');
        $response->assertSee('modalImportGdm');
        $response->assertSee('Unduh Template Excel (.xlsx)', false);

        // 2. Test Koordinator GDM
        $koordinator = $this->getKoordinatorGdm();
        $this->actingAs($koordinator);

        $responseKoord = $this->get(route('admin.gdm.index'));
        $responseKoord->assertStatus(200);
        $responseKoord->assertSee('Export Excel');
        $responseKoord->assertSee('Import Excel');
        $responseKoord->assertSee('modalImportGdm');
        $responseKoord->assertSee('Unduh Template Excel (.xlsx)', false);
    }

    public function test_superadmin_can_export_gdm_to_excel(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get(route('admin.gdm.export'));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertStringContainsString('data_guru_daerah_muda_', $response->headers->get('content-disposition'));
    }

    public function test_superadmin_can_export_gdm_with_filters(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabang = Cabang::first();

        $response = $this->get(route('admin.gdm.export', [
            'status'         => 'aktif',
            'cabang_asal_id' => $cabang?->id,
            'search'         => 'Ahmad',
        ]));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }

    public function test_superadmin_can_download_gdm_import_template(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get(route('admin.gdm.template'));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertStringContainsString('template_import_gdm_', $response->headers->get('content-disposition'));
    }

    public function test_superadmin_can_import_gdm_from_excel_file_with_penugasan(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabangAsal = Cabang::first();
        $cabangTugas = Cabang::skip(1)->first() ?? $cabangAsal;
        $this->assertNotNull($cabangAsal, 'Data master cabang harus ada.');

        $uniqueName = 'Ust. GDM Automated ' . rand(1000, 9999);
        $currentYear = (int) date('Y');

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Format Import GDM');

        $headers = [
            'A1' => 'Nama Lengkap GDM * (WAJIB)',
            'B1' => 'Cabang Asal * (WAJIB: Nama / Kode)',
            'C1' => 'Tempat Lahir (Opsional)',
            'D1' => 'Tanggal Lahir (Opsional: YYYY-MM-DD)',
            'E1' => 'No. WhatsApp (Opsional)',
            'F1' => 'Alamat Domisili (Opsional)',
            'G1' => 'Status Kader * (WAJIB: aktif / nonaktif)',
            'H1' => 'Cabang Penugasan Kajian (Opsional: Nama / Kode)',
            'I1' => 'Tahun Penugasan (Opsional: cth. 2026)',
            'J1' => 'Hari Kajian (Opsional)',
            'K1' => 'Jam Kajian (Opsional)',
            'L1' => 'Catatan / Keterangan (Opsional)',
        ];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->setCellValue('A2', $uniqueName);
        $sheet->setCellValue('B2', $cabangAsal->name);
        $sheet->setCellValue('C2', 'Sragen Kota');
        $sheet->setCellValue('D2', '1997-08-17');
        $sheet->setCellValue('E2', '081233445566');
        $sheet->setCellValue('F2', 'Dk. Kauman RT 01, Sragen');
        $sheet->setCellValue('G2', 'aktif');
        $sheet->setCellValue('H2', $cabangTugas->name);
        $sheet->setCellValue('I2', $currentYear);
        $sheet->setCellValue('J2', 'Ahad Pagi');
        $sheet->setCellValue('K2', '06:00 - 07:30 WIB');
        $sheet->setCellValue('L2', 'Kader hasil import automated test');

        $tempFile = tempnam(sys_get_temp_dir(), 'gdm_test_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'import_gdm_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->post(route('admin.gdm.import'), [
            'file_excel'      => $uploadedFile,
            'update_existing' => 1,
        ]);

        $response->assertRedirect(route('admin.gdm.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('import_result');

        $this->assertDatabaseHas('guru_daerah_muda', [
            'nama'         => $uniqueName,
            'cabang_id'    => $cabangAsal->id,
            'tempat_lahir' => 'Sragen Kota',
            'status'       => 'aktif',
        ]);

        $gdm = GuruDaerahMuda::where('nama', $uniqueName)->first();
        $this->assertNotNull($gdm);

        $this->assertDatabaseHas('gdm_penugasan', [
            'gdm_id'    => $gdm->id,
            'cabang_id' => $cabangTugas->id,
            'tahun'     => $currentYear,
            'status'    => 'aktif',
        ]);

        // Cleanup
        $gdm->delete();
        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_import_gdm_updates_existing_gdm_when_upsert_enabled(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $cabang = Cabang::first();
        $uniqueName = 'Ust. Upsert Test ' . rand(1000, 9999);

        $existingGdm = GuruDaerahMuda::create([
            'nama'         => $uniqueName,
            'cabang_id'    => $cabang->id,
            'tempat_lahir' => 'Tempat Lama',
            'no_wa'        => '081111111111',
            'status'       => 'aktif',
            'sumber_data'  => 'manual',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nama Lengkap GDM');
        $sheet->setCellValue('B1', 'Cabang Asal');
        $sheet->setCellValue('C1', 'Tempat Lahir');
        $sheet->setCellValue('E1', 'No. WhatsApp');
        $sheet->setCellValue('G1', 'Status');

        $sheet->setCellValue('A2', $uniqueName);
        $sheet->setCellValue('B2', $cabang->name);
        $sheet->setCellValue('C2', 'Tempat Lahir Baru Diperbarui');
        $sheet->setCellValue('E2', '089999999999');
        $sheet->setCellValue('G2', 'aktif');

        $tempFile = tempnam(sys_get_temp_dir(), 'gdm_upsert_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'import_gdm_upsert_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->post(route('admin.gdm.import'), [
            'file_excel'      => $uploadedFile,
            'update_existing' => 1,
        ]);

        $response->assertRedirect(route('admin.gdm.index'));

        $this->assertDatabaseHas('guru_daerah_muda', [
            'id'           => $existingGdm->id,
            'tempat_lahir' => 'Tempat Lahir Baru Diperbarui',
            'no_wa'        => '089999999999',
        ]);

        // Cleanup
        $existingGdm->delete();
        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_import_gdm_validation_fails_on_non_excel_file(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $tempFile = tempnam(sys_get_temp_dir(), 'not_excel_') . '.txt';
        file_put_contents($tempFile, 'bukan file excel');

        $uploadedFile = new UploadedFile(
            $tempFile,
            'test.txt',
            'text/plain',
            null,
            true
        );

        $response = $this->post(route('admin.gdm.import'), [
            'file_excel' => $uploadedFile,
        ]);

        $response->assertSessionHasErrors('file_excel');

        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_unauthorized_roles_cannot_access_gdm_export_or_import(): void
    {
        $unauthorizedUser = $this->getUnauthorizedUser();
        if (!$unauthorizedUser) {
            $this->markTestSkipped('Unauthorized user not found in database.');
        }

        $this->actingAs($unauthorizedUser);

        // Export route should be 403
        $this->get(route('admin.gdm.export'))->assertStatus(403);

        // Template route should be 403
        $this->get(route('admin.gdm.template'))->assertStatus(403);

        // Import route should be 403
        $this->post(route('admin.gdm.import'))->assertStatus(403);
    }
}
