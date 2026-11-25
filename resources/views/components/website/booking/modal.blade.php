<div
    x-data="{
        open: false,
        step: 1,
        checkIn: '',
        checkOut: '',
        adults: 2,
        children: 0,
        rooms: 1,
        preference: 'recommendation',
        fullName: '',
        email: '',
        phone: '',
        specialRequests: '',
        accepted: false,
        error: '',
        today: '{{ now('Asia/Colombo')->toDateString() }}',

        get nights() {
            if (!this.checkIn || !this.checkOut) return 0;

            return Math.max(0,
                (Date.parse(this.checkOut) -
                 Date.parse(this.checkIn)) / 86400000
            );
        },

        get firstName() {
            return this.fullName.trim().split(/\s+/)[0];
        },

        openModal(data = {}) {
    if (this.$refs.dialog.open) return;

    this.step = 1;
    this.error = '';
    this.accepted = false;

    // Reset or populate booking preferences
    this.checkIn = data.checkIn ?? '';
    this.checkOut = data.checkOut ?? '';
    this.adults = Number(data.adults ?? 2);
    this.children = Number(data.children ?? 0);
    this.rooms = Number(data.rooms ?? 1);

    // Reset previous guest details
    this.preference = 'recommendation';
    this.fullName = '';
    this.email = '';
    this.phone = '';
    this.specialRequests = '';

    this.open = true;

    this.$nextTick(() => {
        this.$refs.dialog.showModal();
        this.$refs.dialog.scrollTop = 0;
        document.body.classList.add('overflow-hidden');
    });
},

        closeModal() {
            this.$refs.dialog.close();
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },

        goToStep(number) {
            this.error = '';
            this.step = number;

            this.$nextTick(() => {
                this.$refs.dialog.scrollTop = 0;
            });
        },

        nextStep() {
            this.error = '';

            if (!this.checkIn || !this.checkOut) {
                this.error = 'Please select both dates.';
                return;
            }

            if (this.checkIn < this.today) {
                this.error = 'Check-in cannot be in the past.';
                return;
            }

            if (this.checkOut <= this.checkIn) {
                this.error = 'Check-out must be after check-in.';
                return;
            }

            this.goToStep(2);
        },

        nextGuestStep() {
            if (!Number.isInteger(this.adults) ||
                this.adults < 1 || this.adults > 8 ||
                !Number.isInteger(this.children) ||
                this.children < 0 || this.children > 6 ||
                !Number.isInteger(this.rooms) ||
                this.rooms < 1 || this.rooms > 4) {

                this.error = 'Please select valid guest and room counts.';
                return;
            }

            this.goToStep(3);
        },

        nextDetailsStep() {
            if (!this.fullName.trim() ||
                !this.email.trim() ||
                !this.phone.trim()) {

                this.error = 'Please complete all required fields.';
                return;
            }

            if (!/^\+[1-9]\d{6,14}$/.test(
                this.phone.replace(/[\s()-]/g, '')
            )) {
                this.error = 'Please enter a valid international phone number.';
                return;
            }

            this.accepted = false;
            this.goToStep(6);
        },

        completePlan() {
            if (!this.accepted) {
                this.error = 'Please confirm before continuing.';
                return;
            }

            // TODO: Connect Laravel booking service.
            // No reservation or database submission yet.

            this.goToStep(7);
        }
    }"
   @open-booking.window="openModal($event.detail || {})"
>
    <dialog
        x-ref="dialog"
        @cancel.prevent="closeModal()"
        @click.self="closeModal()"
        aria-labelledby="booking-modal-title"
        class="fixed inset-0 m-auto
               w-[calc(100%-2rem)] max-w-[760px]
               max-h-[90dvh] overflow-y-auto
               border-0 bg-ivory p-0 text-navy
               backdrop:bg-[#071522]/80"
    >
        <div class="p-6 sm:p-8 md:p-10">

            {{-- Modal Header --}}
            <div class="mb-7 flex items-center justify-between gap-4">

                <h2
                    id="booking-modal-title"
                    class="font-heading text-[28px]
                           font-medium md:text-[32px]"
                    x-text="step === 7
                        ? 'Your escape, thoughtfully planned.'
                        : 'Plan your stay'"
                ></h2>

                <button
                    type="button"
                    @click="closeModal()"
                    aria-label="Close booking modal"
                    class="shrink-0 cursor-pointer p-2
                           hover:text-dark-gold"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.4"
                        aria-hidden="true"
                    >
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>

            </div>

            {{-- Development Notice --}}
            <div class="border border-gold/30
                        bg-[#eee7d7] px-4 py-4
                        text-[14px] leading-relaxed">
                Preview only — live availability, confirmed rates,
                reservations and payments are not connected yet.
                No booking will be submitted.
            </div>

            {{-- Progress Bar --}}
            <div class="my-6 grid grid-cols-7 gap-2">

                <template x-for="number in 7" :key="number">
                    <div
                        class="h-[3px] transition-colors duration-300"
                        :class="number <= step
                            ? 'bg-[#a77b30]'
                            : 'bg-[#dcd5c7]'"
                    ></div>
                </template>

            </div>

            {{-- STEP 01 - Select Dates --}}
            <div x-show="step === 1" x-cloak>

                <p class="mb-6 text-[11px] font-medium
                          uppercase tracking-[0.22em] text-dark-gold">
                    Step 1 of 7 · Select Dates
                </p>

                <form @submit.prevent="nextStep()">

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div>
                            <label for="booking-check-in"
                                   class="mb-2 block text-sm">
                                Check-in
                            </label>

                            <input
                                id="booking-check-in"
                                type="date"
                                x-model="checkIn"
                                :min="today"
                                required
                                class="w-full min-w-0 rounded-none
                                       border border-[#d5cec0]
                                       bg-transparent px-3 py-3
                                       text-sm focus:border-gold
                                       focus:ring-gold"
                            >
                        </div>

                        <div>
                            <label for="booking-check-out"
                                   class="mb-2 block text-sm">
                                Check-out
                            </label>

                            <input
                                id="booking-check-out"
                                type="date"
                                x-model="checkOut"
                                :min="checkIn || today"
                                required
                                class="w-full min-w-0 rounded-none
                                       border border-[#d5cec0]
                                       bg-transparent px-3 py-3
                                       text-sm focus:border-gold
                                       focus:ring-gold"
                            >
                        </div>

                    </div>

                    <p class="mt-5 text-sm leading-relaxed
                              text-[#42566e]">
                        Choose your preferred travel dates using
                        the date calendars.
                        Dates are preferences, not availability.
                    </p>

                    <div class="mt-6 flex justify-end">
                        <button
                            type="submit"
                            class="cursor-pointer bg-gold
                                   px-7 py-3.5 text-sm font-medium
                                   hover:bg-[#e0b66a]"
                        >
                            Continue <span class="ml-4">→</span>
                        </button>
                    </div>

                </form>
            </div>

            {{-- STEP 02 - Guests & Rooms --}}
            <div x-show="step === 2" x-cloak>

                <p class="mb-6 text-[11px] font-medium
                          uppercase tracking-[0.22em] text-dark-gold">
                    Step 2 of 7 · Guests & Rooms
                </p>

                <form @submit.prevent="nextGuestStep()">

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                        <div>
                            <label for="booking-adults"
                                   class="mb-2 block text-sm">
                                Adults
                            </label>

                            <select
                                id="booking-adults"
                                x-model.number="adults"
                                class="w-full rounded-none
                                       border border-[#d5cec0]
                                       bg-transparent px-4 py-3
                                       text-sm focus:border-gold
                                       focus:ring-gold"
                            >
                                @for ($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}">
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label for="booking-children"
                                   class="mb-2 block text-sm">
                                Children
                            </label>

                            <select
                                id="booking-children"
                                x-model.number="children"
                                class="w-full rounded-none
                                       border border-[#d5cec0]
                                       bg-transparent px-4 py-3
                                       text-sm focus:border-gold
                                       focus:ring-gold"
                            >
                                @for ($i = 0; $i <= 6; $i++)
                                    <option value="{{ $i }}">
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label for="booking-rooms"
                                   class="mb-2 block text-sm">
                                Rooms
                            </label>

                            <select
                                id="booking-rooms"
                                x-model.number="rooms"
                                class="w-full rounded-none
                                       border border-[#d5cec0]
                                       bg-transparent px-4 py-3
                                       text-sm focus:border-gold
                                       focus:ring-gold"
                            >
                                @for ($i = 1; $i <= 4; $i++)
                                    <option value="{{ $i }}">
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                    </div>

                    <p class="mt-5 text-sm leading-relaxed
                              text-[#42566e]">
                        Tell us how many guests will be joining
                        you and how many rooms you would prefer.
                    </p>

                    <div class="mt-7 flex items-center justify-between">

                        <button
                            type="button"
                            @click="goToStep(1)"
                            class="cursor-pointer border-b
                                   border-gold/40 pb-3 text-sm"
                        >
                            ← Back
                        </button>

                        <button
                            type="submit"
                            class="cursor-pointer bg-gold
                                   px-7 py-3.5 text-sm font-medium
                                   hover:bg-[#e0b66a]"
                        >
                            Continue <span class="ml-4">→</span>
                        </button>

                    </div>

                </form>
            </div>

            {{-- STEP 03 - Accommodation Preview --}}
            <div x-show="step === 3" x-cloak>

                <p class="mb-6 text-[11px] font-medium
                          uppercase tracking-[0.22em] text-dark-gold">
                    Step 3 of 7 · Accommodation Preview
                </p>

                {{-- TODO: Load available villas, images and rates
                     from the backend booking service. --}}

                <div class="border border-dashed border-[#d5cec0]
                            bg-[#eeeee5] px-6 py-10 text-center">

                    <p class="font-heading text-xl">
                        Accommodation Preview
                    </p>

                    <p class="mt-3 text-sm leading-relaxed
                              text-[#42566e]">
                        Available accommodation and rates will
                        be displayed once the booking system
                        is connected.
                    </p>

                </div>

                <h3 class="mt-5 font-heading text-[23px]">
                    La Lassaura accommodation
                </h3>

                <p class="mt-4 text-sm leading-relaxed
                          text-[#42566e]">
                    Room categories, capacities and rates
                    are awaiting confirmation.
                </p>

                <div class="mt-6 flex items-center justify-between">

                    <button
                        type="button"
                        @click="goToStep(2)"
                        class="cursor-pointer border-b
                               border-gold/40 pb-3 text-sm"
                    >
                        ← Back
                    </button>

                    <button
                        type="button"
                        @click="goToStep(4)"
                        class="cursor-pointer bg-gold
                               px-7 py-3.5 text-sm font-medium
                               hover:bg-[#e0b66a]"
                    >
                        Continue <span class="ml-4">→</span>
                    </button>

                </div>
            </div>

            {{-- STEP 04 - Select Your Preference --}}
            <div x-show="step === 4" x-cloak>

                <p class="mb-6 text-[11px] font-medium
                          uppercase tracking-[0.22em] text-dark-gold">
                    Step 4 of 7 · Select Your Preference
                </p>

                <label class="flex cursor-pointer items-start
                              gap-3 border border-[#d5cec0] p-5">

                    <input
                        type="radio"
                        name="accommodation-preference"
                        value="recommendation"
                        x-model="preference"
                        class="mt-1 h-4 w-4 text-navy
                               focus:ring-gold"
                    >

                    <div>
                        <p class="text-sm font-medium">
                            Request a suitable accommodation
                            recommendation
                        </p>

                        <p class="mt-2 text-sm leading-relaxed
                                  text-[#42566e]">
                            The villa will need to confirm a room
                            suited to your party. No room is held.
                        </p>
                    </div>

                </label>

                <div class="mt-6 flex items-center justify-between">

                    <button
                        type="button"
                        @click="goToStep(3)"
                        class="cursor-pointer border-b
                               border-gold/40 pb-3 text-sm"
                    >
                        ← Back
                    </button>

                    <button
                        type="button"
                        @click="goToStep(5)"
                        class="cursor-pointer bg-gold
                               px-7 py-3.5 text-sm font-medium
                               hover:bg-[#e0b66a]"
                    >
                        Continue <span class="ml-4">→</span>
                    </button>

                </div>
            </div>

            {{-- STEP 05 - Guest Details --}}
            <div x-show="step === 5" x-cloak>

                <p class="mb-6 text-[11px] font-medium
                          uppercase tracking-[0.22em] text-dark-gold">
                    Step 5 of 7 · Guest Details
                </p>

                <form @submit.prevent="nextDetailsStep()">

                    <div class="space-y-5">

                        <div>
                            <label for="booking-full-name"
                                   class="mb-2 block text-sm">
                                Full name
                            </label>

                            <input
                                id="booking-full-name"
                                type="text"
                                x-model="fullName"
                                autocomplete="name"
                                maxlength="120"
                                required
                                class="w-full rounded-none
                                       border border-[#d5cec0]
                                       bg-transparent px-3 py-3
                                       text-sm focus:border-gold
                                       focus:ring-gold"
                            >
                        </div>

                        <div>
                            <label for="booking-email"
                                   class="mb-2 block text-sm">
                                Email address
                            </label>

                            <input
                                id="booking-email"
                                type="email"
                                x-model="email"
                                autocomplete="email"
                                maxlength="254"
                                required
                                class="w-full rounded-none
                                       border border-[#d5cec0]
                                       bg-transparent px-3 py-3
                                       text-sm focus:border-gold
                                       focus:ring-gold"
                            >
                        </div>

                        <div>
                            <label for="booking-phone"
                                   class="mb-2 block text-sm">
                                International phone number
                            </label>

                            <input
                                id="booking-phone"
                                type="tel"
                                x-model="phone"
                                autocomplete="tel"
                                placeholder="+94 ..."
                                maxlength="30"
                                required
                                class="w-full rounded-none
                                       border border-[#d5cec0]
                                       bg-transparent px-3 py-3
                                       text-sm focus:border-gold
                                       focus:ring-gold"
                            >
                        </div>

                        <div>
                            <label for="booking-requests"
                                   class="mb-2 block text-sm">
                                Special requests (optional)
                            </label>

                            <textarea
                                id="booking-requests"
                                x-model="specialRequests"
                                rows="3"
                                maxlength="1000"
                                class="w-full rounded-none
                                       border border-[#d5cec0]
                                       bg-transparent px-3 py-3
                                       text-sm focus:border-gold
                                       focus:ring-gold"
                            ></textarea>
                        </div>

                    </div>

                    <p class="mt-4 text-xs leading-relaxed
                              text-[#42566e]">
                        Details are used for this preview only
                        and are not sent to the villa.
                    </p>

                    <div class="mt-6 flex items-center justify-between">

                        <button
                            type="button"
                            @click="goToStep(4)"
                            class="cursor-pointer border-b
                                   border-gold/40 pb-3 text-sm"
                        >
                            ← Back
                        </button>

                        <button
                            type="submit"
                            class="cursor-pointer bg-gold
                                   px-7 py-3.5 text-sm font-medium
                                   hover:bg-[#e0b66a]"
                        >
                            Continue <span class="ml-4">→</span>
                        </button>

                    </div>

                </form>
            </div>

            {{-- STEP 06 - Review Your Plan --}}
            <div x-show="step === 6" x-cloak>

                <p class="mb-6 text-[11px] font-medium
                          uppercase tracking-[0.22em] text-dark-gold">
                    Step 6 of 7 · Review Your Plan
                </p>

                <div class="space-y-3 border-y border-[#d5cec0]
                            py-5 text-sm leading-relaxed break-words">

                    <p x-text="checkIn + ' → ' + checkOut +
                               ' · ' + nights + ' nights'"></p>

                    <p x-text="adults + ' adults · ' +
                               children + ' children · ' +
                               rooms + ' requested rooms'"></p>

                    <p x-text="fullName + ' · ' + email"></p>

                    <p x-text="phone"></p>

                    <p x-text="'Requests: ' +
                               (specialRequests.trim() || 'None')"></p>

                    {{-- TODO: Display server-generated quote --}}
                    <p>Price per night: Awaiting confirmation</p>

                    <p class="font-medium">
                        Total price: Awaiting a confirmed quote
                    </p>

                </div>

                <p class="mt-5 text-sm leading-relaxed
                          text-[#42566e]">
                    Payment options and cancellation terms:
                    awaiting property confirmation.
                    No payment information is collected.
                </p>

                <form @submit.prevent="completePlan()">

                    <label class="mt-6 flex cursor-pointer
                                  items-start gap-3 text-sm">

                        <input
                            type="checkbox"
                            x-model="accepted"
                            required
                            class="mt-1 h-4 w-4 shrink-0
                                   rounded-sm border-[#d5cec0]
                                   text-navy focus:ring-gold"
                        >

                        <span>
                            I understand this is a stay plan
                            preview, not a confirmed booking.
                        </span>

                    </label>

                    <div class="mt-6 flex items-center
                                justify-between gap-4">

                        <button
                            type="button"
                            @click="goToStep(5)"
                            class="cursor-pointer border-b
                                   border-gold/40 pb-3 text-sm"
                        >
                            ← Back
                        </button>

                        <button
                            type="submit"
                            :disabled="!accepted"
                            class="cursor-pointer bg-gold
                                   px-6 py-3.5 text-sm font-medium
                                   hover:bg-[#e0b66a]
                                   disabled:cursor-not-allowed
                                   disabled:opacity-50"
                        >
                            Complete stay plan
                            <span class="ml-3">→</span>
                        </button>

                    </div>

                </form>
            </div>

            {{-- STEP 07 - Plan Complete --}}
            <div x-show="step === 7" x-cloak>

                <p class="mb-6 text-[11px] font-medium
                          uppercase tracking-[0.22em] text-dark-gold">
                    Step 7 of 7 · Plan Complete
                </p>

                <div class="flex h-12 w-12 items-center
                            justify-center rounded-full
                            bg-[#d9e8dc] text-2xl text-[#285c48]">
                    ✓
                </div>

                <h3 class="mt-5 font-heading text-[28px]">

                    Thank you, <span x-text="firstName"></span>.

                </h3>

                <p class="mt-4 text-sm leading-[1.9]
                          text-[#42566e]">
                    Your stay preferences are ready.
                    No booking has been made.
                    Please contact the villa through its
                    verified channels to confirm accommodation,
                    pricing and policies.
                </p>

                <p class="mt-5 text-sm font-medium">
                    Status: Preview only · Not reserved
                </p>

                <p class="mt-4 text-sm text-[#42566e]">
                    Your details have not been saved or submitted.
                </p>

                <div class="mt-6 flex justify-end">

                    <button
                        type="button"
                        @click="closeModal()"
                        class="cursor-pointer bg-gold
                               px-7 py-3.5 text-sm font-medium
                               hover:bg-[#e0b66a]"
                    >
                        Back to La Lassaura
                        <span class="ml-4">→</span>
                    </button>

                </div>
            </div>

            {{-- Shared Validation Error --}}
            <p
                x-show="error"
                x-text="error"
                x-cloak
                role="alert"
                class="mt-4 text-sm text-red-600"
            ></p>

        </div>
    </dialog>
</div>