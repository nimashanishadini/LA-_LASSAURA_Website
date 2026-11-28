@props([
    'id',
    'title' => 'Details',
])

<dialog
    id="{{ $id }}"
    class="admin-detail-modal"
    aria-labelledby="{{ $id }}-title"
    x-data
    @click.self="$el.close()"
>

    <div class="admin-modal-inner">

        <header class="admin-modal-header">

            <h2 id="{{ $id }}-title">
                {{ $title }}
            </h2>

            <button
                type="button"
                class="admin-modal-close"
                aria-label="Close dialog"
                @click="$el.closest('dialog').close()"
                autofocus
            >

                <svg
                    width="22"
                    height="22"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path d="m18 6-12 12M6 6l12 12"/>
                </svg>

            </button>

        </header>

        <div class="admin-modal-body">

            {{ $slot }}

        </div>

    </div>

</dialog>