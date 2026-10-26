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

// Payment Gateway Callbacks & Webhooks
Route::match(['get', 'post'], '/payment/bkash/callback', [PaymentGatewayController::class, 'bkashCallback'])->name('payment.bkash.callback');
Route::post('/payment/stripe/webhook', [PaymentGatewayController::class, 'stripeWebhook'])->name('payment.stripe.webhook');
Route::post('/payment/binance/webhook', [PaymentGatewayController::class, 'binanceWebhook'])->name('payment.binance.webhook');

// Authentication routes
require __DIR__.'/auth.php';
