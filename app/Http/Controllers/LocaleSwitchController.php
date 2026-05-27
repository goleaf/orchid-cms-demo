<?php

namespace App\Http\Controllers;

use App\Services\LocaleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleSwitchController extends Controller
{
    public function __invoke(Request $request, LocaleManager $locales): RedirectResponse
    {
        $locale = (string) $request->input('locale', '');

        if (! $locales->switch($request, $locale)) {
            return back()->withErrors([
                'locale' => tkey('locale.messages.unavailable'),
            ]);
        }

        return back()->with('status', tkey('locale.messages.saved'));
    }
}
