<?php

namespace App\Enums;

enum KpiMetricGroup: string
{
    case Sales = 'sales';
    case Finance = 'finance';
    case Students = 'students';
    case Education = 'education';
    case Lessons = 'lessons';
    case Driving = 'driving';
    case Documents = 'documents';
    case Exams = 'exams';
    case Notifications = 'notifications';
    case Staff = 'staff';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $group): string => $group->value,
            self::cases(),
        );
    }
}
