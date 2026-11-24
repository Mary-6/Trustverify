<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_stores_message_and_admin_can_view_it(): void
    {
        $this->get('/contact')->assertOk()->assertSee('Send Message');

        $this->post('/contact', [
            'name' => 'Ada Customer',
            'email' => 'ada@example.com',
            'phone' => '+237600000000',
            'subject' => 'Order help',
            'message' => 'Need help with an order that has not started yet.',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Ada Customer',
            'email' => 'ada@example.com',
            'subject' => 'Order help',
            'status' => 'new',
        ]);

        $message = ContactMessage::firstOrFail();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => 'active']);

        $this->actingAs($admin)->get(route('admin.contact-messages.index'))->assertOk()->assertSee('Order help');
        $this->actingAs($admin)->get(route('admin.contact-messages.show', $message))->assertOk()->assertSee('Need help with an order');
        $this->assertSame('read', $message->fresh()->status);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->post('/contact', [])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
        $this->assertDatabaseCount('contact_messages', 0);
    }
}
