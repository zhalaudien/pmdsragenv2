<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemudaInterest extends Model
{
    protected $table = 'pemuda_interests';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'pemuda_id',
        'interest_id',
    ];

    public function pemuda()
    {
        return $this->belongsTo(Pemuda::class, 'pemuda_id');
    }

    public function interest()
    {
        return $this->belongsTo(Interest::class, 'interest_id');
    }
}
