<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisasi extends Model
{
    protected $table = 'organisasi';

    protected $fillable = [
        'pemuda_id',
        'organization_name',
        'description',
    ];

    public function pemuda()
    {
        return $this->belongsTo(Pemuda::class, 'pemuda_id');
    }
}
