<?php

namespace App\Services;

use App\Models\EmailOtp;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EmailVerification
{
    public function state(User $user): array
    {
        $otp = EmailOtp::where('user_id', $user->id)->where('email', $user->email)->first();

        return ['verified' => $user->hasVerifiedEmail(), 'email' => $user->email, 'expires_at' => $otp?->expires_at?->toIso8601String(), 'resend_after' => $otp ? max(0, $otp->sent_at->timestamp + config('verification.resend_seconds') - now()->timestamp) : 0, 'attempts_remaining' => $otp ? max(0, config('verification.max_attempts') - $otp->attempts) : config('verification.max_attempts'), 'delivery' => $otp?->delivery];
    }

    public function send(User $user, bool $strict = false): array
    {
        $result = DB::transaction(function () use ($user) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($user->hasVerifiedEmail()) {
                return ['skip' => true];
            }
            $otp = EmailOtp::where('user_id', $user->id)->first();
            if ($otp && $otp->email === $user->email && $otp->expires_at->isFuture() && $otp->attempts >= config('verification.max_attempts')) {
                return ['wait' => max(1, $otp->expires_at->timestamp - now()->timestamp), 'message' => 'Too many incorrect codes. Wait until this challenge expires before requesting another code.'];
            }
            if ($otp && $otp->email === $user->email && $otp->sent_at->timestamp + config('verification.resend_seconds') > now()->timestamp) {
                return ['wait' => $otp->sent_at->timestamp + config('verification.resend_seconds') - now()->timestamp, 'message' => 'Please wait before requesting another code.'];
            }
            $key = 'email-otp-send:'.$user->id;
            if (RateLimiter::tooManyAttempts($key, config('verification.max_sends_per_hour'))) {
                return ['wait' => RateLimiter::availableIn($key), 'message' => 'Too many verification emails requested. Please try later.'];
            }
            RateLimiter::hit($key, 3600);
            do {
                $code = (string) random_int(100000, 999999);
            } while ($otp?->code_hash && Hash::check($code, $otp->code_hash));
            $attempts = $otp && $otp->email === $user->email && $otp->expires_at->isFuture() ? $otp->attempts : 0;
            EmailOtp::updateOrCreate(['user_id' => $user->id], ['email' => $user->email, 'code_hash' => Hash::make($code), 'attempts' => $attempts, 'sent_at' => now(), 'expires_at' => now()->addMinutes(config('verification.expiry_minutes')), 'consumed_at' => null, 'delivery' => 'pending']);

            return ['code' => $code, 'email' => $user->email];
        });
        if (isset($result['wait']) && $strict) {
            throw new HttpException(429, $result['message'], null, ['Retry-After' => (string) $result['wait']]);
        }
        if (isset($result['code'])) {
            try {
                $user->notify(new EmailOtpNotification($result['code']));
                $delivery = in_array(config('mail.default'), ['log', 'array'], true) ? 'local' : 'sent';
            } catch (\Throwable $error) {
                $delivery = 'failed';
                Log::warning('Email OTP delivery failed.', ['user_id' => $user->id, 'error_type' => get_class($error)]);
            }
            // Do not modify a replacement challenge created after an email/account change.
            EmailOtp::where('user_id', $user->id)->where('email', $result['email'])->update(['delivery' => $delivery]);
        }

        return $this->state($user->refresh());
    }

    public function verify(User $user, string $code): array
    {
        $result = DB::transaction(function () use ($user, $code) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($user->hasVerifiedEmail()) {
                return ['ok' => true];
            }
            $otp = EmailOtp::where('user_id', $user->id)->lockForUpdate()->first();
            if (! $otp || $otp->email !== $user->email || ! $otp->code_hash || $otp->consumed_at) {
                return ['error' => 'Request a new verification code.'];
            }
            if ($otp->expires_at->lte(now())) {
                return ['error' => 'This verification code has expired. Request a new code.'];
            }
            if ($otp->attempts >= config('verification.max_attempts')) {
                return ['locked' => true, 'wait' => max(1, $otp->expires_at->timestamp - now()->timestamp)];
            }
            $otp->increment('attempts');
            if (! Hash::check($code, $otp->code_hash)) {
                return ['error' => 'That code is incorrect. Please check your email and try again.'];
            }
            $user->markEmailAsVerified();
            $otp->update(['code_hash' => null, 'consumed_at' => now()]);
            DB::afterCommit(fn () => event(new Verified($user)));

            return ['ok' => true];
        });
        // Throw outside the transaction so failed-attempt counters are committed.
        if (isset($result['locked'])) {
            throw new HttpException(429, 'Too many incorrect codes. Wait for expiry and request a new code.', null, ['Retry-After' => (string) $result['wait']]);
        }
        if (isset($result['error'])) {
            throw ValidationException::withMessages(['code' => $result['error']]);
        }

        return $this->state($user->refresh());
    }
}
