<?php

namespace App\Http\Requests\Students;

use App\Enums\EnrollmentStatus;
use App\Models\StudentEnrollment;
use App\Rules\ActiveEnrollmentStatusRule;
use App\Rules\ValidEnrollmentStatusTransitionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeEnrollmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.crm.students', 'students.enrollments.change_status']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(EnrollmentStatus::class),
                new ActiveEnrollmentStatusRule,
                new ValidEnrollmentStatusTransitionRule($this->routeEnrollment(), $this->user(), $this->boolean('override_status_transition')),
            ],
            'override_status_transition' => ['nullable', 'boolean'],
        ];
    }

    public function targetStatus(): EnrollmentStatus
    {
        return EnrollmentStatus::from($this->validated('status'));
    }

    public function routeEnrollment(): ?StudentEnrollment
    {
        $enrollment = $this->route('enrollment');

        return $enrollment instanceof StudentEnrollment ? $enrollment : null;
    }
}
