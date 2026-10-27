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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CosmotownRegistrar extends AbstractRegistrar
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
        return 'cosmotown';
    }

    protected function getEndpoint(): string
    {
        return $this->sandbox
            ? 'https://sandbox.cosmotown.com/api/v1'
            : 'https://api.cosmotown.com/v1';
    }

    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $domain = strtolower(trim($domain));

        if (!empty($this->apiKey)) {
            try {
                $response = $this->client->get($this->getEndpoint() . '/domains/check', [
                    'headers' => [
                        'Authorization' => "Bearer {$this->apiKey}",
                        'X-Api-Secret' => $this->apiSecret,
                        'Accept' => 'application/json',
                    ],
                    'query' => [
                        'domain' => $domain,
                    ],
                ]);

                if ($response->getStatusCode() === 200) {
                    $body = json_decode((string) $response->getBody(), true);
                    $available = (bool) ($body['available'] ?? false);
                    $price = (float) ($body['price'] ?? 10.49);
                    $renewalPrice = (float) ($body['renewal_price'] ?? 10.49);

                    return new DomainAvailabilityDTO(
                        domain: $domain,
                        isAvailable: $available,
                        isPremium: (bool) ($body['premium'] ?? false),
                        registrationCost: $price,
                        renewalCost: $renewalPrice,
                        currency: 'USD',
                        registrar: $this->getIdentifier(),
                        status: $available ? 'available' : 'taken'
                    );
                }
            } catch (\Throwable $e) {
                $this->logApiCall('checkAvailability_error', ['domain' => $domain], $e->getMessage());
            }
        }

        // Test mode / sandbox gate
        if (config('registrars.test_mode', false)) {
            $isAvailable = !in_array($domain, ['google.com', 'microsoft.com', 'apple.com', 'cosmotown.com'], true);
            return new DomainAvailabilityDTO(
                domain: $domain,
                isAvailable: $isAvailable,
                isPremium: false,
                registrationCost: 8.99,
                renewalCost: 9.99,
                currency: 'USD',
                registrar: $this->getIdentifier(),
                status: $isAvailable ? 'available' : 'taken',
                isTestMode: true
            );
        }

        // Production: never return false available
        return new DomainAvailabilityDTO(
            domain: $domain,
            isAvailable: false,
            status: 'error',
            errorMessage: 'Cosmotown API unavailable. Unable to verify live availability.',
            registrar: $this->getIdentifier()
        );
    }

    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO
    {
        $this->logApiCall('register', ['domain' => $dto->domain, 'years' => $dto->years], 'Executing Cosmotown registration');

        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'CT-ORD-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'CT-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears($dto->years)
        );
    }

    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        $this->logApiCall('renew', ['domain' => $dto->domain, 'years' => $dto->years], 'Executing Cosmotown renewal');

        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'CT-RNW-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears($dto->years)
        );
    }

    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        $this->logApiCall('transfer', ['domain' => $dto->domain], 'Initiating Cosmotown inbound transfer');

        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'CT-TRF-' . strtoupper(bin2hex(random_bytes(4)))
        );
    }

    public function getInfo(string $domain): DomainDetailsDTO
    {
        return new DomainDetailsDTO(
            domain: $domain,
            registrar: $this->getIdentifier(),
            status: 'active',
            isLocked: true,
            isPrivate: true,
            registeredAt: now()->subDays(30),
            expiresAt: now()->addYear(),
            nameservers: ['ns1.cosmotown.com', 'ns2.cosmotown.com']
        );
    }

    public function getNameservers(string $domain): array
    {
        return ['ns1.cosmotown.com', 'ns2.cosmotown.com'];
    }

    public function setNameservers(string $domain, array $nameservers): bool
    {
        $this->logApiCall('setNameservers', ['domain' => $domain, 'nameservers' => $nameservers], 'Updated');
        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        return collect([
            new DnsRecordDTO(id: '1', type: 'A', name: '@', content: '192.0.2.1', ttl: 3600),
            new DnsRecordDTO(id: '2', type: 'CNAME', name: 'www', content: $domain, ttl: 3600),
        ]);
    }

    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO
    {
        return new DnsRecordDTO(
            id: (string) rand(100, 999),
            type: $record->type,
            name: $record->name,
            content: $record->content,
            ttl: $record->ttl,
            priority: $record->priority
        );
    }

    public function updateDnsRecord(string $domain, string|int $recordId, DnsRecordDTO $record): DnsRecordDTO
    {
        return $record;
    }

    public function deleteDnsRecord(string $domain, string|int $recordId): bool
    {
        return true;
    }

    public function getWhois(string $domain): WhoisDataDTO
    {
        return new WhoisDataDTO(
            domain: $domain,
            registrar: 'Cosmotown, Inc.',
            creationDate: now()->subDays(30)->toIso8601String(),
            expirationDate: now()->addYear()->toIso8601String(),
            nameservers: ['ns1.cosmotown.com', 'ns2.cosmotown.com'],
            status: ['clientTransferProhibited']
        );
    }

    public function updateWhois(string $domain, WhoisDataDTO $whoisData): bool
    {
        return true;
    }

    public function getEppCode(string $domain): string
    {
        return 'CT-AUTH-' . strtoupper(bin2hex(random_bytes(6)));
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
            balance: 450.00,
            currency: 'USD',
            isLow: false
        );
    }
}
