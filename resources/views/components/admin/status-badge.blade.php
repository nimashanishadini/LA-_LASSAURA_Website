
@props([
    'status'
])

@php
    $normalizedStatus = strtolower((string) $status);

    $tone = match ($normalizedStatus) {
        'published', 'confirmed', 'approved', 'active' => 'success',
        'draft', 'pending' => 'warning',
        'cancelled', 'rejected', 'failed' => 'danger',
        default => 'neutral',
    };
@endphp

<span @class([
    'admin-status-badge',
    'admin-status-' . $tone
])>

    <span class="admin-status-dot"
          aria-hidden="true"></span>

    {{ ucfirst(str_replace('_', ' ', $normalizedStatus)) }}

</span>
