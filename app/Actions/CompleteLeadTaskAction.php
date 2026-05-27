<?php

namespace App\Actions;

use App\Enums\LeadTaskStatus;
use App\Models\MarketingLeadTask;
use App\Models\User;

class CompleteLeadTaskAction
{
    public function handle(MarketingLeadTask $task, ?User $user): MarketingLeadTask
    {
        $task->fill([
            'status' => LeadTaskStatus::Done,
            'completed_at' => now(),
        ])->save();

        app(RecordLeadActivityAction::class)->handle(
            $task->marketingLead,
            $user,
            'task_completed',
            tkey('crm.activities.titles.task_completed'),
            $task->title,
            null,
            null,
            ['task_id' => $task->id],
        );

        return $task->refresh();
    }
}
