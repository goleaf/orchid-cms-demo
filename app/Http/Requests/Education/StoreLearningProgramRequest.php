<?php

namespace App\Http\Requests\Education;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\LearningProgram;
use App\Rules\DictionaryCodeRule;
use App\Rules\TranslatedLearningProgramNameRequiredRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLearningProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('education.manage_topics') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'program.course_id' => ['nullable', 'integer', Rule::exists(Course::class, 'id')],
            'program.course_category_id' => ['nullable', 'integer', Rule::exists(CourseCategory::class, 'id')],
            'program.code' => ['nullable', 'string', 'max:120', new DictionaryCodeRule, Rule::unique(LearningProgram::class, 'code')],
            'program.name_translations' => ['required', 'array', new TranslatedLearningProgramNameRequiredRule],
            'program.name_translations.*' => ['nullable', 'string', 'max:255'],
            'program.description_translations' => ['nullable', 'array'],
            'program.description_translations.*' => ['nullable', 'string', 'max:2000'],
            'program.is_default' => ['nullable', 'boolean'],
            'program.is_active' => ['nullable', 'boolean'],
            'program.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function programData(): array
    {
        $data = $this->validated('program');

        foreach (['course_id', 'course_category_id'] as $field) {
            $data[$field] = filled($data[$field] ?? null) ? (int) $data[$field] : null;
        }

        foreach (['is_default', 'is_active'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
