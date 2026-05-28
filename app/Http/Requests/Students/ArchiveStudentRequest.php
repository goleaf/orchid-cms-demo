<?php

namespace App\Http\Requests\Students;

use App\Models\Student;
use App\Rules\StudentCanBeArchivedRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ArchiveStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.crm.students', 'students.archive']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student' => [new StudentCanBeArchivedRule($this->routeStudent(), $this->user(), $this->boolean('override_active_enrollment'))],
            'override_active_enrollment' => ['nullable', 'boolean'],
        ];
    }

    public function routeStudent(): ?Student
    {
        $student = $this->route('student');

        return $student instanceof Student ? $student : null;
    }
}
