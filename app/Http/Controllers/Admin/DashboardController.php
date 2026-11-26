<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Villa;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', ['rooms' => Villa::withCount('media')->orderBy('name')->get(), 'pendingCount' => Booking::whereNotNull('user_id')->where('status', 'pending')->count()]);
    }
}
