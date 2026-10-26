<?php

use App\Http\Controllers\Api\V1\ApiAccountController;
use App\Http\Controllers\Api\V1\ApiAuthController;
use App\Http\Controllers\Api\V1\ApiBillingController;
use App\Http\Controllers\Api\V1\ApiChildNameserverController;
use App\Http\Controllers\Api\V1\ApiDnsController;
use App\Http\Controllers\Api\V1\ApiDomainCheckController;
use App\Http\Controllers\Api\V1\ApiDomainController;
use App\Http\Controllers\Api\V1\ApiEmailForwardController;
use App\Http\Controllers\Api\V1\ApiNameserverController;
use App\Http\Controllers\Api\V1\ApiTldPricingController;
use App\Http\Controllers\Api\V1\ApiUrlForwardController;
use App\Http\Controllers\Api\V1\ApiWebhookController;
use App\Http\Controllers\Api\V1\ApiWhoisController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| External Reseller REST API V1
| Middleware: ['api', 'api.auth', 'api.ratelimit', 'api.ip_whitelist']
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['api.auth', 'api.ratelimit', 'api.ip_whitelist'])->group(function () {
    // Auth & Account
    Route::get('/auth/test', [ApiAuthController::class, 'test']);
    Route::get('/account/info', [ApiAccountController::class, 'info']);
    Route::get('/account/balance', [ApiAccountController::class, 'balance']);

    // TLD Pricing & Availability Search
    Route::get('/tlds', [ApiTldPricingController::class, 'index']);
    Route::get('/tlds/{tld}/pricing', [ApiTldPricingController::class, 'show']);
    Route::get('/domains/check', [ApiDomainCheckController::class, 'check']);
    Route::post('/domains/check-bulk', [ApiDomainCheckController::class, 'checkBulk']);
    Route::get('/domains/suggestions', [ApiDomainCheckController::class, 'suggestions']);

    // Domain Lifecycle (Register, Transfer, Renew, Lock, Privacy)
    Route::get('/domains', [ApiDomainController::class, 'index']);
    Route::post('/domains/register', [ApiDomainController::class, 'register']);
    Route::post('/domains/transfer', [ApiDomainController::class, 'transfer']);
    Route::get('/domains/{domain}', [ApiDomainController::class, 'show']);
    Route::post('/domains/{domain}/renew', [ApiDomainController::class, 'renew']);
    Route::post('/domains/{domain}/cancel', [ApiDomainController::class, 'cancel']);
    Route::get('/domains/{domain}/auth-code', [ApiDomainController::class, 'authCode']);
    Route::post('/domains/{domain}/lock', [ApiDomainController::class, 'enableLock']);
    Route::delete('/domains/{domain}/lock', [ApiDomainController::class, 'disableLock']);
    Route::post('/domains/{domain}/privacy', [ApiDomainController::class, 'enablePrivacy']);
    Route::delete('/domains/{domain}/privacy', [ApiDomainController::class, 'disablePrivacy']);
    Route::post('/domains/{domain}/auto-renew', [ApiDomainController::class, 'enableAutoRenew']);
    Route::delete('/domains/{domain}/auto-renew', [ApiDomainController::class, 'disableAutoRenew']);

    // Authoritative Nameservers
    Route::get('/domains/{domain}/nameservers', [ApiNameserverController::class, 'index']);
    Route::put('/domains/{domain}/nameservers', [ApiNameserverController::class, 'update']);

    // Child Nameservers (Glue Records)
    Route::get('/domains/{domain}/child-nameservers', [ApiChildNameserverController::class, 'index']);
    Route::post('/domains/{domain}/child-nameservers', [ApiChildNameserverController::class, 'store']);
    Route::put('/domains/{domain}/child-nameservers/{hostname}', [ApiChildNameserverController::class, 'update']);
    Route::delete('/domains/{domain}/child-nameservers/{hostname}', [ApiChildNameserverController::class, 'destroy']);

    // Hosted DNS Zone
    Route::get('/domains/{domain}/dns', [ApiDnsController::class, 'index']);
    Route::post('/domains/{domain}/dns', [ApiDnsController::class, 'store']);
    Route::put('/domains/{domain}/dns/{recordId}', [ApiDnsController::class, 'update']);
    Route::delete('/domains/{domain}/dns/{recordId}', [ApiDnsController::class, 'destroy']);

    // WHOIS & Registry Contacts
    Route::get('/domains/{domain}/contacts', [ApiWhoisController::class, 'show']);
    Route::put('/domains/{domain}/contacts', [ApiWhoisController::class, 'update']);
    Route::get('/whois/{domain}', [ApiWhoisController::class, 'lookup']);

    // Email & URL Forwarding
    Route::get('/domains/{domain}/email-forwards', [ApiEmailForwardController::class, 'index']);
    Route::post('/domains/{domain}/email-forwards', [ApiEmailForwardController::class, 'store']);
    Route::delete('/domains/{domain}/email-forwards/{forwardId}', [ApiEmailForwardController::class, 'destroy']);
    Route::get('/domains/{domain}/url-forwards', [ApiUrlForwardController::class, 'index']);
    Route::post('/domains/{domain}/url-forwards', [ApiUrlForwardController::class, 'store']);
    Route::delete('/domains/{domain}/url-forwards/{forwardId}', [ApiUrlForwardController::class, 'destroy']);

    // Billing & Orders
    Route::get('/invoices', [ApiBillingController::class, 'invoices']);
    Route::get('/invoices/{id}', [ApiBillingController::class, 'invoice']);
    Route::get('/transactions', [ApiBillingController::class, 'transactions']);
    Route::get('/orders', [ApiBillingController::class, 'orders']);
    Route::get('/orders/{id}', [ApiBillingController::class, 'order']);

    // Webhooks
    Route::get('/webhooks', [ApiWebhookController::class, 'index']);
    Route::post('/webhooks', [ApiWebhookController::class, 'store']);
    Route::delete('/webhooks/{id}', [ApiWebhookController::class, 'destroy']);
    Route::post('/webhooks/{id}/test', [ApiWebhookController::class, 'test']);
});
