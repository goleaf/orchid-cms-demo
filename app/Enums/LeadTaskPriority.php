<?php

namespace App\Enums;

enum LeadTaskPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return tkey('crm.tasks.priorities.'.$this->value);
    }
}
