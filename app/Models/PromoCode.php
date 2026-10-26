<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    protected $table = 'promo_codes';

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_uses',
        'current_uses',
        'user_usage_limit',
        'valid_from',
        'valid_to',
        'applicable_tlds',
        'applicable_actions',
        'min_order_amount',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'user_usage_limit' => 'integer',
        'applicable_tlds' => 'array',
        'applicable_actions' => 'array',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'promo_code_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
            });
    }

    public function isValidFor(float $subtotal, User $user, ?string $tld = null, ?string $action = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_to && $this->valid_to->isPast()) {
            return false;
        }

        if ($this->max_uses > 0 && $this->current_uses >= $this->max_uses) {
            return false;
        }

        if ($subtotal < (float) $this->min_order_amount) {
            return false;
        }

        if (!empty($this->applicable_tlds) && $tld && !in_array($tld, $this->applicable_tlds, true)) {
            return false;
        }

        if (!empty($this->applicable_actions) && $action && !in_array($action, $this->applicable_actions, true)) {
            return false;
        }

        $userUses = $this->orders()->where('user_id', $user->id)->count();
        if ($userUses >= $this->user_usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            return round(($subtotal * (float) $this->discount_value) / 100, 2);
        }
        return min($subtotal, (float) $this->discount_value);
    }
}
