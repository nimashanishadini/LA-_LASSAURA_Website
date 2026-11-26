<?php

namespace Tests\Feature;

use App\Jobs\BookingNotice;
use App\Models\BookingPolicy;
use App\Models\User;
use App\Models\Villa;
use App\Services\BookingWorkflow;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingNotificationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_notifications_queue_after_commit_without_affecting_inventory(): void
    {
        Queue::fake();
        $room = Villa::create(['name' => 'Coral', 'slug' => 'coral', 'category' => 'double', 'is_active' => true, 'max_adults' => 2, 'max_children' => 0, 'max_guests' => 2]);
        $start = now('Asia/Colombo')->addDays(2)->toDateString();
        $end = now('Asia/Colombo')->addDays(4)->toDateString();
        $room->rates()->create(['name' => 'Base', 'starts_on' => $start, 'ends_on' => $end, 'days' => 'all', 'priority' => 0, 'nightly_lkr' => '10000', 'discount_percent' => '0', 'is_active' => true]);
        BookingPolicy::create(['key' => 'default', 'check_in_time' => '14:00', 'check_out_time' => '11:00', 'cancellation_policy' => 'Test-only property cancellation policy.']);
        $user = User::factory()->create();
        $workflow = app(BookingWorkflow::class);
        $quote = $workflow->quote($user->id, $room, $start, $end, 2, 0);
        $booking = $workflow->submit($user->id, $quote->id, ['name' => 'Guest', 'email' => $user->email, 'phone' => '+94770000000', 'special_requests' => null]);
        Queue::assertPushed(BookingNotice::class, fn ($job) => $job->bookingId === $booking->id && $job->kind === 'requested');
        $workflow->approve($booking, $user->id);
        Queue::assertPushed(BookingNotice::class, fn ($job) => $job->bookingId === $booking->id && $job->kind === 'confirmed');
        $this->assertSame('confirmed', $booking->fresh()->status);
    }
}
