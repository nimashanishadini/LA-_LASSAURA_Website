<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\Villa;
use App\Models\VillaRate;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RateController extends Controller
{
    public function index()
    {
        return view('admin.rates.index', ['rooms' => Villa::orderBy('name')->get(), 'rates' => VillaRate::with('villa')->orderBy('villa_id')->orderByDesc('priority')->get(), 'fx' => ExchangeRate::orderByDesc('fetched_at')->orderByDesc('id')->first(), 'history' => ExchangeRate::orderByDesc('id')->limit(10)->get()]);
    }

    public function store(Request $request)
    {
        return $this->save($request, new VillaRate);
    }

    public function update(Request $request, VillaRate $rate)
    {
        return $this->save($request, $rate);
    }

    private function save(Request $request, VillaRate $rate)
    {
        $data = $request->validate(['villa_id' => ['required', 'integer', 'exists:villas,id'], 'name' => ['required', 'string', 'max:120'], 'starts_on' => ['required', 'date_format:Y-m-d'], 'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'], 'days' => ['required', 'in:all,weekday,weekend'], 'priority' => ['required', 'integer', 'between:0,1000'], 'nightly_lkr' => ['required', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/', 'numeric', 'gt:0'], 'discount_percent' => ['required', 'numeric', 'decimal:0,2', 'between:0,100'], 'is_active' => ['required', 'boolean']]);
        DB::transaction(function () use ($rate, $data, $request) {
            // Lock source/destination rooms in stable order when moving a rate.
            Villa::whereIn('id', array_filter([$rate->villa_id, $data['villa_id']]))->orderBy('id')->lockForUpdate()->get();
            $conflicts = VillaRate::where('villa_id', $data['villa_id'])->where('is_active', true)->where('priority', $data['priority'])->whereDate('starts_on', '<=', $data['ends_on'])->whereDate('ends_on', '>=', $data['starts_on'])->when($rate->exists, fn ($q) => $q->where('id', '!=', $rate->id))->get();
            foreach ($conflicts as $other) {
                $start = CarbonImmutable::parse(max($data['starts_on'], $other->starts_on->toDateString()));
                $end = CarbonImmutable::parse(min($data['ends_on'], $other->ends_on->toDateString()));
                // At most seven dates needed to check whether day filters overlap.
                for ($day = $start; $day->lte($end) && $day->lt($start->addDays(7)); $day = $day->addDay()) {
                    $applies = fn ($filter) => $filter === 'all' || ($filter === 'weekend') === $day->isWeekend();
                    if ($data['is_active'] && $applies($data['days']) && $applies($other->days)) {
                        throw ValidationException::withMessages(['priority' => 'An active rule with the same priority already covers these dates. Choose a different priority or date/day range.']);
                    }
                }
            }
            $rate->fill($data);
            $rate->updated_by = $request->user()->id;
            $rate->save();
        });

        return redirect()->route('admin.rates.index')->with('status', 'Rate rule saved. New quotes use the updated rates.');
    }

    public function exchange(Request $request)
    {
        $data = $request->validate(['lkr_per_usd' => ['required', 'regex:/^\d{1,8}(?:\.\d{1,6})?$/', 'numeric', 'gt:0'], 'reason' => ['required', 'string', 'max:500']]);
        ExchangeRate::create($data + ['source' => 'Admin override', 'recorded_by' => $request->user()->id, 'fetched_at' => now(), 'is_override' => true]);

        return back()->with('status','Exchange rate recorded. Next successful scheduled refresh replaces this override.');
    }
}
