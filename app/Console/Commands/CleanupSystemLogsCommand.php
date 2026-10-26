<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\LoginHistory;
use App\Models\WebhookLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupSystemLogsCommand extends Command
{
    protected $signature = 'system:cleanup-logs {--days=90}';
    protected $description = 'Prune old activity logs, webhook logs, and login histories to optimize database performance';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $this->info("Pruning logs older than {$days} days (before {$cutoff->toDateString()})...");

        $activityCount = ActivityLog::where('created_at', '<', $cutoff)->delete();
        $this->line("- Pruned {$activityCount} activity logs.");

        $webhookCount = WebhookLog::where('created_at', '<', $cutoff)->delete();
        $this->line("- Pruned {$webhookCount} webhook delivery logs.");

        $loginCount = LoginHistory::where('login_at', '<', $cutoff)->delete();
        $this->line("- Pruned {$loginCount} login history records.");

        // Prune old failed jobs older than cutoff if any
        $failedCount = DB::table('failed_jobs')->where('failed_at', '<', $cutoff)->delete();
        $this->line("- Pruned {$failedCount} old failed jobs.");

        $this->info('Database log maintenance completed successfully.');
        return Command::SUCCESS;
    }
}
