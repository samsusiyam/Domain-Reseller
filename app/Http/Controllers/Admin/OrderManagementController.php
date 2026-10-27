<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\User;
use App\Models\RegistrarModule;
use App\Models\TldPricing;
use App\Jobs\RegisterDomainJob;
use App\Jobs\RenewDomainJob;
use App\Jobs\TransferDomainJob;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
     * Show form to manually create a new order (WHMCS style).
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email', 'company_name')->orderBy('name')->get();
        $registrars = RegistrarModule::where('is_active', true)->get();
        $tlds = TldPricing::where('is_active', true)->orderBy('tld')->get();

        return view('admin.orders.create', compact('users', 'registrars', 'tlds'));
    }

    /**
     * Store new order and optionally provision immediately.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => ['required', 'exists:users,id'],
            'domain_name'   => ['required', 'string', 'max:255'],
            'item_type'     => ['required', 'in:domain_register,domain_renew,domain_transfer'],
            'years'         => ['required', 'integer', 'min:1', 'max:10'],
            'total_amount'  => ['required', 'numeric', 'min:0'],
            'registrar_id'  => ['required', 'exists:registrar_modules,id'],
            'status'        => ['required', 'in:pending,processing,completed'],
            'create_invoice'=> ['nullable', 'boolean'],
            'mark_paid'     => ['nullable', 'boolean'],
            'provision_now' => ['nullable', 'boolean'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $totalAmount = (float) $validated['total_amount'];

        $order = Order::create([
            'uuid'            => (string) Str::uuid(),
            'order_number'    => 'ORD-' . strtoupper(Str::random(8)),
            'user_id'         => $user->id,
            'total_amount'    => $totalAmount,
            'discount_amount' => 0.0000,
            'currency'        => 'USD',
            'status'          => $validated['status'],
            'source'          => 'web',
            'notes'           => 'Manual order created by Administrator',
        ]);

        $item = OrderItem::create([
            'order_id'       => $order->id,
            'item_type'      => $validated['item_type'],
            'domain_name'    => strtolower(trim($validated['domain_name'])),
            'years'          => (int) $validated['years'],
            'unit_price'     => $totalAmount / (int) $validated['years'],
            'total_price'    => $totalAmount,
            'registrar_id'   => $validated['registrar_id'],
            'registrar_cost' => 0.0000,
            'status'         => $validated['status'] === 'completed' ? 'completed' : 'pending',
        ]);

        // Create Invoice if requested
        if ($request->boolean('create_invoice', true)) {
            $isPaid = $request->boolean('mark_paid', true);
            $invoice = Invoice::create([
                'user_id'         => $user->id,
                'subtotal'        => $totalAmount,
                'discount_amount' => 0.0000,
                'tax_amount'      => 0.0000,
                'total_amount'    => $totalAmount,
                'balance_due'     => $isPaid ? 0.0000 : $totalAmount,
                'currency'        => 'USD',
                'status'          => $isPaid ? 'paid' : 'unpaid',
                'paid_at'         => $isPaid ? now() : null,
                'payment_method'  => $isPaid ? 'admin_manual' : null,
                'due_date'        => now()->addDays(7),
                'notes'           => "Invoice for Order #{$order->order_number}",
            ]);

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => ucfirst(str_replace('_', ' ', $validated['item_type'])) . ": {$item->domain_name} ({$item->years} Year(s))",
                'quantity'    => 1,
                'unit_price'  => $totalAmount,
                'total_price' => $totalAmount,
            ]);
        }

        $this->activityLogger->log('order_created', "Admin created Order #{$order->order_number} for client #{$user->id}", $order);

        // Provision immediately if requested
        if ($request->boolean('provision_now')) {
            match ($item->item_type) {
                'domain_register' => RegisterDomainJob::dispatch($item->id),
                'domain_renew'    => RenewDomainJob::dispatch($item->id),
                'domain_transfer' => TransferDomainJob::dispatch($item->id),
                default           => null,
            };
        }

        return redirect()->route('admin.orders.show', $order->id)->with('success', "Order #{$order->order_number} created successfully.");
    }

    /**
     * Show order details and registrar payload logs.
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.domain', 'invoice'])->findOrFail($id);
        $registrars = RegistrarModule::where('is_active', true)->get();
        return view('admin.orders.show', compact('order', 'registrars'));
    }

    /**
     * WHMCS-Style Accept Order: Accept order, mark invoice paid, trigger registrar provisioning.
     */
    public function accept($id)
    {
        $order = Order::with(['items', 'invoice'])->findOrFail($id);

        if ($order->status === 'completed') {
            return back()->with('info', "Order #{$order->order_number} is already completed.");
        }

        $order->update(['status' => 'processing']);

        if ($order->invoice && $order->invoice->status !== 'paid') {
            $order->invoice->update([
                'status'         => 'paid',
                'balance_due'    => 0.0000,
                'paid_at'        => now(),
                'payment_method' => 'admin_approved',
            ]);
        }

        // Trigger provisioning for pending or failed items
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

        $this->activityLogger->log('order_accepted', "Admin accepted Order #{$order->order_number} and triggered provisioning", $order);

        return back()->with('success', "Order #{$order->order_number} accepted! Upstream registrar provisioning has been dispatched.");
    }

    /**
     * Update order status manually.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,completed,failed,cancelled'],
        ]);

        $order->update(['status' => $validated['status']]);
        $this->activityLogger->log('order_status_updated', "Admin changed Order #{$order->order_number} status to {$validated['status']}", $order);

        return back()->with('success', "Order status updated to {$validated['status']}.");
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
        $user->addBalance($refundAmount);

        Transaction::create([
            'user_id'                => $user->id,
            'invoice_id'             => $order->invoice?->id,
            'type'                   => 'credit',
            'gateway'                => 'balance',
            'gateway_transaction_id' => 'REF-' . strtoupper(uniqid()),
            'amount'                 => $refundAmount,
            'currency'               => 'USD',
            'exchange_rate'          => 1.000000,
            'amount_in_usd'          => $refundAmount,
            'status'                 => 'completed',
            'payment_details'        => ['reason' => "Refund for cancelled Order #{$order->order_number}"],
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
