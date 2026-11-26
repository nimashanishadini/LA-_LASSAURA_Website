
@extends('layouts.website')

@section('title', 'Contact | La Lassaura')

@section('description', 'Get in touch and plan your escape to La Lassaura in Unawatuna.')

@section('content')

    <x-website.page-header
        title="Your escape starts with a hello"
        breadcrumb="Contact"
    />

    <section class="bg-ivory px-5 py-12 sm:px-8 lg:py-[56px]">

        <div class="mx-auto max-w-[1060px]">

            <div class="grid items-start gap-10 lg:grid-cols-2 lg:gap-14">
                <div>
                    @include('website.sections.contact.details')
                    @include('website.sections.contact.map')
                </div>
                @include('website.sections.contact.inquiry-form')
            </div>

        </div>

    </section>

    @include('website.sections.home.cta')

@endsection
