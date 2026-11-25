
<section class="bg-ivory px-5 py-12 sm:px-8 lg:py-[56px]">

    <div class="mx-auto max-w-[1040px]">

        <div class="grid grid-cols-1 items-center gap-8 md:grid-cols-2 lg:gap-9">

            {{-- Google Map --}}
            <div class="aspect-video w-full overflow-hidden">

                <iframe
                    src="{{ $location['map_url'] }}"
                    title="La Lassaura Villa Location"
                    class="h-full w-full border-0"
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>

            </div>

            {{-- Location Details --}}
            <div>

                <p class="text-[11px] font-medium uppercase tracking-[0.25em] text-dark-gold">
                    Southern Sri Lanka
                </p>

                <h2 class="mt-4 font-heading text-[30px] font-normal leading-[1.15] text-navy sm:text-[32px]">
                    A world away.
                    <br>
                    Right here in Unawatuna.
                </h2>

                {{-- Address --}}
                <address class="mt-6 text-[14px] not-italic leading-[1.8] text-[#42566e]">

                    {{ $location['name'] }}

                    <br>

                    {{ $location['address'] }}

                </address>

                {{-- Get Directions --}}
                <a
                    href="{{ $location['directions_url'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-7 inline-flex items-center gap-5 border-b border-gold pb-3 text-[13px] font-medium text-navy transition-colors hover:text-dark-gold"
                >

                    Get directions

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

                </a>

            </div>

        </div>

    </div>

</section>
