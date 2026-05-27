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
    ): MarketingLeadTask {
        return $lead->tasks()->create([
            'assigned_to_user_id' => $lead->responsible_manager_id,
            'created_by_user_id' => $createdBy?->id,
            'title' => $title,
            'status' => LeadTaskStatus::Open,
            'priority' => $priority,
            'due_at' => $dueAt,
            'completed_at' => null,
            'notes' => $notes,
        ]);
    }
}
