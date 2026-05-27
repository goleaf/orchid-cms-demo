<?php

namespace App\Enums;

enum GroupStatus: string
{
    case Planned = 'planned';
    case Recruiting = 'recruiting';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
