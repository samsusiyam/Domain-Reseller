<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class WhitelabelController extends Controller
{
    /**
     * Show white-label branding settings.
     */
    public function index(Request $request)
    {
        $reseller = $request->user();
        $prefix = "reseller_{$reseller->id}_";

        $settings = [
            'ns1'           => SystemSetting::get($prefix . 'ns1', 'ns1.' . ($reseller->company_name ? strtolower(str_replace(' ', '', $reseller->company_name)) . '.com' : 'domainreseller.com')),
            'ns2'           => SystemSetting::get($prefix . 'ns2', 'ns2.' . ($reseller->company_name ? strtolower(str_replace(' ', '', $reseller->company_name)) . '.com' : 'domainreseller.com')),
            'brand_name'    => SystemSetting::get($prefix . 'brand_name', $reseller->company_name ?? config('app.name')),
            'support_email' => SystemSetting::get($prefix . 'support_email', $reseller->email),
        ];

        return view('reseller.whitelabel.index', compact('reseller', 'settings'));
    }

    /**
     * Update white-label settings.
     */
    public function update(Request $request)
    {
        $reseller = $request->user();

        $request->validate([
            'ns1'           => ['required', 'string', 'max:255'],
            'ns2'           => ['required', 'string', 'max:255'],
            'brand_name'    => ['required', 'string', 'max:255'],
            'support_email' => ['required', 'email', 'max:255'],
        ]);

        $prefix = "reseller_{$reseller->id}_";

        SystemSetting::set($prefix . 'ns1', $request->input('ns1'), 'whitelabel');
        SystemSetting::set($prefix . 'ns2', $request->input('ns2'), 'whitelabel');
        SystemSetting::set($prefix . 'brand_name', $request->input('brand_name'), 'whitelabel');
        SystemSetting::set($prefix . 'support_email', $request->input('support_email'), 'whitelabel');

        return back()->with('success', 'White-label settings updated successfully.');
    }
}
