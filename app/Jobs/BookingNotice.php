<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class BookingNotice implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $bookingId, public string $kind) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $booking = Booking::with('villa')->findOrFail($this->bookingId);
        if (! $booking->guest_email) {
            return;
        }
        Mail::send('emails.booking', ['booking' => $booking, 'kind' => $this->kind], function ($message) use ($booking) {
            $message->to($booking->guest_email)->subject('La Lassaura booking '.$booking->reference);
        });
    }
}
