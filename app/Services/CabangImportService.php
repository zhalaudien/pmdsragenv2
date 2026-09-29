<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\Wilayah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CabangImportService
{
    /**
     * Generate template Excel untuk import data cabang
     */
    public function generateTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // ---------------------------------------------------------
        // SHEET 1: Format Import Cabang
        // ---------------------------------------------------------
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
            'L1' => 'Deskripsi / Keterangan (Opsional)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Style WAJIB columns (A, B, H)
        foreach (['A1', 'B1', 'H1'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']], // Emerald
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '166534']]],
            ]);
        }

        // Style OPSIONAL columns (C, D, E, F, G, I, J, K, L)
        foreach (['C1', 'D1', 'E1', 'F1', 'G1', 'I1', 'J1', 'K1', 'L1'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']], // Slate 700
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E293B']]],
            ]);
        }
        $sheet->getRowDimension(1)->setRowHeight(36);

        // Contoh Data Baris 2 & 3
        $sampleData = [
            [
                'Masaran 1',
                'Wilayah 1',
                'MSR1',
                'Ust. H. Sutrisno',
                '081234567890',
                'Dk. Masaran RT 02/01, Ds. Masaran, Kec. Masaran',
                'https://maps.app.goo.gl/sample1',
                'sudah',
                'Ahad Pagi',
                '06:00 - 07:30 WIB',
                'Ust. Ahmad Fauzi',
                'Pengajian rutin pemuda setiap Ahad ba\'da Shubuh',
            ],
            [
                'Sragen Kota',
                'Wilayah 1',
                'SRG1',
                'Ust. Budi Santoso',
                '085678901234',
                'Jl. Raya Sukowati No. 12, Sine, Sragen',
                'https://maps.app.goo.gl/sample2',
                'belum',
                '',
                '',
                '',
                'Rencana perintisan gelombang pemuda awal bulan depan',
            ],
        ];

        $r = 2;
        foreach ($sampleData as $rowData) {
            $colIdx = 'A';
            foreach ($rowData as $val) {
                $sheet->setCellValue($colIdx . $r, $val);
                $colIdx++;
            }

            $sheet->getStyle("A{$r}:L{$r}")->applyFromArray([
                'font' => ['size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($r)->setRowHeight(22);
            $r++;
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ---------------------------------------------------------
        // SHEET 2: Referensi Wilayah
        // ---------------------------------------------------------
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi Wilayah');

        $refSheet->setCellValue('A1', 'DAFTAR REFERENSI WILAYAH KABUPATEN SRAGEN');
        $refSheet->mergeCells('A1:C1');
        $refSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E293B']],
        ]);

        $refSheet->setCellValue('A2', 'Gunakan Nama Wilayah atau Kode Wilayah pada kolom Wilayah di sheet "Format Import Cabang".');
        $refSheet->mergeCells('A2:C2');
        $refSheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
        ]);

        $refSheet->setCellValue('A4', 'ID');
        $refSheet->setCellValue('B4', 'Kode Wilayah');
        $refSheet->setCellValue('C4', 'Nama Wilayah');

        $refSheet->getStyle('A4:C4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0284C7']], // Sky 600
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0369A1']]],
        ]);
        $refSheet->getRowDimension(4)->setRowHeight(25);

        $wilayahList = Wilayah::orderBy('id', 'ASC')->get();
        $refRow = 5;
        foreach ($wilayahList as $w) {
            $refSheet->setCellValue('A' . $refRow, $w->id);
            $refSheet->setCellValue('B' . $refRow, $w->code);
            $refSheet->setCellValue('C' . $refRow, $w->name);

            $refSheet->getStyle("A{$refRow}:C{$refRow}")->applyFromArray([
                'font' => ['size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
            $refSheet->getStyle("A{$refRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $refSheet->getStyle("B{$refRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $refSheet->getRowDimension($refRow)->setRowHeight(20);
            $refRow++;
        }

        foreach (['A', 'B', 'C'] as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Return to first sheet active
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Memproses file Excel dan menyimpan data ke database
     *
     * @param string $filePath
     * @param array $options ['update_existing' => bool, 'dry_run' => bool]
     * @return array
     */
    public function importExcel(string $filePath, array $options = []): array
    {
        $updateExisting = (bool) ($options['update_existing'] ?? true);
        $dryRun         = (bool) ($options['dry_run'] ?? false);

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        if ($highestRow < 2) {
            return [
                'success'        => false,
                'message'        => 'File Excel kosong atau tidak memiliki baris data (hanya header).',
                'total_rows'     => 0,
                'inserted_count' => 0,
                'updated_count'  => 0,
                'skipped_count'  => 0,
                'error_count'    => 1,
                'errors'         => [['row' => 1, 'cabang' => '-', 'reason' => 'Tidak ada data untuk diimpor.']],
            ];
        }

        // Cache wilayah list
        $wilayahAll = Wilayah::all();
        $wilayahLookup = [];
        foreach ($wilayahAll as $w) {
            $wilayahLookup[strtolower(trim($w->name))] = $w->id;
            $wilayahLookup[strtolower(trim($w->code))] = $w->id;
            // Support numbers like "1", "2" matching ID
            $wilayahLookup[(string) $w->id] = $w->id;
        }

        $insertedCount = 0;
        $updatedCount  = 0;
        $skippedCount  = 0;
        $processedRows = 0;
        $errors        = [];

        DB::beginTransaction();

        try {
            for ($row = 2; $row <= $highestRow; $row++) {
                $name            = trim((string) $sheet->getCell("A{$row}")->getValue());
                $wilayahInput    = trim((string) $sheet->getCell("B{$row}")->getValue());
                $code            = trim((string) $sheet->getCell("C{$row}")->getValue());
                $pimpinanNama    = trim((string) $sheet->getCell("D{$row}")->getValue());
                $noWa            = trim((string) $sheet->getCell("E{$row}")->getValue());
                $alamat          = trim((string) $sheet->getCell("F{$row}")->getValue());
                $mapsUrl         = trim((string) $sheet->getCell("G{$row}")->getValue());
                $hasGelombangRaw = strtolower(trim((string) $sheet->getCell("H{$row}")->getValue()));
                $gelombangHari   = trim((string) $sheet->getCell("I{$row}")->getValue());
                $gelombangJam    = trim((string) $sheet->getCell("J{$row}")->getValue());
                $gelombangUstadz = trim((string) $sheet->getCell("K{$row}")->getValue());
                $description     = trim((string) $sheet->getCell("L{$row}")->getValue());

                // Skip completely empty rows
                if ($name === '' && $wilayahInput === '' && $code === '') {
                    continue;
                }

                $processedRows++;

                // Validation 1: Nama Cabang is mandatory
                if ($name === '') {
                    $errors[] = [
                        'row'    => $row,
                        'cabang' => '(Kosong)',
                        'reason' => 'Nama Cabang wajib diisi.',
                    ];
                    continue;
                }

                // Validation 2: Wilayah is mandatory and must match
                $cleanWilayahKey = strtolower($wilayahInput);
                // Also strip "wilayah " prefix if present e.g. "wilayah 1" -> "1"
                $cleanNumberKey = preg_replace('/^wilayah\s*/i', '', $cleanWilayahKey);

                $wilayahId = $wilayahLookup[$cleanWilayahKey] 
                    ?? $wilayahLookup[$cleanNumberKey] 
                    ?? null;

                if (!$wilayahId) {
                    $errors[] = [
                        'row'    => $row,
                        'cabang' => $name,
                        'reason' => "Wilayah '{$wilayahInput}' tidak ditemukan dalam referensi master wilayah.",
                    ];
                    continue;
                }

                // Normalisasi Status Gelombang
                $hasGelombang = 'belum';
                if (in_array($hasGelombangRaw, ['sudah', 'ya', 'yes', '1', 'true'], true)) {
                    $hasGelombang = 'sudah';
                }

                $cabangCode = $code !== '' ? strtoupper($code) : null;

                // Cek apakah cabang sudah ada (berdasarkan Nama dalam Wilayah yang sama, atau berdasarkan Kode Cabang unik)
                $existing = Cabang::where('wilayah_id', $wilayahId)
                    ->where(function ($q) use ($name, $cabangCode) {
                        $q->whereRaw('LOWER(name) = ?', [strtolower($name)]);
                        if ($cabangCode !== null) {
                            $q->orWhere('code', $cabangCode);
                        }
                    })
                    ->first();

                // Juga cek jika ada cabang dengan kode yang sama di wilayah manapun
                if (!$existing && $cabangCode !== null) {
                    $existing = Cabang::where('code', $cabangCode)->first();
                }

                $dataPayload = [
                    'wilayah_id'       => $wilayahId,
                    'code'             => $cabangCode,
                    'name'             => $name,
                    'description'      => $description !== '' ? $description : null,
                    'alamat'           => $alamat !== '' ? $alamat : null,
                    'maps_url'         => $mapsUrl !== '' ? $mapsUrl : null,
                    'pimpinan_nama'    => $pimpinanNama !== '' ? $pimpinanNama : null,
                    'no_wa'            => $noWa !== '' ? $noWa : null,
                    'has_gelombang'    => $hasGelombang,
                    'gelombang_hari'   => ($hasGelombang === 'sudah' && $gelombangHari !== '') ? $gelombangHari : null,
                    'gelombang_jam'    => ($hasGelombang === 'sudah' && $gelombangJam !== '') ? $gelombangJam : null,
                    'gelombang_ustadz' => ($hasGelombang === 'sudah' && $gelombangUstadz !== '') ? $gelombangUstadz : null,
                ];

                if ($existing) {
                    if ($updateExisting) {
                        if (!$dryRun) {
                            $existing->update($dataPayload);
                        }
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    if (!$dryRun) {
                        Cabang::create($dataPayload);
                    }
                    $insertedCount++;
                }
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }

            $success = count($errors) < $processedRows || $processedRows === 0;
            $msg = "Import selesai! ";
            if ($insertedCount > 0) $msg .= "{$insertedCount} cabang baru ditambahkan. ";
            if ($updatedCount > 0)  $msg .= "{$updatedCount} cabang diperbarui. ";
            if ($skippedCount > 0)  $msg .= "{$skippedCount} cabang dilewati. ";
            if (count($errors) > 0) $msg .= count($errors) . " baris data memiliki kesalahan.";

            return [
                'success'        => $success,
                'message'        => trim($msg),
                'total_rows'     => $processedRows,
                'inserted_count' => $insertedCount,
                'updated_count'  => $updatedCount,
                'skipped_count'  => $skippedCount,
                'error_count'    => count($errors),
                'errors'         => $errors,
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Cabang Import Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return [
                'success'        => false,
                'message'        => 'Terjadi kesalahan sistem saat memproses file Excel: ' . $e->getMessage(),
                'total_rows'     => $processedRows,
                'inserted_count' => 0,
                'updated_count'  => 0,
                'skipped_count'  => 0,
                'error_count'    => 1,
                'errors'         => [['row' => '-', 'cabang' => '-', 'reason' => $e->getMessage()]],
            ];
        }
    }
}
