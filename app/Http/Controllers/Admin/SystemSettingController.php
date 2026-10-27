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
            'company_name'  => ['required', 'string', 'max:255'],
            'support_email' => ['required', 'email'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'currency_base' => ['required', 'string', 'max:3'],
            'tax_percentage'=> ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($request->only(['company_name', 'support_email', 'support_phone', 'currency_base', 'tax_percentage', 'portal_title', 'footer_text']) as $key => $value) {
            SystemSetting::set($key, $value, 'general');
        }

        $this->activityLogger->log('settings_updated', 'Admin updated general system settings');

        return back()->with('success', 'General settings saved.');
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
            'bkash_enabled'          => SystemSetting::get('bkash_enabled', env('BKASH_ENABLED', 'true')),
            'bkash_mode'             => SystemSetting::get('bkash_mode', env('BKASH_MODE', 'sandbox')),
            'bkash_app_key'          => SystemSetting::get('bkash_app_key', env('BKASH_APP_KEY', '')),
            'bkash_app_secret'       => SystemSetting::get('bkash_app_secret', env('BKASH_APP_SECRET', '')),
            'bkash_username'         => SystemSetting::get('bkash_username', env('BKASH_USERNAME', '')),
            'bkash_password'         => SystemSetting::get('bkash_password', env('BKASH_PASSWORD', '')),

            'stripe_enabled'         => SystemSetting::get('stripe_enabled', env('STRIPE_ENABLED', 'true')),
            'stripe_key'             => SystemSetting::get('stripe_key', env('STRIPE_KEY', '')),
            'stripe_secret'          => SystemSetting::get('stripe_secret', env('STRIPE_SECRET', '')),
            'stripe_webhook_secret'  => SystemSetting::get('stripe_webhook_secret', env('STRIPE_WEBHOOK_SECRET', '')),

            'binance_enabled'        => SystemSetting::get('binance_enabled', env('BINANCE_ENABLED', 'true')),
            'binance_merchant_id'    => SystemSetting::get('binance_merchant_id', env('BINANCE_MERCHANT_ID', '')),
            'binance_api_key'        => SystemSetting::get('binance_api_key', env('BINANCE_API_KEY', '')),
            'binance_secret_key'     => SystemSetting::get('binance_secret_key', env('BINANCE_SECRET_KEY', '')),

            'bank_transfer_enabled'  => SystemSetting::get('bank_transfer_enabled', 'true'),
            'bank_name'              => SystemSetting::get('bank_name', 'City Bank Ltd'),
            'bank_account_name'     => SystemSetting::get('bank_account_name', 'HostNibo IT'),
            'bank_account_number'   => SystemSetting::get('bank_account_number', '1100223344001'),
            'bank_routing_number'   => SystemSetting::get('bank_routing_number', '225272630'),
            'bank_instructions'     => SystemSetting::get('bank_instructions', 'Send payment with your Invoice ID as reference.'),
        ];

        return view('admin.settings.gateways', compact('settings'));
    }

    /**
     * Save payment gateway settings.
     */
    public function updateGateways(Request $request)
    {
        $keys = [
            'bkash_enabled', 'bkash_mode', 'bkash_app_key', 'bkash_app_secret', 'bkash_username', 'bkash_password',
            'stripe_enabled', 'stripe_key', 'stripe_secret', 'stripe_webhook_secret',
            'binance_enabled', 'binance_merchant_id', 'binance_api_key', 'binance_secret_key',
            'bank_transfer_enabled', 'bank_name', 'bank_account_name', 'bank_account_number', 'bank_routing_number', 'bank_instructions'
        ];

        foreach ($keys as $k) {
            if (str_ends_with($k, '_enabled')) {
                SystemSetting::set($k, $request->boolean($k) ? 'true' : 'false', 'gateway');
            } else {
                $val = $request->input($k);
                if ($val !== null) {
                    SystemSetting::set($k, $val, 'gateway');
                }
            }
        }

        $this->activityLogger->log('gateway_settings_updated', 'Admin updated payment gateway configuration');

        return back()->with('success', 'Payment gateway settings updated successfully.');
    }

    /**
     * Nameserver configuration page.
     */
    public function nameservers()
    {
        $settings = [
            'default_ns1' => SystemSetting::get('default_ns1', env('DEFAULT_NS1', 'ns1.hostnibo.com')),
            'default_ns2' => SystemSetting::get('default_ns2', env('DEFAULT_NS2', 'ns2.hostnibo.com')),
            'default_ns3' => SystemSetting::get('default_ns3', 'ns3.hostnibo.com'),
            'default_ns4' => SystemSetting::get('default_ns4', 'ns4.hostnibo.com'),
        ];

        return view('admin.settings.nameservers', compact('settings'));
    }

    /**
     * Save nameserver settings.
     */
    public function updateNameservers(Request $request)
    {
        $request->validate([
            'default_ns1' => ['required', 'string', 'max:255'],
            'default_ns2' => ['required', 'string', 'max:255'],
            'default_ns3' => ['nullable', 'string', 'max:255'],
            'default_ns4' => ['nullable', 'string', 'max:255'],
        ]);

        SystemSetting::set('default_ns1', $request->input('default_ns1'), 'domain');
        SystemSetting::set('default_ns2', $request->input('default_ns2'), 'domain');
        SystemSetting::set('default_ns3', $request->input('default_ns3'), 'domain');
        SystemSetting::set('default_ns4', $request->input('default_ns4'), 'domain');

        $this->activityLogger->log('nameserver_settings_updated', 'Admin updated default nameservers');

        return back()->with('success', 'Default nameservers updated successfully.');
    }

}