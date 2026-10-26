<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientTicketController extends Controller
{
    /**
     * List all support tickets for authenticated client.
     */
    public function index(Request $request)
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->with('domain')
            ->latest('last_reply_at')
            ->paginate(15);

        return view('client.tickets.index', compact('tickets'));
    }

    /**
     * Show ticket creation form.
     */
    public function create(Request $request)
    {
        $domains = $request->user()->domains()->select('id', 'domain_name')->get();
        return view('client.tickets.create', compact('domains'));
    }

    /**
     * Store new support ticket.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject'     => ['required', 'string', 'max:255'],
            'department'  => ['required', 'string', 'in:billing,technical,domain,general'],
            'priority'    => ['required', 'string', 'in:low,medium,high,urgent'],
            'domain_id'   => ['nullable', 'exists:domains,id'],
            'message'     => ['required', 'string'],
            'attachments.*'=> ['nullable', 'file', 'max:5120', 'mimes:jpeg,png,jpg,pdf,txt,zip'],
        ]);

        $user = $request->user();

        $ticket = SupportTicket::create([
            'user_id'    => $user->id,
            'domain_id'  => $request->input('domain_id'),
            'department' => $request->input('department'),
            'priority'   => $request->input('priority'),
            'subject'    => $request->input('subject'),
            'status'     => 'open',
            'last_reply_at' => now(),
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
            'user_id'           => $user->id,
            'is_staff_reply'    => false,
            'message'           => $request->input('message'),
            'attachments'       => $attachmentPaths,
        ]);

        return redirect()->route('client.tickets.show', $ticket->id)->with('success', 'Support ticket opened successfully.');
    }

    /**
     * Show single ticket details and conversation thread.
     */
    public function show(Request $request, $id)
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('uuid', $id);
            })
            ->with(['replies.user', 'domain'])
            ->firstOrFail();

        return view('client.tickets.show', compact('ticket'));
    }

    /**
     * Post reply to existing ticket.
     */
    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('uuid', $id);
            })
            ->firstOrFail();

        $request->validate([
            'message'       => ['required', 'string'],
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
            'is_staff_reply'    => false,
            'message'           => $request->input('message'),
            'attachments'       => $attachmentPaths,
        ]);

        $ticket->update([
            'status'        => 'customer_reply',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Your reply has been posted.');
    }

    /**
     * Close a support ticket.
     */
    public function close(Request $request, $id)
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('uuid', $id);
            })
            ->firstOrFail();

        $ticket->update(['status' => 'closed']);

        return back()->with('success', 'Ticket marked as closed.');
    }
}
