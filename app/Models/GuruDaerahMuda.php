<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuruDaerahMuda extends Model
{
    protected $table = 'guru_daerah_muda';

    protected $fillable = [
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'cabang_id',
        'alamat',
        'no_wa',
        'status',
        'sumber_data',
        'pemuda_id',
        'mta_warga_uuid',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date:Y-m-d',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function pemuda(): BelongsTo
    {
        return $this->belongsTo(Pemuda::class, 'pemuda_id');
    }

    public function penugasan(): HasMany
    {
        return $this->hasMany(GdmPenugasan::class, 'gdm_id')->orderBy('tahun', 'desc')->orderBy('id', 'desc');
    }

    public function penugasanAktif(): HasMany
    {
        return $this->hasMany(GdmPenugasan::class, 'gdm_id')->where('status', 'aktif')->orderBy('tahun', 'desc');
    }

    public function getUsiaAttribute(): ?int
    {
        if (!$this->tanggal_lahir) {
            return null;
        }

        return Carbon::parse($this->tanggal_lahir)->age;
    }

    public function getTtlAttribute(): string
    {
        $parts = [];
        if ($this->tempat_lahir) {
            $parts[] = $this->tempat_lahir;
        }
        if ($this->tanggal_lahir) {
            $parts[] = Carbon::parse($this->tanggal_lahir)->translatedFormat('d F Y');
        }

        return !empty($parts) ? implode(', ', $parts) : '-';
    }

    public function getWaLinkAttribute(): ?string
    {
        if (empty($this->no_wa)) {
            return null;
        }

        $cleanPhone = preg_replace('/\D/', '', $this->no_wa);
        $cleanPhone = preg_replace('/^0/', '62', $cleanPhone);

        return 'https://wa.me/' . $cleanPhone;
    }
}
