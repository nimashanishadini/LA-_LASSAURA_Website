<span
    wire:poll.15s
    class="admin-nav-notification-root"
    aria-live="polite"
    aria-atomic="true"
>
    @if($count > 0)

        <span
            class="admin-nav-notification-badge"
            aria-label="{{ $count }} {{ $label }}"
            title="{{ $count }} {{ $label }}"
        >
            {{ $count > 99 ? '99+' : $count }}
        </span>

    @endif
</span>