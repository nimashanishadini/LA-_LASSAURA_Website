
<div class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-2">

    {{-- Balcony Image --}}
    <div class="overflow-hidden">

        <img
            src="{{ asset('images/home/balcony.jpg') }}"
            alt="Balcony overlooking the tropical surroundings"
            class="aspect-[1.8] w-full object-cover"
            loading="lazy"
        >

    </div>

    {{-- Bathroom Image --}}
    <div class="overflow-hidden">

        @if (file_exists(public_path('images/home/bathroom.jpg')))

            <img
                src="{{ asset('images/home/bathroom.jpg') }}"
                alt="Bathroom interior at La Lassaura"
                class="aspect-[1.8] w-full object-cover"
                loading="lazy"
            >

        @else

            <div class="flex aspect-[1.8] items-center justify-center border border-dashed border-brand-border bg-[#eeeee5] p-6 text-center">

                <p class="text-sm text-[#42566e]">
                    Bathroom photograph coming soon.
                </p>

            </div>

        @endif

    </div>

</div>
