<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Jobs\RegisterDomainJob;
use App\Jobs\RenewDomainJob;
use App\Jobs\TransferDomainJob;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;

class OrderManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Lists all domain orders with status filter.
     */
    public function index(Request $request)
    {
        $query = Order::with('user', 'items');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', $search)
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', $search)->orWhere('email', 'like', $search);
                  })
                  ->orWhereHas('items', function ($iq) use ($search) {
                      $iq->where('domain_name', 'like', $search);
                  });
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show order details and registrar payload logs.
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.domain', 'invoice'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Retry automated provisioning of pending/failed items in order.
     */
    public function retryProvision($id)
    {
        $order = Order::with('items')->findOrFail($id);

        foreach ($order->items as $item) {
            if (in_array($item->status, ['pending', 'failed'])) {
                $item->update(['status' => 'pending']);

                match ($item->item_type) {
                    'domain_register' => RegisterDomainJob::dispatch($item->id),
                    'domain_renew'    => RenewDomainJob::dispatch($item->id),
                    'domain_transfer' => TransferDomainJob::dispatch($item->id),
                    default           => null,
                };
            }
        }

        $order->update(['status' => 'processing']);
        $this->activityLogger->log('order_provision_retried', "Admin retried provisioning for Order #{$order->order_number}", $order);

        return back()->with('success', "Provisioning queued for Order #{$order->order_number}.");
    }

    /**
     * Cancel order and issue refund to customer wallet.
     */
    public function cancel($id)
    {
        $order = Order::with(['user', 'invoice'])->findOrFail($id);

        if ($order->status === 'cancelled') {
            return back()->with('info', 'Order is already cancelled.');
        }

        $user = $order->user;
        $refundAmount = (float) $order->total_amount;

        // Refund to wallet
        $user->increment('wallet_balance', $refundAmount);

        Transaction::create([
            'user_id'        => $user->id,
            'type'           => 'credit',
            'amount'         => $refundAmount,
            'currency'       => 'USD',
            'exchange_rate'  => 1.000000,
            'payment_method' => 'wallet_refund',
            'reference_id'   => 'REF-' . strtoupper(uniqid()),
            'status'         => 'completed',
            'description'    => "Refund for cancelled Order #{$order->order_number}",
        ]);

        $order->update(['status' => 'cancelled']);
        $order->items()->update(['status' => 'cancelled']);

        if ($order->invoice && $order->invoice->status === 'paid') {
            $order->invoice->update(['status' => 'refunded']);
        }

        $this->activityLogger->log('order_cancelled', "Admin cancelled Order #{$order->order_number} and refunded \${$refundAmount}", $order);

        return back()->with('success', "Order #{$order->order_number} cancelled and \${$refundAmount} refunded to user wallet.");
    }
}
