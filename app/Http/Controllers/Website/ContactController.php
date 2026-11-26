<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[+0-9() .-]{6,30}$/'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);
        Inquiry::create($data);

        return redirect()->route('contact.index', ['#inquiry-form'])->with('inquiry_status', 'Thank you. Your inquiry has been received by La Lassaura.');
    }

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
