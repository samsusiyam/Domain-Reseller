<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequestLog extends Model
{
    public $timestamps = false;

    protected $table = 'api_request_logs';

    protected $fillable = [
        'user_id',
        'api_key_id',
        'method',
        'path',
        'ip_address',
        'status_code',
        'duration_ms',
        'request_headers',
        'payload',
        'response_body',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'status_code' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
