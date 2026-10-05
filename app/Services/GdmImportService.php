<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\GdmPenugasan;
use App\Models\GuruDaerahMuda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GdmImportService
{
    /**
     * Generate template Excel untuk import data Guru Daerah Muda (GDM)
     */
    public function generateTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // ---------------------------------------------------------
        // SHEET 1: Format Import GDM
        // ---------------------------------------------------------
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

        // Style WAJIB columns (A, B, G)
        foreach (['A1', 'B1', 'G1'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']], // Emerald
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '166534']]],
            ]);
        }

        // Style OPSIONAL columns (C, D, E, F, H, I, J, K, L)
        foreach (['C1', 'D1', 'E1', 'F1', 'H1', 'I1', 'J1', 'K1', 'L1'] as $cell) {
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
                'Ust. Ahmad Fauzi',
                'Masaran 1',
                'Sragen',
                '1996-04-12',
                '081234567890',
                'Dk. Masaran RT 02/01, Ds. Masaran, Kec. Masaran',
                'aktif',
                'Kedawung 1',
                (int) date('Y'),
                'Ahad Pagi',
                '06:00 - 07:30 WIB',
                'Kader aktif alumni Ponpes MTA',
            ],
            [
                'Ust. Muhammad Ridwan',
                'Sragen Kota',
                'Surakarta',
                '1998-11-20',
                '085678901234',
                'Jl. Sukowati No. 45, Sine, Sragen',
                'aktif',
                'Sambungmacan 2',
                (int) date('Y'),
                'Malam Selasa',
                '19:30 - 21:00 WIB',
                'Penugasan kajian pemuda cabang perintis',
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
            $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($r)->setRowHeight(22);
            $r++;
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ---------------------------------------------------------
        // SHEET 2: Referensi Cabang
        // ---------------------------------------------------------
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi Cabang');

        $refSheet->setCellValue('A1', 'DAFTAR REFERENSI CABANG PEMUDA MTA KABUPATEN SRAGEN');
        $refSheet->mergeCells('A1:G1');
        $refSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E293B']],
        ]);

        $refSheet->setCellValue('A2', 'Gunakan Nama Cabang atau Kode Cabang pada kolom Cabang Asal dan Cabang Penugasan di sheet "Format Import GDM".');
        $refSheet->mergeCells('A2:G2');
        $refSheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
        ]);

        $refSheet->setCellValue('A4', 'ID');
        $refSheet->setCellValue('B4', 'Kode Cabang');
        $refSheet->setCellValue('C4', 'Nama Cabang');
        $refSheet->setCellValue('D4', 'Wilayah');
        $refSheet->setCellValue('E4', 'Status Kajian Pemuda');
        $refSheet->setCellValue('F4', 'Hari & Jam Kajian Cabang');
        $refSheet->setCellValue('G4', 'Ustadz Pengampu Saat Ini');

        $refSheet->getStyle('A4:G4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '991B1B']], // Crimson Red
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '7F1D1D']]],
        ]);
        $refSheet->getRowDimension(4)->setRowHeight(25);

        $cabangList = Cabang::with('wilayah')->orderBy('wilayah_id', 'ASC')->orderBy('name', 'ASC')->get();
        $refRow = 5;
        foreach ($cabangList as $c) {
            $statusGel = $c->has_gelombang === 'sudah' ? 'Sudah Ada Kajian' : 'Belum Ada Kajian';
            $jadwal = array_filter([$c->gelombang_hari, $c->gelombang_jam]);
            $jadwalStr = !empty($jadwal) ? implode(' ', $jadwal) : '-';

            $refSheet->setCellValue('A' . $refRow, $c->id);
            $refSheet->setCellValue('B' . $refRow, $c->code ?? '-');
            $refSheet->setCellValue('C' . $refRow, $c->name);
            $refSheet->setCellValue('D' . $refRow, $c->wilayah ? $c->wilayah->name : '-');
            $refSheet->setCellValue('E' . $refRow, $statusGel);
            $refSheet->setCellValue('F' . $refRow, $jadwalStr);
            $refSheet->setCellValue('G' . $refRow, $c->gelombang_ustadz ?? '-');

            $refSheet->getStyle("A{$refRow}:G{$refRow}")->applyFromArray([
                'font' => ['size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
            $refSheet->getStyle("A{$refRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $refSheet->getStyle("B{$refRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $refSheet->getStyle("E{$refRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $refSheet->getRowDimension($refRow)->setRowHeight(20);
            $refRow++;
        }

        foreach (range('A', 'G') as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Return to first sheet active
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Memproses file Excel dan menyimpan data GDM ke database
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
                'errors'         => [['row' => 1, 'nama' => '-', 'reason' => 'Tidak ada data untuk diimpor.']],
            ];
        }

        // Cache cabang lookup: by lower name, by lower code, and by ID
        $cabangs = Cabang::all();
        $cabangLookup = [];
        foreach ($cabangs as $c) {
            $cabangLookup[strtolower(trim($c->name))] = $c;
            if (!empty($c->code)) {
                $cabangLookup[strtolower(trim($c->code))] = $c;
            }
            $cabangLookup[(string) $c->id] = $c;
        }

        $insertedCount = 0;
        $updatedCount  = 0;
        $skippedCount  = 0;
        $processedRows = 0;
        $errors        = [];

        $syncService = app(GdmCabangSyncService::class);

        DB::beginTransaction();

        try {
            for ($row = 2; $row <= $highestRow; $row++) {
                $nama             = trim((string) $sheet->getCell("A{$row}")->getValue());
                $cabangAsalInput  = trim((string) $sheet->getCell("B{$row}")->getValue());
                $tempatLahir      = trim((string) $sheet->getCell("C{$row}")->getValue());
                $tglLahirRaw      = $sheet->getCell("D{$row}")->getValue();
                $noWa             = trim((string) $sheet->getCell("E{$row}")->getValue());
                $alamat           = trim((string) $sheet->getCell("F{$row}")->getValue());
                $statusRaw        = strtolower(trim((string) $sheet->getCell("G{$row}")->getValue()));
                $cabangTugasInput = trim((string) $sheet->getCell("H{$row}")->getValue());
                $tahunTugasRaw    = $sheet->getCell("I{$row}")->getValue();
                $hariKajian       = trim((string) $sheet->getCell("J{$row}")->getValue());
                $jamKajian        = trim((string) $sheet->getCell("K{$row}")->getValue());
                $catatan          = trim((string) $sheet->getCell("L{$row}")->getValue());

                // Skip completely empty rows
                if ($nama === '' && $cabangAsalInput === '' && $tempatLahir === '' && empty($tglLahirRaw) && $noWa === '') {
                    continue;
                }

                $processedRows++;

                // Validation 1: Nama GDM is mandatory
                if ($nama === '') {
                    $errors[] = [
                        'row'    => $row,
                        'nama'   => '(Kosong)',
                        'reason' => 'Nama Lengkap GDM wajib diisi.',
                    ];
                    continue;
                }

                // Validation 2: Cabang Asal is mandatory
                $cabangAsal = null;
                if ($cabangAsalInput !== '') {
                    $cleanKey = strtolower($cabangAsalInput);
                    $cabangAsal = $cabangLookup[$cleanKey] ?? null;
                    if (!$cabangAsal) {
                        $errors[] = [
                            'row'    => $row,
                            'nama'   => $nama,
                            'reason' => "Cabang Asal '{$cabangAsalInput}' tidak ditemukan dalam referensi master cabang.",
                        ];
                        continue;
                    }
                } else {
                    $errors[] = [
                        'row'    => $row,
                        'nama'   => $nama,
                        'reason' => 'Cabang Asal wajib diisi.',
                    ];
                    continue;
                }

                // Parse Tanggal Lahir
                $tanggalLahir = null;
                if (!empty($tglLahirRaw)) {
                    if (is_numeric($tglLahirRaw)) {
                        try {
                            $tanggalLahir = ExcelDate::excelToDateTimeObject($tglLahirRaw)->format('Y-m-d');
                        } catch (\Throwable) {
                            $tanggalLahir = null;
                        }
                    } else {
                        $ts = strtotime((string) $tglLahirRaw);
                        if ($ts !== false) {
                            $tanggalLahir = date('Y-m-d', $ts);
                        }
                    }

                    if (!$tanggalLahir) {
                        $errors[] = [
                            'row'    => $row,
                            'nama'   => $nama,
                            'reason' => "Format tanggal lahir '{$tglLahirRaw}' tidak valid (gunakan format YYYY-MM-DD).",
                        ];
                        continue;
                    }
                }

                // Status Kader
                $status = in_array($statusRaw, ['aktif', 'nonaktif'], true) ? $statusRaw : 'aktif';

                // Check existing GDM by Nama (case-insensitive) and Cabang Asal
                $existing = GuruDaerahMuda::whereRaw('LOWER(nama) = ?', [strtolower($nama)])
                    ->where('cabang_id', $cabangAsal->id)
                    ->first();

                if (!$existing) {
                    // Cek jika ada GDM dengan nama sama persis walau cabang belum terisi
                    $existing = GuruDaerahMuda::whereRaw('LOWER(nama) = ?', [strtolower($nama)])
                        ->whereNull('cabang_id')
                        ->first();
                }

                $gdmPayload = [
                    'nama'          => $nama,
                    'tempat_lahir'  => $tempatLahir !== '' ? $tempatLahir : null,
                    'tanggal_lahir' => $tanggalLahir,
                    'cabang_id'     => $cabangAsal->id,
                    'alamat'        => $alamat !== '' ? $alamat : null,
                    'no_wa'         => $noWa !== '' ? $noWa : null,
                    'status'        => $status,
                    'catatan'       => $catatan !== '' ? $catatan : null,
                ];

                $gdm = null;
                if ($existing) {
                    if ($updateExisting) {
                        if (!$dryRun) {
                            // Pertahankan sumber_data, pemuda_id, mta_warga_uuid yang sudah ada
                            $existing->update($gdmPayload);
                        }
                        $gdm = $existing;
                        $updatedCount++;
                    } else {
                        $gdm = $existing;
                        $skippedCount++;
                    }
                } else {
                    if (!$dryRun) {
                        $gdmPayload['sumber_data'] = 'manual';
                        $gdm = GuruDaerahMuda::create($gdmPayload);
                    }
                    $insertedCount++;
                }

                // Penugasan Awal (jika diisi)
                if ($cabangTugasInput !== '') {
                    $cleanTugasKey = strtolower($cabangTugasInput);
                    $cabangTugas = $cabangLookup[$cleanTugasKey] ?? null;

                    if (!$cabangTugas) {
                        $errors[] = [
                            'row'    => $row,
                            'nama'   => $nama,
                            'reason' => "Cabang Penugasan '{$cabangTugasInput}' tidak ditemukan dalam referensi master cabang.",
                        ];
                        continue;
                    }

                    $tahunTugas = (int) date('Y');
                    if (!empty($tahunTugasRaw) && is_numeric($tahunTugasRaw)) {
                        $parsedYear = (int) $tahunTugasRaw;
                        if ($parsedYear >= 2000 && $parsedYear <= 2100) {
                            $tahunTugas = $parsedYear;
                        }
                    }

                    $finalHari = $hariKajian !== '' ? $hariKajian : ($cabangTugas->gelombang_hari ?: null);
                    $finalJam  = $jamKajian !== '' ? $jamKajian : ($cabangTugas->gelombang_jam ?: null);

                    if (!$dryRun && $gdm) {
                        $existingPenugasan = GdmPenugasan::where('gdm_id', $gdm->id)
                            ->where('cabang_id', $cabangTugas->id)
                            ->where('tahun', $tahunTugas)
                            ->first();

                        if ($existingPenugasan) {
                            $existingPenugasan->update([
                                'hari_kajian' => $finalHari,
                                'jam_kajian'  => $finalJam,
                                'status'      => 'aktif',
                            ]);
                            $syncService->syncPenugasanToCabang($existingPenugasan);
                        } else {
                            $newPenugasan = GdmPenugasan::create([
                                'gdm_id'      => $gdm->id,
                                'tahun'       => $tahunTugas,
                                'cabang_id'   => $cabangTugas->id,
                                'hari_kajian' => $finalHari,
                                'jam_kajian'  => $finalJam,
                                'status'      => 'aktif',
                                'keterangan'  => 'Diimpor dari Excel',
                            ]);
                            $syncService->syncPenugasanToCabang($newPenugasan);
                        }
                    }
                }
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }

            $success = count($errors) < $processedRows || $processedRows === 0;
            $msg = "Import selesai! ";
            if ($insertedCount > 0) $msg .= "{$insertedCount} kader GDM baru ditambahkan. ";
            if ($updatedCount > 0)  $msg .= "{$updatedCount} kader GDM diperbarui. ";
            if ($skippedCount > 0)  $msg .= "{$skippedCount} kader GDM dilewati. ";
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
            Log::error('GDM Import Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return [
                'success'        => false,
                'message'        => 'Terjadi kesalahan sistem saat memproses file Excel: ' . $e->getMessage(),
                'total_rows'     => $processedRows,
                'inserted_count' => 0,
                'updated_count'  => 0,
                'skipped_count'  => 0,
                'error_count'    => 1,
                'errors'         => [['row' => '-', 'nama' => '-', 'reason' => $e->getMessage()]],
            ];
        }
    }
}
