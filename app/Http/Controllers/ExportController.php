<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Dusun;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ExportController extends Controller
{
    public function index()
    {
        return Inertia::render('Export/Index', [
            'dusunList' => Dusun::all(),
            'kategoriList' => Kategori::all(),
        ]);
    }

    public function csv(Request $request)
    {
        $query = Laporan::with(['kategori', 'dusun', 'user']);

        // Filter
        if ($request->filled('dusun_id')) {
            $query->where('dusun_id', $request->dusun_id);
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

        $laporan = $query->latest('tanggal')->get();

        $filename = 'laporan_sapdesa_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($laporan) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($file, [
                'No', 'Judul', 'Isi', 'Kategori', 'Dusun', 'Pelapor',
                'Tanggal', 'Status', 'Respon Admin', 'Dibuat',
            ]);

            foreach ($laporan as $i => $item) {
                fputcsv($file, [
                    $i + 1,
                    $item->judul,
                    $item->isi,
                    $item->kategori->nama ?? '-',
                    $item->dusun->nama ?? '-',
                    $item->user->name ?? '-',
                    $item->tanggal?->format('d/m/Y'),
                    ucfirst($item->status),
                    $item->respon_admin ?? '-',
                    $item->created_at?->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
