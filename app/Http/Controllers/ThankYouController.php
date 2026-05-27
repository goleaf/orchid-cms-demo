<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ThankYouController extends Controller
{
    public function __invoke(): View
    {
        return view('site.thanks', [
            'seoTitle' => tkey('website.thanks.seo.title'),
            'seoDescription' => tkey('website.thanks.seo.description'),
        ]);
    }
}
