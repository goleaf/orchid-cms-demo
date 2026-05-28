<?php

namespace App\Http\Requests\Education;

use App\Models\TrainingGroup;
use App\Rules\TrainingGroupCanBeUpdatedRule;
use App\Rules\TrainingGroupDateRangeRule;
use App\Rules\TranslatedGroupNameRequiredRule;
use App\Rules\ValidTrainingGroupCapacityValueRule;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateTrainingGroupRequest extends \App\Http\Requests\TrainingGroupRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $group = $this->group();
        $rules = parent::rules();

        $rules['group.id'][] = new TrainingGroupCanBeUpdatedRule($group, $this->user(), $this->boolean('override_group_lock'));
        $rules['name_translations'] = ['required', 'array', new TranslatedGroupNameRequiredRule];
        $rules['group.capacity'][] = new ValidTrainingGroupCapacityValueRule($group, $this->boolean('override_capacity'));
        $rules['group.ends_on'][] = new TrainingGroupDateRangeRule;
        $rules['group.start_date'] = ['nullable', 'date'];
        $rules['group.planned_end_date'] = ['nullable', 'date', new TrainingGroupDateRangeRule];
        $rules['group.actual_end_date'] = ['nullable', 'date', new TrainingGroupDateRangeRule];
        $rules['group.capacity_total'] = ['nullable', 'integer', new ValidTrainingGroupCapacityValueRule($group, $this->boolean('override_capacity'))];
        $rules['override_group_lock'] = ['nullable', 'boolean'];
        $rules['override_capacity'] = ['nullable', 'boolean'];

        return $rules;
    }

    private function group(): ?TrainingGroup
    {
        $groupId = $this->input('group.id');

        return filled($groupId) ? TrainingGroup::query()->find((int) $groupId) : null;
    }
}
