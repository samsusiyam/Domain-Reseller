<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Show form to open new ticket for client.
     */
    public function create()
    {
        $users = \App\Models\User::select("id", "name", "email")->orderBy("name")->get();
        return view("admin.tickets.create", compact("users"));
    }

    /**
     * Store new ticket created by admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "user_id"    => ["required", "exists:users,id"],
            "department" => ["required", "in:technical,billing,domains,general"],
            "priority"   => ["required", "in:low,medium,high,urgent"],
            "subject"    => ["required", "string", "max:200"],
            "message"    => ["required", "string"],
        ]);

        $ticket = SupportTicket::create([
            "uuid"          => (string) \Illuminate\Support\Str::uuid(),
            "ticket_number" => "TCK-" . strtoupper(\Illuminate\Support\Str::random(6)),
            "user_id"       => $validated["user_id"],
            "department"    => $validated["department"],
            "priority"      => $validated["priority"],
            "subject"       => $validated["subject"],
            "status"        => "answered",
            "last_reply_at" => now(),
        ]);

        SupportTicketReply::create([
            "ticket_id" => $ticket->id,
            "user_id"   => auth()->id(),
            "message"   => $validated["message"],
        ]);

        $this->activityLogger->log("ticket_created_by_admin", "Admin opened Ticket #" . $ticket->ticket_number . " for user #" . $ticket->user_id, $ticket);

        return redirect()->route("admin.tickets.show", $ticket->id)->with("success", "Ticket #" . $ticket->ticket_number . " opened successfully.");
    }

    /**
     * Lists all support tickets with filters.
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with('user', 'domain');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', $search)
                  ->orWhere('subject', 'like', $search)
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', $search)->orWhere('email', 'like', $search);
                  });
            });
        }

        $tickets = $query->latest('last_reply_at')->paginate(15)->withQueryString();

        return view('admin.tickets.index', compact('tickets'));
    }

    /**
     * Show ticket conversation thread.
     */
    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'domain', 'replies.user'])->findOrFail($id);
        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Post staff reply.
     */
    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $request->validate([
            'message'       => ['required', 'string'],
            'status'        => ['required', 'in:open,answered,in_progress,closed'],
            'attachments.*' => ['nullable', 'file', 'max:5120', 'mimes:jpeg,png,jpg,pdf,txt,zip'],
        ]);

        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments/' . $ticket->id, 'public');
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
        }

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id'           => $request->user()->id,
            'is_staff_reply'    => true,
            'message'           => $request->input('message'),
            'attachments'       => $attachmentPaths,
        ]);

        $ticket->update([
            'status'        => $request->input('status'),
            'last_reply_at' => now(),
        ]);

        $this->activityLogger->log('ticket_replied', "Staff replied to ticket #{$ticket->ticket_number}", $ticket);

        return back()->with('success', 'Reply posted successfully.');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $request->validate(['status' => 'required|in:open,answered,in_progress,closed']);

        $ticket->update(['status' => $request->input('status')]);

        return back()->with('success', "Ticket status updated to {$request->input('status')}.");
    }

    /**
     * Delete ticket.
     */
    public function destroy($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $this->activityLogger->log('ticket_deleted', "Admin deleted ticket #{$ticket->ticket_number}");
        $ticket->delete();

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket deleted.');
    }
}
