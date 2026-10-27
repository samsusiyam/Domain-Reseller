<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'uuid',
        'transaction_number',
        'user_id',
        'invoice_id',
        'gateway',
        'gateway_transaction_id',
        'type',
        'amount',
        'currency',
        'exchange_rate',
        'amount_in_usd',
        'fee',
        'status',
        'payment_details',
        'verified_by_admin_id',
        'verified_at',
        'payment_method',
        'reference_id',
        'description',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'amount_in_usd' => 'decimal:4',
        'fee' => 'decimal:4',
        'payment_details' => 'array',
        'verified_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Transaction $transaction) {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = 'TXN-' . strtoupper(date('Ymd')) . '-' . strtoupper(Str::random(6));
            }
            if (empty($transaction->currency)) {
                $transaction->currency = 'USD';
            }
            if (empty($transaction->exchange_rate)) {
                $transaction->exchange_rate = 1.000000;
            }
            if (!isset($transaction->amount_in_usd) || $transaction->amount_in_usd === null) {
                $transaction->amount_in_usd = (float) $transaction->amount;
            }
            if (empty($transaction->gateway)) {
                $transaction->gateway = 'manual';
            }
        });
    }

    public function getPaymentMethodAttribute(): string
    {
        return $this->gateway ?? 'manual';
    }

    public function setPaymentMethodAttribute($value): void
    {
        $valid = ['manual', 'bkash', 'stripe', 'binance', 'balance'];
        $this->attributes['gateway'] = in_array($value, $valid) ? $value : 'manual';
    }

    public function getReferenceIdAttribute(): ?string
    {
        return $this->transaction_number ?? $this->gateway_transaction_id;
    }

    public function setReferenceIdAttribute($value): void
    {
        $this->attributes['gateway_transaction_id'] = $value;
    }

    public function getDescriptionAttribute(): string
    {
        return $this->payment_details['reason'] ?? $this->payment_details['notes'] ?? 'Transaction';
    }

    public function setDescriptionAttribute($value): void
    {
        $details = $this->payment_details ?? [];
        $details['reason'] = $value;
        $this->payment_details = $details;
    }

    public function setNotesAttribute($value): void
    {
        $details = $this->payment_details ?? [];
        $details['notes'] = $value;
        $this->payment_details = $details;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function verifiedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_admin_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('type', 'debit');
    }
}
