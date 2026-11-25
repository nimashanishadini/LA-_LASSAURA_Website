<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        return view('website.rooms.index');
    }

    public function show(): View
    {
        return view('website.rooms.show');
    }
}
