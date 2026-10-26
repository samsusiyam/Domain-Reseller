<?php

use App\Http\Controllers\Client\ChildNameserverController;
use App\Http\Controllers\Client\ClientAnnouncementController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientDomainController;
use App\Http\Controllers\Client\ClientInvoiceController;
use App\Http\Controllers\Client\ClientProfileController;
use App\Http\Controllers\Client\ClientTicketController;
use App\Http\Controllers\Client\ClientWalletController;
use App\Http\Controllers\Client\DnsRecordController;
use App\Http\Controllers\Client\DomainRegistrationController;
use App\Http\Controllers\Client\DomainSearchController;
use App\Http\Controllers\Client\DomainTransferController;
use App\Http\Controllers\Client\EmailForwardingController;
use App\Http\Controllers\Client\NameserverController;
use App\Http\Controllers\Client\SessionController;
use App\Http\Controllers\Client\TwoFactorAuthController;
use App\Http\Controllers\Client\UrlForwardingController;
use App\Http\Controllers\Client\WhoisContactController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client Portal Routes
| Prefix: /client, Name prefix: client.
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');

// Profile & Security
Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ClientProfileController::class, 'edit'])->name('edit');
    Route::put('/', [ClientProfileController::class, 'update'])->name('update');
    Route::put('/password', [ClientProfileController::class, 'updatePassword'])->name('password');
});

Route::prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('/', [TwoFactorAuthController::class, 'index'])->name('index');
    Route::post('/enable', [TwoFactorAuthController::class, 'enable'])->name('enable');
    Route::post('/confirm', [TwoFactorAuthController::class, 'confirm'])->name('confirm');
    Route::delete('/disable', [TwoFactorAuthController::class, 'disable'])->name('disable');
});

Route::prefix('sessions')->name('sessions.')->group(function () {
    Route::get('/', [SessionController::class, 'index'])->name('index');
    Route::delete('/{id}', [SessionController::class, 'destroy'])->name('destroy');
});

// Domain Registration & Transfer Cart
Route::prefix('domains')->name('domains.')->group(function () {
    Route::get('/search', [DomainSearchController::class, 'index'])->name('search');
    Route::post('/check', [DomainSearchController::class, 'check'])->name('check');
    Route::get('/register', [DomainRegistrationController::class, 'create'])->name('register');
    Route::post('/register', [DomainRegistrationController::class, 'store'])->name('register.store');
    Route::get('/transfer', [DomainTransferController::class, 'create'])->name('transfer');
    Route::post('/transfer', [DomainTransferController::class, 'store'])->name('transfer.store');

    // Domain Hub & Management
    Route::get('/', [ClientDomainController::class, 'index'])->name('index');
    Route::get('/{domain}', [ClientDomainController::class, 'show'])->name('show');
    Route::post('/{domain}/renew', [ClientDomainController::class, 'renew'])->name('renew');
    Route::post('/{domain}/toggle-autorenew', [ClientDomainController::class, 'toggleAutoRenew'])->name('toggle-autorenew');
    Route::post('/{domain}/toggle-lock', [ClientDomainController::class, 'toggleLock'])->name('toggle-lock');
    Route::post('/{domain}/toggle-privacy', [ClientDomainController::class, 'togglePrivacy'])->name('toggle-privacy');
    Route::post('/{domain}/epp', [ClientDomainController::class, 'fetchEppCode'])->name('epp');

    // Authoritative Nameservers
    Route::get('/{domain}/nameservers', [NameserverController::class, 'edit'])->name('nameservers.edit');
    Route::put('/{domain}/nameservers', [NameserverController::class, 'update'])->name('nameservers.update');

    // Hosted DNS Zone
    Route::get('/{domain}/dns-records', [DnsRecordController::class, 'index'])->name('dns.index');
    Route::post('/{domain}/dns-records', [DnsRecordController::class, 'store'])->name('dns.store');
    Route::put('/{domain}/dns-records/{recordId}', [DnsRecordController::class, 'update'])->name('dns.update');
    Route::delete('/{domain}/dns-records/{recordId}', [DnsRecordController::class, 'destroy'])->name('dns.destroy');

    // Child Nameservers (Glue Records)
    Route::get('/{domain}/child-nameservers', [ChildNameserverController::class, 'index'])->name('child-ns.index');
    Route::post('/{domain}/child-nameservers', [ChildNameserverController::class, 'store'])->name('child-ns.store');
    Route::put('/{domain}/child-nameservers/{nsId}', [ChildNameserverController::class, 'update'])->name('child-ns.update');
    Route::delete('/{domain}/child-nameservers/{nsId}', [ChildNameserverController::class, 'destroy'])->name('child-ns.destroy');

    // WHOIS Contact Info
    Route::get('/{domain}/whois', [WhoisContactController::class, 'edit'])->name('whois.edit');
    Route::put('/{domain}/whois', [WhoisContactController::class, 'update'])->name('whois.update');

    // Email Forwarding
    Route::get('/{domain}/email-forwarding', [EmailForwardingController::class, 'index'])->name('email-forwarding.index');
    Route::post('/{domain}/email-forwarding', [EmailForwardingController::class, 'store'])->name('email-forwarding.store');
    Route::delete('/{domain}/email-forwarding/{id}', [EmailForwardingController::class, 'destroy'])->name('email-forwarding.destroy');

    // URL Redirection
    Route::get('/{domain}/url-forwarding', [UrlForwardingController::class, 'index'])->name('url-forwarding.index');
    Route::post('/{domain}/url-forwarding', [UrlForwardingController::class, 'store'])->name('url-forwarding.store');
    Route::delete('/{domain}/url-forwarding/{id}', [UrlForwardingController::class, 'destroy'])->name('url-forwarding.destroy');
});

// Invoices & Billing
Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/', [ClientInvoiceController::class, 'index'])->name('index');
    Route::get('/{invoice}', [ClientInvoiceController::class, 'show'])->name('show');
    Route::post('/{invoice}/pay', [ClientInvoiceController::class, 'pay'])->name('pay');
});

// Wallet & Financial Ledger
Route::prefix('wallet')->name('wallet.')->group(function () {
    Route::get('/', [ClientWalletController::class, 'index'])->name('index');
    Route::post('/add-funds', [ClientWalletController::class, 'addFunds'])->name('add-funds');
});
Route::get('/transactions', [ClientWalletController::class, 'transactions'])->name('transactions.index');

// Support Tickets
Route::prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [ClientTicketController::class, 'index'])->name('index');
    Route::get('/create', [ClientTicketController::class, 'create'])->name('create');
    Route::post('/', [ClientTicketController::class, 'store'])->name('store');
    Route::get('/{ticket}', [ClientTicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [ClientTicketController::class, 'reply'])->name('reply');
    Route::post('/{ticket}/close', [ClientTicketController::class, 'close'])->name('close');
});

// Platform Announcements
Route::prefix('announcements')->name('announcements.')->group(function () {
    Route::get('/', [ClientAnnouncementController::class, 'index'])->name('index');
    Route::get('/{id}', [ClientAnnouncementController::class, 'show'])->name('show');
});
