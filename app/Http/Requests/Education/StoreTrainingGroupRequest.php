<?php

namespace App\Http\Requests\Education;

use App\Rules\TrainingGroupDateRangeRule;
use App\Rules\TranslatedGroupNameRequiredRule;
use App\Rules\ValidTrainingGroupCapacityValueRule;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreTrainingGroupRequest extends \App\Http\Requests\TrainingGroupRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['name_translations'] = ['required', 'array', new TranslatedGroupNameRequiredRule];
        $rules['group.capacity'][] = new ValidTrainingGroupCapacityValueRule;
        $rules['group.ends_on'][] = new TrainingGroupDateRangeRule;
        $rules['group.start_date'] = ['nullable', 'date'];
        $rules['group.planned_end_date'] = ['nullable', 'date', new TrainingGroupDateRangeRule];
        $rules['group.actual_end_date'] = ['nullable', 'date', new TrainingGroupDateRangeRule];
        $rules['group.capacity_total'] = ['nullable', 'integer', new ValidTrainingGroupCapacityValueRule];

        return $rules;
    }
}
