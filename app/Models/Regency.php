<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regency extends Model
{
    protected $table = 'regencies';
    public $timestamps = false;
    protected $fillable = ['id', 'province_id', 'name'];

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function districts()
    {
        return $this->hasMany(District::class, 'regency_id');
    }
}
