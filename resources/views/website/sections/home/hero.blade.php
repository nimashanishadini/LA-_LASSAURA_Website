
<section
    id="hero"
    class="relative isolate flex min-h-[650px]
           items-center justify-center overflow-hidden
           bg-navy md:min-h-[calc(100svh-134px)]"
>

    {{-- Hero Background Image --}}
    <img
        src="{{ asset('images/home/hero.jpg') }}"
        alt="La Lassaura tropical villa and swimming pool in Unawatuna"
        class="absolute inset-0 -z-20 h-full w-full
               object-cover object-center"
        fetchpriority="high"
        loading="eager"
    >

    {{-- Dark Overlay --}}
    <div class="absolute inset-0 -z-10 bg-navy/45"></div>

    {{-- Gradient Overlay --}}
    <div class="absolute inset-0 -z-10
                bg-gradient-to-b from-navy/10
                via-transparent to-navy/30">
    </div>

    {{-- Main Hero Content --}}
    <div class="mx-auto w-full max-w-[1200px]
                px-6 pb-24 pt-20 text-center
                md:px-10 md:py-28">

        {{-- Location Label --}}
        <div class="mb-8 flex items-center
                    justify-center gap-3 md:mb-9">

            <span class="h-px w-7 bg-gold md:w-8"></span>

            <span class="text-[10px] font-medium uppercase
                         tracking-[0.25em] text-white
                         md:text-[12px]">

                Unawatuna, Sri Lanka

            </span>

            <span class="h-px w-7 bg-gold md:w-8"></span>

        </div>

        {{-- Hero Heading --}}
        <h1 class="font-heading text-[48px] font-medium
                   leading-[1.08] text-white
                   sm:text-[64px] md:text-[78px]
                   xl:text-[84px]">

            <span class="block">
                Your Private Escape
            </span>

            <span class="mt-2 block italic text-[#f1e4ca]">
                in Unawatuna
            </span>

        </h1>

        {{-- Description --}}
        <p class="mx-auto mt-7 max-w-[600px]
                  text-[15px] font-medium leading-relaxed
                  text-white md:mt-9 md:text-[16px]
                  md:leading-[1.8]">

            Discover a peaceful tropical retreat at La Lassaura,
            where comfort, privacy and unforgettable moments
            come together.

        </p>

        {{-- Action Buttons --}}
        <div class="mt-9 flex flex-col items-center
                    justify-center gap-4 sm:flex-row">

            {{-- Explore Rooms --}}
            <a href="{{ url('/#accommodation') }}"
               class="inline-flex min-h-[50px] w-full
                      max-w-[260px] items-center
                      justify-center gap-5 bg-gold
                      px-7 py-3 text-sm font-semibold
                      text-navy transition-all duration-300
                      hover:bg-[#d6ad60] sm:w-auto">

                Explore Our Rooms

                <svg class="h-5 w-5"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.5">
                    <path d="M3 12h17M14 6l6 6-6 6"/>
                </svg>

            </a>

            {{-- Book Your Stay --}}
            <button
    type="button"
    x-data
    @click="$dispatch('open-booking')"
    class="inline-flex min-h-[50px] w-full
           max-w-[260px] items-center
           justify-center gap-5 border
           border-white/40 bg-white/5
           px-7 py-3 text-sm font-semibold
           text-white cursor-pointer
           transition-all duration-300
           hover:border-white hover:bg-white/15
           sm:w-auto"
>

    Book Your Stay

    <svg class="h-5 w-5"
         viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="1.5"
         aria-hidden="true">
        <path d="M3 12h17M14 6l6 6-6 6"/>
    </svg>

</button>

        </div>

    </div>

    {{-- Bottom Captions --}}
   <div class="absolute bottom-16 left-0 right-0
            flex items-center justify-center
            px-6 text-white md:bottom-[64px]
            md:justify-between md:px-9">

        <div class="flex items-center gap-2">

            <svg class="h-5 w-5 shrink-0"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.5">

                <path d="M20 10c0 5-8 11-8 11S4 15 4 10
                         a8 8 0 1 1 16 0Z"/>
                <circle cx="12" cy="10" r="2.5"/>

            </svg>

            <span class="text-[9px] font-medium uppercase
                         tracking-[0.14em] md:text-[12px]">

                A Hidden Gem in Southern Sri Lanka

            </span>

        </div>

        <span class="hidden text-[12px] font-medium
                     uppercase tracking-[0.18em] md:block">

            Relax. Unwind. Stay.

        </span>

    </div>

</section>
