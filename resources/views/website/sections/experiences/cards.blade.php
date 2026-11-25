
<section class="bg-ivory px-5 pt-12 sm:px-8 lg:pt-[60px]">

    <div class="mx-auto max-w-[1040px]">

        <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">

            @foreach ($experiences as $experience)

                <article class="group">

                    {{-- Clickable Experience Image --}}
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-image-gallery', {
                            images: @js($galleryImages),
                            index: {{ $loop->index }}
                        })"
                        aria-label="View {{ $experience['title'] }}"
                        class="block w-full cursor-zoom-in overflow-hidden"
                    >

                        <img
                            src="{{ asset($experience['image']) }}"
                            alt="{{ $experience['title'] }}"
                            class="aspect-[0.86] w-full object-cover transition-transform duration-700 group-hover:scale-105"
                            loading="lazy"
                        >

                    </button>

                    {{-- Experience Number --}}
                    <p class="mt-4 text-[11px] font-medium uppercase tracking-[0.08em] text-dark-gold">
                        {{ $experience['number'] }} — The La Lassaura Way
                    </p>

                    {{-- Experience Title --}}
                    <h2 class="mt-3 font-heading text-[22px] font-normal leading-tight text-navy">
                        {{ $experience['title'] }}
                    </h2>

                    {{-- Description --}}
                    <p class="mt-3 text-[13px] leading-[1.8] text-[#42566e]">
                        {{ $experience['description'] }}
                    </p>

                </article>

            @endforeach

        </div>

    </div>

</section>
