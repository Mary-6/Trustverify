<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::with(['user', 'assignee'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($inner) use ($request) {
                $inner->where('subject', 'like', '%'.$request->string('q').'%')
                    ->orWhereHas('user', fn ($user) => $user->where('email', 'like', '%'.$request->string('q').'%')->orWhere('name', 'like', '%'.$request->string('q').'%'));
            }))
            ->latest('last_reply_at')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket)
    {
        return view('admin.tickets.show', [
            'ticket' => $ticket->load(['user', 'assignee', 'messages.user']),
            'admins' => User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,pending,closed'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $ticket->update([
            'status' => $data['status'],
            'priority' => $data['priority'],
            'assigned_to' => $data['assigned_to'] ?? null,
        ]);

        return back()->with('status', 'Ticket updated.');
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'is_admin' => true,
        ]);

        $ticket->forceFill([
            'last_reply_at' => now(),
            'status' => 'pending',
        ])->save();

        return back()->with('status', 'Reply sent to customer.');
    }
}
