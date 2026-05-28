<?php

namespace App\Enums;

enum KpiPeriod: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Quarter = 'quarter';
}
