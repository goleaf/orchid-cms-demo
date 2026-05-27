<?php

use App\Services\TranslationService;

if (! function_exists('tkey')) {
    function tkey(string $key, array $replace = [], ?string $locale = null): string
    {
        return app(TranslationService::class)->get($key, $replace, $locale);
    }
}
