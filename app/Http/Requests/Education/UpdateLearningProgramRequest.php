<?php

namespace App\Http\Requests\Education;

use App\Models\LearningProgram;
use App\Rules\DictionaryCodeRule;
use App\Rules\TranslatedLearningProgramNameRequiredRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateLearningProgramRequest extends StoreLearningProgramRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $programId = $this->integer('program.id') ?: null;
        $rules = parent::rules();

        $rules['program.id'] = ['required', 'integer', Rule::exists(LearningProgram::class, 'id')];
        $rules['program.code'] = ['nullable', 'string', 'max:120', new DictionaryCodeRule, Rule::unique(LearningProgram::class, 'code')->ignore($programId)];
        $rules['program.name_translations'] = ['required', 'array', new TranslatedLearningProgramNameRequiredRule];

        return $rules;
    }

    public function programId(): int
    {
        return (int) $this->validated('program.id');
    }

    /**
     * @return array<string, mixed>
     */
    public function programData(): array
    {
        $data = parent::programData();
        unset($data['id']);

        return $data;
    }
}
