<?php

namespace App\DTOs;

class DomainAvailabilityDTO
{
    public function __construct(
        public string $domain,
        public bool $isAvailable,
        public bool $isPremium = false,
        public float $registrationCost = 0.0,
        public float $renewalCost = 0.0,
        public string $currency = 'USD',
        public string $registrar = '',
        public string $status = 'available', // 'available' | 'taken' | 'error'
        public ?string $errorMessage = null,
        public bool $isTestMode = false
    ) {
        // Enforce constraint: If status is error, isAvailable can NEVER be true
        if ($this->status === 'error') {
            $this->isAvailable = false;
        } elseif ($this->isAvailable) {
            $this->status = 'available';
        } else {
            $this->status = 'taken';
        }
    }

    public function isCheckFailed(): bool
    {
        return $this->status === 'error';
    }

    public function toArray(): array
    {
        return [
            'domain' => $this->domain,
            'is_available' => $this->isAvailable,
            'status' => $this->status,
            'is_premium' => $this->isPremium,
            'registration_cost' => $this->registrationCost,
            'renewal_cost' => $this->renewalCost,
            'currency' => $this->currency,
            'registrar' => $this->registrar,
            'error_message' => $this->errorMessage,
            'is_test_mode' => $this->isTestMode,
        ];
    }
}
