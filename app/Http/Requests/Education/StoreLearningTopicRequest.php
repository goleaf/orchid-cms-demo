<?php

namespace App\Http\Requests\Education;

use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
use App\Rules\DictionaryCodeRule;
use App\Rules\TranslatedLearningProgramNameRequiredRule;
use App\Rules\ValidLearningTopicTypeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLearningTopicRequest extends FormRequest
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
            'topic.learning_program_module_id' => ['required', 'integer', Rule::exists(LearningProgramModule::class, 'id')],
            'topic.code' => ['nullable', 'string', 'max:120', new DictionaryCodeRule, Rule::unique(LearningTopic::class, 'code')->where('learning_program_module_id', $this->input('topic.learning_program_module_id'))],
            'topic.name_translations' => ['required', 'array', new TranslatedLearningProgramNameRequiredRule],
            'topic.name_translations.*' => ['nullable', 'string', 'max:255'],
            'topic.description_translations' => ['nullable', 'array'],
            'topic.description_translations.*' => ['nullable', 'string', 'max:2000'],
            'topic.topic_type' => ['nullable', 'string', new ValidLearningTopicTypeRule],
            'topic.estimated_hours' => ['nullable', 'numeric', 'min:0', 'max:10000'],
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

        $data['learning_program_module_id'] = (int) $data['learning_program_module_id'];
        $data['topic_type'] = $data['topic_type'] ?? 'theory';
        $data['estimated_hours'] = filled($data['estimated_hours'] ?? null) ? (float) $data['estimated_hours'] : null;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        foreach (['is_required', 'is_active'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        return $data;
    }
}
