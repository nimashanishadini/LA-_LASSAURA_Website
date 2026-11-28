@props([
    'route',
    'match' => null,
    'icon',
    'notification' => null,
])

@php
    $active = request()->routeIs($match ?? $route);
@endphp

<a
    href="{{ route($route) }}"

    @if($active)
        aria-current="page"
    @endif

    @class([
        'admin-shell-link',
        'is-active' => $active
    ])
>

    <x-admin.icon :name="$icon" />

    <span class="admin-nav-link-label">
        {{ $slot }}
    </span>

    @if($notification)

        <span class="admin-nav-notification-container">

            <livewire:admin.notification-badge
                :type="$notification"
                :key="'sidebar-notification-'.$notification"
            />

        </span>

    @endif

</a>