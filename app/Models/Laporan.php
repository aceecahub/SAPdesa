<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laporan extends Model
{
    use SoftDeletes;

    protected $table = 'laporan';

    protected $fillable = [
        'judul', 'isi', 'kategori_id', 'subkategori_id', 'rw_id', 'user_id',
        'tanggal', 'lampiran', 'status', 'respon_admin'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function rw()
    {
        return $this->belongsTo(RW::class);
    }

    public function subkategori()
    {
        return $this->belongsTo(Subkategori::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tindakLanjut()
    {
        return $this->hasMany(TindakLanjut::class);
    }

    public function scopeForRw($query, $rwId)
    {
        return $query->where('rw_id', $rwId);
    }

    public function scopeForDusun($query, $dusunId)
    {
        return $query->whereHas('rw', function ($q) use ($dusunId) {
            $q->where('dusun_id', $dusunId);
        });
    }
}