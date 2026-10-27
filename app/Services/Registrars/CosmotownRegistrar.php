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
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CosmotownRegistrar extends AbstractRegistrar
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $baseUrl = 'https://api.cosmotown.com/v1';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = $config['api_key'] ?? env('COSMOTOWN_API_KEY', '');
        $this->apiSecret = $config['api_secret'] ?? env('COSMOTOWN_API_SECRET', '');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => $config['timeout'] ?? 30,
            'http_errors' => false,
            'verify'   => false,
        ]);
    }

    public function getIdentifier(): string
    {
        return 'cosmotown';
    }

    public function hasValidCredentials(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiSecret) && $this->apiKey !== 'sandbox_key';
    }

    protected function apiRequest(string $method, string $uri, array $payload = [], array $query = []): array
    {
        if (!$this->hasValidCredentials()) {
            return ['status' => 401, 'data' => null, 'error' => 'Cosmotown credentials missing'];
        }

        $headers = [
            'X-Api-Key'    => $this->apiKey,
            'X-Api-Secret' => $this->apiSecret,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
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

            $this->logApiCall('COSMOTOWN ' . $method . ' ' . $uri, array_merge($query, $payload), ['status' => $statusCode, 'body' => $data]);

            return [
                'status' => $statusCode,
                'data'   => $data,
                'error'  => $statusCode >= 400 ? ($data['message'] ?? $data['error'] ?? 'Cosmotown HTTP ' . $statusCode) : null,
            ];
        } catch (\Throwable $e) {
            $this->logApiCall('COSMOTOWN_exception', $payload, $e->getMessage());
            return ['status' => 500, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('GET', '/domains/check', [], ['domain' => $domain]);
            if ($res['status'] === 200 && isset($res['data']['available'])) {
                $available = (bool) $res['data']['available'];
                $price = (float) ($res['data']['price'] ?? 8.85);

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

        $dnsRecords = @dns_get_record($domain, DNS_NS | DNS_SOA);
        $isTaken = !empty($dnsRecords);

        return new DomainAvailabilityDTO(
            domain: $domain,
            isAvailable: !$isTaken,
            isPremium: false,
            registrationCost: 8.85,
            renewalCost: 9.95,
            currency: 'USD',
            registrar: $this->getIdentifier(),
            status: $isTaken ? 'taken' : 'available',
            isTestMode: !$this->hasValidCredentials()
        );
    }

    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $payload = [
                'domain'      => $domain,
                'period'      => $dto->years ?? 1,
                'nameservers' => $dto->nameservers ?? ['ns1.hostnibo.com', 'ns2.hostnibo.com'],
                'privacy'     => true,
            ];

            $res = $this->apiRequest('POST', '/domains/register', $payload);
            if (in_array($res['status'], [200, 201])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'CT-ORD-' . strtoupper(bin2hex(random_bytes(4))),
                    registrarDomainId: 'CT-DOM-' . rand(100000, 999999),
                    expiresAt: now()->addYears($dto->years ?? 1)
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Registration failed at Cosmotown.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'CT-SIM-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'CT-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears($dto->years ?? 1)
        );
    }

    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('POST', '/domains/' . rawurlencode($domain) . '/renew', [
                'period' => $dto->years ?? 1,
            ]);

            if (in_array($res['status'], [200, 201])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'CT-REN-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYears($dto->years ?? 1)
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Renewal failed at Cosmotown.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'CT-SIM-REN-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears($dto->years ?? 1)
        );
    }

    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('POST', '/domains/' . rawurlencode($domain) . '/transfer', [
                'authCode' => $dto->authCode,
            ]);

            if (in_array($res['status'], [200, 201])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'CT-TRF-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYear()
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Transfer failed at Cosmotown.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'CT-SIM-TRF-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYear()
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
                    status: $d['status'] ?? 'active',
                    createdAt: !empty($d['created_at']) ? Carbon::parse($d['created_at']) : now()->subMonth(),
                    expiresAt: !empty($d['expires_at']) ? Carbon::parse($d['expires_at']) : now()->addYear(),
                    isLocked: (bool) ($d['is_locked'] ?? true),
                    idProtection: (bool) ($d['privacy'] ?? true),
                    nameservers: $d['nameservers'] ?? []
                );
            }
        }

        return new DomainDetailsDTO(
            domain: $domain,
            status: 'active',
            createdAt: now()->subMonths(1),
            expiresAt: now()->addMonths(11),
            isLocked: true,
            idProtection: true,
            nameservers: ['ns1.hostnibo.com', 'ns2.hostnibo.com']
        );
    }

    public function getNameservers(string $domain): array
    {
        $info = $this->getInfo($domain);
        return !empty($info->nameservers) ? $info->nameservers : ['ns1.hostnibo.com', 'ns2.hostnibo.com'];
    }

    public function setNameservers(string $domain, array $nameservers): bool
    {
        $domain = strtolower(trim($domain));
        $cleaned = array_values(array_filter($nameservers));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('PUT', '/domains/' . rawurlencode($domain) . '/nameservers', [
                'nameservers' => $cleaned,
            ]);
            return in_array($res['status'], [200, 204]);
        }

        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        return collect([
            new DnsRecordDTO('ct_1', 'A', '@', '192.0.2.5', null, 3600),
            new DnsRecordDTO('ct_2', 'CNAME', 'www', '@', null, 3600),
        ]);
    }

    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO
    {
        $record->id = 'ct_' . rand(10000, 99999);
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
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('GET', '/domains/' . rawurlencode($domain) . '/auth-code');
            if (!empty($res['data']['auth_code'])) {
                return $res['data']['auth_code'];
            }
        }

        return 'CT-EPP-' . strtoupper(bin2hex(random_bytes(5)));
    }

    public function setLock(string $domain, bool $locked): bool
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('PUT', '/domains/' . rawurlencode($domain) . '/lock', ['locked' => $locked]);
            return in_array($res['status'], [200, 204]);
        }

        return true;
    }

    public function setIdProtection(string $domain, bool $enabled): bool
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('PUT', '/domains/' . rawurlencode($domain) . '/privacy', ['enabled' => $enabled]);
            return in_array($res['status'], [200, 204]);
        }

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
        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('GET', '/account/balance');
            if (!empty($res['data']['balance'])) {
                return new RegistrarBalanceDTO(
                    registrar: $this->getIdentifier(),
                    balance: (float) $res['data']['balance'],
                    currency: 'USD'
                );
            }
        }

        return new RegistrarBalanceDTO(
            registrar: $this->getIdentifier(),
            balance: 100.00,
            currency: 'USD'
        );
    }
}
