<?php

namespace App\Actions;

use App\Models\LandingPage;

class GetHomePageAction
{
    /**
     * @return array{page: LandingPage, offers: array<int, array{title: string, body: string}>}
     */
    public function handle(): array
    {
        $page = LandingPage::query()
            ->publicHome()
            ->firstOrFail();

        return [
            'page' => $page,
            'offers' => $page->offerCards(),
        ];
    }
}
