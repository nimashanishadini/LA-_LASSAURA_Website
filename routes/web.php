<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\RateController;
use App\Http\Controllers\Admin\RoomMediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Website\AvailabilityController;
use App\Http\Controllers\Website\BookingController;
use App\Http\Controllers\Website\ContactController;
use App\Http\Controllers\Website\ExperienceController;
use App\Http\Controllers\Website\GalleryController;
use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\ReviewController;
use App\Http\Controllers\Website\RoomController;
use App\Http\Middleware\BookingApi;
use Illuminate\Support\Facades\Route;

// Public Website Routes
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/rooms', [RoomController::class, 'index'])
    ->name('rooms.index');

Route::get('/rooms/accommodation', fn () => redirect()->route('rooms.index'));

Route::get('/rooms/{room:slug}', [RoomController::class, 'show'])
    ->name('rooms.show');

// Dashboard

// Role-based Dashboard Redirect
Route::get('/dashboard', function () {
    if (auth()->user()->canAccessAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('bookings.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// User Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});
Route::get('/experiences', [ExperienceController::class, 'index'])
    ->name('experiences.index');

Route::get('/gallery', [GalleryController::class, 'index'])
    ->name('gallery.index');

Route::get('/contact', [ContactController::class, 'index'])
    ->name('contact.index');

// Route::get('/admin/login', function () {
//     return redirect()->route('admin.dashboard');
// })->name('admin.login');

Route::get('/admin/login', [
    \App\Http\Controllers\Auth\AuthenticatedSessionController::class,
    'createAdmin'
])->middleware('guest')->name('admin.login');

Route::post('/admin/login', [
    \App\Http\Controllers\Auth\AuthenticatedSessionController::class,
    'store'
])->middleware(['guest', 'throttle:20,1'])
  ->name('admin.login.store');


Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified-email', 'can:access-admin'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::middleware('can:manage-rooms')->group(function () {
        Route::resource('rooms', App\Http\Controllers\Admin\RoomController::class)->except(['show', 'destroy']);
        Route::post('rooms/{room}/images', [RoomMediaController::class, 'store'])->name('rooms.images.store');
        Route::patch('rooms/{room}/images/{media}', [RoomMediaController::class, 'update'])->name('rooms.images.update');
        Route::delete('rooms/{room}/images/{media}', [RoomMediaController::class, 'destroy'])->name('rooms.images.destroy');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified-email', 'can:access-admin', 'can:manage-rates'])->group(function () {
    Route::get('/rates', [RateController::class, 'index'])->name('rates.index');
    Route::post('/rates', [RateController::class, 'store'])->name('rates.store');
    Route::put('/rates/{rate}', [RateController::class, 'update'])->name('rates.update');
    Route::post('/exchange-rate', [RateController::class, 'exchange'])->name('rates.exchange');
});

Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified-email', 'can:access-admin', 'can:manage-inquiries'])->group(function () {
    Route::get('inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::patch('inquiries/{inquiry}', [InquiryController::class, 'update'])->name('inquiries.update');
});

Route::post('/experiences/feedback', [ReviewController::class, 'store'])->middleware('throttle:5,1')->name('reviews.store');
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified-email', 'can:access-admin', 'can:manage-reviews'])->group(function () {
    Route::get('reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'update'])->name('reviews.update');
});

Route::get('/availability', AvailabilityController::class)->middleware('throttle:60,1')->name('availability');
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified-email', 'can:access-admin', 'can:view-calendar'])->group(function () {
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::post('maintenance', [CalendarController::class, 'store'])->middleware('can:manage-maintenance')->name('maintenance.store');
    Route::delete('maintenance/{block}', [CalendarController::class, 'destroy'])->middleware('can:manage-maintenance')->name('maintenance.destroy');
});

Route::middleware(['auth', 'verified-email'])->group(function () {
    Route::post('/booking/quotes', [BookingController::class, 'quote'])->middleware('throttle:30,1')->name('bookings.quote');
    Route::get('/booking/quotes/{quote}', [BookingController::class, 'quoteShow'])->name('bookings.quote.show');
    Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('bookings.store');
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/my-bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/my-bookings/{booking}/accept', [BookingController::class, 'accept'])->name('bookings.accept');
    Route::post('/my-bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified-email', 'can:access-admin'])->group(function () {
    Route::get('booking-policies', [App\Http\Controllers\Admin\BookingController::class, 'policies'])->middleware('can:manage-booking-policies')->name('booking-policies');
    Route::put('booking-policies', [App\Http\Controllers\Admin\BookingController::class, 'savePolicies'])->middleware('can:manage-booking-policies')->name('booking-policies.save');
    Route::middleware('can:manage-bookings')->group(function () {
        Route::get('bookings', [App\Http\Controllers\Admin\BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [App\Http\Controllers\Admin\BookingController::class, 'show'])->name('bookings.show');
        Route::post('bookings/{booking}/approve', [App\Http\Controllers\Admin\BookingController::class, 'approve'])->name('bookings.approve');
        Route::post('bookings/{booking}/reject', [App\Http\Controllers\Admin\BookingController::class, 'reject'])->name('bookings.reject');
    });
});

// Same-origin session/CSRF API. Token access remains a separate Sanctum decision.
Route::prefix('api/v1')->middleware([BookingApi::class, 'auth', 'verified-email', 'throttle:30,1'])->group(function () {
    Route::post('quotes', [BookingController::class, 'quote']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('me/bookings', [BookingController::class, 'index']);
    Route::post('admin/bookings/{booking}/approve', [App\Http\Controllers\Admin\BookingController::class, 'approve'])->middleware('can:manage-bookings');
    Route::post('admin/bookings/{booking}/reject', [App\Http\Controllers\Admin\BookingController::class, 'reject'])->middleware('can:manage-bookings');
});

Route::get('/booking', [BookingController::class, 'create'])->name('bookings.create');

require __DIR__.'/auth.php';
