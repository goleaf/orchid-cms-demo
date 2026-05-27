<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = config('app.locale', 'ru');

        try {
            if (Schema::hasTable('languages')) {
                $defaultLocale = Language::defaultCode();
            }
        } catch (QueryException) {
            $defaultLocale = config('app.locale', 'ru');
        }

        $sessionLocale = $request->hasSession()
            ? $request->session()->get('locale')
            : null;

        $locale = is_string($sessionLocale) && $sessionLocale !== ''
            ? $sessionLocale
            : $defaultLocale;

        try {
            if (Schema::hasTable('languages') && ! in_array($locale, Language::activeCodes(), true)) {
                $locale = $defaultLocale;
            }
        } catch (QueryException) {
            $locale = $defaultLocale;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
