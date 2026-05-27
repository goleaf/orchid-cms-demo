<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Models\MarketingLead;
use App\Models\User;
use Illuminate\Support\Carbon;

class MoveLeadToStatusAction
{
    public function handle(MarketingLead $lead, LeadStatus $status, ?User $user, ?string $reason = null): MarketingLead
    {
        $fromStatus = $lead->status;

        if ($fromStatus === $status) {
            return $lead;
        }

        $lead->fill([
            'status' => $status,
            'last_status_changed_at' => now(),
            'next_follow_up_at' => $this->nextFollowUpFor($status),
            'contacted_at' => $status === LeadStatus::Contacted && $lead->contacted_at === null
                ? now()
                : $lead->contacted_at,
            'last_contacted_at' => in_array($status, [LeadStatus::Contacted, LeadStatus::Consultation, LeadStatus::ConsultationDone], true)
                ? now()
                : $lead->last_contacted_at,
            'closed_at' => $status->isFinal() && $lead->closed_at === null
                ? now()
                : ($status->isFinal() ? $lead->closed_at : null),
            'converted_at' => $status->isSuccess() && $lead->converted_at === null
                ? now()
                : $lead->converted_at,
        ])->save();

        $lead->statusHistories()->create([
            'user_id' => $user?->id,
            'from_status' => $fromStatus,
            'to_status' => $status,
            'reason' => $reason,
            'changed_at' => now(),
        ]);

        if ($this->shouldCreateReminder($status)) {
            app(CreateLeadTaskAction::class)->handle(
                $lead->refresh(),
                $user,
                tkey('crm.tasks.system_titles.follow_up', ['status' => $status->label()]),
                $lead->next_follow_up_at,
                $lead->is_hot ? LeadTaskPriority::High : LeadTaskPriority::Normal,
                tkey('crm.tasks.system_notes.status_move_reminder'),
            );
        }

        app(RecordLeadActivityAction::class)->handle(
            $lead->refresh(),
            $user,
            'status_changed',
            tkey('crm.activities.titles.status_changed'),
            $reason,
            $fromStatus?->label(),
            $status->label(),
        );

        return $lead->refresh();
    }

    private function nextFollowUpFor(LeadStatus $status): ?Carbon
    {
        return match ($status) {
            LeadStatus::New, LeadStatus::NoAnswer => now()->addHour(),
            LeadStatus::Contacted => now()->addDay(),
            LeadStatus::Consultation, LeadStatus::ConsultationDone => now()->addDays(2),
            LeadStatus::WaitingDocuments, LeadStatus::WaitingPayment => now()->addDay(),
            LeadStatus::ReadyToEnroll, LeadStatus::AssignedToGroup => now()->addDays(3),
            LeadStatus::Enrolled, LeadStatus::BecameStudent, LeadStatus::Lost, LeadStatus::Rejected, LeadStatus::Duplicate, LeadStatus::Spam, LeadStatus::Archived => null,
        };
    }

    private function shouldCreateReminder(LeadStatus $status): bool
    {
        return in_array($status->value, LeadStatus::openPipelineValues(), true);
    }
}
