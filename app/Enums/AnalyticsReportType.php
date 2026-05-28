<?php

namespace App\Enums;

enum AnalyticsReportType: string
{
    case Operational = 'operational';
    case Sales = 'sales';
    case Education = 'education';
    case Finance = 'finance';
    case Exams = 'exams';
}
