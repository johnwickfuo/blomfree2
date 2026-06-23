<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives the Filament admin panel its own session cookie so an admin and a
 * regular customer can be signed in to the same browser at the same time
 * without their sessions overwriting each other. Must run BEFORE
 * Illuminate\Session\Middleware\StartSession — `StartSession` reads the
 * cookie name from config('session.cookie') when it boots the session.
 */
class UseAdminSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        config(['session.cookie' => config('session.cookie').'_admin']);

        return $next($request);
    }
}
