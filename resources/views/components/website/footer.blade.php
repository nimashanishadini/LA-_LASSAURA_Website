
<footer class="bg-ivory text-navy">

    <div class="mx-auto max-w-[1400px]
                px-6 sm:px-8 lg:px-12
                pt-12 md:pt-14 lg:pt-16">

        {{-- Main Footer Content --}}
        <div class="grid grid-cols-1
                    gap-10 md:grid-cols-2
                    lg:grid-cols-[1.3fr_1fr_0.8fr]
                    lg:gap-16">

            {{-- Column 01 - Brand & Address --}}
            <div>

                <a href="{{ url('/') }}"
                   class="inline-block">

                    <h2 class="font-heading
                               text-[30px]
                               font-medium
                               leading-tight">

                        La Lassaura

                    </h2>

                    <p class="mt-2 text-[11px]
                              font-medium uppercase
                              tracking-[0.22em]">

                        Beach Side Villa

                    </p>

                </a>

                <address class="mt-6 not-italic
                                text-[14px]
                                leading-[1.9]
                                text-[#42566e]">

                    <p>27F3+JRH, Unawatuna, 80600</p>

                    <p>Sri Lanka</p>

                </address>

            </div>

            {{-- Column 02 - Navigation --}}
            <nav
                aria-label="Footer navigation"
                class="grid max-w-[300px]
                       grid-cols-2 gap-x-10
                       gap-y-5 self-start
                       text-[14px]
                       text-[#42566e]"
            >

                @php
                    $footerLinks = [
                        ['label' => 'Home', 'url' => '/'],
                        ['label' => 'Rooms', 'url' => '/#accommodation'],
                        ['label' => 'About', 'url' => '/#about'],
                        ['label' => 'Experiences', 'url' => '/#experiences'],
                        ['label' => 'Gallery', 'url' => '/#gallery'],
                        ['label' => 'Location', 'url' => '/#location'],
                        ['label' => 'Contact', 'url' => '/contact'],
                        ['label' => 'FAQ', 'url' => '/faq'],
                    ];
                @endphp

                @foreach ($footerLinks as $link)

                    <a
                        href="{{ url($link['url']) }}"
                        class="w-fit transition-colors
                               duration-300
                               hover:text-dark-gold"
                    >

                        {{ $link['label'] }}

                    </a>

                @endforeach

            </nav>

            {{-- Column 03 - Preferences --}}
            <div class="w-full max-w-[180px]
                        lg:justify-self-end">

                <label
                    for="footer-currency"
                    class="block text-[14px]
                           font-medium"
                >

                    Your preferred currency

                </label>

                <div class="relative mt-4">

                    <select
                        id="footer-currency"
                        name="currency"
                        class="w-full appearance-none
                               rounded-none
                               border border-[#d5cec0]
                               bg-transparent bg-none
                               px-4 py-3 pr-9
                               text-[14px] text-navy
                               cursor-pointer
                               focus:border-gold
                               focus:ring-1
                               focus:ring-gold"
                    >

                        <option value="USD">USD</option>

                        <option value="LKR">LKR</option>

                    </select>

                    <svg
                        class="pointer-events-none
                               absolute right-3
                               top-1/2 h-4 w-4
                               -translate-y-1/2"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        aria-hidden="true"
                    >
                        <path d="m6 9 6 6 6-6"/>
                    </svg>

                </div>

                <p class="mt-4 text-[14px]
                          text-[#42566e]">

                    Language: English

                </p>

            </div>

        </div>

        {{-- Bottom Footer --}}
        <div class="mt-12 border-t
                    border-[#d5cec0]
                    py-7 md:mt-14
                    lg:mt-16 lg:py-8">

            <div class="grid grid-cols-1
                        items-center gap-4
                        text-[12px]
                        text-[#42566e]
                        md:grid-cols-3">

                {{-- Copyright --}}
                <p>

                    &copy; {{ date('Y') }}
                    La Lassaura. All rights reserved.

                </p>

                {{-- Booking Policies --}}
                <a
                    href="{{ url('/booking-policies') }}"
                    class="w-fit transition-colors
                           duration-300
                           hover:text-dark-gold
                           md:justify-self-center"
                >

                    Booking &amp; cancellation policies

                </a>

                {{-- Tagline --}}
                <p class="md:text-right">

                    Relax. Unwind. Stay.

                </p>

            </div>

        </div>

    </div>

</footer>
