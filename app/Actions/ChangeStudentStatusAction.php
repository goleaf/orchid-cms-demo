<?php

namespace App\Actions;

use App\Enums\StudentStatus as StudentStatusEnum;
use App\Models\Student;
use App\Models\StudentStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ChangeStudentStatusAction
{
    public function handle(Student $student, StudentStatusEnum|string $status, ?User $user = null, bool $allowOverride = false): Student
    {
        $targetStatus = $status instanceof StudentStatusEnum ? $status : StudentStatusEnum::from((string) $status);
        $override = $allowOverride || ($user?->hasAccess('students.override_status_transition') ?? false);

        if (! $override && ! $this->transitionAllowed($student->status, $targetStatus)) {
            throw ValidationException::withMessages([
                'status' => tkey('students.validation.invalid_student_status_transition'),
            ]);
        }

        $oldStatus = $student->status->value;
        $student->forceFill([
            'status' => $targetStatus->value,
            'status_id' => StudentStatus::query()->where('code', $targetStatus->value)->value('id') ?: $student->status_id,
            'updated_by_id' => $user?->id ?? $student->updated_by_id,
        ])->save();

        app(RecordStudentActivityAction::class)->handle(
            $student->refresh(),
            $user,
            'status_changed',
            tkey('students.activities.titles.status_changed'),
            null,
            $oldStatus,
            $targetStatus->value,
        );

        return $student->refresh();
    }

    public function transitionAllowed(StudentStatusEnum $from, StudentStatusEnum $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to->value, $this->allowedTransitions()[$from->value] ?? [], true);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function allowedTransitions(): array
    {
        return [
            'active' => ['inactive', 'blocked', 'archived'],
            'inactive' => ['active', 'archived'],
            'blocked' => ['active', 'archived'],
            'lead' => ['active', 'inactive', 'blocked', 'archived', 'enrolled'],
            'enrolled' => ['active', 'inactive', 'blocked', 'archived', 'graduated'],
            'graduated' => ['archived'],
        ];
    }
}
