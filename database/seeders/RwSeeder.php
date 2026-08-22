<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dusun;
use App\Models\RW;

class RwSeeder extends Seeder
{
    public function run(): void
    {
        $dusunList = Dusun::all();

        foreach ($dusunList as $dusun) {
            for ($i = 1; $i <= 2; $i++) {
                RW::firstOrCreate([
                    'dusun_id' => $dusun->id,
                    'nomor' => $i,
                ], [
                    'nama_ketua' => 'Ketua RW ' . str_pad($i, 2, '0', STR_PAD_LEFT) . ' (' . $dusun->nama . ')',
                ]);
            }
        }
    }
}
