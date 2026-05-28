<?php

namespace App\Support\Crm;

use Illuminate\Database\Eloquent\Model;

class LeadDictionaryUsage
{
    public function isUsed(string $dictionary, Model $item): bool
    {
        if (! method_exists($item, 'leads')) {
            return false;
        }

        return $item->leads()->exists();
    }
}
