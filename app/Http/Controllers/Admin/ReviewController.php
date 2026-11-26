<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return view('admin.reviews.index', ['reviews' => Review::latest()->paginate(15), 'pendingCount' => Review::where('status', 'pending')->count()]);
    }

    public function update(Request $request, Review $review)
    {
        $data = $request->validate(['status' => ['required', 'in:pending,approved,rejected']]);
        $review->status = $data['status'];
        $review->moderated_by = $request->user()->id;
        $review->moderated_at = now();
        $review->save();

        return back()->with('status', 'Feedback moderation updated.');
    }
}
