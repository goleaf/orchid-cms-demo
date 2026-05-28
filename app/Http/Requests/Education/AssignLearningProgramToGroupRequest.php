<?php

namespace App\Http\Requests\Education;

use App\Models\LearningProgram;
use App\Models\TrainingGroup;
use App\Rules\LearningProgramIsActiveRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignLearningProgramToGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.groups.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'learning_program_id' => ['required', 'integer', Rule::exists(LearningProgram::class, 'id'), new LearningProgramIsActiveRule],
        ];
    }
}
