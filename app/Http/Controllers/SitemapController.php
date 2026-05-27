<?php

namespace App\Http\Controllers;

use App\Actions\GenerateSitemapAction;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(GenerateSitemapAction $sitemap): Response
    {
        return response()
            ->view('site.seo.sitemap', ['urls' => $sitemap->handle()], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
