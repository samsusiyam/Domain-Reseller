<?php
/**
 * WHMCS Domain Registrar Module - DomainReseller
 *
 * Enables automated domain registrations, renewals, incoming transfers, DNS zone
 * records, authoritative nameserver management, and WHOIS contact synchronization.
 *
 * @version 1.0.0
 * @author DomainReseller Engineering
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/lib/ApiClient.php';

use DomainReseller\WhmcsModule\ApiClient;

/**
 * Define module metadata.
 */
function domainreseller_MetaData()
{
    return [
        'DisplayName' => 'DomainReseller Wholesale Module',
        'APIVersion' => '1.1',
        'RequiresServer' => false,
        'CheckAvailability' => true,
        'GetDomainSuggestions' => true,
    ];
}

/**
 * Define module configuration parameters shown in WHMCS Admin Area.
 */
function domainreseller_getConfigArray()
{
    return [
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'DomainReseller API Gateway',
        ],
        'ApiUrl' => [
            'FriendlyName' => 'API Endpoint URL',
            'Type' => 'text',
            'Size' => '60',
            'Default' => 'https://yourplatform.com/api/v1',
            'Description' => 'The base URL to your Domain Reseller API endpoint (e.g., https://yourdomain.com/api/v1).',
        ],
        'ApiKey' => [
            'FriendlyName' => 'API Public Key',
            'Type' => 'text',
            'Size' => '50',
            'Description' => 'Your reseller public key identifier (pk_...).',
        ],
        'ApiSecret' => [
            'FriendlyName' => 'API Secret Key',
            'Type' => 'password',
            'Size' => '50',
            'Description' => 'Your reseller secret API token (sk_...).',
        ],
        'AutoRenewDefault' => [
            'FriendlyName' => 'Enable Auto-Renew',
            'Type' => 'yesno',
            'Description' => 'Tick to automatically enable auto-renew when domains are registered.',
        ],
        'TestMode' => [
            'FriendlyName' => 'Sandbox Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to connect to sandbox testing endpoints if configured.',
        ],
    ];
}

/**
 * Helper to initialize API Client.
 */
function domainreseller_getApiClient(array $params): ApiClient
{
    $url = $params['ApiUrl'] ?? 'https://yourplatform.com/api/v1';
    $key = $params['ApiKey'] ?? '';
    $secret = $params['ApiSecret'] ?? '';

    return new ApiClient($url, $key, $secret);
}

/**
 * Register a new domain name.
 */
function domainreseller_RegisterDomain(array $params)
{
    $client = domainreseller_getApiClient($params);

    $domain = $params['sld'] . '.' . $params['tld'];
    $regPeriod = (int) ($params['regperiod'] ?? 1);

    $nameservers = [];
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($params['ns' . $i])) {
            $nameservers[] = $params['ns' . $i];
        }
    }

    $payload = [
        'domain_name'   => $domain,
        'period_years'  => $regPeriod,
        'nameservers'   => $nameservers,
        'id_protection' => (bool) ($params['idprotection'] ?? false),
        'contact'       => [
            'first_name'   => $params['firstname'] ?? '',
            'last_name'    => $params['lastname'] ?? '',
            'company_name' => $params['companyname'] ?? '',
            'email'        => $params['email'] ?? '',
            'phone'        => $params['phonenumber'] ?? '',
            'address_1'    => $params['address1'] ?? '',
            'address_2'    => $params['address2'] ?? '',
            'city'         => $params['city'] ?? '',
            'state'        => $params['state'] ?? '',
            'postal_code'  => $params['postcode'] ?? '',
            'country'      => $params['countrycode'] ?? ($params['country'] ?? 'US'),
        ],
    ];

    $response = $client->post('domains/register', $payload);

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Renew an existing domain name.
 */
function domainreseller_RenewDomain(array $params)
{
    $client = domainreseller_getApiClient($params);

    $domain = $params['sld'] . '.' . $params['tld'];
    $regPeriod = (int) ($params['regperiod'] ?? 1);

    $payload = [
        'domain_name'  => $domain,
        'period_years' => $regPeriod,
    ];

    $response = $client->post('domains/renew', $payload);

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Transfer a domain from another registrar.
 */
function domainreseller_TransferDomain(array $params)
{
    $client = domainreseller_getApiClient($params);

    $domain = $params['sld'] . '.' . $params['tld'];
    $eppCode = $params['eppcode'] ?? ($params['transfersecret'] ?? '');

    $payload = [
        'domain_name'   => $domain,
        'epp_code'      => $eppCode,
        'period_years'  => (int) ($params['regperiod'] ?? 1),
        'id_protection' => (bool) ($params['idprotection'] ?? false),
    ];

    $response = $client->post('domains/transfer', $payload);

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Retrieve active authoritative nameservers.
 */
function domainreseller_GetNameservers(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $response = $client->get('domains/' . urlencode($domain) . '/nameservers');

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    $nsList = $response['data']['nameservers'] ?? [];

    $result = [];
    for ($i = 1; $i <= 5; $i++) {
        $result['ns' . $i] = $nsList[$i - 1] ?? '';
    }

    return $result;
}

/**
 * Update authoritative nameservers.
 */
function domainreseller_SaveNameservers(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $nameservers = [];
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($params['ns' . $i])) {
            $nameservers[] = $params['ns' . $i];
        }
    }

    $response = $client->put('domains/' . urlencode($domain) . '/nameservers', [
        'nameservers' => $nameservers,
    ]);

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Get Registrar Lock status.
 */
function domainreseller_GetRegistrarLock(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $response = $client->get('domains/' . urlencode($domain));

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    $isLocked = $response['data']['is_locked'] ?? false;
    return $isLocked ? 'locked' : 'unlocked';
}

/**
 * Save Registrar Lock status.
 */
function domainreseller_SaveRegistrarLock(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $lockStatus = $params['lockenabled'] === 'locked';

    $response = $client->post('domains/' . urlencode($domain) . '/lock', [
        'is_locked' => $lockStatus,
    ]);

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Get Hosted DNS zone records.
 */
function domainreseller_GetDNS(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $response = $client->get('domains/' . urlencode($domain) . '/dns');

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    $records = [];
    $rawRecords = $response['data']['records'] ?? [];

    foreach ($rawRecords as $rec) {
        $records[] = [
            'hostname' => $rec['name'] ?? '@',
            'type'     => $rec['type'] ?? 'A',
            'address'  => $rec['content'] ?? ($rec['value'] ?? ''),
            'priority' => $rec['priority'] ?? 0,
        ];
    }

    return $records;
}

/**
 * Save Hosted DNS zone records.
 */
function domainreseller_SaveDNS(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $dnsRecords = $params['dnsrecords'] ?? [];
    $formatted = [];

    foreach ($dnsRecords as $rec) {
        if (!empty($rec['address'])) {
            $formatted[] = [
                'name'     => $rec['hostname'],
                'type'     => $rec['type'],
                'content'  => $rec['address'],
                'priority' => (int) ($rec['priority'] ?? 0),
                'ttl'      => 3600,
            ];
        }
    }

    $response = $client->post('domains/' . urlencode($domain) . '/dns/batch', [
        'records' => $formatted,
    ]);

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Get Contact / WHOIS Details.
 */
function domainreseller_GetContactDetails(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $response = $client->get('domains/' . urlencode($domain) . '/whois');

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    $data = $response['data'] ?? [];
    $registrant = $data['registrant'] ?? $data;

    $contacts = [
        'Registrant' => [
            'First Name'   => $registrant['first_name'] ?? '',
            'Last Name'    => $registrant['last_name'] ?? '',
            'Company Name' => $registrant['company_name'] ?? '',
            'Email Address'=> $registrant['email'] ?? '',
            'Address 1'    => $registrant['address_1'] ?? '',
            'City'         => $registrant['city'] ?? '',
            'State'        => $registrant['state'] ?? '',
            'Postcode'     => $registrant['postal_code'] ?? '',
            'Country'      => $registrant['country'] ?? 'US',
            'Phone Number' => $registrant['phone'] ?? '',
        ],
    ];

    $contacts['Admin'] = $contacts['Registrant'];
    $contacts['Tech'] = $contacts['Registrant'];
    $contacts['Billing'] = $contacts['Registrant'];

    return $contacts;
}

/**
 * Save Contact / WHOIS Details.
 */
function domainreseller_SaveContactDetails(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $reg = $params['contactdetails']['Registrant'] ?? [];

    $payload = [
        'registrant' => [
            'first_name'   => $reg['First Name'] ?? '',
            'last_name'    => $reg['Last Name'] ?? '',
            'company_name' => $reg['Company Name'] ?? '',
            'email'        => $reg['Email Address'] ?? '',
            'address_1'    => $reg['Address 1'] ?? '',
            'city'         => $reg['City'] ?? '',
            'state'        => $reg['State'] ?? '',
            'postal_code'  => $reg['Postcode'] ?? '',
            'country'      => $reg['Country'] ?? 'US',
            'phone'        => $reg['Phone Number'] ?? '',
        ],
    ];

    $response = $client->put('domains/' . urlencode($domain) . '/whois', $payload);

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Get domain EPP transfer code.
 */
function domainreseller_GetEPPCode(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $response = $client->post('domains/' . urlencode($domain) . '/epp');

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['eppcode' => $response['data']['epp_code'] ?? ''];
}

/**
 * Toggle ID Protection / Privacy.
 */
function domainreseller_IDProtectToggle(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $protect = (bool) ($params['protectenable'] ?? false);

    $response = $client->post('domains/' . urlencode($domain) . '/privacy', [
        'is_private' => $protect,
    ]);

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Automated Domain Synchronization Cron.
 */
function domainreseller_Sync(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $response = $client->get('domains/' . urlencode($domain));

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    $d = $response['data'] ?? [];

    $status = $d['status'] ?? 'active';
    $active = ($status === 'active');
    $expired = ($status === 'expired');

    $expiryDate = !empty($d['expires_at']) ? date('Y-m-d', strtotime($d['expires_at'])) : null;

    return [
        'active'     => $active,
        'expired'    => $expired,
        'expirydate' => $expiryDate,
    ];
}

/**
 * Automated Transfer Status Synchronization Cron.
 */
function domainreseller_TransferSync(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];

    $response = $client->get('domains/' . urlencode($domain));

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    $d = $response['data'] ?? [];

    if (($d['status'] ?? '') === 'active') {
        return [
            'completed'  => true,
            'expirydate' => !empty($d['expires_at']) ? date('Y-m-d', strtotime($d['expires_at'])) : date('Y-m-d'),
        ];
    } elseif (($d['status'] ?? '') === 'cancelled' || ($d['status'] ?? '') === 'failed') {
        return [
            'failed' => true,
            'reason' => 'Transfer rejected by registry or lost authorization',
        ];
    }

    return ['pending' => true];
}


/**
 * Register a child / private nameserver (glue record).
 */
function domainreseller_RegisterNameserver(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];
    $nameserver = $params['nameserver'];
    $ip = $params['ipaddress'];

    $response = $client->post('domains/' . urlencode($domain) . '/child-nameservers', [
        'hostname'   => $nameserver,
        'ip_address' => $ip,
    ]);

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Modify a child / private nameserver IP address.
 */
function domainreseller_ModifyNameserver(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];
    $nameserver = $params['nameserver'];
    $newIp = $params['newipaddress'];

    $response = $client->put('domains/' . urlencode($domain) . '/child-nameservers/' . urlencode($nameserver), [
        'ip_address' => $newIp,
    ]);

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Delete a child / private nameserver.
 */
function domainreseller_DeleteNameserver(array $params)
{
    $client = domainreseller_getApiClient($params);
    $domain = $params['sld'] . '.' . $params['tld'];
    $nameserver = $params['nameserver'];

    $response = $client->delete('domains/' . urlencode($domain) . '/child-nameservers/' . urlencode($nameserver));

    if (!$response['success']) {
        return ['error' => $response['error']];
    }

    return ['success' => true];
}

/**
 * Check domain availability (WHMCS 7+ Domain Lookup Provider).
 */
function domainreseller_CheckAvailability(array $params)
{
    $client = domainreseller_getApiClient($params);
    $searchTerm = strtolower(trim($params['searchTerm'] ?? ''));
    $tldsToInclude = $params['tldsToInclude'] ?? [];

    $domainsToCheck = [];
    if (!empty($tldsToInclude)) {
        foreach ($tldsToInclude as $tld) {
            $tldClean = '.' . ltrim($tld, '.');
            $domainsToCheck[] = $searchTerm . $tldClean;
        }
    } else {
        $domainsToCheck[] = $searchTerm;
    }

    $response = $client->post('domains/check-bulk', [
        'domains' => $domainsToCheck,
    ]);

    // Handle WHMCS native SearchResult object list if available
    if (class_exists('\\WHMCS\\Domains\\DomainLookup\\ResultsList') && class_exists('\\WHMCS\\Domains\\DomainLookup\\SearchResult')) {
        $results = new \WHMCS\Domains\DomainLookup\ResultsList();

        if (!$response['success'] || empty($response['data'])) {
            return $results;
        }

        foreach ($response['data'] as $item) {
            $domainName = $item['domain'] ?? '';
            $parts = explode('.', $domainName, 2);
            $sld = $parts[0] ?? '';
            $tld = $parts[1] ?? '';

            $searchResult = new \WHMCS\Domains\DomainLookup\SearchResult($sld, $tld);

            if (!empty($item['is_available'])) {
                $searchResult->setStatus(\WHMCS\Domains\DomainLookup\SearchResult::STATUS_NOT_REGISTERED);
            } elseif (($item['status'] ?? '') === 'unsupported_tld') {
                $searchResult->setStatus(\WHMCS\Domains\DomainLookup\SearchResult::STATUS_TLD_NOT_SUPPORTED);
            } else {
                $searchResult->setStatus(\WHMCS\Domains\DomainLookup\SearchResult::STATUS_REGISTERED);
            }

            if (!empty($item['price_usd'])) {
                $searchResult->setRegisterPrice($item['price_usd']);
            }

            $results->append($searchResult);
        }

        return $results;
    }

    return $response['data'] ?? [];
}

/**
 * Provide domain name suggestions (WHMCS 7+ Domain Lookup Provider).
 */
function domainreseller_GetDomainSuggestions(array $params)
{
    $client = domainreseller_getApiClient($params);
    $searchTerm = strtolower(trim($params['searchTerm'] ?? ''));

    $response = $client->get('domains/suggestions', [
        'keyword' => $searchTerm,
    ]);

    if (class_exists('\\WHMCS\\Domains\\DomainLookup\\ResultsList') && class_exists('\\WHMCS\\Domains\\DomainLookup\\SearchResult')) {
        $results = new \WHMCS\Domains\DomainLookup\ResultsList();

        if (!$response['success'] || empty($response['data'])) {
            return $results;
        }

        foreach ($response['data'] as $item) {
            $domainName = $item['domain'] ?? '';
            $parts = explode('.', $domainName, 2);
            $sld = $parts[0] ?? '';
            $tld = $parts[1] ?? '';

            $searchResult = new \WHMCS\Domains\DomainLookup\SearchResult($sld, $tld);
            $searchResult->setStatus(\WHMCS\Domains\DomainLookup\SearchResult::STATUS_NOT_REGISTERED);

            if (!empty($item['price_usd'])) {
                $searchResult->setRegisterPrice($item['price_usd']);
            }

            $results->append($searchResult);
        }

        return $results;
    }

    return $response['data'] ?? [];
}
