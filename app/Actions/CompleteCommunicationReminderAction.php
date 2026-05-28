<?php

namespace App\Actions;

use App\Models\CommunicationReminder;
use App\Models\User;

class CompleteCommunicationReminderAction
{
    public function handle(CommunicationReminder $reminder, ?User $completedBy = null): CommunicationReminder
    {
        $reminder->forceFill([
            'status' => CommunicationReminder::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by_id' => $completedBy?->id,
        ])->save();

        return $reminder->refresh();
    }
}
