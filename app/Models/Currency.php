<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_primary',
        'is_active',
        'decimal_places',
        'symbol_position',
        'last_updated_at',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'decimal_places' => 'integer',
        'last_updated_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'currency_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    public function format(float $amountInUsd): string
    {
        $converted = $amountInUsd * (float) $this->exchange_rate;
        $formatted = number_format($converted, $this->decimal_places);

        return $this->symbol_position === 'before'
            ? $this->symbol . ' ' . $formatted
            : $formatted . ' ' . $this->symbol;
    }
}
