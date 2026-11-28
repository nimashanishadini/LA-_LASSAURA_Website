<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailVerification;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class EmailOtpController extends Controller
{
    public function show(
        Request $request,
        EmailVerification $verification
    ) {
        if ($request->expectsJson()) {
            return response()->json([
                'verification' => $verification->state(
                    $request->user()
                ),
            ]);
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route(
                $request->user()->canAccessAdmin()
                    ? 'admin.dashboard'
                    : 'home'
            );
        }

        $view = $request->user()->canAccessAdmin()
            ? 'auth.admin.verify-email'
            : 'auth.verify-email';

        return view($view, [
            'verification' => $verification->state(
                $request->user()
            ),
        ]);
    }

    public function send(
        Request $request,
        EmailVerification $verification
    ) {
        try {
            $state = $verification->send(
                $request->user(),
                true
            );
        } catch (HttpExceptionInterface $exception) {
            if (
                $exception->getStatusCode() !== 429 ||
                $request->expectsJson() ||
                ! $request->user()->canAccessAdmin()
            ) {
                throw $exception;
            }

            return back()->withErrors([
                'resend' => $exception->getMessage(),
            ]);
        }

        return $request->expectsJson()
            ? response()->json([
                'verification' => $state,
            ])
            : back()->with(
                'status',
                $state['delivery'] === 'sent'
                    ? 'verification-code-sent'
                    : 'verification-delivery-pending'
            );
    }

    public function verify(
        Request $request,
        EmailVerification $verification
    ) {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'regex:/^\d{6}$/',
            ],
        ]);

        try {
            $state = $verification->verify(
                $request->user(),
                $data['code']
            );
        } catch (HttpExceptionInterface $exception) {
            if (
                $exception->getStatusCode() !== 429 ||
                $request->expectsJson() ||
                ! $request->user()->canAccessAdmin()
            ) {
                throw $exception;
            }

            return back()->withErrors([
                'code' => $exception->getMessage(),
            ]);
        }

        $request->session()->regenerate();

        $request->session()->put(
            'auth.last_activity',
            now()->timestamp
        );

        if ($request->expectsJson()) {
            return response()->json([
                'verification' => $state,
                'user' => [
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'email_verified' => true,
                ],
                'csrf_token' => $request->session()->token(),
            ]);
        }

        return redirect()->route(
            $request->user()->canAccessAdmin()
                ? 'admin.dashboard'
                : 'home'
        )->with('status', 'email-verified');
    }
}
