<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\GuruDaerahMuda;
use App\Models\Wilayah;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GdmExportService
{
    /**
     * Generate Spreadsheet untuk Export Data Guru Daerah Muda (GDM)
     */
    public function exportToExcel(array $filters = []): Spreadsheet
    {
        $query = GuruDaerahMuda::with([
            'cabang.wilayah',
            'penugasan.cabang',
        ]);

        if (!empty($filters['search'])) {
            $s = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'LIKE', $s)
                  ->orWhere('tempat_lahir', 'LIKE', $s)
                  ->orWhere('alamat', 'LIKE', $s)
                  ->orWhere('no_wa', 'LIKE', $s)
                  ->orWhere('catatan', 'LIKE', $s);
            });
        }

        if (!empty($filters['cabang_asal_id'])) {
            $query->where('cabang_id', (int) $filters['cabang_asal_id']);
        }

        if (!empty($filters['cabang_penugasan_id'])) {
            $cId = (int) $filters['cabang_penugasan_id'];
            $query->whereHas('penugasan', function ($q) use ($cId) {
                $q->where('cabang_id', $cId);
            });
        }

        if (!empty($filters['tahun'])) {
            $t = (int) $filters['tahun'];
            $query->whereHas('penugasan', function ($q) use ($t) {
                $q->where('tahun', $t);
            });
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['aktif', 'nonaktif'], true)) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['sumber_data']) && in_array($filters['sumber_data'], ['pemuda', 'warga', 'manual'], true)) {
            $query->where('sumber_data', $filters['sumber_data']);
        }

        $gdmList = $query->orderBy('nama', 'ASC')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data GDM');

        // Document properties
        $spreadsheet->getProperties()
            ->setCreator('Sistem Pendataan Pemuda MTA Sragen')
            ->setTitle('Data Guru Daerah Muda (GDM)')
            ->setSubject('Guru Daerah Muda')
            ->setDescription('Export data kader Guru Daerah Muda (GDM) Pemuda MTA Kabupaten Sragen');

        // Title Header
        $sheet->setCellValue('A1', 'DATA GURU DAERAH MUDA (GDM) PEMUDA MTA KABUPATEN SRAGEN');
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $filterDesc = 'Diekspor pada: ' . date('d F Y, H:i:s') . ' WIB';
        if (!empty($filters['cabang_asal_id'])) {
            $cAsal = Cabang::find($filters['cabang_asal_id']);
            if ($cAsal) {
                $filterDesc .= ' | Cabang Asal: ' . $cAsal->name;
            }
        }
        if (!empty($filters['cabang_penugasan_id'])) {
            $cTugas = Cabang::find($filters['cabang_penugasan_id']);
            if ($cTugas) {
                $filterDesc .= ' | Cabang Tugas: ' . $cTugas->name;
            }
        }
        if (!empty($filters['tahun'])) {
            $filterDesc .= ' | Tahun Tugas: ' . $filters['tahun'];
        }
        if (!empty($filters['status'])) {
            $filterDesc .= ' | Status: ' . ucfirst($filters['status']);
        }
        if (!empty($filters['sumber_data'])) {
            $filterDesc .= ' | Sumber: ' . ucfirst($filters['sumber_data']);
        }
        if (!empty($filters['search'])) {
            $filterDesc .= ' | Kata Kunci: "' . $filters['search'] . '"';
        }

        $sheet->setCellValue('A2', $filterDesc);
        $sheet->mergeCells('A2:O2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(10); // Spacer

        // Column Headers
        $headers = [
            'A4' => 'No.',
            'B4' => 'Nama Lengkap GDM',
            'C4' => 'Cabang Asal',
            'D4' => 'Wilayah Asal',
            'E4' => 'Tempat Lahir',
            'F4' => 'Tanggal Lahir',
            'G4' => 'Usia',
            'H4' => 'No. WhatsApp',
            'I4' => 'Alamat Domisili',
            'J4' => 'Status Kader',
            'K4' => 'Sumber Data',
            'L4' => 'Penugasan Kajian Aktif',
            'M4' => 'Jadwal Kajian',
            'N4' => 'Riwayat Penugasan',
            'O4' => 'Catatan / Keterangan',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A4:O4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '991B1B'], // Crimson Red (GDM theme)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '7F1D1D']],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Data Rows
        $row = 5;
        $no = 1;
        $activeGdmCount = 0;

        foreach ($gdmList as $gdm) {
            $wilayahText = $gdm->cabang?->wilayah ? $gdm->cabang->wilayah->name : '-';
            $cabangText  = $gdm->cabang ? $gdm->cabang->name : '-';

            $tglLahirText = '-';
            if ($gdm->tanggal_lahir) {
                try {
                    $tglLahirText = Carbon::parse($gdm->tanggal_lahir)->translatedFormat('d/m/Y');
                } catch (\Throwable) {
                    $tglLahirText = (string) $gdm->tanggal_lahir;
                }
            }

            $usiaText = $gdm->usia !== null ? ($gdm->usia . ' Th') : '-';

            $sumberText = match ($gdm->sumber_data) {
                'pemuda' => 'Data Pemuda',
                'warga'  => 'Warga MTA',
                default  => 'Manual',
            };

            // Penugasan aktif
            $penugasanAktif = $gdm->penugasan->where('status', 'aktif');
            $cabangTugasList = $penugasanAktif->map(fn($p) => ($p->cabang?->name ?? 'Cabang ?') . ' (' . $p->tahun . ')')->filter()->implode(', ');
            $cabangTugasText = !empty($cabangTugasList) ? $cabangTugasList : 'Belum Ditugaskan';

            $jadwalList = $penugasanAktif->map(function ($p) {
                $j = array_filter([$p->hari_kajian, $p->jam_kajian]);
                return !empty($j) ? implode(' ', $j) : null;
            })->filter()->implode('; ');
            $jadwalText = !empty($jadwalList) ? $jadwalList : '-';

            // Riwayat penugasan ringkas
            $riwayatList = $gdm->penugasan->map(function ($p) {
                return $p->tahun . ': ' . ($p->cabang?->name ?? '-') . ' [' . ucfirst($p->status) . ']';
            })->implode("\n");
            $riwayatText = !empty($riwayatList) ? $riwayatList : '-';

            if ($gdm->status === 'aktif') {
                $activeGdmCount++;
            }

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $gdm->nama);
            $sheet->setCellValue('C' . $row, $cabangText);
            $sheet->setCellValue('D' . $row, $wilayahText);
            $sheet->setCellValue('E' . $row, $gdm->tempat_lahir ?? '-');
            $sheet->setCellValue('F' . $row, $tglLahirText);
            $sheet->setCellValue('G' . $row, $usiaText);
            $sheet->setCellValue('H' . $row, $gdm->no_wa ?? '-');
            $sheet->setCellValue('I' . $row, $gdm->alamat ?? '-');
            $sheet->setCellValue('J' . $row, ucfirst($gdm->status));
            $sheet->setCellValue('K' . $row, $sumberText);
            $sheet->setCellValue('L' . $row, $cabangTugasText);
            $sheet->setCellValue('M' . $row, $jadwalText);
            $sheet->setCellValue('N' . $row, $riwayatText);
            $sheet->setCellValue('O' . $row, $gdm->catatan ?? '-');

            // Multi-line cell handling
            if (str_contains($riwayatText, "\n")) {
                $sheet->getStyle("N{$row}")->getAlignment()->setWrapText(true);
            }

            // Alternating row background
            $bgRgb = ($row % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
                'font' => ['size' => 9],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgRgb],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                ],
            ]);

            // Specific cell alignments
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Highlight status badge
            if ($gdm->status === 'aktif') {
                $sheet->getStyle("J{$row}")->getFont()->getColor()->setRGB('059669');
                $sheet->getStyle("J{$row}")->getFont()->setBold(true);
            } else {
                $sheet->getStyle("J{$row}")->getFont()->getColor()->setRGB('64748B');
            }

            $sheet->getRowDimension($row)->setRowHeight(22);
            $row++;
            $no++;
        }

        // Summary row
        $sheet->setCellValue('A' . $row, 'TOTAL DATA GDM');
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue('G' . $row, ($no - 1) . ' Kader');
        $sheet->setCellValue('H' . $row, 'Aktif: ' . $activeGdmCount);
        $sheet->mergeCells("H{$row}:O{$row}");

        $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F172A']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
            ],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Auto-fit column widths
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
