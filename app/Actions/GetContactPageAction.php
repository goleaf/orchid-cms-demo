<?php

namespace App\Actions;

use App\Models\Branch;

class GetContactPageAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        return [
            'branches' => Branch::query()
                ->forAdminList()
                ->withCount(['instructors', 'vehicles', 'groups'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('city')
                ->get(),
            'seoTitle' => tkey('website.contacts.seo.title'),
            'seoDescription' => tkey('website.contacts.seo.description'),
        ];
    }
}
