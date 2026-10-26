<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CronLog extends Model
{
    protected $table = 'cron_logs';
    public $timestamps = false;

    protected $fillable = [
        'command_name',
        'status',
        'output',
        'duration_seconds',
        'executed_at',
        'completed_at',
        'created_at',
    ];

    protected $casts = [
        'duration_seconds' => 'decimal:2',
        'executed_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }
}
