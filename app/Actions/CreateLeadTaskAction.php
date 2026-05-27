<?php

namespace App\Actions;

use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Models\MarketingLead;
use App\Models\MarketingLeadTask;
use App\Models\User;
use Illuminate\Support\Carbon;

class CreateLeadTaskAction
{
    public function handle(
        MarketingLead $lead,
        ?User $createdBy,
        string $title,
        ?Carbon $dueAt,
        LeadTaskPriority $priority = LeadTaskPriority::Normal,
        ?string $notes = null,
        ?int $assignedToUserId = null,
    ): MarketingLeadTask {
        $task = $lead->tasks()->create([
            'assigned_to_user_id' => $assignedToUserId ?? $lead->responsible_manager_id,
            'created_by_user_id' => $createdBy?->id,
            'title' => $title,
            'status' => LeadTaskStatus::Open,
            'priority' => $priority,
            'due_at' => $dueAt,
            'completed_at' => null,
            'notes' => $notes,
        ]);

        app(RecordLeadActivityAction::class)->handle(
            $lead,
            $createdBy,
            'task_created',
            tkey('crm.activities.titles.task_created'),
            $title,
            null,
            null,
            ['task_id' => $task->id],
        );

        return $task;
    }
}
