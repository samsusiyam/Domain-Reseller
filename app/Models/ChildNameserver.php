<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildNameserver extends Model
{
    protected $table = 'child_nameservers';

    protected $fillable = [
        'domain_id',
        'hostname',
        'ip_address',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }
}
