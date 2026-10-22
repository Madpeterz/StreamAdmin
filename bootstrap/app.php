<?php

use App\Http\Middleware\UseDisplayTimezone;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // in-world entry points (api.php, hud.php, sys.php, ans.php): 2.0.x showed times in the
        // display timezone there too
        then: fn () => Route::middleware(UseDisplayTimezone::class)->group(base_path('routes/legacy.php')),
    )
    ->withMiddleware(function (Middleware $middleware) {
        // The container is only reachable through cloudflared (no published ports), and the tunnel
        // terminates https, so trust its X-Forwarded-* headers: https URLs, secure cookies, Livewire assets.
        // The real client IP is already set by nginx from CF-Connecting-IP. TRUSTED_PROXIES can list
        // addresses instead of "*" (comma separated).
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        $middleware->web(append: [
            // a changed password (or removed account) signs that staff member out everywhere
            AuthenticateSession::class,
            UseDisplayTimezone::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
