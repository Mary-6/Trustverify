<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        return view('customer.tickets.index', [
            'tickets' => $request->user()->supportTickets()->latest()->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
        ]);

        $ticket = $request->user()->supportTickets()->create([
            'subject' => $data['subject'],
            'status' => 'open',
            'priority' => $data['priority'] ?? 'normal',
            'last_reply_at' => now(),
        ]);

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'is_admin' => false,
        ]);

        return redirect()->route('dashboard.tickets.show', $ticket)->with('status', 'Support ticket created.');
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);

        return view('customer.tickets.show', [
            'ticket' => $ticket->load(['messages.user']),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'is_admin' => false,
        ]);

        $ticket->forceFill([
            'last_reply_at' => now(),
            'status' => $ticket->status === 'closed' ? 'open' : $ticket->status,
        ])->save();

        return back()->with('status', 'Reply sent.');
    }
}
