<?php

namespace App\Providers;

use App\Actions\Security\RecordFailedLoginAction;
use App\Actions\Security\RecordSecurityEventAction;
use App\Listeners\Security\RecordFailedLoginListener;
use App\Listeners\Security\RecordLogoutListener;
use App\Listeners\Security\RecordSuccessfulLoginListener;
use App\Models\User;
use App\Services\LocaleManager;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

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

        Event::listen(Authenticated::class, function (Authenticated $event): void {
            if (! $event->user instanceof User || ! $event->user->isLockedOut()) {
                return;
            }

            app(RecordSecurityEventAction::class)->handle('login.blocked', $event->user, 'warning');
            app(RecordFailedLoginAction::class)->handle($event->user, $event->user->email, null, request(), $event->guard);

            Auth::guard($event->guard)->logout();

            throw ValidationException::withMessages([
                'email' => tkey('security.validation.account_locked'),
            ]);
        });

        Event::listen(Login::class, RecordSuccessfulLoginListener::class);
        Event::listen(Failed::class, RecordFailedLoginListener::class);
        Event::listen(Logout::class, RecordLogoutListener::class);
    }
}
