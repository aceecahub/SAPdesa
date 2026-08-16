<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Dusun;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Laporan::query();

        // Scope: kepala_dusun hanya dusunnya
        if ($user->isKepalaDusun()) {
            $query->where('dusun_id', $user->dusun_id);
        }

        // Stat cards
        $totalLaporan = (clone $query)->count();
        $statusCounts = (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Per kategori
        $perKategori = (clone $query)
            ->join('kategori', 'laporan.kategori_id', '=', 'kategori.id')
            ->select('kategori.nama', DB::raw('count(*) as total'))
            ->groupBy('kategori.nama')
            ->pluck('total', 'nama')
            ->toArray();

        // Per dusun (only for admin/superadmin)
        $perDusun = [];
        if (!$user->isKepalaDusun()) {
            $perDusun = Laporan::join('dusun', 'laporan.dusun_id', '=', 'dusun.id')
                ->select('dusun.nama', DB::raw('count(*) as total'))
                ->groupBy('dusun.nama')
                ->pluck('total', 'nama')
                ->toArray();
        }

        // Per bulan (6 bulan terakhir)
        $perBulan = (clone $query)
            ->select(
                DB::raw("DATE_FORMAT(tanggal, '%Y-%m') as bulan"),
                DB::raw('count(*) as total')
            )
            ->where('tanggal', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Per subkategori
        $perSubkategori = (clone $query)
            ->join('subkategori', 'laporan.subkategori_id', '=', 'subkategori.id')
            ->select('subkategori.nama', DB::raw('count(*) as total'))
            ->groupBy('subkategori.nama')
            ->pluck('total', 'nama')
            ->toArray();

        // Laporan terbaru (5)
        $laporanTerbaru = (clone $query)
            ->with(['kategori', 'subkategori', 'dusun', 'user'])
            ->latest('tanggal')
            ->limit(5)
            ->get();

        // User stats (superadmin only)
        $totalUsers = $user->isSuperadmin() ? User::count() : null;
        $totalDusun = Dusun::count();

        // Additional KPI metrics
        $selesaiCount = $statusCounts['selesai'] ?? 0;
        $rateSelesai = $totalLaporan > 0 ? round(($selesaiCount / $totalLaporan) * 100, 1) : 0;

        $laporanBulanIni = (clone $query)->whereYear('tanggal', now()->year)->whereMonth('tanggal', now()->month)->count();
        $laporanBulanLalu = (clone $query)->whereYear('tanggal', now()->subMonth()->year)->whereMonth('tanggal', now()->subMonth()->month)->count();
        
        $trenBulanIni = 0;
        if ($laporanBulanLalu > 0) {
            $trenBulanIni = round((($laporanBulanIni - $laporanBulanLalu) / $laporanBulanLalu) * 100, 1);
        } elseif ($laporanBulanIni > 0) {
            $trenBulanIni = 100;
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $totalLaporan,
                'baru' => $statusCounts['baru'] ?? 0,
                'diproses' => $statusCounts['diproses'] ?? 0,
                'selesai' => $selesaiCount,
                'ditolak' => $statusCounts['ditolak'] ?? 0,
                'rateSelesai' => $rateSelesai,
                'laporanBulanIni' => $laporanBulanIni,
                'laporanBulanLalu' => $laporanBulanLalu,
                'trenBulanIni' => $trenBulanIni,
                'totalUsers' => $totalUsers,
                'totalDusun' => $totalDusun,
            ],
            'perKategori' => $perKategori,
            'perSubkategori' => $perSubkategori,
            'perDusun' => $perDusun,
            'perBulan' => $perBulan,
            'laporanTerbaru' => $laporanTerbaru,
        ]);
    }
}
