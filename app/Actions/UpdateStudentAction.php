<?php

namespace App\Actions;

use App\Enums\StudentStatus as StudentStatusEnum;
use App\Models\Student;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateStudentAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Student $student, array $data, ?User $user = null, bool $allowArchivedUpdate = false): Student
    {
        if ($student->is_archived && ! $this->canOverride($user, $allowArchivedUpdate)) {
            throw ValidationException::withMessages([
                'student' => tkey('students.validation.archived_student_locked'),
            ]);
        }

        $before = $student->only(['status', 'status_id', 'manager_id', 'phone', 'email']);
        $targetStatus = $data['status'] ?? null;
        $payload = $this->payload($student, $data, $user);
        unset($payload['status']);

        $student->forceFill($payload)->save();
        $student = $student->refresh();

        if (filled($targetStatus) && $this->scalar($targetStatus) !== $student->status->value) {
            $student = app(ChangeStudentStatusAction::class)->handle($student, $targetStatus, $user);
        }

        app(RecordStudentActivityAction::class)->handle(
            $student->refresh(),
            $user,
            'updated',
            tkey('students.activities.titles.updated'),
        );

        $this->recordChangedFields($student->refresh(), $before, $user);

        return $student->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(Student $student, array $data, ?User $user): array
    {
        $payload = [];

        foreach ([
            'user_id',
            'branch_id',
            'full_name',
            'first_name',
            'last_name',
            'middle_name',
            'email',
            'date_of_birth',
            'national_id',
            'personal_code',
            'gender',
            'preferred_messenger',
            'telegram_username',
            'whatsapp_phone',
            'emergency_contact_name',
            'emergency_contact_phone',
            'address',
            'city',
            'locale',
            'source',
            'status_id',
            'manager_id',
            'administrator_id',
            'source_lead_id',
            'source_id',
            'source_label',
            'consent_accepted',
            'consent_accepted_at',
            'consent_text_version',
            'notes',
            'comment',
            'internal_comment',
            'documents_summary',
            'payment_summary',
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (array_key_exists('phone', $data)) {
            $payload['phone'] = app(NormalizeStudentPhoneAction::class)->handle($data['phone']);
            $payload['normalized_phone'] = $payload['phone'];
        }

        $payload['updated_by_id'] = $data['updated_by_id'] ?? $user?->id ?? $student->updated_by_id;

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $before
     */
    private function recordChangedFields(Student $student, array $before, ?User $user): void
    {
        foreach (['status', 'status_id', 'manager_id', 'phone', 'email'] as $field) {
            if ($this->scalar($before[$field] ?? null) === $this->scalar($student->getAttribute($field))) {
                continue;
            }

            app(RecordStudentActivityAction::class)->handle(
                $student,
                $user,
                $field === 'status' || $field === 'status_id' ? 'status_changed' : 'updated',
                tkey('students.activities.titles.updated'),
                null,
                $this->scalar($before[$field] ?? null),
                $this->scalar($student->getAttribute($field)),
                ['field' => $field],
            );
        }
    }

    private function canOverride(?User $user, bool $allowArchivedUpdate): bool
    {
        return $allowArchivedUpdate || ($user?->hasAccess('students.update_archived') ?? false);
    }

    private function scalar(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        if ($value instanceof StudentStatusEnum) {
            return $value->value;
        }

        return (string) $value;
    }
}
