<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subkategori extends Model
{
    protected $table = 'subkategori';
    protected $fillable = ['kategori_id', 'nama', 'slug'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class);
    }
}
