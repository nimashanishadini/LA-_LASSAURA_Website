<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return ['name' => 'Guest Name', 'email' => 'private@example.com', 'rating' => 5, 'comment' => 'A lovely peaceful experience by the pool.'];
    }

    public function test_feedback_is_pending_until_moderated_and_email_is_private(): void
    {
        $this->post(route('reviews.store'), $this->payload() + ['status' => 'approved'])->assertRedirect()->assertSessionHas('review_status');
        $review = Review::firstOrFail();
        $this->assertSame('pending', $review->status);
        $this->get('/experiences')->assertOk()->assertDontSee($review->comment)->assertDontSee($review->email);
        $user = User::factory()->create();
        $user->role = 'owner';
        $user->save();
        $this->actingAs($user)->patch(route('admin.reviews.update', $review), ['status' => 'approved'])->assertSessionHasNoErrors();
        $this->assertSame($user->id, $review->fresh()->moderated_by);
        $this->get('/experiences')->assertOk()->assertSee($review->comment)->assertSee('Guest Name')->assertDontSee('private@example.com');
        $this->patch(route('admin.reviews.update', $review), ['status' => 'rejected'])->assertSessionHasNoErrors();
        $this->get('/experiences')->assertDontSee($review->comment);
    }

    public function test_validation_and_access_control(): void
    {
        $this->post(route('reviews.store'), ['rating' => 6, 'comment' => 'Hi'])->assertSessionHasErrors(['name', 'email', 'rating', 'comment']);
        $this->assertSame(0, Review::count());
        $this->get('/admin/reviews')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/reviews')->assertForbidden();
    }

    public function test_feedback_form_empty_state_and_admin_view_render(): void
    {
        $this->get('/experiences')->assertOk()->assertSee('Your story could be the first')->assertSee('Share feedback');
        $user = User::factory()->create();
        $user->role = 'editor';
        $user->save();
        $this->actingAs($user)->get('/admin/reviews')->assertOk();
        Review::create($this->payload());
        $this->get('/admin/reviews')->assertOk()->assertSee('private@example.com');
    }
}
