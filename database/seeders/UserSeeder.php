<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Dusun;
use App\Models\RW;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $dusun = Dusun::all()->keyBy('nama');
        $defaultPasswordEnc = Crypt::encryptString('password');

        User::create([
            'name' => 'Superadmin',
            'email' => 'superadmin@sapdesa.test',
            'password' => Hash::make('password'),
            'password_plain' => $defaultPasswordEnc,
            'role' => 'superadmin',
            'dusun_id' => null,
            'rw_id' => null,
        ]);

        User::create([
            'name' => 'Admin Desa',
            'email' => 'admin@sapdesa.test',
            'password' => Hash::make('password'),
            'password_plain' => $defaultPasswordEnc,
            'role' => 'admin',
            'dusun_id' => null,
            'rw_id' => null,
        ]);

        foreach (['Dusun 1', 'Dusun 2', 'Dusun 3', 'Dusun 4'] as $i => $nama) {
            if (isset($dusun[$nama])) {
                User::create([
                    'name' => "Kepala $nama",
                    'email' => "kadus" . ($i + 1) . "@gmail.com",
                    'password' => Hash::make('password'),
                    'password_plain' => $defaultPasswordEnc,
                    'role' => 'kepala_dusun',
                    'dusun_id' => $dusun[$nama]->id,
                    'rw_id' => null,
                ]);
            }
        }

        // Seeder Akun Ketua RW
        $rwList = RW::with('dusun')->get();
        foreach ($rwList as $rw) {
            $rwNomorFormatted = str_pad($rw->nomor, 2, '0', STR_PAD_LEFT);
            $dusunNameClean = strtolower(str_replace(' ', '', $rw->dusun->nama));
            
            User::create([
                'name' => "Ketua RW {$rwNomorFormatted} ({$rw->dusun->nama})",
                'email' => "rw{$rwNomorFormatted}.{$dusunNameClean}@gmail.com",
                'password' => Hash::make('password'),
                'password_plain' => $defaultPasswordEnc,
                'role' => 'ketua_rw',
                'dusun_id' => null,
                'rw_id' => $rw->id,
            ]);
        }
    }
}