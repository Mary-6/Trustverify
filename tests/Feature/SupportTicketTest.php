<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_and_admin_can_converse_on_ticket(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => 'active']);

        $this->actingAs($customer)->post(route('dashboard.tickets.store'), [
            'subject' => 'Order not received',
            'body' => 'I need help with my order.',
            'priority' => 'high',
        ])->assertRedirect();

        $ticket = SupportTicket::firstOrFail();
        $this->assertSame('open', $ticket->status);
        $this->assertSame(1, $ticket->messages()->count());

        $this->actingAs($admin)->patch(route('admin.tickets.update', $ticket), [
            'status' => 'pending',
            'priority' => 'urgent',
            'assigned_to' => $admin->id,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.tickets.messages.store', $ticket), [
            'body' => 'We are checking this now.',
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertSame('pending', $ticket->status);
        $this->assertSame($admin->id, $ticket->assigned_to);
        $this->assertSame(2, $ticket->messages()->count());
        $this->assertTrue($ticket->messages()->latest('id')->first()->is_admin);

        $this->actingAs($customer)->get(route('dashboard.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('We are checking this now.');

        $this->actingAs($customer)->post(route('dashboard.tickets.messages.store', $ticket), [
            'body' => 'Thanks, I will wait.',
        ])->assertRedirect();

        $this->assertSame(3, $ticket->messages()->count());
    }
}
