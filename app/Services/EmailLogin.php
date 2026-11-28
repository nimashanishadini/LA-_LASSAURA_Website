<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\LoginOtpNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EmailLogin
{
    public function send(string $email, string $sessionSecret): void
    {
        $key = 'login-otp-send:'.hash('sha256', $email);
        if (RateLimiter::tooManyAttempts($key, config('verification.max_sends_per_hour'))) {
            abort(429, 'Too many sign-in codes requested. Please try again later.');
        }
        $cooldown = 'login-otp-cooldown:'.hash('sha256', $email);
        if (RateLimiter::tooManyAttempts($cooldown, 1)) {
            abort(429, 'Please wait 60 seconds before requesting another code.');
        }
        RateLimiter::hit($key, 3600);
        RateLimiter::hit($cooldown, config('verification.resend_seconds'));

        $result = DB::transaction(function () use ($email, $sessionSecret) {
            $user = User::where('email', $email)->lockForUpdate()->first();
            // Staff continue to use their password. Keep the guest response identical.
            if (! $user || $user->canAccessAdmin()) return null;
            $otp = DB::table('login_otps')->where('user_id', $user->id)->first();
            $active = $otp && $otp->email === $email && Carbon::parse($otp->expires_at)->isFuture();
            if ($active && $otp->attempts >= config('verification.max_attempts')) return null;
            do { $code = (string) random_int(100000, 999999); }
            while ($otp?->code_hash && Hash::check($code, $otp->code_hash));
            DB::table('login_otps')->updateOrInsert(['user_id' => $user->id], [
                'email' => $email, 'code_hash' => Hash::make($code),
                'session_hash' => hash('sha256', $sessionSecret),
                'password_fingerprint' => hash('sha256', $user->getAuthPassword()),
                'attempts' => $active ? $otp->attempts : 0,
                'expires_at' => now()->addMinutes(config('verification.expiry_minutes')),
                'sent_at' => now(), 'created_at' => $otp?->created_at ?? now(), 'updated_at' => now(),
            ]);
            return [$user, $code];
        });
        if ($result) {
            try { $result[0]->notify(new LoginOtpNotification($result[1])); }
            catch (\Throwable $error) {
                Log::warning('Sign-in code delivery failed.', ['error_type' => get_class($error)]);
            }
        }
    }

    public function verify(string $email, string $sessionSecret, string $code): User
    {
        $user = DB::transaction(function () use ($email, $sessionSecret, $code) {
            $user = User::where('email', $email)->lockForUpdate()->first();
            if (! $user || $user->canAccessAdmin()) return null;
            $otp = DB::table('login_otps')->where('user_id', $user->id)->lockForUpdate()->first();
            if (! $otp || ! $otp->code_hash || $otp->email !== $user->email
                || ! hash_equals($otp->session_hash, hash('sha256', $sessionSecret))
                || ! hash_equals($otp->password_fingerprint, hash('sha256', $user->getAuthPassword()))
                || Carbon::parse($otp->expires_at)->lte(now())
                || $otp->attempts >= config('verification.max_attempts')) return null;
            DB::table('login_otps')->where('id', $otp->id)->increment('attempts');
            if (! Hash::check($code, $otp->code_hash)) return null;
            DB::table('login_otps')->where('id', $otp->id)->update(['code_hash' => null, 'updated_at' => now()]);
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
                DB::afterCommit(fn () => event(new Verified($user)));
            }
            return $user;
        });
        // Failed attempts must commit even when validation fails.
        if (! $user) throw ValidationException::withMessages(['code' => 'The sign-in code is incorrect or expired. Request a new code and try again.']);
        return $user;
    }
}
