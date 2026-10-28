<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Domain extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'domains';

    protected $fillable = [
        'uuid',
        'user_id',
        'registrar_id',
        'domain_name',
        'tld',
        'registration_years',
        'status',
        'registrar_order_id',
        'registrar_domain_id',
        'is_locked',
        'is_private',
        'is_auto_renew',
        'nameservers',
        'auth_code',
        'registered_at',
        'expires_at',
        'next_due_date',
        'last_sync_at',
        'registrar_cost',
        'selling_price',
        'registrant_contact_id',
        'admin_contact_id',
        'tech_contact_id',
        'billing_contact_id',
    ];

    protected $casts = [
        'registration_years' => 'integer',
        'is_locked' => 'boolean',
        'is_private' => 'boolean',
        'is_auto_renew' => 'boolean',
        'nameservers' => 'array',
        'registered_at' => 'datetime',
        'expires_at' => 'datetime',
        'next_due_date' => 'date',
        'last_sync_at' => 'datetime',
        'registrar_cost' => 'decimal:4',
        'selling_price' => 'decimal:4',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Domain $domain) {
            if (empty($domain->uuid)) {
                $domain->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(RegistrarModule::class, 'registrar_id');
    }

    public function registrantContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'registrant_contact_id');
    }

    public function adminContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'admin_contact_id');
    }

    public function techContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'tech_contact_id');
    }

    public function billingContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'billing_contact_id');
    }

    public function dnsRecords(): HasMany
    {
        return $this->hasMany(DnsRecord::class, 'domain_id');
    }

    public function childNameservers(): HasMany
    {
        return $this->hasMany(ChildNameserver::class, 'domain_id');
    }

    public function emailForwards(): HasMany
    {
        return $this->hasMany(EmailForward::class, 'domain_id');
    }

    public function urlForwards(): HasMany
    {
        return $this->hasMany(UrlForward::class, 'domain_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'domain_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringWithinDays(Builder $query, int $days): Builder
    {
        return $query->where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    // Domain Helpers
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function daysUntilExpiration(): int
    {
        return $this->expires_at ? (int) now()->diffInDays($this->expires_at, false) : 0;
    }

    public function canManageDns(): bool
    {
        return $this->status === 'active';
    }

    public function getAuthCodeAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function setAuthCodeAttribute($value)
    {
        $this->attributes['auth_code'] = !empty($value) ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null;
    }

}
