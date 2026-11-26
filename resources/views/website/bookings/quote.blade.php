@extends('layouts.website')
@section('title','Review your stay | La Lassaura')
@section('content')
<x-website.page-header title="A stay to look forward to" breadcrumb="Review your request" />
<section class="px-5 py-12 sm:px-8"><div class="mx-auto grid max-w-[1060px] gap-8 md:grid-cols-2">
<div class="border border-brand-border p-6 sm:p-8"><p class="section-label">Your selected stay</p><h2 class="mt-3 font-heading text-3xl">{{ $quote->villa->name }}</h2><p class="mt-4 text-sm">{{ $quote->check_in->toDateString() }} → {{ $quote->check_out->toDateString() }}</p><p class="mt-2 text-sm">{{ $quote->adults }} adults · {{ $quote->children }} children</p><x-website.booking-summary :snapshot="$quote->snapshot" :terms="$quote->terms" /><p class="mt-4 text-xs text-[#42566e]">Quote expires {{ $quote->expires_at->timezone('Asia/Colombo')->format('d M Y H:i') }} Sri Lanka time. Prices are checked again before submission and approval.</p></div>
<div><h2 class="font-heading text-3xl">Guest details</h2><p class="body-copy mt-3">Submitting sends a request. No room is held until the property confirms it.</p>
@if($errors->any())<div role="alert" class="mt-5 border border-red-300 p-4 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
@if($quote->expires_at->isPast())<p class="mt-5 text-sm text-red-700">This quote has expired. Please calculate a new quote.</p>@endif
<form method="POST" action="{{ route('bookings.store') }}" class="mt-6 space-y-5" x-data="{ sending: false }" @submit="sending=true">@csrf<input type="hidden" name="quote_id" value="{{ $quote->id }}">
<label class="block text-sm">Full name<input name="name" autocomplete="name" maxlength="120" value="{{ old('name',auth()->user()->name) }}" required class="mt-2 block w-full border-brand-border bg-transparent"></label>
<p class="text-sm">Updates will be sent to <strong>{{ auth()->user()->email }}</strong>.</p>
<label class="block text-sm">International phone number<input type="tel" name="phone" autocomplete="tel" placeholder="+94…" maxlength="30" value="{{ old('phone') }}" required class="mt-2 block w-full border-brand-border bg-transparent"></label>
<label class="block text-sm">Special requests (optional)<textarea name="special_requests" rows="3" maxlength="1000" class="mt-2 block w-full border-brand-border bg-transparent">{{ old('special_requests') }}</textarea></label>
<label class="flex items-start gap-3 text-sm"><input class="mt-1 text-dark-gold focus:ring-gold" type="checkbox" name="accepted_terms" value="1" required><span>I accept the price and property terms shown here and understand this is a request pending approval.</span></label>
<button class="min-h-12 bg-gold px-7 py-3 text-sm font-medium disabled:opacity-50" :disabled="sending || @js($quote->expires_at->isPast())" @disabled($quote->expires_at->isPast())>Submit booking request →</button>
</form><a href="{{ route('bookings.create',['room'=>$quote->villa->slug,'check_in'=>$quote->check_in->toDateString(),'check_out'=>$quote->check_out->toDateString(),'adults'=>$quote->adults,'children'=>$quote->children]) }}" class="mt-5 inline-block text-sm underline">Change stay / refresh quote</a>
</div></div></section>
@endsection
