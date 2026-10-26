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
}
