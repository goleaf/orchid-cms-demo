<?php

namespace App\Enums;

enum DashboardWidgetType: string
{
    case Counter = 'counter';
    case Chart = 'chart';
    case Table = 'table';
    case Funnel = 'funnel';
    case Progress = 'progress';
    case Ranking = 'ranking';
    case Alert = 'alert';
    case CalendarSummary = 'calendar_summary';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $type): string => $type->value,
            self::cases(),
        );
    }
}
