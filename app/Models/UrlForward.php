<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UrlForward extends Model
{
    protected $table = 'url_forwards';

    protected $fillable = [
        'domain_id',
        'source_subdomain',
        'destination_url',
        'redirect_type',
        'page_title',
        'meta_description',
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

    public function isMasked(): bool
    {
        return $this->redirect_type === 'masked';
    }
}
