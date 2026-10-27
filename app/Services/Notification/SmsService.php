<?php

namespace App\Services\Notification;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected ?string $provider;
    protected ?string $apiKey;
    protected ?string $senderId;

    public function __construct()
    {
        $this->provider = SystemSetting::get('sms_provider', 'greenweb');
        $this->apiKey   = SystemSetting::get('sms_api_key', '');
        $this->senderId = SystemSetting::get('sms_sender_id', '');
    }

    /**
     * Send an SMS to a Bangladeshi recipient number.
     */
    public function send(string $to, string $message): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'SMS Gateway API key is not configured.'];
        }

        // Format BD phone number (e.g. 017xxxxxxxx -> +88017xxxxxxxx or 88017xxxxxxxx)
        $cleanNumber = preg_replace('/[^0-9]/', '', $to);
        if (strlen($cleanNumber) === 11 && str_starts_with($cleanNumber, '01')) {
            $formattedNumber = '88' . $cleanNumber;
        } elseif (strlen($cleanNumber) === 13 && str_starts_with($cleanNumber, '8801')) {
            $formattedNumber = $cleanNumber;
        } else {
            $formattedNumber = $cleanNumber;
        }

        try {
            switch ($this->provider) {
                case 'greenweb':
                    $res = Http::asForm()->post('https://api.greenweb.com.bd/api.php', [
                        'token'   => $this->apiKey,
                        'to'      => $formattedNumber,
                        'message' => $message,
                    ]);
                    $body = $res->body();
                    $success = str_contains($body, 'Ok') || str_contains($body, '100');
                    return ['success' => $success, 'response' => $body];

                case 'bulksmsbd':
                    $res = Http::get('http://bulksmsbd.net/api/smsapi', [
                        'api_key'  => $this->apiKey,
                        'type'     => 'text',
                        'number'   => $formattedNumber,
                        'senderid' => $this->senderId,
                        'message'  => $message,
                    ]);
                    $json = $res->json();
                    $success = ($json['response_code'] ?? 0) === 202;
                    return ['success' => $success, 'response' => $res->body()];

                case 'alphasms':
                    $res = Http::asJson()->post('https://api.sms.net.bd/sendsms', [
                        'api_key' => $this->apiKey,
                        'msg'     => $message,
                        'to'      => $formattedNumber,
                        'sender_id' => $this->senderId,
                    ]);
                    $json = $res->json();
                    $success = ($json['error'] ?? 1) === 0;
                    return ['success' => $success, 'response' => $res->body()];

                default:
                    return ['success' => false, 'message' => "Unsupported SMS provider: {$this->provider}"];
            }
        } catch (\Throwable $e) {
            Log::channel('single')->error("SMS Send failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check SMS Balance from configured provider.
     */
    public function getBalance(): string
    {
        if (empty($this->apiKey)) {
            return 'Not configured';
        }

        try {
            switch ($this->provider) {
                case 'greenweb':
                    $res = Http::get('https://api.greenweb.com.bd/g_api.php', [
                        'token' => $this->apiKey,
                        'balance' => 'true',
                    ]);
                    return trim($res->body()) . ' SMS';

                case 'bulksmsbd':
                    $res = Http::get('http://bulksmsbd.net/api/getBalanceApi', [
                        'api_key' => $this->apiKey,
                    ]);
                    $json = $res->json();
                    return ($json['balance'] ?? '0') . ' BDT';

                case 'alphasms':
                    $res = Http::asJson()->post('https://api.sms.net.bd/user/balance', [
                        'api_key' => $this->apiKey,
                    ]);
                    $json = $res->json();
                    return ($json['data']['balance'] ?? '0') . ' Credits';

                default:
                    return 'N/A';
            }
        } catch (\Throwable $e) {
            return 'Error checking balance: ' . $e->getMessage();
        }
    }
}
