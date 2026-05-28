<?php

namespace App\Http\Requests\Education;

use App\Models\TrainingGroup;
use App\Rules\GroupCanBePublishedRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishTrainingGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.groups.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id'), new GroupCanBePublishedRule],
        ];
    }
}
