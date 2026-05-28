<?php

namespace App\Http\Requests\Students;

use App\Models\EnrollmentStatus;
use App\Rules\EnrollmentStatusCodeRule;
use App\Rules\OnlyOneDefaultEnrollmentStatusRule;
use App\Rules\SystemDictionaryCodeProtectedRule;
use App\Rules\TranslatedDictionaryNameRequiredRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollmentStatusRequest extends FormRequest
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
            'status.id' => ['nullable', 'integer', Rule::exists(EnrollmentStatus::class, 'id')],
            'status.code' => [
                'required',
                'string',
                'max:120',
                new EnrollmentStatusCodeRule,
                new SystemDictionaryCodeProtectedRule('enrollment-statuses', $statusId),
                Rule::unique(EnrollmentStatus::class, 'code')->ignore($statusId),
            ],
            'status.name' => ['nullable', 'string', 'max:255'],
            'status.name_translations' => ['required', 'array', new TranslatedDictionaryNameRequiredRule],
            'status.name_translations.*' => ['nullable', 'string', 'max:255'],
            'status.description_translations' => ['nullable', 'array'],
            'status.description_translations.*' => ['nullable', 'string', 'max:1000'],
            'status.color' => ['nullable', 'string', 'max:32'],
            'status.sort_order' => ['nullable', 'integer', 'min:0'],
            'status.is_default' => ['nullable', 'boolean', new OnlyOneDefaultEnrollmentStatusRule($statusId)],
            'status.is_active' => ['nullable', 'boolean'],
            'status.is_final' => ['nullable', 'boolean'],
            'status.is_success' => ['nullable', 'boolean'],
            'status.is_cancelled' => ['nullable', 'boolean'],
            'status.is_waiting_documents' => ['nullable', 'boolean'],
            'status.is_waiting_payment' => ['nullable', 'boolean'],
            'status.is_in_progress' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.code.required' => tkey('students.validation.dictionary_key_required'),
            'status.code.max' => tkey('students.validation.dictionary_key_too_long'),
            'status.code.unique' => tkey('students.validation.dictionary_key_unique'),
            'status.name.max' => tkey('students.validation.dictionary_name_too_long'),
            'status.name_translations.required' => tkey('students.validation.default_dictionary_name_required'),
            'status.name_translations.array' => tkey('students.validation.default_dictionary_name_required'),
            'status.name_translations.*.max' => tkey('students.validation.dictionary_name_too_long'),
            'status.description_translations.*.max' => tkey('students.validation.dictionary_description_too_long'),
            'status.color.max' => tkey('students.validation.dictionary_color_too_long'),
            'status.sort_order.integer' => tkey('students.validation.dictionary_sort_order_invalid'),
            'status.sort_order.min' => tkey('students.validation.dictionary_sort_order_invalid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function statusData(): array
    {
        $data = $this->validated('status');
        unset($data['id']);

        foreach ([
            'is_default',
            'is_active',
            'is_final',
            'is_success',
            'is_cancelled',
            'is_waiting_documents',
            'is_waiting_payment',
            'is_in_progress',
        ] as $field) {
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
