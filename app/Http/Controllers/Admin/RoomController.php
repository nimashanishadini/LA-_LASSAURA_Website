<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomRequest;
use App\Models\Amenity;
use App\Models\Villa;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index()
    {
        return view('admin.rooms.index', ['rooms' => Villa::with('primaryImage')->withCount('media')->orderBy('name')->paginate(12)]);
    }

    public function create()
    {
        return view('admin.rooms.form', ['room' => new Villa, 'amenities' => Amenity::orderBy('name')->get()]);
    }

    public function store(RoomRequest $request)
    {
        $room = DB::transaction(function () use ($request) {
            $room = Villa::create($request->safe()->except('amenities'));
            $room->amenities()->sync($request->validated('amenities', []));

            return $room;
        });

        return redirect()->route('admin.rooms.edit', $room)->with('status', 'Room created. Add its photographs below.');
    }

    public function edit(Villa $room)
    {
        return view('admin.rooms.form', ['room' => $room->load('media', 'amenities'), 'amenities' => Amenity::orderBy('name')->get()]);
    }

    public function update(RoomRequest $request, Villa $room)
    {
        DB::transaction(function () use ($request, $room) {
            $room->update($request->safe()->except('amenities'));
            $room->amenities()->sync($request->validated('amenities', []));
        });

        return redirect()->route('admin.rooms.edit', $room)->with('status', 'Room updated.');
    }
}
