<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EmailLoginController extends Controller
{
    public function send(Request $request, EmailLogin $login)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $email = Str::lower(trim($data['email']));
        $secret = $request->session()->get('login_otp.secret') ?? Str::random(64);
        $request->session()->put('login_otp', ['email' => $email, 'secret' => $secret]);
        $login->send($email, $secret);
        return response()->json(['message' => 'If this email belongs to a customer account, a sign-in code has been sent. Check Inbox and Spam. Staff should sign in with a password.', 'resend_after' => config('verification.resend_seconds')]);
    }

    public function verify(Request $request, EmailLogin $login)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255'], 'code' => ['required', 'regex:/^[0-9]{6}$/']]);
        $email = Str::lower(trim($data['email']));
        abort_unless($request->session()->get('login_otp.email') === $email, 422, 'Request a sign-in code for this email first.');
        $user = $login->verify($email, $request->session()->get('login_otp.secret', ''), $data['code']);
        Auth::login($user);
        $request->session()->forget('login_otp');
        $request->session()->regenerate();
        $request->session()->put('auth.last_activity', now()->timestamp);
        return response()->json(['user' => array_merge($user->only(['name', 'email']), ['email_verified' => true]), 'csrf_token' => $request->session()->token(), 'verification' => null]);
    }
}
