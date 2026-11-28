<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        if ($request->routeIs('admin.password.reset')) {

            return view('auth.admin.reset-password', [
                'request' => $request,
            ]);
        }

        return view('auth.reset-password', [
            'request' => $request,
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $isAdminReset = $request->routeIs(
            'admin.password.store'
        );

        /*
         * Use the existing Laravel password broker.
         */
        $status = Password::reset(

            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),

            function (User $user) use (
                $request,
                $isAdminReset
            ) {

                /*
                 * Prevent customer password resets
                 * through the admin recovery endpoint.
                 */
                if (
                    $isAdminReset &&
                    ! $user->canAccessAdmin()
                ) {
                    throw ValidationException::withMessages([
                        'email' => __(
                            Password::INVALID_TOKEN
                        ),
                    ]);
                }

                $user->forceFill([

                    'password' => Hash::make(
                        $request->password
                    ),

                    'remember_token' => Str::random(60),

                ])->save();

                event(new PasswordReset($user));
            }
        );

        /*
         * Redirect to the appropriate login page.
         */
        if ($status == Password::PASSWORD_RESET) {

            return redirect()
                ->route(
                    $isAdminReset
                        ? 'admin.login'
                        : 'login'
                )
                ->with('status', __($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => __($status),
            ]);
    }
}
