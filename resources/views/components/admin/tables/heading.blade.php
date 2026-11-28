@props([
    'align' => 'left',
])

<th
    scope="col"
    {{ $attributes->class([
        'admin-data-table-heading',
        'admin-table-align-'.$align,
    ]) }}
>
    {{ $slot }}
</th>