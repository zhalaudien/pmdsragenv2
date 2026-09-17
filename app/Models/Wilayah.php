<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $fillable = [
        'code',
        'name',
        'description',
        'mta_uuid',
        'mta_code',
    ];

    public function cabang()
    {
        return $this->hasMany(Cabang::class, 'wilayah_id')->orderBy('name', 'ASC');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'wilayah_id');
    }

    public function pemuda()
    {
        return $this->hasManyThrough(Pemuda::class, Cabang::class, 'wilayah_id', 'cabang_id');
    }

    /**
     * Ambil wilayah lengkap beserta cabang-cabangnya
     */
    public static function getWithCabang(?int $wilayahId = null, ?int $cabangId = null)
    {
        $query = static::with(['cabang' => function ($q) use ($cabangId) {
            if (!empty($cabangId)) {
                $q->where('id', $cabangId);
            }
            $q->orderBy('name', 'ASC');
        }])->orderBy('id', 'ASC');

        if (!empty($wilayahId)) {
            $query->where('id', $wilayahId);
        }

        return $query->get();
    }
}
