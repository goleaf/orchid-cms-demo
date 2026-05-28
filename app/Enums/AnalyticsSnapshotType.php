<?php

namespace App\Enums;

enum AnalyticsSnapshotType: string
{
    case OwnerDashboard = 'owner_dashboard';
    case SalesSummary = 'sales_summary';
    case FinanceSummary = 'finance_summary';
    case EducationSummary = 'education_summary';
    case DrivingSummary = 'driving_summary';
    case ExamSummary = 'exam_summary';
    case DocumentSummary = 'document_summary';
    case NotificationSummary = 'notification_summary';

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
