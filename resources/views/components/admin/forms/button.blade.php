@props([
    'type' => 'button',
    'variant' => 'primary',
    'href' => null,
])

@php
    $variantClass = match ($variant) {
        'secondary' => 'admin-btn-secondary',
        'danger' => 'admin-btn-danger',
        default => 'admin-btn-primary',
    };
@endphp

@if($href)

    <a
        href="{{ $href }}"

        {{ $attributes->class([
            'admin-btn',
            $variantClass,
        ]) }}
    >
        {{ $slot }}
    </a>

@else

    <button
        type="{{ $type }}"

        {{ $attributes->class([
            'admin-btn',
            $variantClass,
        ]) }}
    >
        {{ $slot }}
    </button>

@endif