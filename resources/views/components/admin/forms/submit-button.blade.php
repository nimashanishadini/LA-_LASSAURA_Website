@props([
    'label' => 'Save changes',
    'loadingLabel' => 'Saving...',
])

<x-admin.forms.button
    type="submit"
    x-bind:disabled="submitting"
    {{ $attributes }}
>
    <span
        x-cloak
        x-show="submitting"
        class="admin-loading-spinner"
        aria-hidden="true"
    ></span>

    <span
        x-text="submitting
            ? @js($loadingLabel)
            : @js($label)"
    >
        {{ $label }}
    </span>
</x-admin.forms.button>