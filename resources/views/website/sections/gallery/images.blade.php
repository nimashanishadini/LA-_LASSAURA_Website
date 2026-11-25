
<section class="bg-ivory px-5 py-12 sm:px-8 lg:py-[60px]">

    <div class="mx-auto max-w-[1040px]">

        <div
            x-data="{ galleryImages: @js($galleryImages) }"
            class="grid grid-cols-1 items-start gap-x-5 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 lg:gap-y-12"
        >

            @foreach ($galleryImages as $image)

                @php
                    $aspectRatios = [
                        'aspect-square',
                        'aspect-[1.2]',
                        'aspect-square',
                        'aspect-[1.2]',
                        'aspect-square',
                        'aspect-[1.13]',
                        'aspect-square',
                        'aspect-[1.2]',
                    ];
                @endphp

                <button
                    type="button"
                    @click="$dispatch('open-image-gallery', {
                        images: galleryImages,
                        index: {{ $loop->index }},
                        autoplay: true
                    })"
                    aria-label="View {{ $image['title'] }}"
                    @class([
                        'group block w-full cursor-zoom-in text-left',
                        'lg:mt-12' => in_array($loop->index, [1, 7]),
                        'lg:mt-2.5' => $loop->index === 3,
                    ])
                >

                    {{-- Gallery Image --}}
                    <div class="overflow-hidden">

                        <img
                            src="{{ $image['src'] }}"
                            alt="{{ $image['title'] }}"
                            class="{{ $aspectRatios[$loop->index % count($aspectRatios)] }} w-full object-cover transition-transform duration-700 group-hover:scale-105"
                            loading="lazy"
                        >

                    </div>

                    {{-- Image Caption --}}
                    <div class="mt-3 flex items-center justify-between gap-3">

                        <span class="text-[13px] font-medium text-navy">
                            {{ $image['title'] }}
                        </span>

                        <svg
                            class="h-4 w-4 shrink-0 text-navy transition-colors group-hover:text-dark-gold"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.2"
                            aria-hidden="true"
                        >
                            <path d="M5 19L19 5M12 5H19V12"/>
                        </svg>

                    </div>

                </button>

            @endforeach

        </div>

    </div>

</section>
