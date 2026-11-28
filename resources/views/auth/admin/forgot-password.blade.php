
@extends('layouts.admin-auth')

@section('title', 'Forgot Password')

@section('content')

<div class="admin-login"
     x-data="{ submitting: false }"
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

            <circle cx="8" cy="15" r="5"/>

            <path d="m11.5 11.5 8-8L22 6l-3 3-2-2-3 3"/>

        </svg>

    </div>


    {{-- Page Heading --}}
    <div class="admin-login-heading">

        <p class="admin-login-eyebrow">
            Admin Portal
        </p>

        <h1>Forgot password?</h1>

        <p class="admin-login-description">
            Enter your email address and we'll send
            you a secure link to reset your password.
        </p>

    </div>


    {{-- Success Message --}}
    @if(session('status'))

        <div class="admin-auth-success"
             role="status">

            {{ session('status') }}

        </div>

    @endif


    {{-- Reset Request Form --}}
    <form
        method="POST"
        action="{{ route('admin.password.email') }}"
        class="admin-login-form"
        @submit="submitting = true"
    >

        @csrf

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


        {{-- Submit Button --}}
        <button
            type="submit"
            class="admin-login-button"
            :disabled="submitting"
            :aria-busy="submitting.toString()"
        >

            <span x-show="!submitting">
                Send reset link
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

                Sending reset link...

            </span>

        </button>

    </form>


    {{-- Help Information --}}
    <div class="admin-auth-help">

        <svg xmlns="http://www.w3.org/2000/svg"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="1.6"
             aria-hidden="true">

            <circle cx="12" cy="12" r="9"/>

            <path d="M12 11v5M12 7h.01"/>

        </svg>

        <p>
            If you don't receive an email,
            check your spam folder or contact
            your system administrator.
        </p>

    </div>


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
