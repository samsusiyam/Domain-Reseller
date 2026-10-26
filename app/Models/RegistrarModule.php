<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrarModule extends Model
{
    use HasFactory;

    protected $table = 'registrar_modules';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'is_default',
        'environment',
        'config',
        'balance',
        'currency',
        'last_balance_sync_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'config' => 'encrypted:json',
        'balance' => 'decimal:4',
        'last_balance_sync_at' => 'datetime',
    ];

    public function tlds(): HasMany
    {
        return $this->hasMany(TldPricing::class, 'registrar_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'registrar_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function isSandbox(): bool
    {
        return $this->environment === 'sandbox';
    }
}
