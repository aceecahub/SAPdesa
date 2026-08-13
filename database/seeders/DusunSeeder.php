<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dusun;

class DusunSeeder extends Seeder
{
    public function run(): void
    {
        Dusun::insert([
            ['nama' => 'Dusun 1', 'jumlah_penduduk' => 0, 'nama_kepala' => ''],
            ['nama' => 'Dusun 2', 'jumlah_penduduk' => 0, 'nama_kepala' => ''],
            ['nama' => 'Dusun 3', 'jumlah_penduduk' => 0, 'nama_kepala' => ''],
            ['nama' => 'Dusun 4', 'jumlah_penduduk' => 0, 'nama_kepala' => ''],
        ]);
    }
}