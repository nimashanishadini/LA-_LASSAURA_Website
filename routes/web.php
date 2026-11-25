<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\RoomController;
use App\Http\Controllers\Website\ExperienceController;
use App\Http\Controllers\Website\GalleryController;
use App\Http\Controllers\Website\LocationController;
use App\Http\Controllers\Website\ContactController;

// Public Website Routes
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/rooms', [RoomController::class, 'index'])
    ->name('rooms.index');

Route::get('/rooms/accommodation', [RoomController::class, 'show'])
    ->name('rooms.show');

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
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
    Route::get('/location', [LocationController::class, 'index'])
    ->name('location.index');

    Route::get('/contact', [ContactController::class, 'index'])
    ->name('contact.index');
require __DIR__.'/auth.php';
