<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResellerPricing extends Model
{
    protected $table = 'reseller_pricing';

    protected $fillable = [
        'user_id',
        'tld_pricing_id',
        'register_price',
        'renew_price',
        'transfer_price',
    ];

    protected $casts = [
        'register_price' => 'decimal:4',
        'renew_price' => 'decimal:4',
        'transfer_price' => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tld(): BelongsTo
    {
        return $this->belongsTo(TldPricing::class, 'tld_pricing_id');
    }
}
