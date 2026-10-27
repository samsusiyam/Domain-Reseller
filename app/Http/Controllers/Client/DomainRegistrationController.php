<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TldPricing;
use App\Services\Domain\DomainOrderService;
use App\Services\Domain\DomainPricingService;
use Illuminate\Http\Request;

class DomainRegistrationController extends Controller
{
    public function __construct(
        protected DomainOrderService $orderService,
        protected DomainPricingService $pricingService
    ) {}

    /**
     * Show domain registration configuration form.
     */
    public function create(Request $request)
    {
        $domain = strtolower(trim($request->query('domain', '')));
        if (empty($domain)) {
            return redirect()->route('client.domains.search');
        }

        $parts = explode('.', $domain, 2);
        $tld = '.' . ($parts[1] ?? 'com');
        $tldModel = TldPricing::where('tld', $tld)->first();

        $user = $request->user();
        $prices = [];
        for ($y = 1; $y <= 10; $y++) {
            $prices[$y] = $this->pricingService->calculateItemPrice($tld, 'register', $y, $user);
        }

        return view('client.domains.register', compact('domain', 'tld', 'tldModel', 'prices'));
    }

    /**
     * Store registration order and create invoice.
     */
    public function store(Request $request)
    {
        $request->validate([
            'domain'        => ['required', 'string', 'max:255'],
            'years'         => ['required', 'integer', 'min:1', 'max:10'],
            'id_protection' => ['nullable', 'boolean'],
            'dns_management'=> ['nullable', 'boolean'],
            'email_forwarding'=> ['nullable', 'boolean'],
            'ns1'           => ['nullable', 'string', 'max:255'],
            'ns2'           => ['nullable', 'string', 'max:255'],
            'ns3'           => ['nullable', 'string', 'max:255'],
            'ns4'           => ['nullable', 'string', 'max:255'],
            'payment_method'=> ['required', 'string', 'in:wallet,bkash,stripe,binance,manual'],
        ]);

        $user = $request->user();
        $domain = strtolower(trim($request->input('domain')));
        $years = (int)$request->input('years', 1);

        $nameservers = array_values(array_filter([
            $request->input('ns1'),
            $request->input('ns2'),
            $request->input('ns3'),
            $request->input('ns4'),
        ]));

        if (empty($nameservers)) {
            $nameservers = [
                config('dns_templates.default_nameservers.ns1', 'ns1.domainreseller.com'),
                config('dns_templates.default_nameservers.ns2', 'ns2.domainreseller.com'),
            ];
        }

        $items = [
            [
                'action'           => 'register',
                'domain'           => $domain,
                'years'            => $years,
                'id_protection'    => (bool)$request->input('id_protection', false),
                'dns_management'   => (bool)$request->input('dns_management', true),
                'email_forwarding' => (bool)$request->input('email_forwarding', false),
                'nameservers'      => $nameservers,
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
                $this->orderService->payWithBalance($user, $invoice);
                return redirect()->route('client.invoices.show', $invoice->id)->with('success', 'Domain registered successfully!');
            } catch (\Exception $e) {
                return redirect()->route('client.invoices.show', $invoice->id)->with('error', $e->getMessage());
            }
        }

        return redirect()->route('client.invoices.show', $invoice->id);
    }
}
