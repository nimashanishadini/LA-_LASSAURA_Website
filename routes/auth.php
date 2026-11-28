<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\Auth\EmailLoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;

Route::middleware('guest')->group(function () {
    Route::post('login/code', [EmailLoginController::class, 'send'])->middleware('throttle:5,1')->name('login.code.send');
    Route::post('login/code/verify', [EmailLoginController::class, 'verify'])->middleware('throttle:10,1')->name('login.code.verify');
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:20,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:10,1')->name('password.store');
        // Google Customer Authentication

Route::get('auth/google', [
    GoogleAuthController::class,
    'redirect'
])->middleware('throttle:10,1')
  ->name('google.redirect');

Route::get('auth/google/callback', [
    GoogleAuthController::class,
    'callback'
])->middleware('throttle:20,1')
  ->name('google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailOtpController::class, 'show'])->name('verification.notice');
    Route::get('email/verification-status', [EmailOtpController::class, 'show'])->name('verification.status');
    Route::post('email/verification-notification', [EmailOtpController::class, 'send'])->middleware('throttle:10,1')->name('verification.send');
    Route::post('email/verify', [EmailOtpController::class, 'verify'])->middleware('throttle:10,1')->name('verification.verify');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});


/*
|--------------------------------------------------------------------------
| Admin Password Recovery Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware('guest')
    ->group(function () {

        Route::get(
            'forgot-password',
            [PasswordResetLinkController::class, 'create']
        )->name('password.request');


        Route::post(
            'forgot-password',
            [PasswordResetLinkController::class, 'store']
        )->middleware('throttle:5,1')
          ->name('password.email');


        Route::get(
            'reset-password/{token}',
            [NewPasswordController::class, 'create']
        )->name('password.reset');


        Route::post(
            'reset-password',
            [NewPasswordController::class, 'store']
        )->middleware('throttle:10,1')
          ->name('password.store');

    });

