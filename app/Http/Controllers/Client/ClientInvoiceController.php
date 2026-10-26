<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Payments\PaymentManager;
use App\Services\Domain\DomainOrderService;
use App\DTOs\PaymentRequestDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientInvoiceController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected DomainOrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $query = Auth::user()->invoices()->with('items');

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return view('client.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);
        $invoice->load(['items', 'transactions', 'user']);

        return view('client.invoices.show', compact('invoice'));
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        if ($invoice->isPaid()) {
            return redirect()->route('client.invoices.show', $invoice->id)->with('info', 'This invoice is already paid.');
        }

        $gateway = $request->input('gateway', 'manual');

        // 1. Pay with wallet balance
        if ($gateway === 'balance') {
            try {
                $this->orderService->payWithBalance(Auth::user(), $invoice);
                return redirect()->route('client.invoices.show', $invoice->id)->with('success', 'Invoice paid successfully with your account balance!');
            } catch (\Throwable $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        // 2. Pay via gateway
        $driver = $this->paymentManager->driver($gateway);
        $paymentReq = new PaymentRequestDTO(
            invoiceId: (string) $invoice->id,
            amount: (float) $invoice->balance_due,
            currency: 'USD',
            returnUrl: route('payment.callback', ['gateway' => $gateway]),
            cancelUrl: route('client.invoices.show', $invoice->id),
            customerEmail: Auth::user()->email,
            customerName: Auth::user()->name,
            customerPhone: Auth::user()->phone
        );

        $redirect = $driver->initializePayment($paymentReq);

        if ($redirect->isRedirect && !empty($redirect->redirectUrl)) {
            return redirect()->away($redirect->redirectUrl);
        }

        return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'Unable to initialize payment gateway.');
    }

    protected function authorizeInvoice(Invoice $invoice): void
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
