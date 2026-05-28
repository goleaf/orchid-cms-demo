<?php

namespace App\Http\Requests\Students;

use App\Models\StudentEnrollment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddStudentNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.crm.students', 'students.update']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:4000'],
            'enrollment_id' => ['nullable', 'integer', Rule::exists(StudentEnrollment::class, 'id')],
        ];
    }
}
