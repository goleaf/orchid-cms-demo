<?php

namespace App\Actions;

use App\Models\SitePage;

class CreateOrUpdateSitePageAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?SitePage $page, array $attributes): SitePage
    {
        $page ??= new SitePage;
        $attributes = app(GenerateSeoMetadataAction::class)->handle(
            $attributes,
            ['title'],
            ['excerpt', 'subtitle', 'content'],
        );

        $page->fill($attributes);
        $page->save();

        return $page->refresh();
    }
}
