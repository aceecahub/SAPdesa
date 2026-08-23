<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'password_plain', 'role', 'dusun_id', 'rw_id'])]
#[Hidden(['password', 'password_plain', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function dusun()
    {
        return $this->belongsTo(Dusun::class);
    }

    public function rw()
    {
        return $this->belongsTo(RW::class);
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class);
    }

    public function tindakLanjut()
    {
        return $this->hasMany(TindakLanjut::class);
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKepalaDusun(): bool
    {
        return $this->role === 'kepala_dusun';
    }

    public function isKetuaRw(): bool
    {
        return $this->role === 'ketua_rw';
    }

    public function getDecryptedPassword(): ?string
    {
        if (empty($this->password_plain)) {
            return null;
        }
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($this->password_plain);
        } catch (\Throwable $e) {
            return null;
        }
    }
}