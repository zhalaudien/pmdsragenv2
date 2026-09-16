<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    protected $table = 'alamat';

    protected $fillable = [
        'pemuda_id',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'dusun',
        'rt',
        'rw',
        'address_detail',
    ];

    public function pemuda()
    {
        return $this->belongsTo(Pemuda::class, 'pemuda_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regency_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id');
    }
}
