<?php

namespace App\Orchid\Screens\School\Concerns;

use App\Enums\ExamAttemptStatus as LegacyExamAttemptStatus;
use App\Enums\ExamSessionStatus as LegacyExamSessionStatus;
use App\Enums\ExamType as LegacyExamType;
use App\Models\Branch;
use App\Models\ExamActivity;
use App\Models\ExamAttempt;
use App\Models\ExamChecklistItem;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\ExamType;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait InteractsWithExamScreens
{
    /**
     * @return array<int, string>
     */
    protected function examTypeOptions(bool $activeOnly = true): array
    {
        return ExamType::query()
            ->select(['id', 'code', 'name', 'name_translations', 'is_active', 'sort_order'])
            ->when($activeOnly, fn (Builder $query): Builder => $query->active())
            ->ordered()
            ->get()
            ->mapWithKeys(fn (ExamType $type): array => [$type->id => $type->displayName()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function examStatusOptions(bool $activeOnly = true): array
    {
        return \App\Models\ExamStatus::query()
            ->select(['id', 'code', 'name', 'name_translations', 'sort_order', 'is_active'])
            ->when($activeOnly, fn (Builder $query): Builder => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (\App\Models\ExamStatus $status): array => [$status->id => $status->displayName()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function attemptStatusOptions(bool $activeOnly = true): array
    {
        return \App\Models\ExamAttemptStatus::query()
            ->select(['id', 'code', 'name', 'name_translations', 'sort_order', 'is_active'])
            ->when($activeOnly, fn (Builder $query): Builder => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (\App\Models\ExamAttemptStatus $status): array => [$status->id => $status->displayName()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function resultStatusOptions(bool $activeOnly = true): array
    {
        return \App\Models\ExamResultStatus::query()
            ->select(['id', 'code', 'name', 'name_translations', 'sort_order', 'is_active'])
            ->when($activeOnly, fn (Builder $query): Builder => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (\App\Models\ExamResultStatus $status): array => [$status->id => $status->displayName()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function branchOptions(): array
    {
        return Branch::query()
            ->forAdminList()
            ->orderBy('sort_order')
            ->orderBy('city')
            ->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->displayName()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function groupOptions(): array
    {
        return TrainingGroup::query()
            ->operationalList()
            ->orderBy('starts_on')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (TrainingGroup $group): array => [$group->id => $group->displayName()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function examinerOptions(): array
    {
        return User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(200)
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function vehicleOptions(): array
    {
        return Vehicle::query()
            ->select(['id', 'registration_number', 'make', 'model'])
            ->orderBy('registration_number')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (Vehicle $vehicle): array => [
                $vehicle->id => trim($vehicle->registration_number.' '.$vehicle->make.' '.$vehicle->model),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function sessionOptions(): array
    {
        return ExamSession::query()
            ->forExamList()
            ->with([
                'typeRecord:id,code,name,name_translations',
                'statusRecord:id,code,name,name_translations',
            ])
            ->orderByDesc('scheduled_at')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (ExamSession $session): array => [
                $session->id => trim($session->exam_number.' '.$this->sessionTypeLabel($session).' '.$this->dateTime($session->scheduled_at ?? $session->starts_at)),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function enrollmentOptions(): array
    {
        return StudentEnrollment::query()
            ->forAdminList()
            ->with([
                'student:id,first_name,last_name,full_name,student_number',
                'trainingProgram:id,title,title_translations,name_translations',
            ])
            ->active()
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (StudentEnrollment $enrollment): array => [
                $enrollment->id => $this->enrollmentLabel($enrollment),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function studentOptions(): array
    {
        return Student::query()
            ->select(['id', 'first_name', 'last_name', 'full_name', 'student_number'])
            ->orderBy('full_name')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (Student $student): array => [$student->id => $student->display_name])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function yesNoOptions(): array
    {
        return [
            '1' => tkey('common.status.yes'),
            '0' => tkey('common.status.no'),
        ];
    }

    protected function sessionTypeLabel(ExamSession $session): string
    {
        return $session->typeRecord?->displayName()
            ?: $this->legacyExamTypeLabel($session->exam_type);
    }

    protected function sessionStatusLabel(ExamSession $session): string
    {
        return $session->statusRecord?->displayName()
            ?: $this->legacySessionStatusLabel($session->status);
    }

    protected function attemptStatusLabel(ExamAttempt $attempt): string
    {
        return $attempt->statusRecord?->displayName()
            ?: $this->legacyAttemptStatusLabel($attempt->status);
    }

    protected function resultStatusLabel(ExamResult $result): string
    {
        return $result->resultStatus?->displayName() ?? $this->dash();
    }

    protected function activityTypeLabel(ExamActivity $activity): string
    {
        return tkey('exams.activities.types.'.$activity->type);
    }

    protected function checklistTitle(ExamChecklistItem $item): string
    {
        return $item->displayTitle();
    }

    protected function enrollmentLabel(?StudentEnrollment $enrollment): string
    {
        if ($enrollment === null) {
            return $this->dash();
        }

        return trim(collect([
            $enrollment->enrollment_number,
            $enrollment->student?->display_name,
            $enrollment->trainingProgram?->displayTitle(),
        ])->filter()->implode(' '));
    }

    protected function dateTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return $this->dash();
        }

        return Carbon::parse($value)->format('Y-m-d H:i');
    }

    protected function boolLabel(mixed $value): string
    {
        return (bool) $value ? tkey('common.status.yes') : tkey('common.status.no');
    }

    protected function dash(): string
    {
        return '-';
    }

    private function legacyExamTypeLabel(LegacyExamType|string|null $type): string
    {
        $value = $type instanceof LegacyExamType ? $type->value : (string) $type;

        return filled($value) ? tkey('exams.types.'.$value) : $this->dash();
    }

    private function legacySessionStatusLabel(LegacyExamSessionStatus|string|null $status): string
    {
        $value = $status instanceof LegacyExamSessionStatus ? $status->value : (string) $status;

        return filled($value) ? tkey('exams.statuses.'.$value) : $this->dash();
    }

    private function legacyAttemptStatusLabel(LegacyExamAttemptStatus|string|null $status): string
    {
        $value = $status instanceof LegacyExamAttemptStatus ? $status->value : (string) $status;

        return filled($value) ? tkey('exams.attempt_statuses.'.$value) : $this->dash();
    }
}
