<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use Illuminate\Http\Request;

class WhoisLookupController extends Controller
{
    public function index()
    {
        return view('whois.index');
    }

    public function query(Request $request)
    {
        $request->validate(['domain' => 'required|string']);
        $domainName = strtolower(trim($request->domain));

        $localDomain = Domain::where('domain_name', $domainName)->with(['registrantContact'])->first();

        $output = "Domain Name: {$domainName}\n";

        if ($localDomain) {
            $output .= "Registry Domain ID: " . ($localDomain->registrar_domain_id ?? 'N/A') . "\n";
            $output .= "Registrar: " . ($localDomain->registrar?->name ?? 'DomainReseller') . "\n";
            $output .= "Creation Date: " . ($localDomain->registered_at?->toIso8601String() ?? 'N/A') . "\n";
            $output .= "Registry Expiry Date: " . ($localDomain->expires_at?->toIso8601String() ?? 'N/A') . "\n";
            $output .= "Domain Status: " . $localDomain->status . "\n";

            if ($localDomain->is_private) {
                $output .= "Registrant: WHOIS Privacy Protection Service\n";
            } elseif ($localDomain->registrantContact) {
                $c = $localDomain->registrantContact;
                $output .= "Registrant Name: {$c->first_name} {$c->last_name}\n";
                $output .= "Registrant Organization: " . ($c->company_name ?? 'N/A') . "\n";
                $output .= "Registrant Country: {$c->country}\n";
            }

            if (is_array($localDomain->nameservers)) {
                foreach ($localDomain->nameservers as $ns) {
                    $output .= "Name Server: {$ns}\n";
                }
            }
        } else {
            $output .= "Domain status: Not registered on this platform or query live WHOIS servers.\n";
            $output .= "Nameservers: ns1.domainreseller.local\n";
        }

        return view('whois.index', ['domain' => $domainName, 'whoisResult' => $output]);
    }
}
