<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ChangeLeadStatusAction
{
    public function __construct(private readonly MoveLeadToStatusAction $moveLead) {}

    public function handle(
        MarketingLead $lead,
        LeadStatus|string $status,
        ?User $user = null,
        ?string $reason = null,
        ?string $lostReasonCode = null,
        bool $allowOverride = false,
    ): MarketingLead {
        $targetStatus = $status instanceof LeadStatus ? $status : LeadStatus::from((string) $status);
        $override = $allowOverride || ($user?->hasAccess('crm.leads.override_status_transition') ?? false);

        if ($lead->is_converted && ! $override && $targetStatus !== LeadStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => tkey('crm.leads.validation.lead_already_converted'),
            ]);
        }

        if (! $override && ! $this->transitionAllowed($lead->status, $targetStatus)) {
            throw ValidationException::withMessages([
                'status' => tkey('crm.leads.validation.invalid_status_transition'),
            ]);
        }

        if ($lostReasonCode !== null) {
            $lead->forceFill(['lost_reason_code' => $lostReasonCode])->save();
        }

        return $this->moveLead->handle($lead->refresh(), $targetStatus, $user, $reason);
    }

    public function transitionAllowed(LeadStatus $from, LeadStatus $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to->value, $this->allowedTransitions()[$from->value] ?? [], true);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function allowedTransitions(): array
    {
        return [
            'new' => ['no_answer', 'contacted', 'consultation', 'waiting_documents', 'waiting_payment', 'lost', 'duplicate', 'spam'],
            'no_answer' => ['contacted', 'no_answer', 'lost', 'archived'],
            'contacted' => ['consultation', 'consultation_done', 'waiting_documents', 'waiting_payment', 'lost', 'archived'],
            'consultation' => ['waiting_documents', 'waiting_payment', 'ready_to_enroll', 'lost'],
            'consultation_done' => ['waiting_documents', 'waiting_payment', 'ready_to_enroll', 'lost'],
            'waiting_documents' => ['waiting_payment', 'ready_to_enroll', 'lost'],
            'waiting_payment' => ['ready_to_enroll', 'enrolled', 'lost'],
            'ready_to_enroll' => ['enrolled', 'lost'],
            'lost' => ['archived'],
            'rejected' => ['archived'],
            'duplicate' => ['archived'],
            'spam' => ['archived'],
            'enrolled' => ['archived'],
            'assigned_to_group' => ['enrolled', 'became_student', 'archived'],
            'became_student' => ['archived'],
        ];
    }
}
