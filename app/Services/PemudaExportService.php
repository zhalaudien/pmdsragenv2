<?php

namespace App\Services;

use App\Models\Pemuda;
use App\Models\Cabang;
use App\Models\Wilayah;
use App\Models\Skill;
use App\Models\Interest;
use App\Models\EducationLevel;
use App\Models\JobStatus;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PemudaExportService
{
    public const CATEGORIZED_COLUMNS = [
        'personal' => [
            'category_name' => 'Data Pribadi',
            'category_icon' => 'fas fa-user',
            'columns' => [
                'registration_number' => 'No. Registrasi',
                'name'                => 'Nama Lengkap',
                'gender'              => 'Jenis Kelamin',
                'marital_status'      => 'Status Pernikahan',
                'blood_type'          => 'Golongan Darah',
                'birth_place'         => 'Tempat Lahir',
                'birth_date'          => 'Tanggal Lahir',
                'age'                 => 'Usia (Tahun)',
                'phone'               => 'No. WhatsApp / HP',
                'email'               => 'Email',
            ],
        ],
        'wilayah_cabang' => [
            'category_name' => 'Wilayah & Cabang',
            'category_icon' => 'fas fa-sitemap',
            'columns' => [
                'wilayah_name' => 'Wilayah',
                'cabang_name'  => 'Cabang Pemuda MTA',
            ],
        ],
        'alamat' => [
            'category_name' => 'Alamat & Domisili',
            'category_icon' => 'fas fa-map-marker-alt',
            'columns' => [
                'address_detail' => 'Alamat Detail',
                'dusun'          => 'Dusun / Dukuh',
                'rt'             => 'RT',
                'rw'             => 'RW',
                'village_name'   => 'Desa / Kelurahan',
                'district_name'  => 'Kecamatan',
            ],
        ],
        'pendidikan' => [
            'category_name' => 'Pendidikan',
            'category_icon' => 'fas fa-graduation-cap',
            'columns' => [
                'education_level_name' => 'Jenjang Pendidikan',
                'school_name'          => 'Nama Sekolah / Kampus',
                'major'                => 'Jurusan',
                'education_status'     => 'Status Pendidikan',
                'graduation_year'      => 'Tahun Lulus',
            ],
        ],
        'pekerjaan' => [
            'category_name' => 'Pekerjaan & Wirausaha',
            'category_icon' => 'fas fa-briefcase',
            'columns' => [
                'job_status_name'  => 'Status Pekerjaan',
                'job_title'        => 'Profesi / Jabatan',
                'company_name'     => 'Nama Perusahaan / Instansi',
                'business_field'   => 'Bidang Usaha',
                'business_name'    => 'Nama Usaha (Wirausaha)',
                'business_address' => 'Alamat Tempat Usaha',
                'business_contact' => 'Kontak Person Usaha',
                'business_social'  => 'Media Sosial Usaha',
            ],
        ],
        'organisasi_potensi' => [
            'category_name' => 'Element Dakwah, Bakat & Minat',
            'category_icon' => 'fas fa-star',
            'columns' => [
                'organizations' => 'Element Dakwah Yang Diikuti',
                'skills'        => 'Bakat / Keahlian',
                'interests'     => 'Minat',
            ],
        ],
        'status_sistem' => [
            'category_name' => 'Status & Sistem',
            'category_icon' => 'fas fa-info-circle',
            'columns' => [
                'status_verifikasi' => 'Status Verifikasi',
                'status_data'       => 'Status Data',
                'created_at'        => 'Tanggal Registrasi',
            ],
        ],
    ];

    public const PRESETS = [
        'default' => [
            'registration_number', 'name', 'gender', 'age', 'birth_date', 
            'phone', 'cabang_name', 'address_detail', 'education_level_name', 
            'job_status_name', 'skills', 'interests', 'status_verifikasi'
        ],
        'all' => [
            'registration_number', 'name', 'gender', 'marital_status', 'blood_type',
            'birth_place', 'birth_date', 'age', 'phone', 'email',
            'wilayah_name', 'cabang_name', 'address_detail', 'dusun', 'rt', 'rw',
            'village_name', 'district_name', 'education_level_name', 'school_name',
            'major', 'education_status', 'graduation_year', 'job_status_name',
            'job_title', 'company_name', 'business_field', 'business_name',
            'business_address', 'business_contact', 'business_social',
            'organizations', 'skills', 'interests', 'status_verifikasi',
            'status_data', 'created_at'
        ],
        'contact' => [
            'registration_number', 'name', 'gender', 'phone', 'email',
            'cabang_name', 'address_detail', 'dusun', 'rt', 'rw',
            'village_name', 'district_name'
        ],
        'potensi' => [
            'registration_number', 'name', 'gender', 'age', 'cabang_name',
            'education_level_name', 'school_name', 'major', 'job_status_name',
            'job_title', 'organizations', 'skills', 'interests'
        ],
        'business' => [
            'registration_number', 'name', 'gender', 'phone', 'cabang_name',
            'job_status_name', 'business_name', 'business_field', 'business_address',
            'business_contact', 'business_social', 'skills'
        ],
    ];

    public function getAllColumnLabels(): array
    {
        $flat = [];
        foreach (self::CATEGORIZED_COLUMNS as $group) {
            foreach ($group['columns'] as $k => $label) {
                $flat[$k] = $label;
            }
        }
        return $flat;
    }

    public function getCategorizedColumns(): array
    {
        return self::CATEGORIZED_COLUMNS;
    }

    public function getPresets(): array
    {
        return self::PRESETS;
    }

    public function getPreset(string $name): array
    {
        return self::PRESETS[$name] ?? self::PRESETS['default'];
    }

    public function countExportData(array $filters = [], array $scope = []): int
    {
        return Pemuda::query()->filtered($filters, $scope)->count();
    }

    public function exportToExcel(array $selectedColumns = [], array $filters = [], array $scope = []): Spreadsheet
    {
        if (empty($selectedColumns)) {
            $selectedColumns = self::PRESETS['default'];
        }

        $allLabels = $this->getAllColumnLabels();
        $activeColumns = [];
        foreach ($selectedColumns as $colKey) {
            if (isset($allLabels[$colKey])) {
                $activeColumns[$colKey] = $allLabels[$colKey];
            }
        }
        if (empty($activeColumns)) {
            $activeColumns = array_intersect_key($allLabels, array_flip(self::PRESETS['default']));
        }

        $query = Pemuda::with([
            'cabang.wilayah',
            'alamat.district',
            'alamat.village',
            'pendidikan.educationLevel',
            'pekerjaan.jobStatus',
            'organisasi',
            'skills',
            'interests',
        ])->filtered($filters, $scope)->orderBy('created_at', 'DESC');

        $data = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pemuda');

        // Header Title Block
        $totalCols = count($activeColumns);
        $lastColLetter = $this->getColumnLetter($totalCols);

        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', 'DATA REKAPITULASI PEMUDA MTA PERWAKILAN SRAGEN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1E293B');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->setCellValue('A2', 'Diekspor pada: ' . date('d F Y, H:i:s') . ' WIB | Total: ' . count($data) . ' Pemuda');
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setRGB('64748B');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(10); // Spacing row

        // Column Headers (Row 4)
        $headerRow = 4;
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // Column "No"
        $sheet->setCellValue('A4', 'No');
        $colIdx = 2; // B
        foreach ($activeColumns as $colKey => $colLabel) {
            $colLetter = $this->getColumnLetter($colIdx);
            $sheet->setCellValue("{$colLetter}4", $colLabel);
            $colIdx++;
        }

        $fullLastColLetter = $this->getColumnLetter($totalCols + 1);

        // Header Style
        $headerRange = "A4:{$fullLastColLetter}4";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        // Data Rows
        $rowNum = 5;
        $no = 1;
        foreach ($data as $pemuda) {
            $sheet->setCellValue("A{$rowNum}", $no);
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $colIdx = 2;
            foreach ($activeColumns as $colKey => $colLabel) {
                $colLetter = $this->getColumnLetter($colIdx);
                $value     = $this->formatCellValue($colKey, $pemuda);

                // Set explicit string for registration_number and phone to avoid scientific notation
                if (in_array($colKey, ['registration_number', 'phone', 'rt', 'rw'], true)) {
                    $sheet->setCellValueExplicit("{$colLetter}{$rowNum}", $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue("{$colLetter}{$rowNum}", $value);
                }

                // Alignments
                if (in_array($colKey, ['gender', 'blood_type', 'age', 'birth_date', 'graduation_year', 'created_at', 'status_verifikasi', 'status_data', 'rt', 'rw'], true)) {
                    $sheet->getStyle("{$colLetter}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $colIdx++;
            }

            // Zebra stripe
            if ($no % 2 === 0) {
                $sheet->getStyle("A{$rowNum}:{$fullLastColLetter}{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getRowDimension($rowNum)->setRowHeight(20);
            $rowNum++;
            $no++;
        }

        $lastDataRow = $rowNum - 1;
        if ($lastDataRow >= 5) {
            $sheet->getStyle("A5:{$fullLastColLetter}{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'E2E8F0'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Auto-fit column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        for ($c = 2; $c <= ($totalCols + 1); $c++) {
            $l = $this->getColumnLetter($c);
            $sheet->getColumnDimension($l)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function exportToCsv(array $selectedColumns = [], array $filters = [], array $scope = []): string
    {
        if (empty($selectedColumns)) {
            $selectedColumns = self::PRESETS['default'];
        }

        $allLabels = $this->getAllColumnLabels();
        $activeColumns = [];
        foreach ($selectedColumns as $colKey) {
            if (isset($allLabels[$colKey])) {
                $activeColumns[$colKey] = $allLabels[$colKey];
            }
        }

        $query = Pemuda::with([
            'cabang.wilayah',
            'alamat.district',
            'alamat.village',
            'pendidikan.educationLevel',
            'pekerjaan.jobStatus',
            'organisasi',
            'skills',
            'interests',
        ])->filtered($filters, $scope)->orderBy('created_at', 'DESC');

        $data = $query->get();

        $output = fopen('php://temp', 'r+');
        fputs($output, "\xEF\xBB\xBF"); // BOM for UTF-8 Excel compatibility

        // Header Row
        $header = array_merge(['No'], array_values($activeColumns));
        fputcsv($output, $header);

        // Data Rows
        $no = 1;
        foreach ($data as $pemuda) {
            $row = [$no++];
            foreach ($activeColumns as $colKey => $colLabel) {
                $row[] = $this->formatCellValue($colKey, $pemuda);
            }
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    protected function formatCellValue(string $key, Pemuda $p): string
    {
        return match ($key) {
            'registration_number' => (string) ($p->registration_number ?? '-'),
            'name'                => (string) ($p->name ?? '-'),
            'gender'              => $p->gender === 'L' ? 'Laki-laki' : ($p->gender === 'P' ? 'Perempuan' : '-'),
            'marital_status'      => match ($p->marital_status) {
                'belum_menikah' => 'Belum Menikah',
                'sudah_menikah' => 'Sudah Menikah',
                'janda'         => 'Janda',
                'duda'          => 'Duda',
                default         => '-',
            },
            'blood_type'          => (string) ($p->blood_type ?: '-'),
            'birth_place'         => (string) ($p->birth_place ?: '-'),
            'birth_date'          => !empty($p->birth_date) ? date('d/m/Y', strtotime($p->birth_date)) : '-',
            'age'                 => !empty($p->birth_date) ? (string) (date_diff(date_create($p->birth_date), date_create('today'))->y) : '-',
            'phone'               => (string) ($p->phone ?: '-'),
            'email'               => (string) ($p->email ?: '-'),
            'wilayah_name'        => (string) ($p->cabang->wilayah->name ?? '-'),
            'cabang_name'         => (string) ($p->cabang->name ?? '-'),
            'address_detail'      => (string) ($p->alamat->address_detail ?? '-'),
            'dusun'               => (string) ($p->alamat->dusun ?? '-'),
            'rt'                  => (string) ($p->alamat->rt ?? '-'),
            'rw'                  => (string) ($p->alamat->rw ?? '-'),
            'village_name'        => (string) ($p->alamat->village->name ?? '-'),
            'district_name'       => (string) ($p->alamat->district->name ?? '-'),
            'education_level_name'=> (string) ($p->pendidikan->educationLevel->name ?? '-'),
            'school_name'         => (string) ($p->pendidikan->school_name ?? '-'),
            'major'               => (string) ($p->pendidikan->major ?? '-'),
            'education_status'    => match ($p->pendidikan->education_status ?? '') {
                'sedang_sekolah' => 'Sedang Menempuh',
                'lulus'          => 'Lulus',
                'putus_sekolah'  => 'Putus Sekolah',
                default          => '-',
            },
            'graduation_year'     => (string) ($p->pendidikan->graduation_year ?? '-'),
            'job_status_name'     => (string) ($p->pekerjaan->jobStatus->name ?? '-'),
            'job_title'           => (string) ($p->pekerjaan->job_title ?? '-'),
            'company_name'        => (string) ($p->pekerjaan->company_name ?? '-'),
            'business_field'      => (string) ($p->pekerjaan->business_field ?? '-'),
            'business_name'       => (string) ($p->pekerjaan->business_name ?? '-'),
            'business_address'    => (string) ($p->pekerjaan->business_address ?? '-'),
            'business_contact'    => (string) ($p->pekerjaan->business_contact ?? '-'),
            'business_social'     => (string) ($p->pekerjaan->business_social ?? '-'),
            'organizations'       => $p->organisasi->pluck('organization_name')->map(fn($o) => strtoupper($o))->implode(', ') ?: '-',
            'skills'              => $p->skills->pluck('name')->implode(', ') ?: '-',
            'interests'           => $p->interests->pluck('name')->implode(', ') ?: '-',
            'status_verifikasi'   => $p->status_verifikasi === 'verified' ? 'Terverifikasi' : 'Pending',
            'status_data'         => $p->status_data === 'active' ? 'Aktif' : 'Arsip',
            'created_at'          => !empty($p->created_at) ? $p->created_at->format('d/m/Y H:i') : '-',
            default               => '-',
        };
    }

    protected function getColumnLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $mod    = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIndex = (int) (($colIndex - $mod) / 26);
        }
        return $letter;
    }
}
