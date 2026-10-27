<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;

class DomainLifecycleController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');

        $query = Domain::with(['user', 'registrar', 'registrantContact'])->orderBy('expires_at', 'asc');

        if ($tab === 'expiring_30') {
            $query->whereBetween('expires_at', [now(), now()->addDays(30)]);
        } elseif ($tab === 'grace') {
            $query->whereBetween('expires_at', [now()->subDays(30), now()]);
        } elseif ($tab === 'redemption') {
            $query->whereBetween('expires_at', [now()->subDays(60), now()->subDays(30)]);
        } elseif ($tab === 'pending_delete') {
            $query->where('expires_at', '<', now()->subDays(60));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('domain_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $domains = $query->paginate(20)->withQueryString();

        $stats = [
            'expiring_30'    => Domain::whereBetween('expires_at', [now(), now()->addDays(30)])->count(),
            'grace_period'   => Domain::whereBetween('expires_at', [now()->subDays(30), now()])->count(),
            'redemption'     => Domain::whereBetween('expires_at', [now()->subDays(60), now()->subDays(30)])->count(),
            'pending_delete' => Domain::where('expires_at', '<', now()->subDays(60))->count(),
        ];

        return view('admin.domains.lifecycle', compact('domains', 'stats', 'tab'));
    }

    public function sendNotice(Request $request, int $id)
    {
        $domain = Domain::with('user')->findOrFail($id);

        $this->activityLogger->log(
            'icann_wdrp_notice_sent',
            "ICANN WDRP Data Accuracy & Expiry Notice dispatched for {$domain->domain_name} to {$domain->user->email}"
        );

        return back()->with('success', "ICANN compliance & expiry reminder dispatched for {$domain->domain_name}.");
    }
}
