<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $table = 'interests';
    public $timestamps = false;
    protected $fillable = ['id', 'name', 'description'];

    public function pemuda()
    {
        return $this->belongsToMany(Pemuda::class, 'pemuda_interests', 'interest_id', 'pemuda_id');
    }
}
