<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use Illuminate\Http\Request;

class ApiRequestLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ApiRequestLog::with(['user', 'apiKey'])->orderByDesc('id');

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('status')) {
            if ($request->status === 'success') {
                $query->whereBetween('status_code', [200, 299]);
            } elseif ($request->status === 'error') {
                $query->where('status_code', '>=', 400);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('path', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        $stats = [
            'total_today' => ApiRequestLog::whereDate('created_at', today())->count(),
            'errors_today' => ApiRequestLog::whereDate('created_at', today())->where('status_code', '>=', 400)->count(),
            'avg_latency_ms' => (int) round(ApiRequestLog::whereDate('created_at', today())->avg('duration_ms') ?? 0),
        ];

        return view('admin.api-logs.index', compact('logs', 'stats'));
    }

    public function show(int $id)
    {
        $log = ApiRequestLog::with(['user', 'apiKey'])->findOrFail($id);
        return response()->json($log);
    }

    public function clear()
    {
        ApiRequestLog::truncate();
        return back()->with('success', 'All API request logs have been cleared.');
    }
}
