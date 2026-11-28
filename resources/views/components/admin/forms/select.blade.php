@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'id' => null,
    'placeholder' => null,
    'required' => false,
    'help' => null,
    'useOld' => true,
    'showErrors' => true,
])

@php
    $fieldId = $id ?? $name;
    $hasError = $showErrors && $errors->has($name);
    $selectedValue = $useOld ? old($name, $value) : $value;

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

    <select
        id="{{ $fieldId }}"
        name="{{ $name }}"
        @required($required)

        aria-invalid="{{ $hasError ? 'true' : 'false' }}"

        @if($describedBy)
            aria-describedby="{{ $describedBy }}"
        @endif

        {{ $attributes->class([
            'admin-form-control',
            'admin-form-select',
            'admin-form-invalid' => $hasError,
        ]) }}
    >

        @if($placeholder !== null)
            <option value="">
                {{ $placeholder }}
            </option>
        @endif

        @foreach($options as $optionValue => $optionLabel)
            <option
                value="{{ $optionValue }}"
                @selected((string) $selectedValue === (string) $optionValue)
            >
                {{ $optionLabel }}
            </option>
        @endforeach

    </select>

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