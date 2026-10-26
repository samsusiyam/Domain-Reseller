<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TldPricing;
use App\Services\Domain\DomainOrderService;
use App\Services\Domain\DomainPricingService;
use Illuminate\Http\Request;

class DomainTransferController extends Controller
{
    public function __construct(
        protected DomainOrderService $orderService,
        protected DomainPricingService $pricingService
    ) {}

    /**
     * Show domain transfer initiation form.
     */
    public function create(Request $request)
    {
        $domain = strtolower(trim($request->query('domain', '')));
        $tldModel = null;
        $price = null;

        if ($domain) {
            $parts = explode('.', $domain, 2);
            $tld = '.' . ($parts[1] ?? 'com');
            $tldModel = TldPricing::where('tld', $tld)->first();
            if ($tldModel) {
                $price = $this->pricingService->calculateItemPrice($tld, 'transfer', 1, $request->user());
            }
        }

        return view('client.domains.transfer', compact('domain', 'tldModel', 'price'));
    }

    /**
     * Store transfer order and create invoice.
     */
    public function store(Request $request)
    {
        $request->validate([
            'domain'        => ['required', 'string', 'max:255'],
            'epp_code'      => ['required', 'string', 'max:255'],
            'id_protection' => ['nullable', 'boolean'],
            'payment_method'=> ['required', 'string', 'in:wallet,bkash,stripe,binance,manual'],
        ]);

        $user = $request->user();
        $domain = strtolower(trim($request->input('domain')));
        $eppCode = trim($request->input('epp_code'));

        $items = [
            [
                'action'        => 'transfer',
                'domain'        => $domain,
                'years'         => 1,
                'epp_code'      => $eppCode,
                'id_protection' => (bool)$request->input('id_protection', false),
            ]
        ];

        $orderData = $this->orderService->createOrder(
            $user,
            $items,
            $request->input('promo_code'),
            'client_portal',
            $request->ip()
        );

        $invoice = $orderData['invoice'];

        if ($request->input('payment_method') === 'wallet') {
            try {
                $this->orderService->processPaymentWithWallet($invoice);
                return redirect()->route('client.invoices.show', $invoice->id)->with('success', 'Domain transfer initiated successfully!');
            } catch (\Exception $e) {
                return redirect()->route('client.invoices.show', $invoice->id)->with('error', $e->getMessage());
            }
        }

        return redirect()->route('client.invoices.show', $invoice->id);
    }
}
