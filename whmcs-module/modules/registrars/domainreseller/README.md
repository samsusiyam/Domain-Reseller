# DomainReseller WHMCS Registrar Module

Official WHMCS Domain Registrar Integration Module for the **DomainReseller Wholesale Platform**.

Sell domain registrations, renewals, and incoming transfers directly from your WHMCS installation under your own brand with 100% white-label automation.

---

## 🚀 Features

- **Instant Domain Registration**: Automated real-time registrations triggered upon invoice payment in WHMCS.
- **Automated Renewals**: Multi-year domain renewals processed seamlessly.
- **Domain Transfers**: Supports incoming domain transfers with EPP / Auth code verification.
- **Authoritative Nameservers**: Full client and admin ability to view and edit nameservers (NS1 - NS5).
- **Registrar Lock (Transfer Lock)**: Toggle domain transfer locks with a single click.
- **Hosted DNS Management**: Add, update, and remove A, CNAME, MX, and TXT zone records directly inside WHMCS Client Area.
- **WHOIS ID Protection (Privacy)**: Toggle WHOIS contact cloaking and privacy shields.
- **WHOIS Contact Sync**: View and modify Registrant, Administrative, Technical, and Billing contact cards.
- **EPP / Auth Code Fetch**: Clients and admins can retrieve domain authorization codes for transfers.
- **Automated Synchronization Cron**: Synchronizes domain statuses (Active / Expired) and real expiration dates with your upstream ledger daily.
- **Transfer Sync Cron**: Automatically marks transfer orders as completed once the domain moves across registries.

---

## 📦 Installation Instructions

### Step 1: Upload Module Files
Copy the `domainreseller` folder to your WHMCS server inside the registrar modules directory:

```
/path_to_whmcs/modules/registrars/domainreseller/
├── domainreseller.php
├── hooks.php
├── lib/
│   └── ApiClient.php
└── README.md
```

### Step 2: Obtain API Credentials
1. Log in to your **Reseller Portal** on our platform.
2. Navigate to **API & Integrations** &rarr; **[API Credentials](/reseller/api-keys)**.
3. Click **Generate API Key Pair**.
4. Note down your:
   - **API Endpoint URL**: `https://yourplatform.com/api/v1`
   - **API Public Key**: `pk_live_...`
   - **API Secret Key**: `sk_live_...`
5. **Whitelist your WHMCS Server IP**: In the IP whitelist field, ensure your WHMCS server's outbound dedicated IPv4 address is listed.

### Step 3: Activate in WHMCS Admin
1. In your WHMCS Admin Area, go to:
   - **WHMCS v8.x+**: `Configuration` (wrench icon) &rarr; `System Settings` &rarr; `Domain Registrars`
   - **WHMCS v7.x**: `Setup` &rarr; `Products/Services` &rarr; `Domain Registrars`
2. Scroll to **DomainReseller** and click **Activate**.
3. Fill in your configuration values:
   - **API Endpoint URL**: `https://yourplatform.com/api/v1`
   - **API Public Key**: Paste your `pk_...`
   - **API Secret Key**: Paste your `sk_...`
   - **Enable Auto-Renew**: Check if desired.
4. Click **Save Changes**.

---

## 🌐 Configuring TLDs for Automated Registration

1. In WHMCS, navigate to **Domain Pricing** (`System Settings` &rarr; `Domain Pricing`).
2. For each TLD you want to sell (e.g., `.com`, `.net`, `.org`, `.xyz`):
   - Set **Auto Registration** to **DomainReseller**.
   - Check **DNS Management**, **Email Forwarding**, and **ID Protection** addons as applicable.
   - Enter your retail sell prices for 1 to 10 years.
3. Save changes.

---

## ⏰ Cron Synchronization Setup

To ensure WHMCS keeps domain expiration dates and statuses in sync:

1. Ensure your standard WHMCS cron is running every 5 minutes.
2. In WHMCS Admin, verify **Domain Status Sync** is enabled under `General Settings` &rarr; `Domains`.
3. The module's `domainreseller_Sync()` and `domainreseller_TransferSync()` will run automatically to keep records up to date.

---

## 🛠️ Testing & Troubleshooting

### Test Ping
You can verify communication with the API by checking `/api/v1/system/ping` or testing a single nameserver save in WHMCS domain management.

### Common Issues
1. **HTTP 401 Unauthorized**:
   - Check that your `ApiKey` and `ApiSecret` are copied without trailing spaces.
2. **HTTP 403 Forbidden / IP Not Whitelisted**:
   - Your WHMCS server IP changed or is not in the allowed IP Whitelist. Add your server's outbound IP address in the Reseller Portal.
3. **HTTP 422 Insufficient Balance**:
   - Your Reseller Wallet balance has run out. Visit **Wallet & Funds** in your Reseller Portal to add credit via bKash, Stripe, Binance, or Bank.

---

## 📞 Reseller Support
If you need developer integration assistance, open a support ticket under the **API & Development** department in your Reseller Dashboard.
