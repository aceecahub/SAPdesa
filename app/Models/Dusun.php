<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dusun extends Model
{
    protected $table = 'dusun';
    protected $fillable = ['nama', 'jumlah_penduduk', 'nama_kepala'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class);
    }
}