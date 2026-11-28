
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <div class="admin-dashboard">

        {{-- Welcome Section --}}
        <section class="admin-dashboard-welcome">

            <div>
                <h2>
                    Welcome back, {{ auth()->user()->name }}.
                </h2>

                <p>
                    Here's an overview of your property
                    and website activity.
                </p>
            </div>

            <div class="admin-dashboard-date">

                <x-admin.icon name="calendar" />

                <time datetime="{{ now()->toDateString() }}">
                    {{ now()->format('l, d M Y') }}
                </time>

            </div>

        </section>


        {{-- Statistics Cards --}}
        <section class="admin-stats-grid"
                 aria-label="Property statistics">

            <x-admin.stat-card
                label="Total rooms"
                :value="$rooms->count()"
                icon="rooms"
                description="All registered rooms"
                tone="gold"
            />

            <x-admin.stat-card
                label="Published rooms"
                :value="$rooms->where('is_active', true)->count()"
                icon="check-circle"
                description="Visible on the website"
                tone="green"
            />

            <x-admin.stat-card
                label="Draft rooms"
                :value="$rooms->where('is_active', false)->count()"
                icon="file-text"
                description="Not yet published"
                tone="amber"
            />

            <x-admin.stat-card
                label="Room photographs"
                :value="$rooms->sum('media_count')"
                icon="images"
                description="Uploaded room images"
                tone="blue"
            />

        </section>


        {{-- Pending Booking Requests --}}
        @can('manage-bookings')

            <section class="admin-booking-summary">

                <div class="admin-booking-summary-icon">
                    <x-admin.icon name="bookings" />
                </div>

                <div class="admin-booking-summary-content">

                    <h2>Pending booking requests</h2>

                    <p>
                        @if($pendingCount > 0)

                            {{ $pendingCount }}
                            {{ \Illuminate\Support\Str::plural('request', $pendingCount) }}
                            awaiting review.

                        @else

                            You're all caught up.
                            No pending booking requests.

                        @endif
                    </p>

                </div>

                <a
                    href="{{ route('admin.bookings.index', ['status' => 'pending']) }}"
                    class="admin-dashboard-button"
                >
                    Review requests

                    <span aria-hidden="true">→</span>
                </a>

            </section>

        @endcan


        {{-- Quick Actions --}}
        @if(
            auth()->user()->can('manage-rooms') ||
            auth()->user()->can('view-calendar') ||
            auth()->user()->can('manage-bookings')
        )

            <section class="admin-dashboard-panel">

                <div class="admin-dashboard-section-heading">

                    <div>
                        <h2>Quick actions</h2>

                        <p>
                            Access frequently used management tools.
                        </p>
                    </div>

                </div>

                <div class="admin-quick-actions-grid">

                    @can('manage-rooms')

                        <x-admin.quick-action
                            route="admin.rooms.create"
                            icon="plus-circle"
                            label="Add room"
                            description="Create a new accommodation"
                        />

                    @endcan


                    @can('view-calendar')

                        <x-admin.quick-action
                            route="admin.calendar.index"
                            icon="calendar"
                            label="View calendar"
                            description="Manage room availability"
                        />

                    @endcan


                    @can('manage-bookings')

                        <x-admin.quick-action
                            route="admin.bookings.index"
                            icon="bookings"
                            label="Manage bookings"
                            description="Review guest reservations"
                        />

                    @endcan

                </div>

            </section>

        @endif


        {{-- Room Overview --}}
        <section class="admin-dashboard-panel">

            <div class="admin-dashboard-section-heading">

                <div>
                    <h2>Room overview</h2>

                    <p>
                        Monitor accommodation details
                        and website publishing status.
                    </p>
                </div>

                @can('manage-rooms')

                    <a
                        href="{{ route('admin.rooms.index') }}"
                        class="admin-dashboard-text-link"
                    >
                        Manage rooms

                        <span aria-hidden="true">→</span>
                    </a>

                @endcan

            </div>


            @if($rooms->isNotEmpty())

                <div
                    class="admin-dashboard-table-wrapper"
                    role="region"
                    aria-label="Room overview table"
                    tabindex="0"
                >

                    <table class="admin-dashboard-table">

                        <thead>

                            <tr>
                                <th scope="col">Room</th>
                                <th scope="col">Category</th>
                                <th scope="col">Guest capacity</th>
                                <th scope="col">Photos</th>
                                <th scope="col">Website status</th>

                                @can('manage-rooms')
                                    <th scope="col">Action</th>
                                @endcan
                            </tr>

                        </thead>

                        <tbody>

                            @foreach($rooms as $room)

                                <tr>

                                    <th scope="row">

                                        <div class="admin-room-name">

                                            <div
                                                class="admin-room-icon"
                                                aria-hidden="true"
                                            >
                                                <x-admin.icon name="rooms" />
                                            </div>

                                            <span>
                                                {{ $room->name }}
                                            </span>

                                        </div>

                                    </th>

                                    <td class="admin-room-category">
                                        {{ $room->category ?? 'Unconfigured' }}
                                    </td>

                                    <td>
                                        {{ $room->max_guests ?? 'Not configured' }}
                                    </td>

                                    <td>
                                        {{ $room->media_count }}
                                    </td>

                                    <td>

                                        <x-admin.status-badge
                                            :status="$room->is_active ? 'published' : 'draft'"
                                        />

                                    </td>

                                    @can('manage-rooms')

                                        <td>

                                            <a
                                                href="{{ route('admin.rooms.edit', ['room' => $room]) }}"
                                                class="admin-table-action"
                                                aria-label="Edit {{ $room->name }}"
                                            >
                                                Edit
                                            </a>

                                        </td>

                                    @endcan

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <x-admin.empty-state
                    icon="rooms"
                    title="No rooms configured"
                    description="Add your first room to start managing accommodation."
                >

                    @can('manage-rooms')

                        <a
                            href="{{ route('admin.rooms.create') }}"
                            class="admin-dashboard-button"
                        >
                            Add first room
                        </a>

                    @endcan

                </x-admin.empty-state>

            @endif

        </section>

    </div>

@endsection
