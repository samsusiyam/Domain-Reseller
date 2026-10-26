<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    /**
     * Display active user sessions.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                return (object)[
                    'id'               => $session->id,
                    'ip_address'       => $session->ip_address,
                    'user_agent'       => $session->user_agent,
                    'last_activity'    => \Carbon\Carbon::createFromTimestamp($session->last_activity),
                    'is_current_device'=> $session->id === $currentSessionId,
                ];
            });

        return view('client.sessions.index', compact('sessions'));
    }

    /**
     * Terminate a specific session.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        if ($id === $currentSessionId) {
            return back()->with('error', 'Cannot terminate current active session from here.');
        }

        DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Session terminated successfully.');
    }
}
