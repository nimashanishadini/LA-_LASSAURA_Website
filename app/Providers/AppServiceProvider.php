<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-bookings', fn (User $user) => in_array($user->role, ['owner', 'manager', 'reception'], true));
        Gate::define('manage-booking-policies', fn (User $user) => $user->role === 'owner');
        Gate::define('view-calendar', fn (User $user) => in_array($user->role, ['owner', 'manager', 'reception', 'housekeeping'], true));
        Gate::define('manage-maintenance', fn (User $user) => in_array($user->role, ['owner', 'manager'], true));
        Gate::define('manage-reviews', fn (User $user) => in_array($user->role, ['owner', 'manager', 'editor'], true));
        Gate::define('manage-inquiries', fn (User $user) => in_array($user->role, ['owner', 'manager', 'reception'], true));
        Gate::define('manage-rates', fn (User $user) => in_array($user->role, ['owner', 'manager'], true));
        Gate::define('access-admin', fn (User $user) => $user->canAccessAdmin());
        Gate::define('manage-rooms', fn (User $user) => $user->canManageRooms());
    }
}
