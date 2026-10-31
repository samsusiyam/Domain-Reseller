<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use App\Jobs\SendEmailJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailySummaryCommand extends Command
{
    protected $signature = 'reports:daily-summary';
    protected $description = 'Generate daily financial and operational summary report for administrators';

    public function handle(): int
    {
        $today = Carbon::today();

        $dailyRevenueUsd = (float) Transaction::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('amount_in_usd');

        $newRegistrationsCount = Domain::whereDate('registered_at', $today)->count();
        $newOrdersCount = Order::whereDate('created_at', $today)->count();
        $newTicketsCount = SupportTicket::whereDate('created_at', $today)->count();

        $this->info("Daily Summary for {$today->toDateString()}:");
        $this->line("- Revenue: \${$dailyRevenueUsd}");
        $this->line("- New Domains: {$newRegistrationsCount}");
        $this->line("- Orders Placed: {$newOrdersCount}");
        $this->line("- Support Tickets: {$newTicketsCount}");

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            SendEmailJob::dispatch($admin->email, 'admin_daily_summary', [
                'summary_date'          => $today->toDateString(),
                'admin_name'            => $admin->name,
                'daily_revenue'         => '$' . number_format($dailyRevenueUsd, 2),
                'new_registrations'     => $newRegistrationsCount,
                'renewals'              => 0,
                'total_active_domains'  => Domain::where('status', 'active')->count(),
                'company_name'          => config('app.name', 'HostNibo Domain Hub'),
            ], $admin->id);
        }

        $this->info('Daily summary generated and dispatched to administrators.');
        return Command::SUCCESS;
    }
}
