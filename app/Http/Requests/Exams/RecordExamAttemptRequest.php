<?php

namespace App\Http\Requests\Exams;

use App\Enums\ExamAttemptStatus;
use App\Http\Requests\Exams\Concerns\UsesExamValidationMessages;
use App\Models\DrivingLesson;
use App\Models\ExamAdmission;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Rules\ExamAdmissionReadyRule;
use App\Rules\ExamSessionCanAcceptAttemptRule;
use App\Rules\ValidExamTypeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordExamAttemptRequest extends FormRequest
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
            'attempt.exam_admission_id' => ['required', 'integer', Rule::exists(ExamAdmission::class, 'id'), new ExamAdmissionReadyRule($this->boolean('allow_unready_admission'))],
            'attempt.exam_session_id' => ['nullable', 'integer', Rule::exists(ExamSession::class, 'id'), new ExamSessionCanAcceptAttemptRule($this->boolean('allow_full_session'))],
            'attempt.exam_type' => ['nullable', 'string', new ValidExamTypeRule],
            'attempt.status' => ['nullable', Rule::enum(ExamAttemptStatus::class)],
            'attempt.instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'attempt.driving_lesson_id' => ['nullable', 'integer', Rule::exists(DrivingLesson::class, 'id')],
            'attempt.student_document_id' => ['nullable', 'integer', Rule::exists(StudentDocument::class, 'id')],
            'attempt.payment_id' => ['nullable', 'integer', Rule::exists(Payment::class, 'id')],
            'attempt.retake_of_attempt_id' => ['nullable', 'integer', Rule::exists(ExamAttempt::class, 'id')],
            'attempt.score' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'attempt.max_score' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'attempt.passed' => ['nullable', 'boolean'],
            'attempt.result_payload' => ['nullable', 'array'],
            'attempt.started_at' => ['nullable', 'date'],
            'attempt.finished_at' => ['nullable', 'date'],
            'attempt.next_eligible_at' => ['nullable', 'date'],
            'attempt.official_reference' => ['nullable', 'string', 'max:120'],
            'attempt.official_payload' => ['nullable', 'array'],
            'attempt.notes' => ['nullable', 'string', 'max:2000'],
            'attempt.internal_notes' => ['nullable', 'string', 'max:2000'],
            'allow_unready_admission' => ['nullable', 'boolean'],
            'allow_full_session' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function attemptData(): array
    {
        $data = $this->validated('attempt');
        unset($data['exam_admission_id'], $data['exam_session_id']);

        foreach ([
            'instructor_id',
            'driving_lesson_id',
            'student_document_id',
            'payment_id',
            'retake_of_attempt_id',
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = filled($data[$field]) ? (int) $data[$field] : null;
            }
        }

        if ($this->boolean('allow_unready_admission')) {
            $data['allow_unready_admission'] = true;
        }

        if ($this->boolean('allow_full_session')) {
            $data['allow_full_session'] = true;
        }

        return $data;
    }
}
