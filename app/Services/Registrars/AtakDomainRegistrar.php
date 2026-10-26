<?php

namespace App\Services\Registrars;

use App\DTOs\DomainAvailabilityDTO;
use App\DTOs\DomainRegistrationDTO;
use App\DTOs\DomainRenewalDTO;
use App\DTOs\DomainTransferDTO;
use App\DTOs\DomainActionResultDTO;
use App\DTOs\DomainDetailsDTO;
use App\DTOs\DnsRecordDTO;
use App\DTOs\WhoisDataDTO;
use App\DTOs\RegistrarBalanceDTO;
use Illuminate\Support\Collection;

class AtakDomainRegistrar extends AbstractRegistrar
{
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiSecret = $config['api_secret'] ?? '';
    }

    public function getIdentifier(): string
    {
        return 'atakdomain';
    }

    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $isAvailable = !in_array(strtolower(trim($domain)), ['google.com', 'atakdomain.com'], true);

        return new DomainAvailabilityDTO(
            domain: $domain,
            isAvailable: $isAvailable,
            isPremium: false,
            registrationCost: 9.85,
            renewalCost: 11.50,
            currency: 'USD',
            registrar: $this->getIdentifier()
        );
    }

    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO
    {
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'AD-ORD-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'AD-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears($dto->years)
        );
    }

    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'AD-REN-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears($dto->years)
        );
    }

    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'AD-TRF-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYear()
        );
    }

    public function getInfo(string $domain): DomainDetailsDTO
    {
        return new DomainDetailsDTO(
            domain: $domain,
            status: 'active',
            createdAt: now()->subMonths(4),
            expiresAt: now()->addMonths(8),
            isLocked: true,
            idProtection: false,
            nameservers: ['ns1.atakdomain.com', 'ns2.atakdomain.com']
        );
    }

    public function getNameservers(string $domain): array
    {
        return ['ns1.atakdomain.com', 'ns2.atakdomain.com'];
    }

    public function setNameservers(string $domain, array $nameservers): bool
    {
        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        return collect([
            new DnsRecordDTO('ad_1', 'A', '@', '192.0.2.4', null, 3600),
        ]);
    }

    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO
    {
        $record->id = 'ad_' . rand(10000, 99999);
        return $record;
    }

    public function updateDnsRecord(string $domain, string|int $recordId, DnsRecordDTO $record): DnsRecordDTO
    {
        $record->id = (string) $recordId;
        return $record;
    }

    public function deleteDnsRecord(string $domain, string|int $recordId): bool
    {
        return true;
    }

    public function getWhois(string $domain): WhoisDataDTO
    {
        return new WhoisDataDTO();
    }

    public function updateWhois(string $domain, WhoisDataDTO $whoisData): bool
    {
        return true;
    }

    public function getEppCode(string $domain): string
    {
        return 'AD-EPP-' . strtoupper(bin2hex(random_bytes(5)));
    }

    public function setLock(string $domain, bool $locked): bool
    {
        return true;
    }

    public function setIdProtection(string $domain, bool $enabled): bool
    {
        return true;
    }

    public function createChildNameserver(string $domain, string $hostname, string $ip): bool
    {
        return true;
    }

    public function updateChildNameserver(string $domain, string $hostname, string $newIp): bool
    {
        return true;
    }

    public function deleteChildNameserver(string $domain, string $hostname): bool
    {
        return true;
    }

    public function getBalance(): RegistrarBalanceDTO
    {
        return new RegistrarBalanceDTO(
            registrar: $this->getIdentifier(),
            balance: 50.00,
            currency: 'USD'
        );
    }
}
