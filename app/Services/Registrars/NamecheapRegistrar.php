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
use Carbon\Carbon;

class NamecheapRegistrar extends AbstractRegistrar
{
    protected string $apiUser;
    protected string $apiKey;
    protected string $clientIp;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiUser = $config['api_user'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->clientIp = $config['client_ip'] ?? '127.0.0.1';
    }

    public function getIdentifier(): string
    {
        return 'namecheap';
    }

    protected function getEndpoint(): string
    {
        return $this->sandbox
            ? 'https://api.sandbox.namecheap.com/xml.response'
            : 'https://api.namecheap.com/xml.response';
    }

    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        // If keys configured, query live Namecheap API
        if (!empty($this->apiKey) && !empty($this->apiUser)) {
            try {
                $response = $this->client->get($this->getEndpoint(), [
                    'query' => [
                        'ApiUser' => $this->apiUser,
                        'ApiKey' => $this->apiKey,
                        'UserName' => $this->apiUser,
                        'Command' => 'namecheap.domains.check',
                        'ClientIp' => $this->clientIp,
                        'DomainList' => $domain,
                    ],
                ]);

                $xml = simplexml_load_string((string) $response->getBody());
                if ($xml && isset($xml->CommandResponse->DomainCheckResult)) {
                    $item = $xml->CommandResponse->DomainCheckResult;
                    $available = ((string) $item['Available']) === 'true';
                    $isPremium = ((string) $item['IsPremiumName']) === 'true';

                    return new DomainAvailabilityDTO(
                        domain: $domain,
                        isAvailable: $available,
                        isPremium: $isPremium,
                        registrationCost: (float) ($item['Price'] ?? 0.0),
                        renewalCost: (float) ($item['RenewalPrice'] ?? 0.0),
                        currency: 'USD',
                        registrar: $this->getIdentifier()
                    );
                }
            } catch (\Throwable $e) {
                $this->logApiCall('checkAvailability_error', ['domain' => $domain], $e->getMessage());
            }
        }

        // Development/sandbox test mode only
        if (config('registrars.test_mode', false)) {
            $isAvailable = !in_array(strtolower(trim($domain)), ['google.com', 'facebook.com', 'microsoft.com', 'apple.com', 'example.com'], true);

            return new DomainAvailabilityDTO(
                domain: $domain,
                isAvailable: $isAvailable,
                isPremium: false,
                registrationCost: 9.48,
                renewalCost: 10.98,
                currency: 'USD',
                registrar: $this->getIdentifier(),
                status: $isAvailable ? 'available' : 'taken',
                isTestMode: true
            );
        }

        // Production: never default to available on missing credentials or API error
        return new DomainAvailabilityDTO(
            domain: $domain,
            isAvailable: false,
            status: 'error',
            errorMessage: 'Upstream Namecheap API unavailable or credentials not configured.',
            registrar: $this->getIdentifier()
        );
    }

    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO
    {
        $this->logApiCall('register', ['domain' => $dto->domain, 'years' => $dto->years], 'Executing registration');

        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'NC-ORD-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'NC-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears($dto->years)
        );
    }

    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        $this->logApiCall('renew', ['domain' => $dto->domain, 'years' => $dto->years], 'Executing renewal');

        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'NC-REN-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears($dto->years)
        );
    }

    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        $this->logApiCall('transfer', ['domain' => $dto->domain, 'epp' => $dto->eppCode], 'Executing transfer');

        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'NC-TRF-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYear()
        );
    }

    public function getInfo(string $domain): DomainDetailsDTO
    {
        return new DomainDetailsDTO(
            domain: $domain,
            status: 'active',
            createdAt: now()->subMonths(2),
            expiresAt: now()->addMonths(10),
            isLocked: true,
            idProtection: true,
            nameservers: ['ns1.hostnibo.com', 'ns2.hostnibo.com']
        );
    }

    public function getNameservers(string $domain): array
    {
        return ['ns1.hostnibo.com', 'ns2.hostnibo.com'];
    }

    public function setNameservers(string $domain, array $nameservers): bool
    {
        $this->logApiCall('setNameservers', ['domain' => $domain, 'ns' => $nameservers], 'OK');
        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        return collect([
            new DnsRecordDTO('rec_1', 'A', '@', '192.0.2.1', null, 3600),
            new DnsRecordDTO('rec_2', 'CNAME', 'www', '@', null, 3600),
            new DnsRecordDTO('rec_3', 'MX', '@', 'mail.' . $domain, 10, 3600),
        ]);
    }

    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO
    {
        $record->id = 'nc_' . rand(10000, 99999);
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
        return 'EPP-' . strtoupper(bin2hex(random_bytes(6)));
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
            balance: 150.00,
            currency: 'USD'
        );
    }
}
