<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subkategori extends Model
{
    protected $table = 'subkategori';
    protected $fillable = ['nama', 'slug'];

    public function laporan()
    {
        return $this->hasMany(Laporan::class);
    }
}
