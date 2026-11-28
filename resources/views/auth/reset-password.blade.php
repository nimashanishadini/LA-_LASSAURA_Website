
@extends('layouts.customer-auth')

@section('title', 'Reset Password')

@section('heading', 'Reset Password')

@section('content')

<div x-data="{
        showPassword: false,
        showConfirmation: false,
        submitting: false
    }"
    @pageshow.window="submitting = false">

    <p class="customer-centered-intro">

        Create a new password for your
        La Lassaura account.

    </p>

    <form method="POST"
          action="{{ route('password.store') }}"
          @submit="submitting = true">

        @csrf

        <input type="hidden"
               name="token"
               value="{{ $request->route('token') }}">

        @error('token')

            <p class="customer-centered-error" role="alert">
                {{ $message }}
            </p>

        @enderror

        <!-- Email Address -->

        <div class="customer-centered-field">

            <label for="email">Email Address</label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                autocomplete="username"
                placeholder="Enter your email address"
                required
            >

            @error('email')

                <p class="customer-centered-error" role="alert">
                    {{ $message }}
                </p>

            @enderror

        </div>

        <!-- New Password -->

        <div class="customer-centered-field">

            <label for="password">New Password</label>

            <div class="customer-centered-password">

                <input
                    id="password"
                    type="password"
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    placeholder="Enter new password"
                    autocomplete="new-password"
                    required
                >

                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    :aria-pressed="showPassword.toString()"
                    aria-label="Toggle new password visibility"
                    x-text="showPassword ? 'Hide' : 'Show'">

                    Show

                </button>

            </div>

            <p class="customer-centered-hint">
                Choose a strong, unique password.
            </p>

            @error('password')

                <p class="customer-centered-error" role="alert">
                    {{ $message }}
                </p>

            @enderror

        </div>

        <!-- Confirm Password -->

        <div class="customer-centered-field">

            <label for="password_confirmation">
                Confirm Password
            </label>

            <div class="customer-centered-password">

                <input
                    id="password_confirmation"
                    type="password"
                    :type="showConfirmation ? 'text' : 'password'"
                    name="password_confirmation"
                    placeholder="Confirm your password"
                    autocomplete="new-password"
                    required
                >

                <button
                    type="button"
                    @click="showConfirmation = !showConfirmation"
                    :aria-pressed="showConfirmation.toString()"
                    aria-label="Toggle confirmation password visibility"
                    x-text="showConfirmation ? 'Hide' : 'Show'">

                    Show

                </button>

            </div>

            @error('password_confirmation')

                <p class="customer-centered-error" role="alert">
                    {{ $message }}
                </p>

            @enderror

        </div>

        <button type="submit"
                class="customer-centered-primary"
                :disabled="submitting"
                x-text="submitting
                    ? 'Resetting password...'
                    : 'Reset Password'">

            Reset Password

        </button>

    </form>

    <a href="{{ route('login') }}"
       class="customer-centered-secondary">

        Back to Sign In

    </a>

</div>

@endsection
