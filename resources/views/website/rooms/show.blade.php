@extends('layouts.website')
@section('title', $room->name.' | La Lassaura')
@section('description', $room->short_description ?: 'Discover '.$room->name.' at La Lassaura in Unawatuna.')
@section('content')
    <x-website.page-header :title="$room->name" breadcrumb="Rooms" />
    <section class="bg-ivory px-5 py-12 sm:px-8 lg:py-16">
        <div class="mx-auto max-w-[1060px]">
            <a href="{{ route('rooms.index') }}" class="mb-7 inline-block border-b border-gold py-2 text-sm">← All rooms</a>
            <div class="grid items-start gap-8 md:grid-cols-2">
                <div class="min-w-0">
                @if ($room->primaryImage)
                    <img src="{{ $room->primaryImage->url }}" alt="{{ $room->primaryImage->alt_text ?: $room->name }}" class="aspect-[3/2] w-full object-cover">
                @else
                    <div class="flex aspect-[3/2] items-center justify-center bg-stone text-sm text-[#42566e]">Room photographs coming soon</div>
                @endif
                    @if ($room->amenities->isNotEmpty())
                        <section class="mt-5 border-y border-brand-border/70 py-5" aria-label="Room facilities">
                            <p class="mb-4 text-[10px] font-medium uppercase tracking-[0.2em] text-dark-gold">Comforts included</p>
                            <ul class="grid grid-cols-2 gap-x-4 gap-y-5 sm:grid-cols-3 md:grid-cols-2 lg:grid-cols-3">
                                @foreach ($room->amenities as $amenity)
                                    <li class="flex min-w-0 items-center gap-2.5">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gold/10 text-dark-gold">
                                            <x-website.amenity-icon :name="$amenity->icon ?: $amenity->slug" />
                                        </span>
                                        <span class="text-[12px] leading-relaxed text-navy">{{ $amenity->name }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>
                <div>
                    <p class="section-label">{{ ucfirst($room->category ?? 'Guest') }} room</p>
                    <h2 class="mt-3 font-heading text-3xl">Your stay in {{ $room->name }}</h2>
                    @if ($room->short_description)<p class="body-copy mt-4">{{ $room->short_description }}</p>@endif
                    @if ($room->description)<p class="body-copy mt-4 whitespace-pre-line">{{ $room->description }}</p>@endif
                    <dl class="mt-6 grid grid-cols-2 gap-3 border-y border-brand-border py-5 text-sm">
                        <dt>Maximum adults</dt><dd>{{ $room->max_adults ?? 'To be confirmed' }}</dd>
                        <dt>Maximum children</dt><dd>{{ $room->max_children ?? 'To be confirmed' }}</dd>
                        <dt>Total guests</dt><dd>{{ $room->max_guests ?? 'To be confirmed' }}</dd>
                    </dl>
                    <form method="GET" action="{{ route('rooms.show', $room) }}" class="mt-6 grid gap-3 sm:grid-cols-2">
                        <label class="text-sm">Check-in<input class="mt-2 block w-full border-brand-border bg-transparent text-sm" type="date" name="check_in" min="{{ now('Asia/Colombo')->toDateString() }}" value="{{ request('check_in') }}" required></label>
                        <label class="text-sm">Check-out<input class="mt-2 block w-full border-brand-border bg-transparent text-sm" type="date" name="check_out" min="{{ now('Asia/Colombo')->addDay()->toDateString() }}" value="{{ request('check_out') }}" required></label>
                        <button class="bg-gold px-4 py-3 text-sm sm:col-span-2">Calculate stay price</button>
                    </form>
                    @if($errors->any())<p role="alert" class="mt-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif
                    @if($pricingError)<p role="status" class="mt-3 text-sm text-dark-gold">{{ $pricingError }}</p>@endif
                    <x-website.room-price :quote="$quote" />
                    @if($quote && request('check_in'))
                        <details class="mt-4 text-sm"><summary class="cursor-pointer">Nightly breakdown</summary><ul class="mt-3 space-y-2">@foreach($quote['nightly_lines'] as $line)<li>{{ $line['stay_date'] }} · LKR {{ $line['amount_lkr'] }} @if($line['discount_percent'] !== '0.00')({{ $line['discount_percent'] }}% discount)@endif</li>@endforeach</ul></details>
                    @endif
                    <p class="mt-3 text-xs text-[#42566e]">Price estimate only. Availability and booking confirmation are handled separately.</p>
                    <a href="{{ route('bookings.create', ['room'=>$room->slug, 'check_in'=>request('check_in'), 'check_out'=>request('check_out')]) }}" class="mt-7 inline-flex min-h-11 items-center bg-gold px-6 py-3 text-sm font-medium hover:bg-dark-gold hover:text-white">Request {{ $room->name }} →</a>
                </div>
            </div>
            @if (count($galleryImages))
                <section class="mt-12" aria-label="Room photographs" x-data="{ images: @js($galleryImages) }">
                    <h2 class="mb-6 font-heading text-3xl">A closer look</h2>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                        @foreach ($galleryImages as $image)
                            <button type="button" class="cursor-zoom-in" @click="$dispatch('open-image-gallery', { images: images, index: {{ $loop->index }}, autoplay: false })" aria-label="View {{ $image['title'] }}">
                                <img src="{{ $image['src'] }}" alt="{{ $image['title'] }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </section>
@endsection
