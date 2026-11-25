
<section
    id="gallery"
    class="bg-ivory py-16 md:py-20 lg:py-24"
>

    <div class="mx-auto max-w-[1060px]
                px-6 sm:px-8">

        {{-- Section Header --}}
        <div class="mb-9 flex flex-col gap-5
                    md:mb-11 md:flex-row
                    md:items-end md:justify-between">

            <div>

                {{-- Section Label --}}
                <p class="mb-4 text-[11px]
                          font-semibold uppercase
                          tracking-[0.22em]
                          text-dark-gold">

                    Through Our Lens

                </p>

                {{-- Section Heading --}}
                <h2 class="font-heading text-[38px]
                           font-medium leading-[1.15]
                           text-navy sm:text-[44px]
                           lg:text-[46px]">

                    A glimpse of La Lassaura

                </h2>

            </div>

            {{-- View Gallery Link --}}
            <a
                href="{{ url('/#gallery-preview') }}"
                class="group inline-flex w-fit shrink-0
                       items-center gap-6 border-b
                       border-gold/50 pb-3
                       text-[13px] font-medium text-navy
                       transition-colors duration-300
                       hover:text-dark-gold"
            >

                View the gallery

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

            </a>

        </div>

        {{-- Gallery Images --}}
        <div
            id="gallery-preview"
            class="grid grid-cols-1 gap-5
                   md:grid-cols-[7fr_5fr]
                   md:gap-5"
        >

            {{-- Left Image --}}
            <figure class="group overflow-hidden
                           aspect-[1.6/1]
                           md:aspect-auto md:h-[355px]">

                <img
                    src="{{ asset('images/home/villa.jpg') }}"
                    alt="La Lassaura villa overlooking the swimming pool at dusk"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover
                           object-center
                           transition-transform
                           duration-700 ease-out
                           group-hover:scale-105"
                >

            </figure>

            {{-- Right Image --}}
            <figure class="group overflow-hidden
                           aspect-[1.6/1]
                           md:aspect-auto md:h-[355px]">

                <img
                    src="{{ asset('images/home/garden.jpg') }}"
                    alt="Peaceful tropical garden and outdoor seating at La Lassaura"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover
                           object-center
                           transition-transform
                           duration-700 ease-out
                           group-hover:scale-105"
                >

            </figure>

        </div>

    </div>

</section>
