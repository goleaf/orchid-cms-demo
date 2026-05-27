<?php

namespace App\Enums;

enum LeadTaskStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return tkey('crm.tasks.statuses.'.$this->value);
    }
}
