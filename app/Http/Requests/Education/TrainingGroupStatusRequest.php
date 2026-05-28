<?php

namespace App\Http\Requests\Education;

use App\Models\TrainingGroupStatus;
use App\Rules\DictionaryCodeRule;
use App\Rules\TranslatedDictionaryNameRequiredRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingGroupStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.manage_statuses') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $statusId = $this->integer('status.id') ?: null;

        return [
            'status.id' => ['nullable', 'integer', Rule::exists(TrainingGroupStatus::class, 'id')],
            'status.code' => ['required', 'string', 'max:120', new DictionaryCodeRule, Rule::unique(TrainingGroupStatus::class, 'code')->ignore($statusId)],
            'status.name' => ['nullable', 'string', 'max:255'],
            'status.name_translations' => ['required', 'array', new TranslatedDictionaryNameRequiredRule],
            'status.name_translations.*' => ['nullable', 'string', 'max:255'],
            'status.description_translations' => ['nullable', 'array'],
            'status.description_translations.*' => ['nullable', 'string', 'max:1000'],
            'status.color' => ['nullable', 'string', 'max:32'],
            'status.sort_order' => ['nullable', 'integer', 'min:0'],
            'status.is_default' => ['nullable', 'boolean'],
            'status.is_active' => ['nullable', 'boolean'],
            'status.is_public' => ['nullable', 'boolean'],
            'status.accepts_enrollments' => ['nullable', 'boolean'],
            'status.is_in_progress' => ['nullable', 'boolean'],
            'status.is_final' => ['nullable', 'boolean'],
            'status.is_success' => ['nullable', 'boolean'],
            'status.is_cancelled' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function statusData(): array
    {
        $data = $this->validated('status');
        unset($data['id']);

        foreach (['is_default', 'is_active', 'is_public', 'accepts_enrollments', 'is_in_progress', 'is_final', 'is_success', 'is_cancelled'] as $field) {
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
