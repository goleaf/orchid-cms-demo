<?php

namespace App\Http\Middleware;

use App\Actions\StoreUtmInSessionAction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureSiteTracking
{
    public function __construct(private readonly StoreUtmInSessionAction $tracking) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->tracking->handle($request);

        return $next($request);
    }
}
