<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;

class ContactInquiryController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request)
    {
        $query = ContactInquiry::query()->latest();

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('subject', 'like', $search)
                  ->orWhere('message', 'like', $search);
            });
        }

        $inquiries = $query->paginate(15)->withQueryString();
        $unreadCount = ContactInquiry::where('status', 'unread')->count();

        return view('admin.inquiries.index', compact('inquiries', 'unreadCount'));
    }

    public function updateStatus(Request $request, ContactInquiry $inquiry)
    {
        $request->validate([
            'status' => ['required', 'in:unread,replied,archived'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $inquiry->update([
            'status' => $request->input('status'),
            'admin_notes' => $request->input('admin_notes', $inquiry->admin_notes),
        ]);

        return back()->with('success', 'Inquiry status updated successfully.');
    }

    public function destroy(ContactInquiry $inquiry)
    {
        $inquiry->delete();
        $this->activityLogger->log('inquiry_deleted', "Admin deleted inquiry #{$inquiry->id}");

        return back()->with('success', 'Inquiry removed.');
    }
}
