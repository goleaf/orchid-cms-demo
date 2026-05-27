<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

class LeadSourceListScreen extends LeadDictionaryListScreen
{
    public function query(string $dictionary = 'sources'): iterable
    {
        return parent::query('sources');
    }
}
