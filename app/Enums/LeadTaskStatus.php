<?php

namespace App\Enums;

enum LeadTaskStatus: string
{
    case Open = 'open';
    case Done = 'done';
    case Cancelled = 'cancelled';
}
