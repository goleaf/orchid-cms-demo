<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportLeadsCsvAction
{
    public function handle(Builder $query, ?User $user = null): StreamedResponse
    {
        $includeMarketing = $user?->hasAnyAccess([
            'crm.leads.view_marketing',
            'website.view_marketing',
        ]) ?? false;

        return app(ExportMarketingLeadsCsvAction::class)->handle($query, $includeMarketing);
    }
}
