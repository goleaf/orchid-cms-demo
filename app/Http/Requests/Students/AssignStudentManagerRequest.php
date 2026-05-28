<?php

namespace App\Http\Requests\Students;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignStudentManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.crm.students', 'students.assign']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'manager_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'assign_open_tasks' => ['nullable', 'boolean'],
        ];
    }

    public function managerId(): ?int
    {
        $managerId = $this->validated('manager_id', null);

        return filled($managerId) ? (int) $managerId : null;
    }
}
