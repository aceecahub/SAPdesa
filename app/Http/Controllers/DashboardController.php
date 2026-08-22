<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Dusun;
use App\Models\RW;
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

        // Scope berdasarkan role
        if ($user->isKetuaRw()) {
            $query->where('rw_id', $user->rw_id);
        } elseif ($user->isKepalaDusun()) {
            $query->whereHas('rw', function ($q) use ($user) {
                $q->where('dusun_id', $user->dusun_id);
            });
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
        if (!$user->isKetuaRw() && !$user->isKepalaDusun()) {
            $perDusun = Laporan::join('rw', 'laporan.rw_id', '=', 'rw.id')
                ->join('dusun', 'rw.dusun_id', '=', 'dusun.id')
                ->select('dusun.nama', DB::raw('count(*) as total'))
                ->groupBy('dusun.nama')
                ->pluck('total', 'nama')
                ->toArray();
        }

        // Per RW
        $perRw = (clone $query)
            ->join('rw', 'laporan.rw_id', '=', 'rw.id')
            ->join('dusun', 'rw.dusun_id', '=', 'dusun.id')
            ->select(
                DB::raw("CONCAT('RW ', LPAD(rw.nomor, 2, '0'), ' (', dusun.nama, ')') as nama_rw"),
                DB::raw('count(*) as total')
            )
            ->groupBy('rw.id', 'rw.nomor', 'dusun.nama')
            ->pluck('total', 'nama_rw')
            ->toArray();

        // Rincian Dusun & RW internalnya
        $dusunListQuery = Dusun::with(['rw' => function ($q) {
            $q->orderBy('nomor');
        }]);

        if ($user->isKepalaDusun()) {
            $dusunListQuery->where('id', $user->dusun_id);
        }

        $dusunBreakdown = $dusunListQuery->get()->map(function ($dusun) {
            $rws = $dusun->rw->map(function ($rw) {
                $laporanQuery = Laporan::where('rw_id', $rw->id);
                $total = $laporanQuery->count();
                $baru = (clone $laporanQuery)->where('status', 'baru')->count();
                $diproses = (clone $laporanQuery)->where('status', 'diproses')->count();
                $selesai = (clone $laporanQuery)->where('status', 'selesai')->count();
                $ditolak = (clone $laporanQuery)->where('status', 'ditolak')->count();

                return [
                    'id' => $rw->id,
                    'nomor' => $rw->nomor,
                    'label' => 'RW ' . str_pad($rw->nomor, 2, '0', STR_PAD_LEFT),
                    'nama_ketua' => $rw->nama_ketua,
                    'foto_ketua' => $rw->foto_ketua,
                    'total' => $total,
                    'baru' => $baru,
                    'diproses' => $diproses,
                    'selesai' => $selesai,
                    'ditolak' => $ditolak,
                ];
            });

            return [
                'id' => $dusun->id,
                'nama' => $dusun->nama,
                'nama_kepala' => $dusun->nama_kepala,
                'foto_kepala' => $dusun->foto_kepala,
                'jumlah_penduduk' => $dusun->jumlah_penduduk,
                'total_laporan' => $rws->sum('total'),
                'rws' => $rws,
            ];
        });

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
            ->with(['kategori', 'subkategori', 'rw.dusun', 'user'])
            ->latest('tanggal')
            ->limit(5)
            ->get();

        // User stats (superadmin only)
        $totalUsers = $user->isSuperadmin() ? User::count() : null;
        $totalDusun = Dusun::count();
        $totalRw = RW::count();

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
                'totalRw' => $totalRw,
            ],
            'perKategori' => $perKategori,
            'perSubkategori' => $perSubkategori,
            'perDusun' => $perDusun,
            'perRw' => $perRw,
            'dusunBreakdown' => $dusunBreakdown,
            'perBulan' => $perBulan,
            'laporanTerbaru' => $laporanTerbaru,
        ]);
    }
}
