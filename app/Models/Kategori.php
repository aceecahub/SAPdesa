<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $fillable = ['nama', 'slug'];

    public function laporan()
    {
        return $this->hasMany(Laporan::class);
    }

    public function subkategori()
    {
        return $this->hasMany(Subkategori::class);
    }
}