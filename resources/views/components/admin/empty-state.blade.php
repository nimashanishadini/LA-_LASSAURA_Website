
@props([
    'icon' => 'rooms',
    'title' => 'No records found',
    'description' => '',
])

<div class="admin-empty-state">

    <div class="admin-empty-icon">
        <x-admin.icon :name="$icon" />
    </div>

    <p class="admin-empty-title">
        {{ $title }}
    </p>

    @if($description)
        <p class="admin-empty-description">
            {{ $description }}
        </p>
    @endif

    {{ $slot }}

</div>
