<?php

namespace App\Http\Requests\Exams;

use App\Http\Requests\Exams\Concerns\UsesExamValidationMessages;
use App\Models\ExamSession;
use App\Models\ExamStatus;
use App\Rules\ActiveExamStatusRule;
use App\Rules\ValidExamSessionStatusTransitionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeExamSessionStatusRequest extends FormRequest
{
    use UsesExamValidationMessages;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.exams', 'exams.manage_sessions']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $session = $this->sessionModel();

        return [
            'exam_session_id' => ['nullable', 'integer', Rule::exists(ExamSession::class, 'id')],
            'status_id' => ['required', 'integer', Rule::exists(ExamStatus::class, 'id'), new ActiveExamStatusRule, new ValidExamSessionStatusTransitionRule($session, $this->boolean('allow_override'))],
            'allow_override' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function sessionModel(): ?ExamSession
    {
        $routeSession = $this->route('examSession') ?? $this->route('session');

        if ($routeSession instanceof ExamSession) {
            return $routeSession;
        }

        return filled($this->input('exam_session_id')) ? ExamSession::query()->find($this->input('exam_session_id')) : null;
    }
}
