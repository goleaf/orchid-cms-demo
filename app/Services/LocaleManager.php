<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class LocaleManager
{
    public const SESSION_KEY = 'locale';

    public const USER_COLUMN = 'preferred_locale';

    public function resolve(Request $request): string
    {
        $defaultLocale = $this->defaultLocale();
        $sessionLocale = $request->hasSession()
            ? $request->session()->get(self::SESSION_KEY)
            : null;

        if ($this->isActiveLocale($sessionLocale)) {
            return (string) $sessionLocale;
        }

        $userLocale = $this->userPreferredLocale($request);

        if ($this->isActiveLocale($userLocale)) {
            if ($request->hasSession()) {
                $request->session()->put(self::SESSION_KEY, $userLocale);
            }

            return $userLocale;
        }

        return $this->isActiveLocale($defaultLocale)
            ? $defaultLocale
            : config('app.locale', 'ru');
    }

    public function apply(Request $request): string
    {
        $locale = $this->resolve($request);

        App::setLocale($locale);

        return $locale;
    }

    public function switch(Request $request, string $locale): bool
    {
        if (! $this->isActiveLocale($locale)) {
            return false;
        }

        if ($request->hasSession()) {
            $request->session()->put(self::SESSION_KEY, $locale);
        }

        $this->saveUserPreference($request, $locale);
        App::setLocale($locale);

        return true;
    }

    public function defaultLocale(): string
    {
        try {
            if (Schema::hasTable('languages')) {
                return Language::defaultCode();
            }
        } catch (Throwable) {
        }

        return config('app.fallback_locale', config('app.locale', 'ru'));
    }

    /**
     * @return array<int, string>
     */
    public function activeCodes(): array
    {
        try {
            if (Schema::hasTable('languages')) {
                $codes = Language::activeCodes();

                return $codes === []
                    ? [$this->defaultLocale()]
                    : $codes;
            }
        } catch (Throwable) {
        }

        return [$this->defaultLocale()];
    }

    /**
     * @return Collection<int, mixed>
     */
    public function activeLanguages(): Collection
    {
        try {
            if (Schema::hasTable('languages')) {
                $languages = Language::active()
                    ->ordered()
                    ->get();

                if ($languages->isNotEmpty()) {
                    return $languages;
                }
            }
        } catch (Throwable) {
        }

        $defaultLocale = $this->defaultLocale();

        return collect([
            (object) [
                'code' => $defaultLocale,
                'name' => Str::upper($defaultLocale),
                'native_name' => Str::upper($defaultLocale),
                'is_default' => true,
                'is_active' => true,
            ],
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function languageOptions(): array
    {
        return $this->activeLanguages()
            ->mapWithKeys(fn (mixed $language): array => [
                (string) $language->code => (string) ($language->native_name ?: $language->name ?: Str::upper((string) $language->code)),
            ])
            ->all();
    }

    public function isActiveLocale(mixed $locale): bool
    {
        return is_string($locale)
            && $locale !== ''
            && in_array($locale, $this->activeCodes(), true);
    }

    private function userPreferredLocale(Request $request): ?string
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $locale = $user->getAttribute(self::USER_COLUMN);

        return is_string($locale) && $locale !== ''
            ? $locale
            : null;
    }

    private function saveUserPreference(Request $request, string $locale): void
    {
        $user = $request->user();

        if ($user === null || ! $this->usersHavePreferredLocale()) {
            return;
        }

        $user->forceFill([
            self::USER_COLUMN => $locale,
        ])->save();
    }

    private function usersHavePreferredLocale(): bool
    {
        try {
            return Schema::hasTable('users') && Schema::hasColumn('users', self::USER_COLUMN);
        } catch (Throwable) {
            return false;
        }
    }
}
