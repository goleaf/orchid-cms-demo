<?php

namespace App\Actions;

use App\Actions\Concerns\AssignsSortablePosition;
use App\Models\SitePage;

class CreateOrUpdateSitePageAction
{
    use AssignsSortablePosition;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?SitePage $page, array $attributes): SitePage
    {
        $page ??= new SitePage;
        $attributes = $this->assignSortablePosition($page, $attributes);
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
