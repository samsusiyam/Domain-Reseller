<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    protected $table = 'webhooks';

    protected $fillable = [
        'user_id',
        'target_url',
        'secret_key',
        'subscribed_events',
        'is_active',
        'failure_count',
        'last_triggered_at',
    ];

    protected $casts = [
        'subscribed_events' => 'array',
        'is_active' => 'boolean',
        'failure_count' => 'integer',
        'last_triggered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class, 'webhook_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isSubscribedTo(string $event): bool
    {
        return in_array($event, $this->subscribed_events ?? [], true);
    }
}
