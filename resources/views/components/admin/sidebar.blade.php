<aside class="admin-shell-sidebar">

    <!-- Brand -->

    <div class="admin-sidebar-brand">

        <a href="{{ route('admin.dashboard') }}"
           class="admin-brand-link">

            <span class="admin-brand-name">
                LA LASSAURA
            </span>

            <span class="admin-brand-subtitle">
                PROPERTY MANAGEMENT
            </span>

        </a>

        <button
            type="button"
            x-ref="menuButton"
            @click="menu = !menu"
            :aria-expanded="menu.toString()"
            aria-expanded="false"
            aria-controls="admin-nav"
            class="admin-shell-menu-button"
        >
            <span x-text="menu ? 'Close' : 'Menu'">
                Menu
            </span>
        </button>

    </div>

    <!-- Navigation -->

    <nav
        id="admin-nav"
        class="admin-shell-navigation"
        :class="{ 'is-open': menu }"
        aria-label="Admin navigation"
    >

        <p class="admin-nav-heading">
            Workspace
        </p>

        <x-admin.nav-link
            route="admin.dashboard"
            match="admin.dashboard"
            icon="dashboard"
        >
            Dashboard
        </x-admin.nav-link>

        @can('manage-rooms')

            <x-admin.nav-link
                route="admin.rooms.index"
                match="admin.rooms.*"
                icon="rooms"
            >
                Rooms
            </x-admin.nav-link>

        @endcan

        @can('manage-rates')

            <x-admin.nav-link
                route="admin.rates.index"
                match="admin.rates.*"
                icon="rates"
            >
                Rates &amp; currencies
            </x-admin.nav-link>

        @endcan

        @can('view-calendar')

            <x-admin.nav-link
                route="admin.calendar.index"
                match="admin.calendar.*"
                icon="calendar"
            >
                Calendar
            </x-admin.nav-link>

        @endcan

        <!-- Booking notifications -->

        @can('manage-bookings')

            <x-admin.nav-link
                route="admin.bookings.index"
                match="admin.bookings.*"
                icon="bookings"
                notification="bookings"
            >
                Bookings
            </x-admin.nav-link>

        @endcan

        <!-- Inquiry notifications -->

        @can('manage-inquiries')

            <x-admin.nav-link
                route="admin.inquiries.index"
                match="admin.inquiries.*"
                icon="mail"
                notification="inquiries"
            >
                Inquiries
            </x-admin.nav-link>

        @endcan

        <!-- Review notifications -->

        @can('manage-reviews')

            <x-admin.nav-link
                route="admin.reviews.index"
                match="admin.reviews.*"
                icon="star"
                notification="reviews"
            >
                Guest feedback
            </x-admin.nav-link>

        @endcan

        @can('manage-booking-policies')

            <x-admin.nav-link
                route="admin.booking-policies"
                match="admin.booking-policies*"
                icon="settings"
            >
                Booking policies
            </x-admin.nav-link>

        @endcan

        <!-- Account Section -->

        <div class="admin-nav-account">

            <p class="admin-nav-heading">
                Account
            </p>

            <x-admin.nav-link
                route="home"
                icon="external"
            >
                View website
            </x-admin.nav-link>

            <x-admin.nav-link
                route="profile.edit"
                icon="user"
            >
                My profile
            </x-admin.nav-link>

            <form
                action="{{ route('logout') }}"
                method="POST"
            >

                @csrf

                <button
                    type="submit"
                    class="admin-shell-link"
                >

                    <x-admin.icon name="logout" />

                    <span>Sign out</span>

                </button>

            </form>

        </div>

    </nav>

</aside>