import { bookingPopup } from './booking-popup';

export function accountPopup(config) {
    return {
        ...bookingPopup(config),
        loginMethod: 'code', loginCode: '', codeRequested: false, securityNotice: true, activating: false,
        openAccount() {
            if (this.$refs.dialog.open) return;
            this.step = 5; this.error = ''; this.notice = ''; this.authMode = 'login';
            this.loginMethod = 'code'; this.activating = false;
            this.$refs.dialog.showModal(); document.body.classList.add('overflow-hidden');
        },
        switchAccountMode(mode) {
            this.authMode = mode; this.error = ''; this.notice = ''; this.loginCode = '';
            this.password = ''; this.passwordConfirmation = ''; this.activating = false;
        },
        async useAnotherAccount(mode = 'login', method = 'code') {
            await this.perform(async () => {
                const data = await this.request(config.logout, {});
                this.user = null; this.accountChanged = true; this.csrf = data.csrf_token;
                this.setVerification(null); this.codeRequested = false; this.authEmail = ''; this.authName = '';
                document.querySelector('meta[name="csrf-token"]').content = this.csrf;
                document.querySelectorAll('input[name="_token"]').forEach(input => { input.value = this.csrf; });
                this.switchAccountMode(mode); this.loginMethod = method;
                this.$refs.dialog.scrollTop = 0;
            });
        },


async sendPasswordResetLink() {
    await this.perform(async () => {

        const data = await this.request(
            config.forgotPassword,
            {
                email: this.authEmail.trim().toLowerCase()
            }
        );

        this.notice = data.message;

    });
},

        async sendLoginCode() {
            await this.perform(async () => {
                const data = await this.request(config.sendLoginCode, { email: this.authEmail.trim().toLowerCase() });
                this.codeRequested = true; this.loginCode = ''; this.notice = data.message;
                this.resendAt = Date.now() + data.resend_after * 1000; this.clock = Date.now();
            });
        },
        async signInWithCode() {
            if (!this.codeRequested) { this.error = 'Use Send to request a sign-in code first.'; return; }
            await this.perform(async () => {
                const data = await this.request(config.verifyLoginCode, { email: this.authEmail.trim().toLowerCase(), code: this.loginCode });
                this.user = data.user; this.name = data.user.name; this.accountChanged = true;
                this.csrf = data.csrf_token; this.setVerification(data.verification); this.loginCode = '';
                document.querySelector('meta[name="csrf-token"]').content = this.csrf;
                document.querySelectorAll('input[name="_token"]').forEach(input => { input.value = this.csrf; });
                this.notice = 'You are signed in. Welcome to La Lassaura.';
            });
        },
    };
}
