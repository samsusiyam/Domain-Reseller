<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookLog;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 15;

    public function __construct(
        protected int $webhookId,
        protected string $event,
        protected array $payload
    ) {}

    public function handle(): void
    {
        $webhook = Webhook::find($this->webhookId);
        if (!$webhook || !$webhook->is_active) {
            return;
        }

        $startTime = microtime(true);
        $jsonPayload = json_encode([
            'event' => $this->event,
            'timestamp' => now()->toIso8601String(),
            'data' => $this->payload,
        ]);

        $signature = hash_hmac('sha256', $jsonPayload, $webhook->secret_key);

        $client = new Client([
            'timeout' => 10,
            'http_errors' => false,
            'verify' => false,
        ]);

        $status = 'failed';
        $responseCode = null;
        $responseBody = null;

        try {
            $response = $client->post($webhook->target_url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'DomainReseller-Webhook/1.0',
                    'X-Signature-SHA256' => $signature,
                    'X-Event' => $this->event,
                ],
                'body' => $jsonPayload,
            ]);

            $responseCode = $response->getStatusCode();
            $responseBody = substr((string) $response->getBody(), 0, 1000);
            $status = ($responseCode >= 200 && $responseCode < 300) ? 'success' : 'failed';

            if ($status === 'success') {
                $webhook->update(['failure_count' => 0, 'last_triggered_at' => now()]);
            } else {
                $webhook->increment('failure_count');
            }
        } catch (\Throwable $e) {
            $responseBody = substr($e->getMessage(), 0, 1000);
            $webhook->increment('failure_count');
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event_name' => $this->event,
            'payload' => $this->payload,
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'execution_time_ms' => $durationMs,
            'status' => $status,
        ]);
    }
}
