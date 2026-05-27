<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\User;
use Illuminate\Support\Str;

class SaveMarketingLeadCrmAction
{
    public function __construct(
        private readonly UpdateMarketingLeadCrmAction $updateLead,
        private readonly MoveLeadToStatusAction $moveLead,
        private readonly RecordLeadActivityAction $recordActivity,
    ) {}

    /**
     * @param  array<string, mixed>  $leadData
     * @param  array<int, int>  $tagIds
     */
    public function handle(
        ?MarketingLead $lead,
        array $leadData,
        LeadStatus $targetStatus,
        ?User $user,
        mixed $budgetEur = null,
        array $tagIds = [],
    ): MarketingLead {
        $lead ??= new MarketingLead([
            'uuid' => (string) Str::uuid(),
            'lead_number' => app(GenerateLeadNumberAction::class)->handle(),
            'status' => LeadStatus::New,
            'last_status_changed_at' => now(),
            'created_by_user_id' => $user?->id,
        ]);

        $isNew = ! $lead->exists;
        $currentStatus = $lead->status instanceof LeadStatus
            ? $lead->status
            : LeadStatus::New;
        $consentAccepted = (bool) ($leadData['consent_accepted'] ?? $lead->consent_accepted);

        $lead = $this->updateLead->handle($lead, [
            ...$leadData,
            'status' => $currentStatus,
            'budget_eur' => $budgetEur,
            'updated_by_user_id' => $user?->id,
            'consent_accepted_at' => $consentAccepted ? ($lead->consent_accepted_at ?? now()) : null,
        ]);

        $lead->tags()->sync($tagIds);
        $lead = $lead->refresh();

        if ($currentStatus !== $targetStatus) {
            $lead = $this->moveLead->handle(
                $lead,
                $targetStatus,
                $user,
                tkey('crm.activities.reasons.crm_card_status_update'),
            );
        } elseif ($isNew) {
            $lead->statusHistories()->create([
                'user_id' => $user?->id,
                'from_status' => null,
                'to_status' => $targetStatus,
                'reason' => tkey('crm.activities.reasons.manual_lead_created'),
                'changed_at' => now(),
            ]);
        }

        if ($isNew) {
            $this->recordActivity->handle(
                $lead->refresh(),
                $user,
                'created',
                tkey('crm.activities.titles.created'),
                tkey('crm.activities.messages.manual_lead_created'),
            );
        }

        return $lead->refresh();
    }
}
