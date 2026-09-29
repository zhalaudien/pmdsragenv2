<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\Wilayah;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CabangExportService
{
    /**
     * Generate Spreadsheet untuk Export Master Cabang
     */
    public function exportToExcel(array $filters = []): Spreadsheet
    {
        $query = Cabang::with('wilayah')->withCount('pemuda');

        if (!empty($filters['wilayah_id'])) {
            $query->where('wilayah_id', (int) $filters['wilayah_id']);
        }

        if (!empty($filters['has_gelombang']) && in_array($filters['has_gelombang'], ['sudah', 'belum'], true)) {
            $query->where('has_gelombang', $filters['has_gelombang']);
        }

        if (!empty($filters['search'])) {
            $s = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', $s)
                  ->orWhere('code', 'LIKE', $s)
                  ->orWhere('pimpinan_nama', 'LIKE', $s)
                  ->orWhere('alamat', 'LIKE', $s)
                  ->orWhere('gelombang_ustadz', 'LIKE', $s);
            });
        }

        $cabangList = $query->orderBy('wilayah_id', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Cabang');

        // Document properties
        $spreadsheet->getProperties()
            ->setCreator('Sistem Pendataan Pemuda MTA Sragen')
            ->setTitle('Data Master Cabang')
            ->setSubject('Master Cabang')
            ->setDescription('Export data master cabang Pemuda MTA Kabupaten Sragen');

        // Title Header
        $sheet->setCellValue('A1', 'DATA MASTER CABANG PEMUDA MTA KABUPATEN SRAGEN');
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $filterDesc = 'Diekspor pada: ' . date('d F Y, H:i:s') . ' WIB';
        if (!empty($filters['wilayah_id'])) {
            $w = Wilayah::find($filters['wilayah_id']);
            if ($w) $filterDesc .= ' | Wilayah: ' . $w->name;
        }
        if (!empty($filters['has_gelombang'])) {
            $filterDesc .= ' | Gelombang: ' . ($filters['has_gelombang'] === 'sudah' ? 'Sudah' : 'Belum');
        }
        if (!empty($filters['search'])) {
            $filterDesc .= ' | Kata Kunci: "' . $filters['search'] . '"';
        }

        $sheet->setCellValue('A2', $filterDesc);
        $sheet->mergeCells('A2:N2');
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
            'B4' => 'Wilayah',
            'C4' => 'Kode Cabang',
            'D4' => 'Nama Cabang',
            'E4' => 'Pimpinan / Ketua',
            'F4' => 'No. WhatsApp',
            'G4' => 'Alamat Sekretariat',
            'H4' => 'Link Maps',
            'I4' => 'Status Gelombang',
            'J4' => 'Hari Gelombang',
            'K4' => 'Jam Gelombang',
            'L4' => 'Ustadz Pengampu',
            'M4' => 'Total Pemuda',
            'N4' => 'Keterangan',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A4:N4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'], // Emerald Teal
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0D5C56']],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Data Rows
        $row = 5;
        $no = 1;

        foreach ($cabangList as $c) {
            $wilayahText = $c->wilayah ? ($c->wilayah->name . ' (' . $c->wilayah->code . ')') : '-';
            $statusGelombang = $c->has_gelombang === 'sudah' ? 'Sudah Bergelombang' : 'Belum Bergelombang';

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $wilayahText);
            $sheet->setCellValue('C' . $row, $c->code ?? '-');
            $sheet->setCellValue('D' . $row, $c->name);
            $sheet->setCellValue('E' . $row, $c->pimpinan_nama ?? '-');
            $sheet->setCellValue('F' . $row, $c->no_wa ?? '-');
            $sheet->setCellValue('G' . $row, $c->alamat ?? '-');
            $sheet->setCellValue('H' . $row, $c->maps_url ?? '-');
            $sheet->setCellValue('I' . $row, $statusGelombang);
            $sheet->setCellValue('J' . $row, $c->gelombang_hari ?? '-');
            $sheet->setCellValue('K' . $row, $c->gelombang_jam ?? '-');
            $sheet->setCellValue('L' . $row, $c->gelombang_ustadz ?? '-');
            $sheet->setCellValue('M' . $row, (int) ($c->pemuda_count ?? 0));
            $sheet->setCellValue('N' . $row, $c->description ?? '-');

            // Alternating row background
            $bgRgb = ($row % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
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
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Highlight gelombang status
            if ($c->has_gelombang === 'sudah') {
                $sheet->getStyle("I{$row}")->getFont()->getColor()->setRGB('059669');
                $sheet->getStyle("I{$row}")->getFont()->setBold(true);
            } else {
                $sheet->getStyle("I{$row}")->getFont()->getColor()->setRGB('64748B');
            }

            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
            $no++;
        }

        // Summary row
        $lastDataRow = $row - 1;
        $sheet->setCellValue('A' . $row, 'TOTAL DATA');
        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue('M' . $row, "=SUM(M5:M{$lastDataRow})");
        $sheet->setCellValue('N' . $row, ($no - 1) . ' Cabang');

        $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
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
        $sheet->getStyle("M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Auto-fit column widths
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
