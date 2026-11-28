@props(['prefix' => 'popup'])
                <div x-show="step === 5" x-cloak>
                    <div x-show="!user">
                        <p class="body-copy mb-5">Sign in or create an account here to receive updates and manage your request.</p>
                        <div class="mb-6 flex gap-5 text-sm"><button type="button" @click="authMode='login'" :aria-pressed="authMode === 'login'" :class="authMode === 'login' ? 'border-b border-gold font-medium' : ''">Sign in</button><button type="button" @click="authMode='register'" :aria-pressed="authMode === 'register'" :class="authMode === 'register' ? 'border-b border-gold font-medium' : ''">Create account</button></div>
                        <form @submit.prevent="authenticate()" class="space-y-4">
                            <label x-show="authMode === 'register'" for="{{ $prefix }}-auth-name" class="block text-sm">Your name<input id="{{ $prefix }}-auth-name" x-model="authName" :required="authMode === 'register'" autocomplete="name" maxlength="255" class="mt-2 block w-full border-brand-border bg-transparent px-3 py-3 text-sm"></label>
                            <label for="{{ $prefix }}-auth-email" class="block text-sm">Email address<input id="{{ $prefix }}-auth-email" type="email" x-model="authEmail" autocomplete="username" maxlength="255" required class="mt-2 block w-full border-brand-border bg-transparent px-3 py-3 text-sm"></label>
                            <label for="{{ $prefix }}-auth-password" class="block text-sm">Password<input id="{{ $prefix }}-auth-password" type="password" x-model="password" :autocomplete="authMode === 'register' ? 'new-password' : 'current-password'" required class="mt-2 block w-full border-brand-border bg-transparent px-3 py-3 text-sm"></label>
                            <label x-show="authMode === 'register'" for="{{ $prefix }}-auth-confirm" class="block text-sm">Confirm password<input id="{{ $prefix }}-auth-confirm" type="password" x-model="passwordConfirmation" :required="authMode === 'register'" autocomplete="new-password" class="mt-2 block w-full border-brand-border bg-transparent px-3 py-3 text-sm"></label>
                            <div class="flex flex-wrap justify-between gap-4"><a href="{{ route('password.request') }}" class="py-3 text-xs underline">Forgot password?</a><button :disabled="busy" class="bg-gold px-7 py-3.5 text-sm disabled:opacity-40" x-text="authMode === 'register' ? 'Create account & continue →' : 'Sign in & continue →'"></button></div>
                        </form>
                    </div>
                </div>
                <section x-show="step === 5 && user && !user.email_verified" x-cloak class="space-y-4" aria-labelledby="{{ $prefix }}-otp-title">
                    <h3 id="{{ $prefix }}-otp-title" class="font-heading text-2xl">Verify your email</h3>
                    <p class="body-copy" x-text="'Enter the six-digit code for ' + (user?.email || '') + '. The code expires after 10 minutes.'"></p>
                    <p role="status" class="text-sm text-dark-gold" x-show="verification?.delivery === 'local'">Live email delivery is not configured yet. Contact the property to finish verification.</p>
                    <p role="status" class="text-sm text-red-700" x-show="verification?.delivery === 'failed'">The email could not be sent. Please retry later or contact the property.</p>
                    <form @submit.prevent="verifyEmailCode()" class="space-y-4">
                        <label for="{{ $prefix }}-email-code">Verification code<input id="{{ $prefix }}-email-code" type="text" x-model="verificationCode" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required></label>
                        <button :disabled="busy" class="bg-gold disabled:opacity-40">Verify email</button>
                    </form>
                    <button type="button" @click="resendEmailCode()" :disabled="busy || resendRemaining > 0" class="text-sm underline disabled:opacity-50" x-text="resendRemaining > 0 ? 'Resend available in ' + resendRemaining + 's' : verification?.delivery ? 'Resend code' : 'Send verification code'"></button>
                    <p class="text-xs text-[#42566e]">Email verification is required before submitting a booking. Your selected dates and room stay here.</p>
                </section>
