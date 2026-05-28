<?php

namespace App\Http\Requests\Education;

use App\Models\TrainingGroupSchedulePattern;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteTrainingGroupSchedulePatternRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.manage_schedule_patterns') ?? false;
    }

    public function rules(): array
    {
        return [
            'pattern_id' => ['required', 'integer', Rule::exists(TrainingGroupSchedulePattern::class, 'id')],
        ];
    }
}
