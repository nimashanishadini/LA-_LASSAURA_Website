
@extends('layouts.website')

@section('title', 'Location | La Lassaura')

@section('description', 'Find your way to La Lassaura Beach Side Villa in Unawatuna, Sri Lanka.')

@section('content')

    <x-website.page-header
        title="Find your way to Unawatuna"
        breadcrumb="Location"
    />

    @include('website.sections.location.map')

    @include('website.sections.home.cta')

@endsection
