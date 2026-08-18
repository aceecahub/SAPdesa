<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Dusun;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $dusun = Dusun::all()->keyBy('nama');

        User::create([
            'name' => 'Superadmin',
            'email' => 'superadmin@sapdesa.test',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'dusun_id' => null,
        ]);

        User::create([
            'name' => 'Admin Desa',
            'email' => 'admin@sapdesa.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'dusun_id' => null,
        ]);

        foreach (['Dusun 1', 'Dusun 2', 'Dusun 3', 'Dusun 4'] as $i => $nama) {
            User::create([
                'name' => "Kepala $nama",
                'email' => "kadus" . ($i + 1) . "@gmail.com",
                'password' => Hash::make('password'),
                'role' => 'kepala_dusun',
                'dusun_id' => $dusun[$nama]->id,
            ]);
        }

    }
}