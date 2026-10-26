<?php

namespace App\DTOs;

class WhoisDataDTO
{
    /**
     * @param array<string, mixed> $registrant
     * @param array<string, mixed> $admin
     * @param array<string, mixed> $tech
     * @param array<string, mixed> $billing
     */
    public function __construct(
        public array $registrant = [],
        public array $admin = [],
        public array $tech = [],
        public array $billing = []
    ) {}

    public function toArray(): array
    {
        return [
            'registrant' => $this->registrant,
            'admin' => $this->admin,
            'tech' => $this->tech,
            'billing' => $this->billing,
        ];
    }
}
