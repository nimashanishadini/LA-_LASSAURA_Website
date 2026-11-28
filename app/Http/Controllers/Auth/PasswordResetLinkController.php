<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        if (request()->routeIs('admin.password.request')) {
            return view('auth.admin.forgot-password');
        }

        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        /*
         * Admin password recovery.
         */
        if ($request->routeIs('admin.password.email')) {

            $status = Password::sendResetLink(
                $request->only('email'),

                function (User $user, string $token) {

                    if (! $user->canAccessAdmin()) {
                        return;
                    }

                    $notification = new class($token)
                        extends ResetPassword
                    {
                        protected function resetUrl($notifiable)
                        {
                            return route('admin.password.reset', [
                                'token' => $this->token,
                                'email' => $notifiable
                                    ->getEmailForPasswordReset(),
                            ]);
                        }
                    };

                    $user->notify($notification);
                }
            );

            /*
             * Use the same response for valid and
             * unknown accounts to reduce account enumeration.
             */
            return back()->with(
                'status',
                'If an eligible account exists, a password reset link will be sent to your email address.'
            );
        }

        /*
         * Preserve existing customer password recovery.
         */
       
/*
 * Customer password recovery.
 */

$user = User::where(
    'email',
    $request->input('email')
)->first();

if ($user && ! $user->canAccessAdmin()) {
    Password::sendResetLink(
        $request->only('email')
    );
}

$message = 'If an eligible account exists, '
    . 'a password reset link will be sent '
    . 'to your email address.';

if ($request->expectsJson()) {
    return response()->json([
        'message' => $message,
    ]);
}

return back()->with('status', $message);

    }
}