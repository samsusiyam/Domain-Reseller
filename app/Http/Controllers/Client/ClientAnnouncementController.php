<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class ClientAnnouncementController extends Controller
{
    /**
     * List announcements for authenticated client.
     */
    public function index(Request $request)
    {
        $role = $request->user()?->role ?? 'client';
        $announcements = Announcement::published()
            ->forAudience($role)
            ->latest('published_at')
            ->paginate(12);

        return view('client.announcements.index', compact('announcements'));
    }

    /**
     * Display a single announcement.
     */
    public function show(Request $request, $id)
    {
        $role = $request->user()?->role ?? 'client';
        $announcement = Announcement::published()
            ->forAudience($role)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('slug', $id);
            })
            ->firstOrFail();

        return view('client.announcements.show', compact('announcement'));
    }

    /**
     * Public announcement archive.
     */
    public function publicIndex()
    {
        $announcements = Announcement::published()
            ->whereIn('target_audience', ['all', 'clients'])
            ->latest('published_at')
            ->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    /**
     * Public single announcement page.
     */
    public function publicShow(string $slug)
    {
        $announcement = Announcement::published()
            ->whereIn('target_audience', ['all', 'clients'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('announcements.show', compact('announcement'));
    }
}
