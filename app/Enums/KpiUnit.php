<?php

namespace App\Enums;

enum KpiUnit: string
{
    case Count = 'count';
    case Percent = 'percent';
    case Money = 'money';
    case Hours = 'hours';
    case Days = 'days';
    case Ratio = 'ratio';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $unit): string => $unit->value,
            self::cases(),
        );
    }
}
