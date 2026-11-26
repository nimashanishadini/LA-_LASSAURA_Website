<?php

namespace App\Services;

use App\Exceptions\InventoryConflict;
use App\Models\Booking;
use App\Models\InventoryEvent;
use App\Models\MaintenanceBlock;
use App\Models\Villa;
use App\Models\VillaNight;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Availability
{
    public function dates(string $start, string $end, bool $future = true): array
    {
        try {
            $a = CarbonImmutable::createFromFormat('!Y-m-d', $start, 'Asia/Colombo');
            $b = CarbonImmutable::createFromFormat('!Y-m-d', $end, 'Asia/Colombo');
        } catch (InvalidFormatException $error) {
            throw ValidationException::withMessages(['check_out' => 'Enter valid stay dates in YYYY-MM-DD format.']);
        }
        if (! $a || ! $b || $a->toDateString() !== $start || $b->toDateString() !== $end || $b->lte($a) || $a->diffInDays($b) > 366 || ($future && $a->lt(CarbonImmutable::today('Asia/Colombo')))) {
            throw ValidationException::withMessages(['check_out' => 'Choose a valid date range of 1–366 nights, starting today or later.']);
        }
        $dates = [];
        for ($d = $a; $d->lt($b); $d = $d->addDay()) {
            $dates[] = $d->toDateString();
        }

        return $dates;
    }

    public function search(string $start, string $end, int $adults, int $children = 0)
    {
        $this->dates($start, $end);
        if ($adults < 1 || $children < 0) {
            throw ValidationException::withMessages(['adults' => 'Choose valid guest counts.']);
        }

        return Villa::active()->whereNotNull('max_adults')->whereNotNull('max_children')->whereNotNull('max_guests')->where('max_adults', '>=', $adults)->where('max_children', '>=', $children)->where('max_guests', '>=', $adults + $children)->whereNotIn('id', VillaNight::select('villa_id')->where('stay_date', '>=', $start)->where('stay_date', '<', $end))->orderBy('name')->get();
    }

    private function capacity(Villa $room, int $adults, int $children): void
    {
        if (! $room->is_active || $room->max_adults === null || $room->max_children === null || $room->max_guests === null || $adults < 1 || $children < 0 || $adults > $room->max_adults || $children > $room->max_children || $adults + $children > $room->max_guests) {
            throw ValidationException::withMessages(['adults' => 'This room cannot accommodate the selected guests or is not published.']);
        }
    }

    public function request(Villa $room, string $start, string $end, int $adults, int $children, ?int $actor = null): Booking
    {
        $this->dates($start, $end);
        $this->capacity($room, $adults, $children);

        return DB::transaction(function () use ($room, $start, $end, $adults, $children, $actor) {
            $booking = Booking::create(['reference' => (string) Str::uuid(), 'villa_id' => $room->id, 'check_in' => $start, 'check_out' => $end, 'adults' => $adults, 'children' => $children, 'created_by' => $actor, 'status' => 'pending']);
            $this->event('requested', $actor, $booking->id, null, ['status' => 'pending']);

            return $booking;
        });
    }

    public function confirm(Booking $booking, ?int $actor = null): Booking
    {
        return $this->transaction(function () use ($booking, $actor) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($booking->status === 'confirmed') {
                return $booking;
            }
            if ($booking->status !== 'pending') {
                throw new InventoryConflict('Only pending requests can be confirmed.');
            }
            $room = Villa::whereKey($booking->villa_id)->lockForUpdate()->firstOrFail();
            $this->capacity($room, $booking->adults, $booking->children);
            $this->allocate($room->id, $this->dates($booking->check_in->toDateString(), $booking->check_out->toDateString()), $booking->id, null);
            $booking->update(['status' => 'confirmed']);
            $this->event('confirmed', $actor, $booking->id, null, []);

            return $booking;
        });
    }

    public function change(Booking $booking, Villa $room, string $start, string $end, ?int $actor = null): Booking
    {
        $dates = $this->dates($start, $end);

        return $this->transaction(function () use ($booking, $room, $start, $end, $dates, $actor) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
                throw new InventoryConflict('This booking cannot be changed.');
            }
            if ($booking->quote_snapshot !== null) {
                throw new InventoryConflict('Priced booking changes require a new accepted quote and property review.');
            }
            if ($booking->check_in->toDateString() < now('Asia/Colombo')->toDateString()) {
                throw new InventoryConflict('Started stays require front-office policy review.');
            }
            $locked = Villa::whereIn('id', [$booking->villa_id, $room->id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $this->capacity($locked[$room->id], $booking->adults, $booking->children);
            $before = $booking->only(['villa_id', 'check_in', 'check_out']);
            if ($booking->status === 'confirmed') {
                VillaNight::where('booking_id', $booking->id)->delete();
                $this->allocate($room->id, $dates, $booking->id, null);
            }
            $booking->update(['villa_id' => $room->id, 'check_in' => $start, 'check_out' => $end]);
            $this->event('changed', $actor, $booking->id, null, ['before' => $before, 'after' => $booking->only(['villa_id', 'check_in', 'check_out'])]);

            return $booking;
        });
    }

    public function cancel(Booking $booking, ?int $actor = null): void
    {
        DB::transaction(function () use ($booking, $actor) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($booking->status === 'cancelled') {
                return;
            }
            if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
                throw new InventoryConflict('This booking cannot be cancelled.');
            }
            Villa::whereKey($booking->villa_id)->lockForUpdate()->firstOrFail();
            VillaNight::where('booking_id', $booking->id)->where('stay_date', '>=', now('Asia/Colombo')->toDateString())->delete();
            $booking->update(['status' => 'cancelled']);
            $this->event('cancelled', $actor, $booking->id, null, []);
        });
    }

    public function block(Villa $room, string $start, string $end, string $reason, ?int $actor = null): MaintenanceBlock
    {
        $dates = $this->dates($start, $end);

        return $this->transaction(function () use ($room, $start, $end, $reason, $actor, $dates) {
            Villa::whereKey($room->id)->lockForUpdate()->firstOrFail();
            $block = MaintenanceBlock::create(['villa_id' => $room->id, 'starts_on' => $start, 'ends_on' => $end, 'reason' => $reason, 'created_by' => $actor]);
            $this->allocate($room->id, $dates, null, $block->id);
            $this->event('maintenance_created', $actor, null, $block->id, ['start' => $start, 'end' => $end]);

            return $block;
        });
    }

    public function unblock(MaintenanceBlock $block, ?int $actor = null): void
    {
        DB::transaction(function () use ($block, $actor) {
            $block = MaintenanceBlock::whereKey($block->id)->lockForUpdate()->firstOrFail();
            if ($block->cancelled_at) {
                return;
            }
            Villa::whereKey($block->villa_id)->lockForUpdate()->firstOrFail();
            VillaNight::where('maintenance_block_id', $block->id)->where('stay_date', '>=', now('Asia/Colombo')->toDateString())->delete();
            $block->update(['cancelled_at' => now()]);
            $this->event('maintenance_cancelled', $actor, null, $block->id, []);
        });
    }

    private function allocate(int $roomId, array $dates, ?int $bookingId, ?int $blockId): void
    {
        if (VillaNight::where('villa_id', $roomId)->whereIn('stay_date', $dates)->exists()) {
            throw new InventoryConflict;
        }
        VillaNight::insert(array_map(fn ($date) => ['villa_id' => $roomId, 'stay_date' => $date, 'booking_id' => $bookingId, 'maintenance_block_id' => $blockId, 'created_at' => now(), 'updated_at' => now()], $dates));
    }

    private function transaction(callable $callback)
    {
        try {
            return DB::transaction($callback, 3);
        } catch (UniqueConstraintViolationException $error) {
            throw new InventoryConflict;
        }
    }

    private function event(string $action, ?int $actor, ?int $booking, ?int $block, array $details): void
    {
        InventoryEvent::create(['action' => $action, 'actor_id' => $actor, 'booking_id' => $booking, 'maintenance_block_id' => $block, 'details' => $details]);
    }
}
