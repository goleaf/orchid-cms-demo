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
                ->orderBy('city')
                ->get(),
            'seoTitle' => 'Contacts and branches | DrivePro Academy',
            'seoDescription' => 'Branch contacts, phones, addresses, callback request, chat access, and public map for DrivePro Academy.',
        ];
    }
}
