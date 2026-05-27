<?php

namespace App\Http\Controllers;

use App\Services\LocaleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, LocaleManager $locales): RedirectResponse
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
