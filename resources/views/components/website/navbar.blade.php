
<header
    x-data="{ mobileOpen: false, currencyOpen: false, currency: 'USD' }"
    @keydown.escape.window="mobileOpen = false; currencyOpen = false"
    class="relative z-50 bg-ivory"
>
    @php
        $links = [
            'Home' => route('home'),
            'Rooms' => route('rooms.index'),
            'Experiences' => route('experiences.index'),
            'Gallery' => route('gallery.index'),
            'Location' => route('location.index'),
            'Contact' => route('contact.index'),
        ];

        $activePage = match (true) {
            request()->routeIs('home') => 'Home',
            request()->routeIs('rooms.*') => 'Rooms',
            request()->routeIs('experiences.*') => 'Experiences',
            request()->routeIs('gallery.*') => 'Gallery',
            request()->routeIs('location.*') => 'Location',
            request()->routeIs('contact.*') => 'Contact',
            default => null,
        };
    @endphp

    <nav class="mx-auto flex min-h-[96px] max-w-[1600px]
                items-center justify-between gap-6 px-5
                sm:px-8 xl:px-16"
         aria-label="Main navigation">

        {{-- Brand --}}
        <a href="{{ route('home') }}"
           class="flex shrink-0 items-center gap-4">

            <img
                src="{{ asset('images/brand/logo.jpeg') }}"
                alt="La Lassaura logo"
                class="h-[60px] w-[80px] object-contain
                       sm:h-[76px] sm:w-[88px]"
            >

            <div class="text-center">

                <span class="block font-heading text-[21px]
                             font-semibold tracking-[0.07em]
                             text-navy sm:text-[25px]">
                    LA LASSAURA
                </span>

                <span class="mt-1 block text-[9px]
                             uppercase tracking-[0.25em]
                             text-navy sm:text-[11px]">
                    Beach Side Villa
                </span>

            </div>

        </a>

        {{-- Desktop Navigation --}}
        <div class="hidden items-center gap-7 xl:flex">

            @foreach ($links as $label => $href)

                <a href="{{ $href }}"
                   @if ($activePage === $label)
                       aria-current="{{ $label === 'Rooms' && !request()->routeIs('rooms.index') ? 'location' : 'page' }}"
                   @endif
                   class="relative py-4 text-[14px] font-medium
                          transition-colors duration-200
                          hover:text-dark-gold
                          {{ $activePage === $label
                              ? 'text-navy after:absolute after:bottom-0 after:left-0 after:h-px after:w-full after:bg-gold/50'
                              : 'text-navy' }}">

                    {{ $label }}

                </a>

            @endforeach

        </div>

        {{-- Desktop Actions --}}
        <div class="hidden shrink-0 items-center gap-5 xl:flex">

            {{-- Currency Dropdown --}}
            <div class="relative"
                 @click.outside="currencyOpen = false">

                <button
                    type="button"
                    @click="currencyOpen = !currencyOpen"
                    :aria-expanded="currencyOpen"
                    aria-label="Select display currency"
                    class="flex items-center gap-1.5 text-[13px]"
                >

                    <span x-text="currency"></span>

                    <svg class="h-3 w-3"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.5"
                         aria-hidden="true">

                        <path d="m5 9 7 7 7-7"/>

                    </svg>

                </button>

                <div
                    x-cloak
                    x-show="currencyOpen"
                    x-transition
                    class="absolute right-0 top-full mt-4
                           w-28 border border-brand-border
                           bg-ivory py-1 shadow-lg"
                >

                    @foreach (['USD', 'LKR'] as $item)

                        <button
                            type="button"
                            @click="currency = '{{ $item }}';
                                    currencyOpen = false"
                            class="block w-full px-4 py-2
                                   text-left text-sm hover:bg-stone"
                        >
                            {{ $item }}
                        </button>

                    @endforeach

                </div>

            </div>

            {{-- Desktop Booking Button --}}
            <button
                type="button"
                @click="$dispatch('open-booking')"
                class="inline-flex min-h-[48px] items-center
                       justify-center gap-5 bg-gold px-7
                       text-sm font-semibold text-navy
                       cursor-pointer transition-colors
                       hover:bg-dark-gold hover:text-white"
            >

                Book Your Stay

                <svg class="h-5 w-5"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.5"
                     aria-hidden="true">

                    <path d="M3 12h17M14 6l6 6-6 6"/>

                </svg>

            </button>

        </div>

        {{-- Mobile Menu Button --}}
        <button
            type="button"
            @click="mobileOpen = !mobileOpen"
            :aria-expanded="mobileOpen"
            aria-controls="mobile-navigation"
            aria-label="Toggle navigation"
            class="flex h-10 w-10 items-center
                   justify-center text-navy xl:hidden"
        >

            <svg x-show="!mobileOpen"
                 class="h-7 w-7"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.5"
                 aria-hidden="true">

                <path d="M3 6h18M3 12h18M3 18h18"/>

            </svg>

            <svg x-cloak x-show="mobileOpen"
                 class="h-7 w-7"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.5"
                 aria-hidden="true">

                <path d="m6 6 12 12M18 6 6 18"/>

            </svg>

        </button>

    </nav>

    {{-- Mobile Navigation --}}
    <div
        id="mobile-navigation"
        x-cloak
        x-show="mobileOpen"
        x-transition
        class="absolute left-0 top-full w-full
               border-t border-brand-border bg-ivory
               px-6 py-5 shadow-lg xl:hidden"
    >

        @foreach ($links as $label => $href)

            <a href="{{ $href }}"
               @click="mobileOpen = false"
               @if ($activePage === $label)
                   aria-current="{{ $label === 'Rooms' && !request()->routeIs('rooms.index') ? 'location' : 'page' }}"
               @endif
               class="block border-b border-brand-border/50
                      py-3 text-sm font-medium
                      transition-colors hover:text-dark-gold
                      {{ $activePage === $label
                          ? 'text-dark-gold'
                          : 'text-navy' }}">

                {{ $label }}

            </a>

        @endforeach

        {{-- Mobile Currency Selector --}}
        <div class="my-4 flex items-center gap-4">

            <span class="text-sm">Currency:</span>

            @foreach (['USD', 'LKR'] as $item)

                <button
                    type="button"
                    @click="currency = '{{ $item }}'"
                    :class="currency === '{{ $item }}'
                        ? 'bg-gold'
                        : 'border border-brand-border'"
                    :aria-pressed="currency === '{{ $item }}'"
                    class="px-4 py-2 text-sm"
                >

                    {{ $item }}

                </button>

            @endforeach

        </div>

        {{-- Mobile Booking Button --}}
        <button
            type="button"
            @click="mobileOpen = false; $dispatch('open-booking')"
            class="flex w-full items-center justify-center
                   bg-gold px-6 py-4 text-sm font-semibold
                   cursor-pointer transition-colors duration-300
                   hover:bg-dark-gold hover:text-white"
        >

            Book Your Stay →

        </button>

    </div>

</header>
