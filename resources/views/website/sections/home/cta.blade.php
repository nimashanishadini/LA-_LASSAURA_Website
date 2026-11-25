
<section
    id="booking-cta"
    class="bg-navy py-16
           md:py-20 lg:py-[76px]"
>

    <div class="mx-auto max-w-[1000px]
                px-6 text-center sm:px-8">

        {{-- Section Label --}}
        <p class="mb-5 text-[11px]
                  font-medium uppercase
                  tracking-[0.24em]
                  text-[#e8c98d]">

            Make Time for Yourself

        </p>

        {{-- Main Heading --}}
        <h2 class="font-heading text-[38px]
                   font-medium leading-[1.15]
                   text-ivory sm:text-[44px]
                   lg:text-[48px]">

            Your next chapter starts here.

        </h2>

        {{-- Description --}}
        <p class="mx-auto mt-5 max-w-xl
                  text-[15px] leading-relaxed
                  text-[#d0d9e3]">

            A warm welcome. A peaceful setting.
            A stay to look forward to.

        </p>

        {{-- Booking Button --}}
        
<button
    type="button"
    x-data
    @click="$dispatch('open-booking')"
    class="group mt-8 inline-flex
           min-h-[46px] items-center
           justify-center gap-5
           bg-gold px-7 py-3.5
           text-[13px] font-medium
           text-navy cursor-pointer
           transition-colors duration-300
           hover:bg-[#e0b66a]"
>

    Plan Your Escape

    <svg
        class="h-5 w-5 transition-transform
               duration-300
               group-hover:translate-x-1"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.3"
        aria-hidden="true"
    >
        <path d="M3 12h17M14 6l6 6-6 6"/>
    </svg>

</button>


    </div>

</section>
