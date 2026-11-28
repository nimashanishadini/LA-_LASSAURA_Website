<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the customer to Google.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle Google's authentication callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return $this->loginError(
                'Google sign-in was cancelled. Please try again.'
            );
        }

        try {
            $googleUser = Socialite::driver('google')->user();

            $googleId = (string) $googleUser->getId();

            $email = Str::lower(
                trim((string) $googleUser->getEmail())
            );

            $googleData = $googleUser->getRaw();

            $emailVerified =
                $googleData['email_verified']
                ?? $googleData['verified_email']
                ?? false;

            // Only accept verified Google identities.
            if (
                $googleId === '' ||
                strlen($googleId) > 255 ||
                strlen($email) > 255 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                $emailVerified !== true
            ) {
                return $this->loginError(
                    'Unable to verify your Google account.'
                );
            }

            $user = DB::transaction(function () use (
                $googleUser,
                $googleId,
                $email
            ) {
                // Find an already linked Google account.
                $existingGoogleUser = User::where(
                    'google_id',
                    $googleId
                )->lockForUpdate()->first();

                if ($existingGoogleUser) {
                    if (
                        $existingGoogleUser->role !== 'customer' ||
                        Str::lower($existingGoogleUser->email) !== $email ||
                        !$existingGoogleUser->hasVerifiedEmail()
                    ) {
                        return null;
                    }

                    return $existingGoogleUser;
                }

                // Never automatically link an existing account
                // using only a matching email address.
                $existingEmailUser = User::where(
                    'email',
                    $email
                )->lockForUpdate()->first();

                if ($existingEmailUser) {
                    return null;
                }

                // Create a new verified customer account.
                $user = new User();

                $user->name = Str::limit(
                    $googleUser->getName() ?: 'Guest',
                    255,
                    ''
                );

                $user->email = $email;
                $user->google_id = $googleId;

                $user->password = Hash::make(
                    Str::random(64)
                );

                $user->role = 'customer';
                $user->email_verified_at = now();

                $user->save();

                return $user;
            });

            if (!$user) {
                return $this->loginError(
                    'Google sign-in is unavailable for this account. '
                    . 'Please use your existing sign-in method.'
                );
            }

            // Authenticate the customer.
            Auth::guard('web')->login($user);

            // Prevent session fixation.
            $request->session()->regenerate();

            $request->session()->put(
                'auth.last_activity',
                now()->timestamp
            );

            $request->session()->forget('url.intended');

            return redirect()
                ->route('home')
                ->with(
                    'status',
                    'Successfully signed in with Google.'
                );

        } catch (Throwable $exception) {

            // Do not log OAuth tokens or client secrets.
            Log::warning('Google authentication failed.', [
                'exception_type' => get_class($exception),
            ]);

            return $this->loginError(
                'Unable to sign in with Google. Please try again.'
            );
        }
    }

    /**
     * Return the customer to the login page.
     */
    private function loginError(
        string $message
    ): RedirectResponse {
        return redirect()
            ->route('login')
            ->withErrors([
                'email' => $message,
            ]);
    }
}