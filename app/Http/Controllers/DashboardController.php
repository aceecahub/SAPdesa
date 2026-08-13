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

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $totalLaporan,
                'baru' => $statusCounts['baru'] ?? 0,
                'diproses' => $statusCounts['diproses'] ?? 0,
                'selesai' => $statusCounts['selesai'] ?? 0,
                'ditolak' => $statusCounts['ditolak'] ?? 0,
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
