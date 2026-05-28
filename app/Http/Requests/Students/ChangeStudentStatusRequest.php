<?php

namespace App\Http\Requests\Students;

use App\Enums\StudentStatus;
use App\Models\Student;
use App\Rules\ActiveStudentStatusRule;
use App\Rules\ValidStudentStatusTransitionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeStudentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.crm.students', 'students.change_status']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(StudentStatus::class),
                new ActiveStudentStatusRule,
                new ValidStudentStatusTransitionRule($this->routeStudent(), $this->user(), $this->boolean('override_status_transition')),
            ],
            'override_status_transition' => ['nullable', 'boolean'],
        ];
    }

    public function targetStatus(): StudentStatus
    {
        return StudentStatus::from($this->validated('status'));
    }

    public function routeStudent(): ?Student
    {
        $student = $this->route('student');

        return $student instanceof Student ? $student : null;
    }
}
