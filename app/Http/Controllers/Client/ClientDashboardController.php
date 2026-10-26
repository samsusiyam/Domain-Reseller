<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\SupportTicket;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $activeDomainsCount = $user->domains()->where('status', 'active')->count();
        $expiringSoonCount = $user->domains()->where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays(30)])
            ->count();
        $unpaidInvoicesCount = $user->invoices()->where('status', 'unpaid')->count();
        $openTicketsCount = $user->supportTickets()->whereIn('status', ['open', 'answered', 'customer_reply'])->count();

        $recentDomains = $user->domains()->latest()->take(5)->get();
        $recentInvoices = $user->invoices()->latest()->take(5)->get();
        $announcements = Announcement::published()->forAudience('client')->latest('published_at')->take(3)->get();

        return view('client.dashboard', compact(
            'user',
            'activeDomainsCount',
            'expiringSoonCount',
            'unpaidInvoicesCount',
            'openTicketsCount',
            'recentDomains',
            'recentInvoices',
            'announcements'
        ));
    }
}
