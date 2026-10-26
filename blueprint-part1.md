# Domain Reseller Panel — Technical Blueprint
## PART 1: Architecture, Project Structure, Database Migrations & Eloquent Models

---

### Executive Summary & Technical Constraints
This blueprint provides an exhaustive, production-grade technical specification for the **Domain Reseller Panel**. All architectural designs strictly adhere to the following project constraints:

| Parameter | Specification | Architectural Rationale |
| :--- | :--- | :--- |
| **Framework** | Laravel 11.x (PHP 8.2+) | Utilizing modern Laravel 11 slim skeleton, streamlined configs, class-based middleware/routing. |
| **Frontend Stack** | Blade Templates + Tailwind CSS 3.4 + Alpine.js 3.x | Lightweight, reactive, server-rendered UI without requiring a Node.js daemon at runtime. |
| **Database** | MySQL 8.0+ / MariaDB 10.5+ | Relational schema with strict foreign keys, transactional integrity, and compound indexes. |
| **Hosting Environment** | Shared Hosting Compatible (cPanel / DirectAdmin) | Zero reliance on Redis or Supervisor daemons; database queue driver and file cache driver. |
| **Queue Driver** | `database` (`php artisan queue:work --stop-when-empty` via cron) | Executes asynchronous domain provisioning, DNS syncing, webhooks, and email dispatches. |
| **Cache & Session** | `file` driver | Simple, robust, isolated file storage without external daemon dependencies. |
| **Primary Currency** | USD ($) with real-time BDT (৳) conversion | Dual-display, auto-converting exchange rates with administrative manual override. |
| **Language** | English Only (en-US) | Single-locale simplicity; clean view files without redundant translation overhead. |
| **User Roles** | Admin, Reseller, Client | Distinct namespaces, middleware boundaries, dashboards, and permission sets. No sub-resellers. |
| **Domain Scope** | Direct Registration, Transfer, Renewal & Management | No domain marketplace or auctions; pure registrar reseller ecosystem. |
| **Affiliate Scope** | None | No referral links, multi-tier tracking, or commissions. |
| **Supported Registrars** | Namecheap, Spaceship, Dynadot, AtakDomain, NameSilo | Standardized driver interface via Adapter & Factory design patterns. |
| **Payment Gateways** | Manual (Bank/Cash), bKash (Tokenized API), Stripe, Binance Pay | Modular payment manager with webhook validation and instant balance crediting. |

---

## 1. COMPLETE PROJECT DIRECTORY TREE

The project root directory is `f:\DomainReseller`. Below is the complete, file-by-file directory tree representing every single file required for the full-featured application.

```text
f:\DomainReseller
│
├── .env.example
├── .gitattributes
├── .gitignore
├── artisan
├── composer.json
├── package.json
├── phpunit.xml
├── postcss.config.js
├── README.md
├── tailwind.config.js
├── vite.config.js
│
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── CheckExpiringDomains.php
│   │       ├── CheckPendingOrders.php
│   │       ├── DatabaseBackup.php
│   │       ├── ProcessAutoRenewals.php
│   │       ├── ProcessDnsPropagationCheck.php
│   │       ├── PruneActivityLogs.php
│   │       ├── PruneWebhookLogs.php
│   │       ├── SendDomainExpirationReminders.php
│   │       ├── SyncDomainStatuses.php
│   │       ├── SyncRegistrarBalances.php
│   │       └── UpdateCurrencyRates.php
│   │
│   ├── Events/
│   │   ├── DomainExpiringSoon.php
│   │   ├── DomainRegistered.php
│   │   ├── DomainRenewed.php
│   │   ├── DomainStatusChanged.php
│   │   ├── DomainTransferred.php
│   │   ├── InvoiceCancelled.php
│   │   ├── InvoicePaid.php
│   │   ├── LowBalanceAlert.php
│   │   ├── OrderCompleted.php
│   │   ├── OrderCreated.php
│   │   ├── OrderFailed.php
│   │   ├── PasswordResetRequested.php
│   │   ├── SupportTicketCreated.php
│   │   ├── SupportTicketReplied.php
│   │   ├── TransactionCompleted.php
│   │   └── UserRegistered.php
│   │
│   ├── Exceptions/
│   │   ├── DomainNotAvailableException.php
│   │   ├── Handler.php
│   │   ├── InsufficientBalanceException.php
│   │   ├── InvalidApiKeyException.php
│   │   ├── PaymentGatewayException.php
│   │   └── RegistrarApiException.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AdminActivityLogController.php
│   │   │   │   ├── AdminAnnouncementController.php
│   │   │   │   ├── AdminCronLogController.php
│   │   │   │   ├── AdminCurrencyController.php
│   │   │   │   ├── AdminDashboardController.php
│   │   │   │   ├── AdminDomainController.php
│   │   │   │   ├── AdminEmailLogController.php
│   │   │   │   ├── AdminEmailTemplateController.php
│   │   │   │   ├── AdminInvoiceController.php
│   │   │   │   ├── AdminLoginHistoryController.php
│   │   │   │   ├── AdminOrderController.php
│   │   │   │   ├── AdminPaymentGatewayController.php
│   │   │   │   ├── AdminPromoCodeController.php
│   │   │   │   ├── AdminRegistrarModuleController.php
│   │   │   │   ├── AdminReportController.php
│   │   │   │   ├── AdminResellerController.php
│   │   │   │   ├── AdminResellerPricingController.php
│   │   │   │   ├── AdminSupportTicketController.php
│   │   │   │   ├── AdminSystemSettingController.php
│   │   │   │   ├── AdminTldPricingController.php
│   │   │   │   ├── AdminTransactionController.php
│   │   │   │   └── AdminUserController.php
│   │   │   │
│   │   │   ├── Api/
│   │   │   │   └── V1/
│   │   │   │       ├── Reseller/
│   │   │   │       │   ├── ApiBalanceController.php
│   │   │   │       │   ├── ApiChildNameserverController.php
│   │   │   │       │   ├── ApiContactController.php
│   │   │   │       │   ├── ApiDnsController.php
│   │   │   │       │   ├── ApiDomainCheckController.php
│   │   │   │       │   ├── ApiDomainManageController.php
│   │   │   │       │   ├── ApiDomainRegisterController.php
│   │   │   │       │   ├── ApiDomainRenewController.php
│   │   │   │       │   ├── ApiDomainTransferController.php
│   │   │   │       │   ├── ApiEmailForwardController.php
│   │   │   │       │   ├── ApiOrderController.php
│   │   │   │       │   ├── ApiPricingController.php
│   │   │   │       │   └── ApiUrlForwardController.php
│   │   │   │       └── Webhook/
│   │   │   │           ├── BinanceWebhookController.php
│   │   │   │           ├── BkashWebhookController.php
│   │   │   │           ├── RegistrarWebhookController.php
│   │   │   │           └── StripeWebhookController.php
│   │   │   │
│   │   │   ├── Auth/
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── NewPasswordController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   ├── TwoFactorAuthenticationController.php
│   │   │   │   └── VerifyEmailController.php
│   │   │   │
│   │   │   ├── Client/
│   │   │   │   ├── ClientAnnouncementController.php
│   │   │   │   ├── ClientChildNameserverController.php
│   │   │   │   ├── ClientContactController.php
│   │   │   │   ├── ClientDashboardController.php
│   │   │   │   ├── ClientDnsRecordController.php
│   │   │   │   ├── ClientDomainController.php
│   │   │   │   ├── ClientDomainSearchController.php
│   │   │   │   ├── ClientEmailForwardController.php
│   │   │   │   ├── ClientInvoiceController.php
│   │   │   │   ├── ClientOrderController.php
│   │   │   │   ├── ClientProfileController.php
│   │   │   │   ├── ClientSupportTicketController.php
│   │   │   │   ├── ClientTransactionController.php
│   │   │   │   ├── ClientTwoFactorController.php
│   │   │   │   └── ClientUrlForwardController.php
│   │   │   │
│   │   │   └── Reseller/
│   │   │       ├── ResellerAnnouncementController.php
│   │   │       ├── ResellerApiDocsController.php
│   │   │       ├── ResellerApiKeyController.php
│   │   │       ├── ResellerChildNameserverController.php
│   │   │       ├── ResellerContactController.php
│   │   │       ├── ResellerDashboardController.php
│   │   │       ├── ResellerDnsRecordController.php
│   │   │       ├── ResellerDomainController.php
│   │   │       ├── ResellerDomainSearchController.php
│   │   │       ├── ResellerEmailForwardController.php
│   │   │       ├── ResellerInvoiceController.php
│   │   │       ├── ResellerOrderController.php
│   │   │       ├── ResellerPricingController.php
│   │   │       ├── ResellerProfileController.php
│   │   │       ├── ResellerSupportTicketController.php
│   │   │       ├── ResellerTransactionController.php
│   │   │       ├── ResellerTwoFactorController.php
│   │   │       ├── ResellerUrlForwardController.php
│   │   │       └── ResellerWebhookController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── Authenticate.php
│   │   │   ├── CheckAccountStatus.php
│   │   │   ├── LogApiRequest.php
│   │   │   ├── ResellerApiKeyAuth.php
│   │   │   ├── RoleMiddleware.php
│   │   │   ├── SetCurrency.php
│   │   │   ├── TwoFactorVerify.php
│   │   │   └── VerifyWebhookSignature.php
│   │   │
│   │   └── Requests/
│   │       ├── Admin/
│   │       │   ├── StoreAnnouncementRequest.php
│   │       │   ├── StoreCurrencyRequest.php
│   │       │   ├── StoreEmailTemplateRequest.php
│   │       │   ├── StoreManualPaymentRequest.php
│   │       │   ├── StorePromoCodeRequest.php
│   │       │   ├── StoreTldPricingRequest.php
│   │       │   ├── StoreUserRequest.php
│   │       │   ├── UpdatePaymentGatewayRequest.php
│   │       │   ├── UpdateRegistrarModuleRequest.php
│   │       │   ├── UpdateResellerPricingRequest.php
│   │       │   ├── UpdateSystemSettingRequest.php
│   │       │   ├── UpdateTldPricingRequest.php
│   │       │   └── UpdateUserRequest.php
│   │       ├── Api/
│   │       │   ├── ApiCheckDomainRequest.php
│   │       │   ├── ApiCreateContactRequest.php
│   │       │   ├── ApiCreateDnsRecordRequest.php
│   │       │   ├── ApiCreateNameserverRequest.php
│   │       │   ├── ApiRegisterDomainRequest.php
│   │       │   ├── ApiRenewDomainRequest.php
│   │       │   ├── ApiTransferDomainRequest.php
│   │       │   ├── ApiUpdateDnsRecordRequest.php
│   │       │   └── ApiUpdateNameserversRequest.php
│   │       ├── Auth/
│   │       │   ├── LoginRequest.php
│   │       │   ├── RegisterRequest.php
│   │       │   ├── ResetPasswordRequest.php
│   │       │   └── TwoFactorLoginRequest.php
│   │       ├── Client/
│   │       │   ├── CheckoutRequest.php
│   │       │   ├── DomainSearchRequest.php
│   │       │   ├── StoreChildNameserverRequest.php
│   │       │   ├── StoreContactRequest.php
│   │       │   ├── StoreDnsRecordRequest.php
│   │       │   ├── StoreEmailForwardRequest.php
│   │       │   ├── StoreSupportTicketRequest.php
│   │       │   ├── StoreTicketReplyRequest.php
│   │       │   ├── StoreUrlForwardRequest.php
│   │       │   ├── UpdateContactRequest.php
│   │       │   ├── UpdateNameserversRequest.php
│   │       │   └── UpdateProfileRequest.php
│   │       └── Reseller/
│   │           ├── BulkDomainSearchRequest.php
│   │           ├── ResellerCheckoutRequest.php
│   │           ├── StoreApiKeyRequest.php
│   │           ├── StoreResellerChildNameserverRequest.php
│   │           ├── StoreResellerContactRequest.php
│   │           ├── StoreResellerDnsRecordRequest.php
│   │           ├── StoreResellerEmailForwardRequest.php
│   │           ├── StoreResellerTicketReplyRequest.php
│   │           ├── StoreResellerTicketRequest.php
│   │           ├── StoreResellerUrlForwardRequest.php
│   │           ├── StoreWebhookRequest.php
│   │           ├── UpdateApiKeyRequest.php
│   │           └── UpdateWebhookRequest.php
│   │
│   ├── Jobs/
│   │   ├── DispatchWebhookJob.php
│   │   ├── GenerateInvoicePdfJob.php
│   │   ├── ProcessPendingPaymentJob.php
│   │   ├── RegisterDomainJob.php
│   │   ├── RenewDomainJob.php
│   │   ├── SendEmailJob.php
│   │   ├── SyncDomainStatusJob.php
│   │   ├── TransferDomainJob.php
│   │   ├── UpdateDnsRecordsJob.php
│   │   └── UpdateNameserversJob.php
│   │
│   ├── Listeners/
│   │   ├── DispatchWebhookOnEvent.php
│   │   ├── GenerateInvoicePdf.php
│   │   ├── LogActivity.php
│   │   ├── LogUserLogin.php
│   │   ├── ProcessOrderFulfillment.php
│   │   ├── SendDomainExpiringNotification.php
│   │   ├── SendDomainRegisteredNotification.php
│   │   ├── SendDomainRenewedNotification.php
│   │   ├── SendDomainTransferredNotification.php
│   │   ├── SendInvoicePaidNotification.php
│   │   └── SendTicketNotification.php
│   │
│   ├── Models/
│   │   ├── ActivityLog.php
│   │   ├── Announcement.php
│   │   ├── ApiKey.php
│   │   ├── ChildNameserver.php
│   │   ├── Contact.php
│   │   ├── CronLog.php
│   │   ├── Currency.php
│   │   ├── DnsRecord.php
│   │   ├── Domain.php
│   │   ├── EmailForward.php
│   │   ├── EmailLog.php
│   │   ├── EmailTemplate.php
│   │   ├── FailedJob.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── Job.php
│   │   ├── LoginHistory.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── PasswordResetToken.php
│   │   ├── PromoCode.php
│   │   ├── RegistrarModule.php
│   │   ├── ResellerPricing.php
│   │   ├── Session.php
│   │   ├── SupportTicket.php
│   │   ├── SupportTicketReply.php
│   │   ├── SystemSetting.php
│   │   ├── TldPricing.php
│   │   ├── Transaction.php
│   │   ├── TwoFactorToken.php
│   │   ├── UrlForward.php
│   │   ├── User.php
│   │   ├── Webhook.php
│   │   └── WebhookLog.php
│   │
│   ├── Notifications/
│   │   ├── DomainExpiringReminderNotification.php
│   │   ├── DomainRegisteredNotification.php
│   │   ├── DomainRenewedNotification.php
│   │   ├── DomainTransferredNotification.php
│   │   ├── InvoiceGeneratedNotification.php
│   │   ├── InvoicePaidNotification.php
│   │   ├── LowBalanceNotification.php
│   │   ├── SupportTicketCreatedNotification.php
│   │   ├── SupportTicketReplyNotification.php
│   │   └── TwoFactorCodeNotification.php
│   │
│   ├── Services/
│   │   ├── Audit/
│   │   │   └── ActivityLogger.php
│   │   ├── Currency/
│   │   │   └── CurrencyConverter.php
│   │   ├── Domain/
│   │   │   ├── DomainAvailabilityService.php
│   │   │   ├── DomainManagementService.php
│   │   │   ├── DomainOrderService.php
│   │   │   └── DomainPricingService.php
│   │   ├── Payments/
│   │   │   ├── AbstractPaymentGateway.php
│   │   │   ├── BinancePaymentGateway.php
│   │   │   ├── BkashPaymentGateway.php
│   │   │   ├── ManualPaymentGateway.php
│   │   │   ├── PaymentGatewayInterface.php
│   │   │   ├── PaymentManager.php
│   │   │   └── StripePaymentGateway.php
│   │   ├── Registrars/
│   │   │   ├── AbstractRegistrar.php
│   │   │   ├── AtakDomainRegistrar.php
│   │   │   ├── DynadotRegistrar.php
│   │   │   ├── NamecheapRegistrar.php
│   │   │   ├── NamesiloRegistrar.php
│   │   │   ├── RegistrarInterface.php
│   │   │   ├── RegistrarManager.php
│   │   │   └── SpaceshipRegistrar.php
│   │   └── Webhook/
│   │       └── WebhookDispatcher.php
│   │
│   └── View/
│       └── Components/
│           ├── AdminLayout.php
│           ├── Alert.php
│           ├── Button.php
│           ├── ClientLayout.php
│           ├── DataTable.php
│           ├── GuestLayout.php
│           ├── Modal.php
│           ├── ResellerLayout.php
│           ├── StatCard.php
│           └── StatusBadge.php
│
├── bootstrap/
│   ├── app.php
│   └── providers.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── currencies.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── payment_gateways.php
│   ├── queue.php
│   ├── registrars.php
│   ├── reseller.php
│   ├── services.php
│   └── session.php
│
├── database/
│   ├── factories/
│   │   ├── ContactFactory.php
│   │   ├── DomainFactory.php
│   │   ├── OrderFactory.php
│   │   ├── TldPricingFactory.php
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_password_reset_tokens_table.php
│   │   ├── 2024_01_01_000003_create_sessions_table.php
│   │   ├── 2024_01_01_000004_create_two_factor_tokens_table.php
│   │   ├── 2024_01_01_000005_create_currencies_table.php
│   │   ├── 2024_01_01_000006_create_system_settings_table.php
│   │   ├── 2024_01_01_000007_create_registrar_modules_table.php
│   │   ├── 2024_01_01_000008_create_tld_pricing_table.php
│   │   ├── 2024_01_01_000009_create_reseller_pricing_table.php
│   │   ├── 2024_01_01_000010_create_contacts_table.php
│   │   ├── 2024_01_01_000011_create_domains_table.php
│   │   ├── 2024_01_01_000012_create_dns_records_table.php
│   │   ├── 2024_01_01_000013_create_child_nameservers_table.php
│   │   ├── 2024_01_01_000014_create_email_forwards_table.php
│   │   ├── 2024_01_01_000015_create_url_forwards_table.php
│   │   ├── 2024_01_01_000016_create_promo_codes_table.php
│   │   ├── 2024_01_01_000017_create_orders_table.php
│   │   ├── 2024_01_01_000018_create_order_items_table.php
│   │   ├── 2024_01_01_000019_create_invoices_table.php
│   │   ├── 2024_01_01_000020_create_invoice_items_table.php
│   │   ├── 2024_01_01_000021_create_transactions_table.php
│   │   ├── 2024_01_01_000022_create_api_keys_table.php
│   │   ├── 2024_01_01_000023_create_webhooks_table.php
│   │   ├── 2024_01_01_000024_create_webhook_logs_table.php
│   │   ├── 2024_01_01_000025_create_support_tickets_table.php
│   │   ├── 2024_01_01_000026_create_support_ticket_replies_table.php
│   │   ├── 2024_01_01_000027_create_email_templates_table.php
│   │   ├── 2024_01_01_000028_create_email_logs_table.php
│   │   ├── 2024_01_01_000029_create_activity_logs_table.php
│   │   ├── 2024_01_01_000030_create_login_histories_table.php
│   │   ├── 2024_01_01_000031_create_announcements_table.php
│   │   ├── 2024_01_01_000032_create_cron_logs_table.php
│   │   ├── 2024_01_01_000033_create_jobs_table.php
│   │   └── 2024_01_01_000034_create_failed_jobs_table.php
│   └── seeders/
│       ├── CurrencySeeder.php
│       ├── DatabaseSeeder.php
│       ├── EmailTemplateSeeder.php
│       ├── RegistrarModuleSeeder.php
│       ├── SystemSettingSeeder.php
│       ├── TldPricingSeeder.php
│       └── UserSeeder.php
│
├── public/
│   ├── .htaccess
│   ├── favicon.ico
│   ├── index.php
│   ├── robots.txt
│   ├── css/
│   │   └── custom.css
│   ├── images/
│   │   ├── logo-dark.svg
│   │   ├── logo-light.svg
│   │   ├── registrars/
│   │   │   ├── atakdomain.png
│   │   │   ├── dynadot.png
│   │   │   ├── namecheap.png
│   │   │   ├── namesilo.png
│   │   │   └── spaceship.png
│   │   └── gateways/
│   │       ├── binance.png
│   │       ├── bkash.png
│   │       ├── manual.png
│   │       └── stripe.png
│   └── js/
│       └── app.js
│
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views/
│       ├── components/
│       │   ├── alert.blade.php
│       │   ├── badge.blade.php
│       │   ├── button.blade.php
│       │   ├── card.blade.php
│       │   ├── data-table.blade.php
│       │   ├── dropdown.blade.php
│       │   ├── input.blade.php
│       │   ├── modal.blade.php
│       │   ├── pagination.blade.php
│       │   ├── select.blade.php
│       │   ├── stat-box.blade.php
│       │   └── textarea.blade.php
│       │
│       ├── layouts/
│       │   ├── admin.blade.php
│       │   ├── auth.blade.php
│       │   ├── client.blade.php
│       │   ├── guest.blade.php
│       │   ├── reseller.blade.php
│       │   └── partials/
│       │       ├── admin-sidebar.blade.php
│       │       ├── client-sidebar.blade.php
│       │       ├── footer.blade.php
│       │       ├── header.blade.php
│       │       └── reseller-sidebar.blade.php
│       │
│       ├── auth/
│       │   ├── forgot-password.blade.php
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   ├── reset-password.blade.php
│       │   ├── two-factor.blade.php
│       │   └── verify-email.blade.php
│       │
│       ├── admin/
│       │   ├── activity-logs/
│       │   │   └── index.blade.php
│       │   ├── announcements/
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   ├── cron-logs/
│       │   │   └── index.blade.php
│       │   ├── currencies/
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   ├── dashboard.blade.php
│       │   ├── domains/
│       │   │   ├── edit.blade.php
│       │   │   ├── index.blade.php
│       │   │   ├── show.blade.php
│       │   │   └── sync.blade.php
│       │   ├── email-logs/
│       │   │   ├── index.blade.php
│       │   │   └── show.blade.php
│       │   ├── email-templates/
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   ├── gateways/
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   ├── invoices/
│       │   │   ├── create.blade.php
│       │   │   ├── index.blade.php
│       │   │   └── show.blade.php
│       │   ├── login-histories/
│       │   │   └── index.blade.php
│       │   ├── orders/
│       │   │   ├── index.blade.php
│       │   │   └── show.blade.php
│       │   ├── promo-codes/
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   ├── registrars/
│       │   │   ├── balance.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   ├── reports/
│       │   │   ├── domains.blade.php
│       │   │   ├── financial.blade.php
│       │   │   └── index.blade.php
│       │   ├── reseller-pricing/
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   ├── resellers/
│       │   │   ├── index.blade.php
│       │   │   ├── pricing.blade.php
│       │   │   └── show.blade.php
│       │   ├── settings/
│       │   │   ├── automation.blade.php
│       │   │   ├── backup.blade.php
│       │   │   ├── email.blade.php
│       │   │   └── general.blade.php
│       │   ├── tickets/
│       │   │   ├── index.blade.php
│       │   │   └── show.blade.php
│       │   ├── tld-pricing/
│       │   │   ├── bulk-edit.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── index.blade.php
│       │   ├── transactions/
│       │   │   ├── create.blade.php
│       │   │   └── index.blade.php
│       │   └── users/
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       │
│       ├── client/
│       │   ├── announcements/
│       │   │   ├── index.blade.php
│       │   │   └── show.blade.php
│       │   ├── dashboard.blade.php
│       │   ├── domains/
│       │   │   ├── auth-code.blade.php
│       │   │   ├── cart.blade.php
│       │   │   ├── checkout.blade.php
│       │   │   ├── child-nameservers.blade.php
│       │   │   ├── contacts.blade.php
│       │   │   ├── dns.blade.php
│       │   │   ├── forwarding.blade.php
│       │   │   ├── index.blade.php
│       │   │   ├── lock.blade.php
│       │   │   ├── nameservers.blade.php
│       │   │   ├── renew.blade.php
│       │   │   ├── search.blade.php
│       │   │   ├── show.blade.php
│       │   └── transfer.blade.php
│       │   ├── invoices/
│       │   │   ├── index.blade.php
│       │   │   ├── pay.blade.php
│       │   │   └── show.blade.php
│       │   ├── orders/
│       │   │   ├── index.blade.php
│       │   │   └── show.blade.php
│       │   ├── profile/
│       │   │   ├── show.blade.php
│       │   │   └── two-factor.blade.php
│       │   ├── tickets/
│       │   │   ├── create.blade.php
│       │   │   ├── index.blade.php
│       │   │   └── show.blade.php
│       │   └── transactions/
│       │       └── index.blade.php
│       │
│       └── reseller/
│           ├── announcements/
│           │   ├── index.blade.php
│           │   └── show.blade.php
│           ├── api/
│           │   ├── docs.blade.php
│           │   ├── keys.blade.php
│           │   ├── webhook-logs.blade.php
│           │   └── webhooks.blade.php
│           ├── dashboard.blade.php
│           ├── domains/
│           │   ├── auth-code.blade.php
│           │   ├── bulk-search.blade.php
│           │   ├── child-nameservers.blade.php
│           │   ├── contacts.blade.php
│           │   ├── dns.blade.php
│           │   ├── forwarding.blade.php
│           │   ├── index.blade.php
│           │   ├── lock.blade.php
│           │   ├── nameservers.blade.php
│           │   ├── renew.blade.php
│           │   ├── search.blade.php
│           │   ├── show.blade.php
│           │   └── transfer.blade.php
│           ├── invoices/
│           │   ├── index.blade.php
│           │   ├── pay.blade.php
│           │   └── show.blade.php
│           ├── orders/
│           │   ├── index.blade.php
│           │   └── show.blade.php
│           ├── pricing/
│           │   └── index.blade.php
│           ├── profile/
│           │   ├── show.blade.php
│           │   └── two-factor.blade.php
│           ├── tickets/
│           │   ├── create.blade.php
│           │   ├── index.blade.php
│           │   └── show.blade.php
│           └── transactions/
│               └── index.blade.php
│
├── routes/
│   ├── admin.php
│   ├── api.php
│   ├── auth.php
│   ├── client.php
│   ├── console.php
│   ├── reseller.php
│   └── web.php
│
└── storage/
    ├── app/
    │   ├── backups/
    │   ├── invoices/
    │   └── public/
    ├── framework/
    │   ├── cache/
    │   │   └── data/
    │   ├── sessions/
    │   ├── testing/
    │   └── views/
    └── logs/
        ├── api.log
        ├── cron.log
        ├── laravel.log
        ├── payment.log
        └── registrar.log
```

---

## 2. COMPLETE DATABASE MIGRATIONS (ALL 34 TABLES)

This section provides exhaustive migration definitions for every table in the database schema. Standard Laravel timestamps (`created_at`, `updated_at`) and soft deletes (`deleted_at`) are explicitly defined.

---

### Table 1: `users`
**Migration File:** `2024_01_01_000001_create_users_table.php`  
**Purpose:** Stores all administrative staff, domain resellers, and retail clients. Contains account status, profile information, and wallet balance.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `uuid` | `CHAR(36)` | `NOT NULL` | Unique public identifier for API/URL safety. |
| `role` | `ENUM` | `DEFAULT 'client'` | Values: `'admin'`, `'reseller'`, `'client'`. Controls authorization scope. |
| `name` | `VARCHAR(150)` | `NOT NULL` | Full legal name or representative name. |
| `email` | `VARCHAR(191)` | `NOT NULL` | Unique login email and communication destination. |
| `email_verified_at` | `TIMESTAMP` | `NULL` | Verification timestamp. |
| `password` | `VARCHAR(255)` | `NOT NULL` | Bcrypt / Argon2id hashed password string. |
| `phone` | `VARCHAR(30)` | `NULL` | International format phone number (e.g. `+8801700000000`). |
| `company_name` | `VARCHAR(150)` | `NULL` | Organization / business entity name. |
| `address_line_1` | `VARCHAR(255)` | `NULL` | Street address line 1. |
| `address_line_2` | `VARCHAR(255)` | `NULL` | Suite, unit, building or floor. |
| `city` | `VARCHAR(100)` | `NULL` | City or municipality. |
| `state` | `VARCHAR(100)` | `NULL` | State, province, or district. |
| `country` | `CHAR(2)` | `NULL` | ISO 3166-1 alpha-2 country code (e.g., `'US'`, `'BD'`). |
| `postal_code` | `VARCHAR(20)` | `NULL` | ZIP or postal code. |
| `balance` | `DECIMAL(15,4)` | `DEFAULT 0.0000` | Account credit balance in primary base currency (USD). |
| `currency_id` | `BIGINT UNSIGNED` | `NULL` | Preferred display currency reference. |
| `status` | `ENUM` | `DEFAULT 'active'` | Values: `'active'`, `'suspended'`, `'pending_verification'`. |
| `two_factor_secret` | `TEXT` | `NULL` | Encrypted 2FA TOTP secret key. |
| `two_factor_recovery_codes`| `TEXT` | `NULL` | Encrypted JSON list of recovery codes. |
| `two_factor_confirmed_at` | `TIMESTAMP` | `NULL` | Timestamp when user confirmed 2FA activation. |
| `remember_token` | `VARCHAR(100)` | `NULL` | Session remember cookie token. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `deleted_at` | `TIMESTAMP` | `NULL` | Soft deletes for compliance and audit recovery. |

- **Primary Key:** `id`
- **Unique Indexes:** `uuid` (`users_uuid_unique`), `email` (`users_email_unique`)
- **Foreign Keys:**
  - `currency_id` references `currencies(id)` on delete `SET NULL` on update `CASCADE`
- **Composite / Performance Indexes:**
  - `INDEX users_role_status_idx (role, status)` — Optimizes user directory filtering.
  - `INDEX users_country_idx (country)` — Optimizes regional queries.

---

### Table 2: `password_reset_tokens`
**Migration File:** `2024_01_01_000002_create_password_reset_tokens_table.php`  
**Purpose:** Handles secure temporary tokens for password reset verification.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `email` | `VARCHAR(191)` | `NOT NULL` | Target user email address. Primary key. |
| `token` | `VARCHAR(255)` | `NOT NULL` | Cryptographic hash of the reset token. |
| `created_at` | `TIMESTAMP` | `NULL` | Issue timestamp. Expired after configured threshold (e.g., 60 mins). |

- **Primary Key:** `email`
- **Performance Indexes:** None required beyond primary key.

---

### Table 3: `sessions`
**Migration File:** `2024_01_01_000003_create_sessions_table.php`  
**Purpose:** Stores user session payloads when running database session storage or for security monitoring.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `VARCHAR(191)` | `NOT NULL` | Session hash token. Primary key. |
| `user_id` | `BIGINT UNSIGNED` | `NULL` | Authenticated user reference. |
| `ip_address` | `VARCHAR(45)` | `NULL` | IPv4 / IPv6 client address. |
| `user_agent` | `TEXT` | `NULL` | Browser User-Agent string. |
| `payload` | `LONGTEXT` | `NOT NULL` | Serialized session data. |
| `last_activity` | `INTEGER` | `NOT NULL` | Unix timestamp of the last request. |

- **Primary Key:** `id`
- **Performance Indexes:**
  - `INDEX sessions_user_id_index (user_id)`
  - `INDEX sessions_last_activity_index (last_activity)`

---

### Table 4: `two_factor_tokens`
**Migration File:** `2024_01_01_000004_create_two_factor_tokens_table.php`  
**Purpose:** Stores one-time numeric passcode tokens sent via email/SMS for 2FA verification.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | Target user reference. |
| `token` | `VARCHAR(10)` | `NOT NULL` | Hashed 6-digit one-time code. |
| `expires_at` | `TIMESTAMP` | `NOT NULL` | Token expiry timestamp (typically 10 minutes from creation). |
| `created_at` | `TIMESTAMP` | `NULL` | Generation timestamp. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX two_factor_tokens_user_expires_idx (user_id, expires_at)`

---

### Table 5: `currencies`
**Migration File:** `2024_01_01_000005_create_currencies_table.php`  
**Purpose:** Defines monetary units with primary currency in USD and real-time/manual BDT conversion rates.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `code` | `CHAR(3)` | `NOT NULL` | ISO 4217 currency code (`'USD'`, `'BDT'`). Unique. |
| `name` | `VARCHAR(50)` | `NOT NULL` | Currency display title (e.g. 'US Dollar', 'Bangladeshi Taka'). |
| `symbol` | `VARCHAR(10)` | `NOT NULL` | Symbol character (`'$'`, `'৳'`). |
| `exchange_rate` | `DECIMAL(14,6)` | `DEFAULT 1.000000` | Exchange rate relative to USD (USD=1.000000, BDT=118.500000). |
| `is_primary` | `BOOLEAN` | `DEFAULT 0` | 1 if base ledger currency (USD). Only one row can have 1. |
| `is_active` | `BOOLEAN` | `DEFAULT 1` | 1 for active currencies selectable by users. |
| `decimal_places`| `TINYINT UNSIGNED`| `DEFAULT 2` | Display decimal precision (typically 2). |
| `symbol_position`| `ENUM` | `DEFAULT 'before'` | Values: `'before'` ($10.00), `'after'` (10.00৳). |
| `last_updated_at`| `TIMESTAMP` | `NULL` | Timestamp of the last automatic/manual rate update. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `code` (`currencies_code_unique`)
- **Performance Indexes:**
  - `INDEX currencies_is_active_idx (is_active)`

---

### Table 6: `system_settings`
**Migration File:** `2024_01_01_000006_create_system_settings_table.php`  
**Purpose:** Key-value store for application configuration (branding, mail, automation, security).

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `setting_key` | `VARCHAR(100)` | `NOT NULL` | Unique configuration slug (e.g. `'company_name'`, `'default_nameservers'`). |
| `setting_value` | `LONGTEXT` | `NULL` | Serialized or plain string value. |
| `group` | `ENUM` | `DEFAULT 'general'`| Grouping: `'general'`, `'email'`, `'domain'`, `'security'`, `'payment'`, `'automation'`. |
| `is_encrypted` | `BOOLEAN` | `DEFAULT 0` | If 1, value is encrypted using Laravel `Crypt::encryptString`. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `setting_key` (`system_settings_key_unique`)
- **Performance Indexes:**
  - `INDEX system_settings_group_idx (group)`

---

### Table 7: `registrar_modules`
**Migration File:** `2024_01_01_000007_create_registrar_modules_table.php`  
**Purpose:** Configuration, credentials, API balance tracking, and mode for domain registrars.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `name` | `VARCHAR(100)` | `NOT NULL` | Display name (e.g. 'Namecheap', 'Spaceship', 'Dynadot', 'AtakDomain', 'NameSilo'). |
| `slug` | `VARCHAR(50)` | `NOT NULL` | Driver slug matching factory (`'namecheap'`, `'spaceship'`, `'dynadot'`, `'atakdomain'`, `'namesilo'`). |
| `is_active` | `BOOLEAN` | `DEFAULT 0` | Whether registrar is enabled for API routing. |
| `is_default` | `BOOLEAN` | `DEFAULT 0` | Fallback registrar for newly added TLDs. |
| `environment` | `ENUM` | `DEFAULT 'sandbox'`| Operational environment: `'sandbox'` or `'production'`. |
| `config` | `TEXT` | `NOT NULL` | Encrypted JSON containing API keys, secrets, API username, IP whitelist details. |
| `balance` | `DECIMAL(12,4)` | `DEFAULT 0.0000` | Current credit balance fetched from registrar API in USD. |
| `currency` | `CHAR(3)` | `DEFAULT 'USD'` | Registrar billing currency (typically USD). |
| `last_balance_sync_at`| `TIMESTAMP` | `NULL` | Last execution timestamp of balance retrieval cron. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `slug` (`registrar_modules_slug_unique`)
- **Performance Indexes:**
  - `INDEX registrar_modules_is_active_idx (is_active)`

---

### Table 8: `tld_pricing`
**Migration File:** `2024_01_01_000008_create_tld_pricing_table.php`  
**Purpose:** Global TLD directory defining wholesale registrar costs, retail client prices, and default reseller baseline prices.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `tld` | `VARCHAR(30)` | `NOT NULL` | Top-Level Domain including leading dot (e.g., `'.com'`, `'.net'`, `'.com.bd'`). |
| `registrar_id` | `BIGINT UNSIGNED` | `NOT NULL` | Assigned registrar module for automation. |
| `cost_register` | `DECIMAL(10,4)` | `NOT NULL` | Registrar wholesale cost for 1-year registration (USD). |
| `cost_renew` | `DECIMAL(10,4)` | `NOT NULL` | Registrar wholesale cost for 1-year renewal (USD). |
| `cost_transfer` | `DECIMAL(10,4)` | `NOT NULL` | Registrar wholesale cost for domain transfer (USD). |
| `client_register` | `DECIMAL(10,4)` | `NOT NULL` | Retail price charged to direct Clients for registration (USD). |
| `client_renew` | `DECIMAL(10,4)` | `NOT NULL` | Retail price charged to direct Clients for renewal (USD). |
| `client_transfer` | `DECIMAL(10,4)` | `NOT NULL` | Retail price charged to direct Clients for transfer (USD). |
| `default_reseller_register`| `DECIMAL(10,4)` | `NOT NULL` | Baseline reseller price for registration (USD). |
| `default_reseller_renew` | `DECIMAL(10,4)` | `NOT NULL` | Baseline reseller price for renewal (USD). |
| `default_reseller_transfer`| `DECIMAL(10,4)` | `NOT NULL` | Baseline reseller price for transfer (USD). |
| `min_years` | `TINYINT UNSIGNED`| `DEFAULT 1` | Minimum registration period in years (typically 1). |
| `max_years` | `TINYINT UNSIGNED`| `DEFAULT 10` | Maximum registration period in years (typically 10). |
| `is_active` | `BOOLEAN` | `DEFAULT 1` | TLD enabled for public search and ordering. |
| `dns_management_available`| `BOOLEAN` | `DEFAULT 1` | Whether registrar provides free DNS hosting for this TLD. |
| `email_forwarding_available`| `BOOLEAN` | `DEFAULT 1` | Whether email forwarding is supported for this TLD. |
| `id_protection_available`| `BOOLEAN` | `DEFAULT 1` | Whether WHOIS privacy protection is supported. |
| `id_protection_cost` | `DECIMAL(10,4)` | `DEFAULT 0.0000` | Cost of ID protection addon per year (USD). |
| `epp_code_required` | `BOOLEAN` | `DEFAULT 1` | Transfer requires EPP/Auth-Code from prior registrar. |
| `grace_period_days` | `SMALLINT UNSIGNED`| `DEFAULT 30` | Number of days domain can be renewed post-expiry without penalty. |
| `redemption_period_days`| `SMALLINT UNSIGNED`| `DEFAULT 30` | Days domain stays in redemption before deletion. |
| `redemption_fee` | `DECIMAL(10,4)` | `DEFAULT 80.0000`| Fee required to restore domain from redemption (USD). |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `tld` (`tld_pricing_tld_unique`)
- **Foreign Keys:**
  - `registrar_id` references `registrar_modules(id)` on delete `RESTRICT` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX tld_pricing_active_tld_idx (is_active, tld)`

---

### Table 9: `reseller_pricing`
**Migration File:** `2024_01_01_000009_create_reseller_pricing_table.php`  
**Purpose:** Custom per-reseller pricing overrides for specific TLDs. Overrides `default_reseller_*` prices in `tld_pricing`.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | Reseller account reference. Must have role `'reseller'`. |
| `tld_pricing_id` | `BIGINT UNSIGNED` | `NOT NULL` | Reference to the TLD. |
| `register_price` | `DECIMAL(10,4)` | `NOT NULL` | Custom registration price for this reseller (USD). |
| `renew_price` | `DECIMAL(10,4)` | `NOT NULL` | Custom renewal price for this reseller (USD). |
| `transfer_price` | `DECIMAL(10,4)` | `NOT NULL` | Custom transfer price for this reseller (USD). |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `user_id`, `tld_pricing_id` (`reseller_pricing_user_tld_unique`)
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `CASCADE` on update `CASCADE`
  - `tld_pricing_id` references `tld_pricing(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX reseller_pricing_user_idx (user_id)`

---

### Table 10: `contacts`
**Migration File:** `2024_01_01_000010_create_contacts_table.php`  
**Purpose:** Reusable WHOIS contact profiles (Registrant, Admin, Tech, Billing) per user.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | Owner user ID. |
| `contact_type` | `ENUM` | `DEFAULT 'individual'`| Values: `'individual'`, `'company'`. |
| `first_name` | `VARCHAR(100)` | `NOT NULL` | Contact given name. |
| `last_name` | `VARCHAR(100)` | `NOT NULL` | Contact surname. |
| `company_name` | `VARCHAR(150)` | `NULL` | Legal organization name (if company). |
| `email` | `VARCHAR(191)` | `NOT NULL` | WHOIS email address. |
| `phone_country_code`| `VARCHAR(5)` | `NOT NULL` | Dial code without plus (e.g. `'1'`, `'880'`). |
| `phone_number` | `VARCHAR(25)` | `NOT NULL` | Phone subscriber number without dial code. |
| `fax` | `VARCHAR(30)` | `NULL` | Optional fax number. |
| `address_line_1` | `VARCHAR(255)` | `NOT NULL` | Street address. |
| `address_line_2` | `VARCHAR(255)` | `NULL` | Additional address details. |
| `city` | `VARCHAR(100)` | `NOT NULL` | City name. |
| `state` | `VARCHAR(100)` | `NOT NULL` | State, province or territory. |
| `country` | `CHAR(2)` | `NOT NULL` | ISO 3166-1 alpha-2 code. |
| `postal_code` | `VARCHAR(20)` | `NOT NULL` | Postal / ZIP code. |
| `is_default` | `BOOLEAN` | `DEFAULT 0` | Automatically pre-selected during checkout. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `deleted_at` | `TIMESTAMP` | `NULL` | Soft delete support. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX contacts_user_default_idx (user_id, is_default)`

---

### Table 11: `domains`
**Migration File:** `2024_01_01_000011_create_domains_table.php`  
**Purpose:** Core domain asset table tracking lifecycle, registrars, locking, privacy, and WHOIS links.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `uuid` | `CHAR(36)` | `NOT NULL` | Unique public identifier. |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | Owning client or reseller. |
| `registrar_id` | `BIGINT UNSIGNED` | `NOT NULL` | Registrar module fulfilling this domain. |
| `domain_name` | `VARCHAR(255)` | `NOT NULL` | Fully qualified domain name (e.g., `'example.com'`). |
| `tld` | `VARCHAR(30)` | `NOT NULL` | Extension extracted for indexing (e.g., `'.com'`). |
| `registration_years`| `TINYINT UNSIGNED`| `DEFAULT 1` | Duration of initial registration/last renewal. |
| `status` | `ENUM` | `DEFAULT 'pending'`| Values: `'active'`, `'pending'`, `'expired'`, `'redemption'`, `'transferring_in'`, `'transferring_out'`, `'cancelled'`, `'fraud'`. |
| `registrar_order_id`| `VARCHAR(100)` | `NULL` | Order/transaction ID returned by upstream registrar. |
| `registrar_domain_id`| `VARCHAR(100)` | `NULL` | Upstream registrar internal domain identifier. |
| `is_locked` | `BOOLEAN` | `DEFAULT 1` | Registrar lock / ClientTransferProhibited status. |
| `is_private` | `BOOLEAN` | `DEFAULT 0` | WHOIS ID protection privacy mask status. |
| `is_auto_renew` | `BOOLEAN` | `DEFAULT 0` | Automatic renewal using account balance. |
| `nameservers` | `JSON` | `NULL` | Array of current nameservers (e.g. `["ns1.domain.com", "ns2.domain.com"]`). |
| `auth_code` | `VARCHAR(255)` | `NULL` | Encrypted EPP authorization transfer code. |
| `registered_at` | `TIMESTAMP` | `NULL` | Date and time domain was registered. |
| `expires_at` | `TIMESTAMP` | `NULL` | Exact expiration timestamp from registry. |
| `next_due_date` | `DATE` | `NULL` | Date invoice is generated for upcoming renewal. |
| `last_sync_at` | `TIMESTAMP` | `NULL` | Timestamp of last automated sync with registrar API. |
| `registrar_cost` | `DECIMAL(10,4)` | `DEFAULT 0.0000` | Cost charged by registrar at purchase (USD). |
| `selling_price` | `DECIMAL(10,4)` | `DEFAULT 0.0000` | Revenue billed to user at purchase (USD). |
| `registrant_contact_id`| `BIGINT UNSIGNED`| `NULL` | WHOIS Registrant contact record reference. |
| `admin_contact_id` | `BIGINT UNSIGNED` | `NULL` | WHOIS Admin contact record reference. |
| `tech_contact_id` | `BIGINT UNSIGNED` | `NULL` | WHOIS Tech contact record reference. |
| `billing_contact_id`| `BIGINT UNSIGNED`| `NULL` | WHOIS Billing contact record reference. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `deleted_at` | `TIMESTAMP` | `NULL` | Soft deletes for audit history. |

- **Primary Key:** `id`
- **Unique Indexes:** `uuid` (`domains_uuid_unique`), `domain_name` (`domains_domain_name_unique`)
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `RESTRICT` on update `CASCADE`
  - `registrar_id` references `registrar_modules(id)` on delete `RESTRICT` on update `CASCADE`
  - `registrant_contact_id` references `contacts(id)` on delete `SET NULL` on update `CASCADE`
  - `admin_contact_id` references `contacts(id)` on delete `SET NULL` on update `CASCADE`
  - `tech_contact_id` references `contacts(id)` on delete `SET NULL` on update `CASCADE`
  - `billing_contact_id` references `contacts(id)` on delete `SET NULL` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX domains_user_status_idx (user_id, status)` — Optimizes user domain listings.
  - `INDEX domains_expires_status_idx (expires_at, status)` — Used by expiration reminder crons.
  - `INDEX domains_tld_idx (tld)` — Used in reporting and bulk filtering.

---

### Table 12: `dns_records`
**Migration File:** `2024_01_01_000012_create_dns_records_table.php`  
**Purpose:** Custom DNS resource records synced to upstream registrar DNS servers.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `domain_id` | `BIGINT UNSIGNED` | `NOT NULL` | Owning domain reference. |
| `type` | `ENUM` | `NOT NULL` | Values: `'A'`, `'AAAA'`, `'CNAME'`, `'MX'`, `'TXT'`, `'NS'`, `'SRV'`, `'CAA'`. |
| `host` | `VARCHAR(150)` | `NOT NULL` | Subdomain prefix (e.g. `'@'`, `'www'`, `'mail'`). |
| `value` | `TEXT` | `NOT NULL` | Destination IP address, hostname, or TXT content. |
| `priority` | `SMALLINT UNSIGNED`| `NULL` | Priority integer for MX and SRV records. |
| `port` | `SMALLINT UNSIGNED`| `NULL` | Port for SRV records. |
| `weight` | `SMALLINT UNSIGNED`| `NULL` | Weight for SRV records. |
| `ttl` | `INTEGER UNSIGNED`| `DEFAULT 3600` | Time To Live in seconds (minimum 60). |
| `registrar_record_id`| `VARCHAR(100)` | `NULL` | External record identifier assigned by registrar. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `domain_id` references `domains(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX dns_records_domain_idx (domain_id)`
  - `INDEX dns_records_domain_type_idx (domain_id, type)`

---

### Table 13: `child_nameservers`
**Migration File:** `2024_01_01_000013_create_child_nameservers_table.php`  
**Purpose:** Registered glue host records (child nameservers) configured at the registry.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `domain_id` | `BIGINT UNSIGNED` | `NOT NULL` | Parent domain reference. |
| `hostname` | `VARCHAR(150)` | `NOT NULL` | FQDN child host (e.g. `'ns1.example.com'`). |
| `ip_address` | `VARCHAR(45)` | `NOT NULL` | IPv4 or IPv6 glue address. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `domain_id`, `hostname` (`child_ns_domain_hostname_unique`)
- **Foreign Keys:**
  - `domain_id` references `domains(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX child_nameservers_domain_idx (domain_id)`

---

### Table 14: `email_forwards`
**Migration File:** `2024_01_01_000014_create_email_forwards_table.php`  
**Purpose:** Email aliases forwarding inbound mail to external mailboxes.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `domain_id` | `BIGINT UNSIGNED` | `NOT NULL` | Parent domain reference. |
| `source_prefix` | `VARCHAR(100)` | `NOT NULL` | Local part before `@` (e.g., `'info'`, `'sales'`). |
| `destination_email`| `VARCHAR(191)` | `NOT NULL` | External recipient email (e.g., `'client@gmail.com'`). |
| `is_active` | `BOOLEAN` | `DEFAULT 1` | Forwarding rule state. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `domain_id`, `source_prefix` (`email_forwards_domain_prefix_unique`)
- **Foreign Keys:**
  - `domain_id` references `domains(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX email_forwards_domain_idx (domain_id)`

---

### Table 15: `url_forwards`
**Migration File:** `2024_01_01_000015_create_url_forwards_table.php`  
**Purpose:** Web URL redirection and URL masking configurations.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `domain_id` | `BIGINT UNSIGNED` | `NOT NULL` | Parent domain reference. |
| `source_subdomain`| `VARCHAR(100)` | `DEFAULT '@'` | Subdomain prefix (`'@'` for root, `'blog'`, etc.). |
| `destination_url` | `TEXT` | `NOT NULL` | Target web address (e.g., `'https://newsite.com'`). |
| `redirect_type` | `ENUM` | `DEFAULT '301'` | Values: `'301'` (Permanent), `'302'` (Temporary), `'masked'` (Frame Cloaked). |
| `page_title` | `VARCHAR(150)` | `NULL` | HTML title tag for masked redirects. |
| `meta_description`| `TEXT` | `NULL` | Meta description for masked redirects. |
| `is_active` | `BOOLEAN` | `DEFAULT 1` | Redirection status. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `domain_id` references `domains(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX url_forwards_domain_idx (domain_id)`

---

### Table 16: `promo_codes`
**Migration File:** `2024_01_01_000016_create_promo_codes_table.php`  
**Purpose:** Promotional discount vouchers applicable at checkout.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `code` | `VARCHAR(50)` | `NOT NULL` | Coupon code entered by user (e.g., `'SPRING2026'`). |
| `description` | `VARCHAR(255)` | `NULL` | Internal explanation of campaign. |
| `discount_type` | `ENUM` | `DEFAULT 'percentage'`| Values: `'percentage'` (% off) or `'fixed'` ($ off). |
| `discount_value`| `DECIMAL(10,2)` | `NOT NULL` | Percentage (e.g. 15.00) or amount in USD (e.g. 5.00). |
| `max_uses` | `INTEGER UNSIGNED`| `DEFAULT 0` | 0 = unlimited usage; >0 = total redemption cap. |
| `current_uses` | `INTEGER UNSIGNED`| `DEFAULT 0` | Counter tracking completed redemptions. |
| `user_usage_limit`| `TINYINT UNSIGNED`| `DEFAULT 1` | Max times a single user can redeem this code. |
| `valid_from` | `TIMESTAMP` | `NULL` | Coupon activation window start. |
| `valid_to` | `TIMESTAMP` | `NULL` | Coupon expiration window end. |
| `applicable_tlds` | `JSON` | `NULL` | JSON array of eligible TLDs (e.g. `[".com", ".net"]`); `NULL` for all. |
| `applicable_actions`| `JSON`| `NULL` | Eligible actions: `["register", "renew", "transfer"]`. |
| `min_order_amount`| `DECIMAL(10,2)` | `DEFAULT 0.00` | Minimum cart subtotal in USD required to apply. |
| `is_active` | `BOOLEAN` | `DEFAULT 1` | Administrative toggle. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `code` (`promo_codes_code_unique`)
- **Performance Indexes:**
  - `INDEX promo_codes_active_window_idx (is_active, valid_from, valid_to)`

---

### Table 17: `orders`
**Migration File:** `2024_01_01_000017_create_orders_table.php`  
**Purpose:** Header entity recording checkout actions (registrations, renewals, transfers).

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `uuid` | `CHAR(36)` | `NOT NULL` | Public identifier. |
| `order_number` | `VARCHAR(50)` | `NOT NULL` | Sequential human-readable code (e.g., `'ORD-20261026-0001'`). |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | Purchasing client or reseller reference. |
| `promo_code_id` | `BIGINT UNSIGNED` | `NULL` | Applied discount coupon reference. |
| `total_amount` | `DECIMAL(10,4)` | `NOT NULL` | Net final order amount in primary currency (USD). |
| `discount_amount`| `DECIMAL(10,4)` | `DEFAULT 0.0000` | Discount deducted via promo code (USD). |
| `currency` | `CHAR(3)` | `DEFAULT 'USD'` | Base currency code. |
| `status` | `ENUM` | `DEFAULT 'pending'`| Values: `'pending'`, `'processing'`, `'completed'`, `'failed'`, `'cancelled'`. |
| `source` | `ENUM` | `DEFAULT 'web'` | Source of order: `'web'` (portal UI) or `'api'` (reseller API). |
| `ip_address` | `VARCHAR(45)` | `NULL` | Originating client IP address. |
| `notes` | `TEXT` | `NULL` | Order notes or failure reasons. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `uuid` (`orders_uuid_unique`), `order_number` (`orders_order_number_unique`)
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `RESTRICT` on update `CASCADE`
  - `promo_code_id` references `promo_codes(id)` on delete `SET NULL` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX orders_user_status_idx (user_id, status)`
  - `INDEX orders_created_status_idx (created_at, status)`

---

### Table 18: `order_items`
**Migration File:** `2024_01_01_000018_create_order_items_table.php`  
**Purpose:** Granular line items within an order detailing each domain operation and optional addons.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `order_id` | `BIGINT UNSIGNED` | `NOT NULL` | Parent order reference. |
| `domain_id` | `BIGINT UNSIGNED` | `NULL` | Created or modified domain record reference. |
| `item_type` | `ENUM` | `NOT NULL` | Values: `'domain_register'`, `'domain_renew'`, `'domain_transfer'`, `'id_protection'`, `'redemption_fee'`. |
| `domain_name` | `VARCHAR(255)` | `NOT NULL` | Target domain name for line item. |
| `years` | `TINYINT UNSIGNED`| `DEFAULT 1` | Duration period in years. |
| `unit_price` | `DECIMAL(10,4)` | `NOT NULL` | Cost per year or unit in USD. |
| `total_price` | `DECIMAL(10,4)` | `NOT NULL` | Line item total (`unit_price * years`). |
| `registrar_id` | `BIGINT UNSIGNED` | `NOT NULL` | Target upstream registrar for fulfillment. |
| `registrar_cost` | `DECIMAL(10,4)` | `DEFAULT 0.0000` | Realized registrar fee incurred. |
| `registrar_payload` | `JSON` | `NULL` | Raw request parameters dispatched to registrar. |
| `registrar_response`| `JSON` | `NULL` | Full API response or error object from registrar. |
| `status` | `ENUM` | `DEFAULT 'pending'`| Values: `'pending'`, `'processing'`, `'completed'`, `'failed'`. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `order_id` references `orders(id)` on delete `CASCADE` on update `CASCADE`
  - `domain_id` references `domains(id)` on delete `SET NULL` on update `CASCADE`
  - `registrar_id` references `registrar_modules(id)` on delete `RESTRICT` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX order_items_order_id_idx (order_id)`
  - `INDEX order_items_status_idx (status)`

---

### Table 19: `invoices`
**Migration File:** `2024_01_01_000019_create_invoices_table.php`  
**Purpose:** Formal accounting billing documents tracking charges, taxes, discounts, and payment status.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `uuid` | `CHAR(36)` | `NOT NULL` | Public identifier. |
| `invoice_number`| `VARCHAR(50)` | `NOT NULL` | Sequential invoice series (e.g., `'INV-2026-00001'`). |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | Billed customer reference. |
| `order_id` | `BIGINT UNSIGNED` | `NULL` | Associated order (if generated via checkout). |
| `subtotal` | `DECIMAL(10,4)` | `NOT NULL` | Sum of line items before adjustments (USD). |
| `tax_amount` | `DECIMAL(10,4)` | `DEFAULT 0.0000` | Applicable tax or VAT (USD). |
| `discount_amount`| `DECIMAL(10,4)` | `DEFAULT 0.0000` | Subtotal reduction from coupons or credit (USD). |
| `total_amount` | `DECIMAL(10,4)` | `NOT NULL` | Net amount payable (USD). |
| `balance_due` | `DECIMAL(10,4)` | `NOT NULL` | Remaining unpaid balance (USD). |
| `currency` | `CHAR(3)` | `DEFAULT 'USD'` | Base currency code. |
| `status` | `ENUM` | `DEFAULT 'unpaid'` | Values: `'unpaid'`, `'paid'`, `'partially_paid'`, `'cancelled'`, `'refunded'`. |
| `due_date` | `DATE` | `NOT NULL` | Final payment due date. |
| `paid_at` | `TIMESTAMP` | `NULL` | Date and time full payment was settled. |
| `payment_method`| `VARCHAR(50)` | `NULL` | Gateway or method used (`'manual'`, `'bkash'`, `'stripe'`, `'binance'`, `'balance'`). |
| `notes` | `TEXT` | `NULL` | Administrative or customer-facing remarks. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `uuid` (`invoices_uuid_unique`), `invoice_number` (`invoices_number_unique`)
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `RESTRICT` on update `CASCADE`
  - `order_id` references `orders(id)` on delete `SET NULL` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX invoices_user_status_idx (user_id, status)`
  - `INDEX invoices_due_status_idx (due_date, status)`

---

### Table 20: `invoice_items`
**Migration File:** `2024_01_01_000020_create_invoice_items_table.php`  
**Purpose:** Itemized billing lines within an invoice.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `invoice_id` | `BIGINT UNSIGNED` | `NOT NULL` | Parent invoice reference. |
| `description` | `VARCHAR(255)` | `NOT NULL` | Narrative line item label (e.g. `'Domain Registration - example.com (1 Year)'`). |
| `quantity` | `SMALLINT UNSIGNED`| `DEFAULT 1` | Count or period multiplier. |
| `unit_price` | `DECIMAL(10,4)` | `NOT NULL` | Price per item in USD. |
| `total_price` | `DECIMAL(10,4)` | `NOT NULL` | `quantity * unit_price` in USD. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `invoice_id` references `invoices(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX invoice_items_invoice_id_idx (invoice_id)`

---

### Table 21: `transactions`
**Migration File:** `2024_01_01_000021_create_transactions_table.php`  
**Purpose:** Double-entry ledger recording all monetary movements (gateway deposits, invoice payments, balance debits, refunds).

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `uuid` | `CHAR(36)` | `NOT NULL` | Public identifier. |
| `transaction_number`| `VARCHAR(60)` | `NOT NULL` | Unique internal reference (e.g., `'TXN-20261026-94821'`). |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | User ledger reference. |
| `invoice_id` | `BIGINT UNSIGNED` | `NULL` | Settled invoice reference (if tied to invoice). |
| `gateway` | `ENUM` | `NOT NULL` | Values: `'manual'`, `'bkash'`, `'stripe'`, `'binance'`, `'balance'`. |
| `gateway_transaction_id`| `VARCHAR(150)`| `NULL` | External transaction hash, reference, or Stripe PI/bKash TrxID. |
| `type` | `ENUM` | `NOT NULL` | Movement type: `'credit'` (deposit/refund) or `'debit'` (checkout/invoice payment). |
| `amount` | `DECIMAL(15,4)` | `NOT NULL` | Transaction amount in the processing currency. |
| `currency` | `CHAR(3)` | `NOT NULL` | Currency of transaction (e.g. `'USD'`, `'BDT'`). |
| `exchange_rate` | `DECIMAL(14,6)` | `DEFAULT 1.000000` | Rate against USD at time of transaction execution. |
| `amount_in_usd` | `DECIMAL(15,4)` | `NOT NULL` | Canonical normalized amount added/subtracted to USD balance. |
| `fee` | `DECIMAL(10,4)` | `DEFAULT 0.0000` | Gateway surcharge or processing fee incurred (USD). |
| `status` | `ENUM` | `DEFAULT 'pending'`| Values: `'pending'`, `'completed'`, `'failed'`, `'cancelled'`, `'refunded'`. |
| `payment_details`| `JSON` | `NULL` | Encrypted/raw gateway payload, bKash sender number, bank receipt ref. |
| `verified_by_admin_id`| `BIGINT UNSIGNED`| `NULL` | Admin user who confirmed manual transaction. |
| `verified_at` | `TIMESTAMP` | `NULL` | Manual confirmation timestamp. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `uuid` (`transactions_uuid_unique`), `transaction_number` (`transactions_number_unique`)
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `RESTRICT` on update `CASCADE`
  - `invoice_id` references `invoices(id)` on delete `SET NULL` on update `CASCADE`
  - `verified_by_admin_id` references `users(id)` on delete `SET NULL` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX transactions_user_created_idx (user_id, created_at)`
  - `INDEX transactions_gateway_status_idx (gateway, status)`
  - `INDEX transactions_gateway_trx_id_idx (gateway_transaction_id)`

---

### Table 22: `api_keys`
**Migration File:** `2024_01_01_000022_create_api_keys_table.php`  
**Purpose:** REST API authentication credentials issued to Resellers for programmatic domain automation.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | Owning reseller reference. |
| `key_name` | `VARCHAR(100)` | `NOT NULL` | Label identifying integration (e.g. `'WHMCS Server 1'`). |
| `api_key` | `VARCHAR(64)` | `NOT NULL` | Public API token identifier (displayed once). Hashed in DB. |
| `api_secret` | `TEXT` | `NOT NULL` | Encrypted HMAC signature secret for webhook and payload validation. |
| `ip_whitelist` | `JSON` | `NULL` | Array of IPv4/IPv6 addresses permitted to use this key. |
| `allowed_endpoints`| `JSON` | `NULL` | Array of allowed route prefixes (e.g. `["domains:read", "domains:write"]`). |
| `status` | `ENUM` | `DEFAULT 'active'` | Values: `'active'`, `'revoked'`, `'suspended'`. |
| `last_used_at` | `TIMESTAMP` | `NULL` | Timestamp of last received valid API call. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `api_key` (`api_keys_key_unique`)
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX api_keys_user_status_idx (user_id, status)`

---

### Table 23: `webhooks`
**Migration File:** `2024_01_01_000023_create_webhooks_table.php`  
**Purpose:** Outbound webhook registration endpoints for resellers to receive real-time event updates.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | Owning reseller reference. |
| `target_url` | `TEXT` | `NOT NULL` | HTTPS target URL to receive POST notifications. |
| `secret_key` | `VARCHAR(64)` | `NOT NULL` | HMAC secret for `X-Signature-SHA256` payload verification. |
| `subscribed_events`| `JSON`| `NOT NULL` | Array of subscribed event names (e.g. `["domain.registered", "domain.renewed", "domain.expired"]`). |
| `is_active` | `BOOLEAN` | `DEFAULT 1` | Operational state. |
| `failure_count` | `TINYINT UNSIGNED`| `DEFAULT 0` | Consecutive HTTP 5xx / timeout failure tally. Auto-disables at 10. |
| `last_triggered_at`| `TIMESTAMP` | `NULL` | Timestamp of last event dispatch. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX webhooks_user_active_idx (user_id, is_active)`

---

### Table 24: `webhook_logs`
**Migration File:** `2024_01_01_000024_create_webhook_logs_table.php`  
**Purpose:** Audit log of outbound webhook HTTP requests, status codes, and latency.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `webhook_id` | `BIGINT UNSIGNED` | `NOT NULL` | Originating webhook configuration reference. |
| `event_name` | `VARCHAR(100)` | `NOT NULL` | Name of dispatched event (e.g. `'domain.registered'`). |
| `payload` | `JSON` | `NOT NULL` | JSON payload sent to recipient. |
| `response_code` | `SMALLINT UNSIGNED`| `NULL` | HTTP response code received (e.g., 200, 500, 404). |
| `response_body` | `TEXT` | `NULL` | Body snippet returned by target server (truncated to 1000 chars). |
| `execution_time_ms`| `INTEGER UNSIGNED`| `NOT NULL` | Roundtrip latency in milliseconds. |
| `status` | `ENUM` | `DEFAULT 'success'`| Values: `'success'`, `'failed'`. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `webhook_id` references `webhooks(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX webhook_logs_webhook_created_idx (webhook_id, created_at)`
  - `INDEX webhook_logs_status_idx (status)`

---

### Table 25: `support_tickets`
**Migration File:** `2024_01_01_000025_create_support_tickets_table.php`  
**Purpose:** Customer helpdesk tickets submitted by clients and resellers to administrative support.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `uuid` | `CHAR(36)` | `NOT NULL` | Public identifier. |
| `ticket_number` | `VARCHAR(30)` | `NOT NULL` | Unique ticket identifier (e.g. `'TKT-202610-4491'`). |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | Creator user reference. |
| `domain_id` | `BIGINT UNSIGNED` | `NULL` | Related domain reference (optional context). |
| `department` | `ENUM` | `DEFAULT 'technical'`| Values: `'technical'`, `'billing'`, `'domains'`, `'general'`. |
| `priority` | `ENUM` | `DEFAULT 'medium'` | Values: `'low'`, `'medium'`, `'high'`, `'urgent'`. |
| `subject` | `VARCHAR(200)` | `NOT NULL` | Ticket subject line. |
| `status` | `ENUM` | `DEFAULT 'open'` | Values: `'open'`, `'answered'`, `'customer_reply'`, `'in_progress'`, `'closed'`. |
| `last_reply_at` | `TIMESTAMP` | `NULL` | Timestamp of latest message or reply. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `uuid` (`tickets_uuid_unique`), `ticket_number` (`tickets_number_unique`)
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `CASCADE` on update `CASCADE`
  - `domain_id` references `domains(id)` on delete `SET NULL` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX tickets_user_status_idx (user_id, status)`
  - `INDEX tickets_status_priority_idx (status, priority)`

---

### Table 26: `support_ticket_replies`
**Migration File:** `2024_01_01_000026_create_support_ticket_replies_table.php`  
**Purpose:** Sequential communication messages posted within a support ticket.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `support_ticket_id`| `BIGINT UNSIGNED`| `NOT NULL` | Parent ticket reference. |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL` | Message author reference (Admin or Client/Reseller). |
| `is_staff_reply`| `BOOLEAN` | `DEFAULT 0` | 1 if response authored by an administrator. |
| `message` | `LONGTEXT` | `NOT NULL` | Sanitized markdown / rich text content of the reply. |
| `attachments` | `JSON` | `NULL` | Stored array of relative file paths of uploaded documents. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `support_ticket_id` references `support_tickets(id)` on delete `CASCADE` on update `CASCADE`
  - `user_id` references `users(id)` on delete `RESTRICT` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX ticket_replies_ticket_created_idx (support_ticket_id, created_at)`

---

### Table 27: `email_templates`
**Migration File:** `2024_01_01_000027_create_email_templates_table.php`  
**Purpose:** Customizable transactional email templates with dynamic placeholder variables.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `template_key` | `VARCHAR(100)` | `NOT NULL` | Unique slug (e.g. `'domain_registered'`, `'invoice_paid'`, `'two_factor_code'`). |
| `name` | `VARCHAR(150)` | `NOT NULL` | Human-friendly title. |
| `subject` | `VARCHAR(255)` | `NOT NULL` | Email subject line with placeholder tokens (e.g. `'Domain {domain_name} Registered'`). |
| `body_html` | `LONGTEXT` | `NOT NULL` | Responsive HTML email template body. |
| `body_plain` | `TEXT` | `NULL` | Plain text fallback body. |
| `available_placeholders`| `JSON`| `NULL` | Array of recognized placeholder tags (e.g. `["{user_name}", "{domain_name}", "{expiry_date}"]`). |
| `is_active` | `BOOLEAN` | `DEFAULT 1` | If disabled, emails of this type are suppressed. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `template_key` (`email_templates_key_unique`)

---

### Table 28: `email_logs`
**Migration File:** `2024_01_01_000028_create_email_logs_table.php`  
**Purpose:** Outbound transactional email transmission history and delivery status.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `user_id` | `BIGINT UNSIGNED` | `NULL` | Recipient user ID (if registered). |
| `recipient_email`| `VARCHAR(191)` | `NOT NULL` | Target email address. |
| `template_key` | `VARCHAR(100)` | `NULL` | Originating email template key. |
| `subject` | `VARCHAR(255)` | `NOT NULL` | Rendered subject line. |
| `body` | `LONGTEXT` | `NOT NULL` | Rendered HTML email body. |
| `status` | `ENUM` | `DEFAULT 'sent'` | Values: `'sent'`, `'failed'`. |
| `error_message` | `TEXT` | `NULL` | Exception or SMTP error message on failure. |
| `sent_at` | `TIMESTAMP` | `NULL` | Transmission timestamp. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `SET NULL` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX email_logs_recipient_idx (recipient_email)`
  - `INDEX email_logs_created_at_idx (created_at)`

---

### Table 29: `activity_logs`
**Migration File:** `2024_01_01_000029_create_activity_logs_table.php`  
**Purpose:** Comprehensive audit trail recording user and administrator actions across the platform.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `user_id` | `BIGINT UNSIGNED` | `NULL` | Actor ID who performed the action (`NULL` for system/cron). |
| `log_name` | `VARCHAR(100)` | `DEFAULT 'default'`| Category (e.g., `'domain'`, `'auth'`, `'billing'`, `'settings'`). |
| `action` | `VARCHAR(100)` | `NOT NULL` | Action identifier (e.g., `'domain.nameservers_updated'`). |
| `description` | `VARCHAR(255)` | `NOT NULL` | Human-readable explanation. |
| `subject_type` | `VARCHAR(150)` | `NULL` | Polymorphic subject model class (e.g. `App\Models\Domain`). |
| `subject_id` | `BIGINT UNSIGNED` | `NULL` | Polymorphic subject primary key. |
| `properties` | `JSON` | `NULL` | Changeset payload with `'before'` and `'after'` values. |
| `ip_address` | `VARCHAR(45)` | `NULL` | Client IP address. |
| `user_agent` | `TEXT` | `NULL` | Browser User-Agent. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `SET NULL` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX activity_logs_subject_idx (subject_type, subject_id)`
  - `INDEX activity_logs_user_created_idx (user_id, created_at)`
  - `INDEX activity_logs_log_name_idx (log_name)`

---

### Table 30: `login_histories`
**Migration File:** `2024_01_01_000030_create_login_histories_table.php`  
**Purpose:** Security telemetry recording user authentication attempts (both successes and failures).

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `user_id` | `BIGINT UNSIGNED` | `NULL` | Target account reference (`NULL` if unrecognized email attempted). |
| `email` | `VARCHAR(191)` | `NOT NULL` | Email string entered in login form. |
| `ip_address` | `VARCHAR(45)` | `NOT NULL` | Client IP address. |
| `user_agent` | `TEXT` | `NULL` | Client User-Agent string. |
| `country` | `CHAR(2)` | `NULL` | Country code derived from IP geolocation. |
| `city` | `VARCHAR(100)` | `NULL` | City derived from IP geolocation. |
| `status` | `ENUM` | `NOT NULL` | Values: `'success'`, `'failed'`. |
| `failure_reason`| `VARCHAR(255)` | `NULL` | Description of failure (e.g. `'Invalid credentials'`, `'Account suspended'`). |
| `login_at` | `TIMESTAMP` | `NOT NULL` | Timestamp of attempt. |

- **Primary Key:** `id`
- **Foreign Keys:**
  - `user_id` references `users(id)` on delete `CASCADE` on update `CASCADE`
- **Performance Indexes:**
  - `INDEX login_histories_user_idx (user_id, login_at)`
  - `INDEX login_histories_ip_idx (ip_address, status)`

---

### Table 31: `announcements`
**Migration File:** `2024_01_01_000031_create_announcements_table.php`  
**Purpose:** Platform broadcast notices and maintenance advisories shown on dashboards.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `title` | `VARCHAR(200)` | `NOT NULL` | Announcement headline. |
| `slug` | `VARCHAR(220)` | `NOT NULL` | URL-safe slug. |
| `content` | `LONGTEXT` | `NOT NULL` | Rich text / markdown notification body. |
| `target_audience`| `ENUM` | `DEFAULT 'all'` | Values: `'all'` (All users), `'clients'`, `'resellers'`. |
| `is_published` | `BOOLEAN` | `DEFAULT 0` | Visibility flag. |
| `published_at` | `TIMESTAMP` | `NULL` | Publishing date. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |
| `updated_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `slug` (`announcements_slug_unique`)
- **Performance Indexes:**
  - `INDEX announcements_published_audience_idx (is_published, target_audience, published_at)`

---

### Table 32: `cron_logs`
**Migration File:** `2024_01_01_000032_create_cron_logs_table.php`  
**Purpose:** Execution telemetry, duration, and output logs for scheduled Artisan commands.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `command_name` | `VARCHAR(150)` | `NOT NULL` | Signature of command (e.g., `'domains:sync-status'`). |
| `status` | `ENUM` | `DEFAULT 'running'`| Values: `'running'`, `'completed'`, `'failed'`. |
| `output` | `LONGTEXT` | `NULL` | Console output stream or error backtrace. |
| `duration_seconds`| `DECIMAL(8,2)` | `DEFAULT 0.00` | Runtime duration in fractional seconds. |
| `executed_at` | `TIMESTAMP` | `NOT NULL` | Start timestamp of cron trigger. |
| `completed_at` | `TIMESTAMP` | `NULL` | Finish timestamp. |
| `created_at` | `TIMESTAMP` | `NULL` | Standard timestamp. |

- **Primary Key:** `id`
- **Performance Indexes:**
  - `INDEX cron_logs_command_executed_idx (command_name, executed_at)`
  - `INDEX cron_logs_status_idx (status)`

---

### Table 33: `jobs`
**Migration File:** `2024_01_01_000033_create_jobs_table.php`  
**Purpose:** Standard Laravel database queue table supporting shared-hosting asynchronous task processing.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `queue` | `VARCHAR(191)` | `NOT NULL` | Queue partition name (e.g., `'default'`, `'domains'`, `'webhooks'`). |
| `payload` | `LONGTEXT` | `NOT NULL` | Serialized PHP job class and properties. |
| `attempts` | `TINYINT UNSIGNED`| `NOT NULL` | Number of times the job has been attempted. |
| `reserved_at` | `INTEGER UNSIGNED`| `NULL` | Unix timestamp when a worker claimed the job. |
| `available_at` | `INTEGER UNSIGNED`| `NOT NULL` | Unix timestamp when the job becomes eligible for execution. |
| `created_at` | `INTEGER UNSIGNED`| `NOT NULL` | Unix timestamp of job dispatch. |

- **Primary Key:** `id`
- **Performance Indexes:**
  - `INDEX jobs_queue_idx (queue)`

---

### Table 34: `failed_jobs`
**Migration File:** `2024_01_01_000034_create_failed_jobs_table.php`  
**Purpose:** Stores failed queue jobs after exhaustion of retry attempts for administrative inspection.

| Column | Type | Nullable / Default | Attributes & Business Rules |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT` | Primary Key. |
| `uuid` | `VARCHAR(191)` | `NOT NULL` | Unique job UUID. |
| `connection` | `TEXT` | `NOT NULL` | Connection driver name (`'database'`). |
| `queue` | `TEXT` | `NOT NULL` | Queue partition name. |
| `payload` | `LONGTEXT` | `NOT NULL` | Serialized job payload. |
| `exception` | `LONGTEXT` | `NOT NULL` | Full stack trace of the unhandled error. |
| `failed_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Failure timestamp. |

- **Primary Key:** `id`
- **Unique Indexes:** `uuid` (`failed_jobs_uuid_unique`)

---

## 3. ALL ELOQUENT MODELS

This section details all 34 Eloquent models for the application. Each specification includes the class namespace, table binding, traits, fillable attributes, native type casts, relationship definitions with exact return types, local query scopes, modern Laravel 11 accessors/mutators (`Illuminate\Database\Eloquent\Casts\Attribute`), and domain helper methods.

---

### Model 1: `App\Models\User`
**Table:** `users`  
**Traits:** `HasFactory`, `Notifiable`, `SoftDeletes`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'uuid',
        'role',
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'company_name',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
        'balance',
        'currency_id',
        'status',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:4',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:json',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'user_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class, 'user_id');
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class, 'user_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'user_id');
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class, 'user_id');
    }

    public function resellerPricing(): HasMany
    {
        return $this->hasMany(ResellerPricing::class, 'user_id');
    }

    public function twoFactorTokens(): HasMany
    {
        return $this->hasMany(TwoFactorToken::class, 'user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    // Scopes
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', 'admin');
    }

    public function scopeResellers(Builder $query): Builder
    {
        return $query->where('role', 'reseller');
    }

    public function scopeClients(Builder $query): Builder
    {
        return $query->where('role', 'client');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    // Accessors & Mutators
    protected function formattedBalance(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->balance, 2)
        );
    }

    // Domain Helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isReseller(): bool
    {
        return $this->role === 'reseller';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_confirmed_at);
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }

    public function deductBalance(float $amount): bool
    {
        if (!$this->hasSufficientBalance($amount)) {
            return false;
        }
        $this->decrement('balance', $amount);
        return true;
    }

    public function creditBalance(float $amount): void
    {
        $this->increment('balance', $amount);
    }
}
```

---

### Model 2: `App\Models\PasswordResetToken`
**Table:** `password_reset_tokens`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
```

---

### Model 3: `App\Models\Session`
**Table:** `sessions`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $table = 'sessions';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
    ];

    protected $casts = [
        'last_activity' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

---

### Model 4: `App\Models\TwoFactorToken`
**Table:** `two_factor_tokens`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorToken extends Model
{
    protected $table = 'two_factor_tokens';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
```

---

### Model 5: `App\Models\Currency`
**Table:** `currencies`  
**Traits:** `HasFactory`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_primary',
        'is_active',
        'decimal_places',
        'symbol_position',
        'last_updated_at',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'decimal_places' => 'integer',
        'last_updated_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'currency_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    public function format(float $amountInUsd): string
    {
        $converted = $amountInUsd * (float) $this->exchange_rate;
        $formatted = number_format($converted, $this->decimal_places);

        return $this->symbol_position === 'before'
            ? $this->symbol . ' ' . $formatted
            : $formatted . ' ' . $this->symbol;
    }
}
```

---

### Model 6: `App\Models\SystemSetting`
**Table:** `system_settings`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'group',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('setting_key', $key)->first();
        if (!$setting) {
            return $default;
        }

        if ($setting->is_encrypted && !empty($setting->setting_value)) {
            try {
                return Crypt::decryptString($setting->setting_value);
            } catch (\Exception $e) {
                return $default;
            }
        }

        return $setting->setting_value ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general', bool $encrypted = false): void
    {
        $val = $encrypted && !empty($value) ? Crypt::encryptString((string) $value) : (string) $value;

        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $val, 'group' => $group, 'is_encrypted' => $encrypted]
        );
    }
}
```

---

### Model 7: `App\Models\RegistrarModule`
**Table:** `registrar_modules`  
**Traits:** `HasFactory`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrarModule extends Model
{
    use HasFactory;

    protected $table = 'registrar_modules';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'is_default',
        'environment',
        'config',
        'balance',
        'currency',
        'last_balance_sync_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'config' => 'encrypted:json',
        'balance' => 'decimal:4',
        'last_balance_sync_at' => 'datetime',
    ];

    public function tlds(): HasMany
    {
        return $this->hasMany(TldPricing::class, 'registrar_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'registrar_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function isSandbox(): bool
    {
        return $this->environment === 'sandbox';
    }
}
```

---

### Model 8: `App\Models\TldPricing`
**Table:** `tld_pricing`  
**Traits:** `HasFactory`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TldPricing extends Model
{
    use HasFactory;

    protected $table = 'tld_pricing';

    protected $fillable = [
        'tld',
        'registrar_id',
        'cost_register',
        'cost_renew',
        'cost_transfer',
        'client_register',
        'client_renew',
        'client_transfer',
        'default_reseller_register',
        'default_reseller_renew',
        'default_reseller_transfer',
        'min_years',
        'max_years',
        'is_active',
        'dns_management_available',
        'email_forwarding_available',
        'id_protection_available',
        'id_protection_cost',
        'epp_code_required',
        'grace_period_days',
        'redemption_period_days',
        'redemption_fee',
    ];

    protected $casts = [
        'cost_register' => 'decimal:4',
        'cost_renew' => 'decimal:4',
        'cost_transfer' => 'decimal:4',
        'client_register' => 'decimal:4',
        'client_renew' => 'decimal:4',
        'client_transfer' => 'decimal:4',
        'default_reseller_register' => 'decimal:4',
        'default_reseller_renew' => 'decimal:4',
        'default_reseller_transfer' => 'decimal:4',
        'id_protection_cost' => 'decimal:4',
        'redemption_fee' => 'decimal:4',
        'min_years' => 'integer',
        'max_years' => 'integer',
        'grace_period_days' => 'integer',
        'redemption_period_days' => 'integer',
        'is_active' => 'boolean',
        'dns_management_available' => 'boolean',
        'email_forwarding_available' => 'boolean',
        'id_protection_available' => 'boolean',
        'epp_code_required' => 'boolean',
    ];

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(RegistrarModule::class, 'registrar_id');
    }

    public function resellerPricing(): HasMany
    {
        return $this->hasMany(ResellerPricing::class, 'tld_pricing_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getPriceForUser(User $user, string $action = 'register'): float
    {
        if ($user->isReseller()) {
            $custom = $this->resellerPricing()->where('user_id', $user->id)->first();
            if ($custom) {
                return match ($action) {
                    'renew' => (float) $custom->renew_price,
                    'transfer' => (float) $custom->transfer_price,
                    default => (float) $custom->register_price,
                };
            }
            return match ($action) {
                'renew' => (float) $this->default_reseller_renew,
                'transfer' => (float) $this->default_reseller_transfer,
                default => (float) $this->default_reseller_register,
            };
        }

        return match ($action) {
            'renew' => (float) $this->client_renew,
            'transfer' => (float) $this->client_transfer,
            default => (float) $this->client_register,
        };
    }
}
```

---

### Model 9: `App\Models\ResellerPricing`
**Table:** `reseller_pricing`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResellerPricing extends Model
{
    protected $table = 'reseller_pricing';

    protected $fillable = [
        'user_id',
        'tld_pricing_id',
        'register_price',
        'renew_price',
        'transfer_price',
    ];

    protected $casts = [
        'register_price' => 'decimal:4',
        'renew_price' => 'decimal:4',
        'transfer_price' => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tld(): BelongsTo
    {
        return $this->belongsTo(TldPricing::class, 'tld_pricing_id');
    }
}
```

---

### Model 10: `App\Models\Contact`
**Table:** `contacts`  
**Traits:** `HasFactory`, `SoftDeletes`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contacts';

    protected $fillable = [
        'user_id',
        'contact_type',
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone_country_code',
        'phone_number',
        'fax',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}"
        );
    }

    protected function formattedPhone(): Attribute
    {
        return Attribute::make(
            get: fn () => "+{$this->phone_country_code}.{$this->phone_number}"
        );
    }
}
```

---

### Model 11: `App\Models\Domain`
**Table:** `domains`  
**Traits:** `HasFactory`, `SoftDeletes`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Domain extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'domains';

    protected $fillable = [
        'uuid',
        'user_id',
        'registrar_id',
        'domain_name',
        'tld',
        'registration_years',
        'status',
        'registrar_order_id',
        'registrar_domain_id',
        'is_locked',
        'is_private',
        'is_auto_renew',
        'nameservers',
        'auth_code',
        'registered_at',
        'expires_at',
        'next_due_date',
        'last_sync_at',
        'registrar_cost',
        'selling_price',
        'registrant_contact_id',
        'admin_contact_id',
        'tech_contact_id',
        'billing_contact_id',
    ];

    protected $casts = [
        'registration_years' => 'integer',
        'is_locked' => 'boolean',
        'is_private' => 'boolean',
        'is_auto_renew' => 'boolean',
        'nameservers' => 'array',
        'auth_code' => 'encrypted',
        'registered_at' => 'datetime',
        'expires_at' => 'datetime',
        'next_due_date' => 'date',
        'last_sync_at' => 'datetime',
        'registrar_cost' => 'decimal:4',
        'selling_price' => 'decimal:4',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Domain $domain) {
            if (empty($domain->uuid)) {
                $domain->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(RegistrarModule::class, 'registrar_id');
    }

    public function registrantContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'registrant_contact_id');
    }

    public function adminContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'admin_contact_id');
    }

    public function techContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'tech_contact_id');
    }

    public function billingContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'billing_contact_id');
    }

    public function dnsRecords(): HasMany
    {
        return $this->hasMany(DnsRecord::class, 'domain_id');
    }

    public function childNameservers(): HasMany
    {
        return $this->hasMany(ChildNameserver::class, 'domain_id');
    }

    public function emailForwards(): HasMany
    {
        return $this->hasMany(EmailForward::class, 'domain_id');
    }

    public function urlForwards(): HasMany
    {
        return $this->hasMany(UrlForward::class, 'domain_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'domain_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringWithinDays(Builder $query, int $days): Builder
    {
        return $query->where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    // Domain Helpers
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function daysUntilExpiration(): int
    {
        return $this->expires_at ? (int) now()->diffInDays($this->expires_at, false) : 0;
    }

    public function canManageDns(): bool
    {
        return $this->status === 'active';
    }
}
```

---

### Model 12: `App\Models\DnsRecord`
**Table:** `dns_records`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DnsRecord extends Model
{
    protected $table = 'dns_records';

    protected $fillable = [
        'domain_id',
        'type',
        'host',
        'value',
        'priority',
        'port',
        'weight',
        'ttl',
        'registrar_record_id',
    ];

    protected $casts = [
        'priority' => 'integer',
        'port' => 'integer',
        'weight' => 'integer',
        'ttl' => 'integer',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', strtoupper($type));
    }
}
```

---

### Model 13: `App\Models\ChildNameserver`
**Table:** `child_nameservers`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildNameserver extends Model
{
    protected $table = 'child_nameservers';

    protected $fillable = [
        'domain_id',
        'hostname',
        'ip_address',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }
}
```

---

### Model 14: `App\Models\EmailForward`
**Table:** `email_forwards`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailForward extends Model
{
    protected $table = 'email_forwards';

    protected $fillable = [
        'domain_id',
        'source_prefix',
        'destination_email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected function sourceEmail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->source_prefix . '@' . $this->domain->domain_name
        );
    }
}
```

---

### Model 15: `App\Models\UrlForward`
**Table:** `url_forwards`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UrlForward extends Model
{
    protected $table = 'url_forwards';

    protected $fillable = [
        'domain_id',
        'source_subdomain',
        'destination_url',
        'redirect_type',
        'page_title',
        'meta_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isMasked(): bool
    {
        return $this->redirect_type === 'masked';
    }
}
```

---

### Model 16: `App\Models\PromoCode`
**Table:** `promo_codes`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    protected $table = 'promo_codes';

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_uses',
        'current_uses',
        'user_usage_limit',
        'valid_from',
        'valid_to',
        'applicable_tlds',
        'applicable_actions',
        'min_order_amount',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'user_usage_limit' => 'integer',
        'applicable_tlds' => 'array',
        'applicable_actions' => 'array',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'promo_code_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
            });
    }

    public function isValidFor(float $subtotal, User $user, ?string $tld = null, ?string $action = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_to && $this->valid_to->isPast()) {
            return false;
        }

        if ($this->max_uses > 0 && $this->current_uses >= $this->max_uses) {
            return false;
        }

        if ($subtotal < (float) $this->min_order_amount) {
            return false;
        }

        if (!empty($this->applicable_tlds) && $tld && !in_array($tld, $this->applicable_tlds, true)) {
            return false;
        }

        if (!empty($this->applicable_actions) && $action && !in_array($action, $this->applicable_actions, true)) {
            return false;
        }

        $userUses = $this->orders()->where('user_id', $user->id)->count();
        if ($userUses >= $this->user_usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            return round(($subtotal * (float) $this->discount_value) / 100, 2);
        }
        return min($subtotal, (float) $this->discount_value);
    }
}
```

---

### Model 17: `App\Models\Order`
**Table:** `orders`  
**Traits:** `HasFactory`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'promo_code_id',
        'total_amount',
        'discount_amount',
        'currency',
        'status',
        'source',
        'ip_address',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:4',
        'discount_amount' => 'decimal:4',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Order $order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(date('Ymd')) . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'order_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
```

---

### Model 18: `App\Models\OrderItem`
**Table:** `order_items`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'domain_id',
        'item_type',
        'domain_name',
        'years',
        'unit_price',
        'total_price',
        'registrar_id',
        'registrar_cost',
        'registrar_payload',
        'registrar_response',
        'status',
    ];

    protected $casts = [
        'years' => 'integer',
        'unit_price' => 'decimal:4',
        'total_price' => 'decimal:4',
        'registrar_cost' => 'decimal:4',
        'registrar_payload' => 'array',
        'registrar_response' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(RegistrarModule::class, 'registrar_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
```

---

### Model 19: `App\Models\Invoice`
**Table:** `invoices`  
**Traits:** `HasFactory`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'uuid',
        'invoice_number',
        'user_id',
        'order_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'balance_due',
        'currency',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'discount_amount' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'balance_due' => 'decimal:4',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = (string) Str::uuid();
            }
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . date('Y') . '-' . strtoupper(Str::random(7));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'invoice_id');
    }

    // Scopes
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'unpaid')->where('due_date', '<', now());
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function markAsPaid(string $gateway, ?string $transactionId = null): void
    {
        $this->update([
            'status' => 'paid',
            'balance_due' => 0.0000,
            'paid_at' => now(),
            'payment_method' => $gateway,
        ]);
    }
}
```

---

### Model 20: `App\Models\InvoiceItem`
**Table:** `invoice_items`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:4',
        'total_price' => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
```

---

### Model 21: `App\Models\Transaction`
**Table:** `transactions`  
**Traits:** `HasFactory`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'uuid',
        'transaction_number',
        'user_id',
        'invoice_id',
        'gateway',
        'gateway_transaction_id',
        'type',
        'amount',
        'currency',
        'exchange_rate',
        'amount_in_usd',
        'fee',
        'status',
        'payment_details',
        'verified_by_admin_id',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'amount_in_usd' => 'decimal:4',
        'fee' => 'decimal:4',
        'payment_details' => 'array',
        'verified_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Transaction $transaction) {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = 'TXN-' . strtoupper(date('Ymd')) . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function verifiedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_admin_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('type', 'debit');
    }
}
```

---

### Model 22: `App\Models\ApiKey`
**Table:** `api_keys`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $table = 'api_keys';

    protected $fillable = [
        'user_id',
        'key_name',
        'api_key',
        'api_secret',
        'ip_whitelist',
        'allowed_endpoints',
        'status',
        'last_used_at',
    ];

    protected $casts = [
        'api_secret' => 'encrypted',
        'ip_whitelist' => 'array',
        'allowed_endpoints' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isIpWhitelisted(string $ip): bool
    {
        if (empty($this->ip_whitelist)) {
            return true;
        }
        return in_array($ip, $this->ip_whitelist, true);
    }

    public static function generateForUser(User $user, string $name, ?array $ips = null): array
    {
        $rawSecret = 'sk_' . Str::random(40);
        $rawKey = 'pk_' . Str::random(32);

        $apiKey = static::create([
            'user_id' => $user->id,
            'key_name' => $name,
            'api_key' => hash('sha256', $rawKey),
            'api_secret' => $rawSecret,
            'ip_whitelist' => $ips,
            'status' => 'active',
        ]);

        return [
            'model' => $apiKey,
            'public_key' => $rawKey,
            'secret_key' => $rawSecret,
        ];
    }
}
```

---

### Model 23: `App\Models\Webhook`
**Table:** `webhooks`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    protected $table = 'webhooks';

    protected $fillable = [
        'user_id',
        'target_url',
        'secret_key',
        'subscribed_events',
        'is_active',
        'failure_count',
        'last_triggered_at',
    ];

    protected $casts = [
        'subscribed_events' => 'array',
        'is_active' => 'boolean',
        'failure_count' => 'integer',
        'last_triggered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class, 'webhook_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isSubscribedTo(string $event): bool
    {
        return in_array($event, $this->subscribed_events ?? [], true);
    }
}
```

---

### Model 24: `App\Models\WebhookLog`
**Table:** `webhook_logs`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    protected $table = 'webhook_logs';

    protected $fillable = [
        'webhook_id',
        'event_name',
        'payload',
        'response_code',
        'response_body',
        'execution_time_ms',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_code' => 'integer',
        'execution_time_ms' => 'integer',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class, 'webhook_id');
    }
}
```

---

### Model 25: `App\Models\SupportTicket`
**Table:** `support_tickets`  
**Traits:** `HasFactory`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory;

    protected $table = 'support_tickets';

    protected $fillable = [
        'uuid',
        'ticket_number',
        'user_id',
        'domain_id',
        'department',
        'priority',
        'subject',
        'status',
        'last_reply_at',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (SupportTicket $ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-' . date('Ym') . '-' . strtoupper(Str::random(5));
            }
            if (empty($ticket->last_reply_at)) {
                $ticket->last_reply_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'support_ticket_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'customer_reply', 'in_progress']);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }
}
```

---

### Model 26: `App\Models\SupportTicketReply`
**Table:** `support_ticket_replies`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketReply extends Model
{
    protected $table = 'support_ticket_replies';

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'is_staff_reply',
        'message',
        'attachments',
    ];

    protected $casts = [
        'is_staff_reply' => 'boolean',
        'attachments' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

---

### Model 27: `App\Models\EmailTemplate`
**Table:** `email_templates`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    protected $fillable = [
        'template_key',
        'name',
        'subject',
        'body_html',
        'body_plain',
        'available_placeholders',
        'is_active',
    ];

    protected $casts = [
        'available_placeholders' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function render(array $data): array
    {
        $subject = $this->subject;
        $bodyHtml = $this->body_html;
        $bodyPlain = $this->body_plain ?? strip_tags($this->body_html);

        foreach ($data as $key => $val) {
            $token = '{' . $key . '}';
            $subject = str_replace($token, (string) $val, $subject);
            $bodyHtml = str_replace($token, (string) $val, $bodyHtml);
            $bodyPlain = str_replace($token, (string) $val, $bodyPlain);
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_plain' => $bodyPlain,
        ];
    }
}
```

---

### Model 28: `App\Models\EmailLog`
**Table:** `email_logs`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $table = 'email_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'recipient_email',
        'template_key',
        'subject',
        'body',
        'status',
        'error_message',
        'sent_at',
        'created_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

---

### Model 29: `App\Models\ActivityLog`
**Table:** `activity_logs`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'log_name',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeLogName(Builder $query, string $name): Builder
    {
        return $query->where('log_name', $name);
    }
}
```

---

### Model 30: `App\Models\LoginHistory`
**Table:** `login_histories`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    protected $table = 'login_histories';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'status',
        'failure_reason',
        'login_at',
    ];

    protected $casts = [
        'login_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }
}
```

---

### Model 31: `App\Models\Announcement`
**Table:** `announcements`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'target_audience',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Announcement $announcement) {
            if (empty($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title) . '-' . Str::random(5);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('published_at', '<=', now());
    }

    public function scopeForAudience(Builder $query, string $role): Builder
    {
        return $query->where(function (Builder $q) use ($role) {
            $q->where('target_audience', 'all')
              ->orWhere('target_audience', $role === 'reseller' ? 'resellers' : 'clients');
        });
    }
}
```

---

### Model 32: `App\Models\CronLog`
**Table:** `cron_logs`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CronLog extends Model
{
    protected $table = 'cron_logs';
    public $timestamps = false;

    protected $fillable = [
        'command_name',
        'status',
        'output',
        'duration_seconds',
        'executed_at',
        'completed_at',
        'created_at',
    ];

    protected $casts = [
        'duration_seconds' => 'decimal:2',
        'executed_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }
}
```

---

### Model 33: `App\Models\Job`
**Table:** `jobs`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'jobs';
    public $timestamps = false;

    protected $fillable = [
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'reserved_at' => 'integer',
        'available_at' => 'integer',
        'created_at' => 'integer',
    ];
}
```

---

### Model 34: `App\Models\FailedJob`
**Table:** `failed_jobs`  
**Traits:** None

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedJob extends Model
{
    protected $table = 'failed_jobs';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'connection',
        'queue',
        'payload',
        'exception',
        'failed_at',
    ];

    protected $casts = [
        'failed_at' => 'datetime',
    ];
}
```

---

## 4. ARCHITECTURAL VALIDATION & CROSS-REFERENCE SUMMARY

| Component | Target Requirement | Status in Blueprint Part 1 |
| :--- | :--- | :--- |
| **Project Directory Tree** | Every migration, model, controller, view, service, route, command | **100% Exhaustive** (Over 200 explicitly mapped file paths) |
| **Database Migrations** | 34 tables with all columns, types, defaults, foreign keys, indexes | **100% Exhaustive** (34 Tables defined with MySQL types & indexes) |
| **Eloquent Models** | 34 models with fillable, casts, relations, scopes, accessors, methods | **100% Exhaustive** (All 34 models fully declared with PHP code blocks) |
| **Shared Hosting Support** | Database queues, file cache/sessions, no Redis/Node.js dependencies | **Verified** |
| **Dual Currency Support** | USD base ledger with dynamic/manual BDT conversion | **Verified** |
| **Multi-Registrar Drivers** | Namecheap, Spaceship, Dynadot, AtakDomain, NameSilo | **Verified** |
| **Multi-Gateway Support** | Manual, bKash, Stripe, Binance Pay | **Verified** |
| **Exclusions Adhered To** | No Sub-Resellers, No Domain Marketplace, No Affiliate System | **Verified** |
