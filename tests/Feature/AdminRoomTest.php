<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Villa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_login_redirects_to_admin_and_console_grant_validates_roles(): void
    {
        $user = User::factory()->create();
        $this->artisan('admin:grant', ['email' => $user->email, '--role' => 'owner'])->assertExitCode(0);
        $this->assertSame('owner', $user->fresh()->role);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('admin.dashboard', absolute: false));
        $this->artisan('admin:grant', ['email' => $user->email, '--role' => 'invalid'])->assertExitCode(1);
        $this->assertSame('owner', $user->fresh()->role);
    }

    public function test_oversized_photograph_returns_a_readable_validation_error(): void
    {
        $room = $this->room();
        $this->actingAs($this->staff())->from(route('admin.rooms.edit', $room))
            ->post(route('admin.rooms.images.store', $room), ['images' => [UploadedFile::fake()->create('large.jpg', 32000, 'image/jpeg')]])
            ->assertRedirect(route('admin.rooms.edit', $room))
            ->assertSessionHasErrors(['images.0' => 'Each room photograph must be 30 MB or smaller.']);
        $this->assertSame(0, $room->media()->count());
    }

    public function test_request_exceeding_php_limit_redirects_instead_of_rendering_debug_page(): void
    {
        $room = $this->room();
        $this->actingAs($this->staff())->call('POST', route('admin.rooms.images.store', $room), [], [], [], ['CONTENT_LENGTH' => 100 * 1024 * 1024])
            ->assertRedirect(route('admin.rooms.index'))->assertSessionHasErrors('images');
    }

    public function test_original_large_property_photograph_can_be_uploaded(): void
    {
        Storage::fake('public');
        $room = $this->room();
        $file = new UploadedFile(public_path('images/home/interior.jpg'), 'original.jpg', 'image/jpeg', null, true);
        $this->assertGreaterThan(5 * 1024 * 1024, $file->getSize());
        $this->actingAs($this->staff())->post(route('admin.rooms.images.store', $room), ['images' => [$file]], ['Accept' => 'application/json'])->assertCreated();
        $media = $room->media()->firstOrFail();
        $this->assertTrue($media->is_primary);
        Storage::disk('public')->assertExists($media->path);
        $this->assertSame(filesize(public_path('images/home/interior.jpg')), Storage::disk('public')->size($media->path));
    }

    private function staff(string $role = 'owner'): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function room(string $slug = 'sandy'): Villa
    {
        return Villa::create(['name' => ucfirst($slug), 'slug' => $slug, 'category' => 'family', 'max_adults' => null, 'max_children' => null, 'max_guests' => null, 'is_active' => false]);
    }

    private function payload(): array
    {
        return ['name' => 'Sandy', 'slug' => 'sandy', 'category' => 'family', 'is_active' => 0, 'max_adults' => null, 'max_children' => null, 'max_guests' => null];
    }

    public function test_guests_and_customers_cannot_access_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->post(route('admin.rooms.store'), $this->payload())->assertForbidden();
    }

    public function test_reception_has_dashboard_access_but_cannot_manage_rooms(): void
    {
        $this->actingAs($this->staff('reception'))->get('/admin')->assertOk()->assertDontSee('Manage rooms');
        $this->get(route('admin.rooms.index'))->assertForbidden();
        $this->post(route('admin.rooms.store'), $this->payload())->assertForbidden();
    }

    public function test_public_registration_cannot_assign_a_staff_role(): void
    {
        $this->post('/register', ['name' => 'Guest', 'email' => 'guest@example.com', 'password' => 'password', 'password_confirmation' => 'password', 'role' => 'owner']);
        $this->assertDatabaseHas('users', ['email' => 'guest@example.com', 'role' => 'customer']);
    }

    public function test_owner_can_create_edit_and_render_rooms(): void
    {
        $this->actingAs($this->staff())->get(route('admin.rooms.create'))->assertOk();
        $this->post(route('admin.rooms.store'), $this->payload())->assertRedirect();
        $room = Villa::firstOrFail();
        $this->get(route('admin.rooms.edit', $room))->assertOk()->assertSee('Room photographs');
        $this->get(route('admin.rooms.index'))->assertOk()->assertSee('Sandy');
        $this->put(route('admin.rooms.update', $room), array_merge($this->payload(), ['max_adults' => 2, 'max_children' => 2, 'max_guests' => 4, 'is_active' => 1]))->assertSessionHasNoErrors();
        $this->assertTrue($room->fresh()->is_active);
        $this->assertSame(4, $room->fresh()->max_guests);
    }

    public function test_unconfigured_or_invalid_capacity_cannot_be_published(): void
    {
        $this->actingAs($this->staff());
        $this->post(route('admin.rooms.store'), array_merge($this->payload(), ['is_active' => 1]))->assertSessionHasErrors('is_active');
        $this->post(route('admin.rooms.store'), array_merge($this->payload(), ['max_adults' => 4, 'max_guests' => 2]))->assertSessionHasErrors('max_guests');
        $this->assertSame(0, Villa::count());
    }

    public function test_photos_can_be_uploaded_ordered_set_as_cover_and_removed(): void
    {
        Storage::fake('public');
        $room = $this->room();
        $this->actingAs($this->staff())->post(route('admin.rooms.images.store', $room), ['images' => [UploadedFile::fake()->image('sandy.jpg'), UploadedFile::fake()->image('bed.jpg')]])->assertSessionHasNoErrors();
        $images = $room->media()->get();
        $this->assertCount(2, $images);
        Storage::disk('public')->assertExists($images[0]->path);
        $this->patch(route('admin.rooms.images.update', [$room, $images[1]]), ['sort_order' => 0, 'alt_text' => 'Sandy bedroom', 'is_primary' => 1])->assertSessionHasNoErrors();
        $this->assertFalse($images[0]->fresh()->is_primary);
        $this->assertTrue($images[1]->fresh()->is_primary);
        $this->assertSame($images[1]->id, $room->media()->first()->id);
        $this->delete(route('admin.rooms.images.destroy', [$room, $images[1]]))->assertRedirect();
        Storage::disk('public')->assertMissing($images[1]->path);
        $this->assertTrue($images[0]->fresh()->is_primary);
    }

    public function test_invalid_uploads_and_cross_room_image_changes_are_rejected(): void
    {
        Storage::fake('public');
        $room = $this->room();
        $other = $this->room('pearl');
        $media = $other->media()->create(['disk' => 'public', 'path' => 'rooms/test.jpg']);
        $this->actingAs($this->staff())->post(route('admin.rooms.images.store', $room), ['images' => [UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml')]])->assertSessionHasErrors('images.0');
        $this->patch(route('admin.rooms.images.update', [$room, $media]), ['sort_order' => 1])->assertNotFound();
        $this->delete(route('admin.rooms.images.destroy', [$room, $media]))->assertNotFound();
        $this->assertDatabaseHas('villa_media', ['id' => $media->id]);
    }
}
