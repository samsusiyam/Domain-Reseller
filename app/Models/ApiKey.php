<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $table = 'api_keys';

    protected $fillable = [
        'user_id',
        'key_name',
        'api_key',
        'api_secret',
        'ip_whitelist',
        'allowed_endpoints',
        'status',
        'last_used_at',
    ];

    protected $casts = [
        'api_secret' => 'encrypted',
        'ip_whitelist' => 'array',
        'allowed_endpoints' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isIpWhitelisted(string $ip): bool
    {
        if (empty($this->ip_whitelist)) {
            return true;
        }
        return in_array($ip, $this->ip_whitelist, true);
    }

    public static function generateForUser(User $user, string $name, ?array $ips = null): array
    {
        $rawSecret = 'sk_' . Str::random(40);
        $rawKey = 'pk_' . Str::random(32);

        $apiKey = static::create([
            'user_id' => $user->id,
            'key_name' => $name,
            'api_key' => hash('sha256', $rawKey),
            'api_secret' => $rawSecret,
            'ip_whitelist' => $ips,
            'status' => 'active',
        ]);

        return [
            'model' => $apiKey,
            'public_key' => $rawKey,
            'secret_key' => $rawSecret,
        ];
    }
}
