<?php

namespace App\Http\Requests\Students;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExportStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('students.export') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:80'],
            'enrollment_status' => ['nullable', 'string', 'max:80'],
            'manager_id' => ['nullable', 'integer'],
            'administrator_id' => ['nullable', 'integer'],
            'course_id' => ['nullable', 'integer'],
            'training_program_id' => ['nullable', 'integer'],
            'course_category_id' => ['nullable', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'training_group_id' => ['nullable', 'integer'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date'],
            'only_active' => ['nullable', 'boolean'],
            'only_archived' => ['nullable', 'boolean'],
            'only_blocked' => ['nullable', 'boolean'],
            'only_with_active_enrollment' => ['nullable', 'boolean'],
            'only_without_active_enrollment' => ['nullable', 'boolean'],
            'only_waiting_documents' => ['nullable', 'boolean'],
            'only_waiting_payment' => ['nullable', 'boolean'],
            'only_waiting_start' => ['nullable', 'boolean'],
            'only_without_group' => ['nullable', 'boolean'],
            'segment' => ['nullable', 'string', 'max:80'],
        ];
    }
}
