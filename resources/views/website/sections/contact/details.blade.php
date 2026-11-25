
<div>

    {{-- Section Heading --}}
    <h2 class="font-heading text-[30px] font-normal leading-tight text-navy sm:text-[32px]">
        Let’s plan something lovely.
    </h2>

    {{-- Contact Information --}}
    <p class="mt-5 text-[14px] leading-[1.8] text-[#42566e]">
        The villa’s verified email, international telephone
        number and WhatsApp number are awaiting confirmation.
        This preview does not send messages.
        Explore your stay preferences using the planning
        form below.
    </p>

    {{-- Booking Button --}}
    <button
        type="button"
        x-data
        @click="$dispatch('open-booking')"
        class="mt-6 inline-flex cursor-pointer items-center justify-center gap-4 bg-gold px-6 py-3.5 text-[13px] font-medium text-navy transition-colors hover:bg-dark-gold hover:text-white"
    >

        Plan your stay

        <svg
            class="h-4 w-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.3"
            aria-hidden="true"
        >
            <path d="M3 12h17M14 6l6 6-6 6"/>
        </svg>

    </button>

    {{-- WhatsApp Information --}}
    <p class="mt-7 text-[13px] leading-[1.8] text-[#42566e]">
        WhatsApp integration will be enabled once the
        property supplies a verified number.
    </p>

</div>
