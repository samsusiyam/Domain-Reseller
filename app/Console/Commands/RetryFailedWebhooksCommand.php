<?php

namespace App\Console\Commands;

use App\Models\WebhookLog;
use App\Jobs\DispatchWebhookJob;
use Illuminate\Console\Command;

class RetryFailedWebhooksCommand extends Command
{
    protected $signature = 'webhooks:retry-failed {--limit=50}';
    protected $description = 'Retry failed webhook deliveries with exponential backoff';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $failedLogs = WebhookLog::where('status', 'failed')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereHas('webhook', function ($q) {
                $q->where('is_active', true)->where('failure_count', '<', 5);
            })
            ->with('webhook')
            ->take($limit)
            ->get();

        $this->info("Found {$failedLogs->count()} failed webhook events eligible for retry.");

        foreach ($failedLogs as $log) {
            if (!$log->webhook || !$log->webhook->is_active) continue;

            $payload = is_array($log->payload) ? $log->payload : json_decode($log->payload, true);
            DispatchWebhookJob::dispatch($log->webhook_id, $log->event_name, $payload ?? []);
            $this->line("Re-queued webhook delivery for Webhook #{$log->webhook_id} ({$log->event_name})");
        }

        $this->info('Webhook retry process completed.');
        return Command::SUCCESS;
    }
}
