
@props([
    'number',
    'image',
    'title',
    'description',
    'alt' => '',
])

<article class="group min-w-0">

    {{-- Experience Image --}}
    <div class="aspect-[14/15] overflow-hidden">

        <img
            src="{{ asset($image) }}"
            alt="{{ $alt }}"
            loading="lazy"
            class="h-full w-full object-cover
                   object-center transition-transform
                   duration-700 ease-out
                   group-hover:scale-105"
        >

    </div>

    {{-- Experience Number --}}
    <p class="mt-4 text-[11px] font-medium
              uppercase tracking-[0.10em]
              text-dark-gold">

        {{ $number }} — The La Lassaura Way

    </p>

    {{-- Experience Title --}}
    <h3 class="mt-3 font-heading text-[23px]
               font-medium leading-[1.25]
               text-navy">

        {{ $title }}

    </h3>

    {{-- Experience Description --}}
    <p class="mt-3 text-[13px] leading-[1.8]
              text-[#42566e]">

        {{ $description }}

    </p>

</article>
