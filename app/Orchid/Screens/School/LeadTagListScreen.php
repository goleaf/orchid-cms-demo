<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

class LeadTagListScreen extends LeadDictionaryListScreen
{
    public function query(string $dictionary = 'tags'): iterable
    {
        return parent::query('tags');
    }

    public function permission(): iterable
    {
        return ['crm.leads.manage_dictionaries', 'crm.leads.manage_tags'];
    }
}
