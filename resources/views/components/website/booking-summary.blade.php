@props(['snapshot','terms'])
@if($snapshot)
<x-website.room-price :quote="$snapshot" />
<details class="mt-4 text-sm"><summary class="cursor-pointer">Nightly price breakdown</summary><ul class="mt-3 space-y-2">@foreach($snapshot['nightly_lines'] as $line)<li>{{ $line['stay_date'] }} · LKR {{ $line['amount_lkr'] }}</li>@endforeach</ul></details>
@endif
@if($terms)
<div class="mt-5 border-t border-brand-border py-4 text-sm"><h3 class="font-medium">Property terms</h3><p class="mt-2">Check-in: {{ substr($terms['check_in_time'],0,5) }} · Check-out: {{ substr($terms['check_out_time'],0,5) }} (Sri Lanka time)</p><p class="mt-3 whitespace-pre-line leading-relaxed">{{ $terms['cancellation_policy'] }}</p></div>
@endif
