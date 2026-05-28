<?php

namespace App\Enums;

enum KpiDirection: string
{
    case Increase = 'increase';
    case Decrease = 'decrease';
    case Maintain = 'maintain';
}
