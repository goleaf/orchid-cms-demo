<?php

namespace App\Actions;

use App\Models\SitePage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetSitePageAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(SitePage $page): array
    {
        $publicPage = SitePage::query()
            ->active()
            ->published()
            ->bySlug($page->slug)
            ->first();

        if ($publicPage === null) {
            throw new NotFoundHttpException;
        }

        return [
            'page' => $publicPage,
            'seoTitle' => $publicPage->displaySeoTitle(),
            'seoDescription' => $publicPage->displaySeoDescription() ?: tkey('website.seo.default_description'),
            'ogTitle' => $publicPage->displayOgTitle(),
            'ogDescription' => $publicPage->displayOgDescription() ?: tkey('website.seo.default_description'),
            'ogImage' => $publicPage->og_image,
            'canonical' => $publicPage->canonical_url ?: route('website.pages.show', $publicPage),
            'isIndexable' => $publicPage->is_indexable,
        ];
    }
}
