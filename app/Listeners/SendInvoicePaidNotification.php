<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use App\Jobs\SendEmailJob;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendInvoicePaidNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected WebhookDispatcher $webhookDispatcher
    ) {}

    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;
        $user = $invoice->user;

        if (!$user) {
            return;
        }

        // 1. Dispatch Customer Receipt Email
        try {
            SendEmailJob::dispatch($user->email, 'invoice_paid', [
                'user_name'      => $user->name,
                'invoice_number' => $invoice->invoice_number,
                'amount_paid'    => '$' . number_format($invoice->total_amount, 2),
                'paid_at'        => $invoice->paid_at ? $invoice->paid_at->format('M d, Y H:i') : now()->format('M d, Y H:i'),
                'payment_method' => strtoupper($invoice->payment_method ?? 'ONLINE'),
                'invoice_url'    => route('client.invoices.show', $invoice->id),
                'company_name'   => config('app.name', 'HostNibo Domain Hub'),
            ], $user->id);
        } catch (\Throwable $e) {
            Log::error("Failed to queue invoice_paid email for invoice #{$invoice->invoice_number}: " . $e->getMessage());
        }

        // 2. Dispatch Reseller Webhook
        try {
            $this->webhookDispatcher->dispatch('invoice.paid', [
                'event'          => 'invoice.paid',
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total_amount'   => (float) $invoice->total_amount,
                'currency'       => $invoice->currency ?? 'USD',
                'paid_at'        => $invoice->paid_at?->toIso8601String() ?? now()->toIso8601String(),
                'timestamp'      => now()->toIso8601String(),
            ], $user);
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch reseller webhook invoice.paid for #{$invoice->invoice_number}: " . $e->getMessage());
        }
    }
}
