<?php

namespace App\Actions;

use App\Models\ReminderRule;
use App\Models\ReminderSchedule;

class ScheduleReminderAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ReminderRule $rule, array $data): ReminderSchedule
    {
        return $rule->schedules()->create([
            'target_type' => $data['target_type'],
            'target_id' => $data['target_id'],
            'message_id' => $data['message_id'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'status' => $data['status'] ?? ReminderSchedule::STATUS_SCHEDULED,
            'metadata' => $data['metadata'] ?? null,
        ])->refresh();
    }
}
