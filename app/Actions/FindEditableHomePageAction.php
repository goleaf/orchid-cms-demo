<?php

namespace App\Actions;

use App\Models\LandingPage;

class FindEditableHomePageAction
{
    public function handle(): LandingPage
    {
        return LandingPage::query()
            ->editableHome()
            ->firstOrFail();
    }
}
