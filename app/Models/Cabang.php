<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $table = 'cabang';

    protected $fillable = [
        'wilayah_id',
        'code',
        'name',
        'description',
        'alamat',
        'maps_url',
        'pimpinan_nama',
        'no_wa',
        'has_gelombang',
        'gelombang_hari',
        'gelombang_jam',
        'gelombang_ustadz',
        'ketua_pemuda',
        'sekretaris_pemuda',
        'bendahara_pemuda',
        'no_wa_pemuda',
        'mta_uuid',
        'mta_last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'mta_last_synced_at' => 'datetime',
        ];
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function pemuda()
    {
        return $this->hasMany(Pemuda::class, 'cabang_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'cabang_id');
    }

    public function kegiatanPresensi()
    {
        return $this->hasMany(KegiatanPresensi::class, 'cabang_id');
    }

    /**
     * Seluruh riwayat penugasan Guru Daerah Muda (GDM) di cabang ini
     */
    public function penugasanGdm()
    {
        return $this->hasMany(GdmPenugasan::class, 'cabang_id')->orderBy('tahun', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Penugasan Guru Daerah Muda (GDM) yang saat ini aktif di cabang ini
     */
    public function penugasanGdmAktif()
    {
        return $this->hasMany(GdmPenugasan::class, 'cabang_id')->where('status', 'aktif')->orderBy('tahun', 'desc');
    }

    /**
     * Kader Guru Daerah Muda (GDM) yang berasal dari cabang ini
     */
    public function gdmAsal()
    {
        return $this->hasMany(GuruDaerahMuda::class, 'cabang_id');
    }

    /**
     * Ambil cabang dengan join data wilayah
     */
    public static function getWithWilayah()
    {
        return static::with('wilayah')
            ->orderBy('wilayah_id', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();
    }
}
