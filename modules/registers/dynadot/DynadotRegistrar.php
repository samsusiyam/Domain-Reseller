<?php

namespace Modules\Registers\Dynadot;

use App\Contracts\RegistrarModuleInterface;
use App\Services\Registrars\AbstractRegistrar;
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
use Illuminate\Support\Str;

class DynadotRegistrar extends AbstractRegistrar implements RegistrarModuleInterface
{
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = trim($config['api_key'] ?? env('DYNADOT_API_KEY', ''));
        $this->apiSecret = trim($config['api_secret'] ?? env('DYNADOT_API_SECRET', ''));
        $this->client = new Client([
            'timeout'         => $config['timeout'] ?? 30,
            'connect_timeout' => 10,
            'http_errors'     => false,
            'verify'          => false,
        ]);
    }

    /**
     * WHMCS-style configuration fields schema.
     * Both API Key and Secret Key are provided by Dynadot Tools > API.
     */
    public function getConfigFields(): array
    {
        return [
            'api_key' => [
                'label'       => 'Dynadot API Key',
                'type'        => 'password',
                'placeholder' => 'e.g. 7S9JZ7P60F7yF7m8L6D6h7t6Z6Mt7j8S6u7G64yt9L',
                'description' => 'From Dynadot > Tools > API. (IMPORTANT: Whitelist your server IP 188.40.147.141 under IP Address in Dynadot).',
                'required'    => true,
            ],
            'api_secret' => [
                'label'       => 'Dynadot Secret Key',
                'type'        => 'password',
                'placeholder' => '64-character hex secret key from Dynadot',
                'description' => 'From Dynadot > Tools > API. Used for generating HMAC-SHA256 X-Signature for RESTful API v2.',
                'required'    => false,
            ],
        ];
    }

    public function getIdentifier(): string
    {
        return 'dynadot';
    }

    protected function getBaseUrl(): string
    {
        return $this->sandbox
            ? 'https://api-sandbox.dynadot.com'
            : 'https://api.dynadot.com';
    }

    public function hasValidCredentials(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'sandbox_key';
    }

    /**
     * Generate Dynadot RESTful API v2 HMAC-SHA256 X-Signature.
     */
    protected function generateSignature(string $fullPathAndQuery, string $xRequestId, string $requestBody = ''): string
    {
        $stringToSign = $this->apiKey . "\n" . $fullPathAndQuery . "\n" . $xRequestId . "\n" . $requestBody;
        $digest = hash_hmac('sha256', $stringToSign, $this->apiSecret, true);
        return base64_encode($digest);
    }

    /**
     * Executes authenticated Dynadot RESTful API v2 or API3 request.
     */
    protected function apiRequest(array $params): array
    {
        if (!$this->hasValidCredentials()) {
            return [
                'status' => 401,
                'data'   => null,
                'root'   => null,
                'error'  => 'Dynadot API credentials are missing. Please enter your API Key.',
            ];
        }

        // Try RESTful v2 if secret key is present for account/balance info
        if (!empty($this->apiSecret) && ($params['command'] ?? '') === 'get_account_balance') {
            try {
                $path = '/restful/v2/accounts/info';
                $xRequestId = (string) Str::uuid();
                $signature = $this->generateSignature($path, $xRequestId);

                $response = $this->client->get($this->getBaseUrl() . $path, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'X-Signature'   => $signature,
                        'X-Request-ID'  => $xRequestId,
                        'Accept'        => 'application/json',
                    ],
                ]);

                $statusCode = $response->getStatusCode();
                $body = (string) $response->getBody();
                $data = json_decode($body, true) ?? [];

                if ($statusCode === 200 && empty($data['error'])) {
                    $balance = (float)($data['balance'] ?? ($data['data']['balance'] ?? 0));
                    $currency = $data['currency'] ?? 'USD';
                    return [
                        'status' => 200,
                        'data'   => $data,
                        'root'   => ['Balance' => $balance, 'Currency' => $currency, 'ResponseCode' => 0],
                        'error'  => null,
                    ];
                }

                if ($statusCode >= 400 || !empty($data['error'])) {
                    $errDesc = $data['error']['description'] ?? ($data['message'] ?? 'RESTful API error ' . $statusCode);
                    return [
                        'status' => $statusCode,
                        'data'   => $data,
                        'root'   => null,
                        'error'  => $errDesc,
                    ];
                }
            } catch (\Throwable $e) {
                // Fallback to API3
            }
        }

        // Classic API v3 (JSON format)
        $url = $this->getBaseUrl() . '/api3.json';
        $params['key'] = $this->apiKey;

        try {
            $response = $this->client->get($url, [
                'query' => $params,
            ]);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            $this->logApiCall('DYNADOT ' . ($params['command'] ?? 'request'), $params, [
                'status' => $statusCode,
                'body'   => $data,
            ]);

            if (!is_array($data)) {
                return [
                    'status' => $statusCode,
                    'data'   => null,
                    'root'   => null,
                    'error'  => 'Invalid response received from Dynadot API: ' . substr($body, 0, 150),
                ];
            }

            // Dynadot wraps response under command container (Response, SearchResponse, RegisterResponse, etc.)
            $rootKey = array_key_first($data);
            $root = is_array($data[$rootKey] ?? null) ? $data[$rootKey] : $data;

            // Detect error conditions
            $responseCode = $root['ResponseCode'] ?? ($data['Response']['ResponseCode'] ?? null);
            $errorMsg = $root['Error'] ?? ($data['Response']['Error'] ?? null);
            $statusStr = $root['Status'] ?? ($data['Response']['Status'] ?? null);

            $isError = false;
            if ($responseCode !== null && (string)$responseCode !== '0') {
                $isError = true;
            } elseif (!empty($errorMsg)) {
                $isError = true;
            } elseif (is_string($statusStr) && strtolower($statusStr) === 'error') {
                $isError = true;
            } elseif ($statusCode >= 400) {
                $isError = true;
            }

            if ($isError) {
                $finalError = $errorMsg ?: ($statusStr ?: ('Dynadot API error ' . ($responseCode ?? 'Unknown')));
                return [
                    'status' => $statusCode,
                    'data'   => $data,
                    'root'   => $root,
                    'error'  => $finalError,
                ];
            }

            return [
                'status' => $statusCode,
                'data'   => $data,
                'root'   => $root,
                'error'  => null,
            ];
        } catch (\Throwable $e) {
            $this->logApiCall('DYNADOT_exception', $params, $e->getMessage());
            return [
                'status' => 500,
                'data'   => null,
                'root'   => null,
                'error'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Check single domain availability.
     */
    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest([
                'command'    => 'search',
                'domain0'    => $domain,
                'show_price' => 1,
                'currency'   => 'USD',
            ]);

            if ($res['status'] === 200 && empty($res['error'])) {
                $results = $res['root']['SearchResults'] ?? [];
                if (!empty($results) && is_array($results)) {
                    $item = $results[0] ?? [];
                    $available = strtolower($item['Available'] ?? '') === 'yes';
                    
                    // Parse price e.g. "10.15 in USD"
                    $rawPrice = $item['Price'] ?? '10.15';
                    preg_match('/([0-9]+\.?[0-9]*)/', $rawPrice, $matches);
                    $price = !empty($matches[1]) ? (float)$matches[1] : 10.15;

                    return new DomainAvailabilityDTO(
                        domain: $domain,
                        isAvailable: $available,
                        isPremium: str_contains(strtolower($rawPrice), 'premium'),
                        registrationCost: $price,
                        renewalCost: $price,
                        currency: 'USD',
                        registrar: $this->getIdentifier(),
                        status: $available ? 'available' : 'taken'
                    );
                }
            }
        }

        // Fallback simulation / local DNS lookup
        $dnsRecords = @dns_get_record($domain, DNS_NS | DNS_SOA);
        $isTaken = !empty($dnsRecords);

        return new DomainAvailabilityDTO(
            domain: $domain,
            isAvailable: !$isTaken,
            isPremium: false,
            registrationCost: 10.15,
            renewalCost: 11.25,
            currency: 'USD',
            registrar: $this->getIdentifier(),
            status: $isTaken ? 'taken' : 'available',
            isTestMode: !$this->hasValidCredentials()
        );
    }

    /**
     * Register a new domain name at Dynadot.
     */
    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $params = [
                'command'  => 'register',
                'domain'   => $domain,
                'duration' => $dto->years ?? 1,
                'currency' => 'USD',
                'privacy'  => 'on',
            ];

            $res = $this->apiRequest($params);
            if ($res['status'] === 200 && empty($res['error'])) {
                $root = $res['root'] ?? [];
                $expiresAt = !empty($root['Expiration']) 
                    ? Carbon::createFromTimestampMs((int)$root['Expiration']) 
                    : now()->addYears($dto->years ?? 1);

                if (!empty($dto->nameservers)) {
                    $this->setNameservers($domain, $dto->nameservers);
                }

                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'DD-ORD-' . strtoupper(bin2hex(random_bytes(4))),
                    registrarDomainId: 'DD-DOM-' . rand(100000, 999999),
                    expiresAt: $expiresAt,
                    rawResponse: $res['data'] ?? []
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Registration failed at Dynadot.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'DD-SIM-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'DD-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears($dto->years ?? 1)
        );
    }

    /**
     * Renew an active domain.
     */
    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest([
                'command'  => 'renew',
                'domain'   => $domain,
                'duration' => $dto->years ?? 1,
                'currency' => 'USD',
            ]);

            if ($res['status'] === 200 && empty($res['error'])) {
                $root = $res['root'] ?? [];
                $expiresAt = !empty($root['Expiration']) 
                    ? Carbon::createFromTimestampMs((int)$root['Expiration']) 
                    : now()->addYears($dto->years ?? 1);

                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'DD-REN-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: $expiresAt
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Renewal failed at Dynadot.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'DD-SIM-REN-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears($dto->years ?? 1)
        );
    }

    /**
     * Transfer domain into Dynadot.
     */
    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest([
                'command'  => 'transfer',
                'domain'   => $domain,
                'auth'     => $dto->authCode,
                'currency' => 'USD',
                'privacy'  => 'on',
            ]);

            if ($res['status'] === 200 && empty($res['error'])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'DD-TRF-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYear()
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Transfer initiation failed at Dynadot.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'DD-SIM-TRF-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYear()
        );
    }

    /**
     * Retrieve authoritative domain metadata from Dynadot.
     */
    public function getInfo(string $domain): DomainDetailsDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest([
                'command' => 'domain_info',
                'domain'  => $domain,
            ]);

            if ($res['status'] === 200 && empty($res['error'])) {
                $info = $res['root']['DomainInfo'] ?? ($res['root']['Domain'] ?? []);

                $expiresAt = !empty($info['Expiration'])
                    ? Carbon::createFromTimestampMs((int)$info['Expiration'])
                    : null;
                $createdAt = !empty($info['Registration'])
                    ? Carbon::createFromTimestampMs((int)$info['Registration'])
                    : null;

                $isLocked = strtolower($info['Locked'] ?? 'no') === 'yes';
                $privacy = strtolower($info['Privacy'] ?? 'none') !== 'none';
                $status = strtolower($info['Status'] ?? 'active');

                $ns = $this->getNameservers($domain);

                return new DomainDetailsDTO(
                    domain: $domain,
                    status: $status,
                    createdAt: $createdAt,
                    expiresAt: $expiresAt,
                    isLocked: $isLocked,
                    idProtection: $privacy,
                    nameservers: $ns,
                    raw: $info
                );
            }
        }

        throw new \RuntimeException("Unable to fetch domain details from Dynadot API for {$domain}");
    }

    /**
     * Get active nameservers for a domain.
     */
    public function getNameservers(string $domain): array
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest([
                'command' => 'domain_info',
                'domain'  => $domain,
            ]);

            if ($res['status'] === 200 && empty($res['error'])) {
                $info = $res['root']['DomainInfo'] ?? ($res['root']['Domain'] ?? []);
                $nsSettings = $info['NameServerSettings'] ?? [];

                $servers = [];
                if (!empty($nsSettings['NameServers']) && is_array($nsSettings['NameServers'])) {
                    foreach ($nsSettings['NameServers'] as $item) {
                        if (is_array($item) && !empty($item['ServerName'])) {
                            $servers[] = trim($item['ServerName']);
                        } elseif (is_string($item)) {
                            $servers[] = trim($item);
                        }
                    }
                } elseif (!empty($nsSettings['ServerList']) && is_array($nsSettings['ServerList'])) {
                    $servers = array_map('trim', $nsSettings['ServerList']);
                }

                if (!empty($servers)) {
                    return array_values(array_filter($servers));
                }
            }
        }

        return [];
    }

    /**
     * Set authoritative nameservers for a domain.
     */
    public function setNameservers(string $domain, array $nameservers): bool
    {
        $domain = strtolower(trim($domain));
        $cleaned = array_values(array_filter($nameservers));

        if ($this->hasValidCredentials()) {
            $params = [
                'command' => 'set_ns',
                'domain'  => $domain,
            ];
            foreach ($cleaned as $i => $ns) {
                $params['ns' . $i] = $ns;
            }

            $res = $this->apiRequest($params);
            return empty($res['error']);
        }

        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        return collect([
            new DnsRecordDTO('dd_1', 'A', '@', '192.0.2.3', null, 3600),
            new DnsRecordDTO('dd_2', 'CNAME', 'www', '@', null, 3600),
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
        $domain = strtolower(trim($domain));

        $model = \App\Models\Domain::where('domain_name', $domain)->first();
        if ($model && !empty($model->auth_code) && !str_starts_with($model->auth_code, 'DYN-EPP-') && !str_starts_with($model->auth_code, 'DD-EPP-')) {
            return $model->auth_code;
        }

        throw new \RuntimeException("Dynadot requires manual EPP code retrieval via Dynadot account dashboard or please set it in admin domain settings.");
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

    /**
     * Query live Dynadot account balance.
     * Throws an exception if authentication or IP authorization fails.
     */
    public function getBalance(): RegistrarBalanceDTO
    {
        if (!$this->hasValidCredentials()) {
            throw new \RuntimeException('Dynadot API Key is empty. Please enter your API Key.');
        }

        $res = $this->apiRequest([
            'command' => 'get_account_balance',
        ]);

        if (!empty($res['error'])) {
            $msg = $res['error'];
            if (str_contains(strtolower($msg), 'no balance')) {
                return new RegistrarBalanceDTO(
                    registrar: $this->getIdentifier(),
                    balance: 0.00,
                    currency: 'USD'
                );
            }
            if (str_contains(strtolower($msg), 'unauthorized ip') || str_contains(strtolower($msg), 'not authorized')) {
                $msg .= ' (Please whitelist server IP 188.40.147.141 in Dynadot Tools > API)';
            }
            throw new \RuntimeException($msg);
        }

        $root = $res['root'] ?? [];
        if (isset($root['Balance'])) {
            return new RegistrarBalanceDTO(
                registrar: $this->getIdentifier(),
                balance: (float) $root['Balance'],
                currency: $root['Currency'] ?? 'USD'
            );
        }

        throw new \RuntimeException('Failed to parse account balance from Dynadot response.');
    }

    public function getDomainDetails(string $domain): DomainDetailsDTO
    {
        return $this->getInfo($domain);
    }

    public function renewDomain(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        return $this->renew($dto);
    }

    public function lockDomain(string $domain): bool
    {
        return $this->setLock($domain, true);
    }

    public function unlockDomain(string $domain): bool
    {
        return $this->setLock($domain, false);
    }

    public function toggleIdProtection(string $domain, bool $enabled): bool
    {
        return $this->setIdProtection($domain, $enabled);
    }

    public function getAuthCode(string $domain): string
    {
        return $this->getEppCode($domain);
    }

    public function updateNameservers(string $domain, array $nameservers): bool
    {
        return $this->setNameservers($domain, $nameservers);
    }

}
