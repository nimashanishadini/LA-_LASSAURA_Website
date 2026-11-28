@php
    $bookingConfig = [
        'user' => auth()->check() ? array_merge(auth()->user()->only(['name','email']), ['email_verified'=>auth()->user()->hasVerifiedEmail()]) : null,
        'verification' => auth()->check() && !auth()->user()->hasVerifiedEmail() ? app(\App\Services\EmailVerification::class)->state(auth()->user()) : null,
        'verifyEmail' => route('verification.verify'), 'resendEmail' => route('verification.send'), 'csrf' => csrf_token(), 'today' => now('Asia/Colombo')->toDateString(),
        'availability' => route('availability'), 'login' => route('login'), 'register' => route('register'),
        'quote' => route('bookings.quote'), 'submit' => route('bookings.store'), 'history' => route('bookings.index'),
        'quoteShow' => route('bookings.quote.show', '__quote__'), 'show' => route('bookings.show', '__reference__'),
        'accept' => route('bookings.accept', '__reference__'), 'cancel' => route('bookings.cancel', '__reference__'),
    ];
@endphp
<div x-data="bookingPopup(@js($bookingConfig))" @open-booking.window="openModal($event.detail || {})">
    <dialog x-ref="dialog" @cancel.prevent="closeModal()" @click.self="closeModal()" aria-labelledby="booking-popup-title"
        class="fixed inset-0 m-auto max-h-[85dvh] w-[calc(100%-2rem)] max-w-[680px] overflow-y-auto border-0 booking-design booking-compact bg-ivory p-0 text-navy shadow-2xl backdrop:bg-[#071726]/75">
        <div class="booking-content">
            <div class="flex items-center justify-between gap-5">
                <h2 id="booking-popup-title" class="font-heading text-[26px] leading-tight sm:text-[30px]" x-text="mode === 'history' ? 'Your requests' : 'Plan your stay'"></h2>
                <button type="button" aria-label="Close booking popup" @click="closeModal()" :disabled="busy" class="flex h-10 w-10 shrink-0 items-center justify-center text-[28px] font-light disabled:opacity-40">×</button>
            </div>
            <div class="booking-notice">
                Choose your room and review current rates. Your request is confirmed only after approval by the property. No payment is collected here.
            </div>
            <div x-show="mode === 'flow'" class="booking-progress" role="progressbar" aria-label="Booking progress" aria-valuemin="1" aria-valuemax="7" :aria-valuenow="step">
                <template x-for="part in 7" :key="part"><span :class="part <= step ? 'bg-[#a17c36]' : 'bg-[#ded9cd]'" class="h-[3px]"></span></template>
            </div>
            <div class="booking-step-heading">
                <p class="text-[12px] font-semibold uppercase tracking-[0.28em] text-dark-gold sm:text-[14px]" x-show="mode === 'flow'" x-text="'Step ' + step + ' of 7 · ' + titles[step-1]"></p>
                <button type="button" @click="loadHistory()" :disabled="busy" class="booking-history-link">Your requests</button>
            </div>
            <p role="alert" x-show="error" x-text="error" x-cloak class="mb-5 border border-red-200 bg-red-50 p-4 text-sm text-red-800"></p>
            <p role="status" x-show="notice" x-text="notice" x-cloak class="mb-5 border border-brand-border bg-stone p-4 text-sm"></p>
            <p role="status" x-show="busy" x-cloak class="mb-4 text-sm text-[#42566e]">Please wait…</p>
            <div x-show="mode === 'flow'">
                <form x-show="step === 1" @submit.prevent="go(2)" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm" for="popup-check-in">Check-in<input id="popup-check-in" type="date" x-model="checkIn" :min="today" @change="if(checkOut && checkOut <= checkIn) checkOut = ''" required class="mt-2 block w-full border-brand-border bg-transparent px-3 py-3 text-sm focus:border-gold focus:ring-gold"></label>
                        <label class="block text-sm" for="popup-check-out">Check-out<input id="popup-check-out" type="date" x-model="checkOut" :min="minCheckOut" required class="mt-2 block w-full border-brand-border bg-transparent px-3 py-3 text-sm focus:border-gold focus:ring-gold"></label>
                    </div>
                    <p class="booking-help" x-text="(nights ? nights + ' nights · ' : '') + 'Choose your preferred dates. Availability is checked in the next step.'"></p>
                    <div class="booking-actions justify-end"><button class="bg-gold px-7 py-3.5 text-sm font-medium">Continue <span aria-hidden="true">⟶</span></button></div>
                </form>
                <form x-show="step === 2" x-cloak @submit.prevent="findRooms()" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <label for="popup-adults">Adults<select id="popup-adults" x-model.number="adults" required><template x-for="count in 100" :key="count"><option :value="count" x-text="count"></option></template></select></label>
                        <label for="popup-children">Children<select id="popup-children" x-model.number="children" required><template x-for="count in 101" :key="count"><option :value="count - 1" x-text="count - 1"></option></template></select></label>
                        <label for="popup-room-count">Rooms<select id="popup-room-count" disabled aria-describedby="popup-room-help"><option>1</option></select></label>
                    </div>
                    <p id="popup-room-help" class="booking-help">One room per request. Guest counts are checked against each available room's capacity.</p>
                    <div class="booking-actions"><button type="button" @click="go(1)" class="booking-back">← Back</button><button :disabled="busy" class="bg-gold px-7 py-3.5 text-sm font-medium disabled:opacity-40">Continue <span aria-hidden="true">⟶</span></button></div>
                </form>
                <div x-show="step === 3" x-cloak>
                    <img src="{{ asset('images/home/villa.jpg') }}" alt="La Lassaura accommodation beside the pool" class="booking-preview-image" loading="lazy">
                    <h3 class="mt-7 font-heading text-[30px] leading-tight">La Lassaura accommodation</h3>
                    <p class="booking-help">A quiet escape in Unawatuna. Explore our setting, then choose an available room suited to your stay.</p>
                    <p class="mt-6" x-text="rooms.length ? rooms.length + ' room' + (rooms.length === 1 ? '' : 's') + ' available for your selected dates. Your price is calculated at review.' : 'No rooms fit these dates and guest counts. Please try different dates.'"></p>
                    <div class="booking-actions"><button type="button" @click="go(2)" class="booking-back">← Back</button><button type="button" @click="go(4)" :disabled="!rooms.length || busy" class="bg-gold px-7 py-3.5 text-sm disabled:opacity-40">Continue <span aria-hidden="true">⟶</span></button></div>
                </div>
                <div x-show="step === 4" x-cloak>
                    <div class="space-y-4"><template x-for="room in rooms" :key="room.id"><label class="booking-preference" :class="selected === room.slug ? 'border-gold' : 'border-brand-border'">
                        <input type="radio" name="popup-room-preference" :value="room.slug" x-model="selected" class="mt-1 h-5 w-5 shrink-0 border-navy text-navy focus:ring-gold">
                        <span><span class="block" x-text="room.name + ' · ' + room.category + ' room'"></span><span class="booking-help mt-2 block" x-text="'Up to ' + room.max_guests + ' guests. No room is held until the property confirms your request.'"></span></span>
                    </label></template></div>
                    <div class="booking-actions"><button type="button" @click="go(3)" class="booking-back">← Back</button><button type="button" @click="go(5)" :disabled="!selected || busy" class="bg-gold px-7 py-3.5 text-sm disabled:opacity-40">Continue <span aria-hidden="true">⟶</span></button></div>
                </div>
                <x-website.account-fields />
                <form x-show="step === 5 && user && user.email_verified" x-cloak @submit.prevent="makeQuote()" class="space-y-4">
                    <label for="popup-guest-name" class="block text-sm">Full name<input id="popup-guest-name" x-model="name" autocomplete="name" maxlength="120" required class="mt-2 block w-full border-brand-border bg-transparent px-3 py-3 text-sm"></label>
                    <label for="popup-guest-email">Email address<input id="popup-guest-email" type="email" :value="user?.email || ''" readonly autocomplete="email"><span class="mt-2 block text-xs text-[#42566e]">Linked to your signed-in account.</span></label>
                    <label for="popup-phone" class="block text-sm">International phone number<input id="popup-phone" type="tel" x-model="phone" autocomplete="tel" placeholder="+94…" maxlength="30" required class="mt-2 block w-full border-brand-border bg-transparent px-3 py-3 text-sm"></label>
                    <label for="popup-requests" class="block text-sm">Special requests (optional)<textarea id="popup-requests" x-model="specialRequests" rows="3" maxlength="1000" class="mt-2 block w-full border-brand-border bg-transparent px-3 py-3 text-sm"></textarea></label>
                    <p class="booking-help">Your details will be sent to the property when you submit your booking request.</p><div class="booking-actions"><button type="button" @click="go(4)" class="booking-back">← Back</button><button :disabled="busy" class="bg-gold px-7 py-3.5 text-sm disabled:opacity-40">Continue <span aria-hidden="true">⟶</span></button></div>
                </form>
                <div x-show="step === 6" x-cloak>
                    <h3 class="font-heading text-2xl" x-text="selectedRoom?.name || quote?.villa?.name || selected"></h3><p class="mt-3 text-sm" x-text="checkIn + ' → ' + checkOut + ' · ' + adults + ' adults · ' + children + ' children'"></p>
                    @include('components.website.booking.price', ['source'=>'summary','termsSource'=>'terms'])
                    <p class="mt-4 text-xs text-[#42566e]">Submitting sends a pending request. No payment is collected and no room is held until approval.</p>
                    <p x-show="quoteExpired" class="mt-3 text-sm text-red-700">This quote has expired. Go back and refresh the price.</p>
                    <form @submit.prevent="submitRequest()"><label class="mt-5 flex items-start gap-3 text-sm"><input type="checkbox" x-model="accepted" required class="mt-1 text-dark-gold"><span>I accept the displayed price and property terms and understand this request needs approval.</span></label><div class="mt-6 flex justify-between gap-4"><button type="button" @click="go(5)" class="text-sm">← Back / refresh quote</button><button :disabled="busy || !accepted || quoteExpired" class="bg-gold px-6 py-3.5 text-sm disabled:opacity-40">Submit request →</button></div></form>
                </div>
                <div x-show="step === 7" x-cloak>
                    <p class="text-xs font-medium uppercase tracking-wider text-dark-gold" x-text="result?.status"></p><h3 class="mt-3 font-heading text-3xl" x-text="result?.villa?.name"></h3>
                    <p class="mt-3 break-all text-xs text-[#42566e]" x-text="'Reference: ' + (result?.reference || '')"></p><p class="mt-3 text-sm" x-text="(result?.check_in || '').slice(0,10) + ' → ' + (result?.check_out || '').slice(0,10)"></p>
                    <p class="body-copy mt-4" x-text="result?.status === 'confirmed' ? 'Your stay is confirmed. These nights are reserved for you.' : result?.status === 'pending' ? 'Your request is awaiting approval. These dates are not reserved yet.' : 'Your request is ' + (result?.status || '') + '.'"></p>
                    <p x-show="result?.rejection_reason" class="mt-4 text-sm" x-text="result?.rejection_reason"></p>
                    @include('components.website.booking.price', ['source'=>'result?.quote_snapshot','termsSource'=>'result?.terms_snapshot'])
                    <div x-show="result?.status === 'pending' && result?.proposed_snapshot" class="mt-6 border border-gold bg-gold/5 p-5"><h4 class="font-heading text-2xl">Please review this updated quote</h4><p class="body-copy mt-3">The price or terms changed. Acceptance still requires property approval.</p>
                        @include('components.website.booking.price', ['source'=>'result?.proposed_snapshot','termsSource'=>'result?.proposed_terms'])
                        <form @submit.prevent="acceptRevision()"><label class="mt-4 flex items-start gap-3 text-sm"><input type="checkbox" x-model="accepted" required class="mt-1"><span>I accept the revised price and terms above.</span></label><button :disabled="busy || !accepted" class="mt-4 bg-gold px-5 py-3 text-sm disabled:opacity-40">Accept revised quote</button></form>
                    </div>
                    <details class="mt-6 text-sm"><summary class="cursor-pointer">Request history</summary><ul class="mt-3 space-y-2"><template x-for="event in events" :key="event.id"><li x-text="event.action.replaceAll('_', ' ') + ' · ' + formatFx(event.created_at)"></li></template></ul></details>
                    <div class="mt-6 flex flex-wrap gap-5"><button type="button" @click="loadResult(result.reference)" :disabled="busy" class="border-b border-gold pb-2 text-sm">Refresh status</button><button type="button" x-show="result?.status === 'pending'" @click="cancelRequest()" :disabled="busy" class="pb-2 text-sm text-red-700">Cancel pending request</button><a x-show="result?.status === 'confirmed'" href="{{ route('contact.index') }}" class="pb-2 text-sm underline">Contact us about changes</a></div>
                </div>
            </div>
            <div x-show="mode === 'history'" x-cloak>
                <p class="body-copy mb-5">Your requests and confirmed stays, all in one place.</p>
                <template x-for="booking in historyItems" :key="booking.reference"><button type="button" @click="loadResult(booking.reference)" :disabled="busy" class="mb-3 block w-full border border-brand-border p-4 text-left"><span class="flex flex-wrap justify-between gap-2"><span class="font-heading text-2xl" x-text="booking.villa?.name"></span><span class="text-xs uppercase text-dark-gold" x-text="booking.status"></span></span><span class="mt-2 block text-xs" x-text="booking.check_in.slice(0,10) + ' → ' + booking.check_out.slice(0,10)"></span><span x-show="booking.proposed_snapshot" class="mt-2 block text-xs text-dark-gold">Updated quote needs your acceptance</span></button></template>
                <p x-show="!historyItems.length" class="border border-brand-border bg-stone p-5 text-sm">You have no booking requests yet.</p>
                <div class="mt-5 flex flex-wrap justify-between gap-4"><button type="button" @click="mode='flow'; go(1)" class="bg-gold px-6 py-3 text-sm">Plan a stay →</button><button type="button" x-show="nextHistory" @click="loadHistory(nextHistory)" :disabled="busy" class="border-b border-gold text-sm">Older requests →</button></div>
            </div>
        </div>
    </dialog>
</div>
