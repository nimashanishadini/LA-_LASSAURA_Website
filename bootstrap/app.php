<?php

use App\Exceptions\InventoryConflict;
use App\Http\Middleware\EnsureEmailVerified;
use App\Http\Middleware\ExpireIdleSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['verified-email' => EnsureEmailVerified::class]);
        $middleware->web(append: [AuthenticateSession::class, ExpireIdleSession::class]);
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->user()->canAccessAdmin()) {
                return route('admin.dashboard');
            }

            return route('home');
        });
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['code']);
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $error) => $request->is('api/*') || $request->expectsJson());
        $exceptions->render(function (InventoryConflict $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage(), 'code' => 'inventory_conflict'], 409);
            }

            return response()->view('website.bookings.conflict', ['message' => $exception->getMessage()], 409);
        });
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            if ($request->is('admin/rooms/*/images')) {
                $message = 'The upload exceeds the server request limit. Upload fewer images at a time; each image must be 30 MB or smaller.';
                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 413);
                }

                return redirect()->route('admin.rooms.index')->withErrors(['images' => $message]);
            }
        });
    })->create();
