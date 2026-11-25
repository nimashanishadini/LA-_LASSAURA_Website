
@extends('layouts.website')

@section('title', 'Rooms | La Lassaura')

@section('description', 'Discover your private escape at La Lassaura in Unawatuna.')

@section('content')

    {{-- Page Header --}}
    <x-website.page-header
        title="A space to call your own"
        breadcrumb="Rooms"
    />

    {{-- Accommodation Section --}}
    <section class="bg-ivory px-5 py-12 sm:px-8 lg:py-[60px]">

       <div class="mx-auto max-w-[1040px]">

            <x-website.accommodation-preview />

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
