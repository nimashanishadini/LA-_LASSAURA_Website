<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Villa;
use App\Services\RoomPricing;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(RoomPricing $pricing): View
    {
        $rooms = Villa::active()->with('primaryImage')->orderBy('name')->get();
        $prices = [];
        foreach ($rooms as $room) {
            try {
                $today = now('Asia/Colombo')->toDateString();
                $prices[$room->id] = $pricing->quote($room, $today, now('Asia/Colombo')->addDay()->toDateString());
            } catch (ValidationException $exception) {
                $prices[$room->id] = null;
            }
        }

        return view('website.rooms.index', compact('rooms', 'prices'));
    }

    public function show(Request $request, Villa $room, RoomPricing $pricing): View
    {
        abort_unless($room->is_active, 404);
        $room->load(['media', 'primaryImage', 'amenities' => fn ($query) => $query->where('amenities.is_active', true)]);
        $galleryImages = $room->media->map(fn ($image) => ['src' => $image->url, 'title' => $image->alt_text ?: $room->name])->values()->all();

        $quote = null;
        $pricingError = null;
        $dates = $request->validate([
            'check_in' => ['nullable', 'required_with:check_out', 'date_format:Y-m-d'],
            'check_out' => ['nullable', 'required_with:check_in', 'date_format:Y-m-d'],
        ]);
        try {
            $quote = $pricing->quote($room, $dates['check_in'] ?? now('Asia/Colombo')->toDateString(), $dates['check_out'] ?? now('Asia/Colombo')->addDay()->toDateString());
        } catch (ValidationException $exception) {
            $pricingError = collect($exception->errors())->flatten()->first();
        }

        return view('website.rooms.show', compact('room', 'galleryImages', 'quote', 'pricingError'));
    }
}
