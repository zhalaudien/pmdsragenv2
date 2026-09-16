<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
    ];

    protected function casts(): array
    {
        return [
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
}
