<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum CommunicationPriority: string
{
    use HasEnumValues;

    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return tkey('communication.reminders.priorities.'.$this->value);
    }
}
