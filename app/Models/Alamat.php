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

    /**
     * Format alamat domisili lengkap beserta RT/RW, Dusun, Desa, Kecamatan, dan Kabupaten
     */
    public function getAlamatLengkapAttribute(): string
    {
        $parts = [];
        if (!empty($this->address_detail)) {
            $parts[] = trim($this->address_detail);
        }
        if (!empty($this->dusun)) {
            $parts[] = 'Dk. ' . trim($this->dusun);
        }
        $rtRw = [];
        if (!empty($this->rt)) {
            $rtRw[] = 'RT ' . trim($this->rt);
        }
        if (!empty($this->rw)) {
            $rtRw[] = 'RW ' . trim($this->rw);
        }
        if (!empty($rtRw)) {
            $parts[] = implode('/', $rtRw);
        }
        if (!empty($this->village?->name)) {
            $parts[] = 'Desa ' . trim($this->village->name);
        }
        if (!empty($this->district?->name)) {
            $parts[] = 'Kec. ' . trim($this->district->name);
        }
        if (!empty($this->regency?->name)) {
            $parts[] = trim($this->regency->name);
        }

        return !empty($parts) ? implode(', ', $parts) : ($this->address_detail ?: '-');
    }
}
