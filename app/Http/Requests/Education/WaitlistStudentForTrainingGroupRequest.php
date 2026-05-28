<?php

namespace App\Http\Requests\Education;

use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Rules\StudentEnrollmentNotAlreadyInActiveGroupRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WaitlistStudentForTrainingGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess([
            'education.groups.manage_students',
            'education.manage_memberships',
        ]) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'training_group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'enrollment_id' => ['required', 'integer', Rule::exists(StudentEnrollment::class, 'id'), new StudentEnrollmentNotAlreadyInActiveGroupRule],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
