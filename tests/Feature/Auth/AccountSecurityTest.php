<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_failures_lock_out_even_correct_credentials_until_cooldown(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', ['email' => $user->email, 'password' => 'incorrect'])->assertUnprocessable();
        }
        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertGuest();
        $this->travel(61)->seconds();
        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_idle_session_expires_with_json_for_popup_and_redirect_for_html(): void
    {
        config(['session.lifetime' => 30]);
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth.last_activity' => now()->subMinutes(31)->timestamp])->getJson('/my-bookings')->assertUnauthorized()->assertJsonPath('code', 'session_expired');
        $this->assertGuest();
        $this->actingAs($user)->withSession(['auth.last_activity' => now()->subMinutes(31)->timestamp])->get('/profile')->assertRedirect(route('login'));
    }

    public function test_activity_extends_idle_timer_and_password_change_invalidates_old_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth.last_activity' => now()->subMinute()->timestamp])->get('/profile')->assertOk()->assertSessionHas('auth.last_activity', now()->timestamp);
        $user->password = Hash::make('new-password');
        $user->save();
        $this->getJson('/my-bookings')->assertUnauthorized();
        $this->assertGuest();
    }

    public function test_password_reset_token_expires_is_single_use_and_does_not_verify_email(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->post('/forgot-password', ['email' => $user->email]);
        $token = Notification::sent($user, ResetPassword::class)->last()->token;
        $payload = ['token' => $token, 'email' => $user->email, 'password' => 'new-password', 'password_confirmation' => 'new-password'];
        $this->post('/reset-password', $payload)->assertSessionHasNoErrors()->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');
        $this->travel(61)->minutes();
        $this->post('/forgot-password', ['email' => $user->email]);
        $token = Notification::sent($user, ResetPassword::class)->last()->token;
        $this->travel(61)->minutes();
        $payload['token'] = $token;
        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');
    }

    public function test_staff_roles_cannot_modify_protected_settings_or_customer_data(): void
    {
        foreach (['reception', 'finance', 'housekeeping', 'editor'] as $role) {
            $user = User::factory()->create();
            $user->role = $role;
            $user->save();
            $this->actingAs($user);
            $this->putJson(route('admin.booking-policies.save'), ['role' => 'owner'])->assertForbidden();
            $this->postJson(route('admin.rates.exchange'), ['lkr_per_usd' => '1', 'reason' => 'Not permitted'])->assertForbidden();
            $this->patch('/profile', ['name' => $user->name, 'email' => $user->email, 'role' => 'owner'])->assertSessionHasNoErrors();
            $this->assertSame($role, $user->fresh()->role);
        }
    }
}
