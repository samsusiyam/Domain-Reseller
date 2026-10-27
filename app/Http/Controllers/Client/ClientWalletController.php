<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Services\Payments\PaymentManager;
use App\DTOs\PaymentRequestDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientWalletController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager
    ) {}

    public function index()
    {
        $user = Auth::user();
        $transactions = $user->transactions()->latest()->paginate(15);

        return view('client.wallet.index', compact('user', 'transactions'));
    }

    public function addFunds(Request $request)
    {
        $minDeposit = Auth::user()->isReseller()
            ? (float) config('reseller.tiers.1.min_deposit', 10.0)
            : 5.0;

        $request->validate([
            'amount' => ['required', 'numeric', "min:{$minDeposit}"],
            'gateway' => ['required', 'string', 'in:manual,bkash,bkash_personal,sslcommerz,piprapay,uddoktapay,binance,binance_personal,stripe'],
        ]);

        $amount = (float) $request->amount;
        $user = Auth::user();

        // Generate an add-funds invoice
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'subtotal' => $amount,
            'tax_amount' => 0.0,
            'discount_amount' => 0.0,
            'total_amount' => $amount,
            'balance_due' => $amount,
            'currency' => 'USD',
            'status' => 'unpaid',
            'due_date' => now()->addDays(2),
            'notes' => 'Wallet balance credit deposit',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Account Balance Deposit',
            'quantity' => 1,
            'unit_price' => $amount,
            'total_price' => $amount,
        ]);

        // Direct user to pay
        return redirect()->route('client.invoices.show', $invoice->id);
    }

    public function transactions()
    {
        $transactions = Auth::user()->transactions()->latest()->paginate(20);
        return view('client.wallet.transactions', compact('transactions'));
    }
}
