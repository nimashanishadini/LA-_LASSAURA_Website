<?php

namespace Tests\Feature;

use App\Exceptions\InventoryConflict;
use App\Models\InventoryEvent;
use App\Models\MaintenanceBlock;
use App\Models\User;
use App\Models\Villa;
use App\Models\VillaNight;
use App\Services\Availability;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-26 03:00:00', 'UTC'));
    }

    private function room(string $slug = 'coral'): Villa
    {
        return Villa::create(['name' => ucfirst($slug), 'slug' => $slug, 'category' => 'double', 'is_active' => true, 'max_adults' => 2, 'max_children' => 2, 'max_guests' => 4]);
    }

    private function staff(string $role = 'owner'): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    public function test_pending_does_not_block_and_exactly_one_overlapping_confirmation_succeeds(): void
    {
        $room = $this->room();
        $service = app(Availability::class);
        $a = $service->request($room, '2026-11-27', '2026-11-29', 2, 0);
        $b = $service->request($room, '2026-11-27', '2026-11-29', 2, 0);
        $this->assertSame(0, VillaNight::count());
        $this->assertCount(1, $service->search('2026-11-27', '2026-11-29', 2));
        $service->confirm($a);
        $service->confirm($a);
        $this->assertSame(2, VillaNight::count());
        try {
            $service->confirm($b);
            $this->fail('Overlapping confirmation succeeded');
        } catch (InventoryConflict $error) {
            $this->assertSame(409, $error->getStatusCode());
        }
        $this->assertSame('pending', $b->fresh()->status);
        $this->assertCount(0, $service->search('2026-11-27', '2026-11-29', 2));
        $adjacent = $service->request($room, '2026-11-29', '2026-11-30', 2, 0);
        $service->confirm($adjacent);
        $this->assertSame(3, VillaNight::count());
    }

    public function test_maintenance_conflict_rolls_back_and_release_preserves_history(): void
    {
        $room = $this->room();
        $service = app(Availability::class);
        $block = $service->block($room, '2026-11-27', '2026-11-29', 'Repairs');
        try {
            $service->block($room, '2026-11-28', '2026-11-30', 'Conflict');
            $this->fail('Conflict accepted');
        } catch (InventoryConflict $error) {
            $this->assertSame(1, MaintenanceBlock::count());
        }
        $this->assertSame(2, VillaNight::count());
        $this->travelTo(Carbon::parse('2026-11-28 03:00:00', 'UTC'));
        $service->unblock($block);
        $service->unblock($block);
        $this->assertSame(1, VillaNight::count());
        $this->assertNotNull($block->fresh()->cancelled_at);
        $this->assertSame('2026-11-27', VillaNight::first()->stay_date->toDateString());
        $this->assertSame(2, InventoryEvent::count());
    }

    public function test_failed_extension_or_room_change_restores_original_allocations(): void
    {
        $a = $this->room();
        $b = $this->room('pearl');
        $service = app(Availability::class);
        $booking = $service->request($a, '2026-11-27', '2026-11-29', 2, 0);
        $service->confirm($booking);
        $service->block($a, '2026-11-29', '2026-11-30', 'Repairs');
        $service->block($b, '2026-11-27', '2026-11-29', 'Repairs');
        foreach ([[$a, '2026-11-30'], [$b, '2026-11-29']] as [$room,$end]) {
            try {
                $service->change($booking, $room, '2026-11-27', $end);
                $this->fail('Conflict accepted');
            } catch (InventoryConflict $error) {
                $this->assertSame($a->id, $booking->fresh()->villa_id);
                $this->assertSame(2, VillaNight::where('booking_id', $booking->id)->count());
            }
        }
        $service->change($booking, $a, '2026-11-26', '2026-11-29');
        $this->assertSame(3, VillaNight::where('booking_id', $booking->id)->count());
        $service->cancel($booking);
        $this->assertSame(0, VillaNight::where('booking_id', $booking->id)->count());
        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_invalid_dates_capacity_and_unpublished_rooms_are_rejected(): void
    {
        $room = $this->room();
        $service = app(Availability::class);
        foreach ([['2026-11-25', '2026-11-29', 2, 0], ['2026-11-29', '2026-11-29', 2, 0], ['2026-11-28', '2026-11-29', 3, 0], ['2026-11-28', '2026-11-29', 2, 3]] as [$start,$end,$adults,$children]) {
            try {
                $service->request($room, $start, $end, $adults, $children);
                $this->fail('Invalid request accepted');
            } catch (ValidationException $error) {
                $this->assertNotEmpty($error->errors());
            }
        }
        $room->update(['is_active' => false]);
        $this->assertCount(0, $service->search('2026-11-28', '2026-11-29', 1));
    }

    public function test_calendar_permissions_and_maintenance_forms(): void
    {
        $room = $this->room();
        $this->get('/admin/calendar')->assertRedirect('/login');
        $this->actingAs($this->staff('editor'))->get('/admin/calendar')->assertForbidden();
        $this->actingAs($this->staff('reception'))->get('/admin/calendar')->assertOk();
        $payload = ['villa_id' => $room->id, 'check_in' => '2026-11-27', 'check_out' => '2026-11-29', 'reason' => 'Painting'];
        $this->post(route('admin.maintenance.store'), $payload)->assertForbidden();
        $this->actingAs($this->staff())->from('/admin/calendar')->post(route('admin.maintenance.store'), $payload)->assertSessionHasNoErrors();
        $this->postJson(route('admin.maintenance.store'), $payload)->assertStatus(409);
        $this->get('/admin/calendar/events?start=2026-11-26&end=2026-12-01')->assertOk()->assertJsonCount(1)->assertJsonPath('0.end', '2026-11-29')->assertJsonPath('0.extendedProps.kind', 'Maintenance');
        $this->delete(route('admin.maintenance.destroy', MaintenanceBlock::first()))->assertSessionHasNoErrors();
        $this->assertSame(0, VillaNight::count());
    }

    public function test_availability_endpoint_reads_the_same_inventory_and_hides_private_details(): void
    {
        $room = $this->room();
        app(Availability::class)->block($room, '2026-11-27', '2026-11-29', 'Private internal reason');
        $this->getJson('/availability?check_in=2026-11-27&check_out=2026-11-29&adults=2&children=0')->assertOk()->assertJsonCount(0, 'data')->assertDontSee('Private internal reason');
        $this->getJson('/availability?check_in=2026-11-29&check_out=2026-11-30&adults=2&children=0')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/availability?check_in=bad&check_out=bad&adults=2&children=0')->assertUnprocessable();
    }

    public function test_database_enforces_unique_nights_and_exactly_one_owner(): void
    {
        $room = $this->room();
        app(Availability::class)->block($room, '2026-11-27', '2026-11-28', 'Repairs');
        try {
            DB::table('villa_nights')->insert(['villa_id' => $room->id, 'stay_date' => '2026-11-29']);
            $this->fail('Ownerless night accepted');
        } catch (QueryException $error) {
            $this->assertStringContainsString('Exactly one',$error->getMessage());
        }
        $night = VillaNight::first()->getAttributes();
        unset($night['id']);
        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('villa_nights')->insert($night);
    }
}
