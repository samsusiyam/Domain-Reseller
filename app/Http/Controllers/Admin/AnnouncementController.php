<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Lists all system announcements.
     */
    public function index()
    {
        $announcements = Announcement::latest()->paginate(15);
        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Store new announcement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'content'         => ['required', 'string'],
            'target_audience' => ['required', 'in:all,clients,resellers'],
            'published_at'    => ['nullable', 'date'],
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['is_published'] = $request->boolean('is_published', true);
        $validated['published_at'] = $validated['published_at'] ?? now();

        $announcement = Announcement::create($validated);

        $this->activityLogger->log('announcement_created', "Admin created announcement '{$announcement->title}'", $announcement);

        return back()->with('success', 'Announcement published successfully.');
    }

    /**
     * Update existing announcement.
     */
    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'content'         => ['required', 'string'],
            'target_audience' => ['required', 'in:all,clients,resellers'],
            'published_at'    => ['nullable', 'date'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        $announcement->update($validated);

        $this->activityLogger->log('announcement_updated', "Admin updated announcement '{$announcement->title}'", $announcement);

        return back()->with('success', 'Announcement updated successfully.');
    }

    /**
     * Delete announcement.
     */
    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $title = $announcement->title;
        $announcement->delete();

        $this->activityLogger->log('announcement_deleted', "Admin deleted announcement '{$title}'");

        return back()->with('success', 'Announcement deleted.');
    }
}
