@extends('layouts.admin')

@section('title', 'Guest feedback')

@section('content')

    <!-- Overview -->

    <div class="admin-table-overview">

        <div class="admin-table-overview-item">

            <span>Total reviews</span>

            <strong>{{ $reviews->total() }}</strong>

        </div>

        <div class="admin-table-overview-item">

            <span>Pending reviews</span>

            <strong class="admin-table-gold">
                {{ $pendingCount }}
            </strong>

        </div>

    </div>

    <!-- Table heading -->

    <div class="admin-table-page-heading">

        <div>

            <h2>All guest reviews</h2>

            <p>
                Manage and moderate guest feedback.
                Only approved reviews appear on the website.
            </p>

        </div>

    </div>

    <!-- Reviews table -->

    <x-admin.tables.table label="Guest reviews">

        <x-slot:head>

            <x-admin.tables.heading>
                Guest
            </x-admin.tables.heading>

            <x-admin.tables.heading>
                Rating
            </x-admin.tables.heading>

            <x-admin.tables.heading>
                Review
            </x-admin.tables.heading>

            <x-admin.tables.heading>
                Date
            </x-admin.tables.heading>

            <x-admin.tables.heading>
                Status
            </x-admin.tables.heading>

            <x-admin.tables.heading align="right">
                Action
            </x-admin.tables.heading>

        </x-slot:head>

        @forelse($reviews as $review)

            <tr>

                <!-- Guest -->

                <x-admin.tables.cell>

                    <div class="admin-table-person">

                        <div
                            class="admin-table-avatar"
                            aria-hidden="true"
                        >
                            {{ mb_strtoupper(
                                mb_substr($review->name, 0, 1)
                            ) }}
                        </div>

                        <div class="admin-table-person-info">

                            <strong>
                                {{ $review->name }}
                            </strong>

                            <span>
                                {{ $review->email }}
                            </span>

                        </div>

                    </div>

                </x-admin.tables.cell>

                <!-- Rating -->

                <x-admin.tables.cell>

                    <span class="admin-table-rating">

                        <span aria-hidden="true">★</span>

                        {{ $review->rating }} / 5

                    </span>

                </x-admin.tables.cell>

                <!-- Review preview -->

                <x-admin.tables.cell>

                    <div class="admin-table-comment">

                        {{ \Illuminate\Support\Str::limit(
                            $review->comment,
                            65
                        ) }}

                    </div>

                </x-admin.tables.cell>

                <!-- Date -->

                <x-admin.tables.cell>

                    <div class="admin-table-date">

                        <strong>
                            {{ $review->created_at
                                ->timezone('Asia/Colombo')
                                ->format('d M Y') }}
                        </strong>

                        <span>
                            {{ $review->created_at
                                ->timezone('Asia/Colombo')
                                ->format('H:i') }}
                        </span>

                    </div>

                </x-admin.tables.cell>

                <!-- Status -->

                <x-admin.tables.cell>

                    <span class="admin-message-status
                        status-{{ $review->status }}">

                        {{ ucfirst($review->status) }}

                    </span>

                </x-admin.tables.cell>

                <!-- Action -->

                <x-admin.tables.cell align="right">

                    <button
                        type="button"
                        class="admin-table-view-button"
                        aria-label="View review by {{ $review->name }}"
                        aria-haspopup="dialog"
                        aria-controls="review-modal-{{ $review->id }}"
                        @click="document.getElementById(
                            'review-modal-{{ $review->id }}'
                        ).showModal()"
                    >

                        <svg
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <path d="M2 12s3.5-7 10-7
                                10 7 10 7-3.5 7-10 7
                                -10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>

                        View

                    </button>

                </x-admin.tables.cell>

            </tr>

        @empty

            <x-admin.tables.empty
                :colspan="6"
                title="No feedback submitted yet"
                description="Guest reviews will appear here for moderation."
            />

        @endforelse

        <x-slot:footer>

            {{ $reviews->links() }}

        </x-slot:footer>

    </x-admin.tables.table>

    <!-- Review detail modals -->

    @foreach($reviews as $review)

        @php
            $isCurrentReview =
                (string) old('_review_id') ===
                (string) $review->id;
        @endphp

        <x-admin.modal
            :id="'review-modal-'.$review->id"
            title="Review details"
        >

            <!-- Guest information -->

            <div class="admin-modal-guest">

                <div
                    class="admin-review-avatar"
                    aria-hidden="true"
                >
                    {{ mb_strtoupper(
                        mb_substr($review->name, 0, 1)
                    ) }}
                </div>

                <div class="admin-review-person">

                    <h2>{{ $review->name }}</h2>

                    <p>{{ $review->email }}</p>

                    <p>
                        {{ $review->created_at
                            ->timezone('Asia/Colombo')
                            ->format('d M Y H:i') }}
                    </p>

                </div>

                <span class="admin-message-status
                    status-{{ $review->status }}">

                    {{ ucfirst($review->status) }}

                </span>

            </div>

            <!-- Rating -->

            <div class="admin-review-rating">

                <div
                    class="admin-review-stars"
                    aria-hidden="true"
                >

                    @for($star = 1; $star <= 5; $star++)

                        <svg
                            width="21"
                            height="21"
                            viewBox="0 0 24 24"
                            fill="{{ $star <= $review->rating
                                ? 'currentColor'
                                : 'none' }}"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="m12 3 2.8 5.7 6.2.9
                                -4.5 4.4 1.1 6.2
                                -5.6-3-5.6 3 1.1-6.2
                                L3 9.6l6.2-.9L12 3Z"/>
                        </svg>

                    @endfor

                </div>

                <span>
                    {{ $review->rating }} / 5 stars
                </span>

            </div>

            <!-- Full review -->

            <div class="admin-message-body">

                <h3>Guest review</h3>

                <p>{{ $review->comment }}</p>

            </div>

            <!-- Moderation form -->

            <form
                method="POST"
                action="{{ route('admin.reviews.update', $review) }}"
                x-data="{ submitting: false }"
                @submit="
                    if (submitting) {
                        $event.preventDefault();
                    } else {
                        submitting = true;
                    }
                "
                @pageshow.window="submitting = false"
                :aria-busy="submitting"
            >

                @csrf
                @method('PATCH')

                <input
                    type="hidden"
                    name="_review_id"
                    value="{{ $review->id }}"
                >

                <x-admin.forms.select
                    name="status"
                    :id="'review-status-'.$review->id"
                    label="Moderation status"
                    :options="[
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected'
                    ]"
                    :value="$isCurrentReview
                        ? old('status', $review->status)
                        : $review->status"
                    :use-old="false"
                    :show-errors="$isCurrentReview"
                    :required="true"
                />

                <div class="admin-modal-actions">

                    <x-admin.forms.button
                        type="button"
                        variant="secondary"
                        x-bind:disabled="submitting"
                        @click="$el.closest('dialog').close()"
                    >
                        Cancel
                    </x-admin.forms.button>

                    <x-admin.forms.submit-button
                        label="Save decision"
                        loading-label="Saving decision..."
                    />

                </div>

            </form>

        </x-admin.modal>

        @if($isCurrentReview && $errors->any())

            <span
                hidden
                x-init="$nextTick(() => {
                    document.getElementById(
                        'review-modal-{{ $review->id }}'
                    ).showModal()
                })"
            ></span>

        @endif

    @endforeach

@endsection