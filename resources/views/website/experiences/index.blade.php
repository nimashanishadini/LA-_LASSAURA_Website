
@extends('layouts.website')

@section('title', 'Experiences | La Lassaura')

@section('description', 'Discover the luxury of slowing down at La Lassaura in Unawatuna.')

@section('content')

    {{-- Page Header --}}
    <x-website.page-header
        title="The luxury of slowing down"
        breadcrumb="Experiences"
    />

    {{-- Experience Cards --}}
    @include('website.sections.experiences.cards')

    {{-- Experience Gallery --}}
    @include('website.sections.experiences.gallery')

    @include('website.sections.experiences.feedback')

    {{-- Call to Action --}}
    @include('website.sections.home.cta')

@endsection
