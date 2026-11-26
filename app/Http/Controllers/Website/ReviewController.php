<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email:rfc', 'max:255'], 'rating' => ['required', 'integer', 'between:1,5'], 'comment' => ['required', 'string', 'min:10', 'max:2000']]);
        Review::create($data);

        return redirect()->route('experiences.index', ['#share-feedback'])->with('review_status', 'Thank you for sharing your experience. Your feedback will appear after review by our team.');
    }
}
