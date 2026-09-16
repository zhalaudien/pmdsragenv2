<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemudaSkill extends Model
{
    protected $table = 'pemuda_skills';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'pemuda_id',
        'skill_id',
        'level',
    ];

    public function pemuda()
    {
        return $this->belongsTo(Pemuda::class, 'pemuda_id');
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }
}
