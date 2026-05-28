<?php

namespace App\Http\Requests\Education;

use App\Models\LearningProgram;
use App\Models\LearningProgramModule;
use App\Rules\DictionaryCodeRule;
use App\Rules\LearningProgramIsActiveRule;
use App\Rules\TranslatedLearningProgramNameRequiredRule;
use App\Rules\ValidLearningProgramModuleTypeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLearningProgramModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.manage_topics') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'module.learning_program_id' => ['required', 'integer', Rule::exists(LearningProgram::class, 'id'), new LearningProgramIsActiveRule],
            'module.code' => ['nullable', 'string', 'max:120', new DictionaryCodeRule, Rule::unique(LearningProgramModule::class, 'code')->where('learning_program_id', $this->input('module.learning_program_id'))],
            'module.type' => ['required', 'string', new ValidLearningProgramModuleTypeRule],
            'module.name_translations' => ['required', 'array', new TranslatedLearningProgramNameRequiredRule],
            'module.name_translations.*' => ['nullable', 'string', 'max:255'],
            'module.description_translations' => ['nullable', 'array'],
            'module.description_translations.*' => ['nullable', 'string', 'max:2000'],
            'module.required_hours' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'module.sort_order' => ['nullable', 'integer', 'min:0'],
            'module.is_required' => ['nullable', 'boolean'],
            'module.is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function moduleData(): array
    {
        $data = $this->validated('module');

        $data['learning_program_id'] = (int) $data['learning_program_id'];
        $data['required_hours'] = filled($data['required_hours'] ?? null) ? (float) $data['required_hours'] : null;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        foreach (['is_required', 'is_active'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        return $data;
    }
}
