<?php

namespace App\Actions;

use App\Models\LandingPage;

class UpdateLandingPageAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(LandingPage $page, array $attributes): LandingPage
    {
        $page->fill($attributes);
        $page->save();

        return $page;
    }
}
