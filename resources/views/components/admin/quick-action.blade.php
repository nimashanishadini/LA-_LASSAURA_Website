
@props([
    'route',
    'icon',
    'label',
    'description' => '',
])

<a href="{{ route($route) }}"
   class="admin-quick-action">

    <div class="admin-quick-action-icon">
        <x-admin.icon :name="$icon" />
    </div>

    <div class="admin-quick-action-content">

        <span class="admin-quick-action-label">
            {{ $label }}
        </span>

        @if($description)
            <span class="admin-quick-action-description">
                {{ $description }}
            </span>
        @endif

    </div>

    <span class="admin-quick-action-arrow"
          aria-hidden="true">
        →
    </span>

</a>
