<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum StudentTaskStatus: string
{
    use HasEnumValues;

    case Open = 'open';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return tkey('students.tasks.statuses.'.$this->value);
    }
}
