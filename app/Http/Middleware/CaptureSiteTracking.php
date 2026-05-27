<?php

namespace App\Http\Middleware;

use App\Support\Site\SiteTracking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureSiteTracking
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        SiteTracking::capture($request);

        return $next($request);
    }
}
