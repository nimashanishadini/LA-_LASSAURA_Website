
<section
    id="availability-search"
    class="relative z-20 mx-auto -mt-9
       max-w-[1060px] px-5 sm:px-8"
    x-data="{
        checkIn: '',
        checkOut: '',
        adults: 2,
        children: 0,
        

        {{-- get today() {
            const date = new Date();
            return date.getFullYear() + '-' +
                String(date.getMonth() + 1).padStart(2, '0') + '-' +
                String(date.getDate()).padStart(2, '0');
        }, --}}

        today: '{{ now('Asia/Colombo')->toDateString() }}',

        get minCheckOut() {
            if (!this.checkIn) return this.today;

            const date = new Date(this.checkIn + 'T00:00:00');
            date.setDate(date.getDate() + 1);

            return date.getFullYear() + '-' +
                String(date.getMonth() + 1).padStart(2, '0') + '-' +
                String(date.getDate()).padStart(2, '0');
        }
    }"
>

    <form
       @submit.prevent="$dispatch('open-booking', {
    checkIn: checkIn,
    checkOut: checkOut,
    adults: adults,
    children: children
})"
        class="grid grid-cols-1 items-end gap-5
               border border-brand-border/60
               bg-[#faf8f3] p-6
               shadow-[0_15px_45px_rgba(16,38,61,0.05)]
               sm:grid-cols-2 lg:grid-cols-12
               lg:gap-5 lg:p-7"
    >

        {{-- Check-in --}}
        <div class="lg:col-span-2">

            <label
                for="check-in"
                class="mb-3 block text-[11px]
                       font-medium uppercase
                       tracking-[0.12em] text-[#52647a]"
            >
                Check-in
            </label>

            <input
                id="check-in"
                name="check_in"
                type="date"
                x-model="checkIn"
                :min="today"
                @change="if (checkOut && checkOut <= checkIn) checkOut = ''"
                required
                class="h-10 w-full border-0 border-b
                       border-brand-border bg-transparent
                       px-0 text-[13px] text-navy
                       focus:border-gold focus:ring-0"
            >

        </div>

        {{-- Check-out --}}
        <div class="lg:col-span-2">

            <label
                for="check-out"
                class="mb-3 block text-[11px]
                       font-medium uppercase
                       tracking-[0.12em] text-[#52647a]"
            >
                Check-out
            </label>

            <input
                id="check-out"
                name="check_out"
                type="date"
                x-model="checkOut"
                :min="minCheckOut"
                required
                class="h-10 w-full border-0 border-b
                       border-brand-border bg-transparent
                       px-0 text-[13px] text-navy
                       focus:border-gold focus:ring-0"
            >

        </div>

        {{-- Adults --}}
        <div class="lg:col-span-2">

            <label
                for="adults"
                class="mb-3 block text-[11px]
                       font-medium uppercase
                       tracking-[0.12em] text-[#52647a]"
            >
                Adults
            </label>

            <select
                id="adults"
                name="adults"
                x-model.number="adults"
                class="h-10 w-full border-0 border-b
                       border-brand-border bg-transparent
                       pl-1 pr-7 text-[13px] text-navy
                       focus:border-gold focus:ring-0"
            >
                @for ($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}">
                        {{ $i }}
                    </option>
                @endfor
            </select>

        </div>

        {{-- Children --}}
        <div class="lg:col-span-3">

            <label
                for="children"
                class="mb-3 block text-[11px]
                       font-medium uppercase
                       tracking-[0.12em] text-[#52647a]"
            >
                Children
            </label>

            <select
                id="children"
                name="children"
                x-model.number="children"
                class="h-10 w-full border-0 border-b
                       border-brand-border bg-transparent
                       pl-1 pr-7 text-[13px] text-navy
                       focus:border-gold focus:ring-0"
            >
                @for ($i = 0; $i <= 6; $i++)
                    <option value="{{ $i }}">
                        {{ $i }}
                    </option>
                @endfor
            </select>

        </div>

        {{-- Submit Button --}}
        <button
            type="submit"
            class="flex min-h-[50px] w-full
                   items-center justify-center gap-4
                   bg-gold px-4 py-3
                   text-[13px] font-semibold text-navy
                   transition-colors duration-300
                   hover:bg-dark-gold hover:text-white
                   sm:col-span-2 lg:col-span-3"
        >

            Check Availability

            <svg
                class="h-5 w-5 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                aria-hidden="true"
            >
                <path d="M3 12h17M14 6l6 6-6 6"/>
            </svg>

        </button>

    </form>

    {{-- Disclaimer --}}
    <p class="mt-3 text-center text-[11px]
              font-medium leading-relaxed text-[#52647a]">

        One room per request. Dates are reserved only after property approval.

    </p>

    {{-- Temporary Frontend Feedback --}}
    

</section>
