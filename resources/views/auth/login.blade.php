
@extends('layouts.customer-auth')

@section('title', 'Sign In')

@section('content')

<div
    class="customer-login"
    x-data="{
        showPassword: false,
        submitting: false
    }"
    @pageshow.window="submitting = false"
>

    {{-- Heading --}}
    <div class="customer-auth-heading">

        <span class="customer-auth-eyebrow">
            YOUR PRIVATE ESCAPE
        </span>

        <h1>Welcome back</h1>

        <p>
            Sign in to manage your reservations
            and plan your next escape.
        </p>

    </div>

    {{-- Session Status --}}
    @if(session('status'))
        <div class="customer-auth-success" role="status">
            {{ session('status') }}
        </div>
    @endif

    {{-- Login Form --}}
    <form
        method="POST"
        action="{{ route('login') }}"
        class="customer-auth-form"
        @submit="submitting = true"
    >

        @csrf

        {{-- Email --}}
        <div class="customer-auth-field">

            <label for="email">
                Email address
            </label>

            <div class="customer-auth-input-wrapper">

                <svg class="customer-auth-input-icon"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.5"
                     aria-hidden="true">
                    <rect x="3" y="5"
                          width="18" height="14" rx="2"/>
                    <path d="m3 7 9 6 9-6"/>
                </svg>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email"
                    autocomplete="username"
                    required
                    autofocus
                    class="customer-auth-input @error('email') is-invalid @enderror"
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    @error('email')
                        aria-describedby="email-error"
                    @enderror
                >

            </div>

            @error('email')
                <p id="email-error"
                   class="customer-auth-error"
                   role="alert">
                    {{ $message }}
                </p>
            @enderror

        </div>

        {{-- Password --}}
        <div class="customer-auth-field">

            <label for="password">
                Password
            </label>

            <div class="customer-auth-input-wrapper">

                <svg class="customer-auth-input-icon"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.5"
                     aria-hidden="true">
                    <rect x="4" y="10"
                          width="16" height="11" rx="2"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                </svg>

                <input
                    id="password"
                    name="password"
                    type="password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                    class="customer-auth-input customer-password-input @error('password') is-invalid @enderror"
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    @error('password')
                        aria-describedby="password-error"
                    @enderror
                >

                <button
                    type="button"
                    class="customer-password-toggle"
                    @click="showPassword = !showPassword"
                    :aria-pressed="showPassword"
                    x-text="showPassword ? 'Hide' : 'Show'"
                >
                    Show
                </button>

            </div>

            @error('password')
                <p id="password-error"
                   class="customer-auth-error"
                   role="alert">
                    {{ $message }}
                </p>
            @enderror

        </div>

        {{-- Remember Me & Forgot Password --}}
        <div class="customer-auth-options">

            <label class="customer-auth-remember">

                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                >

                <span>Remember me</span>

            </label>

            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="customer-auth-link">
                    Forgot password?
                </a>
            @endif

        </div>

        {{-- Submit --}}
        <button
            type="submit"
            class="customer-auth-submit"
            :disabled="submitting"
            :aria-busy="submitting"
            customer-login-submit
        >

            <span x-show="!submitting">
                Sign In &nbsp; &rarr;
            </span>

            <span
                x-show="submitting"
                x-cloak
                class="customer-auth-loading"
            >
                <span class="customer-auth-spinner"></span>
                Signing in...
            </span>

        </button>

    </form>

    {{-- Registration --}}
    <div class="customer-auth-separator">
        <span>New to La Lassaura?</span>
    </div>

    <a href="{{ route('register') }}"
       class="customer-auth-outline-button">

        Create an account

    </a>

    <p class="customer-auth-note">
        Your next unforgettable stay starts here.
    </p>

</div>

@endsection
