<?php

namespace App\Http\Requests\Education;

use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupSchedulePattern;
use App\Rules\ValidLearningTopicTypeRule;
use App\Rules\ValidScheduleDayRule;
use App\Rules\ValidSchedulePatternTimeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingGroupSchedulePatternRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.manage_schedule_patterns') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pattern.id' => ['nullable', 'integer', Rule::exists(TrainingGroupSchedulePattern::class, 'id')],
            'pattern.training_group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'pattern.title_translations' => ['nullable', 'array'],
            'pattern.title_translations.*' => ['nullable', 'string', 'max:255'],
            'pattern.day_of_week' => ['required', 'integer', new ValidScheduleDayRule],
            'pattern.starts_at' => ['required', 'date_format:H:i'],
            'pattern.ends_at' => ['required', 'date_format:H:i', new ValidSchedulePatternTimeRule],
            'pattern.lesson_type' => ['required', 'string', new ValidLearningTopicTypeRule],
            'pattern.classroom' => ['nullable', 'string', 'max:120'],
            'pattern.instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'pattern.is_active' => ['nullable', 'boolean'],
            'pattern.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function patternData(): array
    {
        $data = $this->validated('pattern');
        unset($data['id']);

        $data['day_of_week'] = (int) $data['day_of_week'];
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }
}
