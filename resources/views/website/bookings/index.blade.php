@extends('layouts.website')
@section('title','My bookings | La Lassaura')
@section('content')
<x-website.page-header title="Your stays at La Lassaura" breadcrumb="My bookings" />
<section class="px-5 py-12 sm:px-8"><div class="mx-auto max-w-[1060px]">
<div class="mb-6 flex flex-wrap items-center justify-between gap-4"><p class="text-sm">Signed in as {{ auth()->user()->name }}</p><a href="{{ route('bookings.create') }}" class="bg-gold px-6 py-3 text-sm">Request a stay →</a></div>
<div class="space-y-5">@forelse($bookings as $booking)<article class="border border-brand-border p-6"><div class="flex flex-wrap items-center justify-between gap-3"><h2 class="font-heading text-3xl">{{ $booking->villa->name }}</h2><span class="text-xs font-medium uppercase tracking-wider {{ $booking->status==='confirmed' ? 'text-emerald-800':'text-dark-gold' }}">{{ str_replace('_',' ',$booking->status) }}</span></div><p class="mt-3 text-sm">{{ $booking->check_in->toDateString() }} → {{ $booking->check_out->toDateString() }}</p>@if($booking->proposed_snapshot)<p class="mt-3 text-sm text-dark-gold">A revised price or terms needs your acceptance.</p>@endif<p class="mt-2 break-all text-xs text-[#42566e]">Reference: {{ $booking->reference }}</p><a href="{{ route('bookings.show',$booking) }}" class="mt-5 inline-block border-b border-gold pb-2 text-sm">View request →</a></article>@empty<div class="border border-brand-border bg-stone p-8"><h2 class="font-heading text-3xl">Your next escape awaits</h2><p class="body-copy mt-3">You have no booking requests yet.</p></div>@endforelse</div><div class="mt-6">{{ $bookings->links() }}</div>
</div></section>
@endsection
