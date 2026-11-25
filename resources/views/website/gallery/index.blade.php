
@extends('layouts.website')

@section('title', 'Gallery | La Lassaura')

@section('description', 'Take a little closer look at La Lassaura in Unawatuna.')

@section('content')

    <x-website.page-header
        title="A little closer to paradise"
        breadcrumb="Gallery"
    />

    @include('website.sections.gallery.images')

    @include('website.sections.home.cta')

@endsection
