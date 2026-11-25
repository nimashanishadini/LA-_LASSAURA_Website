
@extends('layouts.website')

@section('title', 'Accommodation | La Lassaura')

@section('description', 'Explore the accommodation and spaces at La Lassaura.')

@section('content')

    {{-- Page Header --}}
    <x-website.page-header
        title="A space to call your own"
        breadcrumb="Accommodation"
    />

    {{-- Accommodation Details --}}
   <section class="bg-ivory px-5 py-12 sm:px-8 lg:py-[60px]">

        <div class="mx-auto max-w-[1040px]">

            {{-- Reusable Accommodation Component --}}
            <x-website.accommodation-preview
                :show-details-link="false"
            />

            {{-- Additional Images --}}
            @include('website.sections.rooms.gallery')

            {{-- Information Notice --}}
            <p class="mt-8 text-[13px] leading-relaxed text-[#42566e]">
                Room categories, bedroom photographs, capacities,
                inclusions and rates are awaiting confirmation.
                No room inventory or live availability is represented
                in this preview.
            </p>

        </div>

    </section>

    {{-- Call to Action --}}
    @include('website.sections.home.cta')

@endsection
