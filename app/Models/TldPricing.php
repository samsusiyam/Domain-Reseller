<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TldPricing extends Model
{
    use HasFactory;

    protected $table = 'tld_pricing';

    protected $fillable = [
        'tld',
        'registrar_id',
        'cost_register',
        'cost_renew',
        'cost_transfer',
        'client_register',
        'client_renew',
        'client_transfer',
        'default_reseller_register',
        'default_reseller_renew',
        'default_reseller_transfer',
        'min_years',
        'max_years',
        'is_active',
        'dns_management_available',
        'email_forwarding_available',
        'id_protection_available',
        'id_protection_cost',
        'epp_code_required',
        'grace_period_days',
        'redemption_period_days',
        'redemption_fee',
    ];

    protected $casts = [
        'cost_register' => 'decimal:4',
        'cost_renew' => 'decimal:4',
        'cost_transfer' => 'decimal:4',
        'client_register' => 'decimal:4',
        'client_renew' => 'decimal:4',
        'client_transfer' => 'decimal:4',
        'default_reseller_register' => 'decimal:4',
        'default_reseller_renew' => 'decimal:4',
        'default_reseller_transfer' => 'decimal:4',
        'id_protection_cost' => 'decimal:4',
        'redemption_fee' => 'decimal:4',
        'min_years' => 'integer',
        'max_years' => 'integer',
        'grace_period_days' => 'integer',
        'redemption_period_days' => 'integer',
        'is_active' => 'boolean',
        'dns_management_available' => 'boolean',
        'email_forwarding_available' => 'boolean',
        'id_protection_available' => 'boolean',
        'epp_code_required' => 'boolean',
    ];

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(RegistrarModule::class, 'registrar_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'tld', 'tld');
    }

    public function resellerPricing(): HasMany
    {
        return $this->hasMany(ResellerPricing::class, 'tld_pricing_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getPriceForUser(?User $user, string $action = 'register'): float
    {
        if ($user && $user->isReseller()) {
            $custom = $this->resellerPricing()->where('user_id', $user->id)->first();
            if ($custom) {
                return match ($action) {
                    'renew' => (float) $custom->renew_price,
                    'transfer' => (float) $custom->transfer_price,
                    default => (float) $custom->register_price,
                };
            }

            return match ($action) {
                'renew' => (float) $this->default_reseller_renew,
                'transfer' => (float) $this->default_reseller_transfer,
                default => (float) $this->default_reseller_register,
            };
        }

        return match ($action) {
            'renew' => (float) $this->client_renew,
            'transfer' => (float) $this->client_transfer,
            default => (float) $this->client_register,
        };
    }
}
