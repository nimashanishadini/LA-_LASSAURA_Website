@props(['name'])
<svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('wifi')
            <path d="M2 8.5a16 16 0 0 1 20 0M5 12a11 11 0 0 1 14 0M8.5 15.5a5.5 5.5 0 0 1 7 0"/><circle cx="12" cy="19" r=".7" fill="currentColor"/>
            @break
        @case('snowflake')
        @case('air-conditioning')
            <path d="M12 2v20M3.3 7l17.4 10M3.3 17 20.7 7M9 4l3 3 3-3M9 20l3-3 3 3M3.5 10l4-1-1-4M17.5 19l-1-4 4-1M3.5 14l4 1-1 4M17.5 5l-1 4 4 1"/>
            @break
        @case('waves')
        @case('swimming-pool')
            <path d="M2 12c3-4 5 4 8 0s5 4 8 0 4 0 4 0M2 17c3-4 5 4 8 0s5 4 8 0 4 0 4 0M6 8V4a2 2 0 0 1 4 0M14 8V4a2 2 0 0 1 4 0M6 6h8"/>
            @break
        @case('bath')
        @case('private-bathroom')
            <path d="M3 12h18v3a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5v-3ZM5 12V5a3 3 0 0 1 6 0M8 20v2M16 20v2M9 5h4"/>
            @break
        @case('car')
        @case('parking')
            <path d="m4 10 2-6h12l2 6M3 10h18v8H3zM5 18v3M19 18v3M6 14h2M16 14h2"/>
            @break
        @case('trees')
        @case('garden-view')
            <path d="m8 3-5 7h3l-4 6h12l-4-6h3L8 3ZM8 16v5M17 6l-3 5h2l-3 5h9l-3-5h2l-4-5ZM17 16v5"/>
            @break
        @case('sun')
        @case('balcony')
            <path d="M3 13h18M4 13v8M8 13v8M12 13v8M16 13v8M20 13v8M3 21h18M8 10a4 4 0 0 1 8 0M12 2v2M5 5l2 2M19 5l-2 2"/>
            @break
        @case('bed-double')
        @case('king-size-bed')
            <path d="M3 17v4M21 17v4M3 17h18v-6H3v6ZM5 11V4h14v7M7 8h3v3M14 8h3v3"/>
            @break
        @default
            <path d="m5 12 4 4L19 6"/>
    @endswitch
</svg>
