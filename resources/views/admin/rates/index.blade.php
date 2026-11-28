@extends('layouts.admin')

@section('title', 'Rates & currencies')

@section('content')

    @php
        $isExchangeForm = old('_rates_form') === 'exchange';

        $isCreateForm = old('_rates_form') === 'rate'
            && (string) old('_rate_id', '') === '';
    @endphp

    <!-- Exchange rate overview -->

    <div class="mb-7">

        <x-admin.forms.section
            title="LKR → USD display"
            description="Manage the exchange rate used to display prices in USD."
        >

            @if($fx)

                <div class="admin-fx-summary">

                    <div class="admin-fx-label">
                        Current exchange rate
                    </div>

                    <div class="admin-fx-value">
                        1 USD =
                        <strong>{{ $fx->lkr_per_usd }} LKR</strong>
                    </div>

                    <p class="admin-fx-meta">
                        {{ $fx->source }} ·
                        {{ $fx->fetched_at
                            ->timezone('Asia/Colombo')
                            ->format('d M Y, H:i') }}
                        Sri Lanka time
                    </p>

                </div>

                @if($fx->isStale())

                    <div
                        class="admin-feedback admin-feedback-warning"
                        role="status"
                    >
                        This rate is over 24 hours old.
                        Public prices display a stale-rate notice.
                    </div>

                @endif

            @else

                <div
                    class="admin-feedback admin-feedback-warning"
                    role="status"
                >
                    No valid exchange rate recorded.
                    Public prices show LKR only.
                </div>

            @endif

            <div class="admin-rates-toolbar">

                <x-admin.forms.button
                    type="button"
                    @click="document.getElementById(
                        'exchange-rate-modal'
                    ).showModal()"
                    aria-haspopup="dialog"
                    aria-controls="exchange-rate-modal"
                >
                    Update exchange rate
                </x-admin.forms.button>

                <x-admin.forms.button
                    type="button"
                    variant="secondary"
                    @click="document.getElementById(
                        'exchange-history-modal'
                    ).showModal()"
                    aria-haspopup="dialog"
                    aria-controls="exchange-history-modal"
                >
                    View history
                </x-admin.forms.button>

            </div>

        </x-admin.forms.section>

    </div>

    <!-- Configured rate rules -->

    <section>

        <div class="admin-table-page-heading">

            <div>

                <h2>Configured rate rules</h2>

                <p>
                    View and manage existing room pricing rules.
                </p>

            </div>

            @if($rooms->isNotEmpty())

                <x-admin.forms.button
                    type="button"
                    @click="document.getElementById(
                        'create-rate-modal'
                    ).showModal()"
                    aria-haspopup="dialog"
                    aria-controls="create-rate-modal"
                >

                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path d="M12 5v14M5 12h14"/>
                    </svg>

                    Add rate

                </x-admin.forms.button>

            @endif

        </div>

        @if($rooms->isEmpty())

            <div class="admin-feedback admin-feedback-warning mb-5">

                Add a room before configuring rates.

                @can('manage-rooms')

                    <x-admin.forms.button
                        :href="route('admin.rooms.index')"
                        variant="secondary"
                    >
                        Manage rooms
                    </x-admin.forms.button>

                @endcan

            </div>

        @endif

        <!-- Rate rules table -->

        <x-admin.tables.table label="Configured room rate rules">

            <x-slot:head>

                <x-admin.tables.heading>
                    Room / Rule
                </x-admin.tables.heading>

                <x-admin.tables.heading>
                    Nightly price
                </x-admin.tables.heading>

                <x-admin.tables.heading>
                    Date range
                </x-admin.tables.heading>

                <x-admin.tables.heading>
                    Discount
                </x-admin.tables.heading>

                <x-admin.tables.heading>
                    Priority
                </x-admin.tables.heading>

                <x-admin.tables.heading>
                    Status
                </x-admin.tables.heading>

                <x-admin.tables.heading align="right">
                    Action
                </x-admin.tables.heading>

            </x-slot:head>

            @forelse($rates as $rate)

                <tr>

                    <!-- Room and rule -->

                    <x-admin.tables.cell>

                        <div class="admin-rate-table-name">

                            <strong>
                                {{ $rate->villa->name }}
                            </strong>

                            <span>{{ $rate->name }}</span>

                            <small>
                                {{ match($rate->days) {
                                    'weekday' => 'Weekdays',
                                    'weekend' => 'Weekends',
                                    default => 'Every day'
                                } }}
                            </small>

                        </div>

                    </x-admin.tables.cell>

                    <!-- Nightly price -->

                    <x-admin.tables.cell>

                        <span class="admin-rate-table-price">

                            LKR {{ number_format(
                                (float) $rate->nightly_lkr,
                                2
                            ) }}

                        </span>

                    </x-admin.tables.cell>

                    <!-- Date range -->

                    <x-admin.tables.cell>

                        <div class="admin-table-date">

                            <strong>
                                {{ $rate->starts_on?->format('d M Y') }}
                            </strong>

                            <span>
                                to {{ $rate->ends_on?->format('d M Y') }}
                            </span>

                        </div>

                    </x-admin.tables.cell>

                    <!-- Discount -->

                    <x-admin.tables.cell>

                        {{ number_format(
                            (float) $rate->discount_percent,
                            2
                        ) }}%

                    </x-admin.tables.cell>

                    <!-- Priority -->

                    <x-admin.tables.cell>

                        <span class="admin-rate-table-priority">
                            {{ $rate->priority }}
                        </span>

                    </x-admin.tables.cell>

                    <!-- Status -->

                    <x-admin.tables.cell>

                        <span class="admin-rate-status
                            {{ $rate->is_active
                                ? 'is-active'
                                : 'is-inactive' }}">

                            {{ $rate->is_active
                                ? 'Active'
                                : 'Inactive' }}

                        </span>

                    </x-admin.tables.cell>

                    <!-- Edit button -->

                    <x-admin.tables.cell align="right">

                        <button
                            type="button"
                            class="admin-table-view-button"
                            aria-label="Edit rate {{ $rate->name }}"
                            aria-haspopup="dialog"
                            aria-controls="edit-rate-{{ $rate->id }}"
                            @click="document.getElementById(
                                'edit-rate-{{ $rate->id }}'
                            ).showModal()"
                        >

                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path d="m16 3 5 5M4 20l4-1
                                    L21 6l-4-4L4 15l-1 6Z"/>
                            </svg>

                            Edit

                        </button>

                    </x-admin.tables.cell>

                </tr>

            @empty

                <x-admin.tables.empty
                    :colspan="7"
                    title="No rates configured"
                    description="Add the property's agreed room rates to get started."
                />

            @endforelse

        </x-admin.tables.table>

    </section>

    <!-- Exchange rate update modal -->

    <x-admin.modal
        id="exchange-rate-modal"
        title="Update exchange rate"
    >

        <form
            method="POST"
            action="{{ route('admin.rates.exchange') }}"
            class="admin-management-form"
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

            <input
                type="hidden"
                name="_rates_form"
                value="exchange"
            >

            <x-admin.forms.input
                type="number"
                name="lkr_per_usd"
                id="exchange-rate"
                label="LKR per 1 USD"
                min="0.000001"
                max="99999999.999999"
                step="0.000001"
                placeholder="Enter exchange rate"
                :required="true"
                :use-old="$isExchangeForm"
                :show-errors="$isExchangeForm"
            />

            <x-admin.forms.textarea
                name="reason"
                id="exchange-reason"
                label="Reason for override"
                maxlength="500"
                :rows="3"
                placeholder="Enter reason"
                :required="true"
                :use-old="$isExchangeForm"
                :show-errors="$isExchangeForm"
            />

            <p class="admin-form-help">

                Records a new exchange-rate snapshot with your
                user ID. The next successful provider refresh
                replaces the override for new quotes.
                Settlement is LKR.

            </p>

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
                    label="Record exchange rate"
                    loading-label="Recording..."
                />

            </div>

        </form>

    </x-admin.modal>

    <!-- Exchange rate history modal -->

    <x-admin.modal
        id="exchange-history-modal"
        title="Exchange rate history"
    >

        <x-admin.tables.table
            label="Recent exchange rate history"
            class="admin-fx-history-table"
        >

            <x-slot:head>

                <x-admin.tables.heading>
                    LKR / USD
                </x-admin.tables.heading>

                <x-admin.tables.heading>
                    Source
                </x-admin.tables.heading>

                <x-admin.tables.heading>
                    Recorded
                </x-admin.tables.heading>

                <x-admin.tables.heading>
                    Actor
                </x-admin.tables.heading>

            </x-slot:head>

            @forelse($history as $entry)

                <tr>

                    <x-admin.tables.cell>
                        {{ $entry->lkr_per_usd }}
                    </x-admin.tables.cell>

                    <x-admin.tables.cell>
                        {{ $entry->source }}
                    </x-admin.tables.cell>

                    <x-admin.tables.cell>
                        {{ $entry->fetched_at
                            ->timezone('Asia/Colombo')
                            ->format('d M Y H:i') }}
                    </x-admin.tables.cell>

                    <x-admin.tables.cell>
                        {{ $entry->recorded_by
                            ? 'Staff #'.$entry->recorded_by
                            : 'Provider' }}
                    </x-admin.tables.cell>

                </tr>

            @empty

                <x-admin.tables.empty
                    :colspan="4"
                    title="No exchange rate history"
                    description="Recorded exchange rates will appear here."
                />

            @endforelse

        </x-admin.tables.table>

        <div class="admin-modal-actions">

            <x-admin.forms.button
                type="button"
                variant="secondary"
                @click="$el.closest('dialog').close()"
            >
                Close
            </x-admin.forms.button>

        </div>

    </x-admin.modal>

    <!-- Add rate modal -->

    @if($rooms->isNotEmpty())

        <x-admin.modal
            id="create-rate-modal"
            title="Add room rate"
        >

            <div class="admin-feedback admin-feedback-info">

                Set a base rule (priority 0), then higher-priority
                weekend/seasonal rules. The selected rule's
                discount applies once; discounts do not stack.
                Dates refer to occupied nights.

            </div>

            <form
                method="POST"
                action="{{ route('admin.rates.store') }}"
                class="admin-management-form"
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

                <input
                    type="hidden"
                    name="_rates_form"
                    value="rate"
                >

                <input
                    type="hidden"
                    name="_rate_id"
                    value=""
                >

                <x-admin.rate-fields
                    :rate="new \App\Models\VillaRate"
                    :rooms="$rooms"
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
                        label="Add rate rule"
                        loading-label="Adding rate rule..."
                    />

                </div>

            </form>

        </x-admin.modal>

    @endif

    <!-- Edit rate modals -->

    @foreach($rates as $rate)

        <x-admin.modal
            :id="'edit-rate-'.$rate->id"
            title="Edit room rate"
        >

            <div class="admin-rate-modal-summary">

                <strong>{{ $rate->villa->name }}</strong>

                <span>{{ $rate->name }}</span>

            </div>

            <form
                method="POST"
                action="{{ route('admin.rates.update', $rate) }}"
                class="admin-management-form"
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
                @method('PUT')

                <input
                    type="hidden"
                    name="_rates_form"
                    value="rate"
                >

                <input
                    type="hidden"
                    name="_rate_id"
                    value="{{ $rate->id }}"
                >

                <x-admin.rate-fields
                    :rate="$rate"
                    :rooms="$rooms"
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
                        label="Save rate rule"
                        loading-label="Saving rate rule..."
                    />

                </div>

            </form>

        </x-admin.modal>

    @endforeach

    <!-- Reopen the correct modal after validation errors -->

    @if($errors->any())

        @if($isExchangeForm)

            <span
                hidden
                x-init="$nextTick(() => {
                    document.getElementById(
                        'exchange-rate-modal'
                    )?.showModal()
                })"
            ></span>

        @elseif($isCreateForm && $rooms->isNotEmpty())

            <span
                hidden
                x-init="$nextTick(() => {
                    document.getElementById(
                        'create-rate-modal'
                    )?.showModal()
                })"
            ></span>

        @elseif(old('_rates_form') === 'rate')

            @foreach($rates as $rate)

                @if((string) old('_rate_id') === (string) $rate->id)

                    <span
                        hidden
                        x-init="$nextTick(() => {
                            document.getElementById(
                                'edit-rate-{{ $rate->id }}'
                            )?.showModal()
                        })"
                    ></span>

                @endif

            @endforeach

        @endif

    @endif

@endsection