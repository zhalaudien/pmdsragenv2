<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiDetail extends Model
{
    protected $table = 'presensi_detail';

    protected $fillable = [
        'kegiatan_presensi_id',
        'pemuda_id',
        'status_kehadiran',
        'keterangan',
        'waktu_presensi',
        'device_info',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'waktu_presensi' => 'datetime',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    public function kegiatanPresensi()
    {
        return $this->belongsTo(KegiatanPresensi::class, 'kegiatan_presensi_id');
    }

    public function pemuda()
    {
        return $this->belongsTo(Pemuda::class, 'pemuda_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
