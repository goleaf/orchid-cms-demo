<?php

namespace App\Providers;

use App\Actions\Security\CloseUserSessionAction;
use App\Actions\Security\RecordLoginAttemptAction;
use App\Actions\Security\RecordSecurityEventAction;
use App\Actions\Security\RecordUserSessionAction;
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

            Auth::guard($event->guard)->logout();

            throw ValidationException::withMessages([
                'email' => tkey('security.validation.account_locked'),
            ]);
        });

        Event::listen(Login::class, function (Login $event): void {
            if (! $event->user instanceof User) {
                return;
            }

            $request = request();

            app(RecordLoginAttemptAction::class)->handle($event->user, $event->user->email, true, null, $request);
            app(RecordUserSessionAction::class)->handle($event->user, $request->hasSession() ? $request->session()->getId() : null, $request);
        });

        Event::listen(Failed::class, function (Failed $event): void {
            $identifier = is_array($event->credentials)
                ? (string) ($event->credentials['email'] ?? $event->credentials['login'] ?? '')
                : '';

            app(RecordLoginAttemptAction::class)->handle(
                $event->user instanceof User ? $event->user : null,
                $identifier,
                false,
                'invalid_credentials',
                request(),
            );
        });

        Event::listen(Logout::class, function (): void {
            app(CloseUserSessionAction::class)->handle(null, request());
        });
    }
}
