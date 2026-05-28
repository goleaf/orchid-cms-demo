<?php

namespace App\Http\Requests\Exams;

use App\Http\Requests\Exams\Concerns\UsesExamValidationMessages;
use App\Models\ExamAttempt;
use App\Rules\ExamAttemptCanCompleteRule;
use App\Rules\ExamResultScoreRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteExamAttemptRequest extends FormRequest
{
    use UsesExamValidationMessages;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.exams', 'exams.record_results']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attempt_id' => ['required', 'integer', Rule::exists(ExamAttempt::class, 'id'), new ExamAttemptCanCompleteRule],
            'score' => ['nullable', 'numeric', new ExamResultScoreRule],
            'max_score' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'passed' => ['required', 'boolean'],
            'examiner_comment' => ['nullable', 'string', 'max:2000'],
            'mistakes_summary' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
