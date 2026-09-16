<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtaSyncQueue extends Model
{
    protected $table = 'mta_sync_queue';

    protected $fillable = [
        'pemuda_id',
        'cabang_id',
        'status',
        'result',
        'message',
        'mta_warga_uuid',
        'attempts',
        'created_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    public function pemuda()
    {
        return $this->belongsTo(Pemuda::class, 'pemuda_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getQueueSummary(): array
    {
        $total = static::count();
        $pending = static::where('status', 'pending')->count();
        $processing = static::where('status', 'processing')->count();
        $completed = static::where('status', 'completed')->count();
        $failed = static::where('status', 'failed')->count();

        $verified = static::where('result', 'verified')->count();
        $pendingUnverified = static::where('status', 'completed')->where('result', 'pending')->count();

        $remaining = $pending + $processing;
        $processed = $completed + $failed;
        $percent = $total > 0 ? round(($processed / $total) * 100, 1) : 0;

        $estimatedSeconds = ceil($remaining * 1.5);
        $minutes = floor($estimatedSeconds / 60);
        $seconds = $estimatedSeconds % 60;
        $estimatedFormatted = $minutes > 0
            ? "{$minutes} mnt {$seconds} dtk"
            : "{$seconds} dtk";

        return [
            'total'               => $total,
            'pending'             => $pending,
            'processing'          => $processing,
            'completed'           => $completed,
            'failed'              => $failed,
            'processed'           => $processed,
            'remaining'           => $remaining,
            'verified'            => $verified,
            'pending_unverified'  => $pendingUnverified,
            'percent'             => $percent,
            'rate_per_minute'     => 40,
            'delay_seconds'       => 1.5,
            'delay_ms'            => 1500,
            'estimated_seconds'   => $estimatedSeconds,
            'estimated_formatted' => $estimatedFormatted,
        ];
    }

    public static function getNextPendingItem()
    {
        return static::with(['pemuda', 'cabang'])
            ->where('status', 'pending')
            ->orderBy('id', 'ASC')
            ->first();
    }

    public static function getRecentProcessed(int $limit = 20)
    {
        return static::with(['pemuda', 'cabang'])
            ->whereIn('status', ['completed', 'failed'])
            ->orderBy('processed_at', 'DESC')
            ->limit($limit)
            ->get();
    }
}
