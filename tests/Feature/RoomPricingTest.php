<?php

namespace Tests\Feature;

use App\Models\ExchangeRate;
use App\Models\User;
use App\Models\Villa;
use App\Models\VillaRate;
use App\Services\ExchangeRateRefresh;
use App\Services\RoomPricing;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RoomPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-25 08:00:00', 'UTC'));
    }

    private function room(): Villa
    {
        return Villa::create(['name' => 'Sandy', 'slug' => 'sandy', 'category' => 'family', 'is_active' => true, 'max_adults' => 2, 'max_children' => 2, 'max_guests' => 4]);
    }

    private function staff(string $role = 'owner'): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function data(Villa $room, array $extra = []): array
    {
        return array_merge(['villa_id' => $room->id, 'name' => 'Base', 'starts_on' => '2026-11-25', 'ends_on' => '2027-12-31', 'days' => 'all', 'priority' => 0, 'nightly_lkr' => '10000.00', 'discount_percent' => '0.00', 'is_active' => 1], $extra);
    }

    private function fx(string $value = '300.000000'): ExchangeRate
    {
        return ExchangeRate::create(['lkr_per_usd' => $value, 'source' => 'Test fixture', 'fetched_at' => now()]);
    }

    public function test_decimal_totals_priority_discount_checkout_and_snapshots(): void
    {
        $room = $this->room();
        VillaRate::create($this->data($room));
        VillaRate::create($this->data($room, ['name' => 'Weekend', 'days' => 'weekend', 'priority' => 10, 'nightly_lkr' => '12000.00', 'discount_percent' => '10.00']));
        VillaRate::create($this->data($room, ['name' => 'Season', 'starts_on' => '2026-11-29', 'ends_on' => '2026-11-29', 'priority' => 20, 'nightly_lkr' => '15000.00']));
        $this->fx();
        $service = app(RoomPricing::class);
        $quote = $service->quote($room, '2026-11-27', '2026-11-30');
        $this->assertSame('35800.00', $quote['total_lkr']);
        $this->assertSame('119.33', $quote['total_usd']);
        $this->assertCount(3, $quote['nightly_lines']);
        $this->assertSame('10800.00', $quote['nightly_lines'][1]['amount_lkr']);
        VillaRate::query()->update(['nightly_lkr' => '20000.00']);
        $this->fx('400.00');
        $this->assertSame('35800.00', $quote['total_lkr']);
        $this->assertSame('300.000000', $quote['fx']['lkr_per_usd']);
        $this->assertNotSame($quote['total_lkr'], $service->quote($room, '2026-11-27', '2026-11-30')['total_lkr']);
    }

    public function test_missing_fx_stale_fx_and_invalid_provider_preserve_last_valid_rate(): void
    {
        $room = $this->room();
        VillaRate::create($this->data($room));
        $this->assertNull(app(RoomPricing::class)->quote($room, '2026-11-25', '2026-11-26')['total_usd']);
        $fx = $this->fx();
        $fx->update(['fetched_at' => now()->subDays(2)]);
        $this->assertTrue(app(RoomPricing::class)->quote($room, '2026-11-25', '2026-11-26')['fx']['stale']);
        config(['pricing.fx_url' => 'https://fx.example.test/latest']);
        Http::fake(['*' => Http::sequence()->push(['rates' => ['LKR' => 0]])->push([], 503)->push([], 503)->push(['rates' => ['LKR' => '310.123456']])]);
        $this->assertFalse(app(ExchangeRateRefresh::class)->refresh());
        $this->assertSame(1, ExchangeRate::count());
        $this->assertFalse(app(ExchangeRateRefresh::class)->refresh());
        $this->assertSame(1, ExchangeRate::count());
        $this->assertTrue(app(ExchangeRateRefresh::class)->refresh());
        $this->assertSame('310.123456', ExchangeRate::orderByDesc('id')->first()->lkr_per_usd);
    }

    public function test_admin_rules_reject_ambiguous_overlaps_but_allow_disjoint_day_filters(): void
    {
        $room = $this->room();
        $this->actingAs($this->staff());
        $this->get(route('admin.rates.index'))->assertOk();
        $this->post(route('admin.rates.store'), $this->data($room, ['days' => 'weekday']))->assertSessionHasNoErrors();
        $this->post(route('admin.rates.store'), $this->data($room, ['days' => 'all']))->assertSessionHasErrors('priority');
        $this->post(route('admin.rates.store'), $this->data($room, ['days' => 'weekend']))->assertSessionHasNoErrors();
        $this->assertSame(2, VillaRate::count());
        $this->get(route('admin.rates.index'))->assertOk()->assertSee('Configured rate rules');
    }

    public function test_permissions_and_override_validation(): void
    {
        $this->actingAs($this->staff('editor'))->get(route('admin.rates.index'))->assertForbidden();
        $this->post(route('admin.rates.exchange'), ['lkr_per_usd' => '300', 'reason' => 'Test'])->assertForbidden();
        $user = $this->staff();
        $this->actingAs($user);
        $this->post(route('admin.rates.exchange'), ['lkr_per_usd' => '0', 'reason' => 'Bad'])->assertSessionHasErrors('lkr_per_usd');
        $this->post(route('admin.rates.exchange'), ['lkr_per_usd' => '300.25', 'reason' => 'Approved property rate'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('exchange_rates', ['recorded_by' => $user->id, 'is_override' => true, 'reason' => 'Approved property rate']);
    }

    public function test_public_prices_and_dated_breakdown_are_server_generated(): void
    {
        $room = $this->room();
        VillaRate::create($this->data($room));
        $this->fx();
        $this->get('/rooms')->assertOk()->assertSee('LKR 10000.00')->assertSee('USD 33.33');
        $this->get('/rooms/sandy?check_in=2026-11-27&check_out=2026-11-29&total_lkr=1')->assertOk()->assertSee('LKR 20000.00')->assertSee('USD 66.67')->assertSee('Nightly breakdown');
    }

    public function test_unconfigured_night_is_not_silently_priced_as_zero(): void
    {
        $room = $this->room();
        $this->get('/rooms/sandy?check_in=2026-11-27&check_out=2026-11-29')->assertOk()->assertSee('Rates are not configured');
        $this->expectException(ValidationException::class);
        app(RoomPricing::class)->quote($room, '2026-11-27', '2026-11-29');
    }

    public function test_rule_edits_and_deactivation_update_new_quotes_only(): void
    {
        $room = $this->room();
        $rate = VillaRate::create($this->data($room));
        $this->actingAs($this->staff());
        $this->put(route('admin.rates.update', $rate), $this->data($room, ['nightly_lkr' => '12500.00']))->assertSessionHasNoErrors();
        $this->assertSame('12500.00', app(RoomPricing::class)->quote($room, '2026-11-27', '2026-11-28')['total_lkr']);
        $this->put(route('admin.rates.update', $rate), $this->data($room, ['is_active' => 0]))->assertSessionHasNoErrors();
        $this->assertFalse($rate->fresh()->is_active);
        $this->expectException(ValidationException::class);
        app(RoomPricing::class)->quote($room, '2026-11-27', '2026-11-28');
    }

    public function test_reversed_or_excessive_stays_are_rejected(): void
    {
        $room = $this->room();
        foreach ([['2026-11-27', '2026-11-27'], ['2026-11-27', '2026-11-26'], ['2026-11-27', '2027-05-01']] as [$start, $end]) {
            try {
                app(RoomPricing::class)->quote($room, $start, $end);
                $this->fail('Invalid stay was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('check_out', $exception->errors());
            }
        }
    }

    public function test_fractional_rounding_and_no_showing_checkout_night(): void
    {
        $room = $this->room();
        VillaRate::create($this->data($room, ['nightly_lkr' => '0.01']));
        $this->fx('3');
        $quote = app(RoomPricing::class)->quote($room, '2026-11-27', '2026-11-29');
        $this->assertSame('0.02', $quote['total_lkr']);
        $this->assertSame('0.01', $quote['total_usd']);
    }
}
