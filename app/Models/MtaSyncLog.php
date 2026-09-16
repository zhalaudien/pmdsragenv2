<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtaSyncLog extends Model
{
    protected $table = 'mta_sync_logs';
    public $timestamps = false;

    protected $fillable = [
        'sync_type',
        'status',
        'total_records',
        'message',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function log(string $type, string $status, int $totalRecords = 0, ?string $message = null, ?int $userId = null): self
    {
        return static::create([
            'sync_type'     => $type,
            'status'        => $status,
            'total_records' => $totalRecords,
            'message'       => $message,
            'created_by'    => $userId ?? auth()->id(),
            'created_at'    => now(),
        ]);
    }

    public static function getRecentLogs(int $limit = 10)
    {
        return static::with('user')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
    }
}
