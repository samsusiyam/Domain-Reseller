<?php

namespace App\DTOs;

class PaymentRefundDTO
{
    public function __construct(
        public string $transactionId,
        public float $amount,
        public string $currency,
        public string $reason = 'Requested by client/admin'
    ) {}
}
