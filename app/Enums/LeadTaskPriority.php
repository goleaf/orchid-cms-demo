<?php

namespace App\Enums;

enum LeadTaskPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
}
