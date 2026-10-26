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
        public string $registrar = ''
    ) {}

    public function toArray(): array
    {
        return [
            'domain' => $this->domain,
            'is_available' => $this->isAvailable,
            'is_premium' => $this->isPremium,
            'registration_cost' => $this->registrationCost,
            'renewal_cost' => $this->renewalCost,
            'currency' => $this->currency,
            'registrar' => $this->registrar,
        ];
    }
}
