<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Availability;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __invoke(Request $request, Availability $availability)
    {
        $data = $request->validate(['check_in' => ['required', 'date_format:Y-m-d'], 'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'], 'adults' => ['required', 'integer', 'between:1,100'], 'children' => ['required', 'integer', 'between:0,100']]);
        $rooms = $availability->search($data['check_in'], $data['check_out'], (int) $data['adults'], (int) $data['children']);

        return response()->json(['data' => $rooms->load('primaryImage')->map(fn ($room) => array_merge($room->only(['id', 'slug', 'name', 'category', 'max_adults', 'max_children', 'max_guests']), ['image' => $room->primaryImage?->url, 'image_alt' => $room->primaryImage?->alt_text])), 'meta' => ['check_in' => $data['check_in'], 'check_out' => $data['check_out'], 'checkout_exclusive' => true]]);
    }
}
