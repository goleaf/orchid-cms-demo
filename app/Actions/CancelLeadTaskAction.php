<?php

namespace App\Actions;

use App\Enums\LeadTaskStatus;
use App\Models\MarketingLeadTask;
use App\Models\User;

class CancelLeadTaskAction
{
    public function handle(MarketingLeadTask $task, ?User $user, ?string $reason = null): MarketingLeadTask
    {
        $task->fill([
            'status' => LeadTaskStatus::Cancelled,
            'cancelled_at' => now(),
            'notes' => $reason ?? $task->notes,
        ])->save();

        app(RecordLeadActivityAction::class)->handle(
            $task->marketingLead,
            $user,
            'task_cancelled',
            tkey('crm.activities.titles.task_cancelled'),
            $reason ?: $task->title,
            null,
            null,
            ['task_id' => $task->id],
        );

        return $task->refresh();
    }
}
