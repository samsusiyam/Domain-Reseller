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
