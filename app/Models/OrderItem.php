<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'domain_id',
        'item_type',
        'domain_name',
        'years',
        'unit_price',
        'total_price',
        'registrar_id',
        'registrar_cost',
        'registrar_payload',
        'registrar_response',
        'status',
    ];

    protected $casts = [
        'years' => 'integer',
        'unit_price' => 'decimal:4',
        'total_price' => 'decimal:4',
        'registrar_cost' => 'decimal:4',
        'registrar_payload' => 'array',
        'registrar_response' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(RegistrarModule::class, 'registrar_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
