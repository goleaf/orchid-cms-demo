<?php

namespace App\Http\Requests\Students;

use App\Enums\StudentStatus;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Student;
use App\Models\User;
use App\Rules\ActiveStudentStatusRule;
use App\Rules\StudentCanBeUpdatedRule;
use App\Rules\StudentNumberFormatRule;
use App\Rules\StudentPhoneOrEmailRequiredRule;
use App\Rules\UniqueStudentContactRule;
use App\Rules\ValidStudentStatusTransitionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.crm.students', 'students.create', 'students.update']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $student = $this->routeStudent();

        return [
            'student' => [
                new StudentCanBeUpdatedRule($student, $this->user(), $this->boolean('override_locked_student')),
                new UniqueStudentContactRule($student, $this->user(), $this->boolean('override_duplicate_contact')),
            ],
            'student.id' => ['nullable', 'integer', Rule::exists(Student::class, 'id')],
            'student.uuid' => ['nullable', 'uuid'],
            'student.student_number' => ['nullable', 'string', 'max:40', new StudentNumberFormatRule],
            'student.user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'student.branch_id' => ['required', 'integer', Rule::exists(Branch::class, 'id')],
            'student.full_name' => ['nullable', 'string', 'max:240'],
            'student.first_name' => ['required', 'string', 'max:120'],
            'student.last_name' => ['required', 'string', 'max:120'],
            'student.middle_name' => ['nullable', 'string', 'max:120'],
            'student.email' => [
                'nullable',
                'email:rfc',
                'max:190',
                new StudentPhoneOrEmailRequiredRule('student.phone', 'student.email'),
            ],
            'student.phone' => ['nullable', 'string', 'max:60'],
            'student.date_of_birth' => ['nullable', 'date'],
            'student.national_id' => ['nullable', 'string', 'max:80'],
            'student.personal_code' => ['nullable', 'string', 'max:80'],
            'student.gender' => ['nullable', 'string', 'max:40'],
            'student.preferred_messenger' => ['nullable', 'string', 'max:80'],
            'student.telegram_username' => ['nullable', 'string', 'max:120'],
            'student.whatsapp_phone' => ['nullable', 'string', 'max:60'],
            'student.emergency_contact_name' => ['nullable', 'string', 'max:190'],
            'student.emergency_contact_phone' => ['nullable', 'string', 'max:60'],
            'student.address' => ['nullable', 'string', 'max:2000'],
            'student.city' => ['nullable', 'string', 'max:120'],
            'student.locale' => ['nullable', 'string', 'max:10'],
            'student.source' => ['nullable', 'string', 'max:120'],
            'student.status' => [
                'nullable',
                Rule::enum(StudentStatus::class),
                new ActiveStudentStatusRule,
                new ValidStudentStatusTransitionRule($student, $this->user(), $this->boolean('override_status_transition')),
            ],
            'student.status_id' => ['nullable', 'integer', Rule::exists(\App\Models\StudentStatus::class, 'id')],
            'student.manager_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'student.administrator_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'student.source_lead_id' => ['nullable', 'integer', Rule::exists(Lead::class, 'id')],
            'student.source_id' => ['nullable', 'integer', Rule::exists(LeadSource::class, 'id')],
            'student.source_label' => ['nullable', 'string', 'max:120'],
            'student.consent_accepted' => ['nullable', 'boolean'],
            'student.consent_accepted_at' => ['nullable', 'date'],
            'student.consent_text_version' => ['nullable', 'string', 'max:120'],
            'student.notes' => ['nullable', 'string', 'max:2000'],
            'student.comment' => ['nullable', 'string', 'max:2000'],
            'student.internal_comment' => ['nullable', 'string', 'max:2000'],
            'create_onboarding_tasks' => ['nullable', 'boolean'],
            'override_status_transition' => ['nullable', 'boolean'],
            'override_duplicate_contact' => ['nullable', 'boolean'],
            'override_locked_student' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function studentData(): array
    {
        $student = $this->validated('student');
        unset($student['id']);

        foreach ([
            'user_id',
            'branch_id',
            'status_id',
            'manager_id',
            'administrator_id',
            'source_lead_id',
            'source_id',
        ] as $field) {
            if (array_key_exists($field, $student)) {
                $student[$field] = filled($student[$field]) ? (int) $student[$field] : null;
            }
        }

        return $student;
    }

    public function routeStudent(): ?Student
    {
        $student = $this->route('student');

        if ($student instanceof Student) {
            return $student;
        }

        $studentId = $this->input('student.id');

        return filled($studentId) ? Student::query()->find($studentId) : null;
    }
}
