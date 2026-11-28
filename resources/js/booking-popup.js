export function bookingPopup(config) {
    return {
        accountOnly: config.accountOnly || false, accountChanged: false,
        user: config.user, csrf: config.csrf, today: config.today,
        verification: config.verification, verificationCode: '', resendAt: Date.now() + (config.verification?.resend_after || 0)*1000, clock: Date.now(), timer: null,
        step: 1, mode: 'flow', busy: false, error: '', notice: '',
        checkIn: '', checkOut: '', adults: 2, children: 0, selected: '', rooms: [],
        name: config.user?.name || '', phone: '', specialRequests: '', accepted: false,
        authMode: 'login', authName: '', authEmail: '', password: '', passwordConfirmation: '',
        quote: null, result: null, revision: null, events: [], historyItems: [], nextHistory: null,
        resumeHistory: false, resumeReference: null,
        titles: ['Select dates', 'Guests & rooms', 'Accommodation preview', 'Select your preference', 'Guest details', 'Review your request', 'Your request'],
        formatFx(value) { return value ? new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Colombo', dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) + ' Sri Lanka time' : ''; },
        get nights() { return this.checkIn && this.checkOut ? Math.max(0, (Date.parse(this.checkOut) - Date.parse(this.checkIn)) / 86400000) : 0; },
        get resendRemaining() { return Math.max(0, Math.ceil((this.resendAt - this.clock)/1000)); },
        get selectedRoom() { return this.rooms.find(room => room.slug === this.selected); },
        get minCheckOut() {
            const date = new Date(`${this.checkIn || this.today}T00:00:00Z`);
            date.setUTCDate(date.getUTCDate() + 1);
            return date.toISOString().slice(0, 10);
        },
        get quoteExpired() { return !this.quote || Date.parse(this.quote.expires_at) <= Date.now(); },
        get summary() { return this.quote?.snapshot; },
        get terms() { return this.quote?.terms; },
        destroy() { if (this.timer) clearInterval(this.timer); },
        init() {
            this.timer = setInterval(() => { this.clock = Date.now(); }, 1000);
            if (this.accountOnly) return;
            const params = new URLSearchParams(window.location.search);
            if (params.get('booking') !== '1') return;
            this.openModal({ checkIn: params.get('check_in'), checkOut: params.get('check_out'), room: params.get('room'), adults: params.get('adults'), children: params.get('children') });
            if (params.get('reference')) this.loadResult(params.get('reference'));
            else if (params.get('quote')) this.restoreQuote(params.get('quote'));
            else if (params.get('history')) this.loadHistory();
        },
        openModal(data = {}) {
            if (this.$refs.dialog.open) return;
            this.step = 1; this.mode = 'flow'; this.error = ''; this.notice = '';
            this.checkIn = data.checkIn || ''; this.checkOut = data.checkOut || '';
            this.adults = Number(data.adults || 2); this.children = Number(data.children || 0);
            this.selected = data.room || ''; this.rooms = []; this.quote = null; this.result = null;
            this.phone = ''; this.specialRequests = ''; this.accepted = false;
            this.resumeHistory = false; this.resumeReference = null;
            this.$nextTick(() => { this.$refs.dialog.showModal(); document.body.classList.add('overflow-hidden'); });
        },
        openAccount() {
            if (this.$refs.dialog.open) return;
            this.step = 5; this.error = ''; this.notice = ''; this.authMode = 'login';
            this.$refs.dialog.showModal(); document.body.classList.add('overflow-hidden');
        },
        closeModal() {
            if (this.busy) return;
            this.$refs.dialog.close(); document.body.classList.remove('overflow-hidden');
            this.password = ''; this.passwordConfirmation = ''; this.verificationCode = '';
            if (this.accountOnly) { if (this.accountChanged) window.location.reload(); return; }
            const url = new URL(window.location.href);
            for (const key of ['booking', 'reference', 'quote', 'history']) url.searchParams.delete(key);
            window.history.replaceState({}, '', url);
        },
        go(step) { this.step = step; this.error = ''; this.notice = ''; this.$refs.dialog.scrollTop = 0; },
        async request(url, body = undefined) {
            const response = await fetch(url, {
                method: body === undefined ? 'GET' : 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                ...(body === undefined ? {} : { body: JSON.stringify(body) }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const error = new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'We could not complete this request. Please try again.');
                error.status = response.status; error.code = data.code; error.verification = data.verification; throw error;
            }
            return data;
        },
        async perform(task) {
            if (this.busy) return;
            this.busy = true; this.error = ''; this.notice = '';
            try { await task(); }
            catch (error) {
                this.error = error.status === 419 ? 'Your session expired. Refresh the page and sign in again.' : error.message;
                if (error.code === 'email_verification_required') {
                    if(this.user) this.user.email_verified = false;
                    if(error.verification) { this.setVerification(error.verification); if(this.user) this.user.email = error.verification.email; }
                    this.mode = 'flow'; this.step = 5;
                }
                if (error.status === 401) { this.user = null; this.mode = 'flow'; this.step = 5; }
            } finally { this.busy = false; }
        },
        async findRooms() {
            await this.perform(async () => {
                const url = new URL(config.availability, window.location.origin);
                url.search = new URLSearchParams({ check_in: this.checkIn, check_out: this.checkOut, adults: this.adults, children: this.children });
                const data = await this.request(url);
                this.rooms = data.data; this.quote = null; this.accepted = false;
                if (!this.rooms.some(room => room.slug === this.selected)) this.selected = this.rooms[0]?.slug || '';
                this.step = 3;
            });
        },
        async authenticate() {
            await this.perform(async () => {
                const registering = this.authMode === 'register';
                const data = await this.request(registering ? config.register : config.login, {
                    email: this.authEmail.trim().toLowerCase(), password: this.password,
                    ...(registering ? { name: this.authName, password_confirmation: this.passwordConfirmation } : {}),
                });
                this.user = data.user; this.name = data.user.name; this.csrf = data.csrf_token;
                document.querySelector('meta[name="csrf-token"]').content = this.csrf;
                document.querySelectorAll('input[name="_token"]').forEach(input => { input.value = this.csrf; });
                this.setVerification(data.verification);
                this.accountChanged = true;
                if (this.accountOnly) { this.step = 5; return; }
                if (!this.user.email_verified) { this.mode = 'flow'; this.step = 5; return; }
                if (this.resumeReference) await this.resultData(this.resumeReference);
                else if (this.resumeHistory) await this.historyData();
                else this.step = 5;
            });
            this.password = ''; this.passwordConfirmation = '';
        },
        setVerification(state) {
            this.verification = state || null;
            this.resendAt = Date.now() + (state?.resend_after || 0)*1000;
            this.clock = Date.now();
        },
        async verifyEmailCode() {
            await this.perform(async () => {
                const data = await this.request(config.verifyEmail, { code: this.verificationCode });
                this.user = data.user; this.setVerification(data.verification); this.verificationCode = '';
                this.csrf = data.csrf_token;
                document.querySelector('meta[name="csrf-token"]').content = this.csrf;
                document.querySelectorAll('input[name="_token"]').forEach(input => { input.value = this.csrf; });
                this.accountChanged = true;
                if (this.accountOnly) { this.notice = 'Your email is verified. Welcome to La Lassaura.'; return; }
                if (this.resumeReference) await this.resultData(this.resumeReference);
                else if (this.resumeHistory) await this.historyData();
                else { this.step = 5; this.notice = 'Your email is verified. Continue with your guest details.'; }
            });
        },
        async resendEmailCode() {
            await this.perform(async () => {
                const data = await this.request(config.resendEmail, {});
                this.setVerification(data.verification); this.verificationCode = '';
                this.notice = data.verification.delivery === 'sent' ? 'A new verification code has been sent.' : 'Email delivery is not available yet. Please contact the property.';
            });
        },
        async makeQuote() {
            await this.perform(async () => {
                const data = await this.request(config.quote, { room: this.selected, check_in: this.checkIn, check_out: this.checkOut, adults: this.adults, children: this.children });
                this.quote = data.data; this.accepted = false; this.step = 6;
            });
        },
        async restoreQuote(id) {
            await this.perform(async () => {
                const data = await this.request(config.quoteShow.replace('__quote__', encodeURIComponent(id)));
                this.quote = data.data; this.selected = this.quote.villa.slug;
                this.checkIn = this.quote.check_in.slice(0, 10); this.checkOut = this.quote.check_out.slice(0, 10);
                this.adults = this.quote.adults; this.children = this.quote.children; this.step = 6;
            });
        },
        async submitRequest() {
            if (!this.accepted || this.quoteExpired) { this.error = 'Review and accept a current quote before submitting.'; return; }
            await this.perform(async () => {
                const data = await this.request(config.submit, { quote_id: this.quote.id, name: this.name, phone: this.phone, special_requests: this.specialRequests, accepted_terms: true });
                this.resumeReference = data.data.reference;
                await this.resultData(data.data.reference);
            });
        },
        async resultData(reference) {
            const data = await this.request(config.show.replace('__reference__', encodeURIComponent(reference)));
            this.result = data.data.booking; this.revision = data.data.revision; this.events = data.data.events;
            this.step = 7; this.mode = 'flow'; this.accepted = false; this.resumeReference = reference;
        },
        async loadResult(reference) {
            this.resumeReference = reference;
            await this.perform(async () => { await this.resultData(reference); });
        },
        async historyData(url = config.history) {
            const data = await this.request(url);
            this.historyItems = data.data; this.nextHistory = data.next_page_url; this.mode = 'history';
        },
        async loadHistory(url = config.history) {
            this.resumeHistory = true; this.resumeReference = null;
            if (!this.user || !this.user.email_verified) { this.mode = 'flow'; this.step = 5; return; }
            await this.perform(async () => { await this.historyData(url); });
        },
        async acceptRevision() {
            if (!this.accepted) return;
            await this.perform(async () => {
                await this.request(config.accept.replace('__reference__', encodeURIComponent(this.result.reference)), { revision: this.revision, accepted_terms: true });
                await this.resultData(this.result.reference);
                this.notice = this.result.proposed_snapshot ? 'The price or terms changed again. Please review the latest update.' : 'Updated quote accepted. Your request is still awaiting property approval.';
            });
        },
        async cancelRequest() {
            if (!window.confirm('Cancel this pending request?')) return;
            await this.perform(async () => {
                await this.request(config.cancel.replace('__reference__', encodeURIComponent(this.result.reference)), {});
                await this.resultData(this.result.reference);
            });
        },
    };
}
