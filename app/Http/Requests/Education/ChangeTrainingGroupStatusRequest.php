<?php

namespace App\Http\Requests\Education;

use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Rules\ActiveTrainingGroupStatusRule;
use App\Rules\ValidTrainingGroupStatusTransitionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeTrainingGroupStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.groups.update') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'status_id' => ['required', 'integer', Rule::exists(TrainingGroupStatus::class, 'id'), new ActiveTrainingGroupStatusRule, new ValidTrainingGroupStatusTransitionRule($this->group(), $this->user(), $this->boolean('override_status_transition'))],
            'comment' => ['nullable', 'string', 'max:2000'],
            'override_status_transition' => ['nullable', 'boolean'],
        ];
    }

    public function group(): ?TrainingGroup
    {
        return filled($this->input('group_id')) ? TrainingGroup::query()->find($this->integer('group_id')) : null;
    }
}
