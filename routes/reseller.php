<?php

use App\Http\Controllers\Reseller\ApiKeyController;
use App\Http\Controllers\Reseller\ResellerDashboardController;
use App\Http\Controllers\Reseller\ResellerPricingController;
use App\Http\Controllers\Reseller\ResellerReportController;
use App\Http\Controllers\Reseller\SubClientController;
use App\Http\Controllers\Reseller\WebhookController;
use App\Http\Controllers\Reseller\WhitelabelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reseller Portal Routes
| Prefix: /reseller, Name prefix: reseller.
|--------------------------------------------------------------------------
*/

// Reseller Dashboard
Route::get('/dashboard', [ResellerDashboardController::class, 'index'])->name('dashboard');

// Wholesale Pricing Table
Route::get('/pricing', [ResellerPricingController::class, 'index'])->name('pricing');

// API Credentials & Logs
Route::prefix('api-keys')->name('api-keys.')->group(function () {
    Route::get('/', [ApiKeyController::class, 'index'])->name('index');
    Route::post('/', [ApiKeyController::class, 'store'])->name('store');
    Route::put('/{id}', [ApiKeyController::class, 'update'])->name('update');
    Route::delete('/{id}', [ApiKeyController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/regenerate-secret', [ApiKeyController::class, 'regenerateSecret'])->name('regenerate-secret');
});
Route::get('/api-logs', [ApiKeyController::class, 'logs'])->name('api-logs');
Route::get('/api-docs', [ApiKeyController::class, 'docs'])->name('api-docs');

// Webhook Endpoints
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::get('/', [WebhookController::class, 'index'])->name('index');
    Route::post('/', [WebhookController::class, 'store'])->name('store');
    Route::put('/{id}', [WebhookController::class, 'update'])->name('update');
    Route::delete('/{id}', [WebhookController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/test', [WebhookController::class, 'test'])->name('test');
});

// Sub-Clients / End Customer Contacts
Route::prefix('sub-clients')->name('sub-clients.')->group(function () {
    Route::get('/', [SubClientController::class, 'index'])->name('index');
    Route::get('/{id}', [SubClientController::class, 'show'])->name('show');
    Route::post('/{id}/status', [SubClientController::class, 'toggleStatus'])->name('status');
});

// Reseller Reports
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/domains', [ResellerReportController::class, 'domains'])->name('domains');
    Route::get('/revenue', [ResellerReportController::class, 'revenue'])->name('revenue');
    Route::get('/api-usage', [ResellerReportController::class, 'apiUsage'])->name('api-usage');
});

// White-Label Settings
Route::get('/whitelabel', [WhitelabelController::class, 'index'])->name('whitelabel');
Route::put('/whitelabel', [WhitelabelController::class, 'update'])->name('whitelabel.update');
