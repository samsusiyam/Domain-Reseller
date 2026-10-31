<?php
use App\Http\Controllers\Admin\ContactInquiryController;
use App\Http\Controllers\Admin\SystemHealthController;


use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ApiRequestLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\ClientManagementController;
use App\Http\Controllers\Admin\CurrencySettingController;
use App\Http\Controllers\Admin\DomainManagementController;
use App\Http\Controllers\Admin\DomainLifecycleController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\InvoiceManagementController;
use App\Http\Controllers\Admin\ManualPaymentController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\RegistrarModuleController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResellerManagementController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\SmsSettingController;
use App\Http\Controllers\Admin\TldPricingController;
use App\Http\Controllers\Admin\TransactionManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
| Prefix: /admin, Name prefix: admin.
|--------------------------------------------------------------------------
*/

// Admin Dashboard
Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

// Client Management
Route::prefix('clients')->name('clients.')->group(function () {
    Route::get('/', [ClientManagementController::class, 'index'])->name('index');
    Route::get('/create', [ClientManagementController::class, 'create'])->name('create');
    Route::post('/', [ClientManagementController::class, 'store'])->name('store');
    Route::get('/{client}', [ClientManagementController::class, 'show'])->name('show');
    Route::get('/{client}/edit', [ClientManagementController::class, 'edit'])->name('edit');
    Route::put('/{client}', [ClientManagementController::class, 'update'])->name('update');
    Route::delete('/{client}', [ClientManagementController::class, 'destroy'])->name('destroy');
    Route::post('/{client}/status', [ClientManagementController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{client}/adjust-balance', [ClientManagementController::class, 'adjustBalance'])->name('adjust-balance');
    Route::post('/{client}/login-as', [ClientManagementController::class, 'impersonate'])->name('impersonate');
});

// Reseller Management
Route::prefix('resellers')->name('resellers.')->group(function () {
    Route::get('/', [ResellerManagementController::class, 'index'])->name('index');
    Route::get('/create', [ResellerManagementController::class, 'create'])->name('create');
    Route::post('/', [ResellerManagementController::class, 'store'])->name('store');
    Route::get('/{reseller}', [ResellerManagementController::class, 'show'])->name('show');
    Route::get('/{reseller}/edit', [ResellerManagementController::class, 'edit'])->name('edit');
    Route::put('/{reseller}', [ResellerManagementController::class, 'update'])->name('update');
    Route::delete('/{reseller}', [ResellerManagementController::class, 'destroy'])->name('destroy');
    Route::post('/{reseller}/status', [ResellerManagementController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{reseller}/tier', [ResellerManagementController::class, 'updateTier'])->name('update-tier');
    Route::post('/{reseller}/adjust-balance', [ResellerManagementController::class, 'adjustBalance'])->name('adjust-balance');
    Route::get('/{reseller}/api-keys', [ResellerManagementController::class, 'apiKeys'])->name('api-keys');
    Route::post('/{reseller}/api-keys/{apiKey}/revoke', [ResellerManagementController::class, 'revokeApiKey'])->name('revoke-api-key');
});

// Domain Management
Route::get('domains-lifecycle', [DomainLifecycleController::class, 'index'])->name('domains.lifecycle');
    Route::post('domains-lifecycle/{id}/notice', [DomainLifecycleController::class, 'sendNotice'])->name('domains.lifecycle.notice');

    Route::prefix('domains')->name('domains.')->group(function () {
    Route::get('/', [DomainManagementController::class, 'index'])->name('index');
    Route::get('/{domain}', [DomainManagementController::class, 'show'])->name('show');
    Route::get('/{domain}/edit', [DomainManagementController::class, 'edit'])->name('edit');
    Route::put('/{domain}', [DomainManagementController::class, 'update'])->name('update');
    Route::post('/{domain}/sync', [DomainManagementController::class, 'syncWithRegistrar'])->name('sync');
    Route::post('/{domain}/renew', [DomainManagementController::class, 'renew'])->name('renew');
    Route::post('/{domain}/toggle-lock', [DomainManagementController::class, 'toggleLock'])->name('toggle-lock');
    Route::post('/{domain}/toggle-privacy', [DomainManagementController::class, 'togglePrivacy'])->name('toggle-privacy');
    Route::post('/{domain}/get-epp', [DomainManagementController::class, 'fetchEppCode'])->name('get-epp');
    Route::post('/{domain}/nameservers', [DomainManagementController::class, 'updateNameservers'])->name('nameservers');
    Route::delete('/{domain}', [DomainManagementController::class, 'destroy'])->name('destroy');
});

// TLD Pricing Matrix
Route::prefix('tlds')->name('tlds.')->group(function () {
    Route::get('/', [TldPricingController::class, 'index'])->name('index');
    Route::get('/create', [TldPricingController::class, 'create'])->name('create');
    Route::post('/', [TldPricingController::class, 'store'])->name('store');
    Route::get('/{tld}/edit', [TldPricingController::class, 'edit'])->name('edit');
    Route::put('/{tld}', [TldPricingController::class, 'update'])->name('update');
    Route::delete('/{tld}', [TldPricingController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-update', [TldPricingController::class, 'bulkUpdate'])->name('bulk-update');
    Route::post('/apply-margins', [TldPricingController::class, 'applyMargins'])->name('apply-margins');
    Route::post('/sync-costs', [TldPricingController::class, 'syncCosts'])->name('sync-costs');
});

// Registrar Modules
Route::prefix('registrars')->name('registrars.')->group(function () {
    Route::get('/', [RegistrarModuleController::class, 'index'])->name('index');
    Route::get('/{registrar}/edit', [RegistrarModuleController::class, 'edit'])->name('edit');
    Route::put('/{registrar}', [RegistrarModuleController::class, 'update'])->name('update');
    Route::post('/{registrar}/test-connection', [RegistrarModuleController::class, 'testConnection'])->name('test-connection');
    Route::post('/{registrar}/sync-balance', [RegistrarModuleController::class, 'syncBalance'])->name('sync-balance');
    Route::post('/{registrar}/toggle-status', [RegistrarModuleController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/set-default', [RegistrarModuleController::class, 'setDefault'])->name('set-default');
});

// Orders & Provisioning
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderManagementController::class, 'index'])->name('index');
    Route::get('/create', [OrderManagementController::class, 'create'])->name('create');
    Route::post('/', [OrderManagementController::class, 'store'])->name('store');
    Route::get('/{order}', [OrderManagementController::class, 'show'])->name('show');
    Route::post('/{order}/accept', [OrderManagementController::class, 'accept'])->name('accept');
    Route::post('/{order}/status', [OrderManagementController::class, 'updateStatus'])->name('status');
    Route::post('/{order}/retry-provision', [OrderManagementController::class, 'retryProvision'])->name('retry-provision');
    Route::post('/{order}/cancel', [OrderManagementController::class, 'cancel'])->name('cancel');
});

// Invoices & Billing
Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/', [InvoiceManagementController::class, 'index'])->name('index');
    Route::get('/create', [InvoiceManagementController::class, 'create'])->name('create');
    Route::post('/', [InvoiceManagementController::class, 'store'])->name('store');
    Route::get('/{invoice}', [InvoiceManagementController::class, 'show'])->name('show');
    Route::post('/{invoice}/mark-paid', [InvoiceManagementController::class, 'markPaid'])->name('mark-paid');
    Route::post('/{invoice}/cancel', [InvoiceManagementController::class, 'cancel'])->name('cancel');
    Route::post('/{invoice}/refund', [InvoiceManagementController::class, 'refund'])->name('refund');
    Route::get('/{invoice}/pdf', [InvoiceManagementController::class, 'generatePdf'])->name('pdf');
    Route::get('/{invoice}/print', [InvoiceManagementController::class, 'generatePdf'])->name('print');
});

// Transactions Ledger
Route::get('/transactions', [TransactionManagementController::class, 'index'])->name('transactions.index');

// Manual Offline Payment Approvals
Route::prefix('payments/manual-requests')->name('payments.manual.')->group(function () {
    Route::get('/', [ManualPaymentController::class, 'index'])->name('index');
    Route::post('/{id}/approve', [ManualPaymentController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [ManualPaymentController::class, 'reject'])->name('reject');
});

// Support Tickets
Route::prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index'])->name('index');
    Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
    Route::post('/', [SupportTicketController::class, 'store'])->name('store');
    Route::get('/{ticket}', [SupportTicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('reply');
    Route::post('/{ticket}/status', [SupportTicketController::class, 'updateStatus'])->name('status');
    Route::delete('/{ticket}', [SupportTicketController::class, 'destroy'])->name('destroy');
});

// Email Notification Templates
Route::prefix('email-templates')->name('email-templates.')->group(function () {
    Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
    Route::get('/{template}/edit', [EmailTemplateController::class, 'edit'])->name('edit');
    Route::put('/{template}', [EmailTemplateController::class, 'update'])->name('update');
    Route::post('/{template}/send-test', [EmailTemplateController::class, 'sendTest'])->name('send-test');
});

// Promo Codes
Route::prefix('promo-codes')->name('promo-codes.')->group(function () {
    Route::get('/', [PromoCodeController::class, 'index'])->name('index');
    Route::get('/create', [PromoCodeController::class, 'create'])->name('create');
    Route::post('/', [PromoCodeController::class, 'store'])->name('store');
    Route::get('/{promoCode}/edit', [PromoCodeController::class, 'edit'])->name('edit');
    Route::put('/{promoCode}', [PromoCodeController::class, 'update'])->name('update');
    Route::delete('/{promoCode}', [PromoCodeController::class, 'destroy'])->name('destroy');
});

// Announcements
Route::prefix('announcements')->name('announcements.')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::post('/', [AnnouncementController::class, 'store'])->name('store');
    Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
    Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
});

// Activity Logs
Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
    Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    Route::get('/{id}', [ActivityLogController::class, 'show'])->name('show');
    Route::delete('/clear-old', [ActivityLogController::class, 'clearOld'])->name('clear-old');
});

// WHMCS & Reseller API Inspector
Route::prefix('api-logs')->name('api-logs.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\ApiRequestLogController::class, 'index'])->name('index');
    Route::get('/{id}', [\App\Http\Controllers\Admin\ApiRequestLogController::class, 'show'])->name('show');
    Route::post('/clear', [\App\Http\Controllers\Admin\ApiRequestLogController::class, 'clear'])->name('clear');
});

// System Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/gateways', [SystemSettingController::class, 'gateways'])->name('gateways');
        Route::get('/sms', [SmsSettingController::class, 'index'])->name('sms');
        Route::post('/sms', [SmsSettingController::class, 'update'])->name('sms.update');
        Route::post('/sms/test', [SmsSettingController::class, 'sendTest'])->name('sms.test');
    Route::put('/gateways', [SystemSettingController::class, 'updateGateways'])->name('gateways.update');

    Route::get('/nameservers', [SystemSettingController::class, 'nameservers'])->name('nameservers');
    Route::put('/nameservers', [SystemSettingController::class, 'updateNameservers'])->name('nameservers.update');

    Route::get('/general', [SystemSettingController::class, 'general'])->name('general');
    Route::put('/general', [SystemSettingController::class, 'updateGeneral'])->name('general.update');

    Route::get('/email', [SystemSettingController::class, 'email'])->name('email');
    Route::put('/email', [SystemSettingController::class, 'updateEmail'])->name('email.update');
    Route::post('/email/test', [SystemSettingController::class, 'testEmail'])->name('email.test');

    Route::get('/security', [SystemSettingController::class, 'security'])->name('security');
    Route::put('/security', [SystemSettingController::class, 'updateSecurity'])->name('security.update');

    Route::get('/currencies', [CurrencySettingController::class, 'index'])->name('currencies');
    Route::post('/currencies', [CurrencySettingController::class, 'store'])->name('currencies.store');
    Route::put('/currencies/{currency}', [CurrencySettingController::class, 'update'])->name('currencies.update');
    Route::post('/currencies/update-rates', [CurrencySettingController::class, 'updateRates'])->name('currencies.update-rates');
});

// Reports & Business Analytics
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
    Route::get('/domains', [ReportController::class, 'domains'])->name('domains');
    Route::get('/clients', [ReportController::class, 'clients'])->name('clients');
    Route::get('/resellers', [ReportController::class, 'resellers'])->name('resellers');
    Route::get('/tlds', [ReportController::class, 'tlds'])->name('tlds');
    Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
});


// Contact Inquiries
Route::prefix('inquiries')->name('inquiries.')->group(function () {
    Route::get('/', [ContactInquiryController::class, 'index'])->name('index');
    Route::patch('/{inquiry}/status', [ContactInquiryController::class, 'updateStatus'])->name('status');
    Route::delete('/{inquiry}', [ContactInquiryController::class, 'destroy'])->name('destroy');
});

// System Health Diagnostics
Route::get('/system-health', [SystemHealthController::class, 'index'])->name('system-health');
Route::post('/system-health/backup', [SystemHealthController::class, 'createBackup'])->name('system-health.backup');
Route::get('/system-health/backup/download/{filename}', [SystemHealthController::class, 'downloadBackup'])->name('system-health.backup.download');
Route::delete('/system-health/backup/{filename}', [SystemHealthController::class, 'deleteBackup'])->name('system-health.backup.delete');

// Admin Account Profile, Password, Passkeys, 2FA, and Sessions
Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminProfileController::class, 'edit'])->name('edit');
    Route::put('/', [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('update');
    Route::put('/password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'updatePassword'])->name('password');
});

Route::prefix('passkeys')->name('passkeys.')->group(function () {
    Route::post('/register/options', [\App\Http\Controllers\Auth\PasskeyController::class, 'registerOptions'])->name('register.options');
    Route::post('/register', [\App\Http\Controllers\Auth\PasskeyController::class, 'store'])->name('store');
    Route::delete('/{passkey}', [\App\Http\Controllers\Auth\PasskeyController::class, 'destroy'])->name('destroy');
});

Route::prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminProfileController::class, 'twoFactor'])->name('index');
    Route::post('/confirm', [\App\Http\Controllers\Admin\AdminProfileController::class, 'confirmTwoFactor'])->name('confirm');
    Route::post('/disable', [\App\Http\Controllers\Admin\AdminProfileController::class, 'disableTwoFactor'])->name('disable');
});

Route::prefix('sessions')->name('sessions.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminProfileController::class, 'sessions'])->name('index');
    Route::delete('/other', [\App\Http\Controllers\Admin\AdminProfileController::class, 'destroyOtherSessions'])->name('destroy.other');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\AdminProfileController::class, 'destroySession'])->name('destroy');
});
