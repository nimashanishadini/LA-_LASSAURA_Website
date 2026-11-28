<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\LoginOtpNotification;
use App\Services\EmailLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EmailLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function code(User $user): string
    {
        return Notification::sent($user, LoginOtpNotification::class)->last()->code;
    }

    public function test_customer_can_sign_in_and_verify_email_with_a_single_use_code(): void
    {
        $user = User::factory()->unverified()->create();
        $this->postJson(route('login.code.send'), ['email' => $user->email])->assertOk()->assertJsonMissingPath('code');
        $code = $this->code($user);
        $row = DB::table('login_otps')->first();
        $this->assertTrue(Hash::check($code, $row->code_hash));
        $this->assertGuest();
        $this->postJson(route('login.code.verify'), ['email' => $user->email, 'code' => $code])
            ->assertOk()->assertJsonPath('user.email_verified', true)->assertJsonStructure(['csrf_token'])->assertJsonMissingPath('user.password');
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNull(DB::table('login_otps')->first()->code_hash);
        $this->post('/logout');
        $this->withSession(['login_otp' => ['email' => $user->email, 'secret' => 'other-browser']])
            ->postJson(route('login.code.verify'), ['email' => $user->email, 'code' => $code])->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_unknown_and_staff_emails_get_the_same_response_without_creating_accounts_or_sending_codes(): void
    {
        $staff = User::factory()->create(['role' => 'owner']);
        $unknown = $this->postJson(route('login.code.send'), ['email' => 'unknown@example.invalid'])->assertOk()->json();
        $known = $this->postJson(route('login.code.send'), ['email' => $staff->email])->assertOk()->json();
        $this->assertSame($unknown, $known);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('login_otps', 0);
        Notification::assertNothingSent();
    }

    public function test_wrong_attempts_are_persisted_and_resend_cannot_reset_lockout(): void
    {
        $user = User::factory()->create();
        $service = app(EmailLogin::class);
        $service->send($user->email, 'browser-one');
        $code = $this->code($user);
        for ($i = 0; $i < 5; $i++) {
            try { $service->verify($user->email, 'browser-one', $code === '100000' ? '100001' : '100000'); }
            catch (ValidationException) {}
        }
        $this->assertSame(5, DB::table('login_otps')->first()->attempts);
        $this->travel(61)->seconds();
        $service->send($user->email, 'browser-one');
        $this->assertCount(1, Notification::sent($user, LoginOtpNotification::class));
        $this->expectException(ValidationException::class);
        $service->verify($user->email, 'browser-one', $code);
    }

    public function test_resend_replaces_code_and_keeps_failed_attempts(): void
    {
        $user = User::factory()->create(); $service = app(EmailLogin::class);
        $service->send($user->email, 'browser'); $old = $this->code($user);
        try { $service->verify($user->email, 'browser', '000000'); } catch (ValidationException) {}
        $this->travel(61)->seconds(); $service->send($user->email, 'browser');
        $this->assertNotSame($old, $this->code($user));
        $this->assertSame(1, DB::table('login_otps')->first()->attempts);
        $this->expectException(ValidationException::class); $service->verify($user->email, 'browser', $old);
    }

    public function test_expired_code_cannot_sign_in(): void
    {
        $user = User::factory()->create();
        $this->postJson(route('login.code.send'), ['email' => $user->email])->assertOk();
        $this->travel(11)->minutes();
        $this->postJson(route('login.code.verify'), ['email' => $user->email, 'code' => $this->code($user)])->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_code_is_bound_to_browser_and_password(): void
    {
        $user = User::factory()->create(); $service = app(EmailLogin::class);
        $service->send($user->email, 'browser-one'); $code = $this->code($user);
        try { $service->verify($user->email, 'browser-two', $code); $this->fail('Another browser must not consume the code.'); } catch (ValidationException) {}
        $this->assertSame(0, DB::table('login_otps')->first()->attempts);
        $user->update(['password' => 'a-different-password']);
        $this->expectException(ValidationException::class); $service->verify($user->email, 'browser-one', $code);
    }

    public function test_resend_and_invalid_payload_are_throttled_or_rejected(): void
    {
        $user = User::factory()->create();
        $this->postJson(route('login.code.send'), ['email' => 'invalid'])->assertUnprocessable();
        $this->postJson(route('login.code.send'), ['email' => $user->email])->assertOk();
        $this->postJson(route('login.code.send'), ['email' => $user->email])->assertStatus(429);
        $this->postJson(route('login.code.verify'), ['email' => $user->email, 'code' => '123'])->assertUnprocessable();
    }
}
