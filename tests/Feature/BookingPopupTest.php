<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_keep_booking_inside_a_dialog_without_my_bookings_navigation(): void
    {
        $this->get('/')->assertOk()->assertSee('bookingPopup(')->assertSee('booking-popup-title')->assertSee('Your requests')->assertDontSee('My bookings');
        $this->get('/booking?room=coral&adults=2')->assertRedirect(route('home', ['room' => 'coral', 'adults' => '2', 'booking' => 1]));
    }

    public function test_inline_login_returns_fresh_csrf_and_safe_account_fields_without_redirect(): void
    {
        $user = User::factory()->create();
        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])->assertOk()->assertJsonPath('user.email', $user->email)->assertJsonStructure(['csrf_token'])->assertJsonMissingPath('user.password');
        $this->get('/my-bookings')->assertRedirect(route('home', ['booking' => 1, 'history' => 1]));
        $this->getJson('/my-bookings')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_inline_auth_validation_keeps_errors_as_json(): void
    {
        $this->postJson('/login', ['email' => 'no@example.com', 'password' => 'wrong'])->assertUnprocessable();
        $this->postJson('/register', ['name' => 'Guest', 'email' => 'bad', 'password' => 'short', 'password_confirmation' => 'different'])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }
}
