@props([
    'align' => 'left',
])

<td
    {{ $attributes->class([
        'admin-data-table-cell',
        'admin-table-align-'.$align,
    ]) }}
>
    {{ $slot }}
</td>