@props(['rate', 'rooms'])

@php
    $rateKey = $rate->exists ? (string) $rate->id : 'new';

    $useOld = session()->hasOldInput()
    && old('_rates_form') === 'rate'
    && (string) old('_rate_id', '') ===
        ($rate->exists ? (string) $rate->id : '');

    $valueFor = fn ($key, $default = null) =>
        $useOld ? old($key, $default) : $default;

    $roomOptions = $rooms->pluck('name', 'id')->all();
@endphp

<div class="admin-rate-form-grid">

    <x-admin.forms.select
        name="villa_id"
        :id="'rate-room-'.$rateKey"
        label="Room"
        :options="$roomOptions"
        :value="$valueFor('villa_id', $rate->villa_id)"
        :required="true"
        :use-old="false"
        :show-errors="$useOld"
    />

    <x-admin.forms.input
        name="name"
        :id="'rate-name-'.$rateKey"
        label="Rule name"
        :value="$valueFor('name', $rate->name)"
        maxlength="120"
        placeholder="Base / weekend / holiday"
        :required="true"
        :use-old="false"
        :show-errors="$useOld"
    />

    <x-admin.forms.input
        type="number"
        name="nightly_lkr"
        :id="'rate-price-'.$rateKey"
        label="Nightly price (LKR)"
        :value="$valueFor('nightly_lkr', $rate->nightly_lkr)"
        min="0.01"
        max="99999999.99"
        step="0.01"
        placeholder="0.00"
        :required="true"
        :use-old="false"
        :show-errors="$useOld"
    />

    <x-admin.forms.input
        type="date"
        name="starts_on"
        :id="'rate-start-'.$rateKey"
        label="First night"
        :value="$valueFor(
            'starts_on',
            $rate->starts_on?->toDateString()
        )"
        :required="true"
        :use-old="false"
        :show-errors="$useOld"
    />

    <x-admin.forms.input
        type="date"
        name="ends_on"
        :id="'rate-end-'.$rateKey"
        label="Last night (inclusive)"
        :value="$valueFor(
            'ends_on',
            $rate->ends_on?->toDateString()
        )"
        :required="true"
        :use-old="false"
        :show-errors="$useOld"
    />

    <x-admin.forms.select
        name="days"
        :id="'rate-days-'.$rateKey"
        label="Applies on"
        :options="[
            'all' => 'Every day',
            'weekday' => 'Weekdays (Mon–Fri)',
            'weekend' => 'Weekends (Sat–Sun)'
        ]"
        :value="$valueFor('days', $rate->days ?? 'all')"
        :use-old="false"
        :show-errors="$useOld"
    />

    <x-admin.forms.input
        type="number"
        name="priority"
        :id="'rate-priority-'.$rateKey"
        label="Priority"
        :value="$valueFor('priority', $rate->priority ?? 0)"
        min="0"
        max="1000"
        help="Highest priority wins."
        :required="true"
        :use-old="false"
        :show-errors="$useOld"
    />

    <x-admin.forms.input
        type="number"
        name="discount_percent"
        :id="'rate-discount-'.$rateKey"
        label="Discount (%)"
        :value="$valueFor(
            'discount_percent',
            $rate->discount_percent ?? '0.00'
        )"
        min="0"
        max="100"
        step="0.01"
        :required="true"
        :use-old="false"
        :show-errors="$useOld"
    />

    <x-admin.forms.select
        name="is_active"
        :id="'rate-status-'.$rateKey"
        label="Status"
        :options="[
            '1' => 'Active',
            '0' => 'Inactive'
        ]"
        :value="(int) $valueFor(
            'is_active',
            $rate->is_active ?? true
        )"
        :use-old="false"
        :show-errors="$useOld"
    />

</div>