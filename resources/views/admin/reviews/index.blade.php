@extends('layouts.admin')
@section('title', 'Guest feedback')
@section('content')
<p class="mb-6 text-sm text-slate-400">{{ $pendingCount }} pending reviews. Only approved feedback appears on the Experiences page.</p>
<div class="space-y-4">@forelse($reviews as $review)
    <article class="admin-panel"><div class="flex flex-wrap items-center justify-between gap-3"><h2 class="font-semibold">{{ $review->name }}</h2><span class="text-xs uppercase tracking-wider text-amber-300">{{ $review->status }}</span></div><p class="mt-2 text-sm text-slate-400">{{ $review->email }} · {{ $review->created_at->timezone('Asia/Colombo')->format('d M Y H:i') }}</p><p class="mt-3 text-sm text-amber-300">{{ $review->rating }} / 5 stars</p><p class="my-5 whitespace-pre-wrap break-words text-sm leading-relaxed">{{ $review->comment }}</p>
        <form method="POST" action="{{ route('admin.reviews.update', $review) }}" class="flex flex-wrap items-end gap-4">@csrf @method('PATCH')<label class="admin-label">Moderation<select name="status" class="admin-input">@foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $value=>$label)<option value="{{ $value }}" @selected($review->status === $value)>{{ $label }}</option>@endforeach</select></label><button class="admin-button">Save decision</button></form>
    </article>
@empty<div class="admin-panel text-slate-400">No feedback submitted yet.</div>@endforelse</div><div class="mt-6">{{ $reviews->links() }}</div>
@endsection
