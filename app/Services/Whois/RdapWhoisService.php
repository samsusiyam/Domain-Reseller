<?php

namespace App\Services\Whois;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RdapWhoisService
{
    protected const IANA_BOOTSTRAP_URL = 'https://data.iana.org/rdap/dns.json';
    protected const TIMEOUT_SECONDS = 8;
    protected const CACHE_TTL_MINUTES = 30;

    /**
     * Normalize domain name (punycode, lowercase, remove http/www/slashes).
     */
    public function normalizeDomain(string $input): string
    {
        $domain = trim($input);
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = preg_replace('#^www\.#i', '', $domain);
        $domain = explode('/', $domain)[0];
        $domain = explode(':', $domain)[0];
        $domain = mb_strtolower(trim($domain));

        if (function_exists('idn_to_ascii')) {
            $converted = @idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($converted !== false && !empty($converted)) {
                $domain = $converted;
            }
        }

        return $domain;
    }

    /**
     * Query domain RDAP/WHOIS records.
     */
    public function lookup(string $domain): array
    {
        $normalized = $this->normalizeDomain($domain);

        if (empty($normalized) || !str_contains($normalized, '.')) {
            return [
                'success' => false,
                'error' => 'Invalid domain name provided.',
                'domain' => $domain,
                'is_registered' => false,
            ];
        }

        $cacheKey = 'whois_rdap:' . md5($normalized);
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($normalized) {
            return $this->performLookup($normalized);
        });
    }

    protected function performLookup(string $domain): array
    {
        $parts = explode('.', $domain);
        $tld = end($parts);

        // 1. Primary: Query via RDAP
        $rdapBaseUrl = $this->getRdapServerForTld($tld);

        if ($rdapBaseUrl) {
            $queryUrl = rtrim($rdapBaseUrl, '/') . '/domain/' . rawurlencode($domain);
            try {
                $response = Http::timeout(self::TIMEOUT_SECONDS)
                    ->withHeaders([
                        'Accept' => 'application/rdap+json, application/json',
                        'User-Agent' => 'DomainReseller-RDAP/1.0',
                    ])
                    ->get($queryUrl);

                if ($response->status() === 404) {
                    return [
                        'success' => true,
                        'is_registered' => false,
                        'domain' => $domain,
                        'tld' => $tld,
                        'message' => 'Domain is not registered.',
                        'raw' => "Domain Name: {$domain}\nStatus: Not registered (RDAP 404 Not Found)",
                    ];
                }

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->formatRdapResponse($domain, $tld, $data);
                }
            } catch (\Exception $e) {
                Log::warning("RDAP lookup failed for {$domain}: " . $e->getMessage());
            }
        }

        // 2. Secondary Fallback: Port 43 WHOIS Socket
        return $this->lookupPort43Whois($domain, $tld);
    }

    /**
     * Retrieve IANA bootstrap RDAP server URL for a TLD.
     */
    protected function getRdapServerForTld(string $tld): ?string
    {
        $services = Cache::remember('iana_rdap_dns_bootstrap', now()->addHours(24), function () {
            try {
                $res = Http::timeout(self::TIMEOUT_SECONDS)->get(self::IANA_BOOTSTRAP_URL);
                if ($res->successful()) {
                    return $res->json()['services'] ?? [];
                }
            } catch (\Exception $e) {
                Log::warning("Failed to fetch IANA RDAP bootstrap: " . $e->getMessage());
            }
            return [];
        });

        foreach ($services as $service) {
            $tlds = $service[0] ?? [];
            $endpoints = $service[1] ?? [];
            if (in_array(strtolower($tld), array_map('strtolower', $tlds), true) && !empty($endpoints)) {
                return $endpoints[0];
            }
        }

        // Fallbacks for common top-level domains
        $commonEndpoints = [
            'com' => 'https://rdap.verisign.com/com/v1/',
            'net' => 'https://rdap.verisign.com/net/v1/',
            'org' => 'https://rdap.publicinterestregistry.org/rdap/',
            'io'  => 'https://rdap.nic.io/',
            'xyz' => 'https://rdap.centralnic.com/xyz/',
        ];

        return $commonEndpoints[strtolower($tld)] ?? null;
    }

    /**
     * Parse structured RDAP JSON attributes.
     */
    protected function formatRdapResponse(string $domain, string $tld, array $data): array
    {
        // Dates
        $registrationDate = null;
        $expirationDate = null;
        $lastChangedDate = null;

        foreach ($data['events'] ?? [] as $event) {
            $action = $event['eventAction'] ?? '';
            $date = $event['eventDate'] ?? null;
            if ($action === 'registration') {
                $registrationDate = $date;
            } elseif ($action === 'expiration') {
                $expirationDate = $date;
            } elseif ($action === 'last changed' || $action === 'last update') {
                $lastChangedDate = $date;
            }
        }

        // Registrar
        $registrarName = null;
        $registrarIanaId = null;
        $registrantName = 'Redacted for Privacy (GDPR/ICANN Specification)';
        $registrantOrg = null;
        $registrantCountry = null;

        foreach ($data['entities'] ?? [] as $entity) {
            $roles = $entity['roles'] ?? [];
            if (in_array('registrar', $roles, true)) {
                $registrarName = $this->extractVcardName($entity) ?? $entity['handle'] ?? null;
                $registrarIanaId = $entity['publicIds'][0]['identifier'] ?? null;
            } elseif (in_array('registrant', $roles, true)) {
                $name = $this->extractVcardName($entity);
                if ($name && !preg_match('/privacy|redacted|whoisguard/i', $name)) {
                    $registrantName = $name;
                }
                $registrantOrg = $this->extractVcardOrg($entity);
                $registrantCountry = $this->extractVcardCountry($entity);
            }
        }

        // Nameservers
        $nameservers = [];
        foreach ($data['nameservers'] ?? [] as $ns) {
            $nsName = $ns['ldhName'] ?? $ns['handle'] ?? null;
            if ($nsName) {
                $nameservers[] = strtolower($nsName);
            }
        }

        // DNSSEC
        $dnssec = false;
        if (!empty($data['secureDNS']['delegationSigned'])) {
            $dnssec = (bool) $data['secureDNS']['delegationSigned'];
        }

        // Status List
        $statusList = $data['status'] ?? ['active'];

        return [
            'success' => true,
            'is_registered' => true,
            'protocol' => 'RDAP',
            'domain' => $domain,
            'tld' => $tld,
            'registrar' => $registrarName ?? 'Not disclosed',
            'registrar_iana_id' => $registrarIanaId,
            'created_at' => $registrationDate,
            'expires_at' => $expirationDate,
            'updated_at' => $lastChangedDate,
            'nameservers' => $nameservers,
            'dnssec' => $dnssec ? 'Signed' : 'Unsigned',
            'status' => $statusList,
            'registrant' => [
                'name' => $registrantName,
                'organization' => $registrantOrg ?? 'Redacted for Privacy',
                'country' => $registrantCountry ?? 'Redacted',
            ],
            'raw' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];
    }

    /**
     * Fallback to port 43 socket WHOIS query.
     */
    protected function lookupPort43Whois(string $domain, string $tld): array
    {
        $whoisServers = [
            'com' => 'whois.verisign-grs.com',
            'net' => 'whois.verisign-grs.com',
            'org' => 'whois.pir.org',
            'info' => 'whois.afilias.net',
            'biz' => 'whois.biz',
            'io' => 'whois.nic.io',
            'co' => 'whois.nic.co',
            'me' => 'whois.nic.me',
            'us' => 'whois.nic.us',
            'ca' => 'whois.cira.ca',
            'uk' => 'whois.nic.uk',
            'xyz' => 'whois.nic.xyz',
        ];

        $server = $whoisServers[strtolower($tld)] ?? 'whois.iana.org';

        $output = '';
        try {
            $fp = @fsockopen($server, 43, $errno, $errstr, self::TIMEOUT_SECONDS);
            if (!$fp) {
                return [
                    'success' => false,
                    'error' => "Unable to contact WHOIS server for .{$tld}.",
                    'domain' => $domain,
                    'is_registered' => false,
                ];
            }

            stream_set_timeout($fp, self::TIMEOUT_SECONDS);
            fputs($fp, $domain . "\r\n");

            while (!feof($fp)) {
                $output .= fgets($fp, 128);
            }
            fclose($fp);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "WHOIS query timed out or failed: " . $e->getMessage(),
                'domain' => $domain,
                'is_registered' => false,
            ];
        }

        // Check if domain is not registered
        if (preg_match('/(No match for|NOT FOUND|Domain not found|No Data Found|Status: free|No entries found)/i', $output)) {
            return [
                'success' => true,
                'is_registered' => false,
                'domain' => $domain,
                'tld' => $tld,
                'message' => 'Domain is not registered.',
                'raw' => $output,
            ];
        }

        // Parse key attributes from text
        $registrar = null;
        if (preg_match('/Registrar:\s*([^\r\n]+)/i', $output, $m)) {
            $registrar = trim($m[1]);
        }

        $createdAt = null;
        if (preg_match('/(Creation Date|Created On|Registration Time):\s*([^\r\n]+)/i', $output, $m)) {
            $createdAt = trim($m[2]);
        }

        $expiresAt = null;
        if (preg_match('/(Registry Expiry Date|Expiration Date|Expires on):\s*([^\r\n]+)/i', $output, $m)) {
            $expiresAt = trim($m[2]);
        }

        $updatedAt = null;
        if (preg_match('/(Updated Date|Last Updated On):\s*([^\r\n]+)/i', $output, $m)) {
            $updatedAt = trim($m[2]);
        }

        $nameservers = [];
        if (preg_match_all('/Name Server:\s*([^\r\n]+)/i', $output, $matches)) {
            $nameservers = array_values(array_unique(array_map('strtolower', array_map('trim', $matches[1]))));
        }

        $dnssec = preg_match('/DNSSEC:\s*signedDelegation/i', $output) ? 'Signed' : 'Unsigned';

        $statuses = [];
        if (preg_match_all('/Domain Status:\s*([^\r\n]+)/i', $output, $matches)) {
            foreach ($matches[1] as $s) {
                $statuses[] = trim(explode(' ', trim($s))[0]);
            }
        }

        return [
            'success' => true,
            'is_registered' => true,
            'protocol' => 'WHOIS (Port 43)',
            'domain' => $domain,
            'tld' => $tld,
            'registrar' => $registrar ?? 'Upstream Registrar',
            'created_at' => $createdAt,
            'expires_at' => $expiresAt,
            'updated_at' => $updatedAt,
            'nameservers' => $nameservers,
            'dnssec' => $dnssec,
            'status' => !empty($statuses) ? array_unique($statuses) : ['active'],
            'registrant' => [
                'name' => 'Redacted for Privacy (GDPR/ICANN Specification)',
                'organization' => 'Redacted for Privacy',
                'country' => 'Redacted',
            ],
            'raw' => $output,
        ];
    }

    protected function extractVcardName(array $entity): ?string
    {
        $vcard = $entity['vcardArray'][1] ?? [];
        foreach ($vcard as $entry) {
            if (($entry[0] ?? '') === 'fn' && !empty($entry[3])) {
                return (string) $entry[3];
            }
        }
        return null;
    }

    protected function extractVcardOrg(array $entity): ?string
    {
        $vcard = $entity['vcardArray'][1] ?? [];
        foreach ($vcard as $entry) {
            if (($entry[0] ?? '') === 'org' && !empty($entry[3])) {
                return is_array($entry[3]) ? implode(' ', $entry[3]) : (string) $entry[3];
            }
        }
        return null;
    }

    protected function extractVcardCountry(array $entity): ?string
    {
        $vcard = $entity['vcardArray'][1] ?? [];
        foreach ($vcard as $entry) {
            if (($entry[0] ?? '') === 'adr' && !empty($entry[3])) {
                $parts = is_array($entry[3]) ? $entry[3] : [$entry[3]];
                return end($parts) ?: null;
            }
        }
        return null;
    }
}
