<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Cart\CartService;
use App\Services\Domain\DomainOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected DomainOrderService $orderService
    ) {}

    public function index(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login', ['next' => route('checkout.index')])
                ->with('error', 'Please sign in or create an account to complete your domain order.');
        }

        $user = auth()->user();
        $this->cartService->mergeOnLogin();

        $validation = $this->cartService->validateCart();
        $items = $this->cartService->getItems();
        $totals = $this->cartService->getTotals();

        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'Your shopping cart is empty.');
        }

        if (!$validation['is_valid']) {
            return redirect()->route('cart.index')->with('error', 'Some items in your cart are no longer available. Please review your cart.');
        }

        $contacts = Contact::where('user_id', $user->id)->get();
        $bdtCurrency = Currency::where('code', 'BDT')->first();
        $bdtRate = (float) ($bdtCurrency?->exchange_rate ?? 120.00);

        $defaultNs = config('registrars.default_nameservers', [
            'ns1.hostnibo.com',
            'ns2.hostnibo.com',
        ]);

        $idempotencyKey = (string) Str::uuid();
        session(['checkout_idempotency_key' => $idempotencyKey]);

        return view('checkout.index', [
            'user' => $user,
            'items' => $items,
            'totals' => $totals,
            'contacts' => $contacts,
            'bdtRate' => $bdtRate,
            'defaultNs' => $defaultNs,
            'idempotencyKey' => $idempotencyKey,
            'warnings' => $validation['warnings'],
        ]);
    }

    public function process(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login', ['next' => route('checkout.index')]);
        }

        $user = auth()->user();

        $request->validate([
            'idempotency_key' => ['required', 'string'],
            'payment_method' => ['required', 'in:balance,bkash,bkash_personal,uddoktapay,sslcommerz,piprapay,binance_personal,binance_merchant,binance,stripe,manual,bank_transfer'],
            'contact_type' => ['required', 'in:existing,new'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'first_name' => ['required_if:contact_type,new', 'nullable', 'string', 'max:100'],
            'last_name' => ['required_if:contact_type,new', 'nullable', 'string', 'max:100'],
            'email' => ['required_if:contact_type,new', 'nullable', 'email', 'max:150'],
            'phone' => ['required_if:contact_type,new', 'nullable', 'string', 'max:30'],
            'address_1' => ['required_if:contact_type,new', 'nullable', 'string', 'max:255'],
            'city' => ['required_if:contact_type,new', 'nullable', 'string', 'max:100'],
            'country' => ['required_if:contact_type,new', 'nullable', 'string', 'size:2'],
            'postal_code' => ['required_if:contact_type,new', 'nullable', 'string', 'max:20'],
            'ns1' => ['nullable', 'string', 'max:255'],
            'ns2' => ['nullable', 'string', 'max:255'],
            'bank_trx_id' => ['required_if:payment_method,bank_transfer', 'nullable', 'string', 'max:100'],
            'bank_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $idempotencyKey = $request->input('idempotency_key');
        $cacheKey = 'idempotent_order:' . $idempotencyKey;

        if (Cache::has($cacheKey)) {
            $existingUuid = Cache::get($cacheKey);
            return redirect()->route('checkout.success', ['order_id' => $existingUuid]);
        }

        $items = $this->cartService->getItems();
        $totals = $this->cartService->getTotals();

        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'Cart expired or empty.');
        }

        // Contact Setup
        $contact = null;
        if ($request->input('contact_type') === 'existing' && $request->filled('contact_id')) {
            $contact = Contact::where('user_id', $user->id)->where('id', $request->input('contact_id'))->first();
        }

        if (!$contact) {
            $phoneInput = $request->input('phone', $user->phone ?? '+8801700000000');
            $phoneCountry = '880';
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneInput);
            if (str_starts_with($phoneNumber, '880')) {
                $phoneNumber = substr($phoneNumber, 3);
            }

            $contact = Contact::create([
                'user_id' => $user->id,
                'contact_type' => !empty($request->input('organization')) ? 'company' : 'individual',
                'first_name' => $request->input('first_name', explode(' ', $user->name)[0] ?? 'User'),
                'last_name' => $request->input('last_name', explode(' ', $user->name, 2)[1] ?? 'Client'),
                'company_name' => $request->input('organization'),
                'email' => $request->input('email', $user->email),
                'phone_country_code' => $phoneCountry,
                'phone_number' => $phoneNumber ?: '1700000000',
                'address_line_1' => $request->input('address_1', $user->address_line_1 ?? 'Main Street'),
                'city' => $request->input('city', $user->city ?? 'Dhaka'),
                'state' => $request->input('state', 'Dhaka'),
                'country' => $request->input('country', $user->country ?? 'BD'),
                'postal_code' => $request->input('postal_code', $user->postal_code ?? '1200'),
                'is_default' => true,
            ]);
        }

        $paymentMethod = $request->input('payment_method');

        // Verify balance if paying via wallet
        if ($paymentMethod === 'balance') {
            if (!$user->hasSufficientBalance((float) $totals['total'])) {
                return back()->withInput()->with('error', 'Insufficient account balance ($' . number_format($user->balance, 2) . ' available). Please choose another payment method.');
            }
        }

        // Prepare order items payload
        $orderItemsPayload = [];
        $nsList = array_filter([
            $request->input('ns1', 'ns1.hostnibo.com'),
            $request->input('ns2', 'ns2.hostnibo.com'),
        ]);

        foreach ($items as $item) {
            $orderItemsPayload[] = [
                'domain' => $item['domain'],
                'tld' => $item['tld'] ?? (str_contains($item['domain'], '.') ? '.' . ltrim(substr(strrchr($item['domain'], '.'), 1), '.') : '.com'),
                'action' => $item['action'],
                'years' => $item['years'],
                'epp_code' => $item['epp_code'] ?? null,
                'nameservers' => $nsList,
                'contact_id' => $contact->id,
            ];
        }

        $promo = $this->cartService->getPromo();
        $promoCodeStr = $promo['code'] ?? null;

        try {
            $result = $this->orderService->createOrder(
                user: $user,
                items: $orderItemsPayload,
                promoCode: $promoCodeStr,
                source: 'web',
                ipAddress: $request->ip()
            );

            $order = $result['order'];
            $invoice = $result['invoice'];

            if ($paymentMethod === 'balance') {
                $this->orderService->payWithBalance($user, $invoice);
                $this->cartService->clear();
                Cache::put($cacheKey, $order->uuid, now()->addHours(1));

                return redirect()->route('checkout.success', ['order_id' => $order->uuid]);
            }

            // Handle bank transfer / manual
            if ($paymentMethod === 'bank_transfer' || $paymentMethod === 'manual') {
                Transaction::create([
                    'user_id' => $user->id,
                    'invoice_id' => $invoice->id,
                    'gateway' => 'manual',
                    'gateway_transaction_id' => $request->input('bank_trx_id', 'MANUAL-' . strtoupper(Str::random(6))),
                    'type' => 'credit',
                    'amount' => $totals['total'],
                    'currency' => 'USD',
                    'exchange_rate' => 1.0,
                    'amount_in_usd' => $totals['total'],
                    'status' => 'pending',
                    'payment_details' => [
                        'method' => 'bank_transfer',
                        'bank_trx_id' => $request->input('bank_trx_id'),
                        'notes' => $request->input('bank_notes'),
                    ],
                ]);

                $this->cartService->clear();
                Cache::put($cacheKey, $order->uuid, now()->addHours(1));

                return redirect()->route('checkout.success', ['order_id' => $order->uuid, 'pending' => 1]);
            }

            // Direct route to full payment gateway flow
            $this->cartService->clear();
            Cache::put($cacheKey, $order->uuid, now()->addHours(1));

            return redirect()->route('payment.checkout', [
                'invoice' => $invoice->id,
                'gateway' => $paymentMethod,
            ]);

        } catch (\Throwable $e) {
            return redirect()->route('checkout.failed', [
                'reason' => $e->getMessage(),
            ]);
        }

        return redirect()->route('checkout.index');
    }

    public function success(Request $request)
    {
        $orderUuid = $request->query('order_id');
        $order = Order::where('uuid', $orderUuid)->with(['items', 'invoice'])->firstOrFail();

        return view('checkout.success', [
            'order' => $order,
            'isPending' => $request->boolean('pending'),
            'gateway' => $request->query('gateway'),
        ]);
    }

    public function failed(Request $request)
    {
        $reason = $request->query('reason', 'Payment could not be completed. Please review your payment method.');
        return view('checkout.failed', [
            'reason' => $reason,
        ]);
    }
}
