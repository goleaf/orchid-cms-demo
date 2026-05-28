<?php

namespace App\Http\Requests\Students;

use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\Student;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertLeadToStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['students.convert_from_lead', 'crm.leads.convert']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'use_existing_student' => ['nullable', 'boolean'],
            'existing_student_id' => ['nullable', 'integer', Rule::exists(Student::class, 'id')],
            'student.full_name' => ['nullable', 'string', 'max:240'],
            'student.first_name' => ['nullable', 'string', 'max:120'],
            'student.last_name' => ['nullable', 'string', 'max:120'],
            'student.middle_name' => ['nullable', 'string', 'max:120'],
            'student.phone' => ['nullable', 'string', 'max:60'],
            'student.email' => ['nullable', 'email:rfc', 'max:190'],
            'student.city' => ['nullable', 'string', 'max:120'],
            'student.locale' => ['nullable', 'string', 'max:10'],
            'student.date_of_birth' => ['nullable', 'date'],
            'student.personal_code' => ['nullable', 'string', 'max:80'],
            'enrollment.course_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'enrollment.training_program_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'enrollment.course_category_id' => ['nullable', 'integer', Rule::exists(CourseCategory::class, 'id')],
            'enrollment.branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'enrollment.training_group_id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'enrollment.status_id' => ['nullable', 'integer', Rule::exists(\App\Models\EnrollmentStatus::class, 'id')],
            'enrollment.start_date' => ['nullable', 'date'],
            'enrollment.planned_end_date' => ['nullable', 'date'],
            'enrollment.preferred_time' => ['nullable', 'string', 'max:120'],
            'enrollment.training_language' => ['nullable', 'string', 'max:10'],
            'enrollment.format' => ['nullable', 'string', 'max:40'],
            'enrollment.gearbox_type' => ['nullable', 'string', 'max:40'],
            'enrollment.price' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'enrollment.payment_status' => ['nullable', 'string', 'max:60'],
            'create_onboarding_tasks' => ['nullable', 'boolean'],
            'create_document_placeholders' => ['nullable', 'boolean'],
            'create_payment_placeholder' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function studentData(): array
    {
        return $this->validated('student') ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function enrollmentData(): array
    {
        $data = $this->validated('enrollment') ?? [];

        if (isset($data['course_id']) && ! isset($data['training_program_id'])) {
            $data['training_program_id'] = $data['course_id'];
        }

        return $data;
    }

    public function existingStudentId(): ?int
    {
        if (! $this->boolean('use_existing_student')) {
            return null;
        }

        return filled($this->validated('existing_student_id', null))
            ? (int) $this->validated('existing_student_id')
            : null;
    }
}
