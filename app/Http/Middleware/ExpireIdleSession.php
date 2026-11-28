<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpireIdleSession
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $last = $request->session()->get('auth.last_activity');
            if ($last && now()->timestamp - (int) $last >= config('session.lifetime') * 60) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return ($request->expectsJson() || $request->is('api/*')) ? response()->json(['message' => 'Your session expired. Sign in again.', 'code' => 'session_expired'], 401) : redirect()->route('login')->with('status', 'Your session expired. Please sign in again.');
            }
            $request->session()->put('auth.last_activity', now()->timestamp);
        }

        return $next($request);
    }
}
