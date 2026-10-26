# DOMAIN RESELLER PANEL — ARCHITECTURAL BLUEPRINT (PART 3)
**System Framework:** Laravel 11 LTS + Blade + Tailwind CSS + Alpine.js + MySQL  
**Target Environment:** Shared Hosting (cPanel / DirectAdmin, database queues, file cache, no Redis, no Node daemon)  
**Currencies:** USD (Base Ledger) with Dynamic BDT Conversion  
**Scope:** Services, Blade Views, Blade Components, Queue Jobs, Events & Listeners, Notifications, Artisan Commands, Configuration Files, and Core Module Interfaces.

---

## 1. ALL SERVICE CLASSES

Every service class is strictly typed (PHP 8.3), adheres to Single Responsibility Principles, uses dependency injection via Laravel's Service Container, and encapsulates business logic away from Controllers and Artisan Commands.

```
app/
└── Services/
    ├── Domain/
    │   └── DomainService.php
    ├── Dns/
    │   └── DnsService.php
    ├── Nameserver/
    │   └── NameserverService.php
    ├── Whois/
    │   └── WhoisService.php
    ├── Registrar/
    │   ├── RegistrarManager.php
    │   └── Modules/
    │       ├── NamecheapModule.php
    │       ├── SpaceshipModule.php
    │       ├── DynadotModule.php
    │       ├── AtakDomainModule.php
    │       └── NamesiloModule.php
    ├── Billing/
    │   └── BillingService.php
    ├── Order/
    │   └── OrderService.php
    ├── Payment/
    │   ├── PaymentService.php
    │   └── Gateways/
    │       ├── ManualPaymentGateway.php
    │       ├── BkashGateway.php
    │       ├── StripeGateway.php
    │       └── BinanceGateway.php
    ├── Wallet/
    │   └── WalletService.php
    ├── Api/
    │   └── ResellerApiService.php
    ├── Webhook/
    │   └── WebhookService.php
    ├── Email/
    │   └── EmailService.php
    ├── Support/
    │   └── SupportService.php
    ├── Report/
    │   └── ReportService.php
    ├── Currency/
    │   └── CurrencyService.php
    └── ActivityLog/
        └── ActivityLogService.php
```

---

### 1.1 `DomainService`
- **Class:** `App\Services\Domain\DomainService`
- **File Path:** `app/Services/Domain/DomainService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected RegistrarManager $registrarManager,
      protected DnsService $dnsService,
      protected NameserverService $nameserverService,
      protected WhoisService $whoisService,
      protected OrderService $orderService,
      protected BillingService $billingService,
      protected ActivityLogService $activityLogService
  ) {}
  ```
- **Public Methods:**

#### `searchDomain(string $sld, array $tlds = []): array`
- **Parameters:** `string $sld` (Second-level domain, e.g. "mybrand"), `array $tlds` (Optional list of extensions without dot, e.g. `['com', 'net', 'org', 'xyz']`. Defaults to all active system TLDs if empty).
- **Return Type:** `array` (Array of `App\DTOs\DomainSearchResultDTO`).
- **Description:** Sanitizes the input SLD (removes invalid characters, converts to punycode/IDN if needed), fetches pricing and configured registrar for each requested TLD, executes parallel or sequential availability lookups via `RegistrarManager`, applies client/reseller markup pricing, and returns availability status, premium status, and tiered pricing.

#### `checkAvailability(string $domain): DomainAvailabilityDTO`
- **Parameters:** `string $domain` (FQDN, e.g. "example.com").
- **Return Type:** `App\DTOs\DomainAvailabilityDTO` (`domain`, `is_available`, `is_premium`, `registration_price`, `currency`, `registrar`).
- **Description:** Extracts SLD and TLD, resolves assigned registrar driver from `tlds` table, queries the registrar API, and maps raw API response to a normalized availability DTO. Throws `App\Exceptions\UnsupportedTldException` if TLD is inactive.

#### `checkBulkAvailability(array $domains): Collection<DomainAvailabilityDTO>`
- **Parameters:** `array $domains` (Array of up to 50 FQDNs).
- **Return Type:** `Illuminate\Support\Collection<DomainAvailabilityDTO>`
- **Description:** Batches domain queries grouped by registrar to reduce API roundtrips. For registrars supporting bulk checks (e.g. Dynadot, Namesilo), executes bulk endpoints; for others, executes sequential queries with fallback handling.

#### `registerDomain(User $user, string $domain, int $years, array $nameservers, array $contactDetails, bool $idProtection = true): Domain`
- **Parameters:** `User $user`, `string $domain`, `int $years` (1 to 10), `array $nameservers` (List of 2-5 nameserver hostnames), `array $contactDetails` (Registrant, Admin, Tech, Billing address schemas), `bool $idProtection` (WHOIS privacy enabled).
- **Return Type:** `App\Models\Domain`
- **Description:** Verifies user authorization and wallet/invoice payment status. Calls `RegistrarManager::getModuleForDomain($domain)->register()`. Upon success, records domain in `domains` table, saves initial nameservers and contact records, assigns default DNS records if hosted nameservers are used, dispatches `DomainRegisteredEvent`, and logs activity. Throws `DomainRegistrationException` on registrar API rejection.

#### `renewDomain(Domain $domain, int $years): Domain`
- **Parameters:** `Domain $domain`, `int $years` (1 to 10).
- **Return Type:** `App\Models\Domain`
- **Description:** Validates domain status (must not be 'Transferred Away' or 'Cancelled'). Checks current expiration date; communicates with registrar to renew for specified years. Updates `expires_at`, `status = 'active'`, updates registration order status, dispatches `DomainRenewedEvent`, and writes audit log.

#### `transferDomain(User $user, string $domain, string $eppCode, int $years, array $contactDetails): Domain`
- **Parameters:** `User $user`, `string $domain`, `string $eppCode`, `int $years`, `array $contactDetails`.
- **Return Type:** `App\Models\Domain`
- **Description:** Validates EPP / Auth code format. Calls registrar transfer initiate endpoint. Creates domain record with `status = 'pending_transfer'`, stores encrypted EPP code, dispatches `DomainTransferInitiatedEvent`.

#### `getDomainInfo(Domain $domain): DomainInfoDTO`
- **Parameters:** `Domain $domain`.
- **Return Type:** `App\DTOs\DomainInfoDTO`
- **Description:** Queries the upstream registrar for live domain details: registration date, expiration date, lock status, privacy status, child nameservers, and registrar status codes (e.g. `clientTransferProhibited`).

#### `syncDomain(Domain $domain): bool`
- **Parameters:** `Domain $domain`.
- **Return Type:** `bool`
- **Description:** Fetches live upstream registrar status via `getDomainInfo()` and reconciles database values (`expires_at`, `is_locked`, `id_protection`, `status`). If expiration date changed or status moved from pending to active, updates timestamps and dispatches status update notification.

#### `syncAllDomains(?int $registrarId = null): SyncSummaryDTO`
- **Parameters:** `?int $registrarId` (Optional registrar filter).
- **Return Type:** `App\DTOs\SyncSummaryDTO` (`total_checked`, `synced`, `failed`, `errors_array`).
- **Description:** Iterates through active domains in chunks of 100 via database queue jobs (`SyncDomainStatusJob`), avoiding HTTP timeouts in shared hosting environments.

#### `getEppCode(Domain $domain): string`
- **Parameters:** `Domain $domain`.
- **Return Type:** `string`
- **Description:** Checks user permissions (Admin or Domain Owner). Invokes registrar API to fetch the current EPP/transfer authorization code or trigger registrar email to the registrant contact.

#### `setLock(Domain $domain, bool $lock): bool`
- **Parameters:** `Domain $domain`, `bool $lock` (`true` to lock, `false` to unlock).
- **Return Type:** `bool`
- **Description:** Updates registrar transfer lock (`clientTransferProhibited`). On registrar confirmation, updates `domains.is_locked` and logs the security event.

#### `setAutoRenew(Domain $domain, bool $autoRenew): bool`
- **Parameters:** `Domain $domain`, `bool $autoRenew`.
- **Return Type:** `bool`
- **Description:** Toggles the internal `auto_renew` flag in `domains` table. If auto-renewal is handled upstream at registrar, updates registrar API setting accordingly.

#### `setIdProtection(Domain $domain, bool $enable): bool`
- **Parameters:** `Domain $domain`, `bool $enable`.
- **Return Type:** `bool`
- **Description:** Checks if TLD supports privacy protection. Calls registrar module `setIdProtection()`. Updates `domains.id_protection` column.

---

### 1.2 `DnsService`
- **Class:** `App\Services\Dns\DnsService`
- **File Path:** `app/Services/Dns/DnsService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected RegistrarManager $registrarManager,
      protected ActivityLogService $activityLogService
  ) {}
  ```
- **Public Methods:**
  - `getRecords(Domain $domain): Collection<DnsRecordDTO>`: Returns list of DNS records from the registrar API or local DNS manager.
  - `addRecord(Domain $domain, array $recordData): DnsRecordDTO`: Validates record type (`A`, `AAAA`, `CNAME`, `MX`, `TXT`, `SRV`, `CAA`), hostname, value, priority, and TTL. Transmits record to upstream registrar and creates audit log.
  - `updateRecord(Domain $domain, string|int $recordId, array $recordData): DnsRecordDTO`: Updates existing DNS record at registrar API.
  - `deleteRecord(Domain $domain, string|int $recordId): bool`: Removes record by upstream reference ID.
  - `bulkUpdateRecords(Domain $domain, array $records): bool`: Completely overwrites the zone with a validated set of records.
  - `importZone(Domain $domain, string $zoneFileContent): int`: Parses standard BIND RFC 1035 zone file, validates syntax, extracts resource records, and replaces DNS entries. Returns count of imported records.
  - `exportZone(Domain $domain): string`: Formats current DNS records into RFC 1035 standard BIND zone file format with SOA and NS headers.
  - `applyTemplate(Domain $domain, DnsTemplate $template): bool`: Replaces or merges records from predefined templates (e.g., Google Workspace, Microsoft 365, Cloudflare).

---

### 1.3 `NameserverService`
- **Class:** `App\Services\Nameserver\NameserverService`
- **File Path:** `app/Services/Nameserver/NameserverService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected RegistrarManager $registrarManager,
      protected ActivityLogService $activityLogService
  ) {}
  ```
- **Public Methods:**
  - `getNameservers(Domain $domain): array`: Returns array of 2 to 5 nameserver hostnames currently active at registrar.
  - `setNameservers(Domain $domain, array $nameservers): bool`: Validates hostname syntax (FQDN format). Communicates new nameservers to registrar module. Updates `domains.nameservers` JSON column.
  - `createChildNs(Domain $domain, string $hostname, string $ipAddress): ChildNameserver`: Registers private child nameserver (glue record) at the registrar (e.g., `ns1.example.com` -> `192.0.2.1`). Stores record in `child_nameservers` table.
  - `updateChildNs(Domain $domain, string $hostname, string $newIpAddress): bool`: Updates IP address of registered glue record at registrar.
  - `deleteChildNs(Domain $domain, string $hostname): bool`: Removes glue record from registrar registry and deletes database entry.

---

### 1.4 `WhoisService`
- **Class:** `App\Services\Whois\WhoisService`
- **File Path:** `app/Services/Whois/WhoisService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected RegistrarManager $registrarManager,
      protected ActivityLogService $activityLogService
  ) {}
  ```
- **Public Methods:**
  - `getWhoisInfo(Domain $domain): WhoisContactDTO`: Retrieves structured contact information (Registrant, Tech, Admin, Billing) from registrar.
  - `updateWhoisInfo(Domain $domain, array $contactDetails): bool`: Validates postal addresses, international phone format (+E.164), email syntax, and ISO country codes. Transmits updated contact details to registrar.

---

### 1.5 `RegistrarManager`
- **Class:** `App\Services\Registrar\RegistrarManager`
- **File Path:** `app/Services/Registrar/RegistrarManager.php`
- **Extends:** `Illuminate\Support\Manager`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected Application $app
  ) {
      parent::__construct($app);
  }
  ```
- **Public Methods:**
  - `getModule(string $name): RegistrarModuleInterface`: Resolves driver instance by slug (`namecheap`, `spaceship`, `dynadot`, `atakdomain`, `namesilo`).
  - `getModuleForDomain(string $domain): RegistrarModuleInterface`: Looks up TLD of domain from `tlds` table, resolves assigned default registrar, and returns active driver instance.
  - `getActiveModules(): Collection<RegistrarModuleInterface>`: Returns all configured and enabled registrar instances.
  - `getDefaultDriver(): string`: Reads default registrar fallback from `config('registrars.default')`.
  - Module Factory Methods:
    - `createNamecheapDriver(): RegistrarModuleInterface`
    - `createSpaceshipDriver(): RegistrarModuleInterface`
    - `createDynadotDriver(): RegistrarModuleInterface`
    - `createAtakDomainDriver(): RegistrarModuleInterface`
    - `createNamesiloDriver(): RegistrarModuleInterface`

---

### 1.6 `BillingService`
- **Class:** `App\Services\Billing\BillingService`
- **File Path:** `app/Services/Billing/BillingService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected CurrencyService $currencyService,
      protected ActivityLogService $activityLogService
  ) {}
  ```
- **Public Methods:**
  - `createInvoice(User $user, array $items, ?Carbon $dueDate = null, string $currency = 'USD'): Invoice`: Calculates subtotal, applies tax if applicable, calculates discount credits, generates unique invoice number (`INV-YYYYMM-XXXXX`), persists `invoices` and `invoice_items` records.
  - `payInvoice(Invoice $invoice, string $gateway, ?string $transactionId = null, array $gatewayResponse = []): PaymentTransaction`: Sets invoice status to `paid`, sets `paid_at = now()`, creates `payment_transactions` record, executes associated pending order actions via `OrderService::processOrder()`, dispatches `InvoicePaidEvent`.
  - `cancelInvoice(Invoice $invoice, string $reason = ''): bool`: Marks unpaid invoice as `cancelled`, unlocks attached order if necessary.
  - `voidInvoice(Invoice $invoice, string $reason = ''): bool`: Voids invoice for accounting audits.
  - `applyCredit(Invoice $invoice, float $amount): bool`: Deducts user wallet balance or credit balance and applies to reduce unpaid invoice balance.
  - `calculateTax(float $subtotal, ?User $user = null): float`: Computes tax according to country/region tax rules.
  - `convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float`: Invokes `CurrencyService::convert()`.
  - `generateInvoiceNumber(): string`: Generates atomic sequential invoice reference with mutex locking.
  - `getInvoicePdf(Invoice $invoice): Response`: Generates binary PDF stream using lightweight HTML-to-PDF engine suitable for shared hosting (e.g. `barryvdh/laravel-dompdf`).

---

### 1.7 `OrderService`
- **Class:** `App\Services\Order\OrderService`
- **File Path:** `app/Services/Order/OrderService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected DomainService $domainService,
      protected BillingService $billingService,
      protected WalletService $walletService,
      protected ActivityLogService $activityLogService
  ) {}
  ```
- **Public Methods:**
  - `createOrder(User $user, array $cartItems, string $paymentMethod = 'wallet'): Order`: Validates cart item availability and current pricing. Generates `orders` and `order_items` records. Generates an attached unpaid invoice via `BillingService`.
  - `processOrder(Order $order): bool`: Triggered after payment verification. Iterates through order items (Domain Registration, Renewal, Transfer), delegates each action to `DomainService`. Handles partial failure with rollbacks or error ticketing. Marks order as `completed` or `partially_completed`.
  - `cancelOrder(Order $order, string $reason = ''): bool`: Cancels pending order, voids attached unpaid invoice.
  - `processAutoRenewals(): AutoRenewalSummaryDTO`: Scans domains expiring within configured renewal window (e.g. 7 days) having `auto_renew = true`. Attempts wallet balance deduction; if funded, renews domain automatically and sends renewal notification.

---

### 1.8 `PaymentService` & Gateways
- **Class:** `App\Services\Payment\PaymentService`
- **File Path:** `app/Services/Payment/PaymentService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected BillingService $billingService,
      protected WalletService $walletService,
      protected ActivityLogService $activityLogService
  ) {}
  ```
- **Public Methods:**
  - `processPayment(Invoice $invoice, string $gatewayName, array $payload): PaymentRedirectDTO|PaymentResultDTO`: Instantiates requested gateway, initiates checkout session or prepares payment form redirect.
  - `verifyPayment(string $gatewayName, array $callbackData): PaymentResultDTO`: Validates gateway signature, verifies payment state with gateway API, invokes `BillingService::payInvoice()` on success.
  - `refundPayment(PaymentTransaction $transaction, float $amount, string $reason = ''): bool`: Contacts gateway API to initiate full or partial refund. Updates transaction status and reverses ledger.
  - `getGateway(string $name): PaymentGatewayInterface`: Resolves concrete gateway driver.
  - `getEnabledGateways(): Collection<PaymentGatewayInterface>`: Returns list of enabled payment gateways.

#### Concrete Gateway Implementations:
1. **`ManualPaymentGateway` (`app/Services/Payment/Gateways/ManualPaymentGateway.php`)**:
   - Handles manual Bank Transfer, bKash Personal Send Money, and Nagad Personal.
   - Accepts user-submitted transaction ID, sender phone/account, payment proof screenshot.
   - Flags invoice status as `payment_review` awaiting admin verification.
2. **`BkashGateway` (`app/Services/Payment/Gateways/BkashGateway.php`)**:
   - Implements bKash Merchant Checkout API (Grant Token, Create Payment, Execute Payment, Query Payment, Refund).
   - Converts invoice USD amount to BDT using live conversion rate.
3. **`StripeGateway` (`app/Services/Payment/Gateways/StripeGateway.php`)**:
   - Implements Stripe Checkout Session / Payment Intents API.
   - Handles webhook callbacks (`payment_intent.succeeded`, `checkout.session.completed`).
4. **`BinanceGateway` (`app/Services/Payment/Gateways/BinanceGateway.php`)**:
   - Implements Binance Pay API (Order Creation, Webhook Signature Verification with Public Key, Order Query).
   - Accepts crypto payments (USDT, BUSD, BTC) with zero chargeback risk.

---

### 1.9 `WalletService`
- **Class:** `App\Services\Wallet\WalletService`
- **File Path:** `app/Services/Wallet/WalletService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected BillingService $billingService,
      protected ActivityLogService $activityLogService
  ) {}
  ```
- **Public Methods:**
  - `getBalance(User $user, string $currency = 'USD'): float`: Returns current ledger balance in USD or converted to BDT.
  - `addFunds(User $user, float $amount, string $currency, string $source, ?string $reference = null, ?string $description = null): WalletTransaction`: Atomically credits user wallet (`wallets.balance`), creates `wallet_transactions` record with running balance snapshot, dispatches `WalletCreditedEvent`.
  - `deductFunds(User $user, float $amount, string $currency, string $reason, ?string $reference = null): WalletTransaction`: Checks sufficiency; atomically decrements user balance, records debit transaction, dispatches `WalletDebitedEvent`. Throws `InsufficientBalanceException` if balance is deficient.
  - `getHistory(User $user, int $perPage = 15): LengthAwarePaginator`: Returns paginated user wallet transactions.
  - `hasSufficientBalance(User $user, float $amountInUsd): bool`: Returns true if user wallet balance >= required amount.

---

### 1.10 `ResellerApiService`
- **Class:** `App\Services\Api\ResellerApiService`
- **File Path:** `app/Services/Api/ResellerApiService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected ActivityLogService $activityLogService
  ) {}
  ```
- **Public Methods:**
  - `validateApiKey(string $apiKey): ?ResellerApiKey`: Compares SHA-256 hashed API token against `reseller_api_keys.key_hash`. Validates status is `active`.
  - `checkPermission(ResellerApiKey $apiKey, string $ability): bool`: Verifies ability exists in `reseller_api_keys.permissions` array.
  - `checkIpWhitelist(ResellerApiKey $apiKey, string $clientIp): bool`: Checks if client IP matches whitelisted IP ranges/CIDRs. Returns true if IP whitelist is empty.
  - `logApiCall(ResellerApiKey $apiKey, Request $request, Response $response, float $executionTimeMs): ApiLog`: Writes asynchronous API access record into `api_logs` (endpoint, HTTP method, client IP, payload, response code, execution time).
  - `checkRateLimit(ResellerApiKey $apiKey): bool`: Validates request rate using Laravel database cache rate limiter (e.g. 60 requests/minute).

---

### 1.11 `WebhookService`
- **Class:** `App\Services\Webhook\WebhookService`
- **File Path:** `app/Services/Webhook/WebhookService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected ActivityLogService $activityLogService
  ) {}
  ```
- **Public Methods:**
  - `dispatch(string $event, array $payload, ?User $user = null): void`: Finds all active webhook subscriptions listening for `$event` (e.g. `domain.registered`, `domain.renewed`). Pushes `SendWebhookDeliveryJob` onto database queue.
  - `sendWebhook(WebhookEndpoint $endpoint, string $event, array $payload): bool`: Constructs JSON payload, signs request with HMAC-SHA256 using endpoint secret, executes HTTP POST request with 10-second timeout, logs response status.
  - `retryFailed(int $maxRetries = 3): int`: Retries deliveries with status `failed` under retry limit.

---

### 1.12 `EmailService`
- **Class:** `App\Services\Email\EmailService`
- **File Path:** `app/Services/Email/EmailService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected Mailer $mailer
  ) {}
  ```
- **Public Methods:**
  - `sendTemplateEmail(string $templateCode, User|string $recipient, array $data = [], ?string $subject = null): bool`: Fetches template from `email_templates` table, parses shortcodes/merge tags (e.g. `{domain_name}`, `{invoice_id}`, `{amount}`), queues email via `SendEmailNotificationJob`.
  - `getTemplate(string $templateCode): EmailTemplate`: Retrieves template model by code.
  - `renderTemplate(EmailTemplate $template, array $data): array`: Replaces placeholder tags with values and returns `['subject' => ..., 'body' => ...]`.
  - `logEmail(string $recipient, string $subject, string $body, string $status, ?string $errorMessage = null): EmailLog`: Saves copy in `email_logs` table for administrative audit and debugging.
  - `retryFailed(int $limit = 50): int`: Resends failed email queue items.

---

### 1.13 `SupportService`
- **Class:** `App\Services\Support\SupportService`
- **File Path:** `app/Services/Support/SupportService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected ActivityLogService $activityLogService,
      protected EmailService $emailService
  ) {}
  ```
- **Public Methods:**
  - `createTicket(User $user, array $data, array $attachments = []): Ticket`: Generates ticket with ticket number (`TICK-XXXXXX`), priority (`low`, `medium`, `high`, `urgent`), department (`billing`, `domains`, `technical`), and stores file attachments.
  - `replyTicket(Ticket $ticket, User $user, string $message, array $attachments = [], bool $isStaffReply = false): TicketReply`: Appends reply, updates ticket status (`answered` if staff, `customer-reply` if user), sets `last_reply_at = now()`, sends notification email.
  - `closeTicket(Ticket $ticket, ?User $closedBy = null): bool`: Updates ticket status to `closed`.
  - `assignTicket(Ticket $ticket, User $staffUser): bool`: Sets `assigned_to = $staffUser->id`.
  - `changePriority(Ticket $ticket, string $priority): bool`: Updates ticket priority.

---

### 1.14 `ReportService`
- **Class:** `App\Services\Report\ReportService`
- **File Path:** `app/Services/Report/ReportService.php`
- **Public Methods:**
  - `revenueReport(Carbon $startDate, Carbon $endDate, string $groupBy = 'day'): array`: Aggregates invoice payments, net revenue, tax collected, grouped by day/week/month.
  - `domainReport(Carbon $startDate, Carbon $endDate): array`: Returns registration, renewal, and transfer counts, top active TLDs, and churn metrics.
  - `clientReport(Carbon $startDate, Carbon $endDate): array`: New client acquisition count, top spending clients, active wallet balances.
  - `resellerReport(Carbon $startDate, Carbon $endDate): array`: API request volumes, reseller gross volume, discount margins, API failure rates.
  - `tldReport(Carbon $startDate, Carbon $endDate): array`: Profit margins per TLD (selling price minus registrar cost).
  - `paymentReport(Carbon $startDate, Carbon $endDate, ?string $gateway = null): array`: Payment breakdown by gateway (Stripe vs bKash vs Binance vs Manual).
  - `exportCsv(string $reportType, array $data): BinaryFileResponse`: Formats metrics into downloadable CSV stream.
  - `exportPdf(string $reportType, array $data): Response`: Generates printable PDF summary.

---

### 1.15 `CurrencyService`
- **Class:** `App\Services\Currency\CurrencyService`
- **File Path:** `app/Services/Currency/CurrencyService.php`
- **Constructor Dependencies:**
  ```php
  public function __construct(
      protected CacheManager $cache
  ) {}
  ```
- **Public Methods:**
  - `convert(float $amount, string $from = 'USD', string $to = 'BDT'): float`: Applies exchange rate with optional administrative conversion margin percentage.
  - `getRate(string $from = 'USD', string $to = 'BDT'): float`: Returns current exchange rate from database or cache.
  - `updateRates(array $manualRates = []): bool`: Updates rates in `currencies` table and flushes currency cache.
  - `fetchLiveRates(): array`: Fetches open exchange rates API over HTTP; falls back to manual static rates if unreachable.
  - `formatPrice(float $amount, string $currency = 'USD'): string`: Formats money with symbol (e.g. `$12.50` or `৳1,475.00 BDT`).

---

### 1.16 `ActivityLogService`
- **Class:** `App\Services\ActivityLog\ActivityLogService`
- **File Path:** `app/Services/ActivityLog/ActivityLogService.php`
- **Public Methods:**
  - `log(string $action, string $description, ?Model $subject = null, ?User $causer = null, array $properties = []): ActivityLog`: Saves audit entry into `activity_logs` table (action, description, subject type/id, causer type/id, IP address, user agent, old/new properties JSON).
  - `getUserLogs(User $user, int $perPage = 20): LengthAwarePaginator`: Retrieves user-facing activity history.
  - `getAdminLogs(int $perPage = 25, array $filters = []): LengthAwarePaginator`: Retrieves comprehensive audit trail with action/causer filtering.
  - `cleanOldLogs(int $daysToKeep = 90): int`: Prunes old logs past retention window.

---

## 2. ALL BLADE VIEWS & BLADE COMPONENTS

All views follow Blade best practices, Tailwind CSS 3.x utility classes, and Alpine.js 3.x for client-side interactivity.

```
resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── admin.blade.php
│   ├── client.blade.php
│   ├── reseller.blade.php
│   ├── guest.blade.php
│   └── email.blade.php
├── components/
│   ├── alert.blade.php
│   ├── badge.blade.php
│   ├── button.blade.php
│   ├── card.blade.php
│   ├── datatable.blade.php
│   ├── modal.blade.php
│   ├── dropdown.blade.php
│   ├── domain-status-badge.blade.php
│   ├── pagination.blade.php
│   ├── search-bar.blade.php
│   ├── sidebar.blade.php
│   ├── stat-card.blade.php
│   ├── chart.blade.php
│   ├── empty-state.blade.php
│   ├── file-upload.blade.php
│   ├── price-display.blade.php
│   ├── currency-selector.blade.php
│   ├── tld-badge.blade.php
│   └── copy-to-clipboard.blade.php
├── auth/
│   ├── login.blade.php
│   ├── register.blade.php
│   ├── forgot-password.blade.php
│   ├── reset-password.blade.php
│   ├── verify-email.blade.php
│   ├── two-factor-challenge.blade.php
│   └── confirm-password.blade.php
├── admin/
│   ├── dashboard.blade.php
│   ├── domains/
│   │   ├── index.blade.php
│   │   ├── show.blade.php
│   │   ├── dns.blade.php
│   │   ├── nameservers.blade.php
│   │   ├── whois.blade.php
│   │   ├── epp.blade.php
│   │   └── sync.blade.php
│   ├── tlds/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── pricing.blade.php
│   ├── registrars/
│   │   ├── index.blade.php
│   │   ├── edit.blade.php
│   │   └── test.blade.php
│   ├── orders/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── invoices/
│   │   ├── index.blade.php
│   │   ├── show.blade.php
│   │   └── create.blade.php
│   ├── users/
│   │   ├── index.blade.php
│   │   ├── show.blade.php
│   │   ├── edit.blade.php
│   │   └── wallet.blade.php
│   ├── resellers/
│   │   └── api-keys.blade.php
│   ├── payments/
│   │   ├── transactions.blade.php
│   │   ├── gateways.blade.php
│   │   └── manual-requests.blade.php
│   ├── tickets/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── reports/
│   │   ├── revenue.blade.php
│   │   ├── domains.blade.php
│   │   └── resellers.blade.php
│   └── settings/
│       ├── general.blade.php
│       ├── currency.blade.php
│       ├── email-templates.blade.php
│       └── activity-logs.blade.php
├── client/
│   ├── dashboard.blade.php
│   ├── domains/
│   │   ├── index.blade.php
│   │   ├── show.blade.php
│   │   ├── dns.blade.php
│   │   ├── nameservers.blade.php
│   │   ├── whois.blade.php
│   │   └── transfer.blade.php
│   ├── cart/
│   │   ├── index.blade.php
│   │   └── checkout.blade.php
│   ├── invoices/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── wallet/
│   │   └── index.blade.php
│   ├── tickets/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── show.blade.php
│   └── profile/
│       └── edit.blade.php
├── reseller/
│   ├── dashboard.blade.php
│   ├── domains/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── api/
│   │   ├── docs.blade.php
│   │   ├── keys.blade.php
│   │   └── logs.blade.php
│   ├── pricing/
│   │   └── index.blade.php
│   ├── wallet/
│   │   └── index.blade.php
│   └── webhooks/
│       └── index.blade.php
└── emails/
    ├── domain-registered.blade.php
    ├── domain-renewed.blade.php
    ├── domain-transfer-initiated.blade.php
    ├── domain-expiring-reminder.blade.php
    ├── invoice-created.blade.php
    ├── payment-received.blade.php
    ├── wallet-credited.blade.php
    ├── low-balance-warning.blade.php
    ├── support-ticket-created.blade.php
    ├── support-ticket-replied.blade.php
    ├── api-key-generated.blade.php
    └── registrar-balance-low.blade.php
```

---

### 2.1 Layouts

| View Path | Layout Name | Description & UI Components |
|---|---|---|
| `resources/views/layouts/app.blade.php` | Base App Shell | HTML5 doctype, meta tags, CSRF token, Tailwind CSS compiled styles, Alpine.js script tag, toast notification stack container (`x-data="toastManager"`). |
| `resources/views/layouts/admin.blade.php` | Admin Layout | Fixed collapsable sidebar with navigation icons and active state highlights, top navigation bar with search shortcut (`Ctrl+K`), registrar balance chips, dark/light theme switch, user profile dropdown, breadcrumbs, impersonation alert banner. |
| `resources/views/layouts/client.blade.php` | Client Layout | Top header with logo, domain search shortcut, live wallet balance chip in USD/BDT, shopping cart counter badge, responsive mobile menu drawer, main content container, footer with copyright and support links. |
| `resources/views/layouts/reseller.blade.php` | Reseller Layout | Reseller portal layout with API status heartbeat pill, tier indicator badge (e.g. Tier 2 Gold), wallet balance widget, API documentation link, webhook delivery monitor chip, sidebar navigation tailored for wholesale management. |
| `resources/views/layouts/guest.blade.php` | Guest Layout | Minimal centered card layout with subtle geometric background gradient, brand logo, language/currency indicator, card container with rounded corners and drop shadow for authentication flows. |
| `resources/views/layouts/email.blade.php` | Email Base Layout | Responsive 600px HTML email template table with header branding, content body slot, transactional action button styling, security warning footer, and company physical address. |

---

### 2.2 Reusable Blade Components

#### 1. `<x-alert>` (`resources/views/components/alert.blade.php`)
- **Props:** `type` ('success'|'warning'|'error'|'info'), `dismissible` (bool), `title` (?string).
- **UI:** Alert container with colored border-l-4, SVG status icon, body text, and Alpine-powered dismiss button (`x-data="{ show: true }" x-show="show" x-transition`).

#### 2. `<x-badge>` (`resources/views/components/badge.blade.php`)
- **Props:** `variant` ('primary'|'secondary'|'success'|'danger'|'warning'|'gray'), `size` ('sm'|'md'), `dot` (bool).
- **UI:** Rounded pill badge with subtle background color, contrasting text, and optional animated pinging status dot.

#### 3. `<x-button>` (`resources/views/components/button.blade.php`)
- **Props:** `type` ('button'|'submit'), `variant` ('primary'|'secondary'|'danger'|'outline'|'ghost'), `size` ('sm'|'md'|'lg'), `loading` (bool), `icon` (?string).
- **UI:** Styled button with hover/focus ring states, inline SVG icon slot, and dynamic Alpine loading spinner (`x-bind:disabled="loading"`).

#### 4. `<x-card>` (`resources/views/components/card.blade.php`)
- **Props:** `title` (?string), `subtitle` (?string), `actions` (slot), `footer` (slot), `padding` (bool).
- **UI:** White/dark container with border, subtle shadow, header slot with action buttons, body slot, and optional separated footer.

#### 5. `<x-datatable>` (`resources/views/components/datatable.blade.php`)
- **Props:** `headers` (array), `records` (paginator), `searchable` (bool), `filterSlot` (slot).
- **UI:** Responsive table wrapper with search bar input, per-page selection dropdown (10, 25, 50, 100), sortable table column headers with ascending/descending arrows, zebra striping, empty state fallback, and pagination footer.

#### 6. `<x-modal>` (`resources/views/components/modal.blade.php`)
- **Props:** `id` (string), `title` (?string), `maxWidth` ('sm'|'md'|'lg'|'xl'|'2xl').
- **UI:** Alpine-managed modal (`x-data="{ open: false }" @open-modal.window="..."`), backdrop overlay with backdrop-blur, escape-key listener, focus trap, and header/body/footer slots.

#### 7. `<x-dropdown>` (`resources/views/components/dropdown.blade.php`)
- **Props:** `align` ('left'|'right'), `width` ('48'|'64'), `trigger` (slot).
- **UI:** Alpine click-away dropdown (`@click.outside="open = false"`), smooth dropdown menu panel with dividers and hoverable action items.

#### 8. `<x-domain-status-badge>` (`resources/views/components/domain-status-badge.blade.php`)
- **Props:** `status` ('active'|'pending'|'expired'|'grace_period'|'redemption'|'cancelled'|'transferred_away').
- **UI:** Distinct color badges (Active = Emerald, Pending = Amber, Expired = Rose, Grace/Redemption = Purple, Transferred = Gray).

#### 9. `<x-pagination>` (`resources/views/components/pagination.blade.php`)
- **Props:** `paginator` (LengthAwarePaginator).
- **UI:** Tailwind pagination links with "Showing X to Y of Z results", previous/next arrows, and direct page pills.

#### 10. `<x-search-bar>` (`resources/views/components/search-bar.blade.php`)
- **Props:** `placeholder` (string), `action` (string), `value` (?string).
- **UI:** Search form input with magnifying glass SVG icon, clear cross icon via Alpine, and submit button.

#### 11. `<x-sidebar>` (`resources/views/components/sidebar.blade.php`)
- **Props:** `role` ('admin'|'client'|'reseller').
- **UI:** Collapsible navigation links, nested submenus with Alpine toggle, SVG icons, and unread notification badge counters.

#### 12. `<x-stat-card>` (`resources/views/components/stat-card.blade.php`)
- **Props:** `title` (string), `value` (string), `change` (?float), `changeType` ('increase'|'decrease'), `icon` (string), `iconBg` (string).
- **UI:** Dashboard metric card with icon container, headline figure, and green/red trend pill with percentage change.

#### 13. `<x-chart>` (`resources/views/components/chart.blade.php`)
- **Props:** `id` (string), `type` ('line'|'bar'|'doughnut'), `data` (array), `options` (array).
- **UI:** Vanilla HTML5 Canvas wrapper initialized via lightweight CDN Chart.js or Alpine canvas helper, eliminating build dependencies.

#### 14. `<x-empty-state>` (`resources/views/components/empty-state.blade.php`)
- **Props:** `title` (string), `description` (string), `icon` (?string), `actionUrl` (?string), `actionText` (?string).
- **UI:** Centered placeholder with vector illustration, heading, descriptive text, and primary call-to-action button.

#### 15. `<x-file-upload>` (`resources/views/components/file-upload.blade.php`)
- **Props:** `name` (string), `label` (string), `accept` (string), `maxSize` (string).
- **UI:** Drag-and-drop file upload target with upload progress indicator and client-side image preview for payment receipts and support tickets.

#### 16. `<x-price-display>` (`resources/views/components/price-display.blade.php`)
- **Props:** `amountInUsd` (float), `showBdt` (bool).
- **UI:** Displays primary USD price (e.g. `$12.99`) with inline secondary converted BDT text (e.g. `~ ৳1,532.82 BDT`).

#### 17. `<x-currency-selector>` (`resources/views/components/currency-selector.blade.php`)
- **UI:** Dropdown or toggle pill allowing user to view store prices in USD or BDT.

#### 18. `<x-tld-badge>` (`resources/views/components/tld-badge.blade.php`)
- **Props:** `tld` (string).
- **UI:** Monospaced pill badge emphasizing domain extension with colored dot indicator (e.g. `.com`, `.net`, `.xyz`).

#### 19. `<x-copy-to-clipboard>` (`resources/views/components/copy-to-clipboard.blade.php`)
- **Props:** `text` (string), `label` (?string).
- **UI:** Alpine clipboard handler (`navigator.clipboard.writeText`) with momentary "Copied!" checkmark animation.

---

### 2.3 Auth Pages (`resources/views/auth/`)

| View File | UI Components & Form Fields |
|---|---|
| `login.blade.php` | Card with Email, Password, "Remember Me" checkbox, "Forgot Password?" link, Login button with loading spinner, Cloudflare Turnstile captcha widget. |
| `register.blade.php` | Account selector (Client / Reseller), Full Name, Email, Country Code, Mobile Phone, Password, Password Confirmation, Terms checkbox, Register button. |
| `forgot-password.blade.php` | Descriptive help text, Email input, "Email Password Reset Link" button, Back to Login link. |
| `reset-password.blade.php` | Hidden reset token, Email input (readonly), New Password, Confirm New Password, "Reset Password" submit button. |
| `verify-email.blade.php` | Notice banner regarding verification email, "Resend Verification Email" button, Logout link. |
| `two-factor-challenge.blade.php` | 6-digit TOTP code input with auto-tabbing, recovery code toggle link, "Verify Code" submit button. |
| `confirm-password.blade.php` | Security prompt before visiting protected settings, Password input, Confirm button. |

---

### 2.4 Admin Pages (`resources/views/admin/`)

| View File | UI Components & Contents |
|---|---|
| `dashboard.blade.php` | 4 KPI Stat Cards (Total Revenue USD/BDT, Active Domains, Total Clients/Resellers, Pending Orders), Daily Revenue Chart (Canvas), Registrar Balance Monitor Table (Namecheap, Spaceship, Dynadot, AtakDomain, Namesilo), Expiring Domains Alert List (within 7 days), Recent Activity Log Stream. |
| `domains/index.blade.php` | Filterable DataTable: Search by domain name, TLD filter dropdown, status filter (Active, Expired, Pending), Registrar filter, bulk action menu (Sync Status, Enable Auto-Renew, Lock), columns (Domain, Client, Registrar, Expiry Date, Auto-Renew, Lock, Actions). |
| `domains/show.blade.php` | Domain Overview Tab: Registration details, Registrar reference ID, EPP Code reveal toggle, Quick action buttons (Sync with Registrar, Renew Now, Toggle Lock, Toggle ID Protection, Delete), WHOIS summary card, attached orders history. |
| `domains/dns.blade.php` | DNS Zone Editor: Nameserver mode indicator (Registrar DNS vs External), Records DataTable (Type, Hostname, Address/Value, Priority, TTL, Actions), "Add Record" modal, BIND Zone File Import modal, Zone Export button, Predefined Template dropdown (Google Workspace, M365, Cloudflare). |
| `domains/nameservers.blade.php` | Nameserver configuration card: Radio option (Use Default System Nameservers vs Use Custom Nameservers), Input fields for Nameserver 1 through 5, Child Nameservers (Glue Records) table with "Add Host Record" modal (Hostname + IPv4/IPv6). |
| `domains/whois.blade.php` | WHOIS Contact Editor: Tabbed interface (Registrant, Administrator, Technical, Billing), form fields (First Name, Last Name, Organization, Address 1 & 2, City, State, Postcode, Country dropdown, Phone with country code, Email), Privacy Protection toggle switch. |
| `domains/epp.blade.php` | Security modal/card: Domain transfer lock status indicator, EPP / Auth code viewer with Alpine reveal and copy button, "Request New Auth Code" button. |
| `domains/sync.blade.php` | Live sync status runner: Real-time progress bar, console log output container showing sync queries to upstream registrars, summary counters (Updated, Unchanged, Errors). |
| `tlds/index.blade.php` | Supported TLDs DataTable: TLD extension (`.com`, `.net`, etc.), Assigned default registrar, Base cost (USD), Retail price (USD/BDT), Reseller price (USD/BDT), EPP required flag, ID Protection supported flag, Active/Inactive status toggle. |
| `tlds/create.blade.php` | Add New TLD Form: TLD extension input, Registrar dropdown, Grace period days, Redemption period days, Minimum registration years, DNS management supported toggle, EPP required toggle, ID Protection toggle. |
| `tlds/edit.blade.php` | Edit TLD Form: Modify assigned registrar, update renewal constraints, toggle availability for public search. |
| `tlds/pricing.blade.php` | TLD Pricing Matrix: Grid table showing all TLDs across tiers: Base Cost, Retail Price (Register, Renew, Transfer), Reseller Tier 1, Reseller Tier 2, Reseller Tier 3 pricing with USD and auto-calculated BDT values. Bulk markup adjuster tool (+X% or +$X). |
| `registrars/index.blade.php` | 5 Registrar Module Cards: Namecheap, Spaceship, Dynadot, AtakDomain, Namesilo. Displays connection status (Green = Connected, Red = Error), Live API balance in USD, active managed domains count, "Test Connection" button, "Configure" button. |
| `registrars/edit.blade.php` | Registrar Configuration Form: Environment mode (Production vs Sandbox), API Key, API Secret / User ID, IP Whitelist helper displaying server outbound IP, Default nameservers for this registrar, Low balance warning threshold in USD. |
| `registrars/test.blade.php` | Interactive API Diagnostic Tool: Triggers test API handshake, queries account balance, verifies IP authorization, displays formatted raw JSON request/response debug panel. |
| `orders/index.blade.php` | Orders DataTable: Order Number (`ORD-XXXXX`), Client name/email, Items summary (e.g. "register: example.com (1 yr)"), Total amount (USD & BDT), Payment status pill, Execution status pill, Created date, View button. |
| `orders/show.blade.php` | Order Detail View: Line items table, attached invoice link, execution timeline (Payment Received -> Registrar API Call -> DNS Initialized -> Completed), Retry Execution button for failed registrar calls, Cancel & Refund button. |
| `invoices/index.blade.php` | Invoices DataTable: Invoice Number (`INV-YYYYMM-XXXXX`), User, Subtotal, Tax, Total, Due Date, Status (Paid, Unpaid, Void, Cancelled), Payment Gateway pill, Quick Mark as Paid modal trigger. |
| `invoices/show.blade.php` | Printable Invoice View: Company header, client address, invoice metadata, itemized table, payment transaction history with gateway reference ID, "Download PDF" button, "Send Email Reminder" button, "Void Invoice" button. |
| `invoices/create.blade.php` | Manual Invoice Builder: Client selector dropdown, Due date picker, Currency selector (USD/BDT), Dynamic item rows with Alpine (Description, Qty, Unit Price, Taxable toggle), Total calculator, Admin notes input. |
| `users/index.blade.php` | Users DataTable: Name, Email, Role badge (Admin, Client, Reseller), Wallet balance (USD & BDT), Active domains count, Status (Active, Suspended), Impersonate button, Edit button. |
| `users/show.blade.php` | User Profile View: Client metrics, portfolio list of registered domains, recent orders, invoices list, support ticket history, wallet ledger, security activity log. |
| `users/edit.blade.php` | User Edit Form: Name, Email, Phone, Company, Address, Role selector, Reseller Tier assignment dropdown (None, Tier 1, Tier 2, Tier 3), Account status toggle (Active/Suspended), Reset 2FA button. |
| `users/wallet.blade.php` | Admin Wallet Management View: Current balance card, "Adjust Balance" modal (Credit / Debit radio, Amount in USD, Reason note, Reference ID), filterable table of past wallet transactions. |
| `resellers/api-keys.blade.php` | Reseller API Management: List of reseller API keys, IP Whitelist rules, Rate limit overrides, Revoke key button, Generate new key modal with permissions checklist (Domains, DNS, Wallet, Pricing). |
| `payments/transactions.blade.php` | Consolidated Transactions DataTable: Transaction Reference, Invoice #, User, Gateway (bKash, Stripe, Binance, Manual), Amount, Currency, Gateway Fee, Status, Date. |
| `payments/gateways.blade.php` | Payment Gateway Settings Tabs: **Stripe** (Publishable Key, Secret Key, Webhook Secret), **bKash** (App Key, App Secret, Username, Password, Sandbox mode), **Binance** (API Key, Secret Key, Merchant ID), **Manual Payment** (Bank Account details, bKash/Nagad personal numbers, instructions text). |
| `payments/manual-requests.blade.php` | Manual Payment Verification Queue: List of pending receipts submitted by clients. Displays Invoice #, User, Gateway used, Submitted TrxID, Sender Number, Proof Image thumbnail with lightbox preview, "Approve & Credit" button, "Reject" modal with reason. |
| `tickets/index.blade.php` | Support Tickets Queue: Ticket ID, Subject, Department, Priority badge (Urgent, High, Medium, Low), Client name, Assigned Staff dropdown, Status (Open, Answered, Customer-Reply, Closed), Last Updated time. |
| `tickets/show.blade.php` | Ticket Conversation Thread: Ticket details sidebar, message timeline with customer messages and staff replies, Internal Staff Notes tab (invisible to user), Canned Responses dropdown, Rich text reply editor, file attachment uploader, Status changer. |
| `reports/revenue.blade.php` | Revenue Analytics View: Date range picker (Today, 7 days, 30 days, Custom), Revenue breakdown chart (Gross vs Net), Gateway distribution pie chart, export to CSV and PDF buttons. |
| `reports/domains.blade.php` | Domain Trends Report: Registrations vs Renewals vs Transfers bar chart, Top 10 TLDs chart, Expired domain rate analysis, Registrar distribution table. |
| `reports/resellers.blade.php` | Reseller Performance Report: Top resellers by sales volume, API call volume vs error rates table, Reseller tier distribution. |
| `settings/general.blade.php` | System Settings: Site Name, Logo & Favicon upload, Default primary currency, Default nameservers (ns1/ns2.example.com), Admin notification email address, Maintenance mode toggle. |
| `settings/currency.blade.php` | Currency & Exchange Rates: Primary Currency (USD), Secondary Currency (BDT), Exchange Rate input (USD to BDT), Conversion Margin % (e.g. +2%), Live Auto-update toggle via cron, "Fetch Rates Now" button. |
| `settings/email-templates.blade.php` | Email Templates Manager: List of templates (Domain Registered, Expiry Warning, Invoice Created, etc.), Template editor with WYSIWYG/HTML editor, Subject line input, Available placeholder tags cheatsheet (e.g. `{client_name}`, `{domain}`, `{amount}`). |
| `settings/activity-logs.blade.php` | System Audit Trail DataTable: Timestamp, Causer (Admin/User/System), Action, Subject Type & ID, IP Address, "View Payload" modal showing old vs new attributes diff. |

---

### 2.5 Client Pages (`resources/views/client/`)

| View File | UI Components & Contents |
|---|---|
| `dashboard.blade.php` | Quick Domain Search Bar, 4 Metric Cards (Active Domains, Expiring Soon, Unpaid Invoices, Available Wallet Balance), Urgent Alerts (Expiring domains in < 7 days with 1-click renew), Recent Orders list, Active Support Tickets list. |
| `domains/index.blade.php` | My Domains DataTable: Domain name, Registration date, Expiration date, Auto-renew toggle switch (Alpine AJAX call), Status badge, "Manage" dropdown (DNS, Nameservers, WHOIS, Renew). |
| `domains/show.blade.php` | Single Domain Management: Expiration countdown badge, Registrar lock toggle switch, Auto-renew toggle, Nameserver quick summary, WHOIS privacy status, 1-Click Renew Domain button, Transfer Out / Get EPP Code button. |
| `domains/dns.blade.php` | DNS Zone Management: Status indicator ("Managing DNS on Cloud Servers"), Add Record form (Type, Name, Value, Priority, TTL), Records table with inline edit and delete actions, Pre-configured templates (Google Workspace, M365). |
| `domains/nameservers.blade.php` | Nameserver Editor: Radio selection between "Use System Default Nameservers" and "Use Custom Nameservers", 5 input fields for custom nameservers, "Save Nameservers" button, Child Nameservers accordion with Add Host Record form. |
| `domains/whois.blade.php` | WHOIS Privacy & Contact Form: WHOIS ID Protection switch, Registrant Contact form (Name, Org, Email, Phone, Address, City, Country, Postcode), "Copy Registrant to Admin/Tech/Billing" checkbox. |
| `domains/transfer.blade.php` | Transfer Domain In: Domain name input, EPP / Auth Code input, Transfer pricing display in USD and BDT, "Add Transfer to Cart" button. |
| `cart/index.blade.php` | Shopping Cart View: List of cart items (Domain registrations, renewals, transfers), Registration period selector (1 to 10 years) with instant price update, ID Protection addon toggle (Free/Paid), Order Summary card (Subtotal, Tax, Total in USD & BDT), "Proceed to Checkout" button. |
| `cart/checkout.blade.php` | Checkout Page: Invoice details review, Payment Method Selector with interactive radio cards: **Account Wallet** (shows balance), **bKash Online**, **Stripe (Credit/Debit Card)**, **Binance Pay (Crypto)**, **Manual Bank/MFS**; "Pay Now" submit button. |
| `invoices/index.blade.php` | My Invoices DataTable: Invoice #, Date, Due Date, Total (USD & BDT), Status pill, "Pay Now" button for unpaid invoices, "PDF" download icon. |
| `invoices/show.blade.php` | Invoice Detail View: Clean printable invoice layout, line items table, payment history, "Pay Now" gateway selection modal, PDF download button. |
| `wallet/index.blade.php` | Client Wallet Hub: Current Balance Card in USD & BDT, "Add Funds" card (Amount input in USD, Payment method selector, auto BDT conversion calculation), Wallet Transactions history table (Date, Type [Credit/Debit], Amount, Running Balance, Description). |
| `tickets/index.blade.php` | My Support Tickets: Ticket ID, Subject, Department, Priority, Status, Last Reply timestamp, "Open New Ticket" button. |
| `tickets/create.blade.php` | Open Ticket Form: Department selector (Billing, Technical, Domain Operations), Priority selector, Subject line, Related Domain dropdown, Message textarea, Drag-and-drop file attachment area. |
| `tickets/show.blade.php` | Ticket Chat Thread: Chat bubble timeline displaying conversation between user and support staff, file attachments download links, "Reply" form with attachment support, "Close Ticket" button. |
| `profile/edit.blade.php` | Profile & Security: Personal Details Form (Name, Email, Phone, Address), Change Password Form, Two-Factor Authentication (2FA) setup card with QR code and recovery codes modal. |

---

### 2.6 Reseller Pages (`resources/views/reseller/`)

| View File | UI Components & Contents |
|---|---|
| `dashboard.blade.php` | Reseller KPI Cards (API Balance, Active API Domains, Today's API Requests, Current Pricing Tier), Live API Heartbeat indicator, Recent API Error Logs table, Quick API Endpoint link. |
| `domains/index.blade.php` | Wholesale Domains Portfolio: DataTable showing all domains registered via API or reseller dashboard, Client identifier tag, Expiration date, Status, Bulk action tool (Bulk Renew, Bulk Nameservers Update). |
| `domains/show.blade.php` | Reseller Domain Detail: Technical domain controls, upstream registrar status code, raw API sync button, EPP code viewer. |
| `api/docs.blade.php` | Interactive API Documentation: Swagger/Redoc style documentation built directly in Blade. Covers Authentication (Bearer Token), Base URLs, Endpoints (`/api/v1/domains/check`, `/api/v1/domains/register`, `/api/v1/domains/renew`, `/api/v1/domains/dns`, etc.), Request/Response JSON examples, cURL & PHP code snippets. |
| `api/keys.blade.php` | API Credentials Management: Active API Key card (hidden key with copy button), "Generate New Key" button with confirmation modal, Allowed IP Addresses configuration form (supports single IPs and CIDR blocks e.g. `198.51.100.1/24`). |
| `api/logs.blade.php` | Real-time API Logs Viewer: DataTable listing API requests: Timestamp, HTTP Method (POST/GET), Endpoint, Client IP, Response HTTP Code (200, 422, 500), Execution Time (ms), "Inspect" modal showing JSON request payload and response body. |
| `pricing/index.blade.php` | Reseller Wholesale Pricing Sheet: Complete table of all TLDs showing Base Registrar Cost, Standard Retail Price, Reseller Tier Discounted Price in USD and BDT, Profit Margin calculation column, Download Pricing CSV button. |
| `wallet/index.blade.php` | Reseller Wallet & Auto-Topup: Balance card in USD/BDT, Low Balance Alert Threshold setting (e.g. Notify me when balance < $50), "Deposit Funds" form supporting instant Stripe/bKash/Binance top-ups. |
| `webhooks/index.blade.php` | Webhook Subscriptions Manager: Webhook Destination URL input, Secret Key generator, Event checkboxes (`domain.registered`, `domain.renewed`, `domain.expiring`, `dns.updated`), "Send Test Webhook" button, Webhook Delivery History log with retry button. |

---

### 2.7 Email Templates (`resources/views/emails/`)

| Template File | Purpose & Variable Data |
|---|---|
| `domain-registered.blade.php` | Sent upon successful domain registration. Variables: `{name}`, `{domain_name}`, `{registration_date}`, `{expiry_date}`, `{nameservers}`, `{invoice_id}`. |
| `domain-renewed.blade.php` | Sent upon domain renewal completion. Variables: `{name}`, `{domain_name}`, `{years_renewed}`, `{new_expiry_date}`, `{amount_paid}`. |
| `domain-transfer-initiated.blade.php` | Sent when transfer in is started. Variables: `{name}`, `{domain_name}`, `{estimated_completion_days}`, `{epp_code_status}`. |
| `domain-expiring-reminder.blade.php` | Renewal reminder sent at 30, 15, 7, 3, and 1 day before expiration. Variables: `{name}`, `{domain_name}`, `{expiry_date}`, `{renewal_price}`, `{renew_url}`. |
| `invoice-created.blade.php` | Notifies user of new invoice. Variables: `{name}`, `{invoice_id}`, `{due_date}`, `{amount_usd}`, `{amount_bdt}`, `{payment_url}`. |
| `payment-received.blade.php` | Payment receipt confirmation. Variables: `{name}`, `{invoice_id}`, `{transaction_id}`, `{amount_paid}`, `{gateway}`, `{date}`. |
| `wallet-credited.blade.php` | Confirms wallet balance deposit. Variables: `{name}`, `{amount_credited}`, `{new_balance}`, `{source}`, `{reference}`. |
| `low-balance-warning.blade.php` | Alert sent to reseller when wallet balance drops below configured threshold. Variables: `{name}`, `{current_balance}`, `{threshold}`, `{topup_url}`. |
| `support-ticket-created.blade.php` | Confirmation of new ticket. Variables: `{name}`, `{ticket_id}`, `{subject}`, `{department}`, `{view_ticket_url}`. |
| `support-ticket-replied.blade.php` | Notifies user/staff of ticket reply. Variables: `{name}`, `{ticket_id}`, `{replier_name}`, `{reply_message}`, `{ticket_url}`. |
| `api-key-generated.blade.php` | Security notice when reseller API key is generated or rotated. Variables: `{name}`, `{ip_address}`, `{timestamp}`. |
| `registrar-balance-low.blade.php` | Critical notification sent to Administrator when upstream registrar API balance is low. Variables: `{registrar_name}`, `{current_balance}`, `{threshold}`. |

---

## 3. ALL QUEUE JOBS

All queue jobs utilize Laravel's `database` queue driver and implement `ShouldQueue`.

```
app/
└── Jobs/
    ├── ProcessDomainRegistrationJob.php
    ├── ProcessDomainRenewalJob.php
    ├── ProcessDomainTransferJob.php
    ├── SyncDomainStatusJob.php
    ├── SyncAllDomainsJob.php
    ├── ProcessAutoRenewalsJob.php
    ├── SendWebhookDeliveryJob.php
    ├── SendEmailNotificationJob.php
    ├── CheckRegistrarBalancesJob.php
    ├── GenerateInvoicePdfJob.php
    ├── CleanExpiredSessionsJob.php
    └── FetchExchangeRatesJob.php
```

| Job Class | Queue Name | Execution Logic & Description | When Dispatched | Retries & Timeout |
|---|---|---|---|---|
| `ProcessDomainRegistrationJob` | `orders` | Calls `DomainService::registerDomain()`. On success, updates order item status to `completed`. On registrar API failure, logs error, marks order item as `failed`, and creates admin notification ticket. | Dispatched upon successful invoice payment for an order containing registration items. | `$tries = 3;`<br>`$backoff = [30, 120, 300];`<br>`$timeout = 120;` |
| `ProcessDomainRenewalJob` | `orders` | Calls `DomainService::renewDomain()`. Reconciles expiration dates in database. On failure, notifies admin and flags domain for manual review. | Dispatched upon invoice payment for renewal or triggered by auto-renewal job. | `$tries = 3;`<br>`$backoff = [30, 120];`<br>`$timeout = 120;` |
| `ProcessDomainTransferJob` | `orders` | Calls `DomainService::transferDomain()`. Submits transfer request with EPP code to registrar API and records transfer tracking ID. | Dispatched upon invoice payment for domain transfer order. | `$tries = 2;`<br>`$backoff = [60, 300];`<br>`$timeout = 120;` |
| `SyncDomainStatusJob` | `sync` | Queries upstream registrar for live domain status, expiration date, lock status, and WHOIS privacy status. Reconciles database records. | Dispatched individually from admin panel or via bulk sync chunking. | `$tries = 2;`<br>`$backoff = 60;`<br>`$timeout = 60;` |
| `SyncAllDomainsJob` | `sync` | Retrieves all active domains in chunks of 100, dispatching child `SyncDomainStatusJob` instances to prevent queue worker timeouts. | Dispatched by the scheduled command `domains:sync-status`. | `$tries = 1;`<br>`$timeout = 300;` |
| `ProcessAutoRenewalsJob` | `orders` | Queries domains expiring within renewal threshold with auto-renew enabled. Checks wallet balance, deducts payment, and dispatches `ProcessDomainRenewalJob`. | Dispatched daily by `domains:auto-renew` console command. | `$tries = 1;`<br>`$timeout = 300;` |
| `SendWebhookDeliveryJob` | `notifications` | Formats payload, computes HMAC-SHA256 signature header, executes HTTP POST to reseller webhook URL. Records HTTP response code and latency in `webhook_deliveries`. | Dispatched whenever an event triggers an active webhook subscription. | `$tries = 5;`<br>`$backoff = [10, 60, 300, 900, 3600];`<br>`$timeout = 15;` |
| `SendEmailNotificationJob` | `notifications` | Renders HTML email template and sends message via configured shared hosting SMTP credentials. Records status in `email_logs`. | Dispatched whenever transactional emails are triggered. | `$tries = 3;`<br>`$backoff = [15, 60, 180];`<br>`$timeout = 30;` |
| `CheckRegistrarBalancesJob` | `default` | Connects to each active registrar (Namecheap, Spaceship, Dynadot, AtakDomain, Namesilo), retrieves account balance. If below threshold, dispatches admin alert. | Dispatched hourly by scheduled cron command. | `$tries = 2;`<br>`$backoff = 60;`<br>`$timeout = 60;` |
| `GenerateInvoicePdfJob` | `default` | Renders DOMPDF binary for specified invoice and caches the PDF document in storage. | Dispatched upon invoice generation or payment confirmation. | `$tries = 2;`<br>`$backoff = 30;`<br>`$timeout = 60;` |
| `CleanExpiredSessionsJob` | `default` | Purges expired database/file sessions, old API rate-limiting cache keys, and stale shopping carts older than 72 hours. | Dispatched daily during off-peak hours. | `$tries = 1;`<br>`$timeout = 120;` |
| `FetchExchangeRatesJob` | `default` | Queries open exchange rate endpoint for USD/BDT conversion rate, updates `currencies` table, flushes cache. | Dispatched every 12 hours via scheduler. | `$tries = 3;`<br>`$backoff = [60, 300];`<br>`$timeout = 45;` |

---

## 4. ALL EVENTS & LISTENERS

```
app/
├── Events/
│   ├── DomainRegisteredEvent.php
│   ├── DomainRenewedEvent.php
│   ├── DomainTransferredEvent.php
│   ├── DomainExpiringEvent.php
│   ├── InvoicePaidEvent.php
│   ├── InvoiceCreatedEvent.php
│   ├── PaymentReceivedEvent.php
│   ├── WalletCreditedEvent.php
│   ├── WalletDebitedEvent.php
│   ├── SupportTicketCreatedEvent.php
│   ├── SupportTicketRepliedEvent.php
│   ├── RegistrarBalanceLowEvent.php
│   └── ApiCallLoggedEvent.php
└── Listeners/
    ├── SendDomainRegisteredNotification.php
    ├── SendDomainRenewedNotification.php
    ├── SendDomainTransferNotification.php
    ├── SendDomainExpiringNotification.php
    ├── ProcessPaidInvoiceOrders.php
    ├── SendInvoiceCreatedNotification.php
    ├── SendPaymentReceivedNotification.php
    ├── SendWalletCreditedNotification.php
    ├── SendLowBalanceWarningNotification.php
    ├── SendTicketCreatedNotification.php
    ├── SendTicketRepliedNotification.php
    ├── AlertAdminLowRegistrarBalance.php
    ├── DispatchWebhookEvent.php
    └── LogSystemActivity.php
```

| Event Class | Event Properties | Listeners Handling This Event | Queue Status |
|---|---|---|---|
| `DomainRegisteredEvent` | `Domain $domain`<br>`Order $order`<br>`User $user` | `SendDomainRegisteredNotification`<br>`DispatchWebhookEvent`<br>`LogSystemActivity` | Listeners implement `ShouldQueue` |
| `DomainRenewedEvent` | `Domain $domain`<br>`int $years`<br>`User $user` | `SendDomainRenewedNotification`<br>`DispatchWebhookEvent`<br>`LogSystemActivity` | Listeners implement `ShouldQueue` |
| `DomainTransferredEvent` | `Domain $domain`<br>`User $user` | `SendDomainTransferNotification`<br>`DispatchWebhookEvent`<br>`LogSystemActivity` | Listeners implement `ShouldQueue` |
| `DomainExpiringEvent` | `Domain $domain`<br>`int $daysLeft`<br>`User $user` | `SendDomainExpiringNotification`<br>`DispatchWebhookEvent` | Listeners implement `ShouldQueue` |
| `InvoicePaidEvent` | `Invoice $invoice`<br>`PaymentTransaction $transaction` | `ProcessPaidInvoiceOrders`<br>`SendPaymentReceivedNotification`<br>`DispatchWebhookEvent`<br>`LogSystemActivity` | Synchronous execution for `ProcessPaidInvoiceOrders`; queued for notifications |
| `InvoiceCreatedEvent` | `Invoice $invoice` | `SendInvoiceCreatedNotification`<br>`DispatchWebhookEvent` | Listeners implement `ShouldQueue` |
| `PaymentReceivedEvent` | `PaymentTransaction $transaction` | `SendPaymentReceivedNotification`<br>`LogSystemActivity` | Listeners implement `ShouldQueue` |
| `WalletCreditedEvent` | `WalletTransaction $transaction`<br>`User $user` | `SendWalletCreditedNotification`<br>`LogSystemActivity` | Listeners implement `ShouldQueue` |
| `WalletDebitedEvent` | `WalletTransaction $transaction`<br>`User $user` | `SendLowBalanceWarningNotification` (conditional if below threshold)<br>`LogSystemActivity` | Listeners implement `ShouldQueue` |
| `SupportTicketCreatedEvent` | `Ticket $ticket`<br>`User $user` | `SendTicketCreatedNotification`<br>`LogSystemActivity` | Listeners implement `ShouldQueue` |
| `SupportTicketRepliedEvent` | `TicketReply $reply`<br>`Ticket $ticket`<br>`User $replier` | `SendTicketRepliedNotification`<br>`LogSystemActivity` | Listeners implement `ShouldQueue` |
| `RegistrarBalanceLowEvent` | `string $registrarName`<br>`float $currentBalance`<br>`float $threshold` | `AlertAdminLowRegistrarBalance`<br>`LogSystemActivity` | Listeners implement `ShouldQueue` |
| `ApiCallLoggedEvent` | `ApiLog $logEntry` | `DispatchWebhookEvent` (if error)<br>`LogSystemActivity` | Non-blocking / Queued |

---

## 5. ALL NOTIFICATIONS

All notifications inherit from `Illuminate\Notifications\Notification` and support multiple delivery channels (`mail` and `database`).

```
app/
└── Notifications/
    ├── DomainRegistrationSuccessNotification.php
    ├── DomainRenewalSuccessNotification.php
    ├── DomainExpiryReminderNotification.php
    ├── InvoiceCreatedNotification.php
    ├── PaymentSuccessfulNotification.php
    ├── WalletBalanceAddedNotification.php
    ├── LowBalanceAlertNotification.php
    ├── SupportTicketUpdatedNotification.php
    └── AdminRegistrarBalanceAlertNotification.php
```

| Notification Class | Delivery Channels | When Sent | Data Payload / Content Summary |
|---|---|---|---|
| `DomainRegistrationSuccessNotification` | `mail`, `database` | Sent upon completion of registrar registration API call. | Domain name, registration period, expiration date, active nameservers, management dashboard URL. |
| `DomainRenewalSuccessNotification` | `mail`, `database` | Sent when domain renewal succeeds. | Domain name, renewal duration (years), new expiration date, amount charged. |
| `DomainExpiryReminderNotification` | `mail`, `database` | Sent at 30, 15, 7, 3, and 1 days prior to expiration. | Domain name, expiration date, renewal price in USD/BDT, 1-click renewal payment link. |
| `InvoiceCreatedNotification` | `mail`, `database` | Sent when an invoice is issued. | Invoice number, due date, item count, total in USD and BDT, direct payment link. |
| `PaymentSuccessfulNotification` | `mail`, `database` | Sent immediately upon payment confirmation. | Invoice number, transaction ID, payment gateway name, amount paid, attached PDF receipt. |
| `WalletBalanceAddedNotification` | `mail`, `database` | Sent when wallet is credited. | Credited amount, new total balance, payment method/reference, transaction timestamp. |
| `LowBalanceAlertNotification` | `mail`, `database` | Sent to reseller when wallet balance drops below threshold. | Current balance, defined threshold, instant deposit link. |
| `SupportTicketUpdatedNotification` | `mail`, `database` | Sent when support ticket receives a reply or status change. | Ticket ID, subject, responder name, message excerpt, ticket URL. |
| `AdminRegistrarBalanceAlertNotification` | `mail`, `database` | Sent to Administrators when registrar balance is low. | Registrar name, current live balance, defined threshold, registrar login URL. |

---

## 6. ALL ARTISAN COMMANDS

Custom artisan commands located in `app/Console/Commands/` configured with scheduled cron intervals. In shared hosting, a single system cron triggers `php artisan schedule:run >> /dev/null 2>&1` every minute.

```
app/
└── Console/
    └── Commands/
        ├── CheckExpiringDomainsCommand.php
        ├── ProcessAutoRenewalsCommand.php
        ├── SyncDomainStatusCommand.php
        ├── CheckRegistrarBalancesCommand.php
        ├── UpdateCurrencyRatesCommand.php
        ├── RetryFailedWebhooksCommand.php
        ├── GenerateDailySummaryCommand.php
        └── CleanupSystemLogsCommand.php
```

| Command Class | Signature & Arguments | Schedule Interval | Description & Action |
|---|---|---|---|
| `CheckExpiringDomainsCommand` | `domains:check-expiring {--days=30,15,7,3,1}` | Daily at 02:00 (`0 2 * * *`) | Scans `domains` table for records expiring in exactly 30, 15, 7, 3, or 1 days. Dispatches `DomainExpiringEvent` and queues notification emails. |
| `ProcessAutoRenewalsCommand` | `domains:auto-renew` | Daily at 03:00 (`0 3 * * *`) | Identifies domains expiring within configured renewal window with `auto_renew = 1`. Deducts wallet balance and executes registrar renewals. |
| `SyncDomainStatusCommand` | `domains:sync-status {--registrar=} {--limit=100}` | Every 6 hours (`0 */6 * * *`) | Pushes `SyncAllDomainsJob` to reconcile domain status, expiry dates, and lock statuses across all active registrars. |
| `CheckRegistrarBalancesCommand` | `registrars:check-balance {--threshold=50}` | Hourly (`0 * * * *`) | Queries live account balance via API for all 5 registrars. Dispatches alert if balance falls below threshold. |
| `UpdateCurrencyRatesCommand` | `currency:update-rates {--force}` | Every 12 hours (`0 */12 * * *`) | Queries exchange rate API for USD to BDT rate, calculates markup percentage, updates database, and refreshes cache. |
| `RetryFailedWebhooksCommand` | `webhooks:retry-failed {--limit=50}` | Every 15 minutes (`*/15 * * * *`) | Scans `webhook_deliveries` with `status = 'failed'` and attempts under 5; re-queues deliveries with exponential backoff. |
| `GenerateDailySummaryCommand` | `reports:daily-summary` | Daily at 23:55 (`55 23 * * *`) | Computes daily financial totals, registration counts, active tickets, and sends executive summary email to site administrators. |
| `CleanupSystemLogsCommand` | `system:cleanup-logs {--days=90}` | Weekly on Sunday at 04:00 (`0 4 * * 0`) | Prunes `activity_logs`, `api_logs`, and processed queue jobs older than 90 days to maintain optimal MySQL table sizes. |

---

## 7. CONFIG FILES

Detailed configuration files located in the `config/` directory.

### 7.1 `config/registrars.php`
```php
<?php

return [
    'default' => env('DEFAULT_REGISTRAR', 'namecheap'),

    'low_balance_threshold' => env('REGISTRAR_LOW_BALANCE_THRESHOLD', 50.00),

    'modules' => [
        'namecheap' => [
            'class' => \App\Services\Registrar\Modules\NamecheapModule::class,
            'api_user' => env('NAMECHEAP_API_USER'),
            'api_key' => env('NAMECHEAP_API_KEY'),
            'username' => env('NAMECHEAP_USERNAME'),
            'client_ip' => env('NAMECHEAP_CLIENT_IP'),
            'sandbox' => env('NAMECHEAP_SANDBOX', true),
            'timeout' => 30,
        ],
        'spaceship' => [
            'class' => \App\Services\Registrar\Modules\SpaceshipModule::class,
            'api_key' => env('SPACESHIP_API_KEY'),
            'api_secret' => env('SPACESHIP_API_SECRET'),
            'sandbox' => env('SPACESHIP_SANDBOX', true),
            'timeout' => 30,
        ],
        'dynadot' => [
            'class' => \App\Services\Registrar\Modules\DynadotModule::class,
            'api_key' => env('DYNADOT_API_KEY'),
            'sandbox' => env('DYNADOT_SANDBOX', true),
            'timeout' => 30,
        ],
        'atakdomain' => [
            'class' => \App\Services\Registrar\Modules\AtakDomainModule::class,
            'api_key' => env('ATAKDOMAIN_API_KEY'),
            'api_secret' => env('ATAKDOMAIN_API_SECRET'),
            'sandbox' => env('ATAKDOMAIN_SANDBOX', true),
            'timeout' => 30,
        ],
        'namesilo' => [
            'class' => \App\Services\Registrar\Modules\NamesiloModule::class,
            'api_key' => env('NAMESILO_API_KEY'),
            'sandbox' => env('NAMESILO_SANDBOX', true),
            'timeout' => 30,
        ],
    ],

    'default_nameservers' => [
        env('DEFAULT_NS1', 'ns1.yourdomain.com'),
        env('DEFAULT_NS2', 'ns2.yourdomain.com'),
    ],
];
```

### 7.2 `config/payment.php`
```php
<?php

return [
    'default' => env('DEFAULT_PAYMENT_GATEWAY', 'bkash'),

    'gateways' => [
        'manual' => [
            'name' => 'Manual Payment',
            'enabled' => env('GATEWAY_MANUAL_ENABLED', true),
            'instructions' => env('GATEWAY_MANUAL_INSTRUCTIONS', 'Send money to our bank or bKash personal number and enter TrxID.'),
            'accounts' => [
                'bank' => [
                    'bank_name' => env('MANUAL_BANK_NAME', 'Dutch-Bangla Bank Ltd'),
                    'account_name' => env('MANUAL_ACCOUNT_NAME', 'Your Company Ltd'),
                    'account_number' => env('MANUAL_ACCOUNT_NUMBER', '123.120.45678'),
                    'branch' => env('MANUAL_BANK_BRANCH', 'Dhaka'),
                ],
                'bkash_personal' => env('MANUAL_BKASH_PERSONAL', '01700000000'),
                'nagad_personal' => env('MANUAL_NAGAD_PERSONAL', '01800000000'),
            ],
        ],
        'bkash' => [
            'name' => 'bKash Checkout',
            'enabled' => env('GATEWAY_BKASH_ENABLED', true),
            'app_key' => env('BKASH_CHECKOUT_APP_KEY'),
            'app_secret' => env('BKASH_CHECKOUT_APP_SECRET'),
            'username' => env('BKASH_CHECKOUT_USERNAME'),
            'password' => env('BKASH_CHECKOUT_PASSWORD'),
            'sandbox' => env('BKASH_CHECKOUT_SANDBOX', true),
            'currency' => 'BDT',
        ],
        'stripe' => [
            'name' => 'Stripe Credit/Debit Cards',
            'enabled' => env('GATEWAY_STRIPE_ENABLED', true),
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => 'USD',
        ],
        'binance' => [
            'name' => 'Binance Pay (Crypto)',
            'enabled' => env('GATEWAY_BINANCE_ENABLED', true),
            'api_key' => env('BINANCE_PAY_API_KEY'),
            'secret_key' => env('BINANCE_PAY_SECRET_KEY'),
            'merchant_id' => env('BINANCE_PAY_MERCHANT_ID'),
            'currency' => 'USDT',
        ],
    ],
];
```

### 7.3 `config/reseller.php`
```php
<?php

return [
    'tiers' => [
        1 => [
            'name' => 'Tier 1 - Bronze',
            'min_deposit' => 100.00,
            'discount_percentage' => 3.0,
        ],
        2 => [
            'name' => 'Tier 2 - Silver',
            'min_deposit' => 500.00,
            'discount_percentage' => 6.0,
        ],
        3 => [
            'name' => 'Tier 3 - Gold',
            'min_deposit' => 1000.00,
            'discount_percentage' => 10.0,
        ],
    ],

    'rate_limits' => [
        'requests_per_minute' => env('RESELLER_API_RATE_LIMIT', 60),
    ],

    'ip_whitelist_max' => 10,

    'api_token_expiry_days' => null, // null for permanent until revoked
];
```

### 7.4 `config/currencies.php`
```php
<?php

return [
    'primary' => 'USD',
    'secondary' => 'BDT',

    'supported' => [
        'USD' => [
            'name' => 'US Dollar',
            'symbol' => '$',
            'format' => '${amount}',
            'precision' => 2,
        ],
        'BDT' => [
            'name' => 'Bangladeshi Taka',
            'symbol' => '৳',
            'format' => '৳{amount} BDT',
            'precision' => 2,
        ],
    ],

    'exchange_provider' => env('EXCHANGE_PROVIDER', 'openexchangerates'),
    'api_key' => env('EXCHANGE_API_KEY'),
    'fallback_usd_to_bdt' => env('FALLBACK_USD_BDT_RATE', 121.50),
    'margin_percentage' => env('CURRENCY_MARGIN_PERCENT', 2.0),
];
```

### 7.5 `config/dns_templates.php`
```php
<?php

return [
    'templates' => [
        'google_workspace' => [
            'name' => 'Google Workspace',
            'records' => [
                ['type' => 'MX', 'name' => '@', 'content' => 'ASPMX.L.GOOGLE.COM.', 'priority' => 1, 'ttl' => 3600],
                ['type' => 'MX', 'name' => '@', 'content' => 'ALT1.ASPMX.L.GOOGLE.COM.', 'priority' => 5, 'ttl' => 3600],
                ['type' => 'MX', 'name' => '@', 'content' => 'ALT2.ASPMX.L.GOOGLE.COM.', 'priority' => 5, 'ttl' => 3600],
                ['type' => 'MX', 'name' => '@', 'content' => 'ALT3.ASPMX.L.GOOGLE.COM.', 'priority' => 10, 'ttl' => 3600],
                ['type' => 'MX', 'name' => '@', 'content' => 'ALT4.ASPMX.L.GOOGLE.COM.', 'priority' => 10, 'ttl' => 3600],
                ['type' => 'TXT', 'name' => '@', 'content' => 'v=spf1 include:_spf.google.com ~all', 'priority' => null, 'ttl' => 3600],
            ],
        ],
        'microsoft_365' => [
            'name' => 'Microsoft 365',
            'records' => [
                ['type' => 'MX', 'name' => '@', 'content' => '{domain-key}.mail.protection.outlook.com.', 'priority' => 0, 'ttl' => 3600],
                ['type' => 'TXT', 'name' => '@', 'content' => 'v=spf1 include:spf.protection.outlook.com ~all', 'priority' => null, 'ttl' => 3600],
                ['type' => 'CNAME', 'name' => 'autodiscover', 'content' => 'autodiscover.outlook.com.', 'priority' => null, 'ttl' => 3600],
            ],
        ],
    ],
];
```

---

## 8. REGISTRAR MODULE INTERFACE

Every registrar driver (`NamecheapModule`, `SpaceshipModule`, `DynadotModule`, `AtakDomainModule`, `NamesiloModule`) implements the complete `RegistrarModuleInterface`.

```php
<?php

namespace App\Contracts;

use App\DTOs\DomainAvailabilityDTO;
use App\DTOs\DomainRegistrationDTO;
use App\DTOs\DomainRenewalDTO;
use App\DTOs\DomainTransferDTO;
use App\DTOs\DomainActionResultDTO;
use App\DTOs\DomainDetailsDTO;
use App\DTOs\DnsRecordDTO;
use App\DTOs\WhoisDataDTO;
use App\DTOs\RegistrarBalanceDTO;
use Illuminate\Support\Collection;

interface RegistrarModuleInterface
{
    /**
     * Get unique slug identifier for the registrar driver.
     */
    public function getIdentifier(): string;

    /**
     * Check single domain availability.
     */
    public function checkAvailability(string $domain): DomainAvailabilityDTO;

    /**
     * Check availability for multiple domains in bulk.
     *
     * @param array<string> $domains
     * @return Collection<string, DomainAvailabilityDTO>
     */
    public function checkBulkAvailability(array $domains): Collection;

    /**
     * Register a new domain name.
     */
    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO;

    /**
     * Renew an active domain.
     */
    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO;

    /**
     * Initiate domain transfer into registrar.
     */
    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO;

    /**
     * Retrieve authoritative live domain metadata from registrar.
     */
    public function getInfo(string $domain): DomainDetailsDTO;

    /**
     * Get active nameservers for a domain.
     *
     * @return array<string>
     */
    public function getNameservers(string $domain): array;

    /**
     * Set authoritative nameservers for a domain.
     *
     * @param array<string> $nameservers
     */
    public function setNameservers(string $domain, array $nameservers): bool;

    /**
     * Fetch DNS zone records hosted at the registrar.
     *
     * @return Collection<DnsRecordDTO>
     */
    public function getDnsRecords(string $domain): Collection;

    /**
     * Add a DNS record to registrar hosted zone.
     */
    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO;

    /**
     * Update an existing DNS record.
     */
    public function updateDnsRecord(string $domain, string|int $recordId, DnsRecordDTO $record): DnsRecordDTO;

    /**
     * Delete a DNS record by upstream ID.
     */
    public function deleteDnsRecord(string $domain, string|int $recordId): bool;

    /**
     * Retrieve WHOIS contact details for a domain.
     */
    public function getWhois(string $domain): WhoisDataDTO;

    /**
     * Update WHOIS contact information at registry.
     */
    public function updateWhois(string $domain, WhoisDataDTO $whoisData): bool;

    /**
     * Fetch or request domain transfer authorization (EPP) code.
     */
    public function getEppCode(string $domain): string;

    /**
     * Enable or disable registry transfer lock (clientTransferProhibited).
     */
    public function setLock(string $domain, bool $locked): bool;

    /**
     * Enable or disable WHOIS privacy/ID protection.
     */
    public function setIdProtection(string $domain, bool $enabled): bool;

    /**
     * Register a child nameserver (glue record).
     */
    public function createChildNameserver(string $domain, string $hostname, string $ip): bool;

    /**
     * Update IP address of an existing child nameserver.
     */
    public function updateChildNameserver(string $domain, string $hostname, string $newIp): bool;

    /**
     * Delete a child nameserver from registry.
     */
    public function deleteChildNameserver(string $domain, string $hostname): bool;

    /**
     * Query live registrar account balance.
     */
    public function getBalance(): RegistrarBalanceDTO;
}
```

### Supporting DTOs Schema Overview:
- **`DomainAvailabilityDTO`**: `string $domain`, `bool $isAvailable`, `bool $isPremium`, `float $registrationCost`, `float $renewalCost`, `string $currency`, `string $registrar`.
- **`DomainRegistrationDTO`**: `string $domain`, `int $years`, `array $nameservers`, `WhoisDataDTO $contactDetails`, `bool $idProtection`, `?string $eppCode`.
- **`DomainActionResultDTO`**: `bool $success`, `string $domain`, `?string $registrarOrderId`, `?string $errorMessage`, `Carbon $expiresAt`.
- **`DomainDetailsDTO`**: `string $domain`, `string $status`, `Carbon $createdAt`, `Carbon $expiresAt`, `bool $isLocked`, `bool $idProtection`, `array $nameservers`.
- **`DnsRecordDTO`**: `?string $id`, `string $type` (A/AAAA/CNAME/MX/TXT/SRV/CAA), `string $name`, `string $content`, `?int $priority`, `int $ttl`.
- **`WhoisDataDTO`**: `array $registrant`, `array $admin`, `array $tech`, `array $billing` (Each containing: `first_name`, `last_name`, `organization`, `email`, `address1`, `address2`, `city`, `state`, `postcode`, `country_code`, `phone_country_code`, `phone_number`).
- **`RegistrarBalanceDTO`**: `string $registrar`, `float $balance`, `string $currency`, `Carbon $retrievedAt`.

---

## 9. PAYMENT GATEWAY INTERFACE

Every payment gateway driver (`ManualPaymentGateway`, `BkashGateway`, `StripeGateway`, `BinanceGateway`) implements the complete `PaymentGatewayInterface`.

```php
<?php

namespace App\Contracts;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentRedirectDTO;
use App\DTOs\PaymentVerificationDTO;
use App\DTOs\PaymentWebhookResultDTO;
use App\DTOs\PaymentStatusDTO;
use App\DTOs\PaymentRefundDTO;
use App\DTOs\RefundResultDTO;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Return unique identifier for gateway.
     */
    public function getName(): string;

    /**
     * Check if gateway is enabled and credentials are fully configured.
     */
    public function isAvailable(): bool;

    /**
     * List supported currencies for this gateway (e.g. ['USD'], ['BDT'], ['USDT']).
     *
     * @return array<string>
     */
    public function getSupportedCurrencies(): array;

    /**
     * Initialize payment transaction and return redirect URL or form data.
     */
    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO;

    /**
     * Verify payment upon user return callback from gateway.
     */
    public function verifyCallback(Request $request): PaymentVerificationDTO;

    /**
     * Process asynchronous server-to-server webhook IPN callback.
     */
    public function processWebhook(Request $request): PaymentWebhookResultDTO;

    /**
     * Query gateway directly for transaction status.
     */
    public function queryTransaction(string $transactionId): PaymentStatusDTO;

    /**
     * Refund a paid transaction.
     */
    public function refund(PaymentRefundDTO $refundRequest): RefundResultDTO;
}
```

### Supporting Payment DTOs Schema:
- **`PaymentRequestDTO`**: `string $invoiceId`, `float $amount`, `string $currency`, `string $returnUrl`, `string $cancelUrl`, `string $customerEmail`, `string $customerName`, `string $customerPhone`.
- **`PaymentRedirectDTO`**: `bool $isRedirect`, `?string $redirectUrl`, `string $method` ('GET'|'POST'), `array $formParameters`, `?string $gatewayReferenceId`.
- **`PaymentVerificationDTO`**: `bool $isSuccessful`, `string $invoiceId`, `string $transactionId`, `float $amountPaid`, `string $currency`, `?string $errorMessage`, `array $rawPayload`.
- **`PaymentWebhookResultDTO`**: `bool $handled`, `bool $isSuccessful`, `string $invoiceId`, `string $transactionId`, `?string $message`.
- **`PaymentStatusDTO`**: `string $transactionId`, `string $status` ('initiated'|'pending'|'completed'|'failed'|'refunded'), `float $amount`, `string $currency`.
- **`PaymentRefundDTO`**: `string $transactionId`, `float $amount`, `string $currency`, `string $reason`.
- **`RefundResultDTO`**: `bool $isSuccessful`, `string $refundTransactionId`, `?string $errorMessage`.

---
*End of Domain Reseller Panel Architectural Blueprint — Part 3.*
