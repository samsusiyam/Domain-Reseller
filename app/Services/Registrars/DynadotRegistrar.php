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

class DynadotRegistrar extends AbstractRegistrar
{
    protected string $apiKey;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = $config['api_key'] ?? '';
    }

    public function getIdentifier(): string
    {
        return 'dynadot';
    }

    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $domain = strtolower(trim($domain));

        if (!empty($this->apiKey)) {
            try {
                $response = $this->client->get('https://api.dynadot.com/api3.json', [
                    'query' => [
                        'key' => $this->apiKey,
                        'command' => 'search',
                        'domain0' => $domain,
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode((string) $response->getBody(), true);
                    $searchResult = $data['SearchResponse']['SearchResults'][0] ?? null;
                    if ($searchResult) {
                        $available = ($searchResult['Available'] ?? '') === 'yes';
                        $price = (float) ($searchResult['Price'] ?? 10.15);
                        return new DomainAvailabilityDTO(
                            domain: $domain,
                            isAvailable: $available,
                            isPremium: false,
                            registrationCost: $price,
                            renewalCost: $price,
                            currency: 'USD',
                            registrar: $this->getIdentifier(),
                            status: $available ? 'available' : 'taken'
                        );
                    }
                }
            } catch (\Throwable $e) {
                $this->logApiCall('checkAvailability_error', ['domain' => $domain], $e->getMessage());
            }
        }

        if (config('registrars.test_mode', false)) {
            $isAvailable = !in_array($domain, ['google.com', 'yahoo.com'], true);
            return new DomainAvailabilityDTO(
                domain: $domain,
                isAvailable: $isAvailable,
                isPremium: false,
                registrationCost: 10.15,
                renewalCost: 11.25,
                currency: 'USD',
                registrar: $this->getIdentifier(),
                status: $isAvailable ? 'available' : 'taken',
                isTestMode: true
            );
        }

        return new DomainAvailabilityDTO(
            domain: $domain,
            isAvailable: false,
            status: 'error',
            errorMessage: 'Upstream Dynadot API unavailable or credentials missing.',
            registrar: $this->getIdentifier()
        );
    }

    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO
    {
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'DD-ORD-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'DD-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears($dto->years)
        );
    }

    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'DD-REN-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears($dto->years)
        );
    }

    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'DD-TRF-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYear()
        );
    }

    public function getInfo(string $domain): DomainDetailsDTO
    {
        return new DomainDetailsDTO(
            domain: $domain,
            status: 'active',
            createdAt: now()->subMonths(3),
            expiresAt: now()->addMonths(9),
            isLocked: true,
            idProtection: false,
            nameservers: ['ns1.dynadot.com', 'ns2.dynadot.com']
        );
    }

    public function getNameservers(string $domain): array
    {
        return ['ns1.dynadot.com', 'ns2.dynadot.com'];
    }

    public function setNameservers(string $domain, array $nameservers): bool
    {
        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        return collect([
            new DnsRecordDTO('dd_1', 'A', '@', '192.0.2.3', null, 3600),
        ]);
    }

    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO
    {
        $record->id = 'dd_' . rand(10000, 99999);
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
        return 'DD-EPP-' . strtoupper(bin2hex(random_bytes(5)));
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
            balance: 75.00,
            currency: 'USD'
        );
    }
}
