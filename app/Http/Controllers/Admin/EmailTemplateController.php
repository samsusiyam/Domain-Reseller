<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Jobs\SendEmailJob;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Lists all system email templates.
     */
    public function index()
    {
        $templates = EmailTemplate::orderBy('name')->get();
        return view('admin.email-templates.index', compact('templates'));
    }

    /**
     * Edit template form.
     */
    public function edit($id)
    {
        $template = EmailTemplate::findOrFail($id);
        return view('admin.email-templates.edit', compact('template'));
    }

    /**
     * Update email template subject and body.
     */
    public function update(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'subject'   => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $template->update($validated);

        $this->activityLogger->log('email_template_updated', "Admin updated email template {$template->name}", $template);

        return redirect()->route('admin.email-templates.index')->with('success', "Template '{$template->name}' updated.");
    }

    /**
     * Send test email using this template to current admin.
     */
    public function sendTest($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $user = auth()->user();

        // Sample placeholder data for testing
        $mockData = [
            'client_name'    => $user->name,
            'client_email'   => $user->email,
            'company_name'   => config('app.name', 'DomainReseller'),
            'domain_name'    => 'example.com',
            'expiry_date'    => now()->addYear()->format('Y-m-d'),
            'invoice_number' => 'INV-TEST-001',
            'amount'         => '$12.99',
            'login_url'      => route('login'),
        ];

        SendEmailJob::dispatch($user->email, $template->template_key, $mockData, $user->id);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Test email dispatched to {$user->email}"]);
        }

        return back()->with('success', "Test email dispatched to {$user->email}.");
    }
}
