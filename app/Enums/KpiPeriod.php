<?php

namespace App\Enums;

enum KpiPeriod: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Quarter = 'quarter';
    case Year = 'year';
    case Custom = 'custom';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $period): string => $period->value,
            self::cases(),
        );
    }
}
