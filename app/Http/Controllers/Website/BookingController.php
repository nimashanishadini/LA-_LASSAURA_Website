<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingQuote;
use App\Models\InventoryEvent;
use App\Models\Villa;
use App\Services\BookingWorkflow;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        return redirect()->route('home', array_merge($request->only(['room', 'check_in', 'check_out', 'adults', 'children']), ['booking' => 1]));
    }

    public function quote(Request $request, BookingWorkflow $workflow)
    {
        $data = $request->validate(['room' => ['required', 'string', 'exists:villas,slug'], 'check_in' => ['required', 'date_format:Y-m-d'], 'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'], 'adults' => ['required', 'integer', 'between:1,100'], 'children' => ['required', 'integer', 'between:0,100']]);
        $quote = $workflow->quote($request->user()->id, Villa::where('slug', $data['room'])->firstOrFail(), $data['check_in'], $data['check_out'], $data['adults'], $data['children']);

        return $request->expectsJson() ? response()->json(['data' => $quote], 201) : redirect()->route('bookings.quote.show', $quote);
    }

    public function quoteShow(Request $request, string $quote)
    {
        $quote = BookingQuote::with('villa')->where('user_id', $request->user()->id)->findOrFail($quote);

        return $request->expectsJson() ? response()->json(['data' => $quote]) : redirect()->route('home', ['booking' => 1, 'quote' => $quote->id]);
    }

    public function store(Request $request, BookingWorkflow $workflow)
    {
        $data = $request->validate(['quote_id' => ['required', 'uuid'], 'name' => ['required', 'string', 'max:120'], 'phone' => ['required', 'string', function ($attribute, $value, $fail) {
            if (! is_string($value) || ! preg_match('/^\+[1-9]\d{6,14}$/', preg_replace('/[\s()-]/', '', $value))) {
                $fail('Enter a valid international phone number, for example +94771234567.');
            }
        }, 'max:30'], 'special_requests' => ['nullable', 'string', 'max:1000'], 'accepted_terms' => ['required', 'accepted']]);
        $guest = ['name' => $data['name'], 'email' => $request->user()->email, 'phone' => $data['phone'], 'special_requests' => $data['special_requests'] ?? null];
        $booking = $workflow->submit($request->user()->id, $data['quote_id'], $guest);

        return $request->expectsJson() ? response()->json(['data' => $booking], 201) : redirect()->route('bookings.show', $booking)->with('status', 'Request received. This is pending approval; no room has been reserved yet.');
    }

    public function index(Request $request)
    {
        $bookings = Booking::with('villa')->where('user_id', $request->user()->id)->latest()->paginate(10);

        return $request->expectsJson() ? response()->json($bookings) : redirect()->route('home', ['booking' => 1, 'history' => 1]);
    }

    public function show(Request $request, Booking $booking, BookingWorkflow $workflow)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        $data = ['booking' => $booking->load('villa'), 'revision' => $booking->proposed_snapshot ? $workflow->revision($booking) : null, 'events' => InventoryEvent::where('booking_id', $booking->id)->orderBy('id')->get()];

        return $request->expectsJson() ? response()->json(['data' => $data]) : redirect()->route('home', ['booking' => 1, 'reference' => $booking->reference]);
    }

    public function accept(Request $request, Booking $booking, BookingWorkflow $workflow)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        $data = $request->validate(['revision' => ['required', 'string', 'size:64'], 'accepted_terms' => ['required', 'accepted']]);
        $booking = $workflow->accept($booking, $request->user()->id, $data['revision']);

        if ($request->expectsJson()) {
            return response()->json(['data' => $booking]);
        }

        return back()->with('status', $booking->proposed_snapshot ? 'The price or terms changed again. Please review the latest revision below.' : 'Revised quote accepted. Your request is still pending admin approval.');
    }

    public function cancel(Request $request, Booking $booking, BookingWorkflow $workflow)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        $workflow->cancelPending($booking, $request->user()->id);

        return $request->expectsJson() ? response()->json(['message' => 'Pending request cancelled.']) : redirect()->route('home', ['booking' => 1, 'reference' => $booking->reference]);
    }
}
