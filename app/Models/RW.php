<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RW extends Model
{
    protected $table = 'rw';
    protected $fillable = ['nomor', 'nama_ketua', 'foto_ketua', 'dusun_id'];

    /**
     * RW menginduk ke Dusun
     */
    public function dusun()
    {
        return $this->belongsTo(Dusun::class);
    }

    /**
     * Ketua RW (user dengan role ketua_rw yang terikat ke RW ini)
     */
    public function users()
    {
        return $this->hasMany(User::class, 'rw_id');
    }

    /**
     * Laporan yang masuk dari RW ini
     */
    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'rw_id');
    }

    /**
     * Label RW yang mudah dibaca: "RW 01 – Dusun Cikaret"
     */
    public function getLabelAttribute(): string
    {
        $dusunNama = $this->dusun?->nama ?? '-';
        return 'RW ' . str_pad($this->nomor, 2, '0', STR_PAD_LEFT) . ' – Dusun ' . $dusunNama;
    }
}
