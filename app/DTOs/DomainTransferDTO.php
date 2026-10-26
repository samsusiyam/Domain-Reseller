<?php

namespace App\DTOs;

class DomainTransferDTO
{
    /**
     * @param array<string> $nameservers
     */
    public function __construct(
        public string $domain,
        public string $eppCode,
        public ?WhoisDataDTO $contactDetails = null,
        public array $nameservers = []
    ) {}
}
