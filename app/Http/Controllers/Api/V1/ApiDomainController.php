<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Domain;
use App\Models\TldPricing;
use App\Services\Domain\DomainOrderService;
use App\Services\Domain\DomainPricingService;
use App\Services\Registrars\RegistrarManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiDomainController extends BaseApiController
{
    public function __construct(
        protected DomainOrderService $orderService,
        protected DomainPricingService $pricingService,
        protected RegistrarManager $registrarManager
    ) {}

    /**
     * Paginated list of reseller's domains.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Domain::where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $query->where('domain_name', 'like', '%' . $request->input('search') . '%');
        }

        $domains = $query->latest()->paginate(20);

        return $this->sendResponse($domains, 'Domains list retrieved.');
    }

    /**
     * Register domain via API (deducts wholesale balance immediately).
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'domain'        => ['required', 'string', 'max:255'],
            'years'         => ['required', 'integer', 'min:1', 'max:10'],
            'id_protection' => ['nullable', 'boolean'],
            'nameservers'   => ['nullable', 'array'],
        ]);

        $reseller = $request->user();
        $domainName = strtolower(trim($request->input('domain')));
        $years = (int) $request->input('years', 1);

        $nameservers = $request->input('nameservers');
        if (empty($nameservers)) {
            $nameservers = [
                config('dns_templates.default_nameservers.ns1', 'ns1.domainreseller.com'),
                config('dns_templates.default_nameservers.ns2', 'ns2.domainreseller.com'),
            ];
        }

        $items = [
            [
                'action'        => 'register',
                'domain'        => $domainName,
                'years'         => $years,
                'id_protection' => (bool) $request->input('id_protection', false),
                'nameservers'   => $nameservers,
            ]
        ];

        try {
            $orderData = $this->orderService->createOrder(
                $reseller,
                $items,
                null,
                'api',
                $request->ip()
            );

            // Reseller orders via API must be paid via wholesale credit wallet
            $this->orderService->processPaymentWithWallet($orderData['invoice']);

            return $this->sendResponse([
                'order_id'       => $orderData['order']->id,
                'order_number'   => $orderData['order']->order_number,
                'invoice_id'     => $orderData['invoice']->id,
                'domain'         => $domainName,
                'years'          => $years,
                'status'         => 'processing',
                'balance_remain' => (float) $reseller->fresh()->balance,
            ], 'Domain registration order placed and paid from wholesale balance.', 201);
        } catch (\App\Exceptions\InsufficientBalanceException $e) {
            return $this->sendError('Insufficient wholesale wallet balance to complete domain registration.', [], 402);
        } catch (\Throwable $e) {
            return $this->sendError('Registration failed: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Initiate domain transfer via API with EPP code.
     */
    public function transfer(Request $request): JsonResponse
    {
        $request->validate([
            'domain'        => ['required', 'string', 'max:255'],
            'epp_code'      => ['required', 'string', 'max:255'],
            'id_protection' => ['nullable', 'boolean'],
        ]);

        $reseller = $request->user();
        $domainName = strtolower(trim($request->input('domain')));

        $items = [
            [
                'action'        => 'transfer',
                'domain'        => $domainName,
                'years'         => 1,
                'epp_code'      => trim($request->input('epp_code')),
                'id_protection' => (bool) $request->input('id_protection', false),
            ]
        ];

        try {
            $orderData = $this->orderService->createOrder(
                $reseller,
                $items,
                null,
                'api',
                $request->ip()
            );

            $this->orderService->processPaymentWithWallet($orderData['invoice']);

            return $this->sendResponse([
                'order_id'     => $orderData['order']->id,
                'order_number' => $orderData['order']->order_number,
                'domain'       => $domainName,
                'status'       => 'processing_transfer',
            ], 'Domain transfer order initiated.', 201);
        } catch (\App\Exceptions\InsufficientBalanceException $e) {
            return $this->sendError('Insufficient wholesale wallet balance for domain transfer.', [], 402);
        } catch (\Throwable $e) {
            return $this->sendError('Transfer request failed: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Retrieve domain details.
     */
    public function show(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found in your account.", [], 404);
        }

        return $this->sendResponse([
            'id'                => $domainModel->id,
            'domain_name'       => $domainModel->domain_name,
            'status'            => $domainModel->status,
            'registration_date' => $domainModel->registration_date?->format('Y-m-d'),
            'expiry_date'       => $domainModel->expires_at?->format('Y-m-d'),
            'is_locked'         => $domainModel->is_locked,
            'is_private'        => $domainModel->is_private,
            'auto_renew'        => $domainModel->auto_renew,
            'nameservers'       => $domainModel->nameservers,
        ], 'Domain details retrieved.');
    }

    /**
     * Renew domain.
     */
    public function renew(Request $request, string $domain): JsonResponse
    {
        $request->validate(['years' => ['required', 'integer', 'min:1', 'max:10']]);

        $reseller = $request->user();
        $domainModel = Domain::where('user_id', $reseller->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $years = (int) $request->input('years', 1);

        $items = [
            [
                'action'    => 'renew',
                'domain'    => $domainModel->domain_name,
                'domain_id' => $domainModel->id,
                'years'     => $years,
            ]
        ];

        try {
            $orderData = $this->orderService->createOrder($reseller, $items, null, 'api', $request->ip());
            $this->orderService->processPaymentWithWallet($orderData['invoice']);

            return $this->sendResponse([
                'order_id' => $orderData['order']->id,
                'domain'   => $domainModel->domain_name,
                'years'    => $years,
                'status'   => 'processing_renewal',
            ], 'Domain renewal initiated and paid.');
        } catch (\App\Exceptions\InsufficientBalanceException $e) {
            return $this->sendError('Insufficient wholesale wallet balance for renewal.', [], 402);
        } catch (\Throwable $e) {
            return $this->sendError('Renewal failed: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Request domain cancellation.
     */
    public function cancel(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $domainModel->update(['status' => 'cancelled']);

        return $this->sendResponse([
            'domain' => $domainModel->domain_name,
            'status' => 'cancelled',
        ], 'Domain cancellation requested.');
    }

    /**
     * Fetch domain auth / EPP code.
     */
    public function authCode(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        try {
            $driver = $this->registrarManager->driver($domainModel->registrar->slug);
            $authCode = $driver->getAuthCode($domainModel->domain_name);

            return $this->sendResponse([
                'domain'    => $domainModel->domain_name,
                'auth_code' => $authCode,
            ], 'Authorization EPP code retrieved.');
        } catch (\Throwable $e) {
            return $this->sendError('Failed to fetch auth code from registrar: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Enable registrar transfer lock.
     */
    public function enableLock(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        try {
            $driver = $this->registrarManager->driver($domainModel->registrar->slug);
            $driver->lockDomain($domainModel->domain_name);
            $domainModel->update(['is_locked' => true]);

            return $this->sendResponse(['is_locked' => true], 'Domain lock enabled.');
        } catch (\Throwable $e) {
            return $this->sendError('Failed to lock domain: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Disable registrar transfer lock.
     */
    public function disableLock(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        try {
            $driver = $this->registrarManager->driver($domainModel->registrar->slug);
            $driver->unlockDomain($domainModel->domain_name);
            $domainModel->update(['is_locked' => false]);

            return $this->sendResponse(['is_locked' => false], 'Domain lock disabled.');
        } catch (\Throwable $e) {
            return $this->sendError('Failed to unlock domain: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Enable ID privacy protection.
     */
    public function enablePrivacy(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        try {
            $driver = $this->registrarManager->driver($domainModel->registrar->slug);
            $driver->toggleIdProtection($domainModel->domain_name, true);
            $domainModel->update(['is_private' => true]);

            return $this->sendResponse(['is_private' => true], 'ID privacy protection enabled.');
        } catch (\Throwable $e) {
            return $this->sendError('Failed to enable privacy: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Disable ID privacy protection.
     */
    public function disablePrivacy(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::with('registrar')
            ->where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        try {
            $driver = $this->registrarManager->driver($domainModel->registrar->slug);
            $driver->toggleIdProtection($domainModel->domain_name, false);
            $domainModel->update(['is_private' => false]);

            return $this->sendResponse(['is_private' => false], 'ID privacy protection disabled.');
        } catch (\Throwable $e) {
            return $this->sendError('Failed to disable privacy: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Enable automated renewal.
     */
    public function enableAutoRenew(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $domainModel->update(['auto_renew' => true]);
        return $this->sendResponse(['auto_renew' => true], 'Auto renewal enabled.');
    }

    /**
     * Disable automated renewal.
     */
    public function disableAutoRenew(Request $request, string $domain): JsonResponse
    {
        $domainModel = Domain::where('user_id', $request->user()->id)
            ->where('domain_name', strtolower(trim($domain)))
            ->first();

        if (!$domainModel) {
            return $this->sendError("Domain '{$domain}' not found.", [], 404);
        }

        $domainModel->update(['auto_renew' => false]);
        return $this->sendResponse(['auto_renew' => false], 'Auto renewal disabled.');
    }
}
