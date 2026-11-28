@extends('layouts.admin')

@section('title', $room->exists ? 'Edit '.$room->name : 'Add room')

@section('content')

    <!-- Back navigation -->

    <div class="mb-6">
        <a
            href="{{ route('admin.rooms.index') }}"
            class="admin-room-back"
        >
            <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>

            All rooms
        </a>
    </div>

    <!-- Room details form -->

    <form
        method="POST"
        action="{{ $room->exists
            ? route('admin.rooms.update', $room)
            : route('admin.rooms.store') }}"
        x-data="{ submitting: false }"
        @submit="submitting = true"
        @pageshow.window="submitting = false"
        :aria-busy="submitting"
    >
        @csrf

        @if($room->exists)
            @method('PUT')
        @endif

        <x-admin.forms.section
            title="Room details"
            description="Manage room information, guest capacity and website visibility."
        >

            <div class="admin-form-grid">

                <x-admin.forms.input
                    name="name"
                    id="room-name"
                    label="Room name"
                    :value="$room->name"
                    maxlength="120"
                    placeholder="Enter room name"
                    :required="true"
                />

                <x-admin.forms.input
                    name="slug"
                    id="room-slug"
                    label="URL slug"
                    :value="$room->slug"
                    maxlength="120"
                    placeholder="coral-double"
                    help="The unique address used on the website."
                    :required="true"
                />

                <x-admin.forms.select
                    name="category"
                    id="room-category"
                    label="Category"
                    :options="[
                        'family' => 'Family room',
                        'double' => 'Double room'
                    ]"
                    :value="$room->category"
                    placeholder="Select category"
                    :required="true"
                />

                <x-admin.forms.select
                    name="is_active"
                    id="room-status"
                    label="Website status"
                    :options="[
                        '0' => 'Draft',
                        '1' => 'Published'
                    ]"
                    :value="(int) $room->is_active"
                    help="Published rooms can appear on the website."
                />

            </div>

            <!-- Guest capacity -->

            <div class="admin-room-form-group">

                <h3 class="admin-room-group-title">
                    Guest capacity
                </h3>

                <p class="admin-room-group-description">
                    Configure the maximum number of guests allowed
                    in this room.
                </p>

                <div class="admin-room-capacity-grid">

                    <x-admin.forms.input
                        type="number"
                        name="max_adults"
                        id="room-max-adults"
                        label="Maximum adults"
                        :value="$room->max_adults"
                        min="1"
                        max="100"
                        placeholder="Not configured"
                    />

                    <x-admin.forms.input
                        type="number"
                        name="max_children"
                        id="room-max-children"
                        label="Maximum children"
                        :value="$room->max_children"
                        min="0"
                        max="100"
                        placeholder="Not configured"
                    />

                    <x-admin.forms.input
                        type="number"
                        name="max_guests"
                        id="room-max-guests"
                        label="Maximum total guests"
                        :value="$room->max_guests"
                        min="1"
                        max="100"
                        placeholder="Not configured"
                    />

                </div>

                <p class="admin-form-help mt-3">
                    Leave unknown limits blank.
                    Configure all limits before publishing.
                </p>

            </div>

            <!-- Room descriptions -->

            <div class="admin-room-form-group">

                <h3 class="admin-room-group-title">
                    Room descriptions
                </h3>

                <p class="admin-room-group-description">
                    Information displayed to guests on the website.
                </p>

                <div class="space-y-5">

                    <x-admin.forms.textarea
                        name="short_description"
                        id="room-short-description"
                        label="Short description"
                        :value="$room->short_description"
                        :rows="3"
                        maxlength="500"
                        placeholder="Write a short introduction..."
                        help="Maximum 500 characters."
                    />

                    <x-admin.forms.textarea
                        name="description"
                        id="room-description"
                        label="Description"
                        :value="$room->description"
                        :rows="6"
                        maxlength="10000"
                        placeholder="Describe the room, facilities and guest experience..."
                        help="Maximum 10,000 characters."
                    />

                </div>

            </div>

            <!-- Amenities -->

            <fieldset class="admin-room-form-group">

                <legend class="admin-room-group-title">
                    Amenities
                </legend>

                <p class="admin-room-group-description">
                    Select the facilities available in this room.
                </p>

                @php
                    $selectedAmenities = session()->hasOldInput()
                        ? (array) old('amenities', [])
                        : $room->amenities->modelKeys();
                @endphp

                <div class="admin-room-amenities-grid">

                    @forelse($amenities as $amenity)

                        <div class="admin-room-amenity-item">

                            <x-admin.forms.checkbox
                                name="amenities[]"
                                :id="'room-amenity-'.$amenity->id"
                                :label="$amenity->name"
                                :value="$amenity->id"
                                :checked="in_array(
                                    $amenity->id,
                                    $selectedAmenities
                                )"
                            />

                        </div>

                    @empty

                        <p class="admin-form-help">
                            No amenities configured.
                        </p>

                    @endforelse

                </div>

                <x-admin.forms.field-error name="amenities" />

                <x-admin.forms.field-error name="amenities.*" />

            </fieldset>

            <!-- Form actions -->

            <x-slot:footer>

                <x-admin.forms.button
                    variant="secondary"
                    :href="route('admin.rooms.index')"
                >
                    Cancel
                </x-admin.forms.button>

                <x-admin.forms.button
                    type="submit"
                    :disabled="false"
                    x-bind:disabled="submitting"
                >
                    <svg
                        x-cloak
                        x-show="submitting"
                        class="admin-form-spinner"
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                            stroke="currentColor"
                            stroke-width="3"
                            opacity=".25"
                        />

                        <path
                            d="M12 3a9 9 0 0 1 9 9"
                            stroke="currentColor"
                            stroke-width="3"
                            stroke-linecap="round"
                        />
                    </svg>

                    <span
                        x-text="submitting
                            ? 'Saving room...'
                            : 'Save room'"
                    >
                        Save room
                    </span>
                </x-admin.forms.button>

            </x-slot:footer>

        </x-admin.forms.section>

    </form>

    <!-- Room photographs -->

    @if($room->exists)

        <div class="mt-7">

            <x-admin.forms.section
                title="Room photographs"
                description="Manage the photographs displayed on the room page."
            >

                <div class="admin-room-upload-info">

                    <p>
                        JPEG, PNG or WebP · up to 30 MB per image ·
                        select up to 10 images.
                    </p>

                    <p>
                        Photographs upload one at a time.
                        Lower display order appears first.
                    </p>

                </div>

                <!-- Upload images -->

                <form
                    method="POST"
                    action="{{ route('admin.rooms.images.store', $room) }}"
                    enctype="multipart/form-data"
                    x-data="roomPhotoUpload"
                    @submit.prevent="upload($event)"
                    class="admin-room-upload-form"
                    :aria-busy="uploading"
                >
                    @csrf

                    <x-admin.forms.input
                        type="file"
                        name="images[]"
                        id="room-photographs"
                        label="Select photographs"
                        x-ref="photos"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        :required="true"
                        :use-old="false"
                    />

                    <x-admin.forms.button
                        type="submit"
                        x-bind:disabled="uploading"
                    >
                        <span
                            x-text="uploading
                                ? progress
                                : 'Upload images'"
                        >
                            Upload images
                        </span>
                    </x-admin.forms.button>

                    <p
                        x-cloak
                        x-show="uploadError"
                        x-text="uploadError"
                        role="alert"
                        class="admin-field-error admin-form-full"
                    ></p>

                    <x-admin.forms.field-error name="images" />

                    <x-admin.forms.field-error name="images.*" />

                </form>

                <!-- Existing photographs -->

                <div class="admin-room-photo-grid">

                    @forelse($room->media as $image)

                        <article class="admin-room-photo-card">

                            <div class="admin-room-photo-preview">

                                <img
                                    src="{{ $image->url }}"
                                    alt="{{ $image->alt_text }}"
                                    loading="lazy"
                                >

                                @if($image->is_primary)
                                    <span class="admin-room-cover-badge">
                                        Cover image
                                    </span>
                                @endif

                            </div>

                            <!-- Update image -->

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.rooms.images.update',
                                    [$room, $image]
                                ) }}"
                                class="admin-room-photo-form"
                                x-data="{ saving: false }"
                                @submit="saving = true"
                                @pageshow.window="saving = false"
                            >
                                @csrf
                                @method('PATCH')

                                <x-admin.forms.input
                                    name="alt_text"
                                    :id="'image-alt-'.$image->id"
                                    label="Image description"
                                    :value="$image->alt_text"
                                    maxlength="255"
                                    :use-old="false"
                                    :show-errors="false"
                                />

                                <x-admin.forms.input
                                    type="number"
                                    name="sort_order"
                                    :id="'image-order-'.$image->id"
                                    label="Display order"
                                    :value="$image->sort_order"
                                    min="0"
                                    max="10000"
                                    :required="true"
                                    :use-old="false"
                                    :show-errors="false"
                                />

                                <x-admin.forms.checkbox
                                    name="is_primary"
                                    :id="'image-primary-'.$image->id"
                                    label="Cover image"
                                    value="1"
                                    :checked="$image->is_primary"
                                />

                                <x-admin.forms.button
                                    type="submit"
                                    class="w-full"
                                    x-bind:disabled="saving"
                                >
                                    <span
                                        x-text="saving
                                            ? 'Saving...'
                                            : 'Save image'"
                                    >
                                        Save image
                                    </span>
                                </x-admin.forms.button>

                            </form>

                            <!-- Remove image -->

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.rooms.images.destroy',
                                    [$room, $image]
                                ) }}"
                                class="admin-room-photo-delete"
                                x-data="{ removing: false }"
                                @pageshow.window="removing = false"
                                @submit="
                                    if (!confirm('Remove this room photograph?')) {
                                        $event.preventDefault();
                                    } else {
                                        removing = true;
                                    }
                                "
                            >
                                @csrf
                                @method('DELETE')

                                <x-admin.forms.button
                                    type="submit"
                                    variant="danger"
                                    class="w-full"
                                    x-bind:disabled="removing"
                                >
                                    <span
                                        x-text="removing
                                            ? 'Removing...'
                                            : 'Remove image'"
                                    >
                                        Remove image
                                    </span>
                                </x-admin.forms.button>

                            </form>

                        </article>

                    @empty

                        <div class="admin-room-photo-empty">

                            <svg
                                width="40"
                                height="40"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="2"
                                />

                                <circle cx="8.5" cy="8.5" r="1.5"/>

                                <path d="m21 15-5-5L5 21"/>
                            </svg>

                            <h3>No photographs yet</h3>

                            <p>
                                Upload photographs of
                                {{ $room->name }} to begin.
                            </p>

                        </div>

                    @endforelse

                </div>

            </x-admin.forms.section>

        </div>

    @endif

@endsection