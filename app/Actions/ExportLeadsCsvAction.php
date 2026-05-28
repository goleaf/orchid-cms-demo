<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportLeadsCsvAction
{
    /**
     * @param  Builder<MarketingLead>  $query
     *
     * @throws AuthorizationException
     */
    public function handle(Builder $query, ?User $user = null): StreamedResponse
    {
        if (! ($user?->hasAccess('crm.leads.export') ?? false)) {
            throw new AuthorizationException(tkey('crm.leads.validation.export_not_allowed'));
        }

        $includeMarketing = $user->hasAccess('crm.leads.view_marketing');

        return app(ExportMarketingLeadsCsvAction::class)->handle($query, $includeMarketing);
    }
}
