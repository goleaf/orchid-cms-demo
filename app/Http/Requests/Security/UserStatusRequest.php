<?php

namespace App\Http\Requests\Security;

use App\Models\UserStatus;
use App\Rules\OnlyOneDefaultUserStatusRule;
use App\Rules\TranslatedDictionaryNameRequiredRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['security.user_statuses.manage', 'platform.systems.users']) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        $statusId = $this->integer('status.id') ?: null;

        return [
            'status.id' => ['nullable', 'integer', Rule::exists(UserStatus::class, 'id')],
            'status.code' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:[_-][a-z0-9]+)*$/',
                Rule::unique(UserStatus::class, 'code')->ignore($statusId),
            ],
            'status.name_translations' => ['required', 'array', new TranslatedDictionaryNameRequiredRule],
            'status.name_translations.*' => ['nullable', 'string', 'max:255'],
            'status.description_translations' => ['nullable', 'array'],
            'status.description_translations.*' => ['nullable', 'string', 'max:1000'],
            'status.color' => ['nullable', 'string', 'max:32'],
            'status.sort_order' => ['nullable', 'integer', 'min:0'],
            'status.is_default' => ['nullable', 'boolean', new OnlyOneDefaultUserStatusRule($statusId)],
            'status.is_active' => ['nullable', 'boolean'],
            'status.is_blocked' => ['nullable', 'boolean'],
            'status.is_archived' => ['nullable', 'boolean'],
            'status.is_final' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.code.required' => tkey('security.validation.user_status_code_required'),
            'status.code.regex' => tkey('security.validation.user_status_code_invalid'),
            'status.code.unique' => tkey('security.validation.user_status_code_unique'),
            'status.code.max' => tkey('security.validation.user_status_code_invalid'),
            'status.name_translations.required' => tkey('security.validation.user_status_name_required'),
            'status.name_translations.array' => tkey('security.validation.user_status_name_required'),
            'status.name_translations.*.max' => tkey('security.validation.user_status_name_too_long'),
            'status.description_translations.*.max' => tkey('security.validation.user_status_description_too_long'),
            'status.color.max' => tkey('security.validation.user_status_color_too_long'),
            'status.sort_order.integer' => tkey('security.validation.user_status_sort_order_invalid'),
            'status.sort_order.min' => tkey('security.validation.user_status_sort_order_invalid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function statusData(): array
    {
        $data = $this->validated('status');
        unset($data['id']);

        foreach (['is_default', 'is_active', 'is_blocked', 'is_archived', 'is_final'] as $field) {
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
