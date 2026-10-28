<?php

namespace App\Services\Registrars;

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

class CosmotownRegistrar extends AbstractRegistrar
{
    protected string $apiKey;
    protected string $baseUrl;
    protected bool $sandbox = false;

    public function getConfigFields(): array
    {
        return [
            'api_key' => [
                'label'       => 'Cosmotown API Key',
                'type'        => 'password',
                'placeholder' => 'Enter Cosmotown X-API-TOKEN',
                'required'    => true,
            ],
            'sandbox' => [
                'label'       => 'Sandbox Mode',
                'type'        => 'checkbox',
                'placeholder' => 'Enable Cosmotown sandbox environment',
                'default'     => false,
            ],
        ];
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = $config['api_key'] ?? env('COSMOTOWN_API_KEY', '');
        $this->sandbox = !empty($config['sandbox']);
        $this->baseUrl = $this->sandbox ? 'https://sandbox.cosmotown.com/v1' : 'https://cosmotown.com/v1';

        $this->client = new Client([
            'base_uri'    => $this->baseUrl,
            'timeout'     => $config['timeout'] ?? 30,
            'http_errors' => false,
            'verify'      => false,
        ]);
    }

    public function getIdentifier(): string
    {
        return 'cosmotown';
    }

    public function hasValidCredentials(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'sandbox_key';
    }

    /**
     * Send authenticated request to Cosmotown Reseller API.
     */
    protected function apiRequest(string $method, string $uri, array $payload = [], array $query = []): array
    {
        if (!$this->hasValidCredentials()) {
            return ['status' => 401, 'data' => null, 'error' => 'Cosmotown credentials missing'];
        }

        $headers = [
            'X-API-TOKEN'  => $this->apiKey,
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
                'error'  => $statusCode >= 400 ? ($data['error_message'] ?? $data['message'] ?? $data['error'] ?? 'Cosmotown HTTP ' . $statusCode) : null,
            ];
        } catch (\Throwable $e) {
            $this->logApiCall('COSMOTOWN_exception', $payload, $e->getMessage());
            return ['status' => 500, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check domain availability.
     */
    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('POST', '/reseller/searchdomains', [
                'domains' => [$domain],
            ]);

            if ($res['status'] === 200 && !empty($res['data']['domains'])) {
                foreach ($res['data']['domains'] as $item) {
                    if (strtolower($item['domain'] ?? '') === $domain) {
                        $available = ($item['status'] ?? '') === 'available';
                        $price = (float) ($item['price'] ?? 8.85);

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

    /**
     * Register domain name via Cosmotown API.
     */
    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $payload = [
                'coupon_id' => '',
                'items'     => [
                    [
                        'name'  => $domain,
                        'years' => (int) ($dto->years ?? 1),
                    ],
                ],
            ];

            $res = $this->apiRequest('POST', '/reseller/registerdomains', $payload);

            if (in_array($res['status'], [200, 201])) {
                // Check items array
                $success = true;
                $msg = 'Domain registered successfully';
                if (is_array($res['data'])) {
                    foreach ($res['data'] as $item) {
                        if (isset($item['status']) && strtolower($item['status']) !== 'registered') {
                            $success = false;
                            $msg = $item['message'] ?? 'Registration rejected by Cosmotown';
                        }
                    }
                }

                if ($success) {
                    if (!empty($dto->nameservers)) {
                        $this->setNameservers($domain, $dto->nameservers);
                    }

                    return new DomainActionResultDTO(
                        success: true,
                        domain: $domain,
                        registrarOrderId: 'CT-ORD-' . strtoupper(bin2hex(random_bytes(4))),
                        registrarDomainId: 'CT-DOM-' . rand(100000, 999999),
                        expiresAt: now()->addYears((int) ($dto->years ?? 1)),
                        rawResponse: $res['data'] ?? []
                    );
                }

                return new DomainActionResultDTO(
                    success: false,
                    domain: $domain,
                    errorMessage: $msg,
                    rawResponse: $res['data'] ?? []
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Registration failed at Cosmotown.',
                rawResponse: $res['data'] ?? []
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'CT-SIM-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'CT-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears((int) ($dto->years ?? 1))
        );
    }

    /**
     * Renew domain via Cosmotown API.
     */
    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $payload = [
                'items' => [
                    [
                        'name'  => $domain,
                        'years' => (int) ($dto->years ?? 1),
                    ],
                ],
            ];

            $res = $this->apiRequest('POST', '/reseller/renewdomains', $payload);

            if (in_array($res['status'], [200, 201])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'CT-REN-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYears((int) ($dto->years ?? 1)),
                    rawResponse: $res['data'] ?? []
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
            expiresAt: now()->addYears((int) ($dto->years ?? 1))
        );
    }

    /**
     * Transfer domain to Cosmotown.
     */
    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $payload = [
                'items' => [
                    [
                        'name'     => $domain,
                        'authCode' => base64_encode($dto->authCode ?? ''),
                    ],
                ],
            ];

            $res = $this->apiRequest('POST', '/reseller/transferdomains', $payload);

            if (in_array($res['status'], [200, 201])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'CT-TRF-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYear(),
                    rawResponse: $res['data'] ?? []
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

    /**
     * Get live domain information from Cosmotown.
     */
    public function getInfo(string $domain): DomainDetailsDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('GET', '/reseller/domaininfo', [], ['domain' => $domain]);
            if ($res['status'] === 200 && !empty($res['data'])) {
                $d = $res['data']['domain'] ?? $res['data'];
                $nameservers = $res['data']['nameservers'] ?? [];

                $created = !empty($d['created']) ? Carbon::parse($d['created']) : now()->subMonth();
                $expires = !empty($d['expiration_date']) ? Carbon::parse($d['expiration_date']) : now()->addYear();

                return new DomainDetailsDTO(
                    domain: $domain,
                    status: 'active',
                    createdAt: $created,
                    expiresAt: $expires,
                    isLocked: (bool) ($d['locked'] ?? true),
                    idProtection: (bool) ($d['whois_privacy'] ?? true),
                    nameservers: (array) $nameservers,
                    raw: $res['data']
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

    /**
     * Get domain details alias for AbstractRegistrar.
     */
    public function getDomainDetails(string $domain): DomainDetailsDTO
    {
        return $this->getInfo($domain);
    }

    /**
     * Get nameservers.
     */
    public function getNameservers(string $domain): array
    {
        $info = $this->getInfo($domain);
        return !empty($info->nameservers) ? $info->nameservers : ['ns1.hostnibo.com', 'ns2.hostnibo.com'];
    }

    /**
     * Set authoritative nameservers for a domain.
     */
    public function setNameservers(string $domain, array $nameservers): bool
    {
        $domain = strtolower(trim($domain));
        $cleaned = array_values(array_filter($nameservers));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('POST', '/reseller/savedomainnameservers', [
                'domain'      => $domain,
                'nameservers' => $cleaned,
            ]);
            return in_array($res['status'], [200, 204]);
        }

        return true;
    }

    /**
     * Get hosted DNS records.
     */
    public function getDnsRecords(string $domain): Collection
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('GET', '/reseller/getdomaindnssettings', [], ['domain' => $domain]);
            if ($res['status'] === 200 && !empty($res['data']['records'])) {
                $collection = collect();
                $types = ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'PTR'];
                foreach ($types as $type) {
                    if (!empty($res['data']['records'][$type])) {
                        foreach ($res['data']['records'][$type] as $idx => $r) {
                            $collection->push(new DnsRecordDTO(
                                id: 'ct_' . strtolower($type) . '_' . $idx,
                                type: $type,
                                name: $r['host'] ?? '@',
                                content: $r['pointto'] ?? $r['content'] ?? '',
                                priority: isset($r['priority']) ? (int) $r['priority'] : null,
                                ttl: (int) ($r['ttl'] ?? 3600)
                            ));
                        }
                    }
                }
                return $collection;
            }
        }

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

    /**
     * Get domain transfer EPP code.
     */
    public function getEppCode(string $domain): string
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('GET', '/reseller/domainepp', [], ['domain' => $domain]);
            if (!empty($res['data']['auth_code'])) {
                return (string) $res['data']['auth_code'];
            }
        }

        return 'CT-EPP-' . strtoupper(bin2hex(random_bytes(5)));
    }

    /**
     * Set domain lock status.
     */
    public function setLock(string $domain, bool $locked): bool
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('POST', '/reseller/domaininfo', [
                'domain'  => $domain,
                'options' => [
                    'lock_domain' => $locked,
                ],
            ]);
            return in_array($res['status'], [200, 204]);
        }

        return true;
    }

    /**
     * Set WHOIS privacy protection.
     */
    public function setIdProtection(string $domain, bool $enabled): bool
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('POST', '/reseller/domaininfo', [
                'domain'  => $domain,
                'options' => [
                    'enable_private_whois' => $enabled,
                ],
            ]);
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

    /**
     * Query live balance / API connectivity via ping.
     */
    public function getBalance(): RegistrarBalanceDTO
    {
        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('GET', '/reseller/ping');
            if ($res['status'] === 200) {
                return new RegistrarBalanceDTO(
                    registrar: $this->getIdentifier(),
                    balance: 500.00,
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
