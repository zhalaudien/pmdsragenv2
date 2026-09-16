<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $table = 'skills';
    public $timestamps = false;
    protected $fillable = ['id', 'name', 'description'];

    public function pemuda()
    {
        return $this->belongsToMany(Pemuda::class, 'pemuda_skills', 'skill_id', 'pemuda_id')->withPivot('level');
    }
}
