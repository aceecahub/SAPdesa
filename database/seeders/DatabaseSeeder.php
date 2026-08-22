<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DusunSeeder::class,
            RwSeeder::class,
            KategoriSeeder::class,
            UserSeeder::class,
        ]);
    }
}