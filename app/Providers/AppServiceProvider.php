<?php

namespace App\Providers;

use App\Services\LocaleManager;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::resolveMissingNamedRoutesUsing(function (string $name, mixed $parameters = [], bool $absolute = true): ?string {
            $aliases = [
                'site.home' => 'website.home',
                'site.contacts' => 'website.contacts',
                'site.courses.show' => 'website.courses.show',
                'site.branches.show' => 'website.branches.show',
            ];

            if (! isset($aliases[$name])) {
                return null;
            }

            return route($aliases[$name], $parameters, $absolute);
        });

        View::composer('site.layout', function ($view): void {
            $locales = app(LocaleManager::class);

            $view->with([
                'availableLocales' => $locales->activeLanguages(),
                'currentLocale' => app()->getLocale(),
            ]);
        });
    }
}
