<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPolicy;
use App\Models\InventoryEvent;
use App\Services\BookingWorkflow;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['status' => ['nullable', 'in:pending,confirmed,rejected,cancelled']]);

        return view('admin.bookings.index', ['bookings' => Booking::with('villa')->whereNotNull('user_id')->when($data['status'] ?? null, fn ($q, $status) => $q->where('status', $status))->latest()->paginate(15)->withQueryString(), 'pendingCount' => Booking::whereNotNull('user_id')->where('status', 'pending')->count()]);
    }

    public function show(Booking $booking)
    {
        return view('admin.bookings.show', ['booking' => $booking->load('villa'), 'events' => InventoryEvent::where('booking_id', $booking->id)->orderBy('id')->get()]);
    }

    public function approve(Request $request, Booking $booking, BookingWorkflow $workflow)
    {
        $booking = $workflow->approve($booking, $request->user()->id);
        $message = $booking->status === 'confirmed' ? 'Booking confirmed. Nights are allocated and visible on the calendar.' : 'Price or terms changed. A revised quote is awaiting customer acceptance; dates remain unreserved.';

        return $request->expectsJson() ? response()->json(['data' => $booking, 'message' => $message]) : back()->with('status', $message);
    }

    public function reject(Request $request, Booking $booking, BookingWorkflow $workflow)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $booking = $workflow->reject($booking, $request->user()->id, $data['reason']);

        return $request->expectsJson() ? response()->json(['data' => $booking]) : back()->with('status', 'Booking request rejected.');
    }

    public function policies()
    {
        return view('admin.bookings.policies', ['policy' => BookingPolicy::firstOrCreate(['key' => 'default'])]);
    }

    public function savePolicies(Request $request)
    {
        $data = $request->validate(['check_in_time' => ['required', 'date_format:H:i'], 'check_out_time' => ['required', 'date_format:H:i'], 'cancellation_policy' => ['required', 'string', 'min:10', 'max:5000']]);
        BookingPolicy::updateOrCreate(['key' => 'default'], $data);

        return back()->with('status','Property terms saved. Existing confirmed bookings keep their agreed terms.');
    }
}
