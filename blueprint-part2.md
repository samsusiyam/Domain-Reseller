# 🌐 Domain Reseller Panel — Technical Blueprint (Part 2)
## Routes, Controllers, Middleware & Form Requests Architecture

> **Tech Stack**: Laravel 11 (Shared Hosting Optimized) + Blade + Tailwind CSS + Alpine.js + MySQL  
> **Currency Support**: Primary USD with automatic BDT multi-currency conversion  
> **Target Audience**: Admin, Direct Clients, Domain Resellers (WHMCS / Blesta / Custom API compatible)  
> **Document Scope**: Complete HTTP Layer Specification (All Middleware, Complete Route Catalog, All Controllers & Method Signatures, All Form Requests & Validation Rules)

---

# 1. Complete Middleware Architecture

In Laravel 11, middleware aliases and pipeline configurations are registered in `bootstrap/app.php` rather than `app/Http/Kernel.php`. All custom middleware classes reside under `app/Http/Middleware/`.

### Registration in `bootstrap/app.php`:
```php
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'active', 'admin', '2fa.verified', 'track.activity'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['web', 'auth', 'active', 'client', 'verified', '2fa.verified', 'track.activity'])
                ->prefix('client')
                ->name('client.')
                ->group(base_path('routes/client.php'));

            Route::middleware(['web', 'auth', 'active', 'reseller', 'verified', '2fa.verified', 'track.activity'])
                ->prefix('reseller')
                ->name('reseller.')
                ->group(base_path('routes/reseller.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin'            => \App\Http\Middleware\EnsureAdmin::class,
            'client'           => \App\Http\Middleware\EnsureClient::class,
            'reseller'         => \App\Http\Middleware\EnsureReseller::class,
            'active'           => \App\Http\Middleware\EnsureActive::class,
            'verified'         => \App\Http\Middleware\EnsureEmailVerified::class,
            'api.auth'         => \App\Http\Middleware\ApiAuthenticate::class,
            'api.ratelimit'    => \App\Http\Middleware\ApiRateLimit::class,
            'api.ip_whitelist' => \App\Http\Middleware\ApiIpWhitelist::class,
            'track.activity'   => \App\Http\Middleware\TrackActivity::class,
            '2fa.verified'     => \App\Http\Middleware\TwoFactorVerified::class,
            'maintenance.check'=> \App\Http\Middleware\MaintenanceMode::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\MaintenanceMode::class,
        ]);
    })->create();
```

---

## 1.1 Detailed Middleware Specifications

### 1. `EnsureAdmin`
- **File Path**: `app/Http/Middleware/EnsureAdmin.php`
- **Alias**: `admin`
- **Purpose**: Restricts panel access exclusively to users with `role === 'admin'`.
- **Logic**:
  1. Checks if `Auth::check()`. If false, redirects to `route('login')`.
  2. Inspects `$request->user()->role`.
  3. If `$request->user()->role !== 'admin'`, logs unauthorized access attempt in security log and redirects non-admins:
     - Resellers redirected to `route('reseller.dashboard')`.
     - Direct clients redirected to `route('client.dashboard')`.
  4. Returns `403 Forbidden` if user has no valid panel access.
- **Routes Using It**: All routes in `routes/admin.php`.

### 2. `EnsureClient`
- **File Path**: `app/Http/Middleware/EnsureClient.php`
- **Alias**: `client`
- **Purpose**: Restricts panel access to standard client accounts (`role === 'client'`).
- **Logic**:
  1. Checks `Auth::check()`. If false, redirects to `route('login')`.
  2. Inspects `$request->user()->role`.
  3. If `$request->user()->role === 'admin'`, redirects to `route('admin.dashboard')`.
  4. If `$request->user()->role === 'reseller'`, redirects to `route('reseller.dashboard')`.
  5. Allows execution only if `$request->user()->role === 'client'`.
- **Routes Using It**: All routes in `routes/client.php`.

### 3. `EnsureReseller`
- **File Path**: `app/Http/Middleware/EnsureReseller.php`
- **Alias**: `reseller`
- **Purpose**: Restricts reseller portal and wholesale tools to verified resellers (`role === 'reseller'`).
- **Logic**:
  1. Checks `Auth::check()`. If false, redirects to `route('login')`.
  2. Inspects `$request->user()->role`.
  3. If `$request->user()->role === 'admin'`, allows impersonation or redirects to admin.
  4. If `$request->user()->role === 'client'`, redirects to `route('client.dashboard')` with an upgrade alert (`"Apply for Reseller Account"`).
  5. Allows execution if `$request->user()->role === 'reseller'`.
- **Routes Using It**: All routes in `routes/reseller.php`.

### 4. `EnsureActive`
- **File Path**: `app/Http/Middleware/EnsureActive.php`
- **Alias**: `active`
- **Purpose**: Blocks suspended, banned, or pending accounts from performing any authenticated operations.
- **Logic**:
  1. If user is authenticated, inspects `$request->user()->status`.
  2. If `status === 'suspended'`:
     - Invalidates session: `Auth::logout()`, `$request->session()->invalidate()`, `$request->session()->regenerateToken()`.
     - Redirects to `route('login')` with session error flash: `"Your account has been suspended. Reason: Contact Compliance Support."`
  3. If `status === 'pending'`:
     - If email not confirmed, redirects to `route('verification.notice')`.
     - Otherwise renders dedicated view `auth.pending-approval`.
  4. If `status === 'closed'`: logs out and displays termination notice.
- **Routes Using It**: Applied globally to all authenticated groups (`admin`, `client`, `reseller`).

### 5. `EnsureEmailVerified`
- **File Path**: `app/Http/Middleware/EnsureEmailVerified.php`
- **Alias**: `verified`
- **Purpose**: Enforces mandatory email confirmation before domain orders or DNS actions can be executed.
- **Logic**:
  1. Checks if `$request->user()->hasVerifiedEmail()`.
  2. If false, prevents progression to sensitive actions (checkout, domain registration, DNS update).
  3. Returns JSON `403` with `{"message": "Your email address is not verified."}` for AJAX / API requests.
  4. For web requests, redirects to `route('verification.notice')`.
- **Routes Using It**: Applied to all client and reseller panel routes, excluding profile view and email verification endpoints.

### 6. `ApiAuthenticate`
- **File Path**: `app/Http/Middleware/ApiAuthenticate.php`
- **Alias**: `api.auth`
- **Purpose**: Authenticates external WHMCS modules and custom integration calls via API Key & Secret.
- **Logic**:
  1. Extracts credentials from headers:
     - Header `X-Api-Key` or query parameter `api_key`.
     - Header `X-Api-Secret` or query parameter `api_secret`.
  2. If missing, responds with HTTP `401 Unauthorized`: `{"status": "error", "code": 401, "message": "API Key or Secret missing in request headers."}`.
  3. Hashes secret using `hash('sha256', $apiSecret)`.
  4. Queries `api_keys` table where `key_identifier = $apiKey` and `secret_hash = $secretHash` and `status = 'active'`.
  5. If record not found, responds with HTTP `401 Unauthorized`: `{"status": "error", "code": 401, "message": "Invalid API credentials."}`.
  6. Fetches associated user (`$apiKeyRecord->user`). If user is suspended or not a reseller, responds with HTTP `403 Forbidden`.
  7. Updates `api_keys.last_used_at` with `now()`.
  8. Binds authenticated user and API key instance to request: `Auth::setUser($user)` and `$request->attributes->set('api_key', $apiKeyRecord)`.
- **Routes Using It**: All endpoints under `routes/api.php` (`/api/v1/*`).

### 7. `ApiRateLimit`
- **File Path**: `app/Http/Middleware/ApiRateLimit.php`
- **Alias**: `api.ratelimit`
- **Purpose**: Enforces rate limiting on shared hosting using file/database cache driver without Redis.
- **Logic**:
  1. Determines rate limit key: `reseller_api:key:` . `$request->attributes->get('api_key')->id ?? $request->ip()`.
  2. Retrieves custom rate limit configured on the API key (`rate_limit_per_minute`, default: 60 rpm).
  3. Executes `RateLimiter::tooManyAttempts($key, $maxAttempts)` using Laravel's Cache engine (file-backed).
  4. If limit exceeded:
     - Calculates `$retryAfter = RateLimiter::availableIn($key)`.
     - Returns HTTP `429 Too Many Requests`:
       ```json
       {
           "status": "error",
           "code": 429,
           "message": "API rate limit exceeded. Slow down your requests.",
           "retry_after_seconds": 45
       }
       ```
     - Sets HTTP response header `Retry-After: $retryAfter`.
  5. Calls `RateLimiter::hit($key, 60)`.
  6. Adds headers to response: `X-RateLimit-Limit: $maxAttempts`, `X-RateLimit-Remaining: $remaining`.
- **Routes Using It**: All endpoints under `routes/api.php`.

### 8. `ApiIpWhitelist`
- **File Path**: `app/Http/Middleware/ApiIpWhitelist.php`
- **Alias**: `api.ip_whitelist`
- **Purpose**: Restricts API calls strictly to IP addresses pre-authorized by the reseller.
- **Logic**:
  1. Retrieves `$apiKeyRecord = $request->attributes->get('api_key')`.
  2. Inspects `$apiKeyRecord->ip_whitelist` (stored as JSON array of IP strings or CIDR notations).
  3. If `$ipWhitelist` is null or empty (`[]`), checks system global setting `api_require_ip_whitelist`:
     - If true: denies request with `403 Forbidden` (`"No IP addresses whitelisted for this key"`).
     - If false: allows request from any IP (development/flexible mode).
  4. If whitelist contains items:
     - Compares `$request->ip()` against each entry (supports exact IPv4/IPv6 and CIDR subnet masking via `Symfony\Component\HttpFoundation\IpUtils::checkIp()`).
     - If no match found:
       - Logs security event with request IP, attempted key ID, and timestamp.
       - Returns HTTP `403 Forbidden`: `{"status": "error", "code": 403, "message": "Unauthorized client IP address: 203.0.113.19"}`.
- **Routes Using It**: All endpoints under `routes/api.php`.

### 9. `TrackActivity`
- **File Path**: `app/Http/Middleware/TrackActivity.php`
- **Alias**: `track.activity`
- **Purpose**: Tracks last user activity timestamp and audits destructive/mutative HTTP verbs.
- **Logic**:
  1. If user is authenticated:
     - Periodically (every 5 minutes via session debounce) updates `users.last_login_at` and `users.last_login_ip`.
  2. If HTTP method is mutative (`POST`, `PUT`, `PATCH`, `DELETE`):
     - Extracts route name, controller action, sanitized inputs (excluding `password`, `api_secret`, `card_number`, `cvv`).
     - Inserts record into `activity_logs` table:
       - `user_id`: current user ID
       - `action`: route name or action description
       - `method`: `$request->method()`
       - `url`: `$request->fullUrl()`
       - `ip_address`: `$request->ip()`
       - `user_agent`: `$request->userAgent()`
       - `payload`: JSON encoded sanitized parameters
- **Routes Using It**: Admin, Client, and Reseller web portal routes.

### 10. `TwoFactorVerified`
- **File Path**: `app/Http/Middleware/TwoFactorVerified.php`
- **Alias**: `2fa.verified`
- **Purpose**: Enforces TOTP 2FA challenge when user has enabled two-factor authentication.
- **Logic**:
  1. Checks if `$user = $request->user()` has `two_factor_enabled === true`.
  2. If true:
     - Checks session flag: `$request->session()->get('2fa_verified') === true`.
     - If not verified:
       - Saves intended URL in session.
       - Redirects to `route('two-factor.challenge')`.
  3. Exempts routes related to 2FA verification (`two-factor.challenge`, `two-factor.verify`, `logout`).
- **Routes Using It**: Applied to all authenticated panel routes (`admin`, `client`, `reseller`).

### 11. `MaintenanceMode`
- **File Path**: `app/Http/Middleware/MaintenanceMode.php`
- **Alias**: `maintenance.check`
- **Purpose**: Allows admin to put client and reseller operations into maintenance while allowing admin access.
- **Logic**:
  1. Reads `maintenance_mode` setting from cached system settings (`settings` table).
  2. If maintenance mode is active (`1` or `true`):
     - If current route starts with `/admin` or authenticated user has `role === 'admin'`, request proceeds normally.
     - If request IP is present in `maintenance_ip_whitelist` setting, request proceeds normally.
     - For API requests (`/api/*`): returns HTTP `503 Service Unavailable`:
       `{"status": "error", "code": 503, "message": "Platform undergoing scheduled maintenance. Please retry shortly."}`.
     - For Web requests: displays custom maintenance Blade view `errors.503` with custom admin announcement message and estimated completion time.
- **Routes Using It**: Web pipeline, Client routes, Reseller routes, API routes.

---

# 2. Complete Route Catalog

---

## 2.1 Public & Web Routes (`routes/web.php`)

| Method | URI | Controller@method | Middleware | Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | `App\Http\Controllers\Client\HomeController@index` | `web` | Public landing page with domain search bar |
| `GET` | `/domain/search` | `App\Http\Controllers\Client\DomainSearchController@publicSearch` | `web` | Public domain availability search interface |
| `POST` | `/domain/check` | `App\Http\Controllers\Client\DomainSearchController@checkSingle` | `web` | AJAX check single domain availability |
| `POST` | `/domain/check-bulk` | `App\Http\Controllers\Client\DomainSearchController@checkBulk` | `web` | AJAX check bulk domain availability |
| `GET` | `/whois` | `App\Http\Controllers\Client\WhoisLookupController@index` | `web` | Public WHOIS search page |
| `POST` | `/whois/query` | `App\Http\Controllers\Client\WhoisLookupController@query` | `web` | Fetch formatted public WHOIS record |
| `GET` | `/pricing` | `App\Http\Controllers\Client\PricingPageController@index` | `web` | Retail TLD pricing table (USD & BDT) |
| `GET` | `/reseller-program` | `App\Http\Controllers\Client\PricingPageController@resellerInfo` | `web` | Reseller wholesale tiers & features page |
| `GET` | `/announcements` | `App\Http\Controllers\Client\ClientAnnouncementController@publicIndex` | `web` | Public announcements & system news |
| `GET` | `/announcements/{slug}`| `App\Http\Controllers\Client\ClientAnnouncementController@publicShow` | `web` | View single announcement article |
| `GET` | `/knowledgebase` | `App\Http\Controllers\Client\KnowledgebaseController@index` | `web` | Knowledgebase category list |
| `GET` | `/knowledgebase/{category}/{slug}` | `App\Http\Controllers\Client\KnowledgebaseController@show` | `web` | View knowledgebase tutorial article |
| `GET` | `/contact` | `App\Http\Controllers\Client\ContactController@index` | `web` | Public contact inquiry form |
| `POST` | `/contact` | `App\Http\Controllers\Client\ContactController@send` | `web` | Submit contact message (reCAPTCHA verified) |
| `GET` | `/terms` | `App\Http\Controllers\Client\LegalController@terms` | `web` | Terms of Service page |
| `GET` | `/privacy` | `App\Http\Controllers\Client\LegalController@privacy` | `web` | Privacy Policy page |
| `POST` | `/currency/switch` | `App\Http\Controllers\Client\CurrencySwitchController@switch` | `web` | Switch active session currency (USD / BDT) |
| `POST` | `/payment/bkash/callback` | `App\Http\Controllers\Client\PaymentGatewayController@bkashCallback` | `web` | bKash tokenized payment gateway webhook callback |
| `POST` | `/payment/stripe/webhook` | `App\Http\Controllers\Client\PaymentGatewayController@stripeWebhook` | `web` | Stripe asynchronous event webhook listener |
| `POST` | `/payment/binance/webhook`| `App\Http\Controllers\Client\PaymentGatewayController@binanceWebhook` | `web` | Binance Pay instant payment notification webhook |

---

## 2.2 Authentication Routes (`routes/auth.php`)

| Method | URI | Controller@method | Middleware | Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/login` | `App\Http\Controllers\Auth\AuthenticatedSessionController@create` | `guest` | Show login form |
| `POST` | `/login` | `App\Http\Controllers\Auth\AuthenticatedSessionController@store` | `guest` | Authenticate user credentials & create session |
| `POST` | `/logout` | `App\Http\Controllers\Auth\AuthenticatedSessionController@destroy` | `auth` | Invalidate session & log user out |
| `GET` | `/register` | `App\Http\Controllers\Auth\RegisteredUserController@create` | `guest` | Show registration form (Client/Reseller signup) |
| `POST` | `/register` | `App\Http\Controllers\Auth\RegisteredUserController@store` | `guest` | Process user registration & send verification mail |
| `GET` | `/forgot-password` | `App\Http\Controllers\Auth\PasswordResetLinkController@create` | `guest` | Show password reset request form |
| `POST` | `/forgot-password` | `App\Http\Controllers\Auth\PasswordResetLinkController@store` | `guest` | Send reset link token to user email |
| `GET` | `/reset-password/{token}` | `App\Http\Controllers\Auth\NewPasswordController@create` | `guest` | Show new password definition form |
| `POST` | `/reset-password` | `App\Http\Controllers\Auth\NewPasswordController@store` | `guest` | Update password in database and clear tokens |
| `GET` | `/verify-email` | `App\Http\Controllers\Auth\EmailVerificationPromptController@__invoke` | `auth` | Display notice to verify email address |
| `GET` | `/verify-email/{id}/{hash}`| `App\Http\Controllers\Auth\VerifyEmailController@__invoke` | `auth, signed, throttle:6,1` | Validate signed link and mark email verified |
| `POST` | `/email/verification-notification` | `App\Http\Controllers\Auth\EmailVerificationNotificationController@store` | `auth, throttle:6,1` | Resend verification email notification |
| `GET` | `/two-factor-challenge` | `App\Http\Controllers\Auth\TwoFactorChallengeController@create` | `auth` | Prompt for TOTP 2FA authenticator code |
| `POST` | `/two-factor-challenge` | `App\Http\Controllers\Auth\TwoFactorChallengeController@store` | `auth, throttle:5,1` | Verify 2FA code / recovery code & grant session |

---

## 2.3 Admin Panel Routes (`routes/admin.php`)
*Prefix: `/admin` | Name prefix: `admin.` | Middleware: `['web', 'auth', 'active', 'admin', '2fa.verified', 'track.activity']`*

| Method | URI | Controller@method | Middleware | Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/admin/dashboard` | `App\Http\Controllers\Admin\AdminDashboardController@index` | `admin` | Admin dashboard with KPI metrics, chart stats, recent orders |
| `GET` | `/admin/clients` | `App\Http\Controllers\Admin\ClientManagementController@index` | `admin` | Searchable & filterable direct client directory |
| `GET` | `/admin/clients/create` | `App\Http\Controllers\Admin\ClientManagementController@create` | `admin` | Form to create a new client account |
| `POST` | `/admin/clients` | `App\Http\Controllers\Admin\ClientManagementController@store` | `admin` | Store newly created client record |
| `GET` | `/admin/clients/{client}` | `App\Http\Controllers\Admin\ClientManagementController@show` | `admin` | Client profile, domains, invoices, tickets, and logs |
| `GET` | `/admin/clients/{client}/edit` | `App\Http\Controllers\Admin\ClientManagementController@edit` | `admin` | Edit client demographic and contact details |
| `PUT` | `/admin/clients/{client}` | `App\Http\Controllers\Admin\ClientManagementController@update` | `admin` | Update client record in database |
| `DELETE` | `/admin/clients/{client}` | `App\Http\Controllers\Admin\ClientManagementController@destroy` | `admin` | Soft delete / archive client account |
| `POST` | `/admin/clients/{client}/status` | `App\Http\Controllers\Admin\ClientManagementController@toggleStatus` | `admin` | Suspend or reactivate client account |
| `POST` | `/admin/clients/{client}/adjust-balance` | `App\Http\Controllers\Admin\ClientManagementController@adjustBalance` | `admin` | Credit or debit client wallet balance |
| `POST` | `/admin/clients/{client}/login-as` | `App\Http\Controllers\Admin\ClientManagementController@impersonate` | `admin` | Impersonate client session for troubleshooting |
| `GET` | `/admin/resellers` | `App\Http\Controllers\Admin\ResellerManagementController@index` | `admin` | Reseller directory with balance, domain count, tier |
| `GET` | `/admin/resellers/create` | `App\Http\Controllers\Admin\ResellerManagementController@create` | `admin` | Form to create a verified reseller account |
| `POST` | `/admin/resellers` | `App\Http\Controllers\Admin\ResellerManagementController@store` | `admin` | Store new reseller account and initialize pricing tier |
| `GET` | `/admin/resellers/{reseller}` | `App\Http\Controllers\Admin\ResellerManagementController@show` | `admin` | Reseller detail page, API keys, sub-clients, profit stats |
| `GET` | `/admin/resellers/{reseller}/edit` | `App\Http\Controllers\Admin\ResellerManagementController@edit` | `admin` | Edit reseller tier, contact info, and discounts |
| `PUT` | `/admin/resellers/{reseller}` | `App\Http\Controllers\Admin\ResellerManagementController@update` | `admin` | Update reseller record |
| `DELETE` | `/admin/resellers/{reseller}` | `App\Http\Controllers\Admin\ResellerManagementController@destroy` | `admin` | Terminate reseller account |
| `POST` | `/admin/resellers/{reseller}/status` | `App\Http\Controllers\Admin\ResellerManagementController@toggleStatus` | `admin` | Suspend or reactivate reseller account |
| `POST` | `/admin/resellers/{reseller}/tier` | `App\Http\Controllers\Admin\ResellerManagementController@updateTier` | `admin` | Assign reseller pricing tier (e.g. Standard, Silver, Gold) |
| `POST` | `/admin/resellers/{reseller}/adjust-balance` | `App\Http\Controllers\Admin\ResellerManagementController@adjustBalance` | `admin` | Credit or debit reseller wholesale wallet balance |
| `GET` | `/admin/resellers/{reseller}/api-keys` | `App\Http\Controllers\Admin\ResellerManagementController@apiKeys` | `admin` | View all API keys generated by reseller |
| `POST` | `/admin/resellers/{reseller}/api-keys/{apiKey}/revoke`| `App\Http\Controllers\Admin\ResellerManagementController@revokeApiKey` | `admin` | Force revoke reseller API key |
| `GET` | `/admin/domains` | `App\Http\Controllers\Admin\DomainManagementController@index` | `admin` | Master domain registry across all users and registrars |
| `GET` | `/admin/domains/{domain}` | `App\Http\Controllers\Admin\DomainManagementController@show` | `admin` | Domain management hub: WHOIS, DNS, registrar sync status |
| `GET` | `/admin/domains/{domain}/edit` | `App\Http\Controllers\Admin\DomainManagementController@edit` | `admin` | Modify internal domain parameters, dates, registrant |
| `PUT` | `/admin/domains/{domain}` | `App\Http\Controllers\Admin\DomainManagementController@update` | `admin` | Save manual updates to domain entity |
| `POST` | `/admin/domains/{domain}/sync` | `App\Http\Controllers\Admin\DomainManagementController@syncWithRegistrar`| `admin` | Poll registrar API for live status, expiry, and lock flags |
| `POST` | `/admin/domains/{domain}/renew` | `App\Http\Controllers\Admin\DomainManagementController@renew` | `admin` | Force registrar domain renewal for specified years |
| `POST` | `/admin/domains/{domain}/toggle-lock` | `App\Http\Controllers\Admin\DomainManagementController@toggleLock` | `admin` | Toggle registrar lock flag (transfer lock) |
| `POST` | `/admin/domains/{domain}/toggle-privacy`| `App\Http\Controllers\Admin\DomainManagementController@togglePrivacy` | `admin` | Toggle ID privacy protection with registrar |
| `POST` | `/admin/domains/{domain}/get-epp` | `App\Http\Controllers\Admin\DomainManagementController@fetchEppCode` | `admin` | Retrieve live EPP authorization code from registrar |
| `POST` | `/admin/domains/{domain}/nameservers` | `App\Http\Controllers\Admin\DomainManagementController@updateNameservers` | `admin` | Push authoritative nameservers changes to registrar |
| `POST` | `/admin/domains/{domain}/transfer-sync`| `App\Http\Controllers\Admin\DomainManagementController@syncTransferStatus` | `admin` | Poll registrar for pending incoming domain transfer status |
| `DELETE` | `/admin/domains/{domain}` | `App\Http\Controllers\Admin\DomainManagementController@destroy` | `admin` | Delete domain from local database (does not cancel at registry) |
| `GET` | `/admin/tlds` | `App\Http\Controllers\Admin\TldPricingController@index` | `admin` | TLD pricing matrix with registrar mappings and profit margins |
| `GET` | `/admin/tlds/create` | `App\Http\Controllers\Admin\TldPricingController@create` | `admin` | Form to activate a new TLD extension |
| `POST` | `/admin/tlds` | `App\Http\Controllers\Admin\TldPricingController@store` | `admin` | Store new TLD extension and pricing rules |
| `GET` | `/admin/tlds/{tld}/edit` | `App\Http\Controllers\Admin\TldPricingController@edit` | `admin` | Edit register/renew/transfer prices and assigned registrar |
| `PUT` | `/admin/tlds/{tld}` | `App\Http\Controllers\Admin\TldPricingController@update` | `admin` | Save updated TLD pricing |
| `DELETE` | `/admin/tlds/{tld}` | `App\Http\Controllers\Admin\TldPricingController@destroy` | `admin` | Disable / delete TLD configuration |
| `POST` | `/admin/tlds/bulk-update` | `App\Http\Controllers\Admin\TldPricingController@bulkUpdate` | `admin` | Batch update prices across multiple TLDs by percentage or flat rate |
| `POST` | `/admin/tlds/sync-registrar-prices` | `App\Http\Controllers\Admin\TldPricingController@syncRegistrarPrices` | `admin` | Fetch live wholesale costs from registrar APIs |
| `GET` | `/admin/registrars` | `App\Http\Controllers\Admin\RegistrarModuleController@index` | `admin` | Registrar modules directory (Namecheap, Spaceship, Dynadot, etc.) |
| `GET` | `/admin/registrars/{registrar}/edit` | `App\Http\Controllers\Admin\RegistrarModuleController@edit` | `admin` | Configure registrar API credentials and sandbox toggle |
| `PUT` | `/admin/registrars/{registrar}` | `App\Http\Controllers\Admin\RegistrarModuleController@update` | `admin` | Save registrar API keys, username, and endpoints |
| `POST` | `/admin/registrars/{registrar}/test-connection` | `App\Http\Controllers\Admin\RegistrarModuleController@testConnection` | `admin` | Test API connectivity and authentication with registrar |
| `POST` | `/admin/registrars/{registrar}/sync-balance` | `App\Http\Controllers\Admin\RegistrarModuleController@syncBalance` | `admin` | Fetch live credit balance from registrar account |
| `POST` | `/admin/registrars/{registrar}/toggle-status` | `App\Http\Controllers\Admin\RegistrarModuleController@toggleStatus` | `admin` | Enable or disable registrar module |
| `POST` | `/admin/registrars/set-default` | `App\Http\Controllers\Admin\RegistrarModuleController@setDefault` | `admin` | Set primary fallback registrar module |
| `GET` | `/admin/orders` | `App\Http\Controllers\Admin\OrderManagementController@index` | `admin` | Domain order history (Registration, Renewal, Transfer) |
| `GET` | `/admin/orders/{order}` | `App\Http\Controllers\Admin\OrderManagementController@show` | `admin` | Order details, registrar provisioning logs, error stack |
| `POST` | `/admin/orders/{order}/retry-provision` | `App\Http\Controllers\Admin\OrderManagementController@retryProvision` | `admin` | Manually re-trigger failed registrar provisioning job |
| `POST` | `/admin/orders/{order}/cancel` | `App\Http\Controllers\Admin\OrderManagementController@cancel` | `admin` | Cancel order and refund customer wallet |
| `GET` | `/admin/invoices` | `App\Http\Controllers\Admin\InvoiceManagementController@index` | `admin` | Invoice listing (Paid, Unpaid, Refunded, Cancelled) |
| `GET` | `/admin/invoices/create` | `App\Http\Controllers\Admin\InvoiceManagementController@create` | `admin` | Create custom manual invoice |
| `POST` | `/admin/invoices` | `App\Http\Controllers\Admin\InvoiceManagementController@store` | `admin` | Save and dispatch manual invoice to customer |
| `GET` | `/admin/invoices/{invoice}` | `App\Http\Controllers\Admin\InvoiceManagementController@show` | `admin` | View invoice details and payment attempts |
| `GET` | `/admin/invoices/{invoice}/edit` | `App\Http\Controllers\Admin\InvoiceManagementController@edit` | `admin` | Edit invoice items, discounts, and due date |
| `PUT` | `/admin/invoices/{invoice}` | `App\Http\Controllers\Admin\InvoiceManagementController@update` | `admin` | Save modified invoice |
| `POST` | `/admin/invoices/{invoice}/mark-paid` | `App\Http\Controllers\Admin\InvoiceManagementController@markPaid` | `admin` | Mark invoice paid manually and trigger service delivery |
| `POST` | `/admin/invoices/{invoice}/cancel` | `App\Http\Controllers\Admin\InvoiceManagementController@cancel` | `admin` | Cancel unpaid invoice |
| `POST` | `/admin/invoices/{invoice}/refund` | `App\Http\Controllers\Admin\InvoiceManagementController@refund` | `admin` | Issue refund to customer wallet or gateway |
| `GET` | `/admin/invoices/{invoice}/pdf` | `App\Http\Controllers\Admin\InvoiceManagementController@generatePdf` | `admin` | Stream / download printable PDF invoice |
| `GET` | `/admin/transactions` | `App\Http\Controllers\Admin\TransactionManagementController@index` | `admin` | Master audit log of all financial transactions |
| `GET` | `/admin/transactions/{transaction}` | `App\Http\Controllers\Admin\TransactionManagementController@show` | `admin` | Transaction ledger entry and gateway payload |
| `GET` | `/admin/payments/manual-requests` | `App\Http\Controllers\Admin\ManualPaymentController@index` | `admin` | Pending manual bKash/Nagad/Bank slip verification queue |
| `POST` | `/admin/payments/manual-requests/{id}/approve` | `App\Http\Controllers\Admin\ManualPaymentController@approve` | `admin` | Approve manual deposit & credit user balance |
| `POST` | `/admin/payments/manual-requests/{id}/reject` | `App\Http\Controllers\Admin\ManualPaymentController@reject` | `admin` | Reject manual payment submission with reason note |
| `GET` | `/admin/tickets` | `App\Http\Controllers\Admin\SupportTicketController@index` | `admin` | Ticket inbox filterable by department, priority, status |
| `GET` | `/admin/tickets/{ticket}` | `App\Http\Controllers\Admin\SupportTicketController@show` | `admin` | View ticket conversation history and internal staff notes |
| `POST` | `/admin/tickets/{ticket}/reply` | `App\Http\Controllers\Admin\SupportTicketController@reply` | `admin` | Post staff reply or private note to ticket |
| `POST` | `/admin/tickets/{ticket}/status` | `App\Http\Controllers\Admin\SupportTicketController@updateStatus` | `admin` | Change ticket status (In Progress, Resolved, Closed) |
| `POST` | `/admin/tickets/{ticket}/assign` | `App\Http\Controllers\Admin\SupportTicketController@assign` | `admin` | Assign ticket to specific staff member |
| `DELETE` | `/admin/tickets/{ticket}` | `App\Http\Controllers\Admin\SupportTicketController@destroy` | `admin` | Delete support ticket thread |
| `GET` | `/admin/email-templates` | `App\Http\Controllers\Admin\EmailTemplateController@index` | `admin` | Automated email notification template directory |
| `GET` | `/admin/email-templates/{template}/edit` | `App\Http\Controllers\Admin\EmailTemplateController@edit` | `admin` | Edit email subject, blade body, and variables |
| `PUT` | `/admin/email-templates/{template}` | `App\Http\Controllers\Admin\EmailTemplateController@update` | `admin` | Save modified email template |
| `POST` | `/admin/email-templates/{template}/send-test` | `App\Http\Controllers\Admin\EmailTemplateController@sendTest` | `admin` | Send test email to admin inbox |
| `GET` | `/admin/promo-codes` | `App\Http\Controllers\Admin\PromoCodeController@index` | `admin` | Promo code / discount coupons listing |
| `GET` | `/admin/promo-codes/create` | `App\Http\Controllers\Admin\PromoCodeController@create` | `admin` | Form to create coupon code |
| `POST` | `/admin/promo-codes` | `App\Http\Controllers\Admin\PromoCodeController@store` | `admin` | Store coupon rules (max usage, expiry, TLD specific) |
| `GET` | `/admin/promo-codes/{promoCode}/edit` | `App\Http\Controllers\Admin\PromoCodeController@edit` | `admin` | Edit coupon parameters |
| `PUT` | `/admin/promo-codes/{promoCode}` | `App\Http\Controllers\Admin\PromoCodeController@update` | `admin` | Save coupon changes |
| `DELETE` | `/admin/promo-codes/{promoCode}` | `App\Http\Controllers\Admin\PromoCodeController@destroy` | `admin` | Delete coupon code |
| `GET` | `/admin/announcements` | `App\Http\Controllers\Admin\AnnouncementController@index` | `admin` | Broadcast announcements management |
| `POST` | `/admin/announcements` | `App\Http\Controllers\Admin\AnnouncementController@store` | `admin` | Publish new broadcast announcement |
| `PUT` | `/admin/announcements/{announcement}` | `App\Http\Controllers\Admin\AnnouncementController@update` | `admin` | Update announcement content or visibility |
| `DELETE` | `/admin/announcements/{announcement}` | `App\Http\Controllers\Admin\AnnouncementController@destroy` | `admin` | Delete announcement |
| `GET` | `/admin/activity-logs` | `App\Http\Controllers\Admin\ActivityLogController@index` | `admin` | System activity and audit trail log viewer |
| `GET` | `/admin/activity-logs/{id}` | `App\Http\Controllers\Admin\ActivityLogController@show` | `admin` | Detailed view of request payload and trace |
| `DELETE` | `/admin/activity-logs/clear-old` | `App\Http\Controllers\Admin\ActivityLogController@clearOld` | `admin` | Prune activity logs older than 90 days |
| `GET` | `/admin/settings/general` | `App\Http\Controllers\Admin\SystemSettingController@general` | `admin` | General site title, logo, contact, footer settings |
| `PUT` | `/admin/settings/general` | `App\Http\Controllers\Admin\SystemSettingController@updateGeneral` | `admin` | Save general configuration changes |
| `GET` | `/admin/settings/currencies` | `App\Http\Controllers\Admin\CurrencySettingController@index` | `admin` | Currencies list (USD base, BDT conversion rates) |
| `POST` | `/admin/settings/currencies` | `App\Http\Controllers\Admin\CurrencySettingController@store` | `admin` | Add supported fiat currency |
| `PUT` | `/admin/settings/currencies/{currency}` | `App\Http\Controllers\Admin\CurrencySettingController@update` | `admin` | Update conversion rate and symbol format |
| `POST` | `/admin/settings/currencies/update-rates` | `App\Http\Controllers\Admin\CurrencySettingController@updateRates` | `admin` | Fetch live USD/BDT exchange rate via ExchangeRate API |
| `GET` | `/admin/settings/email` | `App\Http\Controllers\Admin\SystemSettingController@email` | `admin` | SMTP mail server setup and sender credentials |
| `PUT` | `/admin/settings/email` | `App\Http\Controllers\Admin\SystemSettingController@updateEmail` | `admin` | Save SMTP host, port, username, password |
| `POST` | `/admin/settings/email/test` | `App\Http\Controllers\Admin\SystemSettingController@testEmail` | `admin` | Send test email via configured SMTP transport |
| `GET` | `/admin/settings/security` | `App\Http\Controllers\Admin\SystemSettingController@security` | `admin` | 2FA requirements, rate limits, session timeout settings |
| `PUT` | `/admin/settings/security` | `App\Http\Controllers\Admin\SystemSettingController@updateSecurity` | `admin` | Save security and firewall rules |
| `GET` | `/admin/reports/revenue` | `App\Http\Controllers\Admin\ReportController@revenue` | `admin` | Financial revenue report with breakdown by gateway and date |
| `GET` | `/admin/reports/domains` | `App\Http\Controllers\Admin\ReportController@domains` | `admin` | Domain volume analytics, registration vs transfer trends |
| `GET` | `/admin/reports/clients` | `App\Http\Controllers\Admin\ReportController@clients` | `admin` | Client acquisition, spending trends, retention rates |
| `GET` | `/admin/reports/resellers` | `App\Http\Controllers\Admin\ReportController@resellers` | `admin` | Reseller wholesale performance and API throughput analysis |
| `GET` | `/admin/reports/tlds` | `App\Http\Controllers\Admin\ReportController@tlds` | `admin` | Most profitable and popular TLD performance report |
| `GET` | `/admin/reports/payments` | `App\Http\Controllers\Admin\ReportController@payments` | `admin` | Gateway success/failure rates and processing fees report |

---

## 2.4 Client Panel Routes (`routes/client.php`)
*Prefix: `/client` | Name prefix: `client.` | Middleware: `['web', 'auth', 'active', 'client', 'verified', '2fa.verified', 'track.activity']`*

| Method | URI | Controller@method | Middleware | Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/client/dashboard` | `App\Http\Controllers\Client\ClientDashboardController@index` | `client` | Client dashboard (domains summary, pending invoices, tickets) |
| `GET` | `/client/profile` | `App\Http\Controllers\Client\ClientProfileController@edit` | `client` | Edit personal contact details and address |
| `PUT` | `/client/profile` | `App\Http\Controllers\Client\ClientProfileController@update` | `client` | Save profile changes |
| `PUT` | `/client/password` | `App\Http\Controllers\Client\ClientProfileController@updatePassword` | `client` | Change account password |
| `GET` | `/client/two-factor` | `App\Http\Controllers\Client\TwoFactorAuthController@index` | `client` | 2FA security settings and QR code generation |
| `POST` | `/client/two-factor/enable` | `App\Http\Controllers\Client\TwoFactorAuthController@enable` | `client` | Generate TOTP secret and show QR code |
| `POST` | `/client/two-factor/confirm`| `App\Http\Controllers\Client\TwoFactorAuthController@confirm` | `client` | Confirm TOTP code and activate 2FA protection |
| `DELETE` | `/client/two-factor/disable` | `App\Http\Controllers\Client\TwoFactorAuthController@disable` | `client` | Disable 2FA using current password |
| `GET` | `/client/sessions` | `App\Http\Controllers\Client\SessionController@index` | `client` | View active browser sessions and device locations |
| `DELETE` | `/client/sessions/{id}` | `App\Http\Controllers\Client\SessionController@destroy` | `client` | Revoke specific session device |
| `GET` | `/client/domains/search` | `App\Http\Controllers\Client\DomainSearchController@index` | `client` | Client domain registration search interface |
| `POST` | `/client/domains/check` | `App\Http\Controllers\Client\DomainSearchController@check` | `client` | AJAX availability check for client |
| `GET` | `/client/domains/register` | `App\Http\Controllers\Client\DomainRegistrationController@create` | `client` | Domain registration configuration (years, nameservers, addons) |
| `POST` | `/client/domains/register` | `App\Http\Controllers\Client\DomainRegistrationController@store` | `client` | Place domain registration order & create invoice |
| `GET` | `/client/domains/transfer` | `App\Http\Controllers\Client\DomainTransferController@create` | `client` | Domain transfer submission form (Domain name & EPP code) |
| `POST` | `/client/domains/transfer` | `App\Http\Controllers\Client\DomainTransferController@store` | `client` | Place domain transfer order & create invoice |
| `GET` | `/client/domains` | `App\Http\Controllers\Client\ClientDomainController@index` | `client` | List all active, expired, and pending domains |
| `GET` | `/client/domains/{domain}` | `App\Http\Controllers\Client\ClientDomainController@show` | `client` | Domain overview hub, expiry date, auto-renew status |
| `POST` | `/client/domains/{domain}/renew` | `App\Http\Controllers\Client\ClientDomainController@renew` | `client` | Order domain renewal for 1-10 years |
| `POST` | `/client/domains/{domain}/toggle-autorenew` | `App\Http\Controllers\Client\ClientDomainController@toggleAutoRenew` | `client` | Toggle auto-renewal preference flag in database |
| `POST` | `/client/domains/{domain}/toggle-lock` | `App\Http\Controllers\Client\ClientDomainController@toggleLock` | `client` | Enable or disable registrar transfer lock |
| `POST` | `/client/domains/{domain}/toggle-privacy` | `App\Http\Controllers\Client\ClientDomainController@togglePrivacy` | `client` | Toggle WHOIS ID protection addon |
| `POST` | `/client/domains/{domain}/epp` | `App\Http\Controllers\Client\ClientDomainController@fetchEppCode` | `client` | Send EPP code to registrant email address |
| `GET` | `/client/domains/{domain}/nameservers` | `App\Http\Controllers\Client\NameserverController@edit` | `client` | View current authoritative nameservers |
| `PUT` | `/client/domains/{domain}/nameservers` | `App\Http\Controllers\Client\NameserverController@update` | `client` | Update authoritative nameservers with registrar |
| `GET` | `/client/domains/{domain}/dns-records` | `App\Http\Controllers\Client\DnsRecordController@index` | `client` | Manage hosted DNS zone records (A, AAAA, CNAME, MX, TXT) |
| `POST` | `/client/domains/{domain}/dns-records` | `App\Http\Controllers\Client\DnsRecordController@store` | `client` | Add new DNS record to hosted zone |
| `PUT` | `/client/domains/{domain}/dns-records/{recordId}` | `App\Http\Controllers\Client\DnsRecordController@update` | `client` | Update existing DNS record |
| `DELETE` | `/client/domains/{domain}/dns-records/{recordId}` | `App\Http\Controllers\Client\DnsRecordController@destroy` | `client` | Delete DNS record from hosted zone |
| `GET` | `/client/domains/{domain}/child-nameservers` | `App\Http\Controllers\Client\ChildNameserverController@index` | `client` | View registered child nameservers (glue records) |
| `POST` | `/client/domains/{domain}/child-nameservers` | `App\Http\Controllers\Client\ChildNameserverController@store` | `client` | Register new child host (e.g., `ns1.mydomain.com` -> IP) |
| `PUT` | `/client/domains/{domain}/child-nameservers/{nsId}` | `App\Http\Controllers\Client\ChildNameserverController@update` | `client` | Update registered child nameserver IP |
| `DELETE` | `/client/domains/{domain}/child-nameservers/{nsId}` | `App\Http\Controllers\Client\ChildNameserverController@destroy` | `client` | Delete child nameserver registration at registrar |
| `GET` | `/client/domains/{domain}/whois` | `App\Http\Controllers\Client\WhoisContactController@edit` | `client` | View Registrant, Admin, Technical, and Billing contacts |
| `PUT` | `/client/domains/{domain}/whois` | `App\Http\Controllers\Client\WhoisContactController@update` | `client` | Update WHOIS contact information at registrar |
| `GET` | `/client/domains/{domain}/email-forwarding` | `App\Http\Controllers\Client\EmailForwardingController@index` | `client` | View email alias forwarders |
| `POST` | `/client/domains/{domain}/email-forwarding` | `App\Http\Controllers\Client\EmailForwardingController@store` | `client` | Add email forwarder (`sales@mydomain.com` -> `me@gmail.com`) |
| `DELETE` | `/client/domains/{domain}/email-forwarding/{id}` | `App\Http\Controllers\Client\EmailForwardingController@destroy` | `client` | Remove email forwarder |
| `GET` | `/client/domains/{domain}/url-forwarding` | `App\Http\Controllers\Client\UrlForwardingController@index` | `client` | View URL redirects (301, 302, masked) |
| `POST` | `/client/domains/{domain}/url-forwarding` | `App\Http\Controllers\Client\UrlForwardingController@store` | `client` | Create URL redirection rule |
| `DELETE` | `/client/domains/{domain}/url-forwarding/{id}` | `App\Http\Controllers\Client\UrlForwardingController@destroy` | `client` | Delete URL redirection rule |
| `GET` | `/client/invoices` | `App\Http\Controllers\Client\ClientInvoiceController@index` | `client` | Invoice history and payment status |
| `GET` | `/client/invoices/{invoice}` | `App\Http\Controllers\Client\ClientInvoiceController@show` | `client` | View invoice details and select payment method |
| `POST` | `/client/invoices/{invoice}/pay` | `App\Http\Controllers\Client\ClientInvoiceController@pay` | `client` | Process invoice payment via wallet or gateway |
| `GET` | `/client/wallet` | `App\Http\Controllers\Client\ClientWalletController@index` | `client` | Wallet balance, deposit funds form, transaction log |
| `POST` | `/client/wallet/add-funds` | `App\Http\Controllers\Client\ClientWalletController@addFunds` | `client` | Initiate deposit request via bKash, Stripe, Binance, Manual |
| `GET` | `/client/transactions` | `App\Http\Controllers\Client\ClientWalletController@transactions`| `client` | Financial ledger and audit receipts |
| `GET` | `/client/tickets` | `App\Http\Controllers\Client\ClientTicketController@index` | `client` | Support tickets list |
| `GET` | `/client/tickets/create` | `App\Http\Controllers\Client\ClientTicketController@create` | `client` | Open new support ticket form |
| `POST` | `/client/tickets` | `App\Http\Controllers\Client\ClientTicketController@store` | `client` | Submit support ticket with attachments |
| `GET` | `/client/tickets/{ticket}` | `App\Http\Controllers\Client\ClientTicketController@show` | `client` | View ticket conversation |
| `POST` | `/client/tickets/{ticket}/reply` | `App\Http\Controllers\Client\ClientTicketController@reply` | `client` | Post reply to existing ticket |
| `POST` | `/client/tickets/{ticket}/close` | `App\Http\Controllers\Client\ClientTicketController@close` | `client` | Mark support ticket resolved/closed |
| `GET` | `/client/announcements` | `App\Http\Controllers\Client\ClientAnnouncementController@index` | `client` | Client announcements and maintenance notices |
| `GET` | `/client/announcements/{id}` | `App\Http\Controllers\Client\ClientAnnouncementController@show` | `client` | Read single announcement |

---

## 2.5 Reseller Panel Routes (`routes/reseller.php`)
*Prefix: `/reseller` | Name prefix: `reseller.` | Middleware: `['web', 'auth', 'active', 'reseller', 'verified', '2fa.verified', 'track.activity']`*

*Note: Resellers also have full access to all standard domain management routes listed in the Client section above.*

| Method | URI | Controller@method | Middleware | Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/reseller/dashboard` | `App\Http\Controllers\Reseller\ResellerDashboardController@index` | `reseller` | Reseller metrics: API calls, active domains, wallet, profit |
| `GET` | `/reseller/pricing` | `App\Http\Controllers\Reseller\ResellerPricingController@index` | `reseller` | View assigned wholesale TLD pricing and margins |
| `GET` | `/reseller/api-keys` | `App\Http\Controllers\Reseller\ApiKeyController@index` | `reseller` | API credentials management list |
| `POST` | `/reseller/api-keys` | `App\Http\Controllers\Reseller\ApiKeyController@store` | `reseller` | Generate new API key and secret with IP restrictions |
| `PUT` | `/reseller/api-keys/{id}` | `App\Http\Controllers\Reseller\ApiKeyController@update` | `reseller` | Update key name, IP whitelist, or rate limit |
| `DELETE` | `/reseller/api-keys/{id}` | `App\Http\Controllers\Reseller\ApiKeyController@destroy` | `reseller` | Revoke / delete API key |
| `POST` | `/reseller/api-keys/{id}/regenerate-secret` | `App\Http\Controllers\Reseller\ApiKeyController@regenerateSecret` | `reseller` | Regenerate API secret key |
| `GET` | `/reseller/api-logs` | `App\Http\Controllers\Reseller\ApiKeyController@logs` | `reseller` | API request history, response codes, and debug payloads |
| `GET` | `/reseller/api-docs` | `App\Http\Controllers\Reseller\ApiKeyController@docs` | `reseller` | Interactive API documentation and WHMCS module guide |
| `GET` | `/reseller/webhooks` | `App\Http\Controllers\Reseller\WebhookController@index` | `reseller` | Webhook endpoint configuration list |
| `POST` | `/reseller/webhooks` | `App\Http\Controllers\Reseller\WebhookController@store` | `reseller` | Register new webhook URL for domain lifecycle events |
| `PUT` | `/reseller/webhooks/{id}` | `App\Http\Controllers\Reseller\WebhookController@update` | `reseller` | Update webhook target URL or subscribed events |
| `DELETE` | `/reseller/webhooks/{id}` | `App\Http\Controllers\Reseller\WebhookController@destroy` | `reseller` | Delete webhook endpoint |
| `POST` | `/reseller/webhooks/{id}/test` | `App\Http\Controllers\Reseller\WebhookController@test` | `reseller` | Dispatch ping test event to webhook endpoint |
| `GET` | `/reseller/sub-clients` | `App\Http\Controllers\Reseller\SubClientController@index` | `reseller` | View end-clients registered under reseller orders |
| `GET` | `/reseller/sub-clients/{id}` | `App\Http\Controllers\Reseller\SubClientController@show` | `reseller` | Sub-client details and associated domains |
| `POST` | `/reseller/sub-clients/{id}/status` | `App\Http\Controllers\Reseller\SubClientController@toggleStatus` | `reseller` | Suspend or reactivate sub-client |
| `GET` | `/reseller/reports/domains` | `App\Http\Controllers\Reseller\ResellerReportController@domains` | `reseller` | Domain portfolio report and upcoming expirations |
| `GET` | `/reseller/reports/revenue` | `App\Http\Controllers\Reseller\ResellerReportController@revenue` | `reseller` | Wholesale spending and profit margin report |
| `GET` | `/reseller/reports/api-usage`| `App\Http\Controllers\Reseller\ResellerReportController@apiUsage` | `reseller` | API endpoint throughput and latency analytics |
| `GET` | `/reseller/whitelabel` | `App\Http\Controllers\Reseller\WhitelabelController@index` | `reseller` | White-label settings (branded default NS, company WHOIS) |
| `PUT` | `/reseller/whitelabel` | `App\Http\Controllers\Reseller\WhitelabelController@update` | `reseller` | Save white-label nameservers and branding defaults |

---

## 2.6 External Reseller REST API v1 (`routes/api.php`)
*Prefix: `/api/v1` | Middleware: `['api', 'api.auth', 'api.ratelimit', 'api.ip_whitelist']`*  
*Total Endpoints: 44 REST API Endpoints for WHMCS / Blesta / Custom integrations*

| Method | URI | Controller@method | Description |
| :--- | :--- | :--- | :--- |
| **Auth & Account** | | | |
| `GET` | `/api/v1/auth/test` | `App\Http\Controllers\Api\V1\ApiAuthController@test` | Verify API key authentication & connection |
| `GET` | `/api/v1/account/info` | `App\Http\Controllers\Api\V1\ApiAccountController@info` | Reseller account details and pricing tier |
| `GET` | `/api/v1/account/balance` | `App\Http\Controllers\Api\V1\ApiAccountController@balance` | Live wallet credit balance in USD & BDT |
| **TLD Pricing & Search** | | | |
| `GET` | `/api/v1/tlds` | `App\Http\Controllers\Api\V1\ApiTldPricingController@index` | Complete wholesale TLD pricing matrix |
| `GET` | `/api/v1/tlds/{tld}/pricing` | `App\Http\Controllers\Api\V1\ApiTldPricingController@show` | Pricing for specific TLD extension |
| `GET` | `/api/v1/domains/check` | `App\Http\Controllers\Api\V1\ApiDomainCheckController@check` | Check availability for a single domain name |
| `POST` | `/api/v1/domains/check-bulk` | `App\Http\Controllers\Api\V1\ApiDomainCheckController@checkBulk` | Check availability for up to 50 domains at once |
| `GET` | `/api/v1/domains/suggestions` | `App\Http\Controllers\Api\V1\ApiDomainCheckController@suggestions` | AI/Algorithmic domain name recommendations |
| **Domain Lifecycle** | | | |
| `GET` | `/api/v1/domains` | `App\Http\Controllers\Api\V1\ApiDomainController@index` | Paginated list of all reseller's domains |
| `POST` | `/api/v1/domains/register` | `App\Http\Controllers\Api\V1\ApiDomainController@register` | Register new domain (deducts wholesale balance) |
| `POST` | `/api/v1/domains/transfer` | `App\Http\Controllers\Api\V1\ApiDomainController@transfer` | Initiate domain transfer with EPP authorization code |
| `GET` | `/api/v1/domains/{domain}` | `App\Http\Controllers\Api\V1\ApiDomainController@show` | Get single domain details, status, and expiry |
| `POST` | `/api/v1/domains/{domain}/renew` | `App\Http\Controllers\Api\V1\ApiDomainController@renew` | Renew registered domain for 1-10 years |
| `POST` | `/api/v1/domains/{domain}/cancel` | `App\Http\Controllers\Api\V1\ApiDomainController@cancel` | Request cancellation / deletion of domain |
| `GET` | `/api/v1/domains/{domain}/auth-code` | `App\Http\Controllers\Api\V1\ApiDomainController@authCode` | Retrieve domain EPP / transfer authorization code |
| `POST` | `/api/v1/domains/{domain}/lock` | `App\Http\Controllers\Api\V1\ApiDomainController@enableLock` | Enable registrar transfer lock |
| `DELETE` | `/api/v1/domains/{domain}/lock` | `App\Http\Controllers\Api\V1\ApiDomainController@disableLock`| Disable registrar transfer lock |
| `POST` | `/api/v1/domains/{domain}/privacy` | `App\Http\Controllers\Api\V1\ApiDomainController@enablePrivacy` | Enable ID privacy protection addon |
| `DELETE` | `/api/v1/domains/{domain}/privacy` | `App\Http\Controllers\Api\V1\ApiDomainController@disablePrivacy`| Disable ID privacy protection addon |
| `POST` | `/api/v1/domains/{domain}/auto-renew` | `App\Http\Controllers\Api\V1\ApiDomainController@enableAutoRenew`| Enable automatic renewal on expiry |
| `DELETE` | `/api/v1/domains/{domain}/auto-renew` | `App\Http\Controllers\Api\V1\ApiDomainController@disableAutoRenew`| Disable automatic renewal |
| **Nameserver Management** | | | |
| `GET` | `/api/v1/domains/{domain}/nameservers` | `App\Http\Controllers\Api\V1\ApiNameserverController@index` | Get current authoritative nameservers |
| `PUT` | `/api/v1/domains/{domain}/nameservers` | `App\Http\Controllers\Api\V1\ApiNameserverController@update` | Update authoritative nameservers (2 to 5 NS) |
| **Child Nameservers (Glue)** | | | |
| `GET` | `/api/v1/domains/{domain}/child-nameservers` | `App\Http\Controllers\Api\V1\ApiChildNameserverController@index` | List registered child host glue records |
| `POST` | `/api/v1/domains/{domain}/child-nameservers` | `App\Http\Controllers\Api\V1\ApiChildNameserverController@store` | Register new child host (e.g. `ns1.example.com` + IP) |
| `PUT` | `/api/v1/domains/{domain}/child-nameservers/{hostname}` | `App\Http\Controllers\Api\V1\ApiChildNameserverController@update` | Update IP address of existing child nameserver |
| `DELETE` | `/api/v1/domains/{domain}/child-nameservers/{hostname}` | `App\Http\Controllers\Api\V1\ApiChildNameserverController@destroy` | Delete child nameserver registration |
| **DNS Record Management** | | | |
| `GET` | `/api/v1/domains/{domain}/dns` | `App\Http\Controllers\Api\V1\ApiDnsController@index` | Get all DNS zone records |
| `POST` | `/api/v1/domains/{domain}/dns` | `App\Http\Controllers\Api\V1\ApiDnsController@store` | Add new DNS record (A, AAAA, CNAME, MX, TXT) |
| `PUT` | `/api/v1/domains/{domain}/dns/{recordId}` | `App\Http\Controllers\Api\V1\ApiDnsController@update` | Update existing DNS record |
| `DELETE` | `/api/v1/domains/{domain}/dns/{recordId}` | `App\Http\Controllers\Api\V1\ApiDnsController@destroy` | Delete DNS record |
| **WHOIS & Contacts** | | | |
| `GET` | `/api/v1/domains/{domain}/contacts` | `App\Http\Controllers\Api\V1\ApiWhoisController@show` | Get Registrant, Admin, Tech, and Billing contacts |
| `PUT` | `/api/v1/domains/{domain}/contacts` | `App\Http\Controllers\Api\V1\ApiWhoisController@update` | Update WHOIS contact details at registry |
| `GET` | `/api/v1/whois/{domain}` | `App\Http\Controllers\Api\V1\ApiWhoisController@lookup` | Live WHOIS raw/parsed lookup |
| **Email & URL Forwarding** | | | |
| `GET` | `/api/v1/domains/{domain}/email-forwards` | `App\Http\Controllers\Api\V1\ApiEmailForwardController@index` | List email forwarders for domain |
| `POST` | `/api/v1/domains/{domain}/email-forwards` | `App\Http\Controllers\Api\V1\ApiEmailForwardController@store` | Add email forwarder |
| `DELETE` | `/api/v1/domains/{domain}/email-forwards/{forwardId}` | `App\Http\Controllers\Api\V1\ApiEmailForwardController@destroy`| Delete email forwarder |
| `GET` | `/api/v1/domains/{domain}/url-forwards` | `App\Http\Controllers\Api\V1\ApiUrlForwardController@index` | List URL redirect rules |
| `POST` | `/api/v1/domains/{domain}/url-forwards` | `App\Http\Controllers\Api\V1\ApiUrlForwardController@store` | Add URL redirect rule |
| `DELETE` | `/api/v1/domains/{domain}/url-forwards/{forwardId}` | `App\Http\Controllers\Api\V1\ApiUrlForwardController@destroy`| Delete URL redirect rule |
| **Billing & Orders** | | | |
| `GET` | `/api/v1/invoices` | `App\Http\Controllers\Api\V1\ApiBillingController@invoices` | List reseller invoices |
| `GET` | `/api/v1/invoices/{id}` | `App\Http\Controllers\Api\V1\ApiBillingController@invoice` | Get invoice details |
| `GET` | `/api/v1/transactions` | `App\Http\Controllers\Api\V1\ApiBillingController@transactions` | Reseller wallet transaction history |
| `GET` | `/api/v1/orders` | `App\Http\Controllers\Api\V1\ApiBillingController@orders` | Reseller domain order queue and statuses |
| `GET` | `/api/v1/orders/{id}` | `App\Http\Controllers\Api\V1\ApiBillingController@order` | Get single order status and provisioning response |
| **Webhooks Management** | | | |
| `GET` | `/api/v1/webhooks` | `App\Http\Controllers\Api\V1\ApiWebhookController@index` | List registered webhook listeners |
| `POST` | `/api/v1/webhooks` | `App\Http\Controllers\Api\V1\ApiWebhookController@store` | Register new webhook listener URL |
| `DELETE` | `/api/v1/webhooks/{id}` | `App\Http\Controllers\Api\V1\ApiWebhookController@destroy` | Delete registered webhook listener |
| `POST` | `/api/v1/webhooks/{id}/test` | `App\Http\Controllers\Api\V1\ApiWebhookController@test` | Dispatch mock event to webhook |

---

# 3. Complete Controller Specifications

All controllers extend `App\Http\Controllers\Controller`. Dependency injection is used via constructor or method parameters.

---

## 3.1 Auth Controllers (`App\Http\Controllers\Auth\`)

### 1. `AuthenticatedSessionController`
- **Namespace**: `App\Http\Controllers\Auth`
- **Methods**:
  - `create(): \Illuminate\View\View`  
    Renders the authentication login view with optional redirect parameters.
  - `store(App\Http\Requests\Auth\LoginRequest $request): \Illuminate\Http\RedirectResponse`  
    Validates credentials, verifies rate limiter, authenticates session, checks user active status, regenerates session token, and redirects to role dashboard (`admin`, `reseller`, or `client`). Uses `LoginRequest`.
  - `destroy(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse`  
    Logs user out via `Auth::guard('web')->logout()`, invalidates session, regenerates CSRF token, and redirects to home page.

### 2. `RegisteredUserController`
- **Namespace**: `App\Http\Controllers\Auth`
- **Methods**:
  - `create(): \Illuminate\View\View`  
    Renders registration page with role selection (Client or Reseller).
  - `store(App\Http\Requests\Auth\RegisterRequest $request): \Illuminate\Http\RedirectResponse`  
    Creates user record with encrypted password, assigns default currency (USD) and role (`client` or `reseller`), initializes zero-balance wallet, fires `Registered` event for verification email, logs user in, and redirects to dashboard. Uses `RegisterRequest`.

### 3. `PasswordResetLinkController`
- **Namespace**: `App\Http\Controllers\Auth`
- **Methods**:
  - `create(): \Illuminate\View\View`  
    Renders password reset request email form.
  - `store(App\Http\Requests\Auth\ForgotPasswordRequest $request): \Illuminate\Http\RedirectResponse`  
    Validates email, generates password reset broker token, dispatches notification email, and returns status back to view. Uses `ForgotPasswordRequest`.

### 4. `NewPasswordController`
- **Namespace**: `App\Http\Controllers\Auth`
- **Methods**:
  - `create(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders reset password form passing the email and reset token.
  - `store(App\Http\Requests\Auth\ResetPasswordRequest $request): \Illuminate\Http\RedirectResponse`  
    Validates reset token, updates password in database, triggers `PasswordReset` event, and redirects to login with success alert. Uses `ResetPasswordRequest`.

### 5. `VerifyEmailController`
- **Namespace**: `App\Http\Controllers\Auth`
- **Methods**:
  - `__invoke(\Illuminate\Foundation\Auth\EmailVerificationRequest $request): \Illuminate\Http\RedirectResponse`  
    Marks user email as verified, fires `Verified` event, and redirects to dashboard with verified query param.

### 6. `EmailVerificationNotificationController`
- **Namespace**: `App\Http\Controllers\Auth`
- **Methods**:
  - `store(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse`  
    Resends verification email link if user has not yet verified email.

### 7. `TwoFactorChallengeController`
- **Namespace**: `App\Http\Controllers\Auth`
- **Methods**:
  - `create(): \Illuminate\View\View`  
    Renders 2FA code / recovery code entry prompt.
  - `store(App\Http\Requests\Auth\TwoFactorChallengeRequest $request): \Illuminate\Http\RedirectResponse`  
    Validates submitted 6-digit TOTP code against `user->two_factor_secret` or validates emergency recovery code. Sets session flag `2fa_verified = true` and redirects to intended URL. Uses `TwoFactorChallengeRequest`.

---

## 3.2 Admin Controllers (`App\Http\Controllers\Admin\`)

### 1. `AdminDashboardController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Computes summary metrics: Total Revenue (MTD & YTD in USD & BDT), Total Active Domains, Domains Expiring in 30 Days, Pending Invoices, Registrar Balance Totals, and recent 10 orders.

### 2. `ClientManagementController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders paginated clients list with search by name, email, phone, status, and registration date.
  - `create(): \Illuminate\View\View`  
    Renders client creation form.
  - `store(App\Http\Requests\Admin\StoreClientRequest $request): \Illuminate\Http\RedirectResponse`  
    Creates client account, assigns role `client`, initializes wallet, and sends welcome email. Uses `StoreClientRequest`.
  - `show(\App\Models\User $client): \Illuminate\View\View`  
    Displays client profile, domain list, billing invoices, activity logs, and wallet ledger.
  - `edit(\App\Models\User $client): \Illuminate\View\View`  
    Renders edit view for client profile.
  - `update(App\Http\Requests\Admin\UpdateClientRequest $request, \App\Models\User $client): \Illuminate\Http\RedirectResponse`  
    Updates client demographic, billing address, and account settings. Uses `UpdateClientRequest`.
  - `destroy(\App\Models\User $client): \Illuminate\Http\RedirectResponse`  
    Soft deletes client account if no active registered domains exist.
  - `toggleStatus(\App\Models\User $client): \Illuminate\Http\JsonResponse`  
    Toggles client status between `active` and `suspended`.
  - `adjustBalance(App\Http\Requests\Admin\AdjustBalanceRequest $request, \App\Models\User $client): \Illuminate\Http\RedirectResponse`  
    Manually credits or debits client wallet balance with audit note. Uses `AdjustBalanceRequest`.
  - `impersonate(\App\Models\User $client): \Illuminate\Http\RedirectResponse`  
    Sets admin impersonation session and redirects to client dashboard.

### 3. `ResellerManagementController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders reseller directory with search, tier filtering, and API key metrics.
  - `create(): \Illuminate\View\View`  
    Renders reseller creation form.
  - `store(App\Http\Requests\Admin\StoreResellerRequest $request): \Illuminate\Http\RedirectResponse`  
    Creates reseller user (`role === 'reseller'`), creates wallet, assigns pricing tier, and dispatches welcome kit. Uses `StoreResellerRequest`.
  - `show(\App\Models\User $reseller): \Illuminate\View\View`  
    Displays reseller metrics: sub-clients count, API keys, balance, revenue generated, and profit margin.
  - `edit(\App\Models\User $reseller): \Illuminate\View\View`  
    Renders edit view for reseller tier, margin overrides, and details.
  - `update(App\Http\Requests\Admin\UpdateResellerRequest $request, \App\Models\User $reseller): \Illuminate\Http\RedirectResponse`  
    Updates reseller settings. Uses `UpdateResellerRequest`.
  - `destroy(\App\Models\User $reseller): \Illuminate\Http\RedirectResponse`  
    Terminates reseller account.
  - `toggleStatus(\App\Models\User $reseller): \Illuminate\Http\JsonResponse`  
    Suspends or reactivates reseller access and API keys.
  - `updateTier(\Illuminate\Http\Request $request, \App\Models\User $reseller): \Illuminate\Http\RedirectResponse`  
    Updates reseller pricing tier (`Standard`, `Silver`, `Gold`, `Platinum`).
  - `adjustBalance(App\Http\Requests\Admin\AdjustBalanceRequest $request, \App\Models\User $reseller): \Illuminate\Http\RedirectResponse`  
    Adjusts reseller wholesale credit wallet with ledger entry. Uses `AdjustBalanceRequest`.
  - `apiKeys(\App\Models\User $reseller): \Illuminate\View\View`  
    Lists all API keys generated by the reseller.
  - `revokeApiKey(\App\Models\User $reseller, \App\Models\ApiKey $apiKey): \Illuminate\Http\RedirectResponse`  
    Revokes specific API key.

### 4. `DomainManagementController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Master domain list with search by domain name, user, registrar, status, and expiry ranges.
  - `show(\App\Models\Domain $domain): \Illuminate\View\View`  
    Full domain details: registrar info, nameservers, lock status, ID protection, and audit logs.
  - `edit(\App\Models\Domain $domain): \Illuminate\View\View`  
    Form to manually adjust expiry dates, registration dates, or assign to another user.
  - `update(App\Http\Requests\Admin\UpdateDomainAdminRequest $request, \App\Models\Domain $domain): \Illuminate\Http\RedirectResponse`  
    Saves administrative overrides on domain. Uses `UpdateDomainAdminRequest`.
  - `syncWithRegistrar(\App\Models\Domain $domain): \Illuminate\Http\JsonResponse`  
    Queries registrar module for real-time status, expiry date, and syncs local database.
  - `renew(\Illuminate\Http\Request $request, \App\Models\Domain $domain): \Illuminate\Http\RedirectResponse`  
    Forces administrative renewal through registrar module for specified years.
  - `toggleLock(\App\Models\Domain $domain): \Illuminate\Http\JsonResponse`  
    Sends registrar API call to toggle domain transfer lock.
  - `togglePrivacy(\App\Models\Domain $domain): \Illuminate\Http\JsonResponse`  
    Toggles registrar WHOIS privacy protection service.
  - `fetchEppCode(\App\Models\Domain $domain): \Illuminate\Http\JsonResponse`  
    Fetches real-time EPP / transfer authorization code from registrar.
  - `updateNameservers(App\Http\Requests\Client\UpdateNameserversRequest $request, \App\Models\Domain $domain): \Illuminate\Http\RedirectResponse`  
    Updates authoritative nameservers via registrar module. Uses `UpdateNameserversRequest`.
  - `syncTransferStatus(\App\Models\Domain $domain): \Illuminate\Http\JsonResponse`  
    Checks registrar status of pending incoming domain transfer.
  - `destroy(\App\Models\Domain $domain): \Illuminate\Http\RedirectResponse`  
    Deletes domain record from local database.

### 5. `TldPricingController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Renders TLD table with cost price, retail price, reseller tier prices, and registrar mappings.
  - `create(): \Illuminate\View\View`  
    Renders form to configure a new TLD.
  - `store(App\Http\Requests\Admin\StoreTldRequest $request): \Illuminate\Http\RedirectResponse`  
    Saves new TLD extension, pricing rules, and registrar mapping. Uses `StoreTldRequest`.
  - `edit(\App\Models\Tld $tld): \Illuminate\View\View`  
    Form to edit TLD pricing, renewal grace period, redemption fees, and EPP requirements.
  - `update(App\Http\Requests\Admin\UpdateTldRequest $request, \App\Models\Tld $tld): \Illuminate\Http\RedirectResponse`  
    Updates TLD parameters. Uses `UpdateTldRequest`.
  - `destroy(\App\Models\Tld $tld): \Illuminate\Http\RedirectResponse`  
    Deactivates or deletes TLD configuration.
  - `bulkUpdate(App\Http\Requests\Admin\BulkTldPricingRequest $request): \Illuminate\Http\RedirectResponse`  
    Batch updates retail and reseller pricing across selected TLDs by percentage or fixed amount. Uses `BulkTldPricingRequest`.
  - `syncRegistrarPrices(): \Illuminate\Http\RedirectResponse`  
    Connects to active registrars and updates wholesale cost columns.

### 6. `RegistrarModuleController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Lists all supported registrar modules (Namecheap, Spaceship, Dynadot, AtakDomain, Namesilo) with status and balance.
  - `edit(\App\Models\Registrar $registrar): \Illuminate\View\View`  
    Form to configure API credentials, environment (sandbox/production), and default nameservers.
  - `update(App\Http\Requests\Admin\UpdateRegistrarRequest $request, \App\Models\Registrar $registrar): \Illuminate\Http\RedirectResponse`  
    Saves encrypted registrar credentials. Uses `UpdateRegistrarRequest`.
  - `testConnection(\App\Models\Registrar $registrar): \Illuminate\Http\JsonResponse`  
    Dispatches test API request to verify authentication with registrar endpoint.
  - `syncBalance(\App\Models\Registrar $registrar): \Illuminate\Http\JsonResponse`  
    Pulls live account credit balance from registrar and updates database cache.
  - `toggleStatus(\App\Models\Registrar $registrar): \Illuminate\Http\JsonResponse`  
    Enables or disables registrar module.
  - `setDefault(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse`  
    Sets primary fallback registrar for unmatched TLDs.

### 7. `OrderManagementController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Lists all domain orders (registration, renewal, transfer) with status filters (`pending`, `processing`, `completed`, `failed`).
  - `show(\App\Models\Order $order): \Illuminate\View\View`  
    Shows order details, raw API request/response payloads to registrar, and error logs.
  - `retryProvision(\App\Models\Order $order): \Illuminate\Http\RedirectResponse`  
    Dispatches queue job to re-attempt automated domain provisioning with registrar.
  - `cancel(\App\Models\Order $order): \Illuminate\Http\RedirectResponse`  
    Cancels order and issues full refund to customer wallet.

### 8. `InvoiceManagementController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Lists invoices with status filters, date range filters, and totals summary.
  - `create(): \Illuminate\View\View`  
    Form to generate custom invoice for customer.
  - `store(App\Http\Requests\Admin\StoreInvoiceRequest $request): \Illuminate\Http\RedirectResponse`  
    Creates invoice with custom line items and notifies user. Uses `StoreInvoiceRequest`.
  - `show(\App\Models\Invoice $invoice): \Illuminate\View\View`  
    Shows invoice details, line items, and payment transaction logs.
  - `edit(\App\Models\Invoice $invoice): \Illuminate\View\View`  
    Form to modify unpaid invoice items or due dates.
  - `update(App\Http\Requests\Admin\UpdateInvoiceRequest $request, \App\Models\Invoice $invoice): \Illuminate\Http\RedirectResponse`  
    Updates invoice record. Uses `UpdateInvoiceRequest`.
  - `markPaid(\App\Models\Invoice $invoice): \Illuminate\Http\RedirectResponse`  
    Marks invoice as paid manually and triggers domain provisioning jobs if applicable.
  - `cancel(\App\Models\Invoice $invoice): \Illuminate\Http\RedirectResponse`  
    Cancels invoice.
  - `refund(\App\Models\Invoice $invoice): \Illuminate\Http\RedirectResponse`  
    Processes refund to user wallet.
  - `generatePdf(\App\Models\Invoice $invoice): \Symfony\Component\HttpFoundation\Response`  
    Renders and streams PDF invoice using Blade view.

### 9. `ManualPaymentController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Lists pending manual offline deposits (bKash/Nagad personal sender numbers, bank transfer slips).
  - `approve(\App\Models\PaymentVerification $verification): \Illuminate\Http\RedirectResponse`  
    Approves manual payment, marks invoice paid / credits wallet balance, and sends confirmation notification.
  - `reject(App\Http\Requests\Admin\RejectManualPaymentRequest $request, \App\Models\PaymentVerification $verification): \Illuminate\Http\RedirectResponse`  
    Rejects payment verification request with explanation note sent to user. Uses `RejectManualPaymentRequest`.

### 10. `SupportTicketController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Lists support tickets with status, priority, and department filters.
  - `show(\App\Models\Ticket $ticket): \Illuminate\View\View`  
    Shows full ticket thread, client details, attachments, and internal notes.
  - `reply(App\Http\Requests\Admin\AdminTicketReplyRequest $request, \App\Models\Ticket $ticket): \Illuminate\Http\RedirectResponse`  
    Submits staff reply or internal note, changes ticket status, and notifies customer. Uses `AdminTicketReplyRequest`.
  - `updateStatus(\Illuminate\Http\Request $request, \App\Models\Ticket $ticket): \Illuminate\Http\JsonResponse`  
    Updates ticket status (`open`, `answered`, `in_progress`, `closed`).
  - `assign(\Illuminate\Http\Request $request, \App\Models\Ticket $ticket): \Illuminate\Http\JsonResponse`  
    Assigns ticket to designated staff member.
  - `destroy(\App\Models\Ticket $ticket): \Illuminate\Http\RedirectResponse`  
    Deletes ticket and attachments.

### 11. `EmailTemplateController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Lists all system email notification templates (Registration, Invoice, Domain Renewal, Expiry Notice, etc.).
  - `edit(\App\Models\EmailTemplate $template): \Illuminate\View\View`  
    Editor for email subject line, markdown/HTML body, and placeholder tags list.
  - `update(App\Http\Requests\Admin\UpdateEmailTemplateRequest $request, \App\Models\EmailTemplate $template): \Illuminate\Http\RedirectResponse`  
    Saves updated email template. Uses `UpdateEmailTemplateRequest`.
  - `sendTest(\App\Models\EmailTemplate $template): \Illuminate\Http\JsonResponse`  
    Sends rendered test email to admin's current email address.

### 12. `PromoCodeController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Lists promo discount codes with redemption metrics.
  - `create(): \Illuminate\View\View`  
    Form to create promo code.
  - `store(App\Http\Requests\Admin\StorePromoCodeRequest $request): \Illuminate\Http\RedirectResponse`  
    Stores promo code (fixed or percentage discount, usage limit, valid TLDs, expiry). Uses `StorePromoCodeRequest`.
  - `edit(\App\Models\PromoCode $promoCode): \Illuminate\View\View`  
    Form to modify promo code.
  - `update(App\Http\Requests\Admin\UpdatePromoCodeRequest $request, \App\Models\PromoCode $promoCode): \Illuminate\Http\RedirectResponse`  
    Updates promo code settings. Uses `UpdatePromoCodeRequest`.
  - `destroy(\App\Models\PromoCode $promoCode): \Illuminate\Http\RedirectResponse`  
    Deletes promo code.

### 13. `AnnouncementController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Lists all platform announcements.
  - `store(App\Http\Requests\Admin\StoreAnnouncementRequest $request): \Illuminate\Http\RedirectResponse`  
    Publishes announcement targeted to `all`, `clients`, or `resellers`. Uses `StoreAnnouncementRequest`.
  - `update(App\Http\Requests\Admin\StoreAnnouncementRequest $request, \App\Models\Announcement $announcement): \Illuminate\Http\RedirectResponse`  
    Updates announcement content and visibility. Uses `StoreAnnouncementRequest`.
  - `destroy(\App\Models\Announcement $announcement): \Illuminate\Http\RedirectResponse`  
    Deletes announcement.

### 14. `ActivityLogController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Searchable audit trail of user and admin actions with IP, URL, and time filters.
  - `show(int $id): \Illuminate\View\View`  
    Displays formatted request payload, user agent, and database mutations.
  - `clearOld(): \Illuminate\Http\RedirectResponse`  
    Prunes activity logs older than 90 days.

### 15. `SystemSettingController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `general(): \Illuminate\View\View`  
    Renders general site branding, support email, company name, and default pagination view.
  - `updateGeneral(App\Http\Requests\Admin\UpdateSystemSettingsRequest $request): \Illuminate\Http\RedirectResponse`  
    Saves site settings into `settings` key-value table and clears settings cache. Uses `UpdateSystemSettingsRequest`.
  - `email(): \Illuminate\View\View`  
    Renders SMTP mail configuration interface.
  - `updateEmail(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse`  
    Updates SMTP mail host, port, encryption, username, password, and sender address.
  - `testEmail(): \Illuminate\Http\JsonResponse`  
    Sends instantaneous test email via configured SMTP settings to verify handshake.
  - `security(): \Illuminate\View\View`  
    Renders security settings (session lifetimes, 2FA enforcement, API rate limit defaults).
  - `updateSecurity(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse`  
    Saves security policy configurations.

### 16. `CurrencySettingController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Lists configured currencies, base currency flag (USD), and live conversion rates (BDT).
  - `store(App\Http\Requests\Admin\StoreCurrencyRequest $request): \Illuminate\Http\RedirectResponse`  
    Adds new currency code and exchange rate. Uses `StoreCurrencyRequest`.
  - `update(App\Http\Requests\Admin\UpdateCurrencyRequest $request, \App\Models\Currency $currency): \Illuminate\Http\RedirectResponse`  
    Updates exchange rate and symbol position. Uses `UpdateCurrencyRequest`.
  - `updateRates(): \Illuminate\Http\RedirectResponse`  
    Fetches real-time exchange rates via external FX API and updates `currencies` table.

### 17. `ReportController`
- **Namespace**: `App\Http\Controllers\Admin`
- **Methods**:
  - `revenue(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders revenue charts, gross vs net profits, and gateway distribution.
  - `domains(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders domain volume analytics, registration vs transfer trends.
  - `clients(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders client growth, top spenders, and active user distribution.
  - `resellers(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders wholesale reseller sales analysis and API throughput.
  - `tlds(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders breakdown of domain volume and margin per TLD extension.
  - `payments(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders payment gateway breakdown (bKash, Stripe, Binance, Manual) and chargeback statistics.

---

## 3.3 Client Controllers (`App\Http\Controllers\Client\`)

### 1. `ClientDashboardController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Renders client dashboard showing active domain count, domains expiring in 30 days, unpaid invoice count, wallet balance (USD & BDT), and quick domain search.

### 2. `ClientProfileController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `edit(): \Illuminate\View\View`  
    Renders profile editing form with client's default contact and billing info.
  - `update(App\Http\Requests\Client\UpdateProfileRequest $request): \Illuminate\Http\RedirectResponse`  
    Saves user name, phone, company, address, city, state, country, and zip. Uses `UpdateProfileRequest`.
  - `updatePassword(App\Http\Requests\Client\UpdatePasswordRequest $request): \Illuminate\Http\RedirectResponse`  
    Validates current password and updates to new encrypted password. Uses `UpdatePasswordRequest`.

### 3. `TwoFactorAuthController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Displays 2FA status, setup instructions, and recovery codes if enabled.
  - `enable(): \Illuminate\View\View`  
    Generates TOTP secret key and inline SVG QR code for Google Authenticator / Authy.
  - `confirm(App\Http\Requests\Client\TwoFactorConfirmRequest $request): \Illuminate\Http\RedirectResponse`  
    Validates code, activates 2FA on account, and outputs 8 emergency recovery codes. Uses `TwoFactorConfirmRequest`.
  - `disable(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse`  
    Validates current password and disables 2FA on account.

### 4. `SessionController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Lists active browser sessions parsed from `sessions` table (device, browser, IP, last active).
  - `destroy(string $id): \Illuminate\Http\RedirectResponse`  
    Terminates selected browser session.

### 5. `DomainSearchController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Renders domain search interface.
  - `check(App\Http\Requests\Client\DomainSearchRequest $request): \Illuminate\Http\JsonResponse`  
    Performs real-time availability check via registrar module and returns pricing in USD & BDT. Uses `DomainSearchRequest`.
  - `publicSearch(): \Illuminate\View\View`  
    Renders public domain search page.
  - `checkSingle(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    AJAX handler for public single domain lookup.
  - `checkBulk(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    AJAX handler for public multi-domain lookup.

### 6. `DomainRegistrationController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `create(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders domain registration configuration form (select registration term 1-10 years, ID protection addon, custom nameservers).
  - `store(App\Http\Requests\Client\RegisterDomainRequest $request): \Illuminate\Http\RedirectResponse`  
    Creates domain order, generates invoice, checks if client chose wallet payment or external gateway, redirects to payment. Uses `RegisterDomainRequest`.

### 7. `DomainTransferController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `create(): \Illuminate\View\View`  
    Renders domain transfer initiation form.
  - `store(App\Http\Requests\Client\TransferDomainRequest $request): \Illuminate\Http\RedirectResponse`  
    Validates domain and EPP code, creates transfer order and invoice, redirects to checkout. Uses `TransferDomainRequest`.

### 8. `ClientDomainController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Lists all domains owned by client with status badges and expiry dates.
  - `show(\App\Models\Domain $domain): \Illuminate\View\View`  
    Domain management hub (overview, quick toggles for lock/privacy, nameservers summary). Authorizes ownership.
  - `renew(int $domainId, \Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse`  
    Creates renewal invoice for 1-10 years and redirects to payment.
  - `toggleAutoRenew(\App\Models\Domain $domain): \Illuminate\Http\JsonResponse`  
    Toggles auto-renewal preference flag in database. Authorizes ownership.
  - `toggleLock(\App\Models\Domain $domain): \Illuminate\Http\JsonResponse`  
    Calls registrar module to enable/disable transfer lock. Authorizes ownership.
  - `togglePrivacy(\App\Models\Domain $domain): \Illuminate\Http\JsonResponse`  
    Calls registrar module to enable/disable WHOIS privacy. Authorizes ownership.
  - `fetchEppCode(\App\Models\Domain $domain): \Illuminate\Http\JsonResponse`  
    Retrieves EPP transfer authorization code from registrar and emails it to user. Authorizes ownership.

### 9. `NameserverController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `edit(\App\Models\Domain $domain): \Illuminate\View\View`  
    Renders authoritative nameservers management view with default vs custom option. Authorizes ownership.
  - `update(App\Http\Requests\Client\UpdateNameserversRequest $request, \App\Models\Domain $domain): \Illuminate\Http\RedirectResponse`  
    Submits nameserver changes to registrar module. Uses `UpdateNameserversRequest`. Authorizes ownership.

### 10. `DnsRecordController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(\App\Models\Domain $domain): \Illuminate\View\View`  
    Renders DNS record management page for domain. Authorizes ownership.
  - `store(App\Http\Requests\Client\StoreDnsRecordRequest $request, \App\Models\Domain $domain): \Illuminate\Http\RedirectResponse`  
    Adds DNS record (A, AAAA, CNAME, MX, TXT, SRV) via registrar DNS API or hosted DNS server. Uses `StoreDnsRecordRequest`. Authorizes ownership.
  - `update(App\Http\Requests\Client\UpdateDnsRecordRequest $request, \App\Models\Domain $domain, string $recordId): \Illuminate\Http\RedirectResponse`  
    Updates existing DNS record. Uses `UpdateDnsRecordRequest`. Authorizes ownership.
  - `destroy(\App\Models\Domain $domain, string $recordId): \Illuminate\Http\RedirectResponse`  
    Deletes DNS record. Authorizes ownership.

### 11. `ChildNameserverController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(\App\Models\Domain $domain): \Illuminate\View\View`  
    Lists registered child nameservers (glue records). Authorizes ownership.
  - `store(App\Http\Requests\Client\StoreChildNameserverRequest $request, \App\Models\Domain $domain): \Illuminate\Http\RedirectResponse`  
    Registers new child nameserver with registrar (e.g. `ns1.example.com` pointing to IPv4). Uses `StoreChildNameserverRequest`. Authorizes ownership.
  - `update(App\Http\Requests\Client\UpdateChildNameserverRequest $request, \App\Models\Domain $domain, string $nsId): \Illuminate\Http\RedirectResponse`  
    Updates IP address of existing child host. Uses `UpdateChildNameserverRequest`. Authorizes ownership.
  - `destroy(\App\Models\Domain $domain, string $nsId): \Illuminate\Http\RedirectResponse`  
    Deletes child nameserver record from registrar. Authorizes ownership.

### 12. `WhoisContactController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `edit(\App\Models\Domain $domain): \Illuminate\View\View`  
    Renders WHOIS contact form displaying Registrant, Admin, Technical, and Billing contact info. Authorizes ownership.
  - `update(App\Http\Requests\Client\UpdateWhoisRequest $request, \App\Models\Domain $domain): \Illuminate\Http\RedirectResponse`  
    Submits updated contact details to registrar module. Uses `UpdateWhoisRequest`. Authorizes ownership.

### 13. `EmailForwardingController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(\App\Models\Domain $domain): \Illuminate\View\View`  
    Lists email forwarders configured for domain. Authorizes ownership.
  - `store(App\Http\Requests\Client\StoreEmailForwardRequest $request, \App\Models\Domain $domain): \Illuminate\Http\RedirectResponse`  
    Configures email forwarding alias with registrar module. Uses `StoreEmailForwardRequest`. Authorizes ownership.
  - `destroy(\App\Models\Domain $domain, int $id): \Illuminate\Http\RedirectResponse`  
    Removes email forwarding alias. Authorizes ownership.

### 14. `UrlForwardingController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(\App\Models\Domain $domain): \Illuminate\View\View`  
    Lists active HTTP domain/subdomain redirect rules. Authorizes ownership.
  - `store(App\Http\Requests\Client\StoreUrlForwardRequest $request, \App\Models\Domain $domain): \Illuminate\Http\RedirectResponse`  
    Configures 301, 302, or masked URL redirect rule. Uses `StoreUrlForwardRequest`. Authorizes ownership.
  - `destroy(\App\Models\Domain $domain, int $id): \Illuminate\Http\RedirectResponse`  
    Removes URL forwarder. Authorizes ownership.

### 15. `ClientInvoiceController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Lists client invoices filterable by status (`paid`, `unpaid`).
  - `show(\App\Models\Invoice $invoice): \Illuminate\View\View`  
    Renders invoice details with payment gateway options (Wallet, bKash, Stripe, Binance, Manual). Authorizes ownership.
  - `pay(\App\Models\Invoice $invoice, \Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse`  
    Processes payment using client wallet balance or redirects to external gateway checkout URL. Authorizes ownership.

### 16. `ClientWalletController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Displays wallet balance in USD & BDT, deposit form, and transaction history.
  - `addFunds(App\Http\Requests\Client\AddFundsRequest $request): \Illuminate\Http\RedirectResponse`  
    Creates deposit invoice and initiates payment gateway session. Uses `AddFundsRequest`.
  - `transactions(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Renders paginated financial transaction history.

### 17. `ClientTicketController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Lists support tickets submitted by client.
  - `create(): \Illuminate\View\View`  
    Renders ticket creation form (department, priority, related domain, message, attachments).
  - `store(App\Http\Requests\Client\StoreTicketRequest $request): \Illuminate\Http\RedirectResponse`  
    Creates ticket thread, saves file attachments, and notifies support staff. Uses `StoreTicketRequest`.
  - `show(\App\Models\Ticket $ticket): \Illuminate\View\View`  
    Displays ticket conversation history. Authorizes ownership.
  - `reply(App\Http\Requests\Client\TicketReplyRequest $request, \App\Models\Ticket $ticket): \Illuminate\Http\RedirectResponse`  
    Posts client reply and updates ticket status to `customer-reply`. Uses `TicketReplyRequest`. Authorizes ownership.
  - `close(\App\Models\Ticket $ticket): \Illuminate\Http\RedirectResponse`  
    Marks ticket closed by user. Authorizes ownership.

### 18. `ClientAnnouncementController`
- **Namespace**: `App\Http\Controllers\Client`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Displays platform announcements visible to clients.
  - `show(int $id): \Illuminate\View\View`  
    Displays full announcement article.
  - `publicIndex(): \Illuminate\View\View`  
    Public announcement archive.
  - `publicShow(string $slug): \Illuminate\View\View`  
    Public single announcement page.

---

## 3.4 Reseller Controllers (`App\Http\Controllers\Reseller\`)

### 1. `ResellerDashboardController`
- **Namespace**: `App\Http\Controllers\Reseller`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Renders reseller overview: Wholesale Wallet Balance (USD/BDT), Total Managed Domains, Sub-clients count, API Requests in last 24h, Tier Status, and Recent Orders.

### 2. `ResellerPricingController`
- **Namespace**: `App\Http\Controllers\Reseller`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Displays assigned wholesale pricing table for all active TLDs, including Registration, Renewal, and Transfer rates according to the reseller's tier.

### 3. `ApiKeyController`
- **Namespace**: `App\Http\Controllers\Reseller`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Lists all API keys generated by reseller with key names, IP restrictions, and last used timestamps.
  - `store(App\Http\Requests\Reseller\StoreApiKeyRequest $request): \Illuminate\Http\RedirectResponse`  
    Generates new key pair (`key_identifier` and plaintext `secret_key`), stores SHA-256 secret hash, flashes secret key once to session for display. Uses `StoreApiKeyRequest`.
  - `update(App\Http\Requests\Reseller\UpdateApiKeyRequest $request, int $id): \Illuminate\Http\RedirectResponse`  
    Updates API key label and IP whitelist array. Uses `UpdateApiKeyRequest`.
  - `destroy(int $id): \Illuminate\Http\RedirectResponse`  
    Revokes and deletes API key.
  - `regenerateSecret(int $id): \Illuminate\Http\RedirectResponse`  
    Regenerates secret key for existing identifier and flashes new secret to session.
  - `logs(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Displays API access logs filterable by endpoint, status code, and IP.
  - `docs(): \Illuminate\View\View`  
    Renders interactive API documentation, code examples (cURL, PHP, Python), and downloadable WHMCS domain registrar module.

### 4. `WebhookController`
- **Namespace**: `App\Http\Controllers\Reseller`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Lists registered webhook URLs and subscribed event types.
  - `store(App\Http\Requests\Reseller\StoreWebhookRequest $request): \Illuminate\Http\RedirectResponse`  
    Registers webhook endpoint URL, generates signing secret, and subscribes to events. Uses `StoreWebhookRequest`.
  - `update(App\Http\Requests\Reseller\StoreWebhookRequest $request, int $id): \Illuminate\Http\RedirectResponse`  
    Updates webhook endpoint URL and event subscriptions. Uses `StoreWebhookRequest`.
  - `destroy(int $id): \Illuminate\Http\RedirectResponse`  
    Deletes webhook endpoint.
  - `test(int $id): \Illuminate\Http\JsonResponse`  
    Dispatches mock event (`domain.test_ping`) to verify webhook endpoint response.

### 5. `SubClientController`
- **Namespace**: `App\Http\Controllers\Reseller`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\View\View`  
    Lists sub-clients created under this reseller via API / WHMCS sync.
  - `show(int $id): \Illuminate\View\View`  
    Displays sub-client profile, associated domains, and registration dates.
  - `toggleStatus(int $id): \Illuminate\Http\JsonResponse`  
    Suspends or reactivates sub-client account.

### 6. `ResellerReportController`
- **Namespace**: `App\Http\Controllers\Reseller`
- **Methods**:
  - `domains(): \Illuminate\View\View`  
    Portfolio report showing domain counts by TLD, upcoming expirations in 30/60/90 days, and renewal rates.
  - `revenue(): \Illuminate\View\View`  
    Financial report showing wholesale expenditure, deposit history, and estimated resale profit.
  - `apiUsage(): \Illuminate\View\View`  
    Analytics showing API requests per hour, endpoint breakdown, error rate, and average response latency.

### 7. `WhitelabelController`
- **Namespace**: `App\Http\Controllers\Reseller`
- **Methods**:
  - `index(): \Illuminate\View\View`  
    Renders white-label settings page: custom default nameservers (e.g. `ns1.resellerbrand.com`), company name for WHOIS, and custom notification email headers.
  - `update(App\Http\Requests\Reseller\UpdateWhitelabelRequest $request): \Illuminate\Http\RedirectResponse`  
    Saves white-label configuration to `reseller_settings` table. Uses `UpdateWhitelabelRequest`.

---

## 3.5 External Reseller REST API V1 Controllers (`App\Http\Controllers\Api\V1\`)

All API controllers extend `App\Http\Controllers\Api\V1\BaseApiController`, which provides uniform JSON response helpers: `sendResponse($data, $message, $code = 200)` and `sendError($error, $errorMessages = [], $code = 404)`.

### 1. `ApiAuthController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `test(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    Validates API key and returns authenticated reseller account identifier and timestamp.

### 2. `ApiAccountController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `info(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    Returns reseller profile, company name, tier level, and white-label settings.
  - `balance(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    Returns live wallet balance in USD and converted BDT.

### 3. `ApiTldPricingController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    Returns complete array of active TLDs with register, renew, transfer, and restore prices.
  - `show(string $tld): \Illuminate\Http\JsonResponse`  
    Returns pricing, min/max registration years, and EPP requirement for specific TLD.

### 4. `ApiDomainCheckController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `check(App\Http\Requests\Api\V1\ApiDomainCheckRequest $request): \Illuminate\Http\JsonResponse`  
    Checks single domain availability via registrar module and returns availability status and price. Uses `ApiDomainCheckRequest`.
  - `checkBulk(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    Checks availability for array of domain names (up to 50 domains) and returns status map.
  - `suggestions(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    Returns alternative domain name suggestions for a given keyword.

### 5. `ApiDomainController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `index(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    Returns paginated list of domains belonging to authenticated reseller.
  - `register(App\Http\Requests\Api\V1\ApiRegisterDomainRequest $request): \Illuminate\Http\JsonResponse`  
    Registers domain: checks balance, creates order, calls registrar API, deducts wallet, returns registered domain object. Uses `ApiRegisterDomainRequest`.
  - `transfer(App\Http\Requests\Api\V1\ApiTransferDomainRequest $request): \Illuminate\Http\JsonResponse`  
    Initiates domain transfer with EPP code: checks balance, creates transfer order, calls registrar API, deducts wallet. Uses `ApiTransferDomainRequest`.
  - `show(string $domain): \Illuminate\Http\JsonResponse`  
    Returns details for specific domain (registration date, expiry, nameservers, lock status, privacy status).
  - `renew(App\Http\Requests\Api\V1\ApiRenewDomainRequest $request, string $domain): \Illuminate\Http\JsonResponse`  
    Renews domain for 1-10 years: verifies balance, calls registrar API, deducts wallet, updates expiry date. Uses `ApiRenewDomainRequest`.
  - `cancel(string $domain): \Illuminate\Http\JsonResponse`  
    Requests domain cancellation / deletion with registrar module.
  - `authCode(string $domain): \Illuminate\Http\JsonResponse`  
    Fetches real-time EPP / transfer authorization code from registrar.
  - `enableLock(string $domain): \Illuminate\Http\JsonResponse`  
    Enables registrar transfer lock.
  - `disableLock(string $domain): \Illuminate\Http\JsonResponse`  
    Disables registrar transfer lock.
  - `enablePrivacy(string $domain): \Illuminate\Http\JsonResponse`  
    Enables WHOIS ID privacy protection addon.
  - `disablePrivacy(string $domain): \Illuminate\Http\JsonResponse`  
    Disables WHOIS ID privacy protection addon.
  - `enableAutoRenew(string $domain): \Illuminate\Http\JsonResponse`  
    Enables automated renewal flag on domain.
  - `disableAutoRenew(string $domain): \Illuminate\Http\JsonResponse`  
    Disables automated renewal flag on domain.

### 6. `ApiNameserverController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `index(string $domain): \Illuminate\Http\JsonResponse`  
    Returns current authoritative nameservers for domain.
  - `update(App\Http\Requests\Api\V1\ApiUpdateNameserversRequest $request, string $domain): \Illuminate\Http\JsonResponse`  
    Pushes 2 to 5 updated authoritative nameservers to registrar module. Uses `ApiUpdateNameserversRequest`.

### 7. `ApiChildNameserverController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `index(string $domain): \Illuminate\Http\JsonResponse`  
    Lists all registered child nameservers (glue records) for domain.
  - `store(App\Http\Requests\Api\V1\ApiStoreChildNameserverRequest $request, string $domain): \Illuminate\Http\JsonResponse`  
    Registers new child host at registrar (e.g. `ns1.example.com` -> IP). Uses `ApiStoreChildNameserverRequest`.
  - `update(App\Http\Requests\Api\V1\ApiUpdateChildNameserverRequest $request, string $domain, string $hostname): \Illuminate\Http\JsonResponse`  
    Updates IP address for registered child nameserver. Uses `ApiUpdateChildNameserverRequest`.
  - `destroy(string $domain, string $hostname): \Illuminate\Http\JsonResponse`  
    Deletes child nameserver record from registrar.

### 8. `ApiDnsController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `index(string $domain): \Illuminate\Http\JsonResponse`  
    Returns all DNS zone records for domain.
  - `store(App\Http\Requests\Api\V1\ApiStoreDnsRecordRequest $request, string $domain): \Illuminate\Http\JsonResponse`  
    Adds new DNS record (A, AAAA, CNAME, MX, TXT, SRV). Uses `ApiStoreDnsRecordRequest`.
  - `update(App\Http\Requests\Api\V1\ApiUpdateDnsRecordRequest $request, string $domain, string $recordId): \Illuminate\Http\JsonResponse`  
    Updates existing DNS record. Uses `ApiUpdateDnsRecordRequest`.
  - `destroy(string $domain, string $recordId): \Illuminate\Http\JsonResponse`  
    Deletes DNS record from zone.

### 9. `ApiWhoisController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `show(string $domain): \Illuminate\Http\JsonResponse`  
    Returns current Registrant, Admin, Technical, and Billing contact details.
  - `update(App\Http\Requests\Api\V1\ApiUpdateWhoisRequest $request, string $domain): \Illuminate\Http\JsonResponse`  
    Updates WHOIS contact details at registrar. Uses `ApiUpdateWhoisRequest`.
  - `lookup(string $domain): \Illuminate\Http\JsonResponse`  
    Returns raw and parsed WHOIS lookup data.

### 10. `ApiEmailForwardController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `index(string $domain): \Illuminate\Http\JsonResponse`  
    Lists all active email forwarders for domain.
  - `store(App\Http\Requests\Api\V1\ApiStoreEmailForwardRequest $request, string $domain): \Illuminate\Http\JsonResponse`  
    Adds email forwarding alias. Uses `ApiStoreEmailForwardRequest`.
  - `destroy(string $domain, string $forwardId): \Illuminate\Http\JsonResponse`  
    Deletes email forwarding alias.

### 11. `ApiUrlForwardController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `index(string $domain): \Illuminate\Http\JsonResponse`  
    Lists all active URL redirection rules for domain.
  - `store(App\Http\Requests\Api\V1\ApiStoreUrlForwardRequest $request, string $domain): \Illuminate\Http\JsonResponse`  
    Adds URL redirection rule. Uses `ApiStoreUrlForwardRequest`.
  - `destroy(string $domain, string $forwardId): \Illuminate\Http\JsonResponse`  
    Deletes URL redirection rule.

### 12. `ApiBillingController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `invoices(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    Returns paginated list of reseller invoices and payment status.
  - `invoice(int $id): \Illuminate\Http\JsonResponse`  
    Returns single invoice details and line items.
  - `transactions(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    Returns reseller wallet transaction history.
  - `orders(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`  
    Returns list of domain orders and provisioning statuses.
  - `order(int $id): \Illuminate\Http\JsonResponse`  
    Returns single order details and registrar provisioning logs.

### 13. `ApiWebhookController`
- **Namespace**: `App\Http\Controllers\Api\V1`
- **Methods**:
  - `index(): \Illuminate\Http\JsonResponse`  
    Lists registered webhook URLs.
  - `store(App\Http\Requests\Api\V1\ApiStoreWebhookRequest $request): \Illuminate\Http\JsonResponse`  
    Registers new webhook listener URL and returns webhook secret. Uses `ApiStoreWebhookRequest`.
  - `destroy(int $id): \Illuminate\Http\JsonResponse`  
    Deletes registered webhook.
  - `test(int $id): \Illuminate\Http\JsonResponse`  
    Sends ping event to test webhook delivery.

---

# 4. Complete Form Requests Specification

Every Form Request class extends `Illuminate\Foundation\Http\FormRequest`, implements `authorize(): bool`, and defines `rules(): array`.

---

## 4.1 Authentication Form Requests (`App\Http\Requests\Auth\`)

### 1. `LoginRequest`
- **File**: `app/Http/Requests/Auth/LoginRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'email'       => ['required', 'string', 'email'],
        'password'    => ['required', 'string'],
        'remember'    => ['nullable', 'boolean'],
    ];
}
```

### 2. `RegisterRequest`
- **File**: `app/Http/Requests/Auth/RegisterRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'name'                  => ['required', 'string', 'max:255'],
        'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password'              => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        'role'                  => ['required', 'string', 'in:client,reseller'],
        'company_name'          => ['nullable', 'string', 'max:255'],
        'phone'                 => ['required', 'string', 'max:30'],
        'address'               => ['required', 'string', 'max:255'],
        'city'                  => ['required', 'string', 'max:100'],
        'state'                 => ['required', 'string', 'max:100'],
        'country'               => ['required', 'string', 'size:2'], // ISO 2-letter country code
        'zip_code'              => ['required', 'string', 'max:20'],
        'terms_and_conditions'  => ['accepted'],
    ];
}
```

### 3. `ForgotPasswordRequest`
- **File**: `app/Http/Requests/Auth/ForgotPasswordRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'email' => ['required', 'string', 'email', 'exists:users,email'],
    ];
}
```

### 4. `ResetPasswordRequest`
- **File**: `app/Http/Requests/Auth/ResetPasswordRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'token'    => ['required', 'string'],
        'email'    => ['required', 'string', 'email'],
        'password' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
    ];
}
```

### 5. `TwoFactorChallengeRequest`
- **File**: `app/Http/Requests/Auth/TwoFactorChallengeRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'code'          => ['nullable', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        'recovery_code' => ['nullable', 'string', 'max:32'],
    ];
}
```

---

## 4.2 Admin Form Requests (`App\Http\Requests\Admin\`)

### 6. `StoreClientRequest`
- **File**: `app/Http/Requests/Admin/StoreClientRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'name'         => ['required', 'string', 'max:255'],
        'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password'     => ['required', 'string', 'min:8'],
        'phone'        => ['required', 'string', 'max:30'],
        'company_name' => ['nullable', 'string', 'max:255'],
        'address'      => ['required', 'string', 'max:255'],
        'city'         => ['required', 'string', 'max:100'],
        'state'        => ['required', 'string', 'max:100'],
        'country'      => ['required', 'string', 'size:2'],
        'zip_code'     => ['required', 'string', 'max:20'],
        'status'       => ['required', 'string', 'in:active,inactive,suspended'],
    ];
}
```

### 7. `UpdateClientRequest`
- **File**: `app/Http/Requests/Admin/UpdateClientRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    $clientId = $this->route('client')?->id ?? $this->route('client');
    return [
        'name'         => ['required', 'string', 'max:255'],
        'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$clientId],
        'phone'        => ['required', 'string', 'max:30'],
        'company_name' => ['nullable', 'string', 'max:255'],
        'address'      => ['required', 'string', 'max:255'],
        'city'         => ['required', 'string', 'max:100'],
        'state'        => ['required', 'string', 'max:100'],
        'country'      => ['required', 'string', 'size:2'],
        'zip_code'     => ['required', 'string', 'max:20'],
        'status'       => ['required', 'string', 'in:active,inactive,suspended'],
    ];
}
```

### 8. `AdjustBalanceRequest`
- **File**: `app/Http/Requests/Admin/AdjustBalanceRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'type'        => ['required', 'string', 'in:credit,debit'],
        'amount'      => ['required', 'numeric', 'min:0.01', 'max:100000.00'],
        'currency'    => ['required', 'string', 'in:USD,BDT'],
        'description' => ['required', 'string', 'max:255'],
    ];
}
```

### 9. `StoreResellerRequest`
- **File**: `app/Http/Requests/Admin/StoreResellerRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'name'         => ['required', 'string', 'max:255'],
        'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password'     => ['required', 'string', 'min:8'],
        'phone'        => ['required', 'string', 'max:30'],
        'company_name' => ['required', 'string', 'max:255'],
        'tier'         => ['required', 'string', 'in:Standard,Silver,Gold,Platinum'],
        'address'      => ['required', 'string', 'max:255'],
        'city'         => ['required', 'string', 'max:100'],
        'state'        => ['required', 'string', 'max:100'],
        'country'      => ['required', 'string', 'size:2'],
        'zip_code'     => ['required', 'string', 'max:20'],
        'status'       => ['required', 'string', 'in:active,inactive,suspended'],
    ];
}
```

### 10. `UpdateResellerRequest`
- **File**: `app/Http/Requests/Admin/UpdateResellerRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    $resellerId = $this->route('reseller')?->id ?? $this->route('reseller');
    return [
        'name'         => ['required', 'string', 'max:255'],
        'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$resellerId],
        'phone'        => ['required', 'string', 'max:30'],
        'company_name' => ['required', 'string', 'max:255'],
        'tier'         => ['required', 'string', 'in:Standard,Silver,Gold,Platinum'],
        'address'      => ['required', 'string', 'max:255'],
        'city'         => ['required', 'string', 'max:100'],
        'state'        => ['required', 'string', 'max:100'],
        'country'      => ['required', 'string', 'size:2'],
        'zip_code'     => ['required', 'string', 'max:20'],
        'status'       => ['required', 'string', 'in:active,inactive,suspended'],
    ];
}
```

### 11. `UpdateDomainAdminRequest`
- **File**: `app/Http/Requests/Admin/UpdateDomainAdminRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'user_id'            => ['required', 'integer', 'exists:users,id'],
        'registrar_id'       => ['required', 'integer', 'exists:registrars,id'],
        'registration_date'  => ['required', 'date'],
        'expiry_date'        => ['required', 'date', 'after:registration_date'],
        'status'             => ['required', 'string', 'in:active,expired,cancelled,pending_transfer,fraud'],
        'auto_renew'         => ['required', 'boolean'],
        'id_protection'      => ['required', 'boolean'],
        'is_locked'          => ['required', 'boolean'],
    ];
}
```

### 12. `StoreTldRequest`
- **File**: `app/Http/Requests/Admin/StoreTldRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'tld'                  => ['required', 'string', 'regex:/^\.[a-z0-9\.\-]+$/', 'unique:tlds,tld'],
        'registrar_id'         => ['required', 'integer', 'exists:registrars,id'],
        'cost_price'           => ['required', 'numeric', 'min:0'],
        'retail_register_price'=> ['required', 'numeric', 'gte:cost_price'],
        'retail_renew_price'   => ['required', 'numeric', 'gte:cost_price'],
        'retail_transfer_price'=> ['required', 'numeric', 'gte:cost_price'],
        'reseller_register_price' => ['required', 'numeric', 'gte:cost_price'],
        'reseller_renew_price'    => ['required', 'numeric', 'gte:cost_price'],
        'reseller_transfer_price' => ['required', 'numeric', 'gte:cost_price'],
        'min_years'            => ['required', 'integer', 'min:1', 'max:10'],
        'max_years'            => ['required', 'integer', 'gte:min_years', 'max:10'],
        'epp_required'         => ['required', 'boolean'],
        'id_protection_available' => ['required', 'boolean'],
        'id_protection_price'  => ['nullable', 'numeric', 'min:0'],
        'status'               => ['required', 'string', 'in:active,inactive'],
    ];
}
```

### 13. `UpdateTldRequest`
- **File**: `app/Http/Requests/Admin/UpdateTldRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    $tldId = $this->route('tld')?->id ?? $this->route('tld');
    return [
        'tld'                  => ['required', 'string', 'regex:/^\.[a-z0-9\.\-]+$/', 'unique:tlds,tld,'.$tldId],
        'registrar_id'         => ['required', 'integer', 'exists:registrars,id'],
        'cost_price'           => ['required', 'numeric', 'min:0'],
        'retail_register_price'=> ['required', 'numeric', 'gte:cost_price'],
        'retail_renew_price'   => ['required', 'numeric', 'gte:cost_price'],
        'retail_transfer_price'=> ['required', 'numeric', 'gte:cost_price'],
        'reseller_register_price' => ['required', 'numeric', 'gte:cost_price'],
        'reseller_renew_price'    => ['required', 'numeric', 'gte:cost_price'],
        'reseller_transfer_price' => ['required', 'numeric', 'gte:cost_price'],
        'min_years'            => ['required', 'integer', 'min:1', 'max:10'],
        'max_years'            => ['required', 'integer', 'gte:min_years', 'max:10'],
        'epp_required'         => ['required', 'boolean'],
        'id_protection_available' => ['required', 'boolean'],
        'id_protection_price'  => ['nullable', 'numeric', 'min:0'],
        'status'               => ['required', 'string', 'in:active,inactive'],
    ];
}
```

### 14. `BulkTldPricingRequest`
- **File**: `app/Http/Requests/Admin/BulkTldPricingRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'tld_ids'       => ['required', 'array', 'min:1'],
        'tld_ids.*'     => ['integer', 'exists:tlds,id'],
        'target_field'  => ['required', 'string', 'in:retail_register,retail_renew,retail_transfer,reseller_register,reseller_renew,reseller_transfer,all'],
        'action_type'   => ['required', 'string', 'in:percentage,fixed_add,fixed_set'],
        'value'         => ['required', 'numeric'],
    ];
}
```

### 15. `UpdateRegistrarRequest`
- **File**: `app/Http/Requests/Admin/UpdateRegistrarRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'name'         => ['required', 'string', 'max:100'],
        'api_username' => ['nullable', 'string', 'max:255'],
        'api_key'      => ['nullable', 'string'],
        'api_secret'   => ['nullable', 'string'],
        'environment'  => ['required', 'string', 'in:production,sandbox'],
        'is_active'    => ['required', 'boolean'],
        'settings'     => ['nullable', 'array'],
    ];
}
```

### 16. `StoreInvoiceRequest`
- **File**: `app/Http/Requests/Admin/StoreInvoiceRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'user_id'          => ['required', 'integer', 'exists:users,id'],
        'currency'         => ['required', 'string', 'in:USD,BDT'],
        'due_date'         => ['required', 'date', 'after_or_equal:today'],
        'items'            => ['required', 'array', 'min:1'],
        'items.*.description' => ['required', 'string', 'max:255'],
        'items.*.amount'      => ['required', 'numeric', 'min:0.01'],
        'items.*.quantity'    => ['required', 'integer', 'min:1'],
        'notes'            => ['nullable', 'string'],
    ];
}
```

### 17. `UpdateInvoiceRequest`
- **File**: `app/Http/Requests/Admin/UpdateInvoiceRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'status'           => ['required', 'string', 'in:unpaid,paid,cancelled,refunded'],
        'due_date'         => ['required', 'date'],
        'notes'            => ['nullable', 'string'],
    ];
}
```

### 18. `RejectManualPaymentRequest`
- **File**: `app/Http/Requests/Admin/RejectManualPaymentRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
    ];
}
```

### 19. `AdminTicketReplyRequest`
- **File**: `app/Http/Requests/Admin/AdminTicketReplyRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'message'     => ['required', 'string', 'min:2'],
        'is_internal' => ['required', 'boolean'],
        'status'      => ['required', 'string', 'in:open,answered,in_progress,closed'],
        'attachment'  => ['nullable', 'file', 'max:5120', 'mimes:jpeg,png,jpg,gif,pdf,txt,zip'],
    ];
}
```

### 20. `UpdateEmailTemplateRequest`
- **File**: `app/Http/Requests/Admin/UpdateEmailTemplateRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'subject' => ['required', 'string', 'max:255'],
        'body'    => ['required', 'string'],
    ];
}
```

### 21. `StorePromoCodeRequest`
- **File**: `app/Http/Requests/Admin/StorePromoCodeRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'code'             => ['required', 'string', 'alpha_num', 'max:50', 'unique:promo_codes,code'],
        'type'             => ['required', 'string', 'in:percentage,fixed'],
        'value'            => ['required', 'numeric', 'min:0.01'],
        'max_uses'         => ['nullable', 'integer', 'min:1'],
        'valid_tlds'       => ['nullable', 'array'],
        'valid_tlds.*'     => ['string'],
        'starts_at'        => ['nullable', 'date'],
        'expires_at'       => ['nullable', 'date', 'after:starts_at'],
        'is_active'        => ['required', 'boolean'],
    ];
}
```

### 22. `UpdatePromoCodeRequest`
- **File**: `app/Http/Requests/Admin/UpdatePromoCodeRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    $id = $this->route('promoCode')?->id ?? $this->route('promoCode');
    return [
        'code'             => ['required', 'string', 'alpha_num', 'max:50', 'unique:promo_codes,code,'.$id],
        'type'             => ['required', 'string', 'in:percentage,fixed'],
        'value'            => ['required', 'numeric', 'min:0.01'],
        'max_uses'         => ['nullable', 'integer', 'min:1'],
        'valid_tlds'       => ['nullable', 'array'],
        'valid_tlds.*'     => ['string'],
        'starts_at'        => ['nullable', 'date'],
        'expires_at'       => ['nullable', 'date', 'after:starts_at'],
        'is_active'        => ['required', 'boolean'],
    ];
}
```

### 23. `StoreAnnouncementRequest`
- **File**: `app/Http/Requests/Admin/StoreAnnouncementRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'title'      => ['required', 'string', 'max:255'],
        'content'    => ['required', 'string'],
        'target'     => ['required', 'string', 'in:all,clients,resellers'],
        'is_active'  => ['required', 'boolean'],
    ];
}
```

### 24. `UpdateSystemSettingsRequest`
- **File**: `app/Http/Requests/Admin/UpdateSystemSettingsRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'company_name'           => ['required', 'string', 'max:255'],
        'contact_email'          => ['required', 'email', 'max:255'],
        'support_phone'          => ['nullable', 'string', 'max:50'],
        'default_currency'       => ['required', 'string', 'in:USD,BDT'],
        'maintenance_mode'       => ['required', 'boolean'],
        'maintenance_whitelist'  => ['nullable', 'string'],
    ];
}
```

### 25. `StoreCurrencyRequest`
- **File**: `app/Http/Requests/Admin/StoreCurrencyRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'code'          => ['required', 'string', 'size:3', 'uppercase', 'unique:currencies,code'],
        'name'          => ['required', 'string', 'max:50'],
        'symbol'        => ['required', 'string', 'max:10'],
        'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
        'symbol_position'=> ['required', 'string', 'in:before,after'],
        'is_default'    => ['required', 'boolean'],
    ];
}
```

### 26. `UpdateCurrencyRequest`
- **File**: `app/Http/Requests/Admin/UpdateCurrencyRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    $id = $this->route('currency')?->id ?? $this->route('currency');
    return [
        'code'          => ['required', 'string', 'size:3', 'uppercase', 'unique:currencies,code,'.$id],
        'name'          => ['required', 'string', 'max:50'],
        'symbol'        => ['required', 'string', 'max:10'],
        'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
        'symbol_position'=> ['required', 'string', 'in:before,after'],
        'is_default'    => ['required', 'boolean'],
    ];
}
```

---

## 4.3 Client & Reseller Web Form Requests (`App\Http\Requests\Client\`, `App\Http\Requests\Reseller\`)

### 27. `UpdateProfileRequest`
- **File**: `app/Http/Requests/Client/UpdateProfileRequest.php`
- **Authorization**: `public function authorize(): bool { return Auth::check(); }`
- **Validation Rules**:
```php
public function rules(): array
{
    $userId = $this->user()->id;
    return [
        'name'         => ['required', 'string', 'max:255'],
        'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$userId],
        'phone'        => ['required', 'string', 'max:30'],
        'company_name' => ['nullable', 'string', 'max:255'],
        'address'      => ['required', 'string', 'max:255'],
        'city'         => ['required', 'string', 'max:100'],
        'state'        => ['required', 'string', 'max:100'],
        'country'      => ['required', 'string', 'size:2'],
        'zip_code'     => ['required', 'string', 'max:20'],
    ];
}
```

### 28. `UpdatePasswordRequest`
- **File**: `app/Http/Requests/Client/UpdatePasswordRequest.php`
- **Authorization**: `public function authorize(): bool { return Auth::check(); }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'current_password' => ['required', 'string', 'current_password'],
        'password'         => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
    ];
}
```

### 29. `TwoFactorConfirmRequest`
- **File**: `app/Http/Requests/Client/TwoFactorConfirmRequest.php`
- **Authorization**: `public function authorize(): bool { return Auth::check(); }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
    ];
}
```

### 30. `DomainSearchRequest`
- **File**: `app/Http/Requests/Client/DomainSearchRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'domain' => ['required', 'string', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/'],
    ];
}
```

### 31. `RegisterDomainRequest`
- **File**: `app/Http/Requests/Client/RegisterDomainRequest.php`
- **Authorization**: `public function authorize(): bool { return Auth::check(); }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'domain'         => ['required', 'string', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/'],
        'years'          => ['required', 'integer', 'min:1', 'max:10'],
        'id_protection'  => ['nullable', 'boolean'],
        'nameservers'    => ['nullable', 'array', 'min:2', 'max:5'],
        'nameservers.*'  => ['string', 'regex:/^[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,}$/'],
        'payment_method' => ['required', 'string', 'in:wallet,bkash,stripe,binance,manual'],
    ];
}
```

### 32. `TransferDomainRequest`
- **File**: `app/Http/Requests/Client/TransferDomainRequest.php`
- **Authorization**: `public function authorize(): bool { return Auth::check(); }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'domain'         => ['required', 'string', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/'],
        'epp_code'       => ['required', 'string', 'min:4', 'max:128'],
        'id_protection'  => ['nullable', 'boolean'],
        'payment_method' => ['required', 'string', 'in:wallet,bkash,stripe,binance,manual'],
    ];
}
```

### 33. `UpdateNameserversRequest`
- **File**: `app/Http/Requests/Client/UpdateNameserversRequest.php`
- **Authorization**:
```php
public function authorize(): bool
{
    $domain = $this->route('domain');
    return $this->user()?->isAdmin() || ($domain && $domain->user_id === $this->user()?->id);
}
```
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'nameservers'   => ['required', 'array', 'min:2', 'max:5'],
        'nameservers.*' => ['required', 'string', 'regex:/^[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,}$/'],
    ];
}
```

### 34. `StoreDnsRecordRequest`
- **File**: `app/Http/Requests/Client/StoreDnsRecordRequest.php`
- **Authorization**:
```php
public function authorize(): bool
{
    $domain = $this->route('domain');
    return $this->user()?->isAdmin() || ($domain && $domain->user_id === $this->user()?->id);
}
```
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'type'     => ['required', 'string', 'in:A,AAAA,CNAME,MX,TXT,SRV'],
        'name'     => ['required', 'string', 'max:255'],
        'content'  => ['required', 'string', 'max:1000'],
        'ttl'      => ['required', 'integer', 'min:60', 'max:86400'],
        'priority' => ['nullable', 'integer', 'min:0', 'max:65535'],
    ];
}
```

### 35. `UpdateDnsRecordRequest`
- **File**: `app/Http/Requests/Client/UpdateDnsRecordRequest.php`
- **Authorization**:
```php
public function authorize(): bool
{
    $domain = $this->route('domain');
    return $this->user()?->isAdmin() || ($domain && $domain->user_id === $this->user()?->id);
}
```
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'type'     => ['required', 'string', 'in:A,AAAA,CNAME,MX,TXT,SRV'],
        'name'     => ['required', 'string', 'max:255'],
        'content'  => ['required', 'string', 'max:1000'],
        'ttl'      => ['required', 'integer', 'min:60', 'max:86400'],
        'priority' => ['nullable', 'integer', 'min:0', 'max:65535'],
    ];
}
```

### 36. `StoreChildNameserverRequest`
- **File**: `app/Http/Requests/Client/StoreChildNameserverRequest.php`
- **Authorization**:
```php
public function authorize(): bool
{
    $domain = $this->route('domain');
    return $this->user()?->isAdmin() || ($domain && $domain->user_id === $this->user()?->id);
}
```
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'hostname'   => ['required', 'string', 'regex:/^[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,}$/'],
        'ip_address' => ['required', 'ip'],
    ];
}
```

### 37. `UpdateChildNameserverRequest`
- **File**: `app/Http/Requests/Client/UpdateChildNameserverRequest.php`
- **Authorization**:
```php
public function authorize(): bool
{
    $domain = $this->route('domain');
    return $this->user()?->isAdmin() || ($domain && $domain->user_id === $this->user()?->id);
}
```
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'ip_address' => ['required', 'ip'],
    ];
}
```

### 38. `UpdateWhoisRequest`
- **File**: `app/Http/Requests/Client/UpdateWhoisRequest.php`
- **Authorization**:
```php
public function authorize(): bool
{
    $domain = $this->route('domain');
    return $this->user()?->isAdmin() || ($domain && $domain->user_id === $this->user()?->id);
}
```
- **Validation Rules**:
```php
public function rules(): array
{
    $contacts = ['registrant', 'admin', 'tech', 'billing'];
    $rules = [];

    foreach ($contacts as $c) {
        $rules["{$c}.first_name"]   = ['required', 'string', 'max:100'];
        $rules["{$c}.last_name"]    = ['required', 'string', 'max:100'];
        $rules["{$c}.company"]      = ['nullable', 'string', 'max:255'];
        $rules["{$c}.email"]        = ['required', 'email', 'max:255'];
        $rules["{$c}.phone"]        = ['required', 'string', 'max:30'];
        $rules["{$c}.address1"]     = ['required', 'string', 'max:255'];
        $rules["{$c}.address2"]     = ['nullable', 'string', 'max:255'];
        $rules["{$c}.city"]         = ['required', 'string', 'max:100'];
        $rules["{$c}.state"]        = ['required', 'string', 'max:100'];
        $rules["{$c}.postal_code"]  = ['required', 'string', 'max:20'];
        $rules["{$c}.country_code"] = ['required', 'string', 'size:2'];
    }

    return $rules;
}
```

### 39. `StoreEmailForwardRequest`
- **File**: `app/Http/Requests/Client/StoreEmailForwardRequest.php`
- **Authorization**:
```php
public function authorize(): bool
{
    $domain = $this->route('domain');
    return $this->user()?->isAdmin() || ($domain && $domain->user_id === $this->user()?->id);
}
```
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'prefix'          => ['required', 'string', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'],
        'forward_to_email'=> ['required', 'email', 'max:255'],
    ];
}
```

### 40. `StoreUrlForwardRequest`
- **File**: `app/Http/Requests/Client/StoreUrlForwardRequest.php`
- **Authorization**:
```php
public function authorize(): bool
{
    $domain = $this->route('domain');
    return $this->user()?->isAdmin() || ($domain && $domain->user_id === $this->user()?->id);
}
```
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'subdomain'   => ['nullable', 'string', 'regex:/^[a-zA-Z0-9\-]+$/'],
        'target_url'  => ['required', 'url', 'max:1000'],
        'redirect_type'=> ['required', 'string', 'in:301,302,masked'],
    ];
}
```

### 41. `AddFundsRequest`
- **File**: `app/Http/Requests/Client/AddFundsRequest.php`
- **Authorization**: `public function authorize(): bool { return Auth::check(); }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'amount'         => ['required', 'numeric', 'min:5.00', 'max:10000.00'],
        'currency'       => ['required', 'string', 'in:USD,BDT'],
        'payment_method' => ['required', 'string', 'in:bkash,stripe,binance,manual'],
        'sender_number'  => ['required_if:payment_method,manual', 'nullable', 'string', 'max:30'],
        'transaction_id' => ['required_if:payment_method,manual', 'nullable', 'string', 'max:100'],
        'deposit_slip'   => ['nullable', 'file', 'max:3072', 'mimes:jpeg,png,jpg,pdf'],
    ];
}
```

### 42. `StoreTicketRequest`
- **File**: `app/Http/Requests/Client/StoreTicketRequest.php`
- **Authorization**: `public function authorize(): bool { return Auth::check(); }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'subject'    => ['required', 'string', 'max:255'],
        'department' => ['required', 'string', 'in:technical,billing,domain_support,general'],
        'priority'   => ['required', 'string', 'in:low,medium,high,urgent'],
        'domain_id'  => ['nullable', 'integer', 'exists:domains,id'],
        'message'    => ['required', 'string', 'min:10'],
        'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpeg,png,jpg,pdf,txt,zip'],
    ];
}
```

### 43. `TicketReplyRequest`
- **File**: `app/Http/Requests/Client/TicketReplyRequest.php`
- **Authorization**:
```php
public function authorize(): bool
{
    $ticket = $this->route('ticket');
    return $ticket && $ticket->user_id === $this->user()?->id;
}
```
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'message'    => ['required', 'string', 'min:2'],
        'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpeg,png,jpg,pdf,txt,zip'],
    ];
}
```

### 44. `StoreApiKeyRequest`
- **File**: `app/Http/Requests/Reseller/StoreApiKeyRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isReseller() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'name'                   => ['required', 'string', 'max:100'],
        'ip_whitelist'           => ['nullable', 'array'],
        'ip_whitelist.*'         => ['ip'],
        'rate_limit_per_minute'  => ['nullable', 'integer', 'min:10', 'max:300'],
    ];
}
```

### 45. `UpdateApiKeyRequest`
- **File**: `app/Http/Requests/Reseller/UpdateApiKeyRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isReseller() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'name'                   => ['required', 'string', 'max:100'],
        'ip_whitelist'           => ['nullable', 'array'],
        'ip_whitelist.*'         => ['ip'],
        'rate_limit_per_minute'  => ['nullable', 'integer', 'min:10', 'max:300'],
    ];
}
```

### 46. `StoreWebhookRequest`
- **File**: `app/Http/Requests/Reseller/StoreWebhookRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isReseller() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'url'            => ['required', 'url', 'max:255'],
        'events'         => ['required', 'array', 'min:1'],
        'events.*'       => ['string', 'in:domain.registered,domain.renewed,domain.transferred,domain.expired,dns.updated'],
    ];
}
```

### 47. `UpdateWhitelabelRequest`
- **File**: `app/Http/Requests/Reseller/UpdateWhitelabelRequest.php`
- **Authorization**: `public function authorize(): bool { return $this->user()?->isReseller() ?? false; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'company_brand_name'  => ['nullable', 'string', 'max:255'],
        'default_ns1'         => ['nullable', 'string', 'regex:/^[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,}$/'],
        'default_ns2'         => ['nullable', 'string', 'regex:/^[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,}$/'],
        'whois_company_name'  => ['nullable', 'string', 'max:255'],
    ];
}
```

---

## 4.4 External API V1 Form Requests (`App\Http\Requests\Api\V1\`)

All API form requests override `failedValidation` to throw `HttpResponseException` returning uniform JSON errors with HTTP status `422 Unprocessable Entity`:
```php
protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
{
    throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
        'status'  => 'error',
        'code'    => 422,
        'message' => 'Validation error.',
        'errors'  => $validator->errors()
    ], 422));
}
```

### 48. `ApiDomainCheckRequest`
- **File**: `app/Http/Requests/Api/V1/ApiDomainCheckRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'domain' => ['required', 'string', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/'],
    ];
}
```

### 49. `ApiRegisterDomainRequest`
- **File**: `app/Http/Requests/Api/V1/ApiRegisterDomainRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'domain'                  => ['required', 'string', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/'],
        'years'                   => ['required', 'integer', 'min:1', 'max:10'],
        'id_protection'           => ['nullable', 'boolean'],
        'nameservers'             => ['nullable', 'array', 'min:2', 'max:5'],
        'nameservers.*'           => ['string', 'regex:/^[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,}$/'],
        'contacts'                => ['required', 'array'],
        'contacts.registrant'     => ['required', 'array'],
        'contacts.registrant.first_name'   => ['required', 'string', 'max:100'],
        'contacts.registrant.last_name'    => ['required', 'string', 'max:100'],
        'contacts.registrant.email'        => ['required', 'email', 'max:255'],
        'contacts.registrant.phone'        => ['required', 'string', 'max:30'],
        'contacts.registrant.address1'     => ['required', 'string', 'max:255'],
        'contacts.registrant.city'         => ['required', 'string', 'max:100'],
        'contacts.registrant.state'        => ['required', 'string', 'max:100'],
        'contacts.registrant.postal_code'  => ['required', 'string', 'max:20'],
        'contacts.registrant.country_code' => ['required', 'string', 'size:2'],
    ];
}
```

### 50. `ApiTransferDomainRequest`
- **File**: `app/Http/Requests/Api/V1/ApiTransferDomainRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'domain'                  => ['required', 'string', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/'],
        'epp_code'                => ['required', 'string', 'min:4', 'max:128'],
        'id_protection'           => ['nullable', 'boolean'],
        'contacts'                => ['nullable', 'array'],
    ];
}
```

### 51. `ApiRenewDomainRequest`
- **File**: `app/Http/Requests/Api/V1/ApiRenewDomainRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'years' => ['required', 'integer', 'min:1', 'max:10'],
    ];
}
```

### 52. `ApiUpdateNameserversRequest`
- **File**: `app/Http/Requests/Api/V1/ApiUpdateNameserversRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'nameservers'   => ['required', 'array', 'min:2', 'max:5'],
        'nameservers.*' => ['required', 'string', 'regex:/^[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,}$/'],
    ];
}
```

### 53. `ApiStoreChildNameserverRequest`
- **File**: `app/Http/Requests/Api/V1/ApiStoreChildNameserverRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'hostname'   => ['required', 'string', 'regex:/^[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,}$/'],
        'ip_address' => ['required', 'ip'],
    ];
}
```

### 54. `ApiUpdateChildNameserverRequest`
- **File**: `app/Http/Requests/Api/V1/ApiUpdateChildNameserverRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'ip_address' => ['required', 'ip'],
    ];
}
```

### 55. `ApiStoreDnsRecordRequest`
- **File**: `app/Http/Requests/Api/V1/ApiStoreDnsRecordRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'type'     => ['required', 'string', 'in:A,AAAA,CNAME,MX,TXT,SRV'],
        'name'     => ['required', 'string', 'max:255'],
        'content'  => ['required', 'string', 'max:1000'],
        'ttl'      => ['required', 'integer', 'min:60', 'max:86400'],
        'priority' => ['nullable', 'integer', 'min:0', 'max:65535'],
    ];
}
```

### 56. `ApiUpdateDnsRecordRequest`
- **File**: `app/Http/Requests/Api/V1/ApiUpdateDnsRecordRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'type'     => ['required', 'string', 'in:A,AAAA,CNAME,MX,TXT,SRV'],
        'name'     => ['required', 'string', 'max:255'],
        'content'  => ['required', 'string', 'max:1000'],
        'ttl'      => ['required', 'integer', 'min:60', 'max:86400'],
        'priority' => ['nullable', 'integer', 'min:0', 'max:65535'],
    ];
}
```

### 57. `ApiUpdateWhoisRequest`
- **File**: `app/Http/Requests/Api/V1/ApiUpdateWhoisRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'registrant'                   => ['required', 'array'],
        'registrant.first_name'        => ['required', 'string', 'max:100'],
        'registrant.last_name'         => ['required', 'string', 'max:100'],
        'registrant.company'           => ['nullable', 'string', 'max:255'],
        'registrant.email'             => ['required', 'email', 'max:255'],
        'registrant.phone'             => ['required', 'string', 'max:30'],
        'registrant.address1'          => ['required', 'string', 'max:255'],
        'registrant.city'              => ['required', 'string', 'max:100'],
        'registrant.state'             => ['required', 'string', 'max:100'],
        'registrant.postal_code'       => ['required', 'string', 'max:20'],
        'registrant.country_code'      => ['required', 'string', 'size:2'],
        'admin'                        => ['nullable', 'array'],
        'tech'                         => ['nullable', 'array'],
        'billing'                      => ['nullable', 'array'],
    ];
}
```

### 58. `ApiStoreEmailForwardRequest`
- **File**: `app/Http/Requests/Api/V1/ApiStoreEmailForwardRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'prefix'           => ['required', 'string', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'],
        'forward_to_email' => ['required', 'email', 'max:255'],
    ];
}
```

### 59. `ApiStoreUrlForwardRequest`
- **File**: `app/Http/Requests/Api/V1/ApiStoreUrlForwardRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'subdomain'     => ['nullable', 'string', 'regex:/^[a-zA-Z0-9\-]+$/'],
        'target_url'    => ['required', 'url', 'max:1000'],
        'redirect_type' => ['required', 'string', 'in:301,302,masked'],
    ];
}
```

### 60. `ApiStoreWebhookRequest`
- **File**: `app/Http/Requests/Api/V1/ApiStoreWebhookRequest.php`
- **Authorization**: `public function authorize(): bool { return true; }`
- **Validation Rules**:
```php
public function rules(): array
{
    return [
        'url'      => ['required', 'url', 'max:255'],
        'events'   => ['required', 'array', 'min:1'],
        'events.*' => ['string', 'in:domain.registered,domain.renewed,domain.transferred,domain.expired,dns.updated'],
    ];
}
```

---

# Summary Metrics

| Category | Total Count |
| :--- | :--- |
| **Custom Middleware Classes** | 11 |
| **Public & Web Routes** | 20 |
| **Authentication Routes** | 14 |
| **Admin Panel Routes** | 56 |
| **Client Panel Routes** | 39 |
| **Reseller Panel Routes** | 21 |
| **Reseller REST API v1 Endpoints** | 44 |
| **Total Route Count** | **194 Endpoints** |
| **Controller Classes** | **45 Controllers** |
| **Total Controller Actions** | **200+ Methods** |
| **Form Request Classes** | **60 Form Requests** |
