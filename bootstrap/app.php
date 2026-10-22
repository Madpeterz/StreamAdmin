<?php

use App\Api\ApiException;
use App\Api\Http\Middleware\IdentifyCaller;
use App\Api\Http\Middleware\InTransaction;
use App\Api\Http\Middleware\VerifySignature;
use App\Http\Middleware\ForceAppUrl;
use App\Http\Middleware\UseDisplayTimezone;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // the in-world API (api/v3/...) and ans.php; the routes carry their own prefix
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // The container is only reachable through cloudflared (no published ports), and the tunnel
        // terminates https, so trust its X-Forwarded-* headers: https URLs, secure cookies, Livewire assets.
        // The real client IP is already set by nginx from CF-Connecting-IP. TRUSTED_PROXIES can list
        // addresses instead of "*" (comma separated).
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        $middleware->web(prepend: [
            // created URLs come from APP_URL, never from the request's Host / X-Forwarded-Host
            ForceAppUrl::class,
        ], append: [
            // a changed password (or removed account) signs that staff member out everywhere
            AuthenticateSession::class,
            UseDisplayTimezone::class,
        ]);

        // in-world calls show times in the display timezone too (swap tags, "time left")
        $middleware->api(append: [UseDisplayTimezone::class]);

        $middleware->alias([
            'api.signed' => VerifySignature::class,
            'api.transaction' => InTransaction::class,
            'api.caller' => IdentifyCaller::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // in-world callers are scripts: always JSON, never a redirect or an HTML error page
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->is('ans.php') || $request->expectsJson());

        $exceptions->render(function (ApiException $e) {
            return response()->json(['ok' => false, 'code' => $e->errorCode, 'message' => $e->getMessage()] + ($e->errors === [] ? [] : ['errors' => $e->errors]), $e->status);
        });
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json(['ok' => false, 'code' => 'validation', 'message' => $e->validator->errors()->first(), 'errors' => $e->errors()], 422);
        });
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }
            $code = $e->getStatusCode() === 404 ? 'not_found' : 'invalid_request';

            return response()->json(['ok' => false, 'code' => $code, 'message' => $e->getStatusCode() === 404 ? 'Unknown endpoint' : ($e->getMessage() ?: 'Bad request')], $e->getStatusCode());
        });
    })->create();
