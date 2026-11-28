<?php

namespace App\Http\Middleware;

use App\Services\EmailVerification;
use Closure;
use Illuminate\Http\Request;

class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->hasVerifiedEmail()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Verify your email with the code sent to your inbox before continuing.', 'code' => 'email_verification_required', 'verification' => $request->user() ? app(EmailVerification::class)->state($request->user()) : null], 403);
            }

            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
