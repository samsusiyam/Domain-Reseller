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
