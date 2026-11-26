<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InventoryConflict;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceBlock;
use App\Models\Villa;
use App\Models\VillaNight;
use App\Services\Availability;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin.calendar.index', ['rooms' => Villa::orderBy('name')->get(), 'blocks' => MaintenanceBlock::with('villa')->whereNull('cancelled_at')->where('ends_on', '>', now('Asia/Colombo')->toDateString())->orderBy('starts_on')->paginate(10)]);
    }

    public function events(Request $request, Availability $availability)
    {
        $data = $request->validate(['start' => ['required', 'date_format:Y-m-d'], 'end' => ['required', 'date_format:Y-m-d', 'after:start'], 'room_id' => ['nullable', 'integer', 'exists:villas,id']]);
        $availability->dates($data['start'], $data['end'], false);
        $nights = VillaNight::with('villa')->where('stay_date', '>=', $data['start'])->where('stay_date', '<', $data['end'])->when($data['room_id'] ?? null, fn ($q, $id) => $q->where('villa_id', $id))->orderBy('stay_date')->get();
        // Render the authoritative allocations, including retained historical nights.
        $events = [];
        foreach ($nights->groupBy(fn ($night) => $night->booking_id ? 'booking-'.$night->booking_id : 'maintenance-'.$night->maintenance_block_id) as $key => $group) {
            $segment = [];
            foreach ($group as $night) {
                if ($segment && end($segment)->stay_date->addDay()->toDateString() !== $night->stay_date->toDateString()) {
                    $events[] = $this->event($key, $segment);
                    $segment = [];
                }
                $segment[] = $night;
            }
            if ($segment) {
                $events[] = $this->event($key, $segment);
            }
        }

        return response()->json($events);
    }

    private function event(string $key, array $segment): array
    {
        $first = $segment[0];
        $last = end($segment);
        $maintenance = $first->maintenance_block_id !== null;

        return ['id' => $key.'-'.$first->stay_date->toDateString(), 'title' => $first->villa->name.' · '.($maintenance ? 'Maintenance' : 'Confirmed stay'), 'start' => $first->stay_date->toDateString(), 'end' => $last->stay_date->addDay()->toDateString(), 'allDay' => true, 'backgroundColor' => $maintenance ? '#64748b' : '#15803d', 'borderColor' => $maintenance ? '#64748b' : '#15803d', 'extendedProps' => ['room' => $first->villa->name, 'kind' => $maintenance ? 'Maintenance' : 'Confirmed stay']];
    }

    public function store(Request $request, Availability $availability)
    {
        $data = $request->validate(['villa_id' => ['required', 'integer', 'exists:villas,id'], 'check_in' => ['required', 'date_format:Y-m-d'], 'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'], 'reason' => ['required', 'string', 'max:500']]);
        try {
            $availability->block(Villa::findOrFail($data['villa_id']), $data['check_in'], $data['check_out'], $data['reason'], $request->user()->id);
        } catch (InventoryConflict $error) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $error->getMessage(), 'code' => 'inventory_conflict'], 409);
            }

return back()->withInput()->withErrors(['check_in' => $error->getMessage()]);
        }

        return back()->with('status', 'Maintenance dates blocked. The end date is available again.');
    }

    public function destroy(Request $request, MaintenanceBlock $block, Availability $availability)
    {
        $availability->unblock($block, $request->user()->id);

        return back()->with('status','Remaining maintenance nights released; history preserved.');
    }
}
