@extends('layouts.admin')
@section('title', 'Room calendar')
@section('content')
<section class="admin-panel">
 <div class="mb-6 flex flex-wrap items-end justify-between gap-5">
  <div><h2 class="text-xl font-semibold">Availability & maintenance</h2><p class="mt-2 text-sm text-slate-400">Stay dates use Sri Lanka time. The end date is available for a new arrival.</p></div>
  <label class="admin-label">Room<select id="calendar-room" class="admin-input"><option value="">All rooms</option>@foreach($rooms as $room)<option value="{{ $room->id }}">{{ $room->name }}</option>@endforeach</select></label>
 </div>
 <div class="mb-5 flex flex-wrap gap-5 text-xs text-slate-300"><span>● <span class="text-emerald-400">Confirmed stay</span></span><span>● Maintenance</span><span class="text-amber-300">Pending requests do not block nights</span></div>
 <p id="calendar-error" class="mb-4 hidden text-sm text-red-300" role="alert"></p>
 <p id="calendar-event" class="mb-4 text-sm text-slate-300" role="status" aria-live="polite"></p>
 <div class="overflow-x-auto"><div id="room-calendar" data-today="{{ now('Asia/Colombo')->toDateString() }}" data-feed="{{ route('admin.calendar.events') }}" class="min-w-[540px]"></div></div>
</section>
@can('manage-maintenance')
<section class="admin-panel mt-7"><h2 class="mb-5 text-xl font-semibold">Block a room for maintenance</h2>
 <form method="POST" action="{{ route('admin.maintenance.store') }}">@csrf
  <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
   <label class="admin-label">Room<select class="admin-input" name="villa_id" required>@foreach($rooms as $room)<option value="{{ $room->id }}" @selected(old('villa_id') == $room->id)>{{ $room->name }}</option>@endforeach</select></label>
   <label class="admin-label">First blocked night<input id="maintenance-start" class="admin-input" type="date" name="check_in" min="{{ now('Asia/Colombo')->toDateString() }}" value="{{ old('check_in') }}" required></label>
   <label class="admin-label">Available again on<input id="maintenance-end" class="admin-input" type="date" name="check_out" min="{{ now('Asia/Colombo')->addDay()->toDateString() }}" value="{{ old('check_out') }}" required></label>
  </div><label class="admin-label mt-5">Reason<input class="admin-input" name="reason" maxlength="500" value="{{ old('reason') }}" required></label>
  <button class="admin-button mt-5" @disabled($rooms->isEmpty())>Block dates</button>
 </form>
</section>
@endcan
<section class="admin-panel mt-7"><h2 class="mb-5 text-xl font-semibold">Upcoming maintenance</h2><div class="overflow-x-auto"><table class="admin-table"><thead><tr><th>Room</th><th>First night</th><th>Available again</th><th>Reason</th>@can('manage-maintenance')<th>Action</th>@endcan</tr></thead><tbody>@forelse($blocks as $block)<tr><td>{{ $block->villa->name }}</td><td>{{ $block->starts_on->toDateString() }}</td><td>{{ $block->ends_on->toDateString() }}</td><td class="max-w-xs whitespace-normal break-words">{{ $block->reason }}</td>@can('manage-maintenance')<td><form action="{{ route('admin.maintenance.destroy', $block) }}" method="POST" x-data @submit="if(!confirm('Release the remaining blocked nights?')) $event.preventDefault()">@csrf @method('DELETE')<button class="text-sm text-red-300">Release dates</button></form></td>@endcan</tr>@empty<tr><td colspan="5" class="text-slate-400">No upcoming maintenance blocks.</td></tr>@endforelse</tbody></table></div><div class="mt-5">{{ $blocks->links() }}</div></section>
@endsection
