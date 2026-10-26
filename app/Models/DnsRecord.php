<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DnsRecord extends Model
{
    protected $table = 'dns_records';

    protected $fillable = [
        'domain_id',
        'type',
        'host',
        'value',
        'priority',
        'port',
        'weight',
        'ttl',
        'registrar_record_id',
    ];

    protected $casts = [
        'priority' => 'integer',
        'port' => 'integer',
        'weight' => 'integer',
        'ttl' => 'integer',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', strtoupper($type));
    }
}
