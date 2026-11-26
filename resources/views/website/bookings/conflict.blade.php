@extends('layouts.website')
@section('title','Please review your stay | La Lassaura')
@section('content')
<section class="px-5 py-20"><div class="mx-auto max-w-[760px] border border-brand-border p-8"><p class="section-label">Your stay needs another look</p><h1 class="mt-4 font-heading text-4xl">Something changed</h1><p role="alert" class="body-copy mt-5">{{ $message }}</p><div class="mt-7 flex flex-wrap gap-5"><a href="{{ route('bookings.create') }}" class="bg-gold px-6 py-3 text-sm">Choose dates again</a><a href="{{ route('bookings.index') }}" class="border-b border-gold py-3 text-sm">My bookings</a>@can('manage-bookings')<a href="{{ route('admin.bookings.index') }}" class="border-b border-gold py-3 text-sm">Admin bookings</a>@endcan</div></div></section>
@endsection
