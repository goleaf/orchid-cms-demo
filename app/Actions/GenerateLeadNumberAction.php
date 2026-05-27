<?php

namespace App\Actions;

use App\Models\MarketingLead;
use Carbon\CarbonInterface;

class GenerateLeadNumberAction
{
    public function handle(?CarbonInterface $date = null, ?string $prefix = null): string
    {
        $date ??= now();
        $prefix ??= (string) config('crm.leads.number_prefix', 'LEAD');
        $year = $date->format('Y');
        $base = $prefix.'-'.$year.'-';
        $sequence = MarketingLead::query()
            ->withTrashed()
            ->where('lead_number', 'like', $base.'%')
            ->count() + 1;

        do {
            $leadNumber = $base.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (
            MarketingLead::query()
                ->withTrashed()
                ->where('lead_number', $leadNumber)
                ->exists()
        );

        return $leadNumber;
    }
}
