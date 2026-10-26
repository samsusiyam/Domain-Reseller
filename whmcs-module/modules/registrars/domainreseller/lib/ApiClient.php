<?php

namespace DomainReseller\WhmcsModule;

/**
 * DomainReseller API Client for WHMCS
 * 
 * Handles programmatic communication between reseller's WHMCS instance 
 * and the upstream Domain Reseller REST API.
 */
class ApiClient
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $apiSecret;
    protected int $timeout;

    public function __construct(string $apiUrl, string $apiKey, string $apiSecret, int $timeout = 60)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = trim($apiKey);
        $this->apiSecret = trim($apiSecret);
        $this->timeout = $timeout;
    }

    /**
     * Perform HTTP GET request.
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        return $this->request('GET', $endpoint, $queryParams);
    }

    /**
     * Perform HTTP POST request.
     */
    public function post(string $endpoint, array $body = []): array
    {
        return $this->request('POST', $endpoint, $body);
    }

    /**
     * Perform HTTP PUT request.
     */
    public function put(string $endpoint, array $body = []): array
    {
        return $this->request('PUT', $endpoint, $body);
    }

    /**
     * Perform HTTP DELETE request.
     */
    public function delete(string $endpoint, array $body = []): array
    {
        return $this->request('DELETE', $endpoint, $body);
    }

    /**
     * Execute cURL request with authentication headers.
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->apiUrl . '/' . ltrim($endpoint, '/');

        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Api-Key: ' . $this->apiKey,
            'X-Api-Secret: ' . $this->apiSecret,
            'User-Agent: WHMCS-DomainReseller-Module/1.0',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'error'   => 'Network / cURL error: ' . $curlError,
                'status'  => 0,
            ];
        }

        $decoded = json_decode($responseBody, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data'    => $decoded['data'] ?? $decoded,
                'raw'     => $decoded,
                'status'  => $httpCode,
            ];
        }

        $errorMessage = $decoded['message'] ?? $decoded['error'] ?? ('HTTP Error ' . $httpCode);
        if (isset($decoded['errors']) && is_array($decoded['errors'])) {
            $flatErrors = [];
            foreach ($decoded['errors'] as $field => $errs) {
                $flatErrors[] = is_array($errs) ? implode(', ', $errs) : $errs;
            }
            $errorMessage .= ' (' . implode('; ', $flatErrors) . ')';
        }

        return [
            'success' => false,
            'error'   => $errorMessage,
            'status'  => $httpCode,
            'raw'     => $decoded,
        ];
    }
}
