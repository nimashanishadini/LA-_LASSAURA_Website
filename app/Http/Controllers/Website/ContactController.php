<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $contact = [
            'email' => null,
            'phone' => null,
            'whatsapp' => null,
        ];

        $location = [
            'name' => 'La Lassaura – Beach Side Villa',
            'address' => '27F3+JRH, Unawatuna, 80600, Sri Lanka.',
            'map_url' => 'https://maps.google.com/maps?q=27F3%2BJRH%2C%20Unawatuna%2C%20Sri%20Lanka&z=15&output=embed',
            'directions_url' => 'https://www.google.com/maps/dir/?api=1&destination=27F3%2BJRH%2C%20Unawatuna%2C%20Sri%20Lanka',
        ];

        return view('website.contact.index', compact(
            'contact',
            'location'
        ));
    }
}
