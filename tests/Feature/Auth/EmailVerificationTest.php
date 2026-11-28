<?php

namespace Tests\Feature\Auth;

use App\Models\EmailOtp;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use App\Services\EmailVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function issue(User $user): string
    {
        app(EmailVerification::class)->send($user);

        return Notification::sent($user, EmailOtpNotification::class)->last()->code;
    }

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->unverified()->create())->get('/verify-email')->assertOk()->assertSee('six-digit code');
    }

    public function test_registration_sends_a_hashed_otp_and_verifies_without_redirecting_popup(): void
    {
        $response = $this->postJson('/register', ['name' => 'Guest', 'email' => 'otp@example.com', 'password' => 'password', 'password_confirmation' => 'password'])->assertCreated();
        $user = User::where('email', 'otp@example.com')->firstOrFail();
        Notification::assertSentTo($user, EmailOtpNotification::class);
        $code = Notification::sent($user, EmailOtpNotification::class)->last()->code;
        $otp = EmailOtp::firstOrFail();
        $this->assertNotSame($code, $otp->code_hash);
        $this->assertTrue(Hash::check($code, $otp->code_hash));
        $this->assertStringNotContainsString($code, $response->getContent());
        $response->assertJsonPath('user.email_verified', false);
        $this->postJson('/email/verify', ['code' => $code])->assertOk()->assertJsonPath('user.email_verified', true);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNull($otp->fresh()->code_hash);
        $this->assertNotNull($otp->fresh()->consumed_at);
    }

    public function test_wrong_attempts_commit_and_lockout_cannot_be_reset_by_resending(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->issue($user);
        $this->actingAs($user);
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/email/verify', ['code' => $code === '100000' ? '100001' : '100000'])->assertUnprocessable();
        }
        $this->assertSame(5, EmailOtp::first()->attempts);
        $this->postJson('/email/verify', ['code' => $code])->assertStatus(429);
        $this->travel(61)->seconds();
        $this->postJson('/email/verification-notification')->assertStatus(429);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_expired_code_is_rejected_and_resend_invalidates_old_code(): void
    {
        $user = User::factory()->unverified()->create();
        $old = $this->issue($user);
        $this->actingAs($user);
        $this->postJson('/email/verification-notification')->assertStatus(429)->assertHeader('Retry-After');
        $this->travel(61)->seconds();
        $this->postJson('/email/verification-notification')->assertOk();
        $new = Notification::sent($user, EmailOtpNotification::class)->last()->code;
        $this->assertNotSame($old, $new);
        $this->postJson('/email/verify', ['code' => $old])->assertUnprocessable();
        $this->travel(11)->minutes();
        $this->postJson('/email/verify', ['code' => $new])->assertUnprocessable();
        $this->postJson('/email/verification-notification')->assertOk();
        $fresh = Notification::sent($user, EmailOtpNotification::class)->last()->code;
        $this->postJson('/email/verify', ['code' => $fresh])->assertOk();
    }

    public function test_code_is_bound_to_account_and_current_email_and_old_link_cannot_bypass_it(): void
    {
        $a = User::factory()->unverified()->create();
        $b = User::factory()->unverified()->create();
        $code = $this->issue($a);
        $this->actingAs($b)->postJson('/email/verify', ['code' => $code])->assertUnprocessable();
        $this->actingAs($a)->get('/verify-email/'.$a->id.'/'.sha1($a->email))->assertNotFound();
        $this->patch('/profile', ['name' => $a->name, 'email' => 'changed@example.com'])->assertRedirect();
        $this->postJson('/email/verify', ['code' => $code])->assertUnprocessable();
        $this->assertFalse($a->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_accounts_cannot_submit_or_access_protected_admin_routes(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        $this->postJson('/bookings', ['status' => 'confirmed'])->assertForbidden()->assertJsonPath('code', 'email_verification_required');
        $this->postJson('/api/v1/quotes', [])->assertForbidden();
        $user->role = 'owner';
        $user->save();
        $this->get('/admin')->assertRedirect(route('verification.notice'));
        $code = $this->issue($user);
        $this->postJson('/email/verify', ['code' => $code])->assertOk();
        $this->get('/admin')->assertOk();
    }

    public function test_hourly_send_limit_and_attempt_count_survive_resends(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->issue($user);
        $this->actingAs($user);
        $this->postJson('/email/verify', ['code' => $code === '100000' ? '100001' : '100000'])->assertUnprocessable();
        for ($i = 0; $i < 4; $i++) {
            $this->travel(61)->seconds();
            $this->postJson('/email/verification-notification')->assertOk();
        }
        $this->assertSame(1, EmailOtp::first()->attempts);
        $this->travel(61)->seconds();
        $this->postJson('/email/verification-notification')->assertStatus(429);
    }

    public function test_failed_html_verification_does_not_flash_code_into_session(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->issue($user);
        $this->actingAs($user)->from('/verify-email')->post('/email/verify', ['code' => $code === '100000' ? '100001' : '100000'])
            ->assertRedirect('/verify-email')->assertSessionHasErrors('code')->assertSessionMissing('_old_input.code');
    }

    public function test_delivery_failure_does_not_delete_the_new_account(): void
    {
        Notification::shouldReceive('send')->andThrow(new \RuntimeException('SMTP unavailable'));
        $this->postJson('/register', ['name' => 'Guest', 'email' => 'delivery@example.com', 'password' => 'password', 'password_confirmation' => 'password'])->assertCreated()->assertJsonPath('verification.delivery', 'failed');
        $this->assertDatabaseHas('users', ['email' => 'delivery@example.com', 'email_verified_at' => null]);
    }
}
