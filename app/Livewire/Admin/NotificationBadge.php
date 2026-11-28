<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\Review;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class NotificationBadge extends Component
{
    #[Locked]
    public string $type;

    public function mount(string $type): void
    {
        abort_unless(
            in_array($type, [
                'bookings',
                'inquiries',
                'reviews',
            ], true),
            404
        );

        $this->type = $type;
    }

    protected function notificationCount(): int
    {
        if (
            !auth()->check() ||
            Gate::denies('access-admin') ||
            !auth()->user()->hasVerifiedEmail()
        ) {
            return 0;
        }

        return match ($this->type) {

            'bookings' => Gate::allows('manage-bookings')
                ? Booking::whereNotNull('user_id')
                    ->where('status', 'pending')
                    ->count()
                : 0,

            'inquiries' => Gate::allows('manage-inquiries')
                ? Inquiry::where('status', 'new')->count()
                : 0,

            'reviews' => Gate::allows('manage-reviews')
                ? Review::where('status', 'pending')->count()
                : 0,

            default => 0,
        };
    }

    public function render()
    {
        $label = match ($this->type) {
            'bookings' => 'pending booking requests',
            'inquiries' => 'new inquiries',
            'reviews' => 'pending reviews',
            default => 'notifications',
        };

        return view('livewire.admin.notification-badge', [
            'count' => $this->notificationCount(),
            'label' => $label,
        ]);
    }
}