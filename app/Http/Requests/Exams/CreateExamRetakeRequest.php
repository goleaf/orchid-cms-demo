<?php

namespace App\Http\Requests\Exams;

use App\Http\Requests\Exams\Concerns\UsesExamValidationMessages;
use App\Models\DrivingLesson;
use App\Models\ExamAttempt;
use App\Models\ExamRetake;
use App\Models\ExamSession;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Rules\ExamAttemptCanBeRetakenRule;
use App\Rules\ExamRetakeAllowedRule;
use App\Rules\ExamSessionCanAcceptAttemptRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateExamRetakeRequest extends FormRequest
{
    use UsesExamValidationMessages;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.exams', 'exams.schedule_retakes']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'previous_attempt_id' => ['nullable', 'required_without:attempt.id', 'integer', Rule::exists(ExamAttempt::class, 'id'), new ExamRetakeAllowedRule],
            'new_attempt_id' => ['nullable', 'integer', Rule::exists(ExamAttempt::class, 'id')],
            'exam_session_id' => ['nullable', 'integer', Rule::exists(ExamSession::class, 'id'), new ExamSessionCanAcceptAttemptRule($this->boolean('allow_full_session'))],
            'planned_at' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'max:40'],
            'retake_id' => ['nullable', 'integer', Rule::exists(ExamRetake::class, 'id')],
            'attempt.id' => ['nullable', 'required_without:previous_attempt_id', 'integer', Rule::exists(ExamAttempt::class, 'id'), new ExamAttemptCanBeRetakenRule],
            'retake.exam_session_id' => ['nullable', 'integer', Rule::exists(ExamSession::class, 'id'), new ExamSessionCanAcceptAttemptRule($this->boolean('allow_full_session'))],
            'retake.instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'retake.driving_lesson_id' => ['nullable', 'integer', Rule::exists(DrivingLesson::class, 'id')],
            'retake.student_document_id' => ['nullable', 'integer', Rule::exists(StudentDocument::class, 'id')],
            'retake.payment_id' => ['nullable', 'integer', Rule::exists(Payment::class, 'id')],
            'retake.next_eligible_at' => ['nullable', 'date'],
            'retake.notes' => ['nullable', 'string', 'max:2000'],
            'retake.internal_notes' => ['nullable', 'string', 'max:2000'],
            'allow_full_session' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retakeData(): array
    {
        $nested = $this->validated('retake') ?? [];

        return [
            ...collect($this->safe()->only([
                'previous_attempt_id',
                'new_attempt_id',
                'exam_session_id',
                'planned_at',
                'reason',
                'status',
                'retake_id',
            ]))->filter(fn (mixed $value): bool => $value !== null)->all(),
            ...$nested,
        ];
    }
}
