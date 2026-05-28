<?php

namespace App\Http\Requests\Education;

use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupSchedulePattern;
use App\Rules\DuplicateSchedulePatternRule;
use App\Rules\SchedulePatternTimeRangeRule;
use App\Rules\ValidDayOfWeekRule;
use App\Rules\ValidSchedulePatternTypeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingGroupSchedulePatternRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess([
            'education.groups.manage_schedule_patterns',
            'education.manage_schedule_patterns',
        ]) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $patternId = $this->integer('pattern.id') ?: null;

        return [
            'pattern.id' => ['nullable', 'integer', Rule::exists(TrainingGroupSchedulePattern::class, 'id')],
            'pattern.training_group_id' => ['required', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'pattern.title_translations' => ['nullable', 'array'],
            'pattern.title_translations.*' => ['nullable', 'string', 'max:255'],
            'pattern.day_of_week' => ['required', 'integer', new ValidDayOfWeekRule],
            'pattern.start_time' => ['required_without:pattern.starts_at', 'nullable', 'date_format:H:i'],
            'pattern.starts_at' => ['required_without:pattern.start_time', 'nullable', 'date_format:H:i'],
            'pattern.end_time' => ['required_without:pattern.ends_at', 'nullable', 'date_format:H:i', new SchedulePatternTimeRangeRule, new DuplicateSchedulePatternRule($patternId)],
            'pattern.ends_at' => ['required_without:pattern.end_time', 'nullable', 'date_format:H:i', new SchedulePatternTimeRangeRule, new DuplicateSchedulePatternRule($patternId)],
            'pattern.type' => ['required_without:pattern.lesson_type', 'nullable', 'string', new ValidSchedulePatternTypeRule],
            'pattern.lesson_type' => ['required_without:pattern.type', 'nullable', 'string', new ValidSchedulePatternTypeRule],
            'pattern.classroom' => ['nullable', 'string', 'max:120'],
            'pattern.classroom_id' => ['nullable', 'integer'],
            'pattern.location_translations' => ['nullable', 'array'],
            'pattern.location_translations.*' => ['nullable', 'string', 'max:255'],
            'pattern.notes_translations' => ['nullable', 'array'],
            'pattern.notes_translations.*' => ['nullable', 'string', 'max:1000'],
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
        $data['type'] = $data['type'] ?? $data['lesson_type'] ?? 'theory';
        $data['lesson_type'] = $data['lesson_type'] ?? $data['type'];
        $data['start_time'] = $data['start_time'] ?? $data['starts_at'];
        $data['starts_at'] = $data['starts_at'] ?? $data['start_time'];
        $data['end_time'] = $data['end_time'] ?? $data['ends_at'];
        $data['ends_at'] = $data['ends_at'] ?? $data['end_time'];
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }
}
