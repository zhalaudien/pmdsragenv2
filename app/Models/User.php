<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role_id',
        'wilayah_id',
        'cabang_id',
        'last_login',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'   => 'hashed',
            'status'     => 'integer',
            'last_login' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(UserRole::class, 'role_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function getRoleNameAttribute(): string
    {
        return $this->role->name ?? '';
    }

    public function isSuperadmin(): bool
    {
        return $this->role_name === 'superadmin' || $this->role_id === 1;
    }

    public function kegiatanPresensi()
    {
        return $this->hasMany(KegiatanPresensi::class, 'created_by');
    }

    public function presensiDetails()
    {
        return $this->hasMany(PresensiDetail::class, 'created_by');
    }
}
