<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // If email notifications or admin alerts are enabled:
        // Mail::to(config('mail.from.address'))->queue(...);

        return back()->with('success', 'Thank you for reaching out! Your message has been received.');
    }
}
