<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\Villa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoomTest extends TestCase
{
    use RefreshDatabase;

    private function room(bool $active = true): Villa
    {
        return Villa::create(['name' => 'Sandy', 'slug' => 'sandy', 'category' => 'family', 'max_adults' => 2, 'max_children' => 2, 'max_guests' => 4, 'is_active' => $active, 'description' => 'A peaceful family bedroom.']);
    }

    public function test_only_active_rooms_are_public_and_admin_edits_are_reflected(): void
    {
        $room = $this->room();
        $this->get('/rooms')->assertOk()->assertSee('Sandy')->assertSee('/rooms/sandy');
        $this->get('/rooms/sandy')->assertOk()->assertSee('A peaceful family bedroom.');
        $room->update(['description' => 'Updated room description.']);
        $this->get('/rooms/sandy')->assertSee('Updated room description.')->assertDontSee('A peaceful family bedroom.');
        $room->update(['is_active' => false]);
        $this->get('/rooms')->assertOk()->assertDontSee('Explore Sandy');
        $this->get('/rooms/sandy')->assertNotFound();
        $this->get('/rooms/unknown')->assertNotFound();
    }

    public function test_room_media_and_active_amenities_are_loaded_without_other_room_images(): void
    {
        $room = $this->room();
        $room->media()->create(['disk' => 'public', 'path' => 'rooms/sandy.jpg', 'alt_text' => 'Sandy bedroom', 'sort_order' => 2, 'is_primary' => true]);
        $first = $room->media()->create(['disk' => 'public', 'path' => 'rooms/sandy-window.jpg', 'alt_text' => 'Sandy window', 'sort_order' => 1]);
        $amenity = Amenity::create(['name' => 'Private balcony', 'slug' => 'balcony', 'is_active' => true]);
        $inactive = Amenity::create(['name' => 'Unverified feature', 'slug' => 'unverified', 'is_active' => false]);
        $room->amenities()->sync([$amenity->id, $inactive->id]);
        $this->get('/rooms')->assertOk()->assertSee('rooms/sandy.jpg');
        $this->get('/rooms/sandy')->assertOk()->assertSee('Sandy bedroom')->assertSee('Private balcony')->assertDontSee('Unverified feature')->assertViewHas('galleryImages', fn ($images) => $images[0]['title'] === 'Sandy window');
    }

    public function test_empty_listing_and_legacy_url_are_safe(): void
    {
        $this->get('/rooms')->assertOk()->assertSee('Our rooms are being prepared');
        $this->get('/rooms/accommodation')->assertRedirect(route('rooms.index'));
    }
}
