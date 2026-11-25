
<section class="bg-ivory px-5 pb-12 pt-10 sm:px-8 lg:pb-[60px] lg:pt-12">

    <div class="mx-auto grid max-w-[1040px] grid-cols-1 gap-7 md:grid-cols-2">

        {{-- Outdoor Relaxation --}}
        <figure class="group">

            <div class="overflow-hidden">

                <img
                    src="{{ asset('images/home/garden.jpg') }}"
                    alt="Outdoor seating surrounded by tropical greenery"
                    class="aspect-[1.5] w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    loading="lazy"
                >

            </div>

            <figcaption class="mt-4 font-heading text-[26px] font-normal text-navy">
                Outdoor relaxation
            </figcaption>

        </figure>

        {{-- Villa Architecture --}}
        <figure class="group">

            <div class="overflow-hidden">

                @if (file_exists(public_path('images/home/courtyard.jpg')))

                    <img
                        src="{{ asset('images/home/courtyard.jpg') }}"
                        alt="Courtyard and architecture at La Lassaura"
                        class="aspect-[1.5] w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                    >

                @else

                    <div class="flex aspect-[1.5] items-center justify-center border border-dashed border-brand-border bg-[#eeeee5] p-5">

                        <p class="text-sm text-[#42566e]">
                            Villa photograph coming soon.
                        </p>

                    </div>

                @endif

            </div>

            <figcaption class="mt-4 font-heading text-[26px] font-normal text-navy">
                Villa architecture
            </figcaption>

        </figure>

    </div>

</section>
