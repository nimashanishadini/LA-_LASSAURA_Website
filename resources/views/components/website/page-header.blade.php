@props([
    'eyebrow' => 'La Lassaura · Unawatuna',
    'title',
    'breadcrumb',
])

<section class="border-b border-brand-border/50 bg-[#eaeae0] px-5 py-14 text-center sm:px-8 sm:py-16 lg:py-[60px]">

    <div class="mx-auto max-w-[1100px]">

        {{-- Eyebrow --}}
        <p class="text-[11px] font-medium uppercase tracking-[0.24em] text-dark-gold">
            {{ $eyebrow }}
        </p>

        {{-- Page Title --}}
        <h1 class="mt-4 font-heading text-[34px] font-normal leading-[1.15] text-navy sm:text-[40px] lg:text-[44px]">
            {{ $title }}
        </h1>

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb"
             class="mt-6 text-[13px] text-[#42566e]">

            <a href="{{ route('home') }}"
               class="underline underline-offset-2 transition-colors hover:text-dark-gold">
                Home
            </a>

            <span aria-hidden="true"> / </span>

            <span aria-current="page">
                {{ $breadcrumb }}
            </span>

        </nav>

    </div>

</section>
