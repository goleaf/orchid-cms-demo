<?php

namespace App\Http\Requests\Students;

use App\Models\StudentStatus;
use App\Rules\DictionaryCodeRule;
use App\Rules\TranslatedDictionaryNameRequiredRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentStatusRequest extends FormRequest
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
        $statusId = $this->integer('status.id') ?: null;

        return [
            'status.id' => ['nullable', 'integer', Rule::exists(StudentStatus::class, 'id')],
            'status.code' => [
                'required',
                'string',
                'max:120',
                new DictionaryCodeRule,
                Rule::unique(StudentStatus::class, 'code')->ignore($statusId),
            ],
            'status.name' => ['nullable', 'string', 'max:255'],
            'status.name_translations' => ['required', 'array', new TranslatedDictionaryNameRequiredRule],
            'status.name_translations.*' => ['nullable', 'string', 'max:255'],
            'status.description_translations' => ['nullable', 'array'],
            'status.description_translations.*' => ['nullable', 'string', 'max:1000'],
            'status.color' => ['nullable', 'string', 'max:32'],
            'status.sort_order' => ['nullable', 'integer', 'min:0'],
            'status.is_default' => ['nullable', 'boolean'],
            'status.is_active' => ['nullable', 'boolean'],
            'status.is_final' => ['nullable', 'boolean'],
            'status.is_blocked' => ['nullable', 'boolean'],
            'status.is_archived' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function statusData(): array
    {
        $data = $this->validated('status');
        unset($data['id']);

        foreach (['is_default', 'is_active', 'is_final', 'is_blocked', 'is_archived'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    public function statusId(): ?int
    {
        $id = $this->validated('status.id', null);

        return filled($id) ? (int) $id : null;
    }
}
