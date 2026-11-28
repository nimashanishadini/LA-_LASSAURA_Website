@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'id' => null,
    'required' => false,
    'help' => null,
    'useOld' => true,
    'showErrors' => true,
])

@php
    $fieldId = $id ?? $name;
    $hasError = $showErrors && $errors->has($name);
    $fieldValue = $useOld ? old($name, $value) : $value;

    $describedBy = trim(
        ($help ? $fieldId . '-help ' : '') .
        ($hasError ? $fieldId . '-error' : '')
    );
@endphp

<div class="admin-form-field">

    <label
        for="{{ $fieldId }}"
        class="admin-form-label"
    >
        {{ $label }}

        @if($required)
            <span class="admin-required" aria-hidden="true">*</span>
        @endif
    </label>

    <input
        id="{{ $fieldId }}"
        name="{{ $name }}"
        type="{{ $type }}"

        @unless(in_array($type, ['file', 'password']))
            value="{{ $fieldValue }}"
        @endunless

        @required($required)

        aria-invalid="{{ $hasError ? 'true' : 'false' }}"

        @if($describedBy)
            aria-describedby="{{ $describedBy }}"
        @endif

        {{ $attributes->class([
            'admin-form-control',
            'admin-form-invalid' => $hasError,
        ]) }}
    >

    @if($help)
        <p
            id="{{ $fieldId }}-help"
            class="admin-form-help"
        >
            {{ $help }}
        </p>
    @endif

    <x-admin.forms.field-error
        :name="$name"
        :id="$fieldId . '-error'"
        :show-errors="$showErrors"
    />

</div>