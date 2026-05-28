<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\MarketingLeadCommunication;
use App\Models\User;
use Illuminate\Support\Carbon;

class LogLeadCallAction
{
    public function handle(
        MarketingLead $lead,
        ?User $user,
        string $result,
        ?int $durationSeconds = null,
        ?string $comment = null,
        ?Carbon $nextFollowUpAt = null,
        ?string $lostReasonCode = null,
    ): MarketingLeadCommunication {
        $lead->forceFill([
            'last_contacted_at' => now(),
            'contacted_at' => $lead->contacted_at ?? now(),
            'next_follow_up_at' => $nextFollowUpAt ?? $lead->next_follow_up_at,
        ])->save();

        $communication = app(AddLeadCommunicationAction::class)->handle(
            $lead->refresh(),
            $user,
            'phone',
            'outbound',
            tkey('crm.communications.system_subjects.consultation_call'),
            $comment,
            ['result' => $result],
            null,
            in_array($result, ['reached', 'ready_to_pay'], true),
            false,
            null,
            null,
            null,
            $result,
            $durationSeconds,
        );

        $targetStatus = match ($result) {
            'no_answer' => LeadStatus::NoAnswer,
            'reached' => LeadStatus::Contacted,
            'ready_to_pay' => LeadStatus::WaitingPayment,
            default => null,
        };

        if ($result === 'refused' && filled($lostReasonCode)) {
            app(MarkLeadLostAction::class)->handle(
                $lead->refresh(),
                (string) $lostReasonCode,
                $comment,
                $user,
            );
        } elseif ($targetStatus !== null) {
            app(ChangeLeadStatusAction::class)->handle(
                $lead->refresh(),
                $targetStatus,
                $user,
                $comment,
                allowOverride: true,
            );
        }

        if ($nextFollowUpAt !== null) {
            $lead->refresh()
                ->forceFill(['next_follow_up_at' => $nextFollowUpAt])
                ->save();
        }

        return $communication->refresh();
    }
}
