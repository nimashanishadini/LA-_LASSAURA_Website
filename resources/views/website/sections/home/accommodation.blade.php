
<section
    id="accommodation"
    class="bg-ivory py-16 md:py-20 lg:py-24"
>

    <div class="mx-auto max-w-[1060px]
                px-6 sm:px-8">

        {{-- Section Header --}}
        <div class="mb-10 flex flex-col gap-5
                    md:mb-11 md:flex-row
                    md:items-end md:justify-between">

            <div>

                <p class="mb-4 text-[11px] font-semibold
                          uppercase tracking-[0.22em]
                          text-dark-gold">

                    Stay a Little Longer

                </p>

                <h2 class="font-heading text-[38px]
                           font-medium leading-[1.15]
                           text-navy sm:text-[44px]
                           lg:text-[46px]">

                    Your own little sanctuary

                </h2>

            </div>

            {{-- Explore Accommodation --}}
            <a href="{{ route('rooms.index') }}"
               class="group inline-flex w-fit shrink-0
                      items-center gap-5 border-b
                      border-gold/50 pb-3
                      text-[13px] font-medium text-navy
                      transition-colors hover:text-dark-gold">

                Explore accommodation

                <svg class="h-5 w-5 transition-transform
                            duration-300
                            group-hover:translate-x-1"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.3"
                     aria-hidden="true">

                    <path d="M3 12h17M14 6l6 6-6 6"/>

                </svg>

            </a>

        </div>

        {{-- Accommodation Content --}}
        <div
            id="accommodation-preview"
            class="grid grid-cols-1 items-center
                   gap-8 md:grid-cols-2 md:gap-8"
        >

            {{-- Left Side Image --}}
            <div class="overflow-hidden">

                <img
                    src="{{ asset('images/home/interior.jpg') }}"
                    alt="Shared living space at La Lassaura with sofa, television and kitchen"
                    loading="lazy"
                    class="aspect-[1.46/1] w-full
                           object-cover object-center
                           transition-transform duration-700
                           hover:scale-[1.03]"
                >

            </div>

            {{-- Right Side Content --}}
            <div>

                {{-- Label --}}
                <p class="mb-3 text-[11px] font-semibold
                          uppercase tracking-[0.20em]
                          text-dark-gold">

                    Accommodation Preview

                </p>

                {{-- Heading --}}
                <h3 class="font-heading text-[30px]
                           font-medium leading-tight
                           text-navy lg:text-[34px]">

                    Your stay at La Lassaura

                </h3>

                {{-- Description --}}
                <p class="mt-4 text-[14px]
                          leading-[1.85] text-[#42566e]">

                    Comfortable spaces, natural textures
                    and a distinctly tropical setting.
                    Explore the actual property before
                    planning your stay.

                </p>

                {{-- Accommodation Details --}}
                <div class="my-5 space-y-2.5
                            border-y border-brand-border/80
                            py-4 text-[13px]
                            leading-relaxed text-[#42566e]">

                    <p>
                        Room name · To be confirmed
                    </p>

                    <p>
                        Maximum occupancy &amp; bed configuration
                        · To be confirmed
                    </p>

                    <p>
                        Room amenities &amp; nightly rate (USD)
                        · To be confirmed
                    </p>

                    <p>
                        Photograph: shared living space;
                        not a bedroom.
                    </p>

                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-5">

                    {{-- Plan Your Stay --}}
                    <button
    type="button"
    x-data
    @click="$dispatch('open-booking')"
    class="group inline-flex min-h-[44px]
           items-center justify-center gap-4
           bg-gold px-6 py-3
           text-[13px] font-medium text-navy
           cursor-pointer transition-colors duration-300
           hover:bg-dark-gold hover:text-white"
>

    Plan your stay

    <svg class="h-5 w-5
                transition-transform
                duration-300
                group-hover:translate-x-1"
         viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="1.3"
         aria-hidden="true">

        <path d="M3 12h17M14 6l6 6-6 6"/>

    </svg>

</button>

                    {{-- View Accommodation --}}
                    <a href="{{ route('rooms.index') }}"
                       class="group inline-flex min-h-[44px]
                              items-center gap-5 border-b
                              border-gold/50 py-3
                              text-[13px] font-medium
                              text-navy transition-colors
                              hover:text-dark-gold">

                        View accommodation

                        <svg class="h-5 w-5
                                    transition-transform
                                    duration-300
                                    group-hover:translate-x-1"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.3"
                             aria-hidden="true">

                            <path d="M3 12h17M14 6l6 6-6 6"/>

                        </svg>

                    </a>

                </div>

            </div>

        </div>

    </div>

</section>
