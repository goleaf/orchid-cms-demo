<?php

namespace App\Http\Requests\Education;

use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
use App\Models\TrainingProgram;
use App\Rules\TranslatedFieldRequiredRule;
use App\Rules\ValidLearningTopicTypeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LearningTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess([
            'education.programs.manage_topics',
            'education.manage_topics',
        ]) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $topicId = $this->integer('topic.id') ?: null;

        return [
            'topic.id' => ['nullable', 'integer', Rule::exists(LearningTopic::class, 'id')],
            'topic.training_program_id' => ['required', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'topic.course_module_id' => ['nullable', 'integer', Rule::exists(LearningProgramModule::class, 'id')],
            'topic.code' => ['nullable', 'string', 'max:120', Rule::unique('learning_topics', 'code')->where('training_program_id', $this->input('topic.training_program_id'))->ignore($topicId)],
            'topic.title_translations' => ['required', 'array', new TranslatedFieldRequiredRule],
            'topic.title_translations.*' => ['nullable', 'string', 'max:255'],
            'topic.description_translations' => ['nullable', 'array'],
            'topic.description_translations.*' => ['nullable', 'string', 'max:2000'],
            'topic.topic_type' => ['required', 'string', new ValidLearningTopicTypeRule],
            'topic.duration_minutes' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'topic.sort_order' => ['nullable', 'integer', 'min:0'],
            'topic.is_required' => ['nullable', 'boolean'],
            'topic.is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function topicData(): array
    {
        $data = $this->validated('topic');
        unset($data['id']);

        foreach (['is_required', 'is_active'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
