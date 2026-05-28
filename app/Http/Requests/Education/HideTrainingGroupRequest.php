<?php

namespace App\Http\Requests\Education;

use App\Models\TrainingGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HideTrainingGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.groups.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id')],
        ];
    }
}
