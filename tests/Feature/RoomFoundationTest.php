<?php

namespace Tests\Feature;

use App\Models\BookingPolicy;
use App\Models\Villa;
use Database\Seeders\BookingPolicySeeder;
use Database\Seeders\VillaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_inventory_is_repeatable_and_preserves_unknown_rules_and_edits(): void
    {
        $this->seed([VillaSeeder::class, BookingPolicySeeder::class]);
        $this->assertSame(4, Villa::count());
        foreach (['sandy' => 'family', 'shelly' => 'family', 'coral' => 'double', 'pearl' => 'double'] as $slug => $category) {
            $room = Villa::where('slug', $slug)->firstOrFail();
            $this->assertSame($category, $room->category);
            $this->assertNull($room->max_adults);
            $this->assertNull($room->max_children);
            $this->assertNull($room->max_guests);
            $this->assertFalse($room->is_active);
        }
        $policy = BookingPolicy::where('key', 'default')->firstOrFail();
        $this->assertNull($policy->check_in_time);
        $this->assertNull($policy->check_out_time);
        $this->assertNull($policy->cancellation_policy);
        $this->assertNull($policy->allows_multiple_rooms);
        Villa::where('slug', 'sandy')->firstOrFail()->update(['max_adults' => 3, 'max_children' => 1, 'max_guests' => 4]);
        $policy->update(['allows_multiple_rooms' => false]);
        $this->seed([VillaSeeder::class, BookingPolicySeeder::class]);
        $this->assertSame(4, Villa::count());
        $this->assertSame(4, Villa::where('slug', 'sandy')->firstOrFail()->max_guests);
        $this->assertSame(1, BookingPolicy::count());
        $this->assertFalse($policy->fresh()->allows_multiple_rooms);
    }
}
