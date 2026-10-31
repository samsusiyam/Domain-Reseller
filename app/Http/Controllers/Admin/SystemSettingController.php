<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SystemSettingController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * General settings page.
     */
    public function general()
    {
        $settings = SystemSetting::where('group', 'general')->pluck('setting_value', 'setting_key');
        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Save general settings.
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'company_name'   => ['required', 'string', 'max:255'],
            'support_email'  => ['required', 'email'],
            'support_phone'  => ['nullable', 'string', 'max:50'],
            'currency_base'  => ['required', 'string', 'max:3'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'logo_light'     => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:4096'],
            'logo_dark'      => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:4096'],
            'favicon'        => ['nullable', 'file', 'mimes:png,ico,svg', 'max:2048'],
        ]);

        foreach ($request->only(['company_name', 'support_email', 'support_phone', 'currency_base', 'tax_percentage', 'portal_title', 'footer_text']) as $key => $value) {
            SystemSetting::set($key, $value, 'general');
        }

        if ($request->hasFile('logo_light')) {
            $path = $request->file('logo_light')->store('branding', 'public');
            SystemSetting::set('logo_light', '/storage/' . $path, 'general');
        }

        if ($request->hasFile('logo_dark')) {
            $path = $request->file('logo_dark')->store('branding', 'public');
            SystemSetting::set('logo_dark', '/storage/' . $path, 'general');
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('branding', 'public');
            SystemSetting::set('favicon', '/storage/' . $path, 'general');
        }

        $this->activityLogger->log('settings_updated', 'Admin updated general system settings and brand logos');

        return back()->with('success', 'General settings and brand logos saved successfully.');
    }

    /**
     * Mail / SMTP configuration page.
     */
    public function email()
    {
        $settings = [
            'mail_mailer'     => SystemSetting::get('mail_mailer', 'smtp'),
            'mail_host'       => SystemSetting::get('mail_host', 'smtp.mailtrap.io'),
            'mail_port'       => SystemSetting::get('mail_port', '2525'),
            'mail_username'   => SystemSetting::get('mail_username', ''),
            'mail_encryption' => SystemSetting::get('mail_encryption', 'tls'),
            'mail_from_address' => SystemSetting::get('mail_from_address', 'support@example.com'),
            'mail_from_name'  => SystemSetting::get('mail_from_name', config('app.name')),
        ];

        return view('admin.settings.email', compact('settings'));
    }

    /**
     * Save email / SMTP settings.
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'mail_host'         => ['required', 'string'],
            'mail_port'         => ['required', 'integer'],
            'mail_from_address' => ['required', 'email'],
            'mail_from_name'    => ['required', 'string'],
        ]);

        SystemSetting::set('mail_mailer', $request->input('mail_mailer', 'smtp'), 'mail');
        SystemSetting::set('mail_host', $request->input('mail_host'), 'mail');
        SystemSetting::set('mail_port', $request->input('mail_port'), 'mail');
        SystemSetting::set('mail_username', $request->input('mail_username'), 'mail');
        if ($request->filled('mail_password')) {
            SystemSetting::set('mail_password', $request->input('mail_password'), 'mail', true);
        }
        SystemSetting::set('mail_encryption', $request->input('mail_encryption', 'tls'), 'mail');
        SystemSetting::set('mail_from_address', $request->input('mail_from_address'), 'mail');
        SystemSetting::set('mail_from_name', $request->input('mail_from_name'), 'mail');

        $this->activityLogger->log('email_settings_updated', 'Admin updated SMTP mail configuration');

        return back()->with('success', 'Email settings saved.');
    }

    /**
     * Test SMTP connection.
     */
    public function testEmail()
    {
        $admin = auth()->user();

        try {
            Mail::raw('This is a test email from ' . config('app.name') . ' to verify SMTP configuration.', function ($message) use ($admin) {
                $message->to($admin->email)
                    ->subject('SMTP Verification Test - ' . config('app.name'));
            });

            return response()->json([
                'success' => true,
                'message' => "Test email successfully sent to {$admin->email}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "SMTP Error: " . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Security configuration page.
     */
    public function security()
    {
        $settings = [
            'enforce_2fa'        => SystemSetting::get('enforce_2fa', 'false'),
            'session_lifetime'   => SystemSetting::get('session_lifetime', '120'),
            'api_rate_limit'     => SystemSetting::get('api_rate_limit', '120'),
            'login_max_attempts' => SystemSetting::get('login_max_attempts', '5'),
        ];

        return view('admin.settings.security', compact('settings'));
    }

    /**
     * Save security settings.
     */
    public function updateSecurity(Request $request)
    {
        $request->validate([
            'session_lifetime'   => ['required', 'integer', 'min:15'],
            'api_rate_limit'     => ['required', 'integer', 'min:30'],
            'login_max_attempts' => ['required', 'integer', 'min:3', 'max:20'],
        ]);

        SystemSetting::set('enforce_2fa', $request->boolean('enforce_2fa') ? 'true' : 'false', 'security');
        SystemSetting::set('session_lifetime', $request->input('session_lifetime'), 'security');
        SystemSetting::set('api_rate_limit', $request->input('api_rate_limit'), 'security');
        SystemSetting::set('login_max_attempts', $request->input('login_max_attempts'), 'security');

        $this->activityLogger->log('security_settings_updated', 'Admin updated platform security settings');

        return back()->with('success', 'Security settings saved.');
    }

    /**
     * Payment Gateways configuration page.
     */
    public function gateways()
    {
        $settings = [
            // bKash Personal
            'bkash_personal_enabled' => SystemSetting::get('bkash_personal_enabled', 'true'),
            'bkash_personal_number'  => SystemSetting::get('bkash_personal_number', '01849693630'),

            // bKash Merchant (Tokenized)
            'bkash_enabled'          => SystemSetting::get('bkash_enabled', 'false'),
            'bkash_mode'             => SystemSetting::get('bkash_mode', 'sandbox'),
            'bkash_app_key'          => SystemSetting::get('bkash_app_key', ''),
            'bkash_app_secret'       => SystemSetting::get('bkash_app_secret', ''),
            'bkash_username'         => SystemSetting::get('bkash_username', ''),
            'bkash_password'         => SystemSetting::get('bkash_password', ''),

            // SSLCommerz
            'sslcommerz_enabled'     => SystemSetting::get('sslcommerz_enabled', 'false'),
            'sslcommerz_mode'        => SystemSetting::get('sslcommerz_mode', 'sandbox'),
            'sslcommerz_store_id'    => SystemSetting::get('sslcommerz_store_id', ''),
            'sslcommerz_store_passwd'=> SystemSetting::get('sslcommerz_store_passwd', ''),

            // PipraPay
            'piprapay_enabled'       => SystemSetting::get('piprapay_enabled', 'false'),
            'piprapay_api_key'       => SystemSetting::get('piprapay_api_key', ''),
            'piprapay_api_url'       => SystemSetting::get('piprapay_api_url', 'https://piprapay.com/api'),

            // UddoktaPay
            'uddoktapay_enabled'     => SystemSetting::get('uddoktapay_enabled', 'true'),
            'uddoktapay_api_key'     => SystemSetting::get('uddoktapay_api_key', ''),
            'uddoktapay_api_url'     => SystemSetting::get('uddoktapay_api_url', 'https://pay.hostnibo.com/api'),

            // Binance Pay Merchant
            'binance_enabled'        => SystemSetting::get('binance_enabled', 'true'),
            'binance_merchant_id'    => SystemSetting::get('binance_merchant_id', ''),
            'binance_merchant_api_key'    => SystemSetting::get('binance_merchant_api_key', SystemSetting::get('binance_api_key', '')),
            'binance_merchant_secret_key' => SystemSetting::get('binance_merchant_secret_key', SystemSetting::get('binance_secret_key', '')),

            // Binance Personal Account (Direct Pay ID + Spot API)
            'binance_personal_enabled'      => SystemSetting::get('binance_personal_enabled', 'true'),
            'binance_personal_pay_id'       => SystemSetting::get('binance_personal_pay_id', '123456789'),
            'binance_personal_email'        => SystemSetting::get('binance_personal_email', 'payments@hostnibo.com'),
            'binance_personal_usdt_address' => SystemSetting::get('binance_personal_usdt_address', ''),
            'binance_personal_network'      => SystemSetting::get('binance_personal_network', 'TRC20'),
            'binance_personal_api_key'      => SystemSetting::get('binance_personal_api_key', ''),
            'binance_personal_secret_key'   => SystemSetting::get('binance_personal_secret_key', ''),

            // Stripe Cards
            'stripe_enabled'         => SystemSetting::get('stripe_enabled', 'false'),
            'stripe_key'             => SystemSetting::get('stripe_key', ''),
            'stripe_secret'          => SystemSetting::get('stripe_secret', ''),
            'stripe_webhook_secret'  => SystemSetting::get('stripe_webhook_secret', ''),

            // Bank Wire / Manual
            'bank_transfer_enabled'  => SystemSetting::get('bank_transfer_enabled', 'true'),
            'bank_name'              => SystemSetting::get('bank_name', 'City Bank Ltd'),
            'bank_account_name'      => SystemSetting::get('bank_account_name', 'HostNibo Technologies Ltd'),
            'bank_account_number'    => SystemSetting::get('bank_account_number', '1100223344001'),
            'bank_routing_number'    => SystemSetting::get('bank_routing_number', '225272630'),
            'bank_instructions'      => SystemSetting::get('bank_instructions', 'Send payment with your Invoice ID as reference.'),
        ];

        return view('admin.settings.gateways', compact('settings'));
    }

    /**
     * Save payment gateway settings.
     */
    public function updateGateways(Request $request)
    {
        $keys = [
            'bkash_personal_enabled', 'bkash_personal_number',
            'bkash_enabled', 'bkash_mode', 'bkash_app_key', 'bkash_app_secret', 'bkash_username', 'bkash_password',
            'sslcommerz_enabled', 'sslcommerz_mode', 'sslcommerz_store_id', 'sslcommerz_store_passwd',
            'piprapay_enabled', 'piprapay_api_key', 'piprapay_api_url',
            'uddoktapay_enabled', 'uddoktapay_api_key', 'uddoktapay_api_url',
            'binance_enabled', 'binance_merchant_id', 'binance_merchant_api_key', 'binance_merchant_secret_key',
            'binance_personal_enabled', 'binance_personal_pay_id', 'binance_personal_email', 'binance_personal_usdt_address', 'binance_personal_network', 'binance_personal_api_key', 'binance_personal_secret_key',
            'stripe_enabled', 'stripe_key', 'stripe_secret', 'stripe_webhook_secret',
            'bank_transfer_enabled', 'bank_name', 'bank_account_name', 'bank_account_number', 'bank_routing_number', 'bank_instructions',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                SystemSetting::set($key, $request->input($key), 'payment');
            }
        }

        $this->activityLogger->log('settings_updated', 'Admin updated payment gateway credentials');

        return back()->with('success', 'Payment gateway credentials saved successfully.');
    }
    /**
     * Default Nameservers configuration page.
     */
    public function nameservers()
    {
        $settings = [
            'default_ns1' => SystemSetting::get('default_ns1', 'ns1.hostnibo.com'),
            'default_ns2' => SystemSetting::get('default_ns2', 'ns2.hostnibo.com'),
            'default_ns3' => SystemSetting::get('default_ns3', ''),
            'default_ns4' => SystemSetting::get('default_ns4', ''),
        ];

        return view('admin.settings.nameservers', compact('settings'));
    }

    /**
     * Save default nameservers.
     */
    public function updateNameservers(Request $request)
    {
        $request->validate([
            'default_ns1' => ['required', 'string', 'max:255'],
            'default_ns2' => ['required', 'string', 'max:255'],
            'default_ns3' => ['nullable', 'string', 'max:255'],
            'default_ns4' => ['nullable', 'string', 'max:255'],
        ]);

        SystemSetting::set('default_ns1', $request->input('default_ns1'), 'general');
        SystemSetting::set('default_ns2', $request->input('default_ns2'), 'general');
        SystemSetting::set('default_ns3', $request->input('default_ns3', ''), 'general');
        SystemSetting::set('default_ns4', $request->input('default_ns4', ''), 'general');

        $this->activityLogger->log('nameservers_updated', 'Admin updated default brand nameservers');

        return back()->with('success', 'Default brand nameservers saved successfully.');
    }

}