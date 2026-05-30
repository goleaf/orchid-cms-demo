<?php

namespace App\Http\Middleware;

use App\Actions\Security\TouchUserSecuritySessionAction;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TouchUserSecuritySession
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $user = $request->user();

            if ($user instanceof User) {
                app(TouchUserSecuritySessionAction::class)->handle($user, null, $request);
            }
        } catch (Throwable) {
            //
        }

        return $response;
    }
}
