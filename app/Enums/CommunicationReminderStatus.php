<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum CommunicationReminderStatus: string
{
    use HasEnumValues;

    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return tkey('communication.reminders.statuses.'.$this->value);
    }
}
