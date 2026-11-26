<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index()
    {
        return view('admin.inquiries.index', ['inquiries' => Inquiry::latest()->paginate(15), 'newCount' => Inquiry::where('status', 'new')->count()]);
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $data = $request->validate(['status' => ['required', 'in:new,read,closed']]);
        $inquiry->status = $data['status'];
        $inquiry->save();

        return back()->with('status', 'Inquiry status updated.');
    }
}
