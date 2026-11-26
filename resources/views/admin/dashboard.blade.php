@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@can('manage-bookings')<a href="{{ route('admin.bookings.index', ['status'=>'pending']) }}" class="admin-panel mb-6 block text-amber-300">{{ $pendingCount }} pending booking requests · Review →</a>@endcan
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
@foreach(['Total rooms' => $rooms->count(), 'Published rooms' => $rooms->where('is_active', true)->count(), 'Draft rooms' => $rooms->where('is_active', false)->count(), 'Room photographs' => $rooms->sum('media_count')] as $label => $count)
<div class="admin-panel"><p class="text-sm text-slate-400">{{ $label }}</p><p class="mt-4 text-4xl font-semibold">{{ $count }}</p></div>@endforeach
</div>
<section class="admin-panel mt-7"><div class="mb-6 flex items-center justify-between gap-3"><h2 class="text-xl font-semibold">Room overview</h2>@can('manage-rooms')<a href="{{ route('admin.rooms.index') }}" class="text-sm text-amber-300">Manage rooms →</a>@endcan</div><div class="overflow-x-auto"><table class="admin-table"><thead><tr><th>Room</th><th>Category</th><th>Guest capacity</th><th>Photos</th><th>Website status</th></tr></thead><tbody>@forelse($rooms as $room)<tr><td>{{ $room->name }}</td><td class="capitalize">{{ $room->category ?? 'Unconfigured' }}</td><td>{{ $room->max_guests ?? 'Not configured' }}</td><td>{{ $room->media_count }}</td><td><span class="{{ $room->is_active ? 'text-emerald-300' : 'text-amber-300' }}">{{ $room->is_active ? 'Published' : 'Draft' }}</span></td></tr>@empty<tr><td colspan="5">No rooms have been configured.</td></tr>@endforelse</tbody></table></div></section>
@endsection
