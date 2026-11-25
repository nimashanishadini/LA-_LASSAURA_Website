
@props([
    'showDetailsLink' => true,
])

<div class="grid grid-cols-1 items-center gap-7 md:grid-cols-2 lg:gap-8">

    {{-- Accommodation Image --}}
    <div class="overflow-hidden">
        <img
            src="{{ asset('images/home/interior.jpg') }}"
            alt="Shared living space at La Lassaura"
            class="aspect-[1.46] w-full object-cover"
            loading="lazy"
        >
    </div>

    {{-- Accommodation Content --}}
    <div>

        <p class="text-[11px] font-medium uppercase tracking-[0.24em] text-dark-gold">
            Accommodation Preview
        </p>

        <h2 class="mt-3 font-heading text-[28px] font-normal leading-tight text-navy lg:text-[30px]">
    Your stay at La Lassaura 
</h2>

        <p class="mt-4 text-[14px] leading-[1.8] text-[#42566e]">
    Comfortable spaces, natural textures and a distinctly
    tropical setting. Explore the actual property before
    planning your stay.
</p>

        {{-- Room Information --}}
       <div class="mt-5 space-y-2 border-y border-brand-border/60 py-4 text-[13px] leading-relaxed text-[#42566e]">

            <p>Room name · To be confirmed</p>

            <p>Maximum occupancy &amp; bed configuration · To be confirmed</p>

            <p>Room amenities &amp; nightly rate (USD) · To be confirmed</p>

            <p>Photograph: shared living space; not a bedroom.</p>

        </div>

        {{-- Action Buttons --}}
        <div class="mt-6 flex flex-wrap items-center gap-6">

            {{-- Booking Modal --}}
            <button
                type="button"
                x-data
                @click="$dispatch('open-booking')"
                class="group inline-flex min-h-[44px] cursor-pointer items-center justify-center gap-4 bg-gold px-6 py-3 text-[13px] font-medium text-navy transition-colors duration-300 hover:bg-dark-gold hover:text-white"
            >
                Plan your stay

                <svg
                    class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.3"
                    aria-hidden="true"
                >
                    <path
                        d="M4 12h15m-6-6 6 6-6 6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

            </button>

            {{-- View Accommodation --}}
            @if ($showDetailsLink)

                <a
                    href="{{ route('rooms.show') }}"
                    class="group inline-flex min-h-[44px] items-center gap-5 border-b border-gold/70 py-3 text-[13px] font-medium text-navy transition-colors hover:text-dark-gold"
                >
                    View accommodation

                    <svg
                        class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.3"
                        aria-hidden="true"
                    >
                        <path
                            d="M4 12h15m-6-6 6 6-6 6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                </a>

            @endif

        </div>

    </div>

</div>
