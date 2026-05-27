<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

class LeadLostReasonListScreen extends LeadDictionaryListScreen
{
    public function query(string $dictionary = 'lost-reasons'): iterable
    {
        return parent::query('lost-reasons');
    }
}
