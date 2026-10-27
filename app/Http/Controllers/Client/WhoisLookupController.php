<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Whois\RdapWhoisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class WhoisLookupController extends Controller
{
    public function __construct(
        protected RdapWhoisService $whoisService
    ) {}

    public function index(Request $request)
    {
        $domain = $request->query('domain');
        $result = null;

        if (!empty($domain)) {
            $result = $this->whoisService->lookup($domain);
        }

        return view('whois.index', [
            'domain' => $domain,
            'result' => $result,
        ]);
    }

    public function query(Request $request)
    {
        $request->validate([
            'domain' => ['required', 'string', 'max:255'],
        ]);

        $ip = $request->ip() ?? '127.0.0.1';
        $limiterKey = 'whois:' . $ip;

        // Rate limit: 20 queries per minute per IP
        if (RateLimiter::tooManyAttempts($limiterKey, 20)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            return back()->with('error', "Too many WHOIS requests. Please wait {$seconds} seconds before trying again.");
        }

        RateLimiter::hit($limiterKey, 60);

        $domainInput = $request->input('domain');
        $result = $this->whoisService->lookup($domainInput);

        return view('whois.index', [
            'domain' => $domainInput,
            'result' => $result,
        ]);
    }
}
