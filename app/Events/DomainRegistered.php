<?php

namespace App\Events;

use App\Models\Domain;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DomainRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Domain $domain,
        public Order $order,
        public User $user
    ) {}
}
