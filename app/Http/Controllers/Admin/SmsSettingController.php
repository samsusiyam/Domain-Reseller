<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Notification\SmsService;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;

class SmsSettingController extends Controller
{
    public function __construct(
        protected SmsService $smsService,
        protected ActivityLogger $activityLogger
    ) {}

    public function index()
    {
        $settings = [
            'sms_enabled'     => SystemSetting::get('sms_enabled', 'false') === 'true',
            'sms_provider'    => SystemSetting::get('sms_provider', 'greenweb'),
            'sms_api_key'     => SystemSetting::get('sms_api_key', ''),
            'sms_sender_id'   => SystemSetting::get('sms_sender_id', ''),
            'notify_invoice'  => SystemSetting::get('sms_notify_invoice', 'true') === 'true',
            'notify_domain'   => SystemSetting::get('sms_notify_domain', 'true') === 'true',
            'notify_expiry'   => SystemSetting::get('sms_notify_expiry', 'true') === 'true',
        ];

        $balance = $settings['sms_enabled'] && !empty($settings['sms_api_key'])
            ? $this->smsService->getBalance()
            : 'Gateway disabled / unconfigured';

        return view('admin.settings.sms', compact('settings', 'balance'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'sms_enabled'   => ['nullable', 'string'],
            'sms_provider'  => ['required', 'string', 'in:greenweb,bulksmsbd,alphasms'],
            'sms_api_key'   => ['nullable', 'string'],
            'sms_sender_id' => ['nullable', 'string'],
            'notify_invoice'=> ['nullable', 'string'],
            'notify_domain' => ['nullable', 'string'],
            'notify_expiry' => ['nullable', 'string'],
        ]);

        SystemSetting::set('sms_enabled', $request->has('sms_enabled') ? 'true' : 'false', 'sms');
        SystemSetting::set('sms_provider', $validated['sms_provider'], 'sms');
        SystemSetting::set('sms_api_key', $validated['sms_api_key'] ?? '', 'sms');
        SystemSetting::set('sms_sender_id', $validated['sms_sender_id'] ?? '', 'sms');
        SystemSetting::set('sms_notify_invoice', $request->has('notify_invoice') ? 'true' : 'false', 'sms');
        SystemSetting::set('sms_notify_domain', $request->has('notify_domain') ? 'true' : 'false', 'sms');
        SystemSetting::set('sms_notify_expiry', $request->has('notify_expiry') ? 'true' : 'false', 'sms');

        $this->activityLogger->log('sms_settings_updated', 'Updated Bangladeshi SMS gateway credentials and notification triggers');

        return back()->with('success', 'SMS gateway settings updated successfully.');
    }

    public function sendTest(Request $request)
    {
        $validated = $request->validate([
            'phone'   => ['required', 'string'],
            'message' => ['required', 'string', 'max:160'],
        ]);

        $result = $this->smsService->send($validated['phone'], $validated['message']);

        if ($result['success']) {
            return back()->with('success', "Test SMS successfully delivered to {$validated['phone']}!");
        }

        return back()->with('error', "SMS dispatch failed: " . ($result['message'] ?? $result['response'] ?? 'Unknown error'));
    }
}
