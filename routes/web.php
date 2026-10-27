<?php

use App\Http\Controllers\Client\ClientAnnouncementController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Client\CurrencySwitchController;
use App\Http\Controllers\Client\DomainSearchController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\KnowledgebaseController;
use App\Http\Controllers\Client\LegalController;
use App\Http\Controllers\Client\PaymentGatewayController;
use App\Http\Controllers\Client\PricingPageController;
use App\Http\Controllers\Client\WhoisLookupController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Public & Shared)
|--------------------------------------------------------------------------
*/

// Home & Marketing Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/pricing', [PricingPageController::class, 'index'])->name('pricing');
Route::get('/reseller-program', [PricingPageController::class, 'resellerInfo'])->name('reseller.program');

// Public Domain & WHOIS Search
Route::get('/domain/search', [DomainSearchController::class, 'publicSearch'])->name('domain.search');
Route::post('/domain/check', [DomainSearchController::class, 'checkSingle'])->name('domain.check');
Route::post('/domain/check-bulk', [DomainSearchController::class, 'checkBulk'])->name('domain.check.bulk');
Route::get('/whois', [WhoisLookupController::class, 'index'])->name('whois.index');
Route::post('/whois/query', [WhoisLookupController::class, 'query'])->name('whois.query');

// Cart & Order Flows
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/items/{domain}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{domain}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/promo', [CartController::class, 'applyPromo'])->name('cart.promo.apply');
Route::delete('/cart/promo', [CartController::class, 'removePromo'])->name('cart.promo.remove');

// Checkout Flow
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/failed', [CheckoutController::class, 'failed'])->name('checkout.failed');

// Public Announcements & Knowledgebase
Route::get('/announcements', [ClientAnnouncementController::class, 'publicIndex'])->name('announcements.index');
Route::get('/announcements/{slug}', [ClientAnnouncementController::class, 'publicShow'])->name('announcements.show');
Route::get('/knowledgebase', [KnowledgebaseController::class, 'index'])->name('knowledgebase.index');
Route::get('/knowledgebase/{slug}', [KnowledgebaseController::class, 'show'])->name('knowledgebase.show');

// Public Contact & Legal Pages
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');

// Currency Switching
Route::post('/currency/switch', [CurrencySwitchController::class, 'switch'])->name('currency.switch');
Route::get('/api/rates', [CurrencySwitchController::class, 'rates'])->name('api.rates');

// Payment Gateway Callbacks & Webhooks
Route::match(['get', 'post'], '/payment/bkash/callback', [PaymentGatewayController::class, 'bkashCallback'])->name('payment.bkash.callback');
Route::post('/payment/stripe/webhook', [PaymentGatewayController::class, 'stripeWebhook'])->name('payment.stripe.webhook');
Route::post('/payment/binance/webhook', [PaymentGatewayController::class, 'binanceWebhook'])->name('payment.binance.webhook');

// Style Guide (Local/Debug only)
if (app()->environment('local') || config('app.debug')) {
    Route::get('/dev/styleguide', function () {
        return view('dev.styleguide');
    })->name('dev.styleguide');
}

// Authentication routes
require __DIR__.'/auth.php';
