<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return ['name' => 'Guest', 'email' => 'guest@example.com', 'subject' => 'Coral room inquiry', 'message' => 'Please let me know more about this room.'];
    }

    public function test_contact_form_saves_inquiry_and_ignores_submitted_status(): void
    {
        $this->get('/contact')->assertOk()->assertSee('Send an inquiry');
        $this->post('/contact', $this->payload() + ['status' => 'closed'])->assertRedirect(route('contact.index', ['#inquiry-form']))->assertSessionHas('inquiry_status');
        $this->assertDatabaseHas('inquiries', ['email' => 'guest@example.com', 'status' => 'new']);
    }

    public function test_validation_does_not_persist_invalid_messages(): void
    {
        $this->from('/contact')->post('/contact', ['name' => 'Test', 'email' => 'bad', 'subject' => 'Test', 'message' => 'Hi'])->assertSessionHasErrors(['email', 'message']);
        $this->assertSame(0, Inquiry::count());
    }

    public function test_inquiries_are_private_and_reception_can_manage_status(): void
    {
        $inquiry = Inquiry::create($this->payload());
        $this->get('/admin/inquiries')->assertRedirect('/login');
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/inquiries')->assertForbidden();
        $user->role = 'reception';
        $user->save();
        $this->get('/admin/inquiries')->assertOk()->assertSee('Coral room inquiry');
        $this->patch(route('admin.inquiries.update', $inquiry), ['status' => 'read'])->assertSessionHasNoErrors();
        $this->assertSame('read', $inquiry->fresh()->status);
        $user->role = 'editor';
        $user->save();
        $this->patch(route('admin.inquiries.update', $inquiry), ['status' => 'closed'])->assertForbidden();
    }

    public function test_contact_submissions_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $this->payload())->assertRedirect();
        }
        $this->post('/contact', $this->payload())->assertStatus(429);
    }
}
