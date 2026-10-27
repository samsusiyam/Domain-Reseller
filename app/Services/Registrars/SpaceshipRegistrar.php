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

class SpaceshipRegistrar extends AbstractRegistrar
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
        return 'spaceship';
    }

    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $domain = strtolower(trim($domain));

        if (!empty($this->apiKey) && !empty($this->apiSecret)) {
            try {
                $response = $this->client->get('https://spaceship.dev/api/v1/domains/' . rawurlencode($domain) . '/available', [
                    'headers' => [
                        'X-API-Key' => $this->apiKey,
                        'X-API-Secret' => $this->apiSecret,
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode((string) $response->getBody(), true);
                    $available = (bool) ($data['available'] ?? false);
                    return new DomainAvailabilityDTO(
                        domain: $domain,
                        isAvailable: $available,
                        isPremium: (bool) ($data['premium'] ?? false),
                        registrationCost: (float) ($data['price'] ?? 8.98),
                        renewalCost: (float) ($data['renewalPrice'] ?? 10.48),
                        currency: 'USD',
                        registrar: $this->getIdentifier(),
                        status: $available ? 'available' : 'taken'
                    );
                }
            } catch (\Throwable $e) {
                $this->logApiCall('checkAvailability_error', ['domain' => $domain], $e->getMessage());
            }
        }

        if (config('registrars.test_mode', false)) {
            $isAvailable = !in_array($domain, ['google.com', 'microsoft.com'], true);
            return new DomainAvailabilityDTO(
                domain: $domain,
                isAvailable: $isAvailable,
                isPremium: false,
                registrationCost: 8.98,
                renewalCost: 10.48,
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
            errorMessage: 'Upstream Spaceship API unavailable or credentials missing.',
            registrar: $this->getIdentifier()
        );
    }

    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO
    {
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'SS-ORD-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'SS-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears($dto->years)
        );
    }

    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'SS-REN-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears($dto->years)
        );
    }

    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'SS-TRF-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYear()
        );
    }

    public function getInfo(string $domain): DomainDetailsDTO
    {
        return new DomainDetailsDTO(
            domain: $domain,
            status: 'active',
            createdAt: now()->subMonths(1),
            expiresAt: now()->addMonths(11),
            isLocked: true,
            idProtection: true,
            nameservers: ['ns1.spaceship.com', 'ns2.spaceship.com']
        );
    }

    public function getNameservers(string $domain): array
    {
        return ['ns1.spaceship.com', 'ns2.spaceship.com'];
    }

    public function setNameservers(string $domain, array $nameservers): bool
    {
        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        return collect([
            new DnsRecordDTO('ss_1', 'A', '@', '192.0.2.2', null, 3600),
            new DnsRecordDTO('ss_2', 'CNAME', 'www', '@', null, 3600),
        ]);
    }

    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO
    {
        $record->id = 'ss_' . rand(10000, 99999);
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
        return 'SS-EPP-' . strtoupper(bin2hex(random_bytes(5)));
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
            balance: 200.00,
            currency: 'USD'
        );
    }
}
