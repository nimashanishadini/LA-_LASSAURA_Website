@extends('layouts.website')
@section('title','Plan your stay | La Lassaura')
@section('content')
<x-website.page-header title="Find your little escape" breadcrumb="Booking request" />
<section class="px-5 py-12 sm:px-8"><div class="mx-auto max-w-[760px] border border-brand-border bg-white/40 p-6 sm:p-9">
<p class="section-label">Your stay, your pace</p><h2 class="mt-3 font-heading text-3xl">Choose your room and dates</h2><p class="body-copy mt-4">Request one room at a time. We will check availability and calculate your price before you submit. Requests are subject to property approval.</p>
@if($errors->any())<div role="alert" class="mt-5 border border-red-300 bg-red-50 p-4 text-sm text-red-800"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('bookings.quote') }}" class="mt-7 space-y-5">@csrf
<label class="block text-sm">Room<select name="room" class="mt-2 block w-full border-brand-border bg-transparent" required><option value="">Select a room</option>@foreach($rooms as $room)<option value="{{ $room->slug }}" @selected(old('room',request('room'))===$room->slug)>{{ $room->name }} · {{ ucfirst($room->category) }} · up to {{ $room->max_guests }} guests</option>@endforeach</select></label>
<div class="grid gap-5 sm:grid-cols-2">@foreach(['check_in'=>'Check-in','check_out'=>'Check-out'] as $field=>$label)<label class="block text-sm">{{ $label }}<input type="date" name="{{ $field }}" value="{{ old($field,request($field)) }}" min="{{ now('Asia/Colombo')->toDateString() }}" class="mt-2 block w-full border-brand-border bg-transparent" required></label>@endforeach</div>
<div class="grid gap-5 sm:grid-cols-2"><label class="block text-sm">Adults<input type="number" name="adults" min="1" max="100" value="{{ old('adults',request('adults',2)) }}" required class="mt-2 block w-full border-brand-border bg-transparent"></label><label class="block text-sm">Children<input type="number" name="children" min="0" max="100" value="{{ old('children',request('children',0)) }}" required class="mt-2 block w-full border-brand-border bg-transparent"></label></div>
<button class="min-h-12 bg-gold px-7 py-3 text-sm font-medium hover:bg-dark-gold hover:text-white" @disabled($rooms->isEmpty())>Check availability & price →</button>
</form><a href="{{ route('bookings.index') }}" class="mt-6 inline-block text-sm underline">My bookings</a>
</div></section>
@endsection
