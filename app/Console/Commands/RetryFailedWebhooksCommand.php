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

        $failedLogs = WebhookLog::where('is_success', false)
            ->where('retry_count', '<', 5)
            ->where('created_at', '>=', now()->subHours(24))
            ->with('webhook')
            ->take($limit)
            ->get();

        $this->info("Found {$failedLogs->count()} failed webhook events eligible for retry.");

        foreach ($failedLogs as $log) {
            if (!$log->webhook || !$log->webhook->is_active) continue;

            $log->increment('retry_count');
            DispatchWebhookJob::dispatch($log->webhook_id, $log->event, $log->payload ?? []);
            $this->line("Re-queued webhook delivery for Webhook #{$log->webhook_id} ({$log->event})");
        }

        $this->info('Webhook retry process completed.');
        return Command::SUCCESS;
    }
}
