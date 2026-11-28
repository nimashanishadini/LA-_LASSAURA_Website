
@extends('layouts.admin-auth')

@section('title', 'Reset Password')

@section('content')

<div class="admin-login admin-reset-password"
     x-data="{
         showPassword: false,
         showConfirmation: false,
         submitting: false
     }"
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

            <rect x="5" y="10"
                  width="14" height="11" rx="2"/>

            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>

            <path d="M12 14v3"/>

        </svg>

    </div>


    {{-- Page Heading --}}
    <div class="admin-login-heading">

        <p class="admin-login-eyebrow">
            Admin Portal
        </p>

        <h1>Reset password</h1>

        <p class="admin-login-description">
            Create a new secure password
            for your management account.
        </p>

    </div>


    {{-- Reset Password Form --}}
    <form
        method="POST"
        action="{{ route('admin.password.store') }}"
        class="admin-login-form"
        @submit="submitting = true"
    >

        @csrf

        {{-- Password Reset Token --}}
        <input
            type="hidden"
            name="token"
            value="{{ $request->route('token') }}"
        >

        @error('token')

            <p class="admin-auth-field-error"
               role="alert">

                {{ $message }}

            </p>

        @enderror


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
                    value="{{ old('email', $request->email) }}"
                    placeholder="Enter your email"
                    autocomplete="username"
                    required
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


        {{-- New Password --}}
        <div class="admin-auth-field">

            <label for="password">
                New password
            </label>

            <div class="admin-auth-input-wrapper">

                <svg xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.6"
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
                    placeholder="Enter new password"
                    autocomplete="new-password"
                    required
                    aria-describedby="password-help{{ $errors->has('password') ? ' password-error' : '' }}"
                    @error('password')
                        aria-invalid="true"
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
                    aria-label="Show or hide new password"
                >

                    <span x-text="showPassword ? 'Hide' : 'Show'">
                        Show
                    </span>

                </button>

            </div>

            <p id="password-help"
               class="admin-password-hint">

                Use a strong, unique password.

            </p>

            @error('password')

                <p id="password-error"
                   class="admin-auth-field-error"
                   role="alert">

                    {{ $message }}

                </p>

            @enderror

        </div>


        {{-- Confirm Password --}}
        <div class="admin-auth-field">

            <label for="password_confirmation">
                Confirm password
            </label>

            <div class="admin-auth-input-wrapper">

                <svg xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.6"
                     class="admin-auth-input-icon"
                     aria-hidden="true">

                    <rect x="5" y="10"
                          width="14" height="11" rx="2"/>

                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>

                </svg>

                <input
                    id="password_confirmation"
                    type="password"
                    :type="showConfirmation ? 'text' : 'password'"
                    name="password_confirmation"
                    placeholder="Confirm new password"
                    autocomplete="new-password"
                    required
                    @error('password_confirmation')
                        aria-invalid="true"
                        aria-describedby="confirmation-error"
                    @enderror
                    @class([
                        'admin-auth-input',
                        'admin-auth-password',
                        'has-error' => $errors->has('password_confirmation')
                    ])
                >

                <button
                    type="button"
                    class="admin-password-toggle"
                    @click="showConfirmation = !showConfirmation"
                    :aria-pressed="showConfirmation.toString()"
                    aria-label="Show or hide confirmation password"
                >

                    <span x-text="showConfirmation ? 'Hide' : 'Show'">
                        Show
                    </span>

                </button>

            </div>

            @error('password_confirmation')

                <p id="confirmation-error"
                   class="admin-auth-field-error"
                   role="alert">

                    {{ $message }}

                </p>

            @enderror

        </div>


        {{-- Submit Button --}}
        <button
            type="submit"
            class="admin-login-button"
            :disabled="submitting"
            :aria-busy="submitting.toString()"
        >

            <span x-show="!submitting">
                Reset password
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

                Resetting password...

            </span>

        </button>

    </form>


    {{-- Footer --}}
    <div class="admin-login-footer">

        <a href="{{ route('admin.login') }}"
           class="admin-back-website">

            <span aria-hidden="true">←</span>

            Back to Admin Login

        </a>

        <p>Secure account recovery</p>

    </div>

</div>

@endsection
