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

    /**
     * Ambil wilayah lengkap beserta cabang-cabangnya
     */
    public static function getWithCabang(?int $wilayahId = null, ?int $cabangId = null)
    {
        $query = static::orderBy('id', 'ASC');

        if (!empty($wilayahId)) {
            $query->where('id', $wilayahId);
        }

        $wilayahList = $query->get();

        foreach ($wilayahList as $w) {
            $cQuery = Cabang::where('wilayah_id', $w->id)->orderBy('name', 'ASC');
            if (!empty($cabangId)) {
                $cQuery->where('id', $cabangId);
            }
            $w->cabang = $cQuery->get();
        }

        return $wilayahList;
    }
}
