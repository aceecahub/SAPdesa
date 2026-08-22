<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Dusun;
use App\Models\RW;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Inertia\Inertia;

class ExportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $dusunListQuery = Dusun::query();
        $rwListQuery = RW::with('dusun')->orderBy('dusun_id')->orderBy('nomor');

        if ($user->isKepalaDusun()) {
            $dusunListQuery->where('id', $user->dusun_id);
            $rwListQuery->where('dusun_id', $user->dusun_id);
        }

        return Inertia::render('Export/Index', [
            'dusunList' => $dusunListQuery->get(),
            'rwList' => $rwListQuery->get(),
            'kategoriList' => Kategori::all(),
        ]);
    }

    private function getFilteredQuery(Request $request)
    {
        $user = Auth::user();
        $query = Laporan::with(['kategori', 'subkategori', 'rw.dusun', 'user']);

        // Scope otomatis untuk Kepala Dusun
        if ($user->isKepalaDusun()) {
            $query->whereHas('rw', function ($q) use ($user) {
                $q->where('dusun_id', $user->dusun_id);
            });
        }

        if ($request->filled('rw_id')) {
            $query->where('rw_id', $request->rw_id);
        }
        if ($request->filled('dusun_id')) {
            $query->whereHas('rw', function ($q) use ($request) {
                $q->where('dusun_id', $request->dusun_id);
            });
        }
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('dari_tanggal')) {
            $query->where('tanggal', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->where('tanggal', '<=', $request->sampai_tanggal);
        }

        return $query->latest('tanggal');
    }

    /**
     * Export ke format native Excel (.xlsx) dengan styling profesional
     */
    public function excel(Request $request)
    {
        $user = Auth::user();
        $laporan = $this->getFilteredQuery($request)->get();
        $filename = 'laporan_sapdesa_' . now()->format('Y-m-d_His') . '.xlsx';

        // Information Filter Metadata
        if ($user->isKepalaDusun() && $user->dusun) {
            $dusunNama = 'Dusun ' . $user->dusun->nama . ' (Kewenangan Kadus)';
        } else {
            $dusunNama = $request->filled('dusun_id') ? (Dusun::find($request->dusun_id)?->nama ?? 'Semua') : 'Semua Dusun';
        }

        $rwObj = $request->filled('rw_id') ? RW::with('dusun')->find($request->rw_id) : null;
        $rwNama = $rwObj ? ('RW ' . str_pad($rwObj->nomor, 2, '0', STR_PAD_LEFT) . ' - Dusun ' . ($rwObj->dusun?->nama ?? '')) : 'Semua RW';
        $kategoriNama = $request->filled('kategori_id') ? (Kategori::find($request->kategori_id)?->nama ?? 'Semua') : 'Semua Kategori';
        $statusNama = $request->filled('status') ? strtoupper($request->status) : 'Semua Status';
        $periode = ($request->filled('dari_tanggal') ? $request->dari_tanggal : 'Awal') . ' s/d ' . ($request->filled('sampai_tanggal') ? $request->sampai_tanggal : 'Sekarang');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Laporan');

        // Show grid lines explicitly
        $sheet->setShowGridLines(true);

        // 1. Header Metadata Block
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'REKAPITULASI LAPORAN ASPIRASI DESA SIRNAPUTRA');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E4D38'));
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('A2', 'Dicetak pada: ' . now()->format('d F Y, H:i') . ' WIB');
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Filter Block
        $sheet->setCellValue('A4', 'Wilayah Dusun:')->getStyle('A4')->getFont()->setBold(true);
        $sheet->setCellValue('B4', $dusunNama);
        $sheet->setCellValue('D4', 'Kategori:')->getStyle('D4')->getFont()->setBold(true);
        $sheet->setCellValue('E4', $kategoriNama);

        $sheet->setCellValue('A5', 'Wilayah RW:')->getStyle('A5')->getFont()->setBold(true);
        $sheet->setCellValue('B5', $rwNama);
        $sheet->setCellValue('D5', 'Status:')->getStyle('D5')->getFont()->setBold(true);
        $sheet->setCellValue('E5', $statusNama);

        $sheet->setCellValue('A6', 'Periode Tanggal:')->getStyle('A6')->getFont()->setBold(true);
        $sheet->setCellValue('B6', $periode);

        // 2. Data Table Column Headers (Row 8)
        $tableHeaderRow = 8;
        $columns = [
            'A' => ['title' => 'No', 'width' => 6, 'align' => Alignment::HORIZONTAL_CENTER],
            'B' => ['title' => 'No. Tiket', 'width' => 14, 'align' => Alignment::HORIZONTAL_CENTER],
            'C' => ['title' => 'Judul Laporan', 'width' => 30, 'align' => Alignment::HORIZONTAL_LEFT],
            'D' => ['title' => 'Isi Aduan / Aspirasi', 'width' => 45, 'align' => Alignment::HORIZONTAL_LEFT],
            'E' => ['title' => 'Kategori', 'width' => 20, 'align' => Alignment::HORIZONTAL_LEFT],
            'F' => ['title' => 'Subkategori', 'width' => 20, 'align' => Alignment::HORIZONTAL_LEFT],
            'G' => ['title' => 'Wilayah RW', 'width' => 14, 'align' => Alignment::HORIZONTAL_CENTER],
            'H' => ['title' => 'Dusun Induk', 'width' => 16, 'align' => Alignment::HORIZONTAL_LEFT],
            'I' => ['title' => 'Nama Pelapor', 'width' => 20, 'align' => Alignment::HORIZONTAL_LEFT],
            'J' => ['title' => 'Tanggal Masuk', 'width' => 15, 'align' => Alignment::HORIZONTAL_CENTER],
            'K' => ['title' => 'Status', 'width' => 14, 'align' => Alignment::HORIZONTAL_CENTER],
            'L' => ['title' => 'Tanggapan / Respon Desa', 'width' => 40, 'align' => Alignment::HORIZONTAL_LEFT],
        ];

        foreach ($columns as $col => $info) {
            $cell = $col . $tableHeaderRow;
            $sheet->setCellValue($cell, $info['title']);
            $sheet->getColumnDimension($col)->setWidth($info['width']);
        }

        // Header Styling
        $headerRange = "A{$tableHeaderRow}:L{$tableHeaderRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E4D38'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension($tableHeaderRow)->setRowHeight(28);

        // 3. Populate Data Rows
        $currentRow = $tableHeaderRow + 1;
        $statusStyles = [
            'baru' => ['bg' => 'FEF3C7', 'color' => '92400E'],
            'diproses' => ['bg' => 'DBEAFE', 'color' => '1E40AF'],
            'selesai' => ['bg' => 'D1FAE5', 'color' => '065F46'],
            'ditolak' => ['bg' => 'FEE2E2', 'color' => '991B1B'],
        ];

        foreach ($laporan as $i => $item) {
            $rwLabel = $item->rw ? 'RW ' . str_pad($item->rw->nomor, 2, '0', STR_PAD_LEFT) : '-';
            $statusKey = strtolower($item->status);
            $statusStyle = $statusStyles[$statusKey] ?? ['bg' => 'F1F5F9', 'color' => '475569'];

            $sheet->setCellValue("A{$currentRow}", $i + 1);
            $sheet->setCellValue("B{$currentRow}", '#LAP-' . str_pad($item->id, 4, '0', STR_PAD_LEFT));
            $sheet->setCellValue("C{$currentRow}", $item->judul);
            $sheet->setCellValue("D{$currentRow}", $item->isi);
            $sheet->setCellValue("E{$currentRow}", $item->kategori->nama ?? '-');
            $sheet->setCellValue("F{$currentRow}", $item->subkategori->nama ?? '-');
            $sheet->setCellValue("G{$currentRow}", $rwLabel);
            $sheet->setCellValue("H{$currentRow}", $item->rw?->dusun->nama ?? '-');
            $sheet->setCellValue("I{$currentRow}", $item->user->name ?? '-');
            $sheet->setCellValue("J{$currentRow}", $item->tanggal ? $item->tanggal->format('d/m/Y') : '-');
            $sheet->setCellValue("K{$currentRow}", strtoupper($item->status));
            $sheet->setCellValue("L{$currentRow}", $item->respon_admin ?? '-');

            // Alignments
            foreach ($columns as $col => $info) {
                $sheet->getStyle("{$col}{$currentRow}")->getAlignment()
                    ->setHorizontal($info['align'])
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);
            }

            // Alternating Row Background
            $rowBg = ($i % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
            $sheet->getStyle("A{$currentRow}:L{$currentRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($rowBg);

            // Status Badge Formatting
            $sheet->getStyle("K{$currentRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => $statusStyle['color']],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $statusStyle['bg']],
                ],
            ]);

            // Thin Borders for Data Row
            $sheet->getStyle("A{$currentRow}:L{$currentRow}")->getBorders()->getAllBorders()->applyFromArray([
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CBD5E1'],
            ]);

            $currentRow++;
        }

        if ($laporan->isEmpty()) {
            $sheet->mergeCells("A{$currentRow}:L{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'Tidak ada data laporan aduan ditemukan untuk kriteria filter yang dipilih.');
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('94A3B8'));
        }

        // 4. Output Response Stream
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export ke CSV ber-styling warna lengkap (HTML Table formatted CSV)
     */
    public function csv(Request $request)
    {
        $user = Auth::user();
        $laporan = $this->getFilteredQuery($request)->get();
        $filename = 'laporan_sapdesa_' . now()->format('Y-m-d_His') . '.csv';

        // Filter Metadata Text
        if ($user->isKepalaDusun() && $user->dusun) {
            $dusunNama = 'Dusun ' . $user->dusun->nama . ' (Kewenangan Kadus)';
        } else {
            $dusunNama = $request->filled('dusun_id') ? (Dusun::find($request->dusun_id)?->nama ?? 'Semua') : 'Semua Dusun';
        }

        $rwObj = $request->filled('rw_id') ? RW::with('dusun')->find($request->rw_id) : null;
        $rwNama = $rwObj ? ('RW ' . str_pad($rwObj->nomor, 2, '0', STR_PAD_LEFT) . ' - Dusun ' . ($rwObj->dusun?->nama ?? '')) : 'Semua RW';
        $kategoriNama = $request->filled('kategori_id') ? (Kategori::find($request->kategori_id)?->nama ?? 'Semua') : 'Semua Kategori';
        $statusNama = $request->filled('status') ? strtoupper($request->status) : 'Semua Status';
        $periode = ($request->filled('dari_tanggal') ? $request->dari_tanggal : 'Awal') . ' s/d ' . ($request->filled('sampai_tanggal') ? $request->sampai_tanggal : 'Sekarang');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($laporan, $dusunNama, $rwNama, $kategoriNama, $statusNama, $periode) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            echo '<style>
                body { font-family: "Segoe UI", Arial, sans-serif; }
                table { border-collapse: collapse; width: 100%; font-size: 11pt; }
                th { background-color: #1e4d38; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #0f2d20; padding: 10px; }
                td { border: 1px solid #cbd5e1; padding: 8px; vertical-align: top; }
                .title { font-size: 16pt; font-weight: bold; color: #1e4d38; margin-bottom: 4px; }
                .subtitle { font-size: 10pt; color: #64748b; margin-bottom: 12px; }
                .meta-table { margin-bottom: 15px; border: none; }
                .meta-table td { border: none; padding: 3px 10px 3px 0; }
                .meta-label { font-weight: bold; color: #475569; width: 140px; }
                .meta-val { color: #0f172a; font-weight: 500; }
                .row-even { background-color: #ffffff; }
                .row-odd { background-color: #f8fafc; }
                .badge-baru { background-color: #fef3c7; color: #92400e; font-weight: bold; text-align: center; border-radius: 4px; padding: 4px; }
                .badge-diproses { background-color: #dbeafe; color: #1e40af; font-weight: bold; text-align: center; border-radius: 4px; padding: 4px; }
                .badge-selesai { background-color: #d1fae5; color: #065f46; font-weight: bold; text-align: center; border-radius: 4px; padding: 4px; }
                .badge-ditolak { background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center; border-radius: 4px; padding: 4px; }
                .center { text-align: center; }
            </style></head>';
            echo '<body>';

            // Title & Metadata Block
            echo '<div class="title">REKAPITULASI LAPORAN ASPIRASI DESA SIRNAPUTRA</div>';
            echo '<div class="subtitle">Dicetak pada: ' . now()->format('d F Y, H:i') . ' WIB</div>';

            echo '<table class="meta-table">';
            echo '<tr><td class="meta-label">Wilayah Dusun:</td><td class="meta-val">' . htmlspecialchars($dusunNama) . '</td><td class="meta-label">Kategori:</td><td class="meta-val">' . htmlspecialchars($kategoriNama) . '</td></tr>';
            echo '<tr><td class="meta-label">Wilayah RW:</td><td class="meta-val">' . htmlspecialchars($rwNama) . '</td><td class="meta-label">Status:</td><td class="meta-val">' . htmlspecialchars($statusNama) . '</td></tr>';
            echo '<tr><td class="meta-label">Periode:</td><td class="meta-val" colspan="3">' . htmlspecialchars($periode) . '</td></tr>';
            echo '</table>';

            echo '<br>';

            // Main Colored Table
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th style="width: 40px;">No</th>';
            echo '<th style="width: 90px;">No. Tiket</th>';
            echo '<th style="width: 220px;">Judul Laporan</th>';
            echo '<th style="width: 320px;">Isi Aduan / Aspirasi</th>';
            echo '<th style="width: 130px;">Kategori</th>';
            echo '<th style="width: 130px;">Subkategori</th>';
            echo '<th style="width: 90px;">Wilayah RW</th>';
            echo '<th style="width: 120px;">Dusun Induk</th>';
            echo '<th style="width: 140px;">Nama Pelapor</th>';
            echo '<th style="width: 100px;">Tanggal Masuk</th>';
            echo '<th style="width: 110px;">Status</th>';
            echo '<th style="width: 250px;">Tanggapan / Respon Desa</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($laporan as $i => $item) {
                $rowClass = ($i % 2 === 0) ? 'row-even' : 'row-odd';
                $rwLabel = $item->rw ? 'RW ' . str_pad($item->rw->nomor, 2, '0', STR_PAD_LEFT) : '-';
                $statusStr = strtolower($item->status);
                $badgeClass = 'badge-' . $statusStr;

                echo "<tr class=\"{$rowClass}\">";
                echo "<td class=\"center\">" . ($i + 1) . "</td>";
                echo "<td class=\"center\">#LAP-" . str_pad($item->id, 4, '0', STR_PAD_LEFT) . "</td>";
                echo "<td><strong>" . htmlspecialchars($item->judul) . "</strong></td>";
                echo "<td>" . nl2br(htmlspecialchars($item->isi)) . "</td>";
                echo "<td>" . htmlspecialchars($item->kategori->nama ?? '-') . "</td>";
                echo "<td>" . htmlspecialchars($item->subkategori->nama ?? '-') . "</td>";
                echo "<td class=\"center\">" . htmlspecialchars($rwLabel) . "</td>";
                echo "<td>" . htmlspecialchars($item->rw?->dusun->nama ?? '-') . "</td>";
                echo "<td>" . htmlspecialchars($item->user->name ?? '-') . "</td>";
                echo "<td class=\"center\">" . ($item->tanggal ? $item->tanggal->format('d/m/Y') : '-') . "</td>";
                echo "<td class=\"{$badgeClass}\">" . strtoupper($item->status) . "</td>";
                echo "<td>" . ($item->respon_admin ? nl2br(htmlspecialchars($item->respon_admin)) : '-') . "</td>";
                echo "</tr>";
            }

            if ($laporan->isEmpty()) {
                echo '<tr><td colspan="12" class="center" style="padding: 20px; color: #94a3b8;">Tidak ada data laporan aduan ditemukan untuk kriteria filter yang dipilih.</td></tr>';
            }

            echo '</tbody>';
            echo '</table>';

            echo '</body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
