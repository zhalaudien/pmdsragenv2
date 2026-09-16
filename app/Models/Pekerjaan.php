<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pekerjaan extends Model
{
    protected $table = 'pekerjaan';

    protected $fillable = [
        'pemuda_id',
        'job_status_id',
        'job_title',
        'company_name',
        'business_field',
        'business_name',
        'business_address',
        'business_contact',
        'business_social',
    ];

    public function pemuda()
    {
        return $this->belongsTo(Pemuda::class, 'pemuda_id');
    }

    public function jobStatus()
    {
        return $this->belongsTo(JobStatus::class, 'job_status_id');
    }
}
