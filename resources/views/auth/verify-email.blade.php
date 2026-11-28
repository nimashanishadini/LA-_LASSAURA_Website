
@extends('layouts.admin-auth')

@section('title', 'Verify Email | La Lassaura Admin')

@section('content')

<div
    class="admin-login admin-verify-email"
    x-data="{
        code: '',
        busy: '',
        remaining: {{ (int) ($verification['resend_after'] ?? 0) }},
        deadline: 0,
        timer: null,

        init() {
            this.deadline = Date.now() + this.remaining * 1000;

            this.timer = setInterval(() => {
                this.remaining = Math.max(
                    0,
                    Math.ceil((this.deadline - Date.now()) / 1000)
                );
            }, 1000);
        },

        destroy() {
            clearInterval(this.timer);
        }
    }"
    @pageshow.window="busy = ''"
>

    {{-- Verification Icon --}}
    <div class="admin-auth-icon" aria-hidden="true">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="26"
            height="26"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <rect x="2" y="4" width="20" height="16" rx="3"/>
            <path d="m2 7 10 7 10-7"/>
        </svg>
    </div>

    {{-- Heading --}}
    <div class="admin-verify-heading">

        <span class="admin-verify-eyebrow">
            ADMIN PORTAL
        </span>

        <h1>Verify your email</h1>

        <p>
            Enter the six-digit verification code sent to
            your registered email address.
        </p>

    </div>

    {{-- Registered Email --}}
    <div class="admin-verify-address">

        <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
            aria-hidden="true"
        >
            <rect x="3" y="5" width="18" height="14" rx="2"/>
            <path d="m3 7 9 6 9-6"/>
        </svg>

        <div>
            <span>Verification email</span>

            <strong>
                {{ auth()->user()->email }}
            </strong>
        </div>

    </div>

    {{-- Success Message --}}
    @if(session('status') === 'verification-code-sent')
        <div class="admin-verify-alert is-success" role="status">
            A new verification code has been sent to your email.
        </div>
    @endif

    {{-- Delivery Errors --}}
    @if(($verification['delivery'] ?? null) === 'failed')
        <div class="admin-verify-alert is-warning" role="alert">
            Email delivery failed. Please try resending the code
            or contact your system administrator.
        </div>
    @elseif(($verification['delivery'] ?? null) === 'local')
        <div class="admin-verify-alert is-warning" role="alert">
            Live email delivery is not configured.
            Contact your system administrator.
        </div>
    @elseif(($verification['delivery'] ?? null) === 'pending')
        <div class="admin-verify-alert is-warning" role="status">
            Email delivery has not been confirmed yet.
            Please check your inbox or try again later.
        </div>
    @elseif(empty($verification['expires_at']))
        <div class="admin-verify-alert is-warning" role="status">
            Request a verification code to continue.
        </div>
    @endif

    {{-- Verification Form --}}
    <form
        method="POST"
        action="{{ route('verification.verify') }}"
        class="admin-verify-form"
        @submit="busy = 'verify'"
    >
        @csrf

        <div class="admin-otp-field">

            <label for="email-code">
                Verification code
            </label>

            <input
                id="email-code"
                name="code"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="[0-9]{6}"
                minlength="6"
                maxlength="6"
                placeholder="000000"
                required
                autofocus
                x-model="code"
                @input="
                    code = $event.target.value
                        .replace(/[^0-9]/g, '')
                        .slice(0, 6);
                    $event.target.value = code;
                "
                class="admin-otp-input @error('code') is-invalid @enderror"
                aria-invalid="{{ $errors->has('code') ? 'true' : 'false' }}"
                aria-describedby="otp-hint @error('code') otp-error @enderror"
            >

            @error('code')
                <p
                    id="otp-error"
                    class="admin-verify-error"
                    role="alert"
                >
                    {{ $message }}
                </p>
            @enderror

            <div id="otp-hint" class="admin-otp-hint">

                <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 2"/>
                </svg>

                <span>
                    Code expires after
                    {{ config('verification.expiry_minutes', 10) }}
                    minutes.
                </span>

            </div>

            @if(isset($verification['attempts_remaining']))
                <p class="admin-otp-attempts">
                    Attempts remaining:
                    {{ $verification['attempts_remaining'] }}
                </p>
            @endif

        </div>

        <button
            type="submit"
            class="admin-verify-submit"
            :disabled="busy !== '' || code.length !== 6"
            :aria-busy="busy === 'verify'"
        >
            <span x-show="busy !== 'verify'">
                Verify email &nbsp; &rarr;
            </span>

            <span
                x-show="busy === 'verify'"
                x-cloak
                class="admin-verify-loading"
            >
                <span class="admin-verify-spinner"></span>
                Verifying...
            </span>
        </button>

    </form>

    {{-- Resend Code --}}
    <div class="admin-verify-resend">

        <p>Didn't receive the code?</p>

        <form
            method="POST"
            action="{{ route('verification.send') }}"
            @submit="busy = 'resend'"
        >
            @csrf

            <button
                type="submit"
                class="admin-verify-link"
                :disabled="busy !== '' || remaining > 0"
                :aria-busy="busy === 'resend'"
            >
                <span
                    x-show="remaining === 0 && busy !== 'resend'"
                >
                    Resend verification code
                </span>

                <span x-show="remaining > 0" x-cloak>
                    Resend in
                    <span x-text="remaining"></span>s
                </span>

                <span x-show="busy === 'resend'" x-cloak>
                    Sending code...
                </span>
            </button>

        </form>

        @error('resend')
            <p class="admin-verify-error" role="alert">
                {{ $message }}
            </p>
        @enderror

    </div>

    {{-- Help Message --}}
    <div class="admin-verify-help">

        <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
            aria-hidden="true"
        >
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v5"/>
            <path d="M12 8h.01"/>
        </svg>

        <p>
            Check your spam folder if you cannot find the email.
            Only the latest verification code can be used.
        </p>

    </div>

    {{-- Sign Out --}}
    <div class="admin-verify-footer">

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                class="admin-verify-signout"
                :disabled="busy !== ''"
            >
                <svg
                    width="17"
                    height="17"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true"
                >
                    <path d="M10 17l5-5-5-5"/>
                    <path d="M15 12H3"/>
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                </svg>

                Sign out
            </button>
        </form>

        <span>Secure account verification</span>

    </div>

</div>

@endsection
