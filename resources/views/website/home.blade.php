
@extends('layouts.website')

@section('title', 'La Lassaura | Your Private Escape in Unawatuna')

@section('content')

    @include('website.sections.home.hero')

    @include('website.sections.home.availability-search')

    @include('website.sections.home.about')

    @include('website.sections.home.features')

    @include('website.sections.home.accommodation')

    @include('website.sections.home.experiences')

    @include('website.sections.home.gallery')

    @include('website.sections.home.location')

    @include('website.sections.home.cta')

@endsection
