
@php
    $experiences = [
        [
            'number' => '01',
            'image' => 'images/home/hero.jpg',
            'alt' => 'Swimming pool at La Lassaura villa',
            'title' => 'Poolside, at your own pace',
            'description' => 'A refreshing dip. A quiet evening. Nothing on the agenda.',
        ],
        [
            'number' => '02',
            'image' => 'images/home/garden.jpg',
            'alt' => 'Peaceful tropical garden and outdoor seating area',
            'title' => 'Find your quiet corner',
            'description' => 'Lush greenery, still waters and a little time for yourself.',
        ],
        [
            'number' => '03',
            'image' => 'images/home/balcony.jpg',
            'alt' => 'Open balconies overlooking the tropical garden',
            'title' => 'Wake up to a greener view',
            'description' => 'Open balconies and tropical views. Take it all in.',
        ],
        [
            'number' => '04',
            'image' => 'images/home/interior.jpg',
            'alt' => 'Comfortable shared living space at La Lassaura',
            'title' => 'Make yourself at home',
            'description' => 'Welcoming living spaces, warm textures and room to unwind.',
        ],
    ];
@endphp

<section
    id="experiences"
    class="bg-[#eeeee5] py-16
           md:py-20 lg:py-24"
>

    <div class="mx-auto max-w-[1200px]
                px-6 sm:px-8">

        {{-- Section Header --}}
        <div class="mx-auto mb-10 max-w-2xl
                    text-center md:mb-12">

            {{-- Section Label --}}
            <p class="mb-4 text-[11px] font-semibold
                      uppercase tracking-[0.22em]
                      text-dark-gold">

                Less Rush. More Living.

            </p>

            {{-- Main Heading --}}
            <h2 class="font-heading text-[38px]
                       font-medium leading-[1.15]
                       text-navy sm:text-[44px]
                       lg:text-[46px]">

                Beautifully unhurried days

            </h2>

            {{-- Subtitle --}}
            <p class="mt-4 text-[14px]
                      leading-relaxed text-[#42566e]">

                Little moments. Lasting memories.
                Entirely your own.

            </p>

        </div>

        {{-- Experiences Grid --}}
        <div class="grid grid-cols-1
                    gap-x-6 gap-y-10
                    sm:grid-cols-2
                    lg:grid-cols-4">

            @foreach ($experiences as $experience)

                <x-website.experience-card
                    :number="$experience['number']"
                    :image="$experience['image']"
                    :title="$experience['title']"
                    :description="$experience['description']"
                    :alt="$experience['alt']"
                />

            @endforeach

        </div>

    </div>

</section>
