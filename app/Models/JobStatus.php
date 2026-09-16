<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    protected $table = 'job_statuses';
    protected $fillable = ['id', 'name', 'description'];
}
