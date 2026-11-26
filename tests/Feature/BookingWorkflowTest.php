<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingPolicy;
use App\Models\BookingQuote;
use App\Models\User;
use App\Models\Villa;
use App\Models\VillaNight;
use App\Models\VillaRate;
use App\Services\Availability;
use App\Services\BookingWorkflow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-26 04:00:00', 'UTC'));
    }

    private function room(): Villa
    {
        $room = Villa::create(['name' => 'Coral', 'slug' => 'coral', 'category' => 'double', 'is_active' => true, 'max_adults' => 2, 'max_children' => 2, 'max_guests' => 4]);
        $room->rates()->create(['name' => 'Base', 'starts_on' => '2026-11-26', 'ends_on' => '2027-12-31', 'days' => 'all', 'priority' => 0, 'nightly_lkr' => '15000.00', 'discount_percent' => '0.00', 'is_active' => true]);
        BookingPolicy::create(['key' => 'default', 'check_in_time' => '14:00', 'check_out_time' => '11:00', 'cancellation_policy' => 'Cancellation requests are reviewed by the property.']);

        return $room;
    }

    private function owner(): User
    {
        $user = User::factory()->create();
        $user->role = 'owner';
        $user->save();

        return $user;
    }

    private function quote(User $user, Villa $room): BookingQuote
    {
        return app(BookingWorkflow::class)->quote($user->id, $room, '2026-12-01', '2026-12-03', 2, 0);
    }

    private function guest(User $user): array
    {
        return ['name' => 'Guest Name', 'email' => $user->email, 'phone' => '+94771234567', 'special_requests' => null];
    }

    public function test_customer_request_approval_and_calendar_end_to_end(): void
    {
        $room = $this->room();
        $user = User::factory()->create();
        $this->actingAs($user)->get('/booking?room=coral')->assertRedirect(route('home', ['room' => 'coral', 'booking' => 1]));
        $this->post(route('bookings.quote'), ['room' => 'coral', 'check_in' => '2026-12-01', 'check_out' => '2026-12-03', 'adults' => 2, 'children' => 0])->assertRedirect();
        $quote = BookingQuote::firstOrFail();
        $this->getJson(route('bookings.quote.show', $quote))->assertOk()->assertJsonPath('data.snapshot.total_lkr', '30000.00');
        $payload = ['quote_id' => $quote->id, 'name' => 'Guest Name', 'phone' => '+94771234567', 'accepted_terms' => 1, 'total_lkr' => '1', 'status' => 'confirmed'];
        $this->post(route('bookings.store'), $payload)->assertRedirect();
        $booking = Booking::firstOrFail();
        $this->assertSame('pending', $booking->status);
        $this->assertSame('30000.00', $booking->quote_snapshot['total_lkr']);
        $this->assertSame(0, VillaNight::count());
        $this->getJson(route('bookings.index'))->assertOk()->assertSee($booking->reference);
        $this->getJson(route('bookings.show', $booking))->assertOk()->assertJsonPath('data.booking.status', 'pending');
        $this->post(route('bookings.store'), $payload)->assertRedirect();
        $this->assertSame(1, Booking::count());
        $this->actingAs($this->owner())->get(route('admin.bookings.index'))->assertOk()->assertSee('Guest Name');
        $this->get(route('admin.bookings.show', $booking))->assertOk();
        $this->post(route('admin.bookings.approve', $booking))->assertRedirect();
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame(2, VillaNight::count());
        $this->getJson('/admin/calendar/events?start=2026-12-01&end=2026-12-04')->assertOk()->assertJsonPath('0.extendedProps.kind', 'Confirmed stay');
        VillaRate::query()->update(['nightly_lkr' => '25000']);
        $this->post(route('admin.bookings.approve', $booking))->assertRedirect();
        $this->assertSame('30000.00', $booking->fresh()->quote_snapshot['total_lkr']);
        $this->assertSame(2, VillaNight::count());
    }

    public function test_changed_price_requires_customer_acceptance_then_admin_approval(): void
    {
        $room = $this->room();
        $user = User::factory()->create();
        $workflow = app(BookingWorkflow::class);
        $owner = $this->owner();
        $booking = $workflow->submit($user->id, $this->quote($user, $room)->id, $this->guest($user));
        VillaRate::query()->update(['nightly_lkr' => '20000']);
        $workflow->approve($booking, $owner->id);
        $booking->refresh();
        $this->assertSame('pending', $booking->status);
        $this->assertSame('40000.00', $booking->proposed_snapshot['total_lkr']);
        $this->assertSame(0, VillaNight::count());
        $this->actingAs($user)->getJson(route('bookings.show', $booking))->assertOk()->assertJsonPath('data.booking.proposed_snapshot.total_lkr', '40000.00');
        $this->post(route('bookings.accept', $booking), ['revision' => $workflow->revision($booking), 'accepted_terms' => 1])->assertRedirect();
        $this->assertNull($booking->fresh()->proposed_snapshot);
        $this->assertSame('pending', $booking->fresh()->status);
        $workflow->approve($booking, $owner->id);
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame('40000.00', $booking->fresh()->quote_snapshot['total_lkr']);
    }

    public function test_quote_expiry_ownership_and_guest_changes_are_protected(): void
    {
        $room = $this->room();
        $a = User::factory()->create();
        $b = User::factory()->create();
        $quote = $this->quote($a, $room);
        $this->actingAs($b)->get(route('bookings.quote.show', $quote))->assertNotFound();
        $this->postJson(route('bookings.store'), ['quote_id' => $quote->id, 'name' => 'Guest', 'phone' => '+94771234567', 'accepted_terms' => 1])->assertNotFound();
        $this->travel(16)->minutes();
        $this->actingAs($a)->postJson(route('bookings.store'), ['quote_id' => $quote->id, 'name' => 'Guest', 'phone' => '+94771234567', 'accepted_terms' => 1])->assertStatus(409);
        $this->assertSame(0, Booking::count());
    }

    public function test_customer_cannot_approve_or_access_someone_elses_booking(): void
    {
        $room = $this->room();
        $a = User::factory()->create();
        $b = User::factory()->create();
        $workflow = app(BookingWorkflow::class);
        $booking = $workflow->submit($a->id, $this->quote($a, $room)->id, $this->guest($a));
        $this->actingAs($b)->get(route('bookings.show', $booking))->assertNotFound();
        $this->post(route('bookings.cancel', $booking))->assertNotFound();
        $this->actingAs($a)->post(route('admin.bookings.approve', $booking))->assertForbidden();
        $this->post(route('bookings.cancel', $booking))->assertRedirect();
        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_approval_rechecks_inventory_and_rejection_does_not_allocate(): void
    {
        $room = $this->room();
        $a = User::factory()->create();
        $workflow = app(BookingWorkflow::class);
        $owner = $this->owner();
        $booking = $workflow->submit($a->id, $this->quote($a, $room)->id, $this->guest($a));
        app(Availability::class)->block($room, '2026-12-01', '2026-12-03', 'Maintenance');
        $this->actingAs($owner)->postJson(route('admin.bookings.approve', $booking))->assertStatus(409);
        $this->assertSame('pending', $booking->fresh()->status);
        $this->post(route('admin.bookings.reject', $booking), ['reason' => 'The room needs maintenance.'])->assertRedirect();
        $this->assertSame('rejected', $booking->fresh()->status);
        $this->assertSame(0, VillaNight::where('booking_id', $booking->id)->count());
    }

    public function test_session_api_protects_ownership_and_ignores_browser_prices(): void
    {
        $room = $this->room();
        $this->get('/api/v1/me/bookings')->assertUnauthorized();
        $user = User::factory()->create();
        $quote = $this->actingAs($user)->post('/api/v1/quotes', ['room' => 'coral', 'check_in' => '2026-12-01', 'check_out' => '2026-12-03', 'adults' => 2, 'children' => 0])->assertCreated()->json('data');
        $response = $this->post('/api/v1/bookings', ['quote_id' => $quote['id'], 'name' => 'Guest', 'phone' => '+94771234567', 'accepted_terms' => 1, 'total_lkr' => '0'])->assertCreated();
        $response->assertJsonPath('data.quote_snapshot.total_lkr', '30000.00');
        $reference = $response->json('data.reference');
        $this->post('/api/v1/admin/bookings/'.$reference.'/approve')->assertForbidden();
        $this->get('/api/v1/me/bookings')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_changed_terms_and_newer_revision_need_fresh_acceptance(): void
    {
        $room = $this->room();
        $user = User::factory()->create();
        $owner = $this->owner();
        $workflow = app(BookingWorkflow::class);
        $quote = $this->quote($user, $room);
        $booking = $workflow->submit($user->id, $quote->id, $this->guest($user));
        BookingPolicy::query()->update(['cancellation_policy' => 'Revised cancellation rules for future acceptance.']);
        $workflow->approve($booking, $owner->id);
        $booking->refresh();
        $revision = $workflow->revision($booking);
        VillaRate::query()->update(['nightly_lkr' => '22000']);
        $workflow->accept($booking, $user->id, $revision);
        $this->assertNotNull($booking->fresh()->proposed_snapshot);
        $this->assertSame('30000.00', $booking->fresh()->quote_snapshot['total_lkr']);
        $this->assertSame('44000.00', $booking->fresh()->proposed_snapshot['total_lkr']);
        $this->assertSame(0, VillaNight::count());
    }

    public function test_changed_guest_replay_and_unaccepted_current_price_are_rejected(): void
    {
        $room = $this->room();
        $user = User::factory()->create();
        $workflow = app(BookingWorkflow::class);
        $quote = $this->quote($user, $room);
        $workflow->submit($user->id, $quote->id, $this->guest($user));
        $this->actingAs($user)->postJson(route('bookings.store'), ['quote_id' => $quote->id, 'name' => 'Different guest', 'phone' => '+94771234567', 'accepted_terms' => 1])->assertStatus(409);
        $nextQuote = $this->quote($user, $room);
        VillaRate::query()->update(['nightly_lkr' => '19000']);
        $this->postJson(route('bookings.store'), ['quote_id' => $nextQuote->id, 'name' => 'Guest', 'phone' => '+94771234567', 'accepted_terms' => 1])->assertStatus(409);
        $this->assertSame(1, Booking::count());
    }

    public function test_registration_keeps_selected_stay_and_profile_deletion_is_clear(): void
    {
        $this->get('/booking?room=coral')->assertRedirect(route('home', ['room' => 'coral', 'booking' => 1]));
        $this->get('/login')->assertOk()->assertSee('Create an account');
        $this->postJson('/register', ['name' => 'New guest', 'email' => 'new@example.com', 'password' => 'password', 'password_confirmation' => 'password'])->assertCreated()->assertJsonStructure(['user' => ['name', 'email'], 'csrf_token']);
        $room = $this->room();
        $user = auth()->user();
        app(BookingWorkflow::class)->submit($user->id, $this->quote($user, $room)->id, $this->guest($user));
        $this->from('/profile')->delete('/profile', ['password' => 'password'])->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_property_settings_required_and_only_owner_can_edit_them(): void
    {
        $room = $this->room();
        BookingPolicy::query()->delete();
        $owner = $this->owner();
        $this->actingAs($owner)->post(route('bookings.quote'), ['room' => 'coral', 'check_in' => '2026-12-01', 'check_out' => '2026-12-03', 'adults' => 2, 'children' => 0])->assertSessionHasErrors('booking');
        $this->get(route('admin.booking-policies'))->assertOk();
        $this->put(route('admin.booking-policies.save'), ['check_in_time' => '14:00', 'check_out_time' => '11:00', 'cancellation_policy' => 'Property-approved test cancellation terms.'])->assertSessionHasNoErrors();
        $other = User::factory()->create();
        $other->role = 'reception';
        $other->save();
        $this->actingAs($other)->get(route('admin.booking-policies'))->assertForbidden();
    }
}
