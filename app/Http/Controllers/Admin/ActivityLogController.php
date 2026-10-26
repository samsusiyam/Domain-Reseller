<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Searchable audit trail of user and admin actions.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                  ->orWhere('ip_address', 'like', $search)
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', $search)->orWhere('email', 'like', $search);
                  });
            });
        }

        $logs = $query->latest('created_at')->paginate(25)->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }

    /**
     * Show log details.
     */
    public function show($id)
    {
        $log = ActivityLog::with('user')->findOrFail($id);
        return view('admin.activity-logs.show', compact('log'));
    }

    /**
     * Prune logs older than 90 days.
     */
    public function clearOld()
    {
        $pruned = ActivityLog::where('created_at', '<', now()->subDays(90))->delete();
        return back()->with('success', "Pruned {$pruned} activity logs older than 90 days.");
    }
}
