<?php

namespace App\Enums;

enum InstructorStatus: string
{
    case Active = 'active';
    case Vacation = 'vacation';
    case Inactive = 'inactive';
}
