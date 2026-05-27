<?php

namespace App\Http\Controllers;

use App\Actions\GetSitemapAction;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(GetSitemapAction $sitemap): Response
    {
        return response()
            ->view('site.seo.sitemap', ['urls' => $sitemap->handle()], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
