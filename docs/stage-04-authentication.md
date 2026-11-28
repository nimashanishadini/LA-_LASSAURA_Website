# Stage 04.01 — authentication and account security

Updated 26 November 2026. Backend/UI implementation is tested; live SMTP delivery
and reviewer acceptance are pending. Do not tick full stage acceptance yet.
Stage 04.02 admin UI and 04.03 broader role/audit completion are separate work.

## Current implementation

| Requirement | Implementation | Evidence |
| --- | --- | --- |
| Customer registration | Customer role only, password hash, session regeneration, pending email OTP | EmailVerificationTest, AdminRoomTest |
| Staff authentication | Same session login; staff roles granted only from trusted console, no public role assignment | AdminRoomTest, AccountSecurityTest |
| Email verification | Six-digit random OTP; hash stored, bound to account/current email; old signed-link route removed | EmailVerificationTest |
| OTP expiry/abuse | 10-minute expiry; 5 incorrect attempts per active challenge, 60s resend cooldown, 5 sends/hour/account, IP route throttles | EmailVerificationTest |
| OTP resend/consumption | New code differs from and replaces old code; resend retains active failed attempts; successful verification clears hash | EmailVerificationTest |
| Delivery failures | Account remains unverified, failure logged without code/SMTP secrets; UI reports failure/local-only delivery | EmailVerificationTest |
| Booking access | Verified email checked on web/JSON booking routes and domain quote/submit methods | EmailVerificationTest, BookingWorkflowTest |
| Admin access | Verified email plus role gate; unverified staff must verify before admin actions | EmailVerificationTest, role tests |
| Password reset | Existing broker, expiring single-use reset token, rotated remember token; does not verify email | PasswordResetTest, AccountSecurityTest |
| Session security | Regenerate on login/register/verification; explicit idle timeout using configured lifetime; authenticate-session detects changed passwords | AccountSecurityTest, BookingPopupTest |
| Login throttling | 5 bad attempts per normalized email/IP then 60s cooldown; additional 20/minute route limit | AccountSecurityTest |
| Permission protection | Public registration/profile cannot set roles; reception/finance/housekeeping/editor cannot edit sensitive settings | AccountSecurityTest, AdminRoomTest |

Register endpoint is limited to 5/minute/IP, OTP verification/resend to 10/minute/IP,
forgot-password to 5/minute/IP, reset submission to 10/minute/IP. Existing password
broker also throttles reset emails. Limits are enforced server-side, not only UI.

## Popup and non-popup flow

The existing compact seven-step booking design is preserved. Guest Details step
contains inline account login/registration, then the OTP form for unverified
accounts. Verified customers continue to guest details and quote acceptance.
OTP responses do not contain codes or hashes. Verification regenerates the session;
the popup refreshes its CSRF token and other page form tokens before submitting.
Standalone /verify-email uses the same service for staff/non-popup login. Editing
an account email clears verified state, invalidates the old challenge and issues
a code for the new email. Existing accounts are never silently marked verified.

## Data and timestamps

email_otps stores one challenge per user: current email, hashed code, failed-attempt
count, UTC sent/expiry/consumption datetimes and delivery state. A transaction locks
the user/challenge; incorrect attempts commit before an error is returned. Correct
codes are single-use; successful retry on an already verified account is idempotent.
No raw codes are returned in APIs, flashed to session, or logged by error handlers.
The development log mailer necessarily contains the generated test email; never
use it as production email delivery. Account deletion cascades challenge removal.

## Session policy

Idle expiry uses SESSION_LIFETIME (currently 120 minutes locally). Expired popup
requests receive JSON 401 or CSRF 419 and must sign in/refresh; HTML requests go to
login. Normal authenticated activity updates the idle timer. Laravel's underlying
session storage lifetime also applies. Remember-me follows Laravel's persistent
login behavior when a session has already expired in storage; it is not MFA.
A password change/reset rejects an existing authenticated session on its next
request if it has the previous password hash. Production must configure HTTPS
and secure-cookie settings for its deployment; no production hosting was tested.

## SMTP setup and remaining acceptance

Owner selected Gmail SMTP and configured credentials privately in .env.
Sanitized runtime checks confirm SMTP, smtp.gmail.com:587, credentials present,
and sender matching the account. The live test failed with a connection timeout;
TCP probes to both ports 587 and 465 also timed out from the agent environment.
SMTP authentication and inbox delivery therefore remain unverified. Check delivery
from the local application/network before acceptance. SMTP timeout is 10 seconds. Do not paste
credentials into docs/chat or commit .env. OTP mail sends synchronously so it
does not depend on a queue worker; booking notifications remain queued.

- [x] Automated wrong/expired/reused/cross-account/old-link bypass checks.
- [x] Registration, password reset, idle expiry and role/security checks.
- [x] Popup registration → OTP → quote → pending booking tested in isolated Chrome.
- [x] OTP migration applied successfully on local MySQL/MariaDB.
- [x] Select SMTP provider and configure credentials.
- [ ] Verify a real inbox receives OTP and password-reset messages.
- [ ] Review production HTTPS/session configuration and approve timeout/limits.
- [ ] Developer/reviewer sign-off.

No live email or production credentials were used in automated/browser tests.


### Gmail SMTP selection

Owner selected Gmail SMTP. Host/port/scheme defaults now use smtp.gmail.com,
587 and smtp (STARTTLS negotiation), with La Lassaura sender name. MAIL_MAILER
was changed to smtp after the sender and App Password were configured privately.
Enable Google 2-Step Verification, generate an App Password, then set
MAIL_USERNAME and MAIL_FROM_ADDRESS to the same Gmail address, MAIL_PASSWORD
to that App Password, and MAIL_MAILER=smtp. Keep MAIL_URL unset so it does not
override these settings. Run php artisan config:clear; restart long-running
queue workers when mail configuration changes. Real delivery remains unverified.

### Public Sign In popup and customer email-code login

The header's Sign In button opens a standalone account dialog (also in the mobile
menu), using the supplied reference layout: email/Send, verification code, password
switch, Join Now, Activate My Account, and reservation-request access. Registration
still creates a customer with a password and requires email verification. Activation
uses the same customer sign-in-code flow and verifies email possession. Existing
booking authentication remains inside the seven-step booking popup.

POST /login/code sends a customer sign-in code; POST /login/code/verify consumes it.
These are guest-only, CSRF-protected session endpoints, throttled to 5 sends/minute/IP
and 10 verification submissions/minute/IP. Per email: 60-second resend cooldown,
5 sends/hour, 10-minute expiry, 5 wrong attempts per active challenge. Resends replace
the code without clearing active failed attempts. login_otps stores a hash only,
bound to the requesting browser session, current email and password fingerprint.
Successful consumption is locked/transactional, clears the hash, regenerates the
session and CSRF token, and verifies the customer's email if needed. Password changes
invalidate outstanding sign-in codes. Staff retain password login. Unknown/staff
emails receive the same generic send response; no account is created by sending.

Google/WeChat and other third-party providers are not configured or advertised.
The reservation link opens the existing owned-request popup after authentication;
no new booking page or public reference-number lookup was introduced.

EmailLoginTest covers OTP login, expiry, reuse, browser/password binding, attempts,
resend and generic responses. Isolated Chrome tests cover reference desktop/mobile
layout, registration/verification, bad-code rejection, code and password login and
activation entry. These use fake email delivery; Gmail inbox delivery remains pending.
