<?php

namespace App\Providers;

use App\Services\LocaleManager;
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
        View::composer('site.layout', function ($view): void {
            $locales = app(LocaleManager::class);

            $view->with([
                'availableLocales' => $locales->activeLanguages(),
                'currentLocale' => app()->getLocale(),
            ]);
        });
    }
}
