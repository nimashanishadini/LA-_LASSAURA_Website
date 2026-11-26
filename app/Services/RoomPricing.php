<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\Villa;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class RoomPricing
{
    public function quote(Villa $room, string $checkIn, string $checkOut): array
    {
        $start = CarbonImmutable::parse($checkIn, 'Asia/Colombo')->startOfDay();
        $end = CarbonImmutable::parse($checkOut, 'Asia/Colombo')->startOfDay();
        if ($start->lt(CarbonImmutable::today('Asia/Colombo')) || $end->lte($start) || $start->diffInDays($end) > 90) {
            throw ValidationException::withMessages(['check_out' => 'Choose future stay dates between 1 and 90 nights.']);
        }
        $rates = $room->rates()->where('is_active', true)->whereDate('starts_on', '<=', $end->toDateString())->whereDate('ends_on', '>=', $start->toDateString())->orderByDesc('priority')->get();
        $lines = [];
        $total = BigDecimal::zero();
        for ($date = $start; $date->lt($end); $date = $date->addDay()) {
            $matches = $rates->filter(fn ($rate) => ($date->toDateString() >= $rate->starts_on->toDateString() && $date->toDateString() <= $rate->ends_on->toDateString()) && ($rate->days === 'all' || ($rate->days === 'weekend') === $date->isWeekend()))->values();
            $rate = $matches->first();
            if (! $rate) {
                throw ValidationException::withMessages(['check_in' => 'Rates are not configured for '.$date->toDateString().'. Please contact the property.']);
            }
            if ($matches->count() > 1 && $matches[1]->priority === $rate->priority) {
                throw ValidationException::withMessages(['check_in' => 'Conflicting rates need property review.']);
            }
            $amount = BigDecimal::of($rate->nightly_lkr)->multipliedBy(BigDecimal::of('100')->minus($rate->discount_percent))->dividedBy('100', 2, RoundingMode::HALF_UP);
            $total = $total->plus($amount);
            $lines[] = ['stay_date' => $date->toDateString(), 'rate_id' => $rate->id, 'name' => $rate->name, 'base_lkr' => $rate->nightly_lkr, 'discount_percent' => $rate->discount_percent, 'amount_lkr' => (string) $amount];
        }
        $fx = ExchangeRate::where('lkr_per_usd', '>', 0)->where('fetched_at', '<=', now())->orderByDesc('fetched_at')->orderByDesc('id')->first();

        return ['room_id' => $room->id, 'check_in' => $checkIn, 'check_out' => $checkOut, 'nightly_lines' => $lines, 'nights' => count($lines), 'total_lkr' => (string) $total->toScale(2), 'total_usd' => $fx ? (string) $total->dividedBy($fx->lkr_per_usd, 2, RoundingMode::HALF_UP) : null, 'fx' => $fx ? ['id' => $fx->id, 'lkr_per_usd' => $fx->lkr_per_usd, 'source' => $fx->source, 'fetched_at' => $fx->fetched_at->toIso8601String(), 'stale' => $fx->isStale(), 'is_override' => $fx->is_override] : null];
    }
}
