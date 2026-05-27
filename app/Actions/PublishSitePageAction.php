<?php

namespace App\Actions;

use App\Models\SitePage;
use App\Rules\PublishedPageRequirementRule;
use Illuminate\Support\Facades\Validator;

class PublishSitePageAction
{
    public function handle(SitePage $page): SitePage
    {
        Validator::make(
            [
                'page' => [
                    'id' => $page->getKey(),
                    'slug' => $page->slug,
                ],
                'title_translations' => $page->title_translations,
                'content_translations' => $page->content_translations,
                'publish' => true,
            ],
            ['publish' => [new PublishedPageRequirementRule]],
        )->validate();

        $page->forceFill([
            'is_active' => true,
            'published_at' => $page->published_at ?? now(),
        ])->save();

        return $page->refresh();
    }
}
