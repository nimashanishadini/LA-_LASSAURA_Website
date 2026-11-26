@props(['rate', 'rooms'])
@php
    $useOld = session()->hasOldInput() && (string) old('_rate_id', '') === ($rate->exists ? (string) $rate->id : '');
    $valueFor = fn ($key, $default = null) => $useOld ? old($key, $default) : $default;
@endphp
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <label class="admin-label">Room<select name="villa_id" class="admin-input" required>@foreach($rooms as $room)<option value="{{ $room->id }}" @selected($valueFor('villa_id', $rate->villa_id) == $room->id)>{{ $room->name }}</option>@endforeach</select></label>
    <label class="admin-label">Rule name<input class="admin-input" name="name" maxlength="120" value="{{ $valueFor('name', $rate->name) }}" placeholder="Base / weekend / holiday" required></label>
    <label class="admin-label">Nightly price (LKR)<input class="admin-input" type="number" name="nightly_lkr" min="0.01" max="99999999.99" step="0.01" value="{{ $valueFor('nightly_lkr', $rate->nightly_lkr) }}" required></label>
    <label class="admin-label">First night<input class="admin-input" type="date" name="starts_on" value="{{ $valueFor('starts_on', $rate->starts_on?->toDateString()) }}" required></label>
    <label class="admin-label">Last night (inclusive)<input class="admin-input" type="date" name="ends_on" value="{{ $valueFor('ends_on', $rate->ends_on?->toDateString()) }}" required></label>
    <label class="admin-label">Applies on<select class="admin-input" name="days">@foreach(['all'=>'Every day','weekday'=>'Weekdays (Mon–Fri)','weekend'=>'Weekends (Sat–Sun)'] as $value=>$label)<option value="{{ $value }}" @selected($valueFor('days', $rate->days ?? 'all') === $value)>{{ $label }}</option>@endforeach</select></label>
    <label class="admin-label">Priority (highest wins)<input class="admin-input" type="number" name="priority" min="0" max="1000" value="{{ $valueFor('priority', $rate->priority ?? 0) }}" required></label>
    <label class="admin-label">Discount (%)<input class="admin-input" type="number" name="discount_percent" min="0" max="100" step="0.01" value="{{ $valueFor('discount_percent', $rate->discount_percent ?? '0.00') }}" required></label>
    <label class="admin-label">Status<select class="admin-input" name="is_active"><option value="1" @selected($valueFor('is_active', $rate->is_active ?? true))>Active</option><option value="0" @selected(!$valueFor('is_active', $rate->is_active ?? true))>Inactive</option></select></label>
</div>
