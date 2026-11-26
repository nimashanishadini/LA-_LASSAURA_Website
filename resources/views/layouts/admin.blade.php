<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Dashboard') | La Lassaura Admin</title>@vite(['resources/css/app.css', 'resources/js/app.js']) @livewireStyles</head>
<body class="bg-[#0e1116] text-slate-100 font-body antialiased" x-data="{ menu: false }">
<a href="#admin-content" class="sr-only focus:not-sr-only focus:block focus:p-4">Skip to content</a>
<div class="min-h-screen lg:flex">
<aside class="border-r border-slate-700/60 bg-[#192635] lg:fixed lg:inset-y-0 lg:w-64">
<div class="flex items-center justify-between border-b border-white/10 p-6"><a href="{{ route('admin.dashboard') }}" class="text-xl font-semibold tracking-widest">LA<br>LASSAURA <span class="mt-2 block text-[10px] font-normal tracking-[0.25em] text-slate-400">PROPERTY MANAGEMENT</span></a><button type="button" @click="menu = !menu" :aria-expanded="menu" aria-controls="admin-nav" class="rounded border border-slate-500 px-3 py-2 lg:hidden">Menu</button></div>
<nav id="admin-nav" :class="menu ? 'block' : 'hidden lg:block'" class="space-y-2 p-4" aria-label="Admin navigation">
<a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif class="admin-nav {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : '' }}">▦ <span>Dashboard</span></a>
@can('manage-rooms')<a href="{{ route('admin.rooms.index') }}" @if(request()->routeIs('admin.rooms.*')) aria-current="page" @endif class="admin-nav {{ request()->routeIs('admin.rooms.*') ? 'bg-white/10 text-white' : '' }}">⌂ <span>Rooms</span></a>@endcan
@can('manage-rates')<a href="{{ route('admin.rates.index') }}" class="admin-nav {{ request()->routeIs('admin.rates.*') ? 'bg-white/10 text-white' : '' }}">◇ <span>Rates & currencies</span></a>@endcan
@can('manage-inquiries')<a href="{{ route('admin.inquiries.index') }}" class="admin-nav {{ request()->routeIs('admin.inquiries.*') ? 'bg-white/10 text-white' : '' }}">✉ <span>Inquiries</span></a>@endcan
@can('manage-reviews')<a href="{{ route('admin.reviews.index') }}" class="admin-nav {{ request()->routeIs('admin.reviews.*') ? 'bg-white/10 text-white' : '' }}">☆ <span>Guest feedback</span></a>@endcan
@can('view-calendar')<a href="{{ route('admin.calendar.index') }}" class="admin-nav {{ request()->routeIs('admin.calendar.*') ? 'bg-white/10 text-white' : '' }}">▦ <span>Calendar</span></a>@endcan
@can('manage-bookings')<a href="{{ route('admin.bookings.index') }}" class="admin-nav {{ request()->routeIs('admin.bookings.*') ? 'bg-white/10 text-white' : '' }}">▤ <span>Bookings</span></a>@endcan
@can('manage-booking-policies')<a href="{{ route('admin.booking-policies') }}" class="admin-nav {{ request()->routeIs('admin.booking-policies*') ? 'bg-white/10 text-white' : '' }}">⚙ <span>Booking policies</span></a>@endcan
<div class="mt-8 border-t border-white/10 pt-5"><a href="{{ route('home') }}" class="admin-nav">↗ <span>View website</span></a><a href="{{ route('profile.edit') }}" class="admin-nav">○ <span>My profile</span></a></div>
<form action="{{ route('logout') }}" method="POST">@csrf<button class="admin-nav w-full text-left">↪ <span>Sign out</span></button></form>
</nav></aside>
<div class="min-w-0 flex-1 lg:ml-64"><header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-700/60 px-6 py-5 lg:px-10"><span class="text-xs uppercase tracking-[0.2em] text-slate-400">La Lassaura · Admin</span><span class="text-sm text-slate-300">{{ auth()->user()->name }} <span class="ml-2 rounded-full bg-white/5 px-3 py-1 text-xs capitalize">{{ auth()->user()->role }}</span></span></header>
<main id="admin-content" class="mx-auto max-w-7xl p-5 sm:p-8 lg:p-10"><div class="mb-8"><p class="mb-2 text-xs uppercase tracking-[0.2em] text-amber-400">Property overview</p><h1 class="text-3xl font-semibold">@yield('title', 'Dashboard')</h1></div>
@if(session('status'))<div role="status" class="mb-6 rounded-xl border border-emerald-700 bg-emerald-950 p-4 text-emerald-100">{{ session('status') }}</div>@endif
@if($errors->any())<div role="alert" class="mb-6 rounded-xl border border-red-800 bg-red-950 p-4"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')</main></div></div>@livewireScripts</body></html>
