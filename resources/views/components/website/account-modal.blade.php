@php
    $accountConfig = [
        'accountOnly' => true, 'logout' => route('logout'),
        'user' => auth()->check() ? array_merge(auth()->user()->only(['name', 'email']), ['email_verified' => auth()->user()->hasVerifiedEmail()]) : null,
        'verification' => auth()->check() && !auth()->user()->hasVerifiedEmail() ? app(\App\Services\EmailVerification::class)->state(auth()->user()) : null,
        'csrf' => csrf_token(), 'today' => now('Asia/Colombo')->toDateString(),
        'sendLoginCode' => route('login.code.send'), 'verifyLoginCode' => route('login.code.verify'),
        'login' => route('login'), 'register' => route('register'),
        'verifyEmail' => route('verification.verify'), 'resendEmail' => route('verification.send'),
        'forgotPassword' => route('password.email'),
    ];
@endphp
<div x-data="accountPopup(@js($accountConfig))" @open-account.window="openAccount()">
    <dialog x-ref="dialog" @cancel.prevent="closeModal()" @click.self="closeModal()" aria-labelledby="account-modal-title" class="account-dialog account-reference">
        <header class="account-heading">
           <h2
    id="account-modal-title"
    x-text="user
        ? (user.email_verified ? 'My Account' : 'Sign In')
        : (authMode === 'forgot'
            ? 'Forgot Password?'
            : (authMode === 'register'
                ? 'Join Now'
                : (activating ? 'Activate My Account' : 'Sign In')))"
></h2>
            <button type="button" @click="closeModal()" :disabled="busy" aria-label="Close account popup" class="account-close">×</button>
        </header>
        <div x-show="securityNotice" class="account-security">
            <span class="account-info" aria-hidden="true">i</span><span>Secure access to your La Lassaura account</span>
            <button type="button" @click="securityNotice = false" aria-label="Dismiss security notice">×</button>
        </div>
        <div class="account-main">
            <p x-show="error" x-text="error" role="alert" class="account-alert account-error"></p>
            <p x-show="notice" x-text="notice" role="status" class="account-alert"></p>

            <div x-show="!user && authMode === 'login'">
                <form @submit.prevent="loginMethod === 'code' ? signInWithCode() : authenticate()">
                    <label for="account-auth-email">Email Address</label>
                    <div class="account-email-row">
                       <input
                            id="account-auth-email"
                            type="email"
                            x-model="authEmail"
                            @input="codeRequested = false; loginCode = ''"
                            placeholder="Enter your email address"
                            autocomplete="username"
                            maxlength="255"
                            required
                        >
                        <button x-show="loginMethod === 'code'" type="button" @click="if ($el.form.querySelector('[type=email]').reportValidity()) sendLoginCode()" :disabled="busy || resendRemaining > 0" class="account-gold account-send" x-text="resendRemaining > 0 ? resendRemaining + 's' : 'Send'"></button>
                    </div>
                    <div x-show="loginMethod === 'code'" class="account-field">
                        <label for="account-login-code">Verification Code</label>
                        <input
                            id="account-login-code"
                            type="text"
                            x-model="loginCode"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            placeholder="000000"
                            pattern="[0-9]{6}"
                            minlength="6"
                            maxlength="6"
                            :required="loginMethod === 'code'"
                            :disabled="loginMethod !== 'code'"
                        >
                    </div>
                    <div x-show="loginMethod === 'password'" class="account-field">
                        <label for="account-auth-password">Password</label>
                       <input
                            id="account-auth-password"
                            type="password"
                            x-model="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            :required="loginMethod === 'password'"
                            :disabled="loginMethod !== 'password'"
                        >
                    </div>
                    <div class="account-help-links">
                       
                        <button
                            type="button"
                            @click="
                                loginMethod = loginMethod === 'code' ? 'password' : 'code';
                                error = '';
                                notice = '';
                            "
                            :disabled="busy"
                            x-text="loginMethod === 'code' ? 'Use password instead' : 'Use email code instead'"
                        >
                            Use password instead
                        </button>

                        <button x-show="loginMethod === 'code'" type="button" @click="notice = 'Check Inbox and Spam. Codes expire in 10 minutes. Use Send to request another code after the countdown. New customers should choose Join Now.'">Not received verification code?</button>
                       <button
    x-show="loginMethod === 'password'"
    type="button"
    @click="switchAccountMode('forgot')"
    :disabled="busy"
>
    Forgot password?
</button>
                    </div>
                    <button type="submit" :disabled="busy" class="account-gold account-primary" x-text="activating ? 'Activate My Account' : 'Sign In'"></button>
                </form>
                <div class="account-secondary-actions">
                    <button type="button" @click="switchAccountMode('register')">Join Now</button>
                    <button type="button" @click="activating = true; loginMethod = 'code'; error = ''; notice = 'Enter your registered email and request a code to verify and activate your customer account.'">Activate My Account</button>
                </div>
                <!-- Google Sign In -->

<div class="account-social-login">

    <div class="account-social-divider">
        <span>Other sign in options</span>
    </div>

    <a
        href="{{ route('google.redirect') }}"
        class="account-google-button"
        :aria-disabled="busy ? 'true' : 'false'"
        @click="if (busy) $event.preventDefault()"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 48 48"
            width="22"
            height="22"
            aria-hidden="true"
        >
            <path fill="#EA4335"
                d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/>

            <path fill="#4285F4"
                d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.23 5.48-4.74 7.18l7.73 6C44.44 38.04 46.98 31.88 46.98 24.55Z"/>

            <path fill="#FBBC05"
                d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.6.28-3.14.78-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.87.93 7.53 2.56 10.78Z"/>

            <path fill="#34A853"
                d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.15 1.45-4.93 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/>
        </svg>

        <span>Continue with Google</span>
    </a>

</div>
            </div>


<!-- CUSTOMER FORGOT PASSWORD -->

<div x-show="!user && authMode === 'forgot'" x-cloak>

    <p class="account-intro">
        Enter your registered email address and
        we'll send you a link to reset your password.
    </p>

    <form @submit.prevent="sendPasswordResetLink()">

        <label for="account-forgot-email">
            Email Address
        </label>

        <input
            id="account-forgot-email"
            type="email"
            x-model="authEmail"
            placeholder="Enter your email address"
            autocomplete="email"
            maxlength="255"
            :required="authMode === 'forgot'"
            :disabled="busy || authMode !== 'forgot'"
        >

        <button
            type="submit"
            class="account-gold account-primary"
            :disabled="busy"
            x-text="busy
                ? 'Sending reset link...'
                : 'Send Password Reset Link'"
        >
            Send Password Reset Link
        </button>

    </form>

    <div class="account-alert" style="margin-top: 22px;">
        If you don't receive the email, please
        check your spam or junk folder.
    </div>

    <div class="account-secondary-actions"
         style="grid-template-columns: 1fr;">

        <button
            type="button"
            @click="switchAccountMode('login'); loginMethod = 'password'"
            :disabled="busy"
        >
            Back to Sign In
        </button>

    </div>

</div>


            <div x-show="!user && authMode === 'register'" x-cloak>
                <p class="account-intro">Create your La Lassaura account. We’ll email you a code to verify your address.</p>
                <form @submit.prevent="authenticate()" class="account-register-form">
                    <label for="account-register-name">Full Name<input id="account-register-name" x-model="authName" :required="authMode === 'register'" autocomplete="name" maxlength="255"></label>
                    <label for="account-register-email">Email Address<input id="account-register-email" type="email" x-model="authEmail" :required="authMode === 'register'" autocomplete="email" maxlength="255"></label>
                    <label for="account-register-password">Password<input id="account-register-password" type="password" x-model="password" :required="authMode === 'register'" autocomplete="new-password"></label>
                    <label for="account-auth-confirm">Confirm Password<input id="account-auth-confirm" type="password" x-model="passwordConfirmation" :required="authMode === 'register'" autocomplete="new-password"></label>
                    <button :disabled="busy" class="account-gold account-primary">Create Account</button>
                </form>
                <button type="button" @click="switchAccountMode('login')" class="account-back">Already have an account? Sign In</button>
            </div>

            <section x-show="user && !user.email_verified" x-cloak>
                <form @submit.prevent="verifyEmailCode()">
                    <label for="account-verification-email">Email Address</label>
                    <div class="account-email-row">
                        <input id="account-verification-email" type="email" :value="user?.email || ''" readonly autocomplete="email">
                        <button type="button" @click="resendEmailCode()" :disabled="busy || resendRemaining > 0" class="account-gold account-send" x-text="resendRemaining > 0 ? resendRemaining + 's' : 'Send'"></button>
                    </div>
                    <div class="account-field">
                        <label for="account-email-code">Verification Code</label>
                        <input id="account-email-code" type="text" x-model="verificationCode" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
                    </div>
                    <div class="account-help-links">
                        <button type="button" @click="useAnotherAccount('login', 'password')" :disabled="busy">Change to use password</button>
                        <button type="button" @click="notice = 'Check Inbox and Spam. Use Send to request a new verification code after the countdown.'">Not received verification code?</button>
                    </div>
                    <p x-show="verification?.delivery === 'failed'" role="status" class="account-delivery-error">The email could not be sent. Please retry with Send.</p>
                    <p x-show="verification?.delivery === 'local'" role="status" class="account-delivery-error">Live email delivery is not configured. Please contact the property.</p>
                    <button :disabled="busy" class="account-gold account-primary">Sign In</button>
                </form>
                <div class="account-secondary-actions">
                    <button type="button" @click="useAnotherAccount('register')" :disabled="busy">Join Now</button>
                    <button type="button" @click="notice = 'Request a code with Send, then enter it above and choose Sign In to activate your account.'">Activate My Account</button>
                </div>
            </section>

            <div x-show="user && user.email_verified" x-cloak class="account-success">
                <p class="account-welcome" x-text="'Welcome, ' + (user?.name || '')"></p>
                <p x-text="user?.email"></p>
                <button type="button" @click="closeModal()" class="account-gold account-primary">Continue Exploring</button>
                @auth @if(auth()->user()->canAccessAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="account-back">Open admin dashboard</a>
                @endif @endauth
            </div>
            <div x-show="user" x-cloak class="account-signout">
                <button type="button" @click="useAnotherAccount()" :disabled="busy">Sign out / use another account</button>
            </div>
            <p x-show="busy" role="status" class="account-intro">Please wait…</p>
        </div>
        <div class="account-decoration" aria-hidden="true">
            <svg viewBox="0 0 700 150" preserveAspectRatio="xMidYMax slice"><defs><pattern id="account-leaf-veins" width="34" height="34" patternUnits="userSpaceOnUse" patternTransform="rotate(30)"><path d="M0 0H34V34H0Z M0 17H34 M17 0V34" fill="none" stroke="currentColor" stroke-width=".7"/></pattern></defs><path d="M90 160C100 18 240 0 350 30C440 -15 600 10 640 160Z" fill="url(#account-leaf-veins)"/><path d="M100 160Q255 60 350 30Q470 75 640 160M350 30V160" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            <span>LA LASSAURA · UNAWATUNA</span>
        </div>
        <a href="{{ route('bookings.index') }}" @click="if (!user || !user.email_verified) { $event.preventDefault(); switchAccountMode('login'); notice = 'Sign in to view your reservation requests.'; $refs.dialog.scrollTop = 0; }" class="account-reservations">View your reservation requests <span aria-hidden="true">›</span></a>
    </dialog>
</div>
