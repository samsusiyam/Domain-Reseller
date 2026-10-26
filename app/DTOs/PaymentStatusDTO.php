<?php

namespace App\DTOs;

class PaymentStatusDTO
{
    public function __construct(
        public string $transactionId,
        public string $status, // initiated, pending, completed, failed, refunded
        public float $amount,
        public string $currency,
        public array $raw = []
    ) {}
}
