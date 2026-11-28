@extends('layouts.admin')

@section('title', 'Room calendar')

@section('content')

    @php
        $today = now('Asia/Colombo')->toDateString();

        $tomorrow = now('Asia/Colombo')
            ->addDay()
            ->toDateString();

        $roomOptions = $rooms->pluck('name', 'id')->all();
    @endphp

    <div class="admin-calendar-page">

        <!-- Calendar -->

        <section class="admin-calendar-card">

            <div class="admin-calendar-header">

                <div>

                    <h2>Availability & maintenance</h2>

                    <p>
                        View confirmed stays and maintenance dates.
                        All stay dates use Sri Lanka time.
                        The end date is available for a new arrival.
                    </p>

                </div>

                <div class="admin-calendar-filter">

                    <x-admin.forms.select
                        name="calendar_room"
                        id="calendar-room"
                        label="Filter by villa"
                        placeholder="All villas"
                        :options="$roomOptions"
                        :use-old="false"
                        :show-errors="false"
                    />

                </div>

            </div>

            <!-- Calendar legend -->

            <div class="admin-calendar-legend">

                <span class="admin-calendar-legend-item">

                    <span
                        class="admin-calendar-dot confirmed"
                        aria-hidden="true"
                    ></span>

                    Confirmed stay

                </span>

                <span class="admin-calendar-legend-item">

                    <span
                        class="admin-calendar-dot maintenance"
                        aria-hidden="true"
                    ></span>

                    Maintenance

                </span>

            </div>

            <!-- Calendar error -->

            <p
                id="calendar-error"
                class="admin-calendar-error hidden"
                role="alert"
            ></p>

            <!-- Selected event information -->

            <p
                id="calendar-event"
                class="admin-calendar-message"
                role="status"
                aria-live="polite"
            ></p>

            <!-- Calendar layout switch -->

            <div x-data="{ calendarMode: 'timeline' }">

                <div
                    class="calendar-layout-switch"
                    role="group"
                    aria-label="Calendar layout"
                >

                    <button
                        type="button"
                        class="timeline-button"
                        :class="{ 'is-active': calendarMode === 'timeline' }"
                        :aria-pressed="calendarMode === 'timeline'"
                        @click="calendarMode = 'timeline'"
                    >
                        Villa timeline
                    </button>

                    <button
                        type="button"
                        class="timeline-button"
                        :class="{ 'is-active': calendarMode === 'grid' }"
                        :aria-pressed="calendarMode === 'grid'"
                        @click="calendarMode = 'grid'; $nextTick(() => window.dispatchEvent(new Event('resize')))"
                    >
                        Calendar grid
                    </button>

                </div>

                <!-- Villa Timeline -->

                <section
                    id="villa-timeline"
                    x-show="calendarMode === 'timeline'"
                    data-rooms="{{ $rooms->map(fn ($room) => [
                        'id' => $room->id,
                        'name' => $room->name,
                    ])->values()->toJson() }}"
                    aria-label="Villa reservation timeline"
                    aria-busy="false"
                >

                    <!-- Timeline toolbar -->

                    <div class="timeline-toolbar">

                        <div class="timeline-navigation">

                            <!-- Previous period -->

                            <button
                                type="button"
                                id="timeline-prev"
                                class="timeline-button"
                                aria-label="Previous period"
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
                                    <path d="m15 18-6-6 6-6"/>
                                </svg>

                            </button>

                            <!-- Next period -->

                            <button
                                type="button"
                                id="timeline-next"
                                class="timeline-button"
                                aria-label="Next period"
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
                                    <path d="m9 18 6-6-6-6"/>
                                </svg>

                            </button>

                            <!-- Current period -->

                            <h3
                                id="timeline-title"
                                class="timeline-title"
                                aria-live="polite"
                            ></h3>

                            <!-- Today -->

                            <button
                                type="button"
                                id="timeline-today"
                                class="timeline-button"
                            >
                                Today
                            </button>

                        </div>

                        <!-- Day / Week / Month -->

                        <div
                            class="timeline-view-switch"
                            role="group"
                            aria-label="Timeline period"
                        >

                            <button
                                type="button"
                                class="timeline-button"
                                data-timeline-view="day"
                                aria-pressed="false"
                            >
                                Day
                            </button>

                            <button
                                type="button"
                                class="timeline-button is-active"
                                data-timeline-view="week"
                                aria-pressed="true"
                            >
                                Week
                            </button>

                            <button
                                type="button"
                                class="timeline-button"
                                data-timeline-view="month"
                                aria-pressed="false"
                            >
                                Month
                            </button>

                        </div>

                    </div>

                    <!-- Loading state -->

                    <div
                        id="timeline-loading"
                        class="timeline-loading"
                        role="status"
                        hidden
                    >

                        <span
                            class="timeline-spinner"
                            aria-hidden="true"
                        ></span>

                        Loading villa calendar...

                    </div>

                    <!-- Error state -->

                    <div
                        id="timeline-error"
                        class="timeline-error"
                        role="alert"
                        hidden
                    ></div>

                    <button
                        type="button"
                        id="timeline-retry"
                        class="timeline-button"
                        hidden
                    >
                        Try again
                    </button>

                    <!-- Timeline grid -->

                    <div
                        class="timeline-scroll"
                        role="region"
                        aria-label="Villa bookings by date"
                        tabindex="0"
                    >

                        <div
                            id="timeline-grid"
                            class="timeline-grid"
                        ></div>

                    </div>

                    <!-- Mobile scroll hint -->

                    <div class="timeline-mobile-hint">

                        <svg
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                            aria-hidden="true"
                        >
                            <path d="M3 12h18"/>
                            <path d="m7 8-4 4 4 4"/>
                            <path d="m17 8 4 4-4 4"/>
                        </svg>

                        Swipe to view more dates

                    </div>

                </section>

                <!-- Existing FullCalendar -->

                <div
                    x-show="calendarMode === 'grid'"
                    x-cloak
                >

                    <div
                        class="admin-calendar-scroll"
                        role="region"
                        aria-label="Villa availability calendar"
                        tabindex="0"
                    >

                        <div
                            id="room-calendar"
                            data-today="{{ $today }}"
                            data-feed="{{ route('admin.calendar.events') }}"
                        ></div>

                    </div>

                </div>

            </div>

            <!-- Calendar notice -->

            <div class="admin-calendar-notice">

                Pending requests do not block nights.

                @can('manage-maintenance')

                    Select a date on the calendar to prepare
                    a maintenance block.

                @endcan

            </div>

        </section>

        <!-- Maintenance form -->

        @can('manage-maintenance')

            <div class="admin-calendar-section">

                <x-admin.forms.section
                    title="Block a room for maintenance"
                    description="Reserve unavailable nights for repairs, cleaning or other maintenance work."
                >

                    @if($rooms->isEmpty())

                        <div class="admin-management-empty">

                            <h3>No rooms available</h3>

                            <p>
                                Add a room before blocking
                                maintenance dates.
                            </p>

                        </div>

                    @else

                        <form
                            method="POST"
                            action="{{ route('admin.maintenance.store') }}"
                            x-data="{ submitting: false }"
                            @submit="submitting ? $event.preventDefault() : (submitting = true)"
                            @pageshow.window="submitting = false"
                            :aria-busy="submitting"
                        >

                            @csrf

                            <div class="admin-form-grid">

                                <x-admin.forms.select
                                    name="villa_id"
                                    id="maintenance-room"
                                    label="Villa"
                                    :options="$roomOptions"
                                    :required="true"
                                />

                                <x-admin.forms.input
                                    type="date"
                                    name="check_in"
                                    id="maintenance-start"
                                    label="First blocked night"
                                    :min="$today"
                                    :required="true"
                                />

                                <x-admin.forms.input
                                    type="date"
                                    name="check_out"
                                    id="maintenance-end"
                                    label="Available again on"
                                    :min="$tomorrow"
                                    :required="true"
                                    help="This date is not blocked."
                                />

                                <x-admin.forms.input
                                    name="reason"
                                    id="maintenance-reason"
                                    label="Reason"
                                    maxlength="500"
                                    placeholder="Enter maintenance reason"
                                    :required="true"
                                />

                            </div>

                            <div class="admin-maintenance-actions">

                                <x-admin.forms.submit-button
                                    label="Block dates"
                                    loading-label="Blocking dates..."
                                />

                            </div>

                        </form>

                    @endif

                </x-admin.forms.section>

            </div>

        @endcan

        <!-- Upcoming maintenance -->

        <section class="admin-calendar-section">

            <div class="admin-table-page-heading">

                <div>

                    <h2>Upcoming maintenance</h2>

                    <p>
                        View and manage existing maintenance blocks.
                    </p>

                </div>

            </div>

            <x-admin.tables.table label="Upcoming maintenance">

                <x-slot:head>

                    <x-admin.tables.heading>
                        Villa
                    </x-admin.tables.heading>

                    <x-admin.tables.heading>
                        First night
                    </x-admin.tables.heading>

                    <x-admin.tables.heading>
                        Available again
                    </x-admin.tables.heading>

                    <x-admin.tables.heading>
                        Reason
                    </x-admin.tables.heading>

                    @can('manage-maintenance')

                        <x-admin.tables.heading align="right">
                            Action
                        </x-admin.tables.heading>

                    @endcan

                </x-slot:head>

                @forelse($blocks as $block)

                    <tr>

                        <x-admin.tables.cell>

                            <strong>
                                {{ $block->villa->name }}
                            </strong>

                        </x-admin.tables.cell>

                        <x-admin.tables.cell>

                            {{ $block->starts_on->format('d M Y') }}

                        </x-admin.tables.cell>

                        <x-admin.tables.cell>

                            {{ $block->ends_on->format('d M Y') }}

                        </x-admin.tables.cell>

                        <x-admin.tables.cell>

                            <div class="admin-maintenance-reason">

                                {{ $block->reason }}

                            </div>

                        </x-admin.tables.cell>

                        @can('manage-maintenance')

                            <x-admin.tables.cell align="right">

                                <form
                                    method="POST"
                                    action="{{ route('admin.maintenance.destroy', $block) }}"
                                    x-data="{ submitting: false }"
                                    @submit="(submitting || !window.confirm('Release the remaining blocked nights?')) ? $event.preventDefault() : (submitting = true)"
                                    @pageshow.window="submitting = false"
                                    :aria-busy="submitting"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <x-admin.forms.button
                                        type="submit"
                                        variant="danger"
                                        x-bind:disabled="submitting"
                                    >

                                        <span
                                            x-text="submitting ? 'Releasing...' : 'Release dates'"
                                        >
                                            Release dates
                                        </span>

                                    </x-admin.forms.button>

                                </form>

                            </x-admin.tables.cell>

                        @endcan

                    </tr>

                @empty

                    <x-admin.tables.empty
                        :colspan="auth()->user()->can('manage-maintenance') ? 5 : 4"
                        title="No upcoming maintenance"
                        description="Scheduled maintenance blocks will appear here."
                    />

                @endforelse

                <x-slot:footer>

                    {{ $blocks->links() }}

                </x-slot:footer>

            </x-admin.tables.table>

        </section>

    </div>

@endsection