@props([
    'title',
    'description' => null,
])

<section {{ $attributes->class(['admin-form-section']) }}>

    <div class="admin-form-section-heading">

        <h2 class="admin-form-section-title">
            {{ $title }}
        </h2>

        @if($description)
            <p class="admin-form-section-description">
                {{ $description }}
            </p>
        @endif

    </div>

    <div class="admin-form-section-content">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="admin-form-section-footer">
            {{ $footer }}
        </div>
    @endisset

</section>