
@props(['name'])

<svg
    aria-hidden="true"
    focusable="false"
    class="h-5 w-5 shrink-0"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.6"
    stroke-linecap="round"
    stroke-linejoin="round"
>

    @switch($name)

        @case('dashboard')
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/>
            @break

        @case('rooms')
            <path d="M3 21V10l9-7 9 7v11"/>
            <path d="M3 17h18"/>
            <path d="M7 17v-5h10v5"/>
            @break

        @case('rates')
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 6v12m3-9h-5a2 2 0 0 0 0 4h4a2 2 0 0 1 0 4H9"/>
            @break

        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"/>
            <path d="M7 3v4m10-4v4M3 10h18m-14 4h2m3 0h2m3 0h1"/>
            @break

        @case('bookings')
            <rect x="5" y="3" width="14" height="18" rx="2"/>
            <path d="M9 8h6m-6 4h6m-6 4h4"/>
            @break

        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2"/>
            <path d="m3 7 9 7 9-7"/>
            @break

        @case('star')
            <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9z"/>
            @break

        @case('settings')
            <path d="M4 6h16M4 12h16M4 18h16"/>
            <circle cx="8" cy="6" r="2" fill="#192635"/>
            <circle cx="16" cy="12" r="2" fill="#192635"/>
            <circle cx="10" cy="18" r="2" fill="#192635"/>
            @break

        @case('external')
            <path d="M13 4h7v7m0-7L10 14M10 4H4v16h16v-6"/>
            @break

        @case('user')
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
            @break

        @case('logout')
            <path d="M10 4H4v16h6m4-13 5 5-5 5m-6-5h11"/>
            @break


@case('check-circle')
    <circle cx="12" cy="12" r="9"/>
    <path d="m8 12 3 3 5-6"/>
    @break

@case('file-text')
    <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
    <path d="M14 3v6h6"/>
    <path d="M8 13h8M8 17h8"/>
    @break

@case('images')
    <rect x="3" y="3" width="18" height="18" rx="2"/>
    <circle cx="8.5" cy="8.5" r="1.5"/>
    <path d="m21 15-5-5L5 21"/>
    @break

@case('plus-circle')
    <circle cx="12" cy="12" r="9"/>
    <path d="M12 8v8M8 12h8"/>
    @break


    @endswitch

</svg>
