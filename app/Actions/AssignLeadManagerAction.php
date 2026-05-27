<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\User;

class AssignLeadManagerAction
{
    public function handle(MarketingLead $lead, ?User $manager, ?User $assignedBy): MarketingLead
    {
        $oldManager = $lead->responsibleManager?->name;

        $lead->fill([
            'responsible_manager_id' => $manager?->id,
            'assigned_by_user_id' => $assignedBy?->id,
            'assigned_at' => now(),
        ])->save();

        $lead->tasks()
            ->open()
            ->update(['assigned_to_user_id' => $manager?->id]);

        app(RecordLeadActivityAction::class)->handle(
            $lead->refresh(),
            $assignedBy,
            'manager_assigned',
            tkey('crm.activities.titles.manager_assigned'),
            null,
            $oldManager,
            $manager?->name,
        );

        return $lead->refresh();
    }
}
