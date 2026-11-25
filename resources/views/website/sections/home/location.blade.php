
<section
    id="location"
    class="bg-ivory pb-16 pt-4
           md:pb-20 md:pt-6 lg:pb-24"
>

    <div class="mx-auto grid max-w-[1060px]
                grid-cols-1 items-center
                gap-8 px-6 sm:px-8
                md:grid-cols-2 md:gap-9">

        {{-- Left Side - Google Map --}}
        <div class="h-[280px] w-full
                    overflow-hidden md:h-[290px]">

            <iframe
                src="https://maps.google.com/maps?q=27F3%2BJRH%2C%20Unawatuna%2C%20Sri%20Lanka&t=&z=15&ie=UTF8&iwloc=&output=embed"
                title="La Lassaura Beach Side Villa Location"
                class="h-full w-full border-0"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>

        </div>

        {{-- Right Side - Location Content --}}
        <div class="py-3">

            {{-- Section Label --}}
            <p class="mb-4 text-[11px]
                      font-semibold uppercase
                      tracking-[0.22em]
                      text-dark-gold">

                Southern Sri Lanka

            </p>

            {{-- Section Heading --}}
            <h2 class="font-heading text-[32px]
                       font-medium leading-[1.15]
                       text-navy sm:text-[36px]">

                <span class="block">
                    A world away.
                </span>

                <span class="block">
                    Right here in Unawatuna.
                </span>

            </h2>

            {{-- Villa Address --}}
            <address class="mt-6 text-[14px]
                            not-italic leading-[1.85]
                            text-[#42566e]">

                <p>
                    La Lassaura – Beach Side Villa
                </p>

                <p>
                    27F3+JRH, Unawatuna, 80600, Sri Lanka.
                </p>

            </address>

            {{-- Get Directions --}}
            <a
                href="https://www.google.com/maps/dir/?api=1&destination=27F3%2BJRH%2C%20Unawatuna%2C%20Sri%20Lanka"
                target="_blank"
                rel="noopener noreferrer"
                class="group mt-7 inline-flex
                       items-center gap-6
                       border-b border-gold/50
                       pb-3 text-[13px]
                       font-medium text-navy
                       transition-colors duration-300
                       hover:text-dark-gold"
            >

                Get directions

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

    </div>

</section>
