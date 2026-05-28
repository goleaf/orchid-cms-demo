<?php

namespace App\Http\Requests\Education;

use App\Models\TrainingGroup;
use App\Rules\TrainingGroupCanBeArchivedRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArchiveTrainingGroupRequest extends FormRequest
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
            'group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id'), new TrainingGroupCanBeArchivedRule($this->group(), $this->user(), $this->boolean('override_active_memberships'))],
            'override_active_memberships' => ['nullable', 'boolean'],
        ];
    }

    public function group(): ?TrainingGroup
    {
        return filled($this->input('group_id')) ? TrainingGroup::query()->find($this->integer('group_id')) : null;
    }
}
