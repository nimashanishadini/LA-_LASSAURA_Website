
@extends('layouts.admin-auth')

@section('title', 'Admin Login')

@section('content')

    <div class="admin-login"
         x-data="{ showPassword: false, submitting: false }"
         @pageshow.window="submitting = false">

        {{-- Security Icon --}}
        <div class="admin-login-icon">

            <svg xmlns="http://www.w3.org/2000/svg"
                 width="26"
                 height="26"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.6"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 aria-hidden="true">

                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"/>

                <path d="m9 12 2 2 4-4"/>

            </svg>

        </div>


        {{-- Heading --}}
        <div class="admin-login-heading">

            <p class="admin-login-eyebrow">
                Admin Portal
            </p>

            <h1>
                Welcome back
            </h1>

            <p class="admin-login-description">
                Sign in to your management account.
            </p>

        </div>


        {{-- Session Status --}}
        @if(session('status'))

            <div class="admin-auth-success"
                 role="status">

                {{ session('status') }}

            </div>

        @endif


        {{-- Login Form --}}
        <form
            method="POST"
            action="{{ route('admin.login.store') }}"
            class="admin-login-form"
            @submit="submitting = true"
        >

            @csrf


            {{-- Email Address --}}
            <div class="admin-auth-field">

                <label for="email">
                    Email address
                </label>

                <div class="admin-auth-input-wrapper">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.6"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         class="admin-auth-input-icon"
                         aria-hidden="true">

                        <rect x="3" y="5"
                              width="18" height="14" rx="2"/>

                        <path d="m3 7 9 6 9-6"/>

                    </svg>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email"
                        autocomplete="username"
                        required
                        autofocus
                        @error('email')
                            aria-invalid="true"
                            aria-describedby="email-error"
                        @enderror
                        @class([
                            'admin-auth-input',
                            'has-error' => $errors->has('email')
                        ])
                    >

                </div>

                @error('email')

                    <p id="email-error"
                       class="admin-auth-field-error"
                       role="alert">

                        {{ $message }}

                    </p>

                @enderror

            </div>


            {{-- Password --}}
            <div class="admin-auth-field">

                <label for="password">
                    Password
                </label>

                <div class="admin-auth-input-wrapper">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.6"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         class="admin-auth-input-icon"
                         aria-hidden="true">

                        <rect x="5" y="10"
                              width="14" height="11" rx="2"/>

                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>

                    </svg>

                    <input
                        id="password"
                        type="password"
                        :type="showPassword ? 'text' : 'password'"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                        @error('password')
                            aria-invalid="true"
                            aria-describedby="password-error"
                        @enderror
                        @class([
                            'admin-auth-input',
                            'admin-auth-password',
                            'has-error' => $errors->has('password')
                        ])
                    >

                    <button
                        type="button"
                        class="admin-password-toggle"
                        @click="showPassword = !showPassword"
                        :aria-pressed="showPassword.toString()"
                        aria-label="Show or hide password"
                    >

                        <span x-text="showPassword ? 'Hide' : 'Show'">
                            Show
                        </span>

                    </button>

                </div>

                @error('password')

                    <p id="password-error"
                       class="admin-auth-field-error"
                       role="alert">

                        {{ $message }}

                    </p>

                @enderror

            </div>


            {{-- Remember Me & Forgot Password --}}
            <div class="admin-auth-options">

                <label class="admin-remember-label"
                       for="remember_me">

                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                    >

                    <span>Remember me</span>

                </label>

                
@if(Route::has('admin.password.request'))

    <a
        href="{{ route('admin.password.request') }}"
        class="admin-forgot-password"
    >
        Forgot password?
    </a>

@endif


            </div>


            {{-- Submit Button --}}
            <button
                type="submit"
                class="admin-login-button"
                :disabled="submitting"
                :aria-busy="submitting.toString()"
            >

                <span x-show="!submitting">
                    Sign in to Admin
                </span>

                <span x-show="!submitting"
                      aria-hidden="true">
                    →
                </span>

                <span
                    x-show="submitting"
                    x-cloak
                    class="admin-login-loading"
                >

                    <span class="admin-auth-spinner"
                          aria-hidden="true"></span>

                    Signing in...

                </span>

            </button>

        </form>


        {{-- Footer --}}
        <div class="admin-login-footer">

            <a href="{{ route('home') }}"
               class="admin-back-website">

                <span aria-hidden="true">←</span>

                Back to website

            </a>

            <p>
                Authorized personnel only
            </p>

        </div>

    </div>

@endsection
