
@props([
    'label',
    'value',
    'icon',
    'description' => '',
    'tone' => 'gold',
])

<article class="admin-stat-card">

    <div class="admin-stat-header">

        <p class="admin-stat-label">
            {{ $label }}
        </p>

        <div @class([
            'admin-stat-icon',
            'admin-tone-' . $tone
        ])>
            <x-admin.icon :name="$icon" />
        </div>

    </div>

    <p class="admin-stat-value">
        {{ number_format($value) }}
    </p>

    @if($description)
        <p class="admin-stat-description">
            {{ $description }}
        </p>
    @endif

</article>
