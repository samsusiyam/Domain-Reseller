<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Jobs\SendEmailJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringDomainsCommand extends Command
{
    protected $signature = 'domains:check-expiry {--days=30,15,7,3,1}';
    protected $description = 'Scan domains table for upcoming expirations and dispatch reminder notifications';

    public function handle(): int
    {
        $daysList = array_map('intval', explode(',', $this->option('days')));
        $today = Carbon::today();

        $this->info('Checking expiring domains for reminder thresholds: ' . implode(', ', $daysList) . ' days.');

        foreach ($daysList as $days) {
            $targetDate = $today->copy()->addDays($days)->toDateString();

            $expiringDomains = Domain::where('status', 'active')
                ->whereDate('expires_at', $targetDate)
                ->with('user')
                ->get();

            $this->line("Found {$expiringDomains->count()} domains expiring in {$days} days.");

            foreach ($expiringDomains as $domain) {
                if (!$domain->user) continue;

                $data = [
                    'client_name'   => $domain->user->name,
                    'domain_name'   => $domain->domain_name,
                    'expiry_date'   => $domain->expires_at->format('Y-m-d'),
                    'days_remain'   => $days,
                    'renewal_url'   => route('client.domains.show', $domain->id),
                    'company_name'  => config('app.name'),
                ];

                SendEmailJob::dispatch('domain_expiring_reminder', $domain->user->email, $data, $domain->user->id);
            }
        }

        $this->info('Expiring domain scan completed successfully.');
        return Command::SUCCESS;
    }
}
