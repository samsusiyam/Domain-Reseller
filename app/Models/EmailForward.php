<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailForward extends Model
{
    protected $table = 'email_forwards';

    protected $fillable = [
        'domain_id',
        'source_prefix',
        'destination_email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected function sourceEmail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->source_prefix . '@' . ($this->domain ? $this->domain->domain_name : '')
        );
    }
}
