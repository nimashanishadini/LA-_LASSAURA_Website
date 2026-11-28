@props([
    'name',
    'id' => null,
    'showErrors' => true,
])

@if($showErrors && $errors->has($name))
    <div
        id="{{ $id ?? $name . '-error' }}"
        class="admin-field-error"
        role="alert"
    >
        @foreach($errors->get($name) as $message)
            <p>{{ $message }}</p>
        @endforeach
    </div>
@endif