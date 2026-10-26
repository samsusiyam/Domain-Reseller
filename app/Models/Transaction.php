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
        });
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
