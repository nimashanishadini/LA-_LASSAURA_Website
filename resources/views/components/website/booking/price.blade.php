<div x-show="{{ $source }}" class="mt-5 border-y border-brand-border/70 py-4 text-sm">
    <p class="font-medium" x-text="'LKR ' + ({{ $source }}?.total_lkr || '') + ' · ' + ({{ $source }}?.nights || '') + ' nights'"></p>
    <p class="mt-1 text-[#42566e]" x-show="{{ $source }}?.total_usd !== null" x-text="'USD ' + ({{ $source }}?.total_usd || '') + ' (display estimate; settlement in LKR)'"></p>
    <p class="mt-1 text-xs text-[#42566e]" x-show="{{ $source }}?.total_usd === null">USD estimate currently unavailable.</p>
    <p class="mt-2 text-xs text-[#42566e]" x-show="{{ $source }}?.fx" x-text="'Exchange rate updated: ' + formatFx({{ $source }}?.fx?.fetched_at)"></p>
    <p class="mt-1 text-xs text-dark-gold" x-show="{{ $source }}?.fx?.stale">Using the last successful exchange rate; it is over 24 hours old.</p>
    <details class="mt-4"><summary class="cursor-pointer">Nightly breakdown</summary><ul class="mt-3 space-y-2"><template x-for="line in ({{ $source }}?.nightly_lines || [])" :key="line.stay_date"><li x-text="line.stay_date + ' · LKR ' + line.amount_lkr"></li></template></ul></details>
    <h4 class="mt-5 font-medium">Property terms</h4>
    <p class="mt-2 text-xs" x-text="'Check-in ' + ({{ $termsSource }}?.check_in_time || '').slice(0,5) + ' · Check-out ' + ({{ $termsSource }}?.check_out_time || '').slice(0,5) + ' · Sri Lanka time'"></p>
    <p class="mt-3 whitespace-pre-line leading-relaxed" x-text="{{ $termsSource }}?.cancellation_policy"></p>
</div>
