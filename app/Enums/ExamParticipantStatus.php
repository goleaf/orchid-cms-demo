<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum ExamParticipantStatus: string
{
    use HasEnumValues;

    case Registered = 'registered';
    case Admitted = 'admitted';
    case Blocked = 'blocked';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return tkey('exams.participants.statuses.'.$this->value);
    }
}
