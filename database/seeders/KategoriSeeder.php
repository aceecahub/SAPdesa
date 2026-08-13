<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        Kategori::insert([
            ['nama' => 'Aspirasi', 'slug' => 'aspirasi'],
            ['nama' => 'Pengaduan', 'slug' => 'pengaduan'],
            ['nama' => 'Kritik/Saran', 'slug' => 'kritik-saran'],
        ]);
    }
}