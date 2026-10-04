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

    public function getSumberAlamatLabelAttribute(): string
    {
        if ($this->pemuda_id) {
            return 'Tersinkron Pemuda';
        }
        if (!empty($this->mta_warga_uuid)) {
            return 'Tersinkron Warga MTA';
        }
        return 'Manual';
    }

    /**
     * Sinkronkan alamat domisili GDM dari sumber asalnya (Data Pemuda atau Warga MTA)
     */
    public function syncAlamatFromSource(?\App\Services\MtaApiService $mtaApiService = null): bool
    {
        $changed = false;

        // 1. Cek dari Data Pemuda jika memiliki pemuda_id atau mta_warga_uuid
        $pemuda = null;
        if ($this->pemuda_id) {
            $pemuda = Pemuda::with(['alamat.village', 'alamat.district', 'alamat.regency'])->find($this->pemuda_id);
        }

        if (!$pemuda && !empty($this->mta_warga_uuid)) {
            $pemuda = Pemuda::with(['alamat.village', 'alamat.district', 'alamat.regency'])
                ->where('mta_warga_uuid', $this->mta_warga_uuid)
                ->first();
            if ($pemuda) {
                $this->pemuda_id = $pemuda->id;
                $changed = true;
            }
        }

        if ($pemuda && $pemuda->alamat) {
            $alamatLengkap = $pemuda->alamat->alamat_lengkap;
            if (!empty($alamatLengkap) && $this->alamat !== $alamatLengkap) {
                $this->alamat = $alamatLengkap;
                $changed = true;
            }
            if (empty($this->tempat_lahir) && !empty($pemuda->birth_place)) {
                $this->tempat_lahir = $pemuda->birth_place;
                $changed = true;
            }
            if (empty($this->tanggal_lahir) && !empty($pemuda->birth_date)) {
                $this->tanggal_lahir = $pemuda->birth_date;
                $changed = true;
            }
            if (empty($this->no_wa) && !empty($pemuda->phone)) {
                $this->no_wa = $pemuda->phone;
                $changed = true;
            }
            if (empty($this->cabang_id) && !empty($pemuda->cabang_id)) {
                $this->cabang_id = $pemuda->cabang_id;
                $changed = true;
            }
            if (empty($this->mta_warga_uuid) && !empty($pemuda->mta_warga_uuid)) {
                $this->mta_warga_uuid = $pemuda->mta_warga_uuid;
                $changed = true;
            }

            if ($changed) {
                $this->save();
            }
            return $changed;
        }

        // 2. Jika tidak ada di Pemuda namun memiliki mta_warga_uuid (Warga MTA)
        if (!empty($this->mta_warga_uuid)) {
            $apiService = $mtaApiService ?: app(\App\Services\MtaApiService::class);
            if ($apiService->isEnabled()) {
                $res = $apiService->getWargaDetail($this->mta_warga_uuid);
                if (($res['success'] ?? false) && !empty($res['data'])) {
                    $w = $res['data'];
                    $alamatWarga = $w['alamat'] ?? ($w['alamat_lengkap'] ?? null);
                    if (!empty($alamatWarga) && $this->alamat !== $alamatWarga) {
                        $this->alamat = $alamatWarga;
                        $changed = true;
                    }
                    if (empty($this->tempat_lahir) && !empty($w['tempat_lahir'])) {
                        $this->tempat_lahir = $w['tempat_lahir'];
                        $changed = true;
                    }
                    $noWa = $w['telepon'] ?? ($w['hp'] ?? ($w['no_wa'] ?? null));
                    if (empty($this->no_wa) && !empty($noWa)) {
                        $this->no_wa = $noWa;
                        $changed = true;
                    }
                    if ($changed) {
                        $this->save();
                    }
                    return $changed;
                }
            }
        }

        return false;
    }
}
