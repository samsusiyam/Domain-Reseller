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

        $dailyRevenueUsd = (float) Transaction::where('type', 'payment')
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('amount');

        $newRegistrationsCount = Domain::whereDate('registration_date', $today)->count();
        $newOrdersCount = Order::whereDate('created_at', $today)->count();
        $newTicketsCount = SupportTicket::whereDate('created_at', $today)->count();

        $this->info("Daily Summary for {$today->toDateString()}:");
        $this->line("- Revenue: \${$dailyRevenueUsd}");
        $this->line("- New Domains: {$newRegistrationsCount}");
        $this->line("- Orders Placed: {$newOrdersCount}");
        $this->line("- Support Tickets: {$newTicketsCount}");

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            SendEmailJob::dispatch('admin_daily_summary', $admin->email, [
                'admin_name'        => $admin->name,
                'date'              => $today->toDateString(),
                'revenue_usd'       => '$' . number_format($dailyRevenueUsd, 2),
                'new_domains_count' => $newRegistrationsCount,
                'new_orders_count'  => $newOrdersCount,
                'new_tickets_count' => $newTicketsCount,
            ], $admin->id);
        }

        $this->info('Daily summary generated and dispatched to administrators.');
        return Command::SUCCESS;
    }
}
