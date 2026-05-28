<?php

namespace App\Actions;

use App\Enums\StudentStatus as StudentStatusEnum;
use App\Models\Branch;
use App\Models\Student;
use App\Models\StudentStatus;
use App\Models\User;
use Illuminate\Support\Str;

class CreateStudentAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null, bool $createOnboardingTasks = false): Student
    {
        $uuid = (string) ($data['uuid'] ?? Str::uuid());
        $phone = app(NormalizeStudentPhoneAction::class)->handle($data['phone'] ?? null);
        $statusCode = $this->statusCode($data['status'] ?? null, $data['status_id'] ?? null);
        $name = $this->nameParts($data, $uuid);

        $student = Student::query()->create([
            'uuid' => $uuid,
            'student_number' => $data['student_number'] ?? app(GenerateStudentNumberAction::class)->handle(),
            'user_id' => $data['user_id'] ?? null,
            'branch_id' => $data['branch_id'] ?? $this->defaultBranchId(),
            'full_name' => $data['full_name'] ?? $name['full_name'],
            'first_name' => $name['first_name'],
            'last_name' => $name['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'email' => $data['email'] ?? $this->placeholderEmail($uuid),
            'phone' => $phone,
            'normalized_phone' => $phone,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'national_id' => $data['national_id'] ?? $data['personal_code'] ?? null,
            'personal_code' => $data['personal_code'] ?? $data['national_id'] ?? null,
            'gender' => $data['gender'] ?? null,
            'preferred_messenger' => $data['preferred_messenger'] ?? null,
            'telegram_username' => $data['telegram_username'] ?? null,
            'whatsapp_phone' => $data['whatsapp_phone'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'locale' => $data['locale'] ?? app()->getLocale(),
            'source' => $data['source'] ?? $data['source_label'] ?? 'manual',
            'status' => $statusCode,
            'status_id' => $data['status_id'] ?? $this->statusId($statusCode),
            'manager_id' => $data['manager_id'] ?? null,
            'administrator_id' => $data['administrator_id'] ?? null,
            'source_lead_id' => $data['source_lead_id'] ?? $data['lead_id'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'source_label' => $data['source_label'] ?? null,
            'consent_accepted' => (bool) ($data['consent_accepted'] ?? false),
            'consent_accepted_at' => $data['consent_accepted_at'] ?? ((bool) ($data['consent_accepted'] ?? false) ? now() : null),
            'consent_text_version' => $data['consent_text_version'] ?? null,
            'notes' => $data['notes'] ?? null,
            'comment' => $data['comment'] ?? null,
            'internal_comment' => $data['internal_comment'] ?? null,
            'portal_access_created_at' => $data['portal_access_created_at'] ?? null,
            'documents_summary' => $data['documents_summary'] ?? null,
            'payment_summary' => $data['payment_summary'] ?? null,
            'created_by_id' => $data['created_by_id'] ?? $user?->id,
            'updated_by_id' => $data['updated_by_id'] ?? $user?->id,
            'registered_at' => $data['registered_at'] ?? now(),
        ]);

        app(RecordStudentActivityAction::class)->handle(
            $student,
            $user,
            'created_manually',
            tkey('students.activities.titles.created_manually'),
            null,
        );

        if ($createOnboardingTasks || (bool) ($data['create_onboarding_tasks'] ?? false)) {
            app(CreateStudentOnboardingTasksAction::class)->handle($student->refresh(), $user);
        }

        return $student->refresh();
    }

    private function statusCode(mixed $status, mixed $statusId): string
    {
        if (filled($status)) {
            return $status instanceof StudentStatusEnum ? $status->value : (string) $status;
        }

        if (filled($statusId)) {
            $code = StudentStatus::query()->whereKey($statusId)->value('code');

            if (filled($code)) {
                return (string) $code;
            }
        }

        return StudentStatus::query()
            ->where('is_default', true)
            ->value('code') ?: StudentStatusEnum::Active->value;
    }

    private function statusId(string $statusCode): ?int
    {
        return StudentStatus::query()
            ->where('code', $statusCode)
            ->value('id');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{full_name: string, first_name: string, last_name: string}
     */
    private function nameParts(array $data, string $uuid): array
    {
        $fullName = trim((string) ($data['full_name'] ?? trim((string) ($data['first_name'] ?? '').' '.(string) ($data['last_name'] ?? ''))));
        $firstName = filled($data['first_name'] ?? null)
            ? (string) $data['first_name']
            : trim(str($fullName)->before(' ')->toString());
        $lastName = filled($data['last_name'] ?? null)
            ? (string) $data['last_name']
            : trim(str($fullName)->after(' ')->toString());

        return [
            'full_name' => $fullName ?: 'Student '.$uuid,
            'first_name' => $firstName ?: 'Student',
            'last_name' => $lastName ?: $uuid,
        ];
    }

    private function placeholderEmail(string $uuid): string
    {
        return 'student-'.$uuid.'@example.invalid';
    }

    private function defaultBranchId(): ?int
    {
        return Branch::query()
            ->orderBy('id')
            ->value('id');
    }
}
