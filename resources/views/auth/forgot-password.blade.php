
@extends('layouts.customer-auth')

@section('title', 'Forgot Password')

@section('heading', 'Forgot Password?')

@section('content')

<div x-data="{ submitting: false }"
     @pageshow.window="submitting = false">

    <p class="customer-centered-intro">
        Enter your registered email address and
        we'll send you a link to reset your password.
    </p>

    @if (session('status'))

        <div class="customer-centered-success" role="status">
            {{ session('status') }}
        </div>

    @endif

    <form method="POST"
          action="{{ route('password.email') }}"
          @submit="submitting = true">

        @csrf

        <div class="customer-centered-field">

            <label for="email">Email Address</label>

            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="Enter your email address"
                autocomplete="email"
                required
                autofocus
            >

            @error('email')

                <p class="customer-centered-error" role="alert">
                    {{ $message }}
                </p>

            @enderror

        </div>

        <button type="submit"
                class="customer-centered-primary"
                :disabled="submitting"
                x-text="submitting
                    ? 'Sending reset link...'
                    : 'Send Password Reset Link'">

            Send Password Reset Link

        </button>

    </form>

    <div class="customer-centered-help">

        If you don't receive the email, please
        check your spam or junk folder.

    </div>

    <a href="{{ route('login') }}"
       class="customer-centered-secondary">

        Back to Sign In

    </a>

</div>

@endsection
