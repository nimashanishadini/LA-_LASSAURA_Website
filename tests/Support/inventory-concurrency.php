<?php

use App\Exceptions\InventoryConflict;
use App\Models\Booking;
use App\Models\BookingPolicy;
use App\Models\User;
use App\Models\Villa;
use App\Models\VillaNight;
use App\Services\Availability;
use App\Services\BookingWorkflow;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// Explicit opt-in integration check: creates/drops only its own random test DB.
// Never migrates or clears the application database.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$worker = ($argv[1] ?? '') === 'worker';
$workflowMode = ($argv[1] ?? '') === 'workflow' || ($worker && ($argv[4] ?? '') === 'workflow');
$schema = $worker ? ($argv[2] ?? '') : 'la_lassaura_inventory_test_'.bin2hex(random_bytes(6));
if (! preg_match('/^la_lassaura_inventory_test_[a-f0-9]{12}$/', $schema)) {
    throw new RuntimeException('Invalid test database name.');
}
$base = config('database.connections.'.config('database.default'));
if (($base['driver'] ?? '') !== 'mysql') {
    throw new RuntimeException('Run using the local MySQL/MariaDB configuration.');
}
// URL overrides must not redirect an isolated test connection to the real DB.
unset($base['url']);
config(['database.connections.inventory_check' => array_merge($base, ['database' => $schema]), 'database.default' => 'inventory_check']);
if ($worker) {
    fgets(STDIN);
    try {
        if ($workflowMode) {
            app(BookingWorkflow::class)->approve(Booking::findOrFail((int) $argv[3]), (int) $argv[5]);
        } else {
            app(Availability::class)->confirm(Booking::findOrFail((int) $argv[3]));
        }
        echo "200\n";
    } catch (InventoryConflict $error) {
        echo "409\n";
    } catch (Throwable $error) {
        fwrite(STDERR, get_class($error).': '.$error->getMessage());
        exit(1);
    }
    exit(0);
}
config(['database.connections.inventory_admin' => $base]);
$admin = DB::connection('inventory_admin');
$admin->statement('CREATE DATABASE `'.$schema.'`');
$processes = [];
try {
    Artisan::call('migrate', ['--database' => 'inventory_check', '--force' => true]);
    $room = Villa::create(['name' => 'Concurrency fixture', 'slug' => 'concurrency-fixture', 'category' => 'double', 'max_adults' => 2, 'max_children' => 0, 'max_guests' => 2, 'is_active' => true]);
    $start = now('Asia/Colombo')->addDays(5)->toDateString();
    $end = now('Asia/Colombo')->addDays(7)->toDateString();
    $actor = 0;
    if ($workflowMode) {
        $user = User::factory()->create(['email' => 'fixture@example.invalid']);
        $actor = $user->id;
        BookingPolicy::create(['key' => 'default', 'check_in_time' => '14:00', 'check_out_time' => '11:00', 'cancellation_policy' => 'Isolated test fixture terms only.']);
        $room->rates()->create(['name' => 'Fixture rate', 'starts_on' => $start, 'ends_on' => $end, 'days' => 'all', 'priority' => 0, 'nightly_lkr' => '10000', 'discount_percent' => '0', 'is_active' => true]);
        $workflow = app(BookingWorkflow::class);
        $guest = ['name' => 'Concurrency fixture', 'email' => $user->email, 'phone' => '+94770000000', 'special_requests' => null];
        $a = $workflow->submit($user->id, $workflow->quote($user->id, $room, $start, $end, 2, 0)->id, $guest);
        $b = $workflow->submit($user->id, $workflow->quote($user->id, $room, $start, $end, 2, 0)->id, $guest);
    } else {
        $a = app(Availability::class)->request($room, $start, $end, 2, 0);
        $b = app(Availability::class)->request($room, $start, $end, 2, 0);
    }
    foreach ([$a, $b] as $booking) {
        $process = proc_open([PHP_BINARY, __FILE__, 'worker', $schema, (string) $booking->id, $workflowMode ? 'workflow' : 'inventory', (string) $actor], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2));
        if (! is_resource($process)) {
            throw new RuntimeException('Cannot start concurrency worker.');
        }
        $processes[] = [$process, $pipes];
    }
    foreach ($processes as [$process,$pipes]) {
        fwrite($pipes[0], "start\n");
        fclose($pipes[0]);
    }
    $statuses = [];
    foreach ($processes as [$process,$pipes]) {
        stream_set_timeout($pipes[1], 20);
        $out = trim(stream_get_contents($pipes[1]));
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);
        if ($exit !== 0) {
            throw new RuntimeException('Worker failed: '.$errors);
        }
        $statuses[] = $out;
    }
    $processes = [];
    sort($statuses);
    if ($statuses !== ['200', '409'] || VillaNight::count() !== 2 || Booking::where('status', 'confirmed')->count() !== 1 || Booking::where('status', 'pending')->count() !== 1) {
        throw new RuntimeException('Concurrency invariant failed: '.json_encode($statuses));
    }
    try {
        DB::table('villa_nights')->insert(['villa_id' => $room->id, 'stay_date' => now('Asia/Colombo')->addDays(10)->toDateString()]);
        throw new RuntimeException('Database accepted an ownerless inventory row.');
    } catch (QueryException $error) {
        if (! str_contains($error->getMessage(), 'villa_nights_one_owner')) {
            throw $error;
        }
    }
    echo "PASS: real concurrent confirmations returned 200 and 409; exactly one confirmed booking owns both nights. MySQL/MariaDB owner constraint enforced.\n";
} finally {
    foreach ($processes as [$process,$pipes]) {
        if (is_resource($process)) {
            proc_terminate($process);
        }
    }
    DB::disconnect('inventory_check');
    $admin->statement('DROP DATABASE `'.$schema.'`');
}
