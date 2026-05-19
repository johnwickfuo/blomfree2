<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAffiliate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_affiliate) {
            return redirect()->route('affiliate.landing');
        }

        $affiliate = $user->affiliate;

        if (! $affiliate) {
            return redirect()->route('affiliate.landing');
        }

        if ($affiliate->isSuspended()) {
            return redirect()->route('affiliate.suspended');
        }

        return $next($request);
    }
}
