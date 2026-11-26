@extends('layouts.website')
@section('title','Booking '.$booking->reference.' | La Lassaura')
@section('content')
<x-website.page-header :title="$booking->villa->name" breadcrumb="My booking" />
<section class="px-5 py-12 sm:px-8"><div class="mx-auto max-w-[800px]">
<a href="{{ route('bookings.index') }}" class="text-sm underline">← My bookings</a>
@if(session('status'))<p role="status" class="mt-5 border border-brand-border bg-stone p-4 text-sm">{{ session('status') }}</p>@endif
@if($errors->any())<p role="alert" class="mt-5 text-sm text-red-700">{{ $errors->first() }}</p>@endif
<div class="mt-6 border border-brand-border p-6 sm:p-8"><p class="section-label">{{ str_replace('_',' ',$booking->status) }}</p><p class="mt-3 break-all text-xs text-[#42566e]">Reference: {{ $booking->reference }}</p><h2 class="mt-4 font-heading text-3xl">{{ $booking->check_in->toDateString() }} → {{ $booking->check_out->toDateString() }}</h2><p class="mt-3 text-sm">{{ $booking->adults }} adults · {{ $booking->children }} children</p>
@if($booking->status==='pending')<p class="body-copy mt-4">Your request is awaiting approval. These dates are not reserved.</p>@elseif($booking->status==='confirmed')<p class="mt-4 text-sm text-emerald-800">Your stay is confirmed and these nights are reserved.</p>@endif
@if($booking->rejection_reason)<p class="mt-4 text-sm">Property response: {{ $booking->rejection_reason }}</p>@endif
<x-website.booking-summary :snapshot="$booking->quote_snapshot" :terms="$booking->terms_snapshot" />
</div>
@if($booking->proposed_snapshot && $booking->status==='pending')<section class="mt-6 border border-gold bg-gold/5 p-6"><h2 class="font-heading text-3xl">Please review this updated quote</h2><p class="body-copy mt-3">The price or property terms changed. Accepting sends the request back for admin approval; it does not reserve a room.</p><x-website.booking-summary :snapshot="$booking->proposed_snapshot" :terms="$booking->proposed_terms" /><form method="POST" action="{{ route('bookings.accept',$booking) }}" class="mt-5">@csrf<input type="hidden" name="revision" value="{{ $revision }}"><label class="flex items-start gap-3 text-sm"><input type="checkbox" name="accepted_terms" value="1" required class="mt-1"><span>I accept the revised price and terms displayed above.</span></label><button class="mt-5 bg-gold px-6 py-3 text-sm">Accept revised quote</button></form></section>@endif
@if($booking->status==='pending')<form class="mt-6" method="POST" action="{{ route('bookings.cancel',$booking) }}" x-data @submit="if(!confirm('Cancel this pending request?')) $event.preventDefault()">@csrf<button class="text-sm text-red-700 underline">Cancel pending request</button></form>@elseif($booking->status==='confirmed')<p class="body-copy mt-6">To request changes or cancellation, <a class="underline" href="{{ route('contact.index') }}">contact the property</a> and include your reference.</p>@endif
<section class="mt-8"><h2 class="font-heading text-2xl">Request history</h2><ul class="mt-4 space-y-3 text-sm text-[#42566e]">@foreach($events as $event)<li>{{ $event->created_at->timezone('Asia/Colombo')->format('d M Y H:i') }} · {{ ucfirst(str_replace('_',' ',$event->action)) }}</li>@endforeach</ul></section>
</div></section>
@endsection
