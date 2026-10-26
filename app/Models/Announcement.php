<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'target_audience',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Announcement $announcement) {
            if (empty($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title) . '-' . Str::random(5);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('published_at', '<=', now());
    }

    public function scopeForAudience(Builder $query, string $role): Builder
    {
        return $query->where(function (Builder $q) use ($role) {
            $q->where('target_audience', 'all')
              ->orWhere('target_audience', $role === 'reseller' ? 'resellers' : 'clients');
        });
    }
}
