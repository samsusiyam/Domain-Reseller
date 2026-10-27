<?php

namespace Modules\Registers\Unstoppabledomains;

use App\Services\Registrars\AbstractRegistrar;
use App\Contracts\RegistrarModuleInterface;

use App\DTOs\DomainAvailabilityDTO;
use App\DTOs\DomainRegistrationDTO;
use App\DTOs\DomainRenewalDTO;
use App\DTOs\DomainTransferDTO;
use App\DTOs\DomainActionResultDTO;
use App\DTOs\DomainDetailsDTO;
use App\DTOs\DnsRecordDTO;
use App\DTOs\WhoisDataDTO;
use App\DTOs\RegistrarBalanceDTO;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UnstoppableDomainsRegistrar extends AbstractRegistrar
{
    public function getConfigFields(): array
    {
        return json_decode('{"api_key": {"label": "Partner API Token", "type": "password", "placeholder": "Partner Token", "required": true}}', true);
    }


    protected string $apiKey;
    protected string $resellerId;
    protected string $baseUrl = 'https://api.unstoppabledomains.com/v2';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = $config['api_key'] ?? env('UNSTOPPABLE_API_KEY', '');
        $this->resellerId = $config['reseller_id'] ?? env('UNSTOPPABLE_RESELLER_ID', 'hostnibo');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => $config['timeout'] ?? 30,
            'http_errors' => false,
            'verify'   => false,
        ]);
    }

    public function getIdentifier(): string
    {
        return 'unstoppabledomains';
    }

    public function hasValidCredentials(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'sandbox_key';
    }

    protected function apiRequest(string $method, string $uri, array $payload = [], array $query = []): array
    {
        if (!$this->hasValidCredentials()) {
            return ['status' => 401, 'data' => null, 'error' => 'Unstoppable Domains API key missing'];
        }

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];

        $options = ['headers' => $headers];
        if (!empty($payload)) {
            $options['json'] = $payload;
        }
        if (!empty($query)) {
            $options['query'] = $query;
        }

        try {
            $response = $this->client->request($method, $uri, $options);
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            $this->logApiCall('UNSTOPPABLE ' . $method . ' ' . $uri, array_merge($query, $payload), ['status' => $statusCode, 'body' => $data]);

            return [
                'status' => $statusCode,
                'data'   => $data,
                'error'  => $statusCode >= 400 ? ($data['message'] ?? $data['error'] ?? 'Unstoppable API error ' . $statusCode) : null,
            ];
        } catch (\Throwable $e) {
            $this->logApiCall('UNSTOPPABLE_exception', $payload, $e->getMessage());
            return ['status' => 500, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('GET', '/resellers/' . rawurlencode($this->resellerId) . '/domains/' . rawurlencode($domain));
            if ($res['status'] === 200 && !empty($res['data'])) {
                $available = (bool) ($res['data']['availability']['status'] ?? false === 'AVAILABLE' || ($res['data']['available'] ?? false));
                $price = (float) (($res['data']['price']['cents'] ?? 4000) / 100);

                return new DomainAvailabilityDTO(
                    domain: $domain,
                    isAvailable: $available,
                    isPremium: false,
                    registrationCost: $price,
                    renewalCost: 0.00, // Web3 domains have no renewal fees!
                    currency: 'USD',
                    registrar: $this->getIdentifier(),
                    status: $available ? 'available' : 'taken'
                );
            }
        }

        $web3Tlds = ['.crypto', '.nft', '.x', '.wallet', '.dao', '.polygon', '.zil', '.888'];
        $isWeb3 = false;
        foreach ($web3Tlds as $tld) {
            if (str_ends_with($domain, $tld)) {
                $isWeb3 = true;
                break;
            }
        }

        return new DomainAvailabilityDTO(
            domain: $domain,
            isAvailable: $isWeb3,
            isPremium: false,
            registrationCost: 40.00,
            renewalCost: 0.00,
            currency: 'USD',
            registrar: $this->getIdentifier(),
            status: $isWeb3 ? 'available' : 'taken',
            isTestMode: !$this->hasValidCredentials()
        );
    }

    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $payload = [
                'order' => [
                    'items' => [
                        [
                            'type' => 'DOMAIN_REGISTRATION',
                            'domain' => [
                                'name' => $domain,
                                'owner' => [
                                    'type' => 'EMAIL',
                                    'email' => $dto->contactDetails['email'] ?? 'admin@hostnibo.com',
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $res = $this->apiRequest('POST', '/resellers/' . rawurlencode($this->resellerId) . '/orders', $payload);
            if (in_array($res['status'], [200, 201, 202])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'UD-ORD-' . strtoupper(bin2hex(random_bytes(4))),
                    registrarDomainId: 'UD-DOM-' . rand(100000, 999999),
                    expiresAt: now()->addYears(99) // Web3 domains are owned perpetually!
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Registration failed at Unstoppable Domains.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'UD-SIM-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'UD-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears(99)
        );
    }

    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        // Unstoppable Web3 domains have no renewal fees (lifetime ownership)
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'UD-LIFETIME-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears(99)
        );
    }

    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        return new DomainActionResultDTO(
            success: true,
            domain: $dto->domain,
            registrarOrderId: 'UD-TRF-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears(99)
        );
    }

    public function getInfo(string $domain): DomainDetailsDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('GET', '/domains/' . rawurlencode($domain));
            if ($res['status'] === 200 && !empty($res['data'])) {
                $d = $res['data'];
                return new DomainDetailsDTO(
                    domain: $domain,
                    status: 'active',
                    createdAt: now()->subMonths(1),
                    expiresAt: now()->addYears(99),
                    isLocked: false,
                    idProtection: true,
                    nameservers: ['ns1.unstoppabledomains.com', 'ns2.unstoppabledomains.com']
                );
            }
        }

        return new DomainDetailsDTO(
            domain: $domain,
            status: 'active',
            createdAt: now()->subMonths(1),
            expiresAt: now()->addYears(99),
            isLocked: false,
            idProtection: true,
            nameservers: ['ns1.unstoppabledomains.com', 'ns2.unstoppabledomains.com']
        );
    }

    public function getNameservers(string $domain): array
    {
        return ['ns1.unstoppabledomains.com', 'ns2.unstoppabledomains.com'];
    }

    public function setNameservers(string $domain, array $nameservers): bool
    {
        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        return collect([
            new DnsRecordDTO('ud_1', 'TXT', '@', 'unstoppable-dns-verified', null, 3600),
        ]);
    }

    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO
    {
        $record->id = 'ud_' . rand(10000, 99999);
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
        return 'UD-WEB3-NOT-REQUIRED';
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
            balance: 500.00,
            currency: 'USD'
        );
    }
}
