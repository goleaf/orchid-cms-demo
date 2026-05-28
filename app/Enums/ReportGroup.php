<?php

namespace App\Enums;

enum ReportGroup: string
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
    case System = 'system';

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
