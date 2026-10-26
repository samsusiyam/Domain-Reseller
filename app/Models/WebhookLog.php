<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    protected $table = 'webhook_logs';

    protected $fillable = [
        'webhook_id',
        'event_name',
        'payload',
        'response_code',
        'response_body',
        'execution_time_ms',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_code' => 'integer',
        'execution_time_ms' => 'integer',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class, 'webhook_id');
    }
}
