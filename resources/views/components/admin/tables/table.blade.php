@props([
    'label' => 'Data table',
])

<div class="admin-data-table-card">

    <div
        class="admin-data-table-scroll"
        role="region"
        aria-label="{{ $label }}"
        tabindex="0"
    >

        <table {{ $attributes->class(['admin-data-table']) }}>

            <caption class="admin-table-sr-only">
                {{ $label }}
            </caption>

            <thead>
                <tr>
                    {{ $head }}
                </tr>
            </thead>

            <tbody>
                {{ $slot }}
            </tbody>

        </table>

    </div>

    @isset($footer)

        <div class="admin-data-table-footer">
            {{ $footer }}
        </div>

    @endisset

</div>