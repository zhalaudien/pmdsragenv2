<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendidikan extends Model
{
    protected $table = 'pendidikan';

    protected $fillable = [
        'pemuda_id',
        'education_level_id',
        'school_name',
        'major',
        'education_status',
        'graduation_year',
    ];

    public function pemuda()
    {
        return $this->belongsTo(Pemuda::class, 'pemuda_id');
    }

    public function educationLevel()
    {
        return $this->belongsTo(EducationLevel::class, 'education_level_id');
    }
}
