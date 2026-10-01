<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GdmPenugasan extends Model
{
    protected $table = 'gdm_penugasan';

    protected $fillable = [
        'gdm_id',
        'tahun',
        'cabang_id',
        'hari_kajian',
        'jam_kajian',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
        ];
    }

    public function gdm(): BelongsTo
    {
        return $this->belongsTo(GuruDaerahMuda::class, 'gdm_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }
}
