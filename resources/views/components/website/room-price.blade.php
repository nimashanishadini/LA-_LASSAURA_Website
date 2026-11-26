@props(['quote'])
@if($quote)
    <div class="mt-5 border-t border-brand-border/70 pt-4 text-sm">
        <p class="font-medium text-navy">LKR {{ $quote['total_lkr'] }} <span class="font-normal">{{ $quote['nights'] === 1 ? 'per night' : 'for '.$quote['nights'].' nights' }}</span></p>
        @if($quote['total_usd'] !== null)
            <p class="mt-1 text-[#42566e]">USD {{ $quote['total_usd'] }} <span class="text-xs">(display estimate; settlement in LKR)</span></p>
            <p class="mt-2 text-xs text-[#42566e]">Exchange rate: {{ \Carbon\CarbonImmutable::parse($quote['fx']['fetched_at'])->timezone('Asia/Colombo')->format('d M Y H:i') }} Sri Lanka time</p>
            @if($quote['fx']['stale'])<p class="mt-1 text-xs text-dark-gold">Exchange rate is over 24 hours old; using the last successful rate.</p>@endif
        @else<p class="mt-1 text-xs text-[#42566e]">USD estimate unavailable until an exchange rate is configured.</p>@endif
    </div>
@else<p class="mt-5 text-sm text-[#42566e]">Nightly rates available on enquiry.</p>@endif
