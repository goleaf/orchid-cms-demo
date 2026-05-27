<?php

namespace App\Actions;

use App\Models\SitePage;

class UnpublishSitePageAction
{
    public function handle(SitePage $page): SitePage
    {
        $page->forceFill([
            'is_active' => false,
            'published_at' => null,
        ])->save();

        return $page->refresh();
    }
}
