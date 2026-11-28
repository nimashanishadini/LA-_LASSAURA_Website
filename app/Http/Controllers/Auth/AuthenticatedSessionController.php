<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\EmailVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the customer login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Display the admin login view.
     */
    public function createAdmin(): View
    {
        return view('auth.admin.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        /*
         * Admin portal access restriction.
         *
         * Customer accounts must not sign in
         * through the admin login page.
         */
        if (
            $request->routeIs('admin.login.store') &&
            Gate::denies('access-admin')
        ) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ])->redirectTo(route('admin.login'));
        }

        /*
         * Preserve existing session activity tracking.
         */
        $request->session()->put(
            'auth.last_activity',
            now()->timestamp
        );

        /*
         * Preserve existing email verification.
         */
        if (! $request->user()->hasVerifiedEmail()) {
            app(EmailVerification::class)
                ->send($request->user());
        }

        /*
         * Preserve JSON responses used by
         * the website booking authentication flow.
         */
        if ($request->expectsJson()) {
            return response()->json([
                'user' => array_merge(
                    $request->user()->only(['name', 'email']),
                    [
                        'email_verified' =>
                            $request->user()->hasVerifiedEmail(),
                    ]
                ),

                'verification' => app(EmailVerification::class)
                    ->state($request->user()),

                'csrf_token' => $request->session()->token(),
            ]);
        }

        /*
         * Unverified accounts must complete
         * email verification before continuing.
         */
        if (! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $request->session()->forget('url.intended');

        /*
         * Role-based redirect.
         */
        if ($request->user()->canAccessAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        /*
         * Preserve existing customer redirect.
         */
        return redirect()->route('bookings.index');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'csrf_token' => $request->session()->token(),
            ]);
        }

        return redirect('/');
    }
}