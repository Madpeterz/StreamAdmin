<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every URL the app creates (password reset links sent in-world, redirects, assets) uses APP_URL,
 * which is set on the stack. Without this the host comes from the request, and the Cloudflare
 * tunnel passes a client's own X-Forwarded-Host through, so a reset link could be pointed at
 * another site.
 */
class ForceAppUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $url = (string) config('app.url');
        if ($url !== '') {
            URL::forceRootUrl($url);
            if (str_starts_with($url, 'https://')) {
                URL::forceScheme('https');
            }
        }

        return $next($request);
    }
}
