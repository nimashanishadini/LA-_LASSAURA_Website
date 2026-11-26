<?php

use App\Models\User;
use App\Services\ExchangeRateRefresh;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:grant {email} {--role=owner}', function () {
    $role = $this->option('role');
    if (! in_array($role, ['owner', 'manager', 'reception', 'finance', 'housekeeping', 'editor'], true)) {
        $this->error('Invalid staff role.');

        return 1;
    }
    $user = User::where('email', $this->argument('email'))->first();
    if (! $user) {
        $this->error('Account not found. Register the account first.');

        return 1;
    }
    $user->role = $role;
    $user->save();
    $this->info('Staff access assigned to '.$user->email.'.');

    return 0;
})->purpose('Assign staff access to an existing account from a trusted server console');

Artisan::command('rates:refresh-fx', function (ExchangeRateRefresh $refresh) {
    if (! $refresh->refresh()) {
        $this->warn('No FX rate saved. Configure FX_RATE_URL or check provider response. Last valid rate retained.');

        return 1;
    }
    $this->info('Exchange rate refreshed.');

    return 0;
});
Schedule::command('rates:refresh-fx')->hourly()->withoutOverlapping();
