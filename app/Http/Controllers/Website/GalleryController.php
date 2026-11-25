<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleryImages = [
            [
                'src' => asset('images/home/hero.jpg'),
                'title' => 'Evenings by the pool',
            ],
            [
                'src' => asset('images/home/about.jpg'),
                'title' => 'A warm arrival',
            ],
            [
                'src' => asset('images/home/garden.jpg'),
                'title' => 'A slower kind of day',
            ],
            [
                'src' => asset('images/home/villa.jpg'),
                'title' => 'Your tropical hideaway',
            ],
            [
                'src' => asset('images/home/interior.jpg'),
                'title' => 'Room to feel at home',
            ],
            [
                'src' => asset('images/home/balcony.jpg'),
                'title' => 'A different perspective',
            ],
            [
                'src' => asset('images/home/courtyard.jpg'),
                'title' => 'Architecture with character',
            ],
            [
                'src' => asset('images/home/bathroom.jpg'),
                'title' => 'Thoughtful details',
            ],
        ];

        return view('website.gallery.index', compact(
            'galleryImages'
        ));
    }
}
