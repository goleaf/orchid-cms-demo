<?php

namespace App\Http\Requests\Students;

use App\Rules\DictionaryItemCanBeDeletedRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteStudentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('students.manage_statuses') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'record' => [
                'required',
                'integer',
                new DictionaryItemCanBeDeletedRule('student-statuses'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'record.required' => tkey('students.validation.dictionary_record_required'),
            'record.integer' => tkey('students.validation.dictionary_record_unavailable'),
        ];
    }

    public function recordId(): int
    {
        return (int) $this->validated('record');
    }
}
