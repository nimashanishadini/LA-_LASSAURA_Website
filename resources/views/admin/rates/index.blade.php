@extends('layouts.admin')
@section('title', 'Rates & currencies')
@section('content')
<div class="admin-panel mb-7">
    <h2 class="text-xl font-semibold">LKR → USD display</h2>
    @if($fx)
        <p class="mt-4 text-lg">1 USD = <strong>{{ $fx->lkr_per_usd }} LKR</strong></p>
        <p class="mt-2 text-sm text-slate-400">{{ $fx->source }} · {{ $fx->fetched_at->timezone('Asia/Colombo')->format('d M Y, H:i') }} Sri Lanka time</p>
        @if($fx->isStale())<p class="mt-2 text-sm text-amber-300">This rate is over 24 hours old. Public prices display a stale-rate notice.</p>@endif
    @else<p class="mt-4 text-slate-400">No valid exchange rate recorded. Public prices show LKR only.</p>@endif
    <form method="POST" action="{{ route('admin.rates.exchange') }}" class="mt-6 space-y-4">@csrf
        <div class="grid gap-4 sm:grid-cols-2"><label class="admin-label">LKR per 1 USD<input class="admin-input" type="number" min="0.000001" max="99999999.999999" step="0.000001" name="lkr_per_usd" value="{{ old('lkr_per_usd') }}" required></label><label class="admin-label">Reason for override<input class="admin-input" name="reason" maxlength="500" value="{{ old('reason') }}" required></label></div>
        <p class="text-xs text-slate-400">Records a new exchange-rate snapshot with your user ID. The next successful provider refresh replaces the override for new quotes. Settlement is LKR.</p>
        <button class="admin-button">Record exchange rate</button>
    </form>
    @if($history->isNotEmpty())<details class="mt-6"><summary class="cursor-pointer text-sm text-slate-300">Recent exchange-rate history</summary><div class="overflow-x-auto mt-4"><table class="admin-table"><thead><tr><th>LKR / USD</th><th>Source</th><th>Recorded</th><th>Actor</th></tr></thead><tbody>@foreach($history as $entry)<tr><td>{{ $entry->lkr_per_usd }}</td><td>{{ $entry->source }}</td><td>{{ $entry->fetched_at->timezone('Asia/Colombo')->format('d M Y H:i') }}</td><td>{{ $entry->recorded_by ? 'Staff #'.$entry->recorded_by : 'Provider' }}</td></tr>@endforeach</tbody></table></div></details>@endif
</div>
<section class="admin-panel mb-7"><h2 class="text-xl font-semibold">Add room rate</h2><p class="my-4 text-sm text-slate-400">Set a base rule (priority 0), then higher-priority weekend/seasonal rules. The selected rule's discount applies once; discounts do not stack. Dates refer to occupied nights.</p>
    @if($rooms->isEmpty())<p>Add a room before configuring rates.</p>@else
    <form action="{{ route('admin.rates.store') }}" method="POST">@csrf<x-admin.rate-fields :rate="new \App\Models\VillaRate" :rooms="$rooms" /><button class="admin-button mt-5">Add rate rule</button></form>@endif
</section>
<section><h2 class="mb-5 text-xl font-semibold">Configured rate rules</h2><div class="space-y-4">
    @forelse($rates as $rate)<details class="admin-panel" @if(old('_rate_id') == $rate->id) open @endif><summary class="cursor-pointer font-medium">{{ $rate->villa->name }} · {{ $rate->name }} <span class="ml-3 text-sm text-slate-400">LKR {{ $rate->nightly_lkr }} · priority {{ $rate->priority }} · {{ $rate->is_active ? 'Active' : 'Inactive' }}</span></summary><form class="mt-6" method="POST" action="{{ route('admin.rates.update', $rate) }}">@csrf @method('PUT')<input type="hidden" name="_rate_id" value="{{ $rate->id }}"><x-admin.rate-fields :rate="$rate" :rooms="$rooms" /><button class="admin-button mt-5">Save rate rule</button></form></details>@empty<p class="text-sm text-slate-400">No rates configured. Add the property's agreed rates above.</p>@endforelse
</div></section>
@endsection
