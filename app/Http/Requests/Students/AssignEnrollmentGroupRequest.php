<?php

namespace App\Http\Requests\Students;

use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Rules\EnrollmentCanJoinGroupRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignEnrollmentGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.crm.students', 'students.enrollments.assign_group']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'training_group_id' => [
                'required',
                'integer',
                Rule::exists(TrainingGroup::class, 'id'),
                new EnrollmentCanJoinGroupRule($this->routeEnrollment(), $this->boolean('allow_overbooking')),
            ],
            'allow_overbooking' => ['nullable', 'boolean'],
        ];
    }

    public function groupId(): int
    {
        return (int) $this->validated('training_group_id');
    }

    public function routeEnrollment(): ?StudentEnrollment
    {
        $enrollment = $this->route('enrollment');

        return $enrollment instanceof StudentEnrollment ? $enrollment : null;
    }
}
