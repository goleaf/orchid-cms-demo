<?php

namespace App\Http\Requests\Students;

use App\Enums\EnrollmentPaymentStatus;
use App\Enums\EnrollmentStatus;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\Instructor;
use App\Models\Lead;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Rules\ActiveEnrollmentStatusRule;
use App\Rules\EnrollmentCanJoinGroupRule;
use App\Rules\EnrollmentNumberFormatRule;
use App\Rules\StudentEnrollmentCanBeUpdatedRule;
use App\Rules\ValidEnrollmentStatusTransitionRule;
use App\Rules\ValidGearboxTypeRule;
use App\Rules\ValidTrainingFormatRule;
use App\Rules\ValidTrainingLanguageRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.crm.students', 'students.manage_enrollments', 'students.enrollments.create', 'students.enrollments.update']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $enrollment = $this->routeEnrollment();

        return [
            'enrollment' => [new StudentEnrollmentCanBeUpdatedRule($enrollment, $this->user(), $this->boolean('override_locked_enrollment'))],
            'enrollment.id' => ['nullable', 'integer', Rule::exists(StudentEnrollment::class, 'id')],
            'enrollment.enrollment_number' => ['nullable', 'string', 'max:40', new EnrollmentNumberFormatRule],
            'enrollment.student_id' => ['nullable', 'integer', Rule::exists(Student::class, 'id')],
            'enrollment.student_profile_id' => ['nullable', 'integer', Rule::exists(Student::class, 'id')],
            'enrollment.lead_id' => ['nullable', 'integer', Rule::exists(Lead::class, 'id')],
            'enrollment.training_program_id' => ['required_without:enrollment.course_id', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'enrollment.course_id' => ['required_without:enrollment.training_program_id', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'enrollment.course_category_id' => ['nullable', 'integer', Rule::exists(CourseCategory::class, 'id')],
            'enrollment.branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'enrollment.training_group_id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id'), new EnrollmentCanJoinGroupRule($enrollment, $this->boolean('allow_overbooking'))],
            'enrollment.status' => [
                'nullable',
                Rule::enum(EnrollmentStatus::class),
                new ActiveEnrollmentStatusRule,
                new ValidEnrollmentStatusTransitionRule($enrollment, $this->user(), $this->boolean('override_status_transition')),
            ],
            'enrollment.status_id' => ['nullable', 'integer', Rule::exists(\App\Models\EnrollmentStatus::class, 'id')],
            'enrollment.manager_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'enrollment.administrator_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'enrollment.instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'enrollment.teacher_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'enrollment.start_date' => ['nullable', 'date'],
            'enrollment.planned_end_date' => ['nullable', 'date'],
            'enrollment.actual_end_date' => ['nullable', 'date'],
            'enrollment.preferred_time' => ['nullable', 'string', 'max:120'],
            'enrollment.training_language' => ['nullable', 'string', 'max:10', new ValidTrainingLanguageRule],
            'enrollment.format' => ['nullable', 'string', 'max:40', new ValidTrainingFormatRule],
            'enrollment.gearbox_type' => ['nullable', 'string', 'max:40', new ValidGearboxTypeRule],
            'enrollment.price' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'enrollment.discount' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'enrollment.currency' => ['nullable', 'string', 'size:3'],
            'enrollment.payment_status' => ['nullable', 'string', 'max:60', Rule::in(EnrollmentPaymentStatus::values())],
            'enrollment.notes' => ['nullable', 'string', 'max:2000'],
            'enrollment.internal_notes' => ['nullable', 'string', 'max:2000'],
            'create_onboarding_tasks' => ['nullable', 'boolean'],
            'allow_overbooking' => ['nullable', 'boolean'],
            'override_status_transition' => ['nullable', 'boolean'],
            'override_locked_enrollment' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function enrollmentData(): array
    {
        $enrollment = $this->validated('enrollment');
        unset($enrollment['id']);

        foreach ([
            'student_id',
            'student_profile_id',
            'lead_id',
            'training_program_id',
            'course_id',
            'course_category_id',
            'branch_id',
            'training_group_id',
            'status_id',
            'manager_id',
            'administrator_id',
            'instructor_id',
            'teacher_id',
        ] as $field) {
            if (array_key_exists($field, $enrollment)) {
                $enrollment[$field] = filled($enrollment[$field]) ? (int) $enrollment[$field] : null;
            }
        }

        if ($this->boolean('allow_overbooking')) {
            $enrollment['allow_overbooking'] = true;
        }

        return $enrollment;
    }

    public function routeEnrollment(): ?StudentEnrollment
    {
        $enrollment = $this->route('enrollment');

        if ($enrollment instanceof StudentEnrollment) {
            return $enrollment;
        }

        $id = $this->input('enrollment.id');

        return filled($id) ? StudentEnrollment::query()->find($id) : null;
    }
}
