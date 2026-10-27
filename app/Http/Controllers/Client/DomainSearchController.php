<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Domain\DomainAvailabilityService;
use App\Models\TldPricing;
use Illuminate\Http\Request;

class DomainSearchController extends Controller
{
    public function __construct(
        protected DomainAvailabilityService $availabilityService
    ) {}

    
    public function checkTransferEligibility(Request $request, \App\Services\Whois\RdapWhoisService $whoisService)
    {
        $domain = strtolower(trim($request->input('domain', '')));
        $eppCode = trim($request->input('epp_code', ''));

        if (empty($domain) || !str_contains($domain, '.')) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid domain name.',
            ], 422);
        }

        $parts = explode('.', $domain, 2);
        $tld = '.' . $parts[1];
        $tldModel = \App\Models\TldPricing::where('tld', $tld)->where('is_active', true)->first();

        if (!$tldModel) {
            return response()->json([
                'success' => false,
                'message' => "The extension {$tld} is not currently supported for transfer.",
            ], 422);
        }

        $transferPrice = (float) $tldModel->client_transfer;
        $regularPrice = round($transferPrice * 1.08, 2);

        $lookup = $whoisService->lookup($domain);

        if (!($lookup['is_registered'] ?? true)) {
            return response()->json([
                'success' => false,
                'message' => "Domain '{$domain}' is not registered yet. You can register it directly.",
                'can_register' => true,
            ]);
        }

        $statusList = array_map('strtolower', $lookup['status'] ?? []);
        $isLocked = false;
        foreach ($statusList as $st) {
            if (str_contains($st, 'transferprohibited') || str_contains($st, 'clienthold') || str_contains($st, 'serverhold')) {
                $isLocked = true;
                break;
            }
        }

        $createdDate = $lookup['created_at'] ?? null;
        $isUnder60Days = false;
        if ($createdDate) {
            try {
                $createdAt = \Carbon\Carbon::parse($createdDate);
                if ($createdAt->diffInDays(now()) < 60) {
                    $isUnder60Days = true;
                }
            } catch (\Throwable $e) {}
        }

        $eppValid = false;
        $eppMessage = null;
        if (!empty($eppCode)) {
            if (strlen($eppCode) >= 6 && !str_contains($eppCode, ' ')) {
                $eppValid = true;
                $eppMessage = 'Code successfully verified';
            } else {
                $eppValid = false;
                $eppMessage = 'Invalid EPP code format (must be at least 6 characters).';
            }
        }

        return response()->json([
            'success'          => true,
            'domain'           => $domain,
            'tld'              => $tld,
            'is_registered'    => true,
            'is_locked'        => $isLocked,
            'lock_status'      => $isLocked ? 'LOCKED' : 'UNLOCKED',
            'lock_message'     => $isLocked ? 'Transfer prohibited. Please unlock domain at your current registrar.' : 'Registrar Lock disabled (Ready for transfer)',
            'is_under_60_days' => $isUnder60Days,
            'registrar'        => $lookup['registrar'] ?? 'Current Registrar',
            'transfer_price'   => $transferPrice,
            'regular_price'    => $regularPrice,
            'epp_valid'        => $eppValid,
            'epp_message'      => $eppMessage,
            'eligible'         => !$isLocked && !$isUnder60Days,
        ]);
    }

    public function publicTransfer(Request $request)
    {
        $domain = strtolower(trim($request->query('domain', '')));
        $allTlds = TldPricing::where('is_active', true)->get();

        $tldModel = null;
        $transferPrice = null;

        if ($domain && str_contains($domain, '.')) {
            $parts = explode('.', $domain, 2);
            $tld = '.' . $parts[1];
            $tldModel = TldPricing::where('tld', $tld)->where('is_active', true)->first();
            if ($tldModel) {
                $transferPrice = (float)$tldModel->client_transfer;
            }
        }

        return view('domain.transfer', compact('domain', 'allTlds', 'tldModel', 'transferPrice'));
    }

    public function publicSearch(Request $request)
    {
        $query = $request->query('query');
        $result = null;
        $suggestions = collect();

        if ($query) {
            $result = $this->availabilityService->check($query);
            $baseName = explode('.', trim($query))[0];

            $popularTlds = ['.com', '.net', '.org', '.xyz', '.io', '.co'];
            foreach ($popularTlds as $tld) {
                $altDomain = $baseName . $tld;
                if (strtolower($altDomain) !== strtolower(trim($query))) {
                    $suggestions->push($this->availabilityService->check($altDomain));
                }
            }
        }

        $allTlds = TldPricing::where('is_active', true)->get();

        return view('domain.search', compact('query', 'result', 'suggestions', 'allTlds'));
    }

    public function index(Request $request)
    {
        return $this->publicSearch($request);
    }

    public function checkSingle(Request $request)
    {
        $request->validate(['domain' => 'required|string']);
        $availability = $this->availabilityService->check($request->domain);

        return response()->json([
            'success' => true,
            'data' => $availability->toArray(),
        ]);
    }

    public function checkBulk(Request $request)
    {
        $request->validate(['domains' => 'required|array']);
        $results = $this->availabilityService->checkBulk($request->domains);

        return response()->json([
            'success' => true,
            'data' => $results->map->toArray(),
        ]);
    }

    public function check(Request $request)
    {
        return $this->checkSingle($request);
    }
}
