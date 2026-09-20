<?php

namespace App\Services;

use App\Models\Pemuda;
use App\Models\Alamat;
use App\Models\Pendidikan;
use App\Models\Pekerjaan;
use App\Models\Organisasi;
use App\Models\PemudaSkill;
use App\Models\PemudaInterest;
use App\Models\Wilayah;
use App\Models\Cabang;
use App\Models\District;
use App\Models\Village;
use App\Models\EducationLevel;
use App\Models\JobStatus;
use App\Models\Skill;
use App\Models\Interest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PemudaImportService
{
    public function generateTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // SHEET 1: Format Import Pemuda
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Format Import Pemuda');

        $headers = [
            'A1'  => 'Nama Lengkap * (WAJIB)',
            'B1'  => 'Cabang * (WAJIB: Nama / Kode)',
            'C1'  => 'Jenis Kelamin * (WAJIB: L/P)',
            'D1'  => 'Status Pernikahan * (WAJIB: belum_menikah/sudah_menikah/janda/duda)',
            'E1'  => 'Tanggal Lahir * (WAJIB: YYYY-MM-DD)',
            'F1'  => 'Tempat Lahir (Opsional)',
            'G1'  => 'No. Telepon / WA (Opsional)',
            'H1'  => 'Email (Opsional)',
            'I1'  => 'Golongan Darah (Opsional: A/B/AB/O/tidak_tahu)',
            'J1'  => 'Kecamatan (Opsional)',
            'K1'  => 'Desa / Kelurahan (Opsional)',
            'L1'  => 'Dusun / Dukuh (Opsional)',
            'M1'  => 'RT (Opsional)',
            'N1'  => 'RW (Opsional)',
            'O1'  => 'Alamat Detail (Opsional)',
            'P1'  => 'Jenjang Pendidikan (Opsional: SMA/S1/dsb)',
            'Q1'  => 'Nama Sekolah / Kampus (Opsional)',
            'R1'  => 'Jurusan (Opsional)',
            'S1'  => 'Status Pendidikan (Opsional: lulus/sedang_sekolah/putus_sekolah)',
            'T1'  => 'Tahun Lulus (Opsional)',
            'U1'  => 'Status Pekerjaan (Opsional: Karyawan/Wirausaha/dsb)',
            'V1'  => 'Profesi / Jabatan (Opsional)',
            'W1'  => 'Nama Perusahaan / Tempat Usaha (Opsional)',
            'X1'  => 'Bidang Usaha (Opsional)',
            'Y1'  => 'Element Dakwah (Opsional)',
            'Z1'  => 'Keahlian (Opsional: Pisahkan Koma)',
            'AA1' => 'Minat (Opsional: Pisahkan Koma)',
            'AB1' => 'Status Verifikasi (Opsional: verified/pending)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header Styling 1: Wajib (A1:E1)
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '146C43']]],
        ]);

        // Header Styling 2: Opsional (F1:AB1)
        $sheet->getStyle('F1:AB1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '495057']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '6C757D']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(40);

        // Contoh Data Baris 2 & 3
        $sampleData = [
            [
                'Muhammad Yusuf', 'Sragen 1', 'L', 'belum_menikah', '2001-05-14',
                'Sragen', '081234567890', 'yusuf@example.com', 'O', 'Sragen', 'Sine',
                'Dukuh Kebon', '01', '03', 'Jl. Sukowati No. 45', 'S1', 'Universitas Sebelas Maret',
                'Informatika', 'lulus', '2023', 'Karyawan Swasta', 'Software Engineer',
                'PT Digital Inovasi', 'Teknologi Informasi', 'Satgas, Bankom',
                'Pemrograman Web, Desain Grafis', 'Teknologi Informasi, Keagamaan', 'verified'
            ],
            [
                'Aisyah Rahmawati', 'Masaran 1', 'P', 'sudah_menikah', '2002-11-20',
                'Sragen', '082198765432', 'aisyah@example.com', 'A', 'Masaran', 'Masaran',
                'Dukuh Rejo', '02', '01', 'RT 02 RW 01 Masaran', 'SMA', 'SMK Negeri 1 Sragen',
                'Tata Boga', 'lulus', '2020', 'Wirausaha', 'Owner Bakery',
                'Rahma Bakery', 'Kuliner & Makanan', 'Tim Ikhrom',
                'Memasak / Kuliner, Akuntansi', 'Wirausaha / Bisnis', 'pending'
            ]
        ];

        $rIdx = 2;
        foreach ($sampleData as $row) {
            $colLetter = 'A';
            foreach ($row as $val) {
                $sheet->setCellValueExplicit($colLetter . $rIdx, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $colLetter++;
            }
            $rIdx++;
        }

        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);

        // SHEET 2: Referensi
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi & Petunjuk');

        $refHeaders = [
            'A1' => 'Daftar Cabang',
            'B1' => 'Kode Cabang',
            'C1' => 'Wilayah',
            'E1' => 'Kecamatan (Sragen)',
            'G1' => 'Jenjang Pendidikan',
            'I1' => 'Status Pekerjaan',
            'K1' => 'Daftar Keahlian',
            'M1' => 'Daftar Minat',
        ];

        foreach ($refHeaders as $cell => $title) {
            $refSheet->setCellValue($cell, $title);
            $refSheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('1E3A8A');
        }

        // Fill Cabang
        $cabangList = Cabang::with('wilayah')->orderBy('name', 'ASC')->get();
        $cRow = 2;
        foreach ($cabangList as $c) {
            $refSheet->setCellValue("A{$cRow}", $c->name);
            $refSheet->setCellValue("B{$cRow}", $c->code ?: '-');
            $refSheet->setCellValue("C{$cRow}", $c->wilayah->name ?? '-');
            $cRow++;
        }

        // Fill Kecamatan
        $districts = District::where('regency_id', 3314)->orderBy('name', 'ASC')->get();
        $dRow = 2;
        foreach ($districts as $d) {
            $refSheet->setCellValue("E{$dRow}", $d->name);
            $dRow++;
        }

        // Fill Education Levels
        $eduLevels = EducationLevel::orderBy('id', 'ASC')->get();
        $eRow = 2;
        foreach ($eduLevels as $e) {
            $refSheet->setCellValue("G{$eRow}", $e->name);
            $eRow++;
        }

        // Fill Job Statuses
        $jobs = JobStatus::orderBy('id', 'ASC')->get();
        $jRow = 2;
        foreach ($jobs as $j) {
            $refSheet->setCellValue("I{$jRow}", $j->name);
            $jRow++;
        }

        // Fill Skills
        $skills = Skill::orderBy('name', 'ASC')->get();
        $sRow = 2;
        foreach ($skills as $s) {
            $refSheet->setCellValue("K{$sRow}", $s->name);
            $sRow++;
        }

        // Fill Interests
        $interests = Interest::orderBy('name', 'ASC')->get();
        $iRow = 2;
        foreach ($interests as $i) {
            $refSheet->setCellValue("M{$iRow}", $i->name);
            $iRow++;
        }

        foreach (['A', 'B', 'C', 'E', 'G', 'I', 'K', 'M'] as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function importExcel(string $filePath, array $options = []): array
    {
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'File import tidak ditemukan di server.'];
        }

        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();
            $highestRow  = $sheet->getHighestRow();
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Gagal membaca format file Excel: ' . $e->getMessage()];
        }

        if ($highestRow < 2) {
            return ['success' => false, 'message' => 'File Excel kosong atau tidak memiliki baris data.'];
        }

        $dryRun         = (bool) ($options['dry_run'] ?? false);
        $updateExisting = (bool) ($options['update_existing'] ?? true);
        $defaultCabangId= !empty($options['default_cabang_id']) ? (int) $options['default_cabang_id'] : null;
        $userId         = !empty($options['user_id']) ? (int) $options['user_id'] : auth()->id();

        $rowsToProcess = [];
        $errors        = [];
        $successCount  = 0;
        $updatedCount  = 0;
        $skippedCount  = 0;

        // Preload caches
        $cabangs = Cabang::all();
        $cabangMap = [];
        foreach ($cabangs as $c) {
            $cabangMap[strtolower(trim($c->name))] = $c->id;
            if (!empty($c->code)) {
                $cabangMap[strtolower(trim($c->code))] = $c->id;
            }
        }

        $districts = District::where('regency_id', 3314)->get();
        $districtMap = [];
        foreach ($districts as $d) {
            $districtMap[strtolower(trim($d->name))] = $d->id;
        }

        $eduLevels = EducationLevel::all();
        $eduMap = [];
        foreach ($eduLevels as $el) {
            $eduMap[strtolower(trim($el->name))] = $el->id;
        }

        $jobStatuses = JobStatus::all();
        $jobMap = [];
        foreach ($jobStatuses as $js) {
            $jobMap[strtolower(trim($js->name))] = $js->id;
        }

        $skills = Skill::all();
        $skillMap = [];
        foreach ($skills as $sk) {
            $skillMap[strtolower(trim($sk->name))] = $sk->id;
        }

        $interests = Interest::all();
        $interestMap = [];
        foreach ($interests as $in) {
            $interestMap[strtolower(trim($in->name))] = $in->id;
        }

        for ($row = 2; $row <= $highestRow; $row++) {
            $name         = trim((string) $sheet->getCell("A{$row}")->getValue());
            $cabangRaw    = trim((string) $sheet->getCell("B{$row}")->getValue());
            $genderRaw    = strtoupper(trim((string) $sheet->getCell("C{$row}")->getValue()));
            $maritalRaw   = strtolower(trim((string) $sheet->getCell("D{$row}")->getValue()));
            $birthDateRaw = $sheet->getCell("E{$row}")->getValue();

            // Skip empty rows
            if (empty($name) && empty($cabangRaw) && empty($birthDateRaw)) {
                continue;
            }

            // Validations
            $rowErrors = [];

            if (empty($name) || mb_strlen($name) < 3) {
                $rowErrors[] = 'Nama lengkap minimal 3 karakter.';
            }

            $cabangId = null;
            if (!empty($cabangRaw) && isset($cabangMap[strtolower($cabangRaw)])) {
                $cabangId = $cabangMap[strtolower($cabangRaw)];
            } elseif ($defaultCabangId) {
                $cabangId = $defaultCabangId;
            } else {
                $rowErrors[] = "Cabang '{$cabangRaw}' tidak ditemukan dalam referensi cabang.";
            }

            $gender = in_array($genderRaw, ['L', 'P'], true) ? $genderRaw : null;
            if (!$gender) {
                $rowErrors[] = "Jenis kelamin harus 'L' atau 'P'.";
            }

            $maritalStatus = in_array($maritalRaw, ['belum_menikah', 'sudah_menikah', 'janda', 'duda'], true)
                ? $maritalRaw
                : 'belum_menikah';

            // Parse Birth Date
            $birthDate = null;
            if (!empty($birthDateRaw)) {
                if (is_numeric($birthDateRaw)) {
                    try {
                        $birthDate = ExcelDate::excelToDateTimeObject($birthDateRaw)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $birthDate = null;
                    }
                } else {
                    $ts = strtotime((string) $birthDateRaw);
                    if ($ts !== false) {
                        $birthDate = date('Y-m-d', $ts);
                    }
                }
            }

            if (!$birthDate) {
                $rowErrors[] = "Format tanggal lahir tidak valid (gunakan YYYY-MM-DD).";
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row'    => $row,
                    'name'   => $name ?: '-',
                    'errors' => $rowErrors,
                ];
                continue;
            }

            // Read Optional Fields
            $birthPlace = trim((string) $sheet->getCell("F{$row}")->getValue()) ?: 'Sragen';
            $phone      = trim((string) $sheet->getCell("G{$row}")->getValue());
            $email      = trim((string) $sheet->getCell("H{$row}")->getValue()) ?: null;
            $bloodType  = strtoupper(trim((string) $sheet->getCell("I{$row}")->getValue())) ?: null;
            if (!in_array($bloodType, ['A', 'B', 'AB', 'O'], true)) {
                $bloodType = null;
            }

            $districtRaw   = trim((string) $sheet->getCell("J{$row}")->getValue());
            $villageRaw    = trim((string) $sheet->getCell("K{$row}")->getValue());
            $dusun         = trim((string) $sheet->getCell("L{$row}")->getValue()) ?: null;
            $rt            = trim((string) $sheet->getCell("M{$row}")->getValue()) ?: null;
            $rw            = trim((string) $sheet->getCell("N{$row}")->getValue()) ?: null;
            $addressDetail = trim((string) $sheet->getCell("O{$row}")->getValue()) ?: null;

            $districtId = $districtMap[strtolower($districtRaw)] ?? 1;
            $villageId  = 1;
            if (!empty($villageRaw)) {
                $vill = Village::where('district_id', $districtId)->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($villageRaw)])->first();
                if ($vill) {
                    $villageId = $vill->id;
                }
            }

            // Pendidikan
            $eduLevelRaw = trim((string) $sheet->getCell("P{$row}")->getValue());
            $schoolName  = trim((string) $sheet->getCell("Q{$row}")->getValue()) ?: '-';
            $major       = trim((string) $sheet->getCell("R{$row}")->getValue()) ?: null;
            $eduStatusRaw= strtolower(trim((string) $sheet->getCell("S{$row}")->getValue()));
            $gradYear    = trim((string) $sheet->getCell("T{$row}")->getValue()) ?: null;

            $eduLevelId = $eduMap[strtolower($eduLevelRaw)] ?? 4; // default SMA
            $eduStatus  = in_array($eduStatusRaw, ['sedang_sekolah', 'lulus', 'putus_sekolah'], true) ? $eduStatusRaw : 'lulus';

            // Pekerjaan
            $jobStatusRaw = trim((string) $sheet->getCell("U{$row}")->getValue());
            $jobTitle     = trim((string) $sheet->getCell("V{$row}")->getValue()) ?: null;
            $companyName  = trim((string) $sheet->getCell("W{$row}")->getValue()) ?: null;
            $bizField     = trim((string) $sheet->getCell("X{$row}")->getValue()) ?: null;

            $jobStatusId = $jobMap[strtolower($jobStatusRaw)] ?? 1;

            // Element Dakwah
            $orgRaw = trim((string) $sheet->getCell("Y{$row}")->getValue());
            // Skills
            $skillRaw = trim((string) $sheet->getCell("Z{$row}")->getValue());
            // Interests
            $interestRaw = trim((string) $sheet->getCell("AA{$row}")->getValue());
            // Status Verifikasi
            $verifRaw = strtolower(trim((string) $sheet->getCell("AB{$row}")->getValue()));
            $statusVerifikasi = in_array($verifRaw, ['verified', 'pending'], true) ? $verifRaw : 'verified';

            $rowsToProcess[] = [
                'row'               => $row,
                'cabang_id'         => $cabangId,
                'name'              => $name,
                'gender'            => $gender,
                'marital_status'    => $maritalStatus,
                'blood_type'        => $bloodType,
                'birth_place'       => $birthPlace,
                'birth_date'        => $birthDate,
                'phone'             => $phone ?: null,
                'email'             => $email,
                'status_verifikasi' => $statusVerifikasi,
                'status_data'       => 'active',
                'created_by'        => $userId,
                // Alamat
                'alamat'            => [
                    'province_id'    => 33,
                    'regency_id'     => 3314,
                    'district_id'    => $districtId,
                    'village_id'     => $villageId,
                    'dusun'          => $dusun,
                    'rt'             => $rt,
                    'rw'             => $rw,
                    'address_detail' => $addressDetail ?: 'Sragen',
                ],
                // Pendidikan
                'pendidikan'        => [
                    'education_level_id' => $eduLevelId,
                    'school_name'        => $schoolName,
                    'major'              => $major,
                    'education_status'   => $eduStatus,
                    'graduation_year'    => $gradYear,
                ],
                // Pekerjaan
                'pekerjaan'         => [
                    'job_status_id'  => $jobStatusId,
                    'job_title'      => $jobTitle,
                    'company_name'   => $companyName,
                    'business_field' => $bizField,
                ],
                'org_raw'      => $orgRaw,
                'skill_raw'    => $skillRaw,
                'interest_raw' => $interestRaw,
            ];
        }

        if ($dryRun) {
            return [
                'success'      => true,
                'dry_run'      => true,
                'total_rows'   => count($rowsToProcess) + count($errors),
                'valid_rows'   => count($rowsToProcess),
                'error_count'  => count($errors),
                'errors'       => $errors,
                'message'      => 'Validasi template berhasil. ' . count($rowsToProcess) . ' baris valid siap diimpor.',
            ];
        }

        // Execute import with database transaction
        DB::beginTransaction();
        try {
            foreach ($rowsToProcess as $item) {
                $existing = Pemuda::findExistingPemuda($item['name'], $item['gender'], $item['birth_date'], $item['cabang_id']);

                if ($existing) {
                    if ($updateExisting) {
                        $existing->update([
                            'marital_status'    => $item['marital_status'],
                            'blood_type'        => $item['blood_type'] ?: $existing->blood_type,
                            'birth_place'       => $item['birth_place'] ?: $existing->birth_place,
                            'phone'             => $item['phone'] ?: $existing->phone,
                            'email'             => $item['email'] ?: $existing->email,
                            'status_verifikasi' => $item['status_verifikasi'],
                        ]);

                        $pemudaId = $existing->id;
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                        continue;
                    }
                } else {
                    $regNumber = Pemuda::generateRegistrationNumber($item['cabang_id'], $item['birth_date']);

                    $pemuda = Pemuda::create([
                        'cabang_id'           => $item['cabang_id'],
                        'registration_number' => $regNumber,
                        'name'                => $item['name'],
                        'gender'              => $item['gender'],
                        'marital_status'      => $item['marital_status'],
                        'blood_type'          => $item['blood_type'],
                        'birth_place'         => $item['birth_place'],
                        'birth_date'          => $item['birth_date'],
                        'phone'               => $item['phone'],
                        'email'               => $item['email'],
                        'status_verifikasi'   => $item['status_verifikasi'],
                        'status_data'         => $item['status_data'],
                        'created_by'          => $item['created_by'],
                    ]);

                    $pemudaId = $pemuda->id;
                    $successCount++;
                }

                // Alamat
                Alamat::updateOrCreate(
                    ['pemuda_id' => $pemudaId],
                    array_merge($item['alamat'], ['pemuda_id' => $pemudaId])
                );

                // Pendidikan
                Pendidikan::updateOrCreate(
                    ['pemuda_id' => $pemudaId],
                    array_merge($item['pendidikan'], ['pemuda_id' => $pemudaId])
                );

                // Pekerjaan
                Pekerjaan::updateOrCreate(
                    ['pemuda_id' => $pemudaId],
                    array_merge($item['pekerjaan'], ['pemuda_id' => $pemudaId])
                );

                // Organisasi
                if (!empty($item['org_raw'])) {
                    $orgs = array_filter(array_map('trim', explode(',', $item['org_raw'])));
                    foreach ($orgs as $orgName) {
                        Organisasi::firstOrCreate([
                            'pemuda_id'         => $pemudaId,
                            'organization_name' => mb_strtoupper($orgName),
                        ]);
                    }
                }

                // Skills
                if (!empty($item['skill_raw'])) {
                    $skillNames = array_filter(array_map('trim', explode(',', $item['skill_raw'])));
                    foreach ($skillNames as $skName) {
                        $cleanSk = strtolower($skName);
                        $skId = $skillMap[$cleanSk] ?? null;
                        if (!$skId) {
                            $newSkill = Skill::firstOrCreate(['name' => ucwords($skName)]);
                            $skId = $newSkill->id;
                            $skillMap[$cleanSk] = $skId;
                        }
                        PemudaSkill::firstOrCreate([
                            'pemuda_id' => $pemudaId,
                            'skill_id'  => $skId,
                        ], ['level' => 'menengah']);
                    }
                }

                // Interests
                if (!empty($item['interest_raw'])) {
                    $intNames = array_filter(array_map('trim', explode(',', $item['interest_raw'])));
                    foreach ($intNames as $inName) {
                        $cleanIn = strtolower($inName);
                        $inId = $interestMap[$cleanIn] ?? null;
                        if (!$inId) {
                            $newInt = Interest::firstOrCreate(['name' => ucwords($inName)]);
                            $inId = $newInt->id;
                            $interestMap[$cleanIn] = $inId;
                        }
                        PemudaInterest::firstOrCreate([
                            'pemuda_id'   => $pemudaId,
                            'interest_id' => $inId,
                        ]);
                    }
                }
            }

            DB::commit();

            return [
                'success'       => true,
                'dry_run'       => false,
                'total_rows'    => count($rowsToProcess) + count($errors),
                'success_count' => $successCount,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount,
                'error_count'   => count($errors),
                'errors'        => $errors,
                'message'       => "Import selesai. Berhasil menambahkan {$successCount} data baru, memperbarui {$updatedCount} data, dan melewati {$skippedCount} data duplikat.",
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[PemudaImportService] Import Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memproses import data: ' . $e->getMessage(),
            ];
        }
    }
}
