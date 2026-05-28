<?php

namespace App\Http\Requests\Education;

use App\Models\LearningTopic;
use App\Rules\DictionaryCodeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateLearningTopicRequest extends StoreLearningTopicRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $topicId = $this->integer('topic.id') ?: null;
        $rules = parent::rules();

        $rules['topic.id'] = ['required', 'integer', Rule::exists(LearningTopic::class, 'id')];
        $rules['topic.code'] = ['nullable', 'string', 'max:120', new DictionaryCodeRule, Rule::unique(LearningTopic::class, 'code')->where('learning_program_module_id', $this->input('topic.learning_program_module_id'))->ignore($topicId)];

        return $rules;
    }

    public function topicId(): int
    {
        return (int) $this->validated('topic.id');
    }

    /**
     * @return array<string, mixed>
     */
    public function topicData(): array
    {
        $data = parent::topicData();
        unset($data['id']);

        return $data;
    }
}
