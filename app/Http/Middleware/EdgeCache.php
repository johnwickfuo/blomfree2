<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets CDN-friendly Cache-Control headers on responses so an edge cache
 * (Cloudflare, Bunny, etc.) can serve cached pages for the given number of
 * minutes. Admins always bypass the cache. The response is also marked
 * private + no-store for any logged-in admin so the CDN never caches their
 * personalised pages.
 */
class EdgeCache
{
    public function handle(Request $request, Closure $next, int $minutes = 5): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response->getStatusCode() !== 200 || $request->method() !== 'GET') {
            return $response;
        }

        $user = $request->user();
        if ($user && ($user->is_admin ?? false)) {
            $response->headers->set('Cache-Control', 'private, no-store');

            return $response;
        }

        $seconds = max(0, $minutes) * 60;
        $response->headers->set(
            'Cache-Control',
            "public, max-age={$seconds}, s-maxage={$seconds}",
        );

        return $response;
    }
}
