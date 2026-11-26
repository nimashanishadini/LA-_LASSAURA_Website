@extends('layouts.admin')
@section('title', 'Inquiries')
@section('content')
<p class="mb-6 text-sm text-slate-400">{{ $newCount }} new inquiries · Messages submitted through the website contact form.</p>
<div class="space-y-4">
    @forelse($inquiries as $inquiry)
        <details class="admin-panel">
            <summary class="cursor-pointer"><span class="font-medium">{{ $inquiry->subject }}</span><span class="ml-3 text-xs uppercase tracking-wider {{ $inquiry->status === 'new' ? 'text-amber-300' : 'text-slate-400' }}">{{ $inquiry->status }}</span><span class="mt-2 block text-sm text-slate-400">{{ $inquiry->name }} · {{ $inquiry->created_at->timezone('Asia/Colombo')->format('d M Y H:i') }}</span></summary>
            <dl class="mt-5 grid gap-2 text-sm sm:grid-cols-[100px_1fr]"><dt class="text-slate-400">Email</dt><dd class="break-all">{{ $inquiry->email }}</dd><dt class="text-slate-400">Phone</dt><dd>{{ $inquiry->phone ?: 'Not provided' }}</dd></dl>
            <p class="my-6 whitespace-pre-wrap break-words border-y border-slate-700 py-5 text-sm leading-relaxed">{{ $inquiry->message }}</p>
            <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}" class="flex flex-wrap items-end gap-4">@csrf @method('PATCH')<label class="admin-label">Status<select name="status" class="admin-input">@foreach(['new'=>'New','read'=>'Read','closed'=>'Closed'] as $value=>$label)<option value="{{ $value }}" @selected($inquiry->status === $value)>{{ $label }}</option>@endforeach</select></label><button class="admin-button">Update status</button></form>
        </details>
    @empty<div class="admin-panel text-slate-400">No inquiries received yet.</div>@endforelse
</div><div class="mt-6">{{ $inquiries->links() }}</div>
@endsection
