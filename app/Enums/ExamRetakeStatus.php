<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum ExamRetakeStatus: string
{
    use HasEnumValues;

    case Planned = 'planned';
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return tkey('exams.retakes.statuses.'.$this->value);
    }
}
