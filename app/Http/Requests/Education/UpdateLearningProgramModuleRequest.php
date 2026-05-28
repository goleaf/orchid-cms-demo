<?php

namespace App\Http\Requests\Education;

use App\Models\LearningProgramModule;
use App\Rules\DictionaryCodeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateLearningProgramModuleRequest extends StoreLearningProgramModuleRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $moduleId = $this->integer('module.id') ?: null;
        $rules = parent::rules();

        $rules['module.id'] = ['required', 'integer', Rule::exists(LearningProgramModule::class, 'id')];
        $rules['module.code'] = ['nullable', 'string', 'max:120', new DictionaryCodeRule, Rule::unique(LearningProgramModule::class, 'code')->where('learning_program_id', $this->input('module.learning_program_id'))->ignore($moduleId)];

        return $rules;
    }

    public function moduleId(): int
    {
        return (int) $this->validated('module.id');
    }

    /**
     * @return array<string, mixed>
     */
    public function moduleData(): array
    {
        $data = parent::moduleData();
        unset($data['id']);

        return $data;
    }
}
