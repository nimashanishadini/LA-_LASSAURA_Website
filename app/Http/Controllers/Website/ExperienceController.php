<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    public function index(): View
    {
        // Experience Cards
        $experiences = [
            [
                'number' => '01',
                'image' => 'images/home/hero.jpg',
                'title' => 'Poolside, at your own pace',
                'description' => 'A refreshing dip. A quiet evening. Nothing on the agenda.',
            ],
            [
                'number' => '02',
                'image' => 'images/home/garden.jpg',
                'title' => 'Find your quiet corner',
                'description' => 'Lush greenery, still waters and a little time for yourself.',
            ],
            [
                'number' => '03',
                'image' => 'images/home/balcony.jpg',
                'title' => 'Wake up to a greener view',
                'description' => 'Open balconies and tropical views. Take it all in.',
            ],
            [
                'number' => '04',
                'image' => 'images/home/interior.jpg',
                'title' => 'Make yourself at home',
                'description' => 'Welcoming living spaces, warm textures and room to unwind.',
            ],
        ];

        // Lightbox Gallery Images
        $galleryImages = [
            [
                'src' => asset('images/home/hero.jpg'),
                'title' => 'Evenings by the pool',
            ],
            [
                'src' => asset('images/home/garden.jpg'),
                'title' => 'Outdoor relaxation',
            ],
            [
                'src' => asset('images/home/balcony.jpg'),
                'title' => 'Wake up to a greener view',
            ],
            [
                'src' => asset('images/home/interior.jpg'),
                'title' => 'Welcoming living spaces',
            ],
            [
                'src' => asset('images/home/courtyard.jpg'),
                'title' => 'Villa architecture',
            ],
            [
                'src' => asset('images/home/about.jpg'),
                'title' => 'A warm welcome',
            ],
            [
                'src' => asset('images/home/villa.jpg'),
                'title' => 'La Lassaura Villa',
            ],
            [
                'src' => asset('images/home/bathroom.jpg'),
                'title' => 'Bathroom interior',
            ],
        ];

        $approved = Review::where('status', 'approved');
        $reviewCount = (clone $approved)->count();
        $averageRating = (clone $approved)->avg('rating');
        $reviews = $approved->select('id', 'name', 'rating', 'comment', 'created_at')->latest()->paginate(6, ['*'], 'reviews_page')->fragment('guest-feedback');

        return view('website.experiences.index', compact(
            'experiences',
            'galleryImages', 'reviews', 'reviewCount', 'averageRating'
        ));
    }
}
