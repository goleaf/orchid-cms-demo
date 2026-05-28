<?php

namespace App\Actions;

use App\Enums\EnrollmentStatus as EnrollmentStatusEnum;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateStudentEnrollmentAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(StudentEnrollment $enrollment, array $data, ?User $user = null, bool $allowLockedUpdate = false): StudentEnrollment
    {
        if (($enrollment->is_completed || $enrollment->is_cancelled) && ! $this->canOverride($user, $allowLockedUpdate)) {
            throw ValidationException::withMessages([
                'enrollment' => $enrollment->is_completed
                    ? tkey('students.validation.completed_enrollment_locked')
                    : tkey('students.validation.cancelled_enrollment_locked'),
            ]);
        }

        $before = $enrollment->only([
            'training_program_id',
            'course_category_id',
            'branch_id',
            'status',
            'status_id',
        ]);
        $targetStatus = $data['status'] ?? null;
        $hasGroupChange = array_key_exists('training_group_id', $data);
        $targetGroupId = $data['training_group_id'] ?? null;
        $payload = $this->payload($data, $user);
        unset($payload['status']);

        $enrollment->forceFill($payload)->save();
        $enrollment = $enrollment->refresh();

        if (filled($targetStatus) && $this->scalar($targetStatus) !== $enrollment->status->value) {
            $enrollment = app(ChangeEnrollmentStatusAction::class)->handle($enrollment, $targetStatus, $user);
        }

        if ($hasGroupChange) {
            $enrollment = $this->syncGroup($enrollment->refresh(), $targetGroupId, $user, (bool) ($data['allow_overbooking'] ?? false));
        }

        app(RecordStudentActivityAction::class)->handle(
            $enrollment->student,
            $user,
            'enrollment_updated',
            tkey('students.activities.titles.enrollment_updated'),
            null,
            null,
            null,
            ['enrollment_id' => $enrollment->id],
            $enrollment,
        );

        $this->recordChangedFields($enrollment->refresh(), $before, $user);

        return $enrollment->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(array $data, ?User $user): array
    {
        $payload = [];

        foreach ([
            'lead_id',
            'training_program_id',
            'course_category_id',
            'branch_id',
            'status_id',
            'manager_id',
            'administrator_id',
            'instructor_id',
            'teacher_id',
            'started_at',
            'start_date',
            'planned_end_date',
            'actual_end_date',
            'completed_at',
            'preferred_time',
            'training_language',
            'format',
            'gearbox_type',
            'contracted_price_cents',
            'paid_cents',
            'price',
            'discount',
            'currency',
            'payment_status',
            'theory_progress',
            'practice_progress',
            'total_theory_hours',
            'completed_theory_hours',
            'total_practice_hours',
            'completed_practice_hours',
            'notes',
            'internal_notes',
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (array_key_exists('course_id', $data)) {
            $payload['training_program_id'] = $data['course_id'];
        }

        $payload['updated_by_id'] = $data['updated_by_id'] ?? $user?->id;

        return $payload;
    }

    private function syncGroup(StudentEnrollment $enrollment, mixed $targetGroupId, ?User $user, bool $allowOverbooking): StudentEnrollment
    {
        if (filled($targetGroupId)) {
            app(AddStudentToTrainingGroupAction::class)->handle($enrollment, (int) $targetGroupId, $user, $allowOverbooking);

            return $enrollment->refresh();
        }

        if ($enrollment->training_group_id === null) {
            return $enrollment;
        }

        $oldGroupId = (int) $enrollment->training_group_id;
        $oldGroup = TrainingGroup::query()
            ->select(['id', 'places_taken'])
            ->whereKey($oldGroupId)
            ->first();

        if ($oldGroup !== null) {
            $oldGroup->forceFill([
                'places_taken' => max(0, ((int) $oldGroup->places_taken) - 1),
            ])->save();
        }

        $enrollment->forceFill([
            'training_group_id' => null,
            'updated_by_id' => $user?->id ?? $enrollment->updated_by_id,
        ])->save();

        app(RecordStudentActivityAction::class)->handle(
            $enrollment->student,
            $user,
            'group_changed',
            tkey('students.activities.titles.group_changed'),
            null,
            (string) $oldGroupId,
            null,
            ['enrollment_id' => $enrollment->id],
            $enrollment->refresh(),
        );

        return $enrollment->refresh();
    }

    /**
     * @param  array<string, mixed>  $before
     */
    private function recordChangedFields(StudentEnrollment $enrollment, array $before, ?User $user): void
    {
        $types = [
            'training_program_id' => 'course_changed',
            'course_category_id' => 'course_changed',
            'branch_id' => 'branch_changed',
            'status' => 'enrollment_status_changed',
            'status_id' => 'enrollment_status_changed',
        ];

        foreach ($types as $field => $type) {
            if ($this->scalar($before[$field] ?? null) === $this->scalar($enrollment->getAttribute($field))) {
                continue;
            }

            app(RecordStudentActivityAction::class)->handle(
                $enrollment->student,
                $user,
                $type,
                tkey('students.activities.titles.enrollment_updated'),
                null,
                $this->scalar($before[$field] ?? null),
                $this->scalar($enrollment->getAttribute($field)),
                ['field' => $field, 'enrollment_id' => $enrollment->id],
                $enrollment,
            );
        }
    }

    private function canOverride(?User $user, bool $allowLockedUpdate): bool
    {
        return $allowLockedUpdate || ($user?->hasAccess('students.enrollments.update_locked') ?? false);
    }

    private function scalar(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        if ($value instanceof EnrollmentStatusEnum) {
            return $value->value;
        }

        return (string) $value;
    }
}
