<?php

namespace App\Enums;

enum AnalyticsDashboardAudience: string
{
    case Owner = 'owner';
    case Director = 'director';
    case Manager = 'manager';
    case Administrator = 'administrator';
    case Instructor = 'instructor';
    case Finance = 'finance';
    case Marketing = 'marketing';
    case System = 'system';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $audience): string => $audience->value,
            self::cases(),
        );
    }
}
