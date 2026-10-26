<?php

namespace App\Services\Domain;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TldPricing;
use App\Models\Domain;
use App\Models\Transaction;
use App\Jobs\RegisterDomainJob;
use App\Jobs\RenewDomainJob;
use App\Jobs\TransferDomainJob;
use App\Exceptions\InsufficientBalanceException;
use Illuminate\Support\Facades\DB;

class DomainOrderService
{
    public function __construct(
        protected DomainPricingService $pricingService,
        protected DomainAvailabilityService $availabilityService
    ) {}

    /**
     * Create Order & Invoice from checkout items.
     */
    public function createOrder(
        User $user,
        array $items,
        ?string $promoCode = null,
        string $source = 'web',
        ?string $ipAddress = null
    ): array {
        return DB::transaction(function () use ($user, $items, $promoCode, $source, $ipAddress) {
            $totals = $this->pricingService->calculateOrderTotals($items, $user, $promoCode);

            // 1. Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'promo_code_id' => $totals['promo_code']?->id,
                'total_amount' => $totals['total'],
                'discount_amount' => $totals['discount'],
                'currency' => 'USD',
                'status' => 'pending',
                'source' => $source,
                'ip_address' => $ipAddress,
            ]);

            // 2. Create Order Items
            foreach ($items as $item) {
                $tld = $this->availabilityService->extractTld($item['domain']);
                $tldModel = TldPricing::where('tld', $tld)->firstOrFail();
                $pricing = $this->pricingService->calculateItemPrice($tld, $item['action'] ?? 'register', $item['years'] ?? 1, $user);

                $existingDomain = null;
                if (!empty($item['domain_id'])) {
                    $existingDomain = Domain::find($item['domain_id']);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'domain_id' => $existingDomain?->id,
                    'item_type' => match ($item['action'] ?? 'register') {
                        'renew' => 'domain_renew',
                        'transfer' => 'domain_transfer',
                        default => 'domain_register',
                    },
                    'domain_name' => strtolower(trim($item['domain'])),
                    'years' => $item['years'] ?? 1,
                    'unit_price' => $pricing['unit_price_usd'],
                    'total_price' => $pricing['total_usd'],
                    'registrar_id' => $tldModel->registrar_id,
                    'registrar_cost' => $pricing['registrar_cost_usd'],
                    'registrar_payload' => $item,
                    'status' => 'pending',
                ]);
            }

            // 3. Create Invoice
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount'],
                'tax_amount' => 0.0000,
                'total_amount' => $totals['total'],
                'balance_due' => $totals['total'],
                'currency' => 'USD',
                'status' => 'unpaid',
                'due_date' => now()->addDays(7),
            ]);

            // 4. Create Invoice Items
            foreach ($order->items as $orderItem) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => strtoupper(str_replace('_', ' ', $orderItem->item_type)) . ' - ' . $orderItem->domain_name . ' (' . $orderItem->years . ' Yr)',
                    'quantity' => $orderItem->years,
                    'unit_price' => $orderItem->unit_price,
                    'total_price' => $orderItem->total_price,
                ]);
            }

            if ($totals['promo_code']) {
                $totals['promo_code']->increment('current_uses');
            }

            return [
                'order' => $order->fresh(['items']),
                'invoice' => $invoice->fresh(['items']),
            ];
        });
    }

    /**
     * Process order fulfillment immediately via account balance.
     */
    public function payWithBalance(User $user, Invoice $invoice): bool
    {
        if (!$user->hasSufficientBalance((float) $invoice->balance_due)) {
            throw new InsufficientBalanceException("Insufficient wallet balance. Please add funds.");
        }

        return DB::transaction(function () use ($user, $invoice) {
            $amountDue = (float) $invoice->balance_due;
            $user->deductBalance($amountDue);

            // Record transaction
            Transaction::create([
                'user_id' => $user->id,
                'invoice_id' => $invoice->id,
                'gateway' => 'balance',
                'gateway_transaction_id' => 'WALLET-' . strtoupper(bin2hex(random_bytes(6))),
                'type' => 'debit',
                'amount' => $amountDue,
                'currency' => 'USD',
                'exchange_rate' => 1.000000,
                'amount_in_usd' => $amountDue,
                'status' => 'completed',
            ]);

            $invoice->markAsPaid('balance');

            if ($invoice->order) {
                $this->fulfillOrder($invoice->order);
            }

            return true;
        });
    }

    /**
     * Fulfill order items asynchronously via queue jobs.
     */
    public function fulfillOrder(Order $order): void
    {
        $order->update(['status' => 'processing']);

        foreach ($order->items as $item) {
            $item->update(['status' => 'processing']);

            match ($item->item_type) {
                'domain_register' => RegisterDomainJob::dispatch($item->id),
                'domain_renew'    => RenewDomainJob::dispatch($item->id),
                'domain_transfer' => TransferDomainJob::dispatch($item->id),
                default           => null,
            };
        }
    }
}
