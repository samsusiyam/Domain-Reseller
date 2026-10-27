<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Show contact us form.
     */
    public function index()
    {
        return view('contact');
    }

    /**
     * Handle contact inquiry submission.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            ContactInquiry::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $request->input('phone'),
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'status' => 'unread',
                'ip_address' => $request->ip(),
            ]);

            // Attempt email notification if SMTP is configured
            $adminEmail = config('app.support_email', config('mail.from.address'));
            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::raw(
                        "New contact inquiry from {$validated['name']} ({$validated['email']}):

" .
                        "Subject: {$validated['subject']}
" .
                        "Phone: " . ($request->input('phone') ?: 'Not provided') . "

" .
                        "Message:
{$validated['message']}",
                        function ($m) use ($adminEmail, $validated) {
                            $m->to($adminEmail)
                              ->subject("[HostNibo Inquiry] " . $validated['subject'])
                              ->replyTo($validated['email'], $validated['name']);
                        }
                    );
                } catch (\Exception $e) {
                    Log::warning('Contact mail sending skipped/failed: ' . $e->getMessage());
                }
            }

            return back()->with('success', 'Thank you for reaching out! Your inquiry has been received by our engineering team.');
        } catch (\Exception $e) {
            Log::error('Contact inquiry creation error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Unable to send inquiry at this moment. Please email directly to ' . config('app.support_email', 'support@hostnibo.com'));
        }
    }
}
