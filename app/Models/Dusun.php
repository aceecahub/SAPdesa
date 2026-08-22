<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dusun extends Model
{
    protected $table = 'dusun';
    protected $fillable = ['nama', 'jumlah_penduduk', 'nama_kepala', 'foto_kepala'];

    public function rw()
    {
        return $this->hasMany(RW::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}