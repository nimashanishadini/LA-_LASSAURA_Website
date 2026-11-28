@props([
    'name',
    'label',
    'value' => '1',
    'checked' => false,
    'id' => null,
])

@php
    $fieldId = $id ?? $name;
@endphp

<label
    for="{{ $fieldId }}"
    class="admin-form-checkbox-label"
>
    <input
        id="{{ $fieldId }}"
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked($checked)

        {{ $attributes->class([
            'admin-form-checkbox',
        ]) }}
    >

    <span>{{ $label }}</span>
</label>