<?php

namespace App\Actions\Analytics;

use App\Enums\DocumentStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\KpiDirection;
use App\Enums\KpiPeriod;
use App\Enums\KpiSnapshotStatus;
use App\Enums\LessonStatus;
use App\Enums\PaymentStatus;
use App\Models\DrivingLesson;
use App\Models\Enrollment;
use App\Models\ExamSession;
use App\Models\KpiMetric;
use App\Models\KpiSnapshot;
use App\Models\KpiTarget;
use App\Models\MarketingLead;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Models\StudentProfile;
use Carbon\CarbonInterface;

class RecordKpiSnapshotAction
{
    /**
     * @param  array<string, mixed>  $sourcePayload
     */
    public function handle(
        KpiMetric $metric,
        KpiPeriod|string $period = KpiPeriod::Day,
        CarbonInterface|string|null $snapshotDate = null,
        float|int|string|null $value = null,
        array $sourcePayload = [],
    ): KpiSnapshot {
        $period = $period instanceof KpiPeriod ? $period : KpiPeriod::from($period);
        $date = $snapshotDate instanceof CarbonInterface
            ? $snapshotDate->toDateString()
            : (filled($snapshotDate) ? (string) $snapshotDate : now()->toDateString());
        $value = (float) ($value ?? $this->calculateValue($metric->code));
        $target = $this->targetFor($metric, $period, $date);

        return KpiSnapshot::query()->updateOrCreate(
            [
                'kpi_metric_id' => $metric->id,
                'period' => $period->value,
                'snapshot_date' => $date,
                'branch_id' => $target?->branch_id,
                'training_program_id' => $target?->training_program_id,
                'training_group_id' => $target?->training_group_id,
            ],
            [
                'value' => $value,
                'target_value' => $target?->target_value,
                'status' => $this->statusFor($value, $target),
                'source_payload' => $sourcePayload,
                'calculated_at' => now(),
            ],
        );
    }

    private function targetFor(KpiMetric $metric, KpiPeriod $period, string $date): ?KpiTarget
    {
        return KpiTarget::query()
            ->where('kpi_metric_id', $metric->id)
            ->where('period', $period->value)
            ->where('starts_on', '<=', $date)
            ->where(function ($query) use ($date): void {
                $query->whereNull('ends_on')
                    ->orWhere('ends_on', '>=', $date);
            })
            ->orderByDesc('starts_on')
            ->first();
    }

    private function statusFor(float $value, ?KpiTarget $target): KpiSnapshotStatus
    {
        if ($target === null) {
            return KpiSnapshotStatus::Neutral;
        }

        $targetValue = (float) $target->target_value;
        $warningValue = $target->warning_value !== null ? (float) $target->warning_value : null;

        return match ($target->direction) {
            KpiDirection::Decrease => $value <= $targetValue
                ? KpiSnapshotStatus::OnTrack
                : (($warningValue !== null && $value <= $warningValue) ? KpiSnapshotStatus::Warning : KpiSnapshotStatus::OffTrack),
            KpiDirection::Maintain => abs($value - $targetValue) <= ($warningValue ?? 0.01)
                ? KpiSnapshotStatus::OnTrack
                : KpiSnapshotStatus::Warning,
            default => $value >= $targetValue
                ? KpiSnapshotStatus::OnTrack
                : (($warningValue !== null && $value >= $warningValue) ? KpiSnapshotStatus::Warning : KpiSnapshotStatus::OffTrack),
        };
    }

    private function calculateValue(string $code): float
    {
        return (float) match ($code) {
            'open_leads' => MarketingLead::query()->open()->count(),
            'converted_leads' => MarketingLead::query()->converted()->count(),
            'active_students' => StudentProfile::query()->active()->count(),
            'active_enrollments' => Enrollment::query()->where('status', EnrollmentStatus::Active->value)->count(),
            'lessons_today' => DrivingLesson::query()
                ->where('status', LessonStatus::Scheduled->value)
                ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])
                ->count(),
            'scheduled_exams' => ExamSession::query()->upcoming()->count(),
            'paid_revenue_eur' => Payment::query()->where('status', PaymentStatus::Paid->value)->sum('amount_cents') / 100,
            'pending_documents' => StudentDocument::query()
                ->whereIn('status', [DocumentStatus::Missing->value, DocumentStatus::Submitted->value])
                ->count(),
            default => 0,
        };
    }
}
