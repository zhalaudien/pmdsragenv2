<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class CabangExcelImportExportTest extends TestCase
{
    protected function getSuperadmin(): User
    {
        return User::where('role_id', 1)->first() ?? User::where('username', 'superadmin')->first();
    }

    protected function getNonSuperadmin(): ?User
    {
        return User::where('role_id', '!=', 1)->first();
    }

    public function test_superadmin_can_see_export_and_import_buttons_on_cabang_master_page(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get(route('admin.cabang.index'));
        $response->assertStatus(200);

        // Buttons
        $response->assertSee('Export Excel');
        $response->assertSee('Import Excel');
        $response->assertSee('Tambah Cabang Baru');

        // Modal
        $response->assertSee('modalImportCabang');
        $response->assertSee('Unduh Template Excel (.xlsx)', false);
    }

    public function test_superadmin_can_export_cabang_to_excel(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get(route('admin.cabang.export'));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertStringContainsString('data_master_cabang_', $response->headers->get('content-disposition'));
    }

    public function test_superadmin_can_download_cabang_import_template(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $response = $this->get(route('admin.cabang.template'));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertStringContainsString('template_import_cabang_', $response->headers->get('content-disposition'));
    }

    public function test_superadmin_can_import_cabang_from_excel_file(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $this->assertNotNull($wilayah, 'Data master wilayah harus ada.');

        $uniqueCode = 'TEST' . rand(1000, 9999);
        $uniqueName = 'Cabang Uji Coba ' . rand(1000, 9999);

        // Create in-memory Excel spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Format Import Cabang');

        // Headers
        $headers = [
            'A1' => 'Nama Cabang * (WAJIB)',
            'B1' => 'Wilayah * (WAJIB: Nama / Kode)',
            'C1' => 'Kode Cabang (Opsional)',
            'D1' => 'Nama Pimpinan / Ketua (Opsional)',
            'E1' => 'No. WhatsApp (Opsional)',
            'F1' => 'Alamat Cabang (Opsional)',
            'G1' => 'Link Google Maps (Opsional)',
            'H1' => 'Status Gelombang * (WAJIB: sudah / belum)',
            'I1' => 'Hari Gelombang (Opsional)',
            'J1' => 'Jam Gelombang (Opsional)',
            'K1' => 'Ustadz Pengampu (Opsional)',
            'L1' => 'Deskripsi / Keterangan (Opsional)',
        ];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Data row
        $sheet->setCellValue('A2', $uniqueName);
        $sheet->setCellValue('B2', $wilayah->name);
        $sheet->setCellValue('C2', $uniqueCode);
        $sheet->setCellValue('D2', 'Ust. Test Pimpinan');
        $sheet->setCellValue('E2', '081299998888');
        $sheet->setCellValue('F2', 'Jl. Sukowati Test No. 99');
        $sheet->setCellValue('G2', 'https://maps.example.com');
        $sheet->setCellValue('H2', 'sudah');
        $sheet->setCellValue('I2', 'Ahad Pagi');
        $sheet->setCellValue('J2', '06:00 - 07:30');
        $sheet->setCellValue('K2', 'Ust. Fulan');
        $sheet->setCellValue('L2', 'Cabang pengujian import automated test');

        $tempFile = tempnam(sys_get_temp_dir(), 'cabang_test_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'import_cabang_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->post(route('admin.cabang.import'), [
            'file_excel'      => $uploadedFile,
            'update_existing' => 1,
        ]);

        $response->assertRedirect(route('admin.cabang.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('import_result');

        $this->assertDatabaseHas('cabang', [
            'code'          => $uniqueCode,
            'name'          => $uniqueName,
            'pimpinan_nama' => 'Ust. Test Pimpinan',
            'has_gelombang' => 'sudah',
        ]);

        // Cleanup
        Cabang::where('code', $uniqueCode)->delete();
        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_import_cabang_updates_existing_cabang_when_upsert_enabled(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $uniqueCode = 'UPSERT' . rand(1000, 9999);
        $uniqueName = 'Cabang Awal ' . rand(1000, 9999);

        $existingCabang = Cabang::create([
            'wilayah_id'    => $wilayah->id,
            'code'          => $uniqueCode,
            'name'          => $uniqueName,
            'pimpinan_nama' => 'Pimpinan Lama',
            'no_wa'         => '081111111111',
            'has_gelombang' => 'belum',
        ]);

        // Create spreadsheet with same name/code but updated data
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nama Cabang');
        $sheet->setCellValue('B1', 'Wilayah');
        $sheet->setCellValue('C1', 'Kode Cabang');
        $sheet->setCellValue('D1', 'Nama Pimpinan');
        $sheet->setCellValue('E1', 'No. WA');
        $sheet->setCellValue('H1', 'Status Gelombang');

        $sheet->setCellValue('A2', $uniqueName);
        $sheet->setCellValue('B2', $wilayah->code);
        $sheet->setCellValue('C2', $uniqueCode);
        $sheet->setCellValue('D2', 'Pimpinan Baru Diperbarui');
        $sheet->setCellValue('E2', '089999999999');
        $sheet->setCellValue('H2', 'sudah');

        $tempFile = tempnam(sys_get_temp_dir(), 'cabang_upsert_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'import_upsert_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->post(route('admin.cabang.import'), [
            'file_excel'      => $uploadedFile,
            'update_existing' => 1,
        ]);

        $response->assertRedirect(route('admin.cabang.index'));

        $this->assertDatabaseHas('cabang', [
            'id'            => $existingCabang->id,
            'pimpinan_nama' => 'Pimpinan Baru Diperbarui',
            'no_wa'         => '089999999999',
            'has_gelombang' => 'sudah',
        ]);

        // Cleanup
        $existingCabang->delete();
        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_import_cabang_validation_fails_on_non_excel_file(): void
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

        $response = $this->post(route('admin.cabang.import'), [
            'file_excel' => $uploadedFile,
        ]);

        $response->assertSessionHasErrors('file_excel');

        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_superadmin_can_import_cabang_with_pengurus_pemuda_columns(): void
    {
        $superadmin = $this->getSuperadmin();
        $this->actingAs($superadmin);

        $wilayah = Wilayah::first();
        $this->assertNotNull($wilayah, 'Data master wilayah harus ada.');

        $uniqueCode = 'KSB' . rand(1000, 9999);
        $uniqueName = 'Cabang KSB Test ' . rand(1000, 9999);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Format Import Cabang');

        $headers = [
            'A1' => 'Nama Cabang * (WAJIB)',
            'B1' => 'Wilayah * (WAJIB: Nama / Kode)',
            'C1' => 'Kode Cabang (Opsional)',
            'D1' => 'Nama Pimpinan / Ketua (Opsional)',
            'E1' => 'No. WhatsApp (Opsional)',
            'F1' => 'Alamat Cabang (Opsional)',
            'G1' => 'Link Google Maps (Opsional)',
            'H1' => 'Status Gelombang * (WAJIB: sudah / belum)',
            'I1' => 'Hari Gelombang (Opsional)',
            'J1' => 'Jam Gelombang (Opsional)',
            'K1' => 'Ustadz Pengampu (Opsional)',
            'L1' => 'Ketua Pemuda (Opsional)',
            'M1' => 'Sekretaris Pemuda (Opsional)',
            'N1' => 'Bendahara Pemuda (Opsional)',
            'O1' => 'No. WhatsApp Pemuda (Opsional)',
            'P1' => 'Deskripsi / Keterangan (Opsional)',
        ];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->setCellValue('A2', $uniqueName);
        $sheet->setCellValue('B2', $wilayah->name);
        $sheet->setCellValue('C2', $uniqueCode);
        $sheet->setCellValue('D2', 'Ust. Pimpinan Cabang');
        $sheet->setCellValue('E2', '081211112222');
        $sheet->setCellValue('F2', 'Alamat Lengkap KSB');
        $sheet->setCellValue('G2', 'https://maps.example.com/ksb');
        $sheet->setCellValue('H2', 'sudah');
        $sheet->setCellValue('I2', 'Ahad Pagi');
        $sheet->setCellValue('J2', '06:00 - 07:30');
        $sheet->setCellValue('K2', 'Ust. Pengampu KSB');
        $sheet->setCellValue('L2', 'Rizky Ketua');
        $sheet->setCellValue('M2', 'Adit Sekretaris');
        $sheet->setCellValue('N2', 'Bima Bendahara');
        $sheet->setCellValue('O2', '089876543210');
        $sheet->setCellValue('P2', 'Catatan pengujian KSB import');

        $tempFile = tempnam(sys_get_temp_dir(), 'cabang_ksb_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'import_cabang_ksb_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->post(route('admin.cabang.import'), [
            'file_excel'      => $uploadedFile,
            'update_existing' => 1,
        ]);

        $response->assertRedirect(route('admin.cabang.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cabang', [
            'code'              => $uniqueCode,
            'name'              => $uniqueName,
            'pimpinan_nama'     => 'Ust. Pimpinan Cabang',
            'ketua_pemuda'      => 'Rizky Ketua',
            'sekretaris_pemuda' => 'Adit Sekretaris',
            'bendahara_pemuda'  => 'Bima Bendahara',
            'no_wa_pemuda'      => '089876543210',
        ]);

        // Cleanup
        Cabang::where('code', $uniqueCode)->delete();
        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_non_superadmin_cannot_access_cabang_export_or_import(): void
    {
        $nonSuperadmin = $this->getNonSuperadmin();
        if (!$nonSuperadmin) {
            $this->markTestSkipped('Non-superadmin user not found in database.');
        }

        $this->actingAs($nonSuperadmin);

        // Export route should be 403
        $this->get(route('admin.cabang.export'))->assertStatus(403);

        // Template route should be 403
        $this->get(route('admin.cabang.template'))->assertStatus(403);

        // Import route should be 403
        $this->post(route('admin.cabang.import'))->assertStatus(403);
    }
}
