<?php

namespace App\DTOs;

use Carbon\Carbon;

class RegistrarBalanceDTO
{
    public function __construct(
        public string $registrar,
        public float $balance,
        public string $currency = 'USD',
        public ?Carbon $retrievedAt = null
    ) {
        $this->retrievedAt = $this->retrievedAt ?? now();
    }

    public function toArray(): array
    {
        return [
            'registrar' => $this->registrar,
            'balance' => $this->balance,
            'currency' => $this->currency,
            'retrieved_at' => $this->retrievedAt?->toIso8601String(),
        ];
    }

    public function formatted(): string
    {
        return '$' . number_format($this->balance, 2) . ' ' . $this->currency;
    }
}