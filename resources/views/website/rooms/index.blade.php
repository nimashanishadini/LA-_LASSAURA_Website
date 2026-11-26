@extends('layouts.website')
@section('title', 'Rooms | La Lassaura')
@section('description', 'Explore the family and double rooms at La Lassaura in Unawatuna.')
@section('content')
    <x-website.page-header title="A space to call your own" breadcrumb="Rooms" />
    <section class="bg-ivory px-5 py-12 sm:px-8 lg:py-16">
        <div class="mx-auto max-w-[1060px]">
            <div class="grid gap-8 md:grid-cols-2">
                @forelse ($rooms as $room)
                    <article class="overflow-hidden border border-brand-border/70 bg-white/40">
                        <a href="{{ route('rooms.show', $room) }}" class="block">
                            @if ($room->primaryImage)
                                <img src="{{ $room->primaryImage->url }}" alt="{{ $room->primaryImage->alt_text ?: $room->name }}" class="aspect-[3/2] w-full object-cover" loading="lazy">
                            @else
                                <div class="flex aspect-[3/2] items-center justify-center bg-stone text-sm text-[#42566e]">Room photographs coming soon</div>
                            @endif
                        </a>
                        <div class="p-6 sm:p-8">
                            <p class="section-label">{{ ucfirst($room->category ?? 'Guest') }} room</p>
                            <h2 class="mt-3 font-heading text-3xl">{{ $room->name }}</h2>
                            @if ($room->short_description)<p class="body-copy mt-4">{{ $room->short_description }}</p>@endif
                            <p class="mt-4 text-sm text-[#42566e]">{{ $room->max_guests !== null ? 'Up to '.$room->max_guests.' guests' : 'Guest capacity to be confirmed' }}</p>
                            <x-website.room-price :quote="$prices[$room->id] ?? null" />
                            <a href="{{ route('rooms.show', $room) }}" class="mt-6 inline-flex min-h-11 items-center border-b border-gold text-sm font-medium">Explore {{ $room->name }} →</a>
                        </div>
                    </article>
                @empty
                    <div class="border border-brand-border bg-stone p-8 md:col-span-2">
                        <h2 class="font-heading text-3xl">Our rooms are being prepared</h2>
                        <p class="body-copy mt-3">Room details and photographs will be available here soon.</p>
                        <a href="{{ route('contact.index') }}" class="mt-5 inline-block border-b border-gold py-2 text-sm">Contact us →</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
    @include('website.sections.home.cta')
@endsection
