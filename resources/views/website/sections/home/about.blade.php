
<section
    id="about"
    class="bg-ivory py-16
           md:py-24 lg:py-28"
>

    <div class="mx-auto grid max-w-[1200px]
                grid-cols-1 items-center gap-12
                px-6 sm:px-8
                md:grid-cols-2 md:gap-16
                lg:gap-24">

        {{-- Left Side Image --}}
        <div class="relative mb-4 mr-4">

            <div class="aspect-[1.07/1] overflow-hidden">

                <img
                    src="{{ asset('images/home/about.jpg') }}"
                    alt="Warm brick archway leading into La Lassaura tropical villa"
                    loading="lazy"
                    class="h-full w-full object-cover
                           object-center"
                >

            </div>

            {{-- Floating Badge --}}
            <div class="absolute -bottom-5 -right-5
                        flex h-[112px] w-[112px]
                        flex-col items-center
                        justify-center gap-1.5
                        border-[7px] border-ivory
                        bg-navy text-[#e8c98d]
                        md:h-[126px] md:w-[126px]">

                {{-- Leaf Icon --}}
                <svg
                    class="mb-1 h-7 w-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M20 3c-7-1-15 2-15 9
                             a7 7 0 0 0 7 7
                             c7 0 9-9 8-16Z"/>
                    <path d="M3 21 15 9"/>
                </svg>

                <span class="text-[10px] uppercase
                             tracking-[0.15em]">
                    Simply
                </span>

                <span class="font-heading text-[21px]
                             italic leading-none">
                    serene.
                </span>

            </div>

        </div>

        {{-- Right Side Content --}}
        <div class="md:py-5">

            {{-- Section Label --}}
            <p class="mb-5 text-[11px] font-semibold
                      uppercase tracking-[0.22em]
                      text-dark-gold">

                A Place to Slow Down

            </p>

            {{-- Section Heading --}}
            <h2 class="font-heading font-medium
           text-[34px] sm:text-[40px]
           lg:text-[48px]
           leading-[1.15] text-navy">

    Step Into Your

    <span class="block italic">
        Tropical Escape
    </span>

</h2>

            {{-- Gold Divider --}}
            <div class="mb-7 mt-8 h-px w-14 bg-gold"></div>

            {{-- Description --}}
            <div class="space-y-5 text-[15px]
                        font-normal leading-[1.85]
                        text-[#42566e]">

                <p>
                    Beyond the warm brick archway,
                    a quieter world awaits. Welcome to
                    La Lassaura — a tropical hideaway
                    in Unawatuna, where thoughtful spaces
                    and the beauty of nature set the pace.
                </p>

                <p>
                    From sunlit balconies to peaceful
                    poolside evenings, settle in, feel at
                    home and let the everyday drift away.
                </p>

            </div>

            {{-- Discover Link --}}
            <a
                href="{{ url('/#about') }}"
                class="group mt-8 inline-flex items-center
                       gap-6 border-b border-gold/60
                       pb-3 text-[13px] font-medium
                       text-navy transition-colors
                       hover:border-dark-gold
                       hover:text-dark-gold"
            >

                Discover La Lassaura

                <svg
                    class="h-5 w-5 transition-transform
                           duration-300
                           group-hover:translate-x-1"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.2"
                    aria-hidden="true"
                >
                    <path d="M3 12h17M14 6l6 6-6 6"/>
                </svg>

            </a>

        </div>

    </div>

</section>
