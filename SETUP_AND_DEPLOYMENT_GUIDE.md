# Domain Reseller Platform - Setup & Deployment Guide

Welcome to your production-ready **Domain Reseller Platform & Multi-Registrar Wholesale Engine**.

This platform allows you to operate as a domain registrar/reseller hub:
1. **Upstream Aggregation**: Connects to Namecheap, Spaceship, Dynadot, AtakDomain, and NameSilo.
2. **Wholesale API & WHMCS Module**: Resellers can connect their own WHMCS, Blesta, or custom billing platforms via your 44-endpoint REST API.
3. **Retail Client Portal**: End customers can register, renew, transfer, and manage authoritative nameservers, hosted DNS records, glue child nameservers, WHOIS contacts, email forwards, and URL redirects.
4. **Multi-Currency Ledger**: Core accounting ledger strictly holds USD amounts, with real-time conversion and checkout support for Bangladeshi Taka (BDT) and other currencies.
5. **Multiple Payment Gateways**: Instant automated checkouts via bKash, Stripe (Cards), Binance Pay (Crypto), and Manual Bank Transfer verification.
6. **Strict Shared Hosting Compatibility**: Runs seamlessly on cPanel, DirectAdmin, or Plesk with standard PHP 8.2+ and MySQL 8.0 / MariaDB 10.4+. No Node.js or Redis background daemon dependencies required.

---

## 📁 Repository Structure Overview

```
f:\DomainReseller\
├── app/
│   ├── Console/Commands/       # 8 automated scheduler commands
│   ├── Contracts/              # Registrar & Payment gateway interfaces
│   ├── DTOs/                   # 16 Data Transfer Objects
│   ├── Events/                 # DomainRegistered, InvoicePaid
│   ├── Http/
│   │   ├── Controllers/        # Auth, Client, Reseller, Admin & 44-endpoint API V1
│   │   └── Middleware/         # 12 security, auth, IP whitelist, rate limiters
│   ├── Jobs/                   # RegisterDomain, RenewDomain, TransferDomain, Webhooks
│   ├── Models/                 # 34 Eloquent Models
│   └── Services/               # RegistrarManager, PaymentManager, DomainOrder, FX, Webhook
├── bootstrap/app.php           # App configuration, middleware aliases & CSRF exceptions
├── config/                     # All 16 configuration files
├── database/
│   ├── migrations/             # All 34 database migrations
│   └── seeders/                # Currencies, Settings, Registrars, TLDs, Templates, Admin User
├── public/                     # Public webroot (.htaccess, index.php)
├── resources/views/            # 112 Blade templates (Client, Reseller, Admin, Public, Auth)
├── routes/                     # web, client, reseller, admin, api, auth, console
└── whmcs-module/               # Ready-to-distribute WHMCS Registrar Module package
```

---

## 🚀 Shared Hosting Deployment (cPanel / DirectAdmin)

### Step 1: Upload Files
1. Zip the project contents (excluding `storage/logs/*` and cache).
2. In cPanel File Manager, upload and extract into your target directory:
   - For main domain: Place files in `/home/username/domainreseller/` and point Document Root to `/home/username/domainreseller/public/`.
   - Alternatively, place contents of `public/` inside `public_html/` and the rest of the application files one level above `public_html/`.

### Step 2: Configure Environment (.env)
Copy `.env.example` to `.env` and fill in your database credentials:

```env
APP_NAME=DomainReseller
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_cpanel_dbname
DB_USERNAME=your_cpanel_dbuser
DB_PASSWORD=your_cpanel_dbpassword

# Shared hosting queue & cache drivers (No Redis daemon needed)
QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file

# Mail / SMTP configuration
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465
MAIL_USERNAME=support@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=support@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 3: Run Database Migrations & Seeders
Via cPanel Terminal or SSH:

```bash
php artisan key:generate
php artisan migrate --seed
```

This installs all 34 database tables and seeds:
- Supported Currencies (USD primary, BDT conversion).
- System settings & default nameservers.
- Registrar Modules (Namecheap, Spaceship, Dynadot, AtakDomain, NameSilo).
- Default TLD catalog (`.com`, `.net`, `.org`, `.xyz`, `.io`, `.co`, etc.).
- Default Email notification templates.
- Default Super Administrator account:
  - **Email**: `admin@domainreseller.local`
  - **Password**: `Admin@Password123!` *(Change immediately upon first login)*

### Step 4: Configure cPanel Cron Job
In cPanel &rarr; **Cron Jobs**, set the schedule to **Once Per Minute** (`* * * * *`):

```bash
* * * * * /usr/local/bin/php /home/username/domainreseller/artisan schedule:run >> /dev/null 2>&1
```

The scheduled runner automatically handles:
- Checking domain expiration alerts (30, 15, 7, 1 day warnings).
- Processing automated domain renewals.
- Syncing domain status from upstream registrars.
- Monitoring upstream registrar wallet balances.
- Fetching live currency exchange rates (USD to BDT).
- Retrying failed webhook payloads.
- Processing database queue jobs (`queue:work --stop-when-empty`).

---

## 💳 Payment Gateway Configurations

Configure your payment gateway API keys in `.env` or in `config/payment_gateways.php`:

### 1. bKash (Direct Tokenized API)
```env
BKASH_APP_KEY=your_app_key
BKASH_APP_SECRET=your_app_secret
BKASH_USERNAME=your_username
BKASH_PASSWORD=your_password
BKASH_SANDBOX=false
```

### 2. Stripe (Cards)
```env
STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_SECRET_KEY=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

### 3. Binance Pay (Cryptocurrency / USDT)
```env
BINANCE_PAY_API_KEY=your_binance_key
BINANCE_PAY_SECRET_KEY=your_binance_secret
```

### 4. Manual Offline Bank Transfers
Admin can verify and approve offline bank receipts in **Admin Console &rarr; Payments &rarr; Manual Requests**.

---

## 🔌 Upstream Registrar API Configuration

In **Admin Console &rarr; Upstream Registrars**, enter your API credentials for each supplier:
- **Namecheap**: API User, API Key, Account Username, Whitelisted Server IP.
- **Spaceship**: API Key, API Secret.
- **Dynadot**: API Key.
- **AtakDomain**: API Key, Secret.
- **NameSilo**: API Key.

Click **Test API** and **Sync Balance** to verify real-time connectivity.

---

## 📦 Distributing WHMCS Module to Your Resellers

The module files are ready inside `/whmcs-module/modules/registrars/domainreseller/`:
1. Zip the `domainreseller` folder and distribute it to your resellers (or let them download it from **Reseller Portal &rarr; API & WHMCS Guide**).
2. Reseller generates an API Key in their portal (**Reseller Portal &rarr; API Credentials**).
3. Reseller installs the module into their WHMCS: `/modules/registrars/domainreseller/`.
4. Reseller activates the module in WHMCS and enters your API URL, Public Key, and Secret Key.
5. All domain orders from the reseller's WHMCS will automatically register and provision on your platform.
