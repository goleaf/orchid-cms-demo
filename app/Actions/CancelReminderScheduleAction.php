<?php

namespace App\Actions;

use App\Models\ReminderSchedule;

class CancelReminderScheduleAction
{
    public function handle(ReminderSchedule $schedule): ReminderSchedule
    {
        $schedule->forceFill([
            'status' => ReminderSchedule::STATUS_CANCELLED,
            'processed_at' => now(),
        ])->save();

        return $schedule->refresh();
    }
}
