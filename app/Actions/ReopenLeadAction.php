<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReopenLeadAction
{
    public function handle(
        MarketingLead $lead,
        ?User $user = null,
        LeadStatus|string|null $status = null,
        ?string $reason = null,
    ): MarketingLead {
        if ($lead->is_converted && ! ($user?->hasAccess('crm.leads.override_status_transition') ?? false)) {
            throw ValidationException::withMessages([
                'lead' => tkey('crm.leads.validation.lead_already_converted'),
            ]);
        }

        $targetStatus = $status === null
            ? LeadStatus::New
            : ($status instanceof LeadStatus ? $status : LeadStatus::from((string) $status));

        if ($targetStatus->isFinal()) {
            throw ValidationException::withMessages([
                'status' => tkey('crm.leads.validation.invalid_status_transition'),
            ]);
        }

        $lead->forceFill([
            'closed_at' => null,
            'status' => $targetStatus,
            'last_status_changed_at' => now(),
            'updated_by_user_id' => $user?->id ?? $lead->updated_by_user_id,
        ])->save();

        app(RecordLeadActivityAction::class)->handle(
            $lead->refresh(),
            $user,
            'reopened',
            tkey('crm.activities.titles.reopened'),
            $reason,
            null,
            $targetStatus->label(),
        );

        return $lead->refresh();
    }
}
