<?php

namespace App\Http\Controllers;

use App\Actions\GetSitePageAction;
use App\Models\SitePage;
use Illuminate\Contracts\View\View;

class SitePageController extends Controller
{
    public function show(SitePage $sitePage, GetSitePageAction $page): View
    {
        return view('site.page', $page->handle($sitePage));
    }
}
