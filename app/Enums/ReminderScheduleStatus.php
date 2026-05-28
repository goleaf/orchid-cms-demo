<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum ReminderScheduleStatus: string
{
    use HasEnumValues;

    case Scheduled = 'scheduled';
    case Queued = 'queued';
    case Sent = 'sent';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return tkey('notifications.reminder_schedules.statuses.'.$this->value);
    }
}
