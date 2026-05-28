<?php

namespace App\Http\Requests\Education;

use App\Models\TrainingGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddTrainingGroupNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.groups.update') ?? false;
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    public function group(): TrainingGroup
    {
        return TrainingGroup::query()->findOrFail((int) $this->validated('group_id'));
    }

    public function body(): string
    {
        return (string) $this->validated('body');
    }
}
