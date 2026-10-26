<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePaid
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public ?Transaction $transaction = null
    ) {}
}
