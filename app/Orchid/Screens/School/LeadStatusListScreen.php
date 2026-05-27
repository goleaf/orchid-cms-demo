<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

class LeadStatusListScreen extends LeadDictionaryListScreen
{
    public function query(string $dictionary = 'statuses'): iterable
    {
        return parent::query('statuses');
    }
}
