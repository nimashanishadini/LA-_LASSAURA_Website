@props([
    'name',
    'label',
    'value' => null,
    'id' => null,
    'rows' => 4,
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

    <textarea
        id="{{ $fieldId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @required($required)

        aria-invalid="{{ $hasError ? 'true' : 'false' }}"

        @if($describedBy)
            aria-describedby="{{ $describedBy }}"
        @endif

        {{ $attributes->class([
            'admin-form-control',
            'admin-form-textarea',
            'admin-form-invalid' => $hasError,
        ]) }}
    >{{ $fieldValue }}</textarea>

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