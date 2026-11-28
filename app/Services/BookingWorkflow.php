<?php

namespace App\Services;

use App\Exceptions\InventoryConflict;
use App\Jobs\BookingNotice;
use App\Models\Booking;
use App\Models\BookingPolicy;
use App\Models\BookingQuote;
use App\Models\InventoryEvent;
use App\Models\User;
use App\Models\Villa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingWorkflow
{
    public function __construct(private Availability $inventory, private RoomPricing $pricing) {}

    private function terms(): array
    {
        $policy = BookingPolicy::where('key', 'default')->first();
        if (! $policy || ! $policy->check_in_time || ! $policy->check_out_time || ! trim($policy->cancellation_policy ?? '')) {
            throw ValidationException::withMessages(['booking' => 'The property must configure check-in/out times and cancellation terms before accepting booking requests. Please contact us.']);
        }

        return ['check_in_time' => $policy->check_in_time, 'check_out_time' => $policy->check_out_time, 'cancellation_policy' => $policy->cancellation_policy, 'timezone' => 'Asia/Colombo'];
    }

    private function available(Villa $room, string $start, string $end, int $adults, int $children): void
    {
        if (! $this->inventory->search($start, $end, $adults, $children)->contains('id', $room->id)) {
            throw new InventoryConflict('This room cannot be requested for the selected dates and guests. Please choose another stay.');
        }
    }

    // Only settlement economics and accepted terms trigger reacceptance. FX is display-only.
    private function fingerprint(array $quote, array $terms): string
    {
        $lines = array_map(fn ($line) => [$line['stay_date'], $line['amount_lkr']], $quote['nightly_lines']);

        return hash('sha256', json_encode([$quote['room_id'], $quote['check_in'], $quote['check_out'], $quote['total_lkr'], $lines, $terms], JSON_THROW_ON_ERROR));
    }

    public function quote(int $user, Villa $room, string $start, string $end, int $adults, int $children): BookingQuote
    {
        abort_unless(User::find($user)?->hasVerifiedEmail(), 403, 'Verify your email before requesting a quote.');

        return DB::transaction(function () use ($user, $room, $start, $end, $adults, $children) {
            $room = Villa::whereKey($room->id)->lockForUpdate()->firstOrFail();
            $this->available($room, $start, $end, $adults, $children);

            return BookingQuote::create(['id' => (string) Str::uuid(), 'user_id' => $user, 'villa_id' => $room->id, 'check_in' => $start, 'check_out' => $end, 'adults' => $adults, 'children' => $children, 'snapshot' => $this->pricing->quote($room, $start, $end), 'terms' => $this->terms(), 'expires_at' => now()->addMinutes(15)]);
        });
    }

    public function submit(int $user, string $quoteId, array $guest): Booking
    {
        abort_unless(User::find($user)?->hasVerifiedEmail(), 403, 'Verify your email before submitting a booking.');

        return DB::transaction(function () use ($user, $quoteId, $guest) {
            $quote = BookingQuote::where('user_id', $user)->whereKey($quoteId)->lockForUpdate()->firstOrFail();
            $hash = hash('sha256', json_encode($guest, JSON_THROW_ON_ERROR));
            $existing = Booking::where('quote_id', $quote->id)->first();
            if ($existing) {
                if ($existing->submission_hash !== $hash) {
                    throw new InventoryConflict('This quote was already submitted with different guest details.');
                }

                return $existing;
            }
            if ($quote->expires_at->isPast()) {
                throw new InventoryConflict('This quote has expired. Calculate a new quote before submitting.');
            }
            $room = Villa::whereKey($quote->villa_id)->lockForUpdate()->firstOrFail();
            $this->available($room, $quote->check_in->toDateString(), $quote->check_out->toDateString(), $quote->adults, $quote->children);
            $current = $this->pricing->quote($room, $quote->check_in->toDateString(), $quote->check_out->toDateString());
            if ($this->fingerprint($current, $this->terms()) !== $this->fingerprint($quote->snapshot, $quote->terms)) {
                throw new InventoryConflict('The price or property terms changed. Calculate and review a new quote.');
            }
            $booking = $this->inventory->request($room, $quote->check_in->toDateString(), $quote->check_out->toDateString(), $quote->adults, $quote->children, $user);
            $booking->forceFill(['user_id' => $user, 'quote_id' => $quote->id, 'guest_name' => $guest['name'], 'guest_email' => $guest['email'], 'guest_phone' => $guest['phone'], 'special_requests' => $guest['special_requests'] ?? null, 'submission_hash' => $hash, 'quote_snapshot' => $quote->snapshot, 'terms_snapshot' => $quote->terms])->save();
            $this->notify($booking, 'requested');

            return $booking;
        });
    }

    public function approve(Booking $booking, int $actor): Booking
    {
        return DB::transaction(function () use ($booking, $actor) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($booking->status === 'confirmed') {
                return $booking;
            }
            if ($booking->status !== 'pending' || ! $booking->quote_snapshot || ! $booking->user_id) {
                throw new InventoryConflict('Only customer requests with an accepted quote can be approved.');
            }
            $room = Villa::whereKey($booking->villa_id)->lockForUpdate()->firstOrFail();
            $this->available($room, $booking->check_in->toDateString(), $booking->check_out->toDateString(), $booking->adults, $booking->children);
            $current = $this->pricing->quote($room, $booking->check_in->toDateString(), $booking->check_out->toDateString());
            $terms = $this->terms();
            if ($this->fingerprint($current, $terms) !== $this->fingerprint($booking->quote_snapshot, $booking->terms_snapshot)) {
                $booking->forceFill(['proposed_snapshot' => $current, 'proposed_terms' => $terms])->save();
                $this->event($booking, $actor, 'quote_revision_proposed', []);
                $this->notify($booking, 'price_updated');

                return $booking;
            }
            // An outstanding proposed revision must be explicitly resolved by the customer.
            if ($booking->proposed_snapshot) {
                throw new InventoryConflict('Customer acceptance of the proposed quote is still required.');
            }
            $booking = $this->inventory->confirm($booking, $actor);
            $booking->forceFill(['confirmed_at' => now()])->save();
            $this->notify($booking, 'confirmed');

            return $booking;
        }, 3);
    }

    public function accept(Booking $booking, int $user, string $revision): Booking
    {
        return DB::transaction(function () use ($booking, $user, $revision) {
            $booking = Booking::where('user_id', $user)->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($booking->status !== 'pending' || ! $booking->proposed_snapshot) {
                throw new InventoryConflict('There is no pending revised quote to accept.');
            }
            if (! hash_equals($this->revision($booking), $revision)) {
                throw new InventoryConflict('The revised quote changed. Reload and review it again.');
            }
            $room = Villa::whereKey($booking->villa_id)->lockForUpdate()->firstOrFail();
            $this->available($room, $booking->check_in->toDateString(), $booking->check_out->toDateString(), $booking->adults, $booking->children);
            $current = $this->pricing->quote($room, $booking->check_in->toDateString(), $booking->check_out->toDateString());
            $terms = $this->terms();
            if ($this->fingerprint($current, $terms) !== $this->fingerprint($booking->proposed_snapshot, $booking->proposed_terms)) {
                $booking->forceFill(['proposed_snapshot' => $current, 'proposed_terms' => $terms])->save();
                $this->event($booking, $user, 'quote_revision_proposed', []);

                return $booking;
            }
            $booking->forceFill(['quote_snapshot' => $booking->proposed_snapshot, 'terms_snapshot' => $booking->proposed_terms, 'proposed_snapshot' => null, 'proposed_terms' => null])->save();
            $this->event($booking, $user, 'quote_revision_accepted', []);

            return $booking;
        });
    }

    public function revision(Booking $booking): string
    {
        return $this->fingerprint($booking->proposed_snapshot, $booking->proposed_terms);
    }

    public function reject(Booking $booking, int $actor, string $reason): Booking
    {
        return DB::transaction(function () use ($booking, $actor, $reason) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($booking->status !== 'pending') {
                throw new InventoryConflict('Only pending requests can be rejected.');
            }
            $booking->forceFill(['status' => 'rejected', 'rejection_reason' => $reason])->save();
            $this->event($booking, $actor, 'rejected', ['reason' => $reason]);
            $this->notify($booking, 'rejected');

            return $booking;
        });
    }

    public function cancelPending(Booking $booking, int $user): void
    {
        DB::transaction(function () use ($booking, $user) {
            $booking = Booking::where('user_id', $user)->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($booking->status === 'cancelled') {
                return;
            }
            if ($booking->status !== 'pending') {
                throw new InventoryConflict('Contact the property to request cancellation of a confirmed stay.');
            }
            $this->inventory->cancel($booking, $user);
            $this->notify($booking, 'cancelled');
        });
    }

    private function event(Booking $booking, int $actor, string $action, array $details): void
    {
        InventoryEvent::create(['booking_id' => $booking->id, 'actor_id' => $actor, 'action' => $action, 'details' => $details]);
    }

    private function notify(Booking $booking, string $kind): void
    {
        // Queue failure must never undo the committed reservation.
        DB::afterCommit(function () use ($booking, $kind) {
            try {
                BookingNotice::dispatch($booking->id, $kind);
            } catch (\Throwable $error) {
                Log::error('Booking notification could not be queued.', ['booking_id' => $booking->id, 'kind' => $kind, 'error_type' => get_class($error)]);
            }
        });
    }
}
